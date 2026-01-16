<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller responsável pelo Dashboard do usuário.
 */
class DashboardController extends Controller
{
    /**
     * Exibe o dashboard principal.
     *
     * Carrega métricas globais e lista resumida de projetos
     * com indicadores de saúde e progresso.
     *
     * @return Response
     */
    public function index(): Response
    {
        $userId = Auth::id();

        /**
         * Projetos recentes com:
         * - total de tarefas
         * - tarefas concluídas
         * - cálculo de progresso (%)
         *
         * ⚠️ Eager loading otimizado (1 query)
         */
        $projects = Project::query()
            ->where('user_id', $userId)
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => function ($query) {
                    $query->where('status', 'done');
                },
            ])
            ->orderBy('position')
            ->limit(10)
            ->get()
            ->map(function (Project $project) {
                $progress = $project->tasks_count > 0
                    ? round(($project->completed_tasks_count / $project->tasks_count) * 100)
                    : 0;

                return [
                    'id'        => $project->id,
                    'name'      => $project->name,
                    'description' => $project->description,
                    'health'    => $project->health_status,
                    'tasks_count' => $project->tasks_count,
                    'progress'  => $progress,
                ];
            });

        return Inertia::render('Dashboard', [
            'stats' => [
                'active_projects' => Project::where('user_id', $userId)->where('status', 'active')->count(),
                'pending_tasks'   => Task::where('user_id', $userId)->where('status', 'pending')->count(),
                'recent_alerts'   => $projects->where('health', 'Em Alerta')->count(),
            ],
            'projects' => $projects,
            'notifications' => [],
        ]);
    }
}
