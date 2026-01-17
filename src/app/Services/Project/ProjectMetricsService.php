<?php

namespace App\Services\Project;

use App\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Serviço responsável pelo Read Model de Projetos.
 *
 * Implementa um CQRS light:
 * - Apenas leitura
 * - Dados otimizados para UI
 * - Cacheados por usuário e filtros
 *
 * Centraliza:
 * - Agregações (contagem de tarefas)
 * - Cálculos derivados (progresso)
 * - Normalização de payload
 */
class ProjectMetricsService
{
    /**
     * Tempo de vida do cache (em segundos).
     *
     * @var int
     */
    protected int $ttl = 300; // 5 minutos

    /**
     * Retorna projetos recentes do usuário com métricas de progresso.
     *
     * Usado para dashboard, traz projetos limitados sem filtro textual ou de saúde.
     *
     * @param int $userId ID do usuário autenticado
     * @param int $limit Limite de projetos retornados
     * @return Collection<int, array<string, mixed>> Coleção de projetos normalizados para UI
     */
    public function getRecentProjects(int $userId, int $limit = 10): Collection
    {
        $cacheKey = $this->cacheKey($userId, null, $limit);

        return Cache::remember($cacheKey, $this->ttl, function () use ($userId, $limit) {
            return Project::query()
                ->where('user_id', $userId)
                ->withCount([
                    'tasks',
                    'tasks as completed_tasks_count' => fn($query) => $query->where('status', 'done'),
                ])
                ->orderBy('position')
                ->limit($limit)
                ->get()
                ->map(fn(Project $project) => $this->mapProject($project));
        });
    }

    /**
     * Retorna projetos do usuário com filtros opcionais e métricas de progresso.
     *
     * Suporta busca textual por nome/descrição e filtro por status de saúde.
     *
     * @param int $userId ID do usuário autenticado
     * @param string|null $search Termo de busca textual (nome ou descrição)
     * @param string|null $healthStatus Filtro por status de saúde (ex: 'Em Alerta', 'Saudável', 'all')
     * @return Collection<int, array<string, mixed>> Coleção de projetos normalizados para UI
     */
    public function getProjectsWithFilters(
        int $userId,
        ?string $search = null,
        ?string $healthStatus = null
    ): Collection {
        $cacheKey = $this->cacheKey($userId, $search, null);

        $projects = Cache::remember($cacheKey, $this->ttl, function () use ($userId, $search) {
            $query = Project::query()
                ->where('user_id', $userId)
                ->when(
                    filled($search),
                    fn($query) => $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    })
                )
                ->withCount([
                    'tasks',
                    'tasks as completed_tasks_count' => fn($query) => $query->where('status', 'done'),
                ])
                ->orderBy('position');

            return $query->get();
        });

        if ($healthStatus && $healthStatus !== 'all') {
            $projects = $projects->filter(fn(Project $project) => $project->health_status === $healthStatus);
        }

        return $projects->map(fn(Project $project) => $this->mapProject($project))->values();
    }

    /**
     * Normaliza um projeto com métricas calculadas.
     *
     * Centraliza a transformação para garantir consistência
     * entre todas as telas da aplicação.
     *
     * @param Project $project Projeto com contadores carregados
     * @return array<string, mixed> Estrutura pronta para a UI
     */
    protected function mapProject(Project $project): array
    {
        $progress = $project->tasks_count > 0
            ? (int) round(($project->completed_tasks_count / $project->tasks_count) * 100)
            : 0;

        return [
            'id'          => $project->id,
            'name'        => $project->name,
            'description' => $project->description,
            'health'      => $project->health_status,
            'tasks_count' => $project->tasks_count,
            'progress'    => $progress,
        ];
    }

    /**
     * Gera a chave de cache para o read model de projetos.
     *
     * A chave considera:
     * - Usuário
     * - Termo de busca
     * - Limite
     *
     * Evita colisões e mantém o cache determinístico.
     *
     * @param int $userId
     * @param string|null $search
     * @param int|null $limit
     * @return string
     */
    protected function cacheKey(
        int $userId,
        ?string $search,
        ?int $limit
    ): string {
        return sprintf(
            'projects.metrics:%d:search:%s:limit:%s',
            $userId,
            $search ? md5($search) : 'none',
            $limit ?? 'all'
        );
    }

    /**
     * Invalida todos os caches de métricas de um usuário.
     *
     * Estratégia:
     * - Namespace por usuário
     * - Compatível com múltiplos filtros
     *
     * Deve ser chamado por observers (projects, tasks).
     *
     * @param int $userId
     * @return void
     */
    public function clearUserCache(int $userId): void
    {
        Cache::tags($this->userCacheTag($userId))->flush();
    }

    /**
     * Retorna a tag de cache do usuário.
     *
     * @param int $userId
     * @return array<int, string>
     */
    protected function userCacheTag(int $userId): array
    {
        return ["projects.metrics.user.{$userId}"];
    }
}
