<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

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
     * @var array<string, 'datetime'>
     */
    protected $casts = [
        'deadline' => 'datetime',
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

    /**
     * Boot do modelo.
     *
     * Define automaticamente o status da tarefa e o usuário no momento da criação.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function (Task $task) {
            if (empty($task->user_id)) {
                $task->user_id = Auth::id();
            }

            if ($task->status === null) {
                $task->status = 'pending';
            }
        });
    }
}
