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
 * Adaptação para funcionar sem cache com tags,
 * usando estratégia de versionamento para invalidação.
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
     * Usa cache com versão para evitar usar tags.
     *
     * @param int $userId ID do usuário autenticado
     * @param int $limit Limite de projetos retornados
     * @return Collection<int, array<string, mixed>> Projetos normalizados para UI
     */
    public function getRecentProjects(int $userId, int $limit = 10): Collection
    {
        // Obtém versão atual do cache para o usuário
        $version = $this->getUserCacheVersion($userId);

        // Gera chave de cache incluindo a versão para garantir invalidação
        $cacheKey = $this->cacheKey($userId, null, $limit, $version);

        // Retorna do cache ou executa consulta
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
     * Usa cache versionado para invalidação sem tags.
     *
     * @param int $userId ID do usuário autenticado
     * @param string|null $search Termo de busca (nome ou descrição)
     * @param string|null $healthStatus Filtro por status de saúde ('Em Alerta', 'Saudável', 'all')
     * @return Collection<int, array<string, mixed>> Projetos normalizados para UI
     */
    public function getProjectsWithFilters(
        int $userId,
        ?string $search = null,
        ?string $healthStatus = null
    ): Collection {
        $version = $this->getUserCacheVersion($userId);
        $cacheKey = $this->cacheKey($userId, $search, null, $version);

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

        // Filtra projetos por status de saúde, se aplicável
        if ($healthStatus && $healthStatus !== 'all') {
            $projects = $projects->filter(fn(Project $project) => $project->health_status === $healthStatus);
        }

        // Normaliza os dados para UI
        return $projects->map(fn(Project $project) => $this->mapProject($project))->values();
    }

    /**
     * Normaliza um projeto com métricas calculadas.
     *
     * Centraliza a transformação para garantir consistência.
     *
     * @param Project $project Projeto com contadores carregados
     * @return array<string, mixed> Estrutura pronta para UI
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
     * Gera a chave de cache para projetos,
     * incluindo versão para controle de invalidação.
     *
     * @param int $userId
     * @param string|null $search Termo de busca
     * @param int|null $limit Limite de resultados
     * @param string $version Versão atual do cache do usuário
     * @return string Chave única para cache
     */
    protected function cacheKey(
        int $userId,
        ?string $search,
        ?int $limit,
        string $version
    ): string {
        return sprintf(
            'projects.metrics:%d:search:%s:limit:%s:version:%s',
            $userId,
            $search ? md5($search) : 'none',
            $limit ?? 'all',
            $version
        );
    }

    /**
     * Obtém a versão atual do cache do usuário.
     * Se não existir, inicializa com timestamp atual.
     *
     * @param int $userId
     * @return string Versão do cache (timestamp)
     */
    protected function getUserCacheVersion(int $userId): string
    {
        $versionKey = $this->cacheVersionKey($userId);

        // Usa rememberForever para persistir a versão até invalidação
        return Cache::rememberForever($versionKey, fn() => (string) time());
    }

    /**
     * Invalida o cache do usuário atualizando a versão.
     *
     * O timestamp novo faz as chaves antigas expirarem.
     *
     * @param int $userId
     * @return void
     */
    public function clearUserCache(int $userId): void
    {
        $versionKey = $this->cacheVersionKey($userId);

        // Atualiza versão com timestamp atual e expira em 30 dias para limpeza
        Cache::put($versionKey, (string) time(), now()->addDays(30));
    }

    /**
     * Gera a chave para armazenar a versão do cache de um usuário.
     *
     * @param int $userId
     * @return string Chave da versão do cache
     */
    protected function cacheVersionKey(int $userId): string
    {
        return "projects.metrics.version.user.{$userId}";
    }
}
