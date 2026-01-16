<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Verifica se o projeto pertence ao usuário autenticado.
     */
    public function owner(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }

    /**
     * Determine se o usuário pode visualizar o projeto.
     */
    public function view(User $user, Project $project): bool
    {
        return $this->owner($user, $project);
    }

    /**
     * Determine se o usuário pode atualizar o projeto.
     */
    public function update(User $user, Project $project): bool
    {
        return $this->owner($user, $project);
    }

    /**
     * Determine se o usuário pode deletar o projeto.
     */
    public function delete(User $user, Project $project): bool
    {
        return $this->owner($user, $project);
    }

    /**
     * Determine se o usuário pode restaurar o projeto.
     */
    public function restore(User $user, Project $project): bool
    {
        return $this->owner($user, $project);
    }

    /**
     * Determine se o usuário pode deletar permanentemente o projeto.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return $this->owner($user, $project);
    }

    /**
     * Determine se o usuário pode criar um projeto.
     * Geralmente qualquer usuário autenticado pode criar.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Pode listar projetos? Permitido para qualquer usuário autenticado.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }
}
