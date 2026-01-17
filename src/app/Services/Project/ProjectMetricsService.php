<?php

namespace App\Services\Project;

use App\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Service responsável pelo Read Model de Projetos.
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
     * Retorna projetos do usuário com métricas de progresso.
     *
     * Suporta:
     * - Busca textual (nome e descrição)
     * - Limite de resultados
     * - Cache isolado por usuário + filtros
     *
     * @param int $userId ID do usuário autenticado
     * @param string|null $search Termo de busca textual
     * @param int|null $limit Limite de projetos
     *
     * @return Collection Coleção pronta para consumo pela UI
     */
    public function getProjectsWithProgress(
        int $userId,
        ?string $search = null,
        ?int $limit = null
    ): Collection {
        $cacheKey = $this->cacheKey($userId, $search, $limit);

        return Cache::remember($cacheKey, $this->ttl, function () use ($userId, $search, $limit) {
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
                    'tasks as completed_tasks_count' => fn($query) =>
                    $query->where('status', 'done'),
                ])
                ->orderBy('position');

            if ($limit !== null) {
                $query->limit($limit);
            }

            return $query
                ->get()
                ->map(fn(Project $project) => $this->mapProject($project));
        });
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
     *
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
