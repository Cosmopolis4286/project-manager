<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ProjectService
{
    public function __construct() {}

    /**
     * Cria um novo projeto para o usuário autenticado.
     *
     * @param array $data Dados validados
     * @return Project
     */
    public function createProject(array $data): Project
    {
        $data['user_id'] = Auth::id();

        return Project::create($data);
    }

    /**
     * Atualiza um projeto existente.
     *
     * ATENÇÃO: É responsabilidade do controlador garantir que o usuário
     * tem permissão para atualizar este projeto.
     *
     * @param Project $project
     * @param array $data Dados validados
     * @return Project
     */
    public function updateProject(Project $project, array $data): Project
    {
        $project->update($data);

        return $project;
    }

    /**
     * Reordena projetos do usuário.
     *
     * @param array $projectsData Array com id e position
     * @return void
     */
    public function reorderProjects(array $projectsData): void
    {
        $userId = Auth::id();

        foreach ($projectsData as $projectData) {
            Project::where('id', $projectData['id'])
                ->where('user_id', $userId)
                ->update(['position' => $projectData['position']]);
        }
    }
}
