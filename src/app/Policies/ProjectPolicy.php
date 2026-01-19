<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Verifica se o projeto pertence ao usuário autenticado.
     *
     * @param User $user
     * @param Project $project
     * @return bool
     */
    public function owner(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }

    /**
     * Determine se o usuário pode visualizar o projeto.
     *
     * @param User $user
     * @param Project $project
     * @return bool
     */
    public function view(User $user, Project $project): bool
    {
        return $this->owner($user, $project);
    }

    /**
     * Determine se o usuário pode atualizar o projeto.
     *
     * @param User $user
     * @param Project $project
     * @return bool
     */
    public function update(User $user, Project $project): bool
    {
        return $this->owner($user, $project);
    }

    /**
     * Determine se o usuário pode deletar o projeto.
     *
     * @param User $user
     * @param Project $project
     * @return bool
     */
    public function delete(User $user, Project $project): bool
    {
        return $this->owner($user, $project);
    }

    /**
     * Determine se o usuário pode restaurar o projeto.
     *
     * @param User $user
     * @param Project $project
     * @return bool
     */
    public function restore(User $user, Project $project): bool
    {
        return $this->owner($user, $project);
    }

    /**
     * Determine se o usuário pode deletar permanentemente o projeto.
     *
     * @param User $user
     * @param Project $project
     * @return bool
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return $this->owner($user, $project);
    }

    /**
     * Determine se o usuário pode criar um projeto.
     * Geralmente qualquer usuário autenticado pode criar.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Pode listar projetos? Permitido para qualquer usuário autenticado.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine se o usuário pode reordenar os projetos.
     *
     * @param User $user
     * @param iterable|array $projects
     * @return bool
     */
    public function reorder(User $user, iterable $projects): bool
    {
        foreach ($projects as $project) {
            if (!$this->owner($user, $project)) {
                return false;
            }
        }
        return true;
    }
}
