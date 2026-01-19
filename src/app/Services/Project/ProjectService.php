<?php

namespace App\Services\Project;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use App\Services\Project\ProjectMetricsService;

/**
 * Service responsável pelos comandos (write) relacionados a Projetos.
 *
 * Implementa operações que modificam o estado do domínio:
 * - criação
 * - atualização
 * - reordenação
 *
 * IMPORTANTE:
 * Sempre que o estado é alterado, o cache de métricas do usuário
 * é invalidado, mantendo consistência com o modelo de leitura
 * (CQRS light).
 */
class ProjectService
{
    /**
     * @param ProjectMetricsService $metrics
     */
    public function __construct(
        protected ProjectMetricsService $metrics
    ) {}

    /**
     * Cria um novo projeto para o usuário autenticado.
     *
     * @param array $data Dados validados do projeto
     * @return Project Projeto criado
     *
     * @throws \RuntimeException Caso ocorra algum erro ao criar o projeto
     */
    public function createProject(array $data): Project
    {
        try {
            $data['user_id'] = Auth::id();
            $project = Project::create($data);
            $this->metrics->clearUserCache($data['user_id']);
            return $project;
        } catch (\Exception $e) {
            throw new \RuntimeException('Erro ao criar o projeto', 0, $e);
        }
    }

    /**
     * Atualiza um projeto existente.
     *
     * ATENÇÃO:
     * A verificação de autorização deve ser realizada no Controller.
     *
     * Após a atualização, o cache de métricas do usuário
     * é invalidado para evitar dados inconsistentes.
     *
     * @param Project $project Projeto a ser atualizado
     * @param array $data Dados validados
     * @return Project Projeto atualizado
     */
    public function updateProject(Project $project, array $data): Project
    {
        $project->update($data);

        $this->metrics->clearUserCache($project->user_id);

        return $project;
    }

    /**
     * Reordena os projetos do usuário autenticado.
     *
     * Espera um array contendo os IDs dos projetos e
     * suas respectivas posições.
     *
     * Exemplo:
     * [
     *   ['id' => 1, 'position' => 1],
     *   ['id' => 2, 'position' => 2],
     * ]
     *
     * Após a reordenação, o cache de métricas do usuário
     * é invalidado para manter a consistência dos dados exibidos.
     *
     * @param array $projectsData Lista de projetos com suas posições
     * @return void
     */
    public function reorderProjects(array $projectsData): void
    {
        $userId = Auth::id();

        foreach ($projectsData as $project) {
            Project::where('id', $project['id'])
                ->where('user_id', $userId)
                ->update([
                    'position' => $project['position'],
                ]);
        }

        $this->metrics->clearUserCache($userId);
    }

    /**
     * Exclui um projeto do usuário autenticado.
     *
     * Realiza verificação de propriedade e invalida cache.
     *
     * @param int $projectId ID do projeto a ser excluído
     * @return void
     *
     * @throws ModelNotFoundException Caso o projeto não pertença ao usuário ou não exista
     * @throws \RuntimeException Em caso de erro na exclusão
     */
    public function delete(int $projectId): void
    {
        $userId = Auth::id();

        $project = Project::where('id', $projectId)
            ->where('user_id', $userId)
            ->firstOrFail();

        try {
            $project->delete();

            $this->metrics->clearUserCache($userId);
        } catch (\Exception $e) {
            throw new \RuntimeException('Erro ao excluir o projeto', 0, $e);
        }
    }
}
