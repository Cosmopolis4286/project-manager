<?php

namespace App\Observers;

use App\Models\Task;
use App\Services\Project\ProjectMetricsService;

/**
 * Observer de Task.
 *
 * Responsável por reagir a eventos do ciclo de vida
 * das tarefas e manter a consistência do domínio
 * do projeto ao qual pertencem.
 *
 * Responsabilidades:
 * - Recalcular o status do projeto quando tarefas mudam
 * - Invalidar cache de métricas do usuário (CQRS light)
 *
 * Eventos observados:
 * - created
 * - updated (status ou deadline)
 * - deleted
 */
class TaskObserver
{
    /**
     * Serviço responsável pelas métricas e cache
     * de projetos do usuário.
     */
    public function __construct(
        protected ProjectMetricsService $metrics
    ) {}

    /**
     * Executado após a criação de uma tarefa.
     *
     * Uma nova tarefa pode alterar:
     * - total de tarefas
     * - percentual de tarefas atrasadas
     *
     * @param Task $task
     * @return void
     */
    public function created(Task $task): void
    {
        $this->recalculateProject($task);
    }

    /**
     * Executado após atualização de uma tarefa.
     *
     * O recálculo só é necessário se atributos
     * relevantes para a regra de negócio forem alterados.
     *
     * @param Task $task
     * @return void
     */
    public function updated(Task $task): void
    {
        if ($task->wasChanged(['status', 'deadline'])) {
            $this->recalculateProject($task);
        }
    }

    /**
     * Executado após remoção de uma tarefa.
     *
     * A exclusão impacta diretamente o cálculo
     * do status do projeto.
     *
     * @param Task $task
     * @return void
     */
    public function deleted(Task $task): void
    {
        $this->recalculateProject($task);
    }

    /**
     * Recalcula o status do projeto associado à tarefa
     * e invalida o cache de métricas do usuário.
     *
     * Este método centraliza a lógica reativa do domínio,
     * garantindo consistência entre tarefas, projetos
     * e dados de leitura cacheados.
     *
     * @param Task $task
     * @return void
     */
    protected function recalculateProject(Task $task): void
    {
        if (!$task->project) {
            return;
        }

        // Regra de domínio
        $task->project->recalculateStatus();

        // Invalidação de cache (CQRS light)
        $this->metrics->clearUserCache(
            $task->project->user_id
        );
    }
}
