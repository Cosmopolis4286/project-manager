<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Modelo Project.
 *
 * Representa um projeto pertencente a um usuário e
 * agrega regras de domínio relacionadas ao seu estado
 * e à organização de suas tarefas.
 *
 * Responsabilidades:
 * - Gerenciar atributos persistentes do projeto
 * - Manter relacionamento com tarefas
 * - Determinar e persistir o status do projeto (active | alert)
 * - Garantir ordenação estável por usuário (position)
 */
class Project extends Model
{
    /**
     * Atributos atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'status',
        'user_id',
        'position',
    ];

    /**
     * Relacionamento: um projeto possui várias tarefas.
     *
     * @return HasMany
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Atributo calculado para exibição do status de saúde do projeto.
     *
     * OBS:
     * Este accessor é apenas para apresentação.
     * A decisão do status é persistida no banco através
     * do método recalculateStatus().
     *
     * @return string
     */
    public function getHealthStatusAttribute(): string
    {
        return $this->status === 'alert'
            ? 'Em Alerta'
            : 'Saudável';
    }

    /**
     * Scope para retornar apenas projetos ativos.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Recalcula e persiste o status do projeto com base
     * no estado de suas tarefas.
     *
     * Regras de negócio:
     * - Sem tarefas → status "active"
     * - Mais de 20% das tarefas atrasadas (deadline < now e != done)
     *   → status "alert"
     *
     * Este método representa uma regra central de domínio
     * e deve ser chamado sempre que uma tarefa for criada,
     * atualizada ou removida.
     *
     * @return void
     */
    public function recalculateStatus(): void
    {
        $totalTasks = $this->tasks()->count();

        if ($totalTasks === 0) {
            if ($this->status !== 'active') {
                $this->update(['status' => 'active']);
            }
            return;
        }

        $overdueTasks = $this->tasks()
            ->where('deadline', '<', now())
            ->where('status', '!=', 'done')
            ->count();

        $newStatus = ($overdueTasks / $totalTasks) > 0.2
            ? 'alert'
            : 'active';

        if ($this->status !== $newStatus) {
            $this->update(['status' => $newStatus]);
        }
    }

    /**
     * Boot do modelo.
     *
     * Define automaticamente:
     * - o usuário proprietário do projeto
     * - a posição do projeto dentro da ordenação do usuário
     *
     * Garante que novos projetos sejam sempre inseridos
     * ao final da lista do respectivo usuário.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            if (empty($project->user_id)) {
                $project->user_id = Auth::id();
            }

            if ($project->position === null) {
                $maxPosition = static::where('user_id', $project->user_id)
                    ->max('position');

                $project->position = $maxPosition !== null
                    ? $maxPosition + 1
                    : 1;
            }
        });
    }
}
