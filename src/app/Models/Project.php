<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * Modelo Project representa os projetos do sistema.
 *
 * Contém atributos, relacionamentos com tarefas e métodos auxiliares.
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
        'status', // Incluido para filtrar projetos ativos
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
     * Atributo customizado para status de saúde do projeto.
     * Avalia a proporção de tarefas atrasadas.
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

        return ($overdue / $total) > 0.2 ? 'Em Alerta' : 'Saudável';
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
}
