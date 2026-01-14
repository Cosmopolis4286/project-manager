<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Task representa as tarefas vinculadas a um projeto.
 *
 * Contém atributos essenciais, casts e relacionamento com Project.
 */
class Task extends Model
{
    /**
     * Atributos que podem ser atribuídos em massa.
     *
     * @var string[]
     */
    protected $fillable = [
        'project_id',
        'title',
        'description',
        'status',
        'deadline',
    ];

    /**
     * Casts para conversão de atributos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'deadline' => 'date',
    ];

    /**
     * Relacionamento: Uma tarefa pertence a um projeto.
     *
     * @return BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Scope para filtrar tarefas pendentes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
