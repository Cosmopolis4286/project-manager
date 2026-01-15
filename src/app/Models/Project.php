<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Modelo Project representa os projetos do sistema.
 *
 * Responsabilidades:
 * - Gerenciar atributos do projeto
 * - Relacionamento com tarefas
 * - Cálculo de saúde do projeto
 * - Ordenação automática (position)
 */
class Project extends Model
{
    /**
     * Atributos que podem ser atribuídos em massa.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'description',
        'status',
        'user_id',
        'position',
    ];

    /**
     * Relacionamento: Um projeto possui muitas tarefas.
     *
     * @return HasMany
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Atributo calculado: Status de saúde do projeto.
     *
     * Regras:
     * - Sem tarefas → Saudável
     * - Mais de 20% das tarefas atrasadas → Em Alerta
     *
     * @return string
     */
    public function getHealthStatusAttribute(): string
    {
        $total = $this->tasks()->count();

        if ($total === 0) {
            return 'Saudável';
        }

        $overdue = $this->tasks()
            ->where('deadline', '<', now())
            ->where('status', '!=', 'done')
            ->count();

        return ($overdue / $total) > 0.2
            ? 'Em Alerta'
            : 'Saudável';
    }

    /**
     * Scope para filtrar projetos ativos.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Boot do modelo.
     *
     * Define automaticamente a posição e usuário do projeto
     * no momento da criação, garantindo ordenação estável
     * por usuário.
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
                $maxPosition = static::where('user_id', $project->user_id)->max('position');
                $project->position = $maxPosition !== null ? $maxPosition + 1 : 1;
            }
        });
    }
}
