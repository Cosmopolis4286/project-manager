<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Services\Project\ProjectMetricsService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller responsável pelo Dashboard do usuário.
 */
class DashboardController extends Controller
{
    public function __construct(
        protected ProjectMetricsService $projectMetrics
    ) {}

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
        $projects = $this->projectMetrics->getProjectsWithProgress($userId, 10);

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
