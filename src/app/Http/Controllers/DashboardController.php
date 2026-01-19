<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Services\Project\ProjectMetricsService;
use Illuminate\Http\Request;
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
     * Exibe o dashboard principal do usuário autenticado.
     *
     * Responsabilidades:
     * - Carregar métricas globais (projetos ativos, tarefas pendentes, alertas)
     * - Retornar projetos recentes ou filtrados por busca textual
     * - Sincronizar filtros recebidos via query string com a UI (Inertia)
     *
     * Regras:
     * - Sem termo de busca → projetos recentes (limitados)
     * - Com termo de busca → lista filtrada (server-side)
     *
     * @param  Request  $request  Request HTTP com filtros opcionais (ex: search)
     * @return Response           Resposta Inertia para a view Dashboard
     */
    public function index(Request $request): Response
    {
        /** @var int $userId */
        $userId = Auth::id();

        /**
         * Termo de busca textual (nome ou descrição do projeto).
         *
         * Normalizado:
         * - trim automático
         * - string vazia tratada como null
         */
        $search = $request->string('search')->trim()->toString();

        /**
         * Projetos exibidos no dashboard.
         *
         * - Sem busca: projetos recentes (resumo)
         * - Com busca: projetos filtrados (server-side)
         */
        $projects = filled($search)
            ? $this->projectMetrics->getProjectsWithFilters(
                userId: $userId,
                search: $search
            )
            : $this->projectMetrics->getRecentProjects($userId, 10);

        return Inertia::render('Dashboard', [
            /**
             * Filtros ativos (refletidos na UI).
             */
            'filters' => [
                'search' => $search,
            ],

            /**
             * Métricas globais do dashboard.
             */
            'stats' => [
                'active_projects' => Project::where('user_id', $userId)
                    ->where('status', 'active')
                    ->count(),

                'pending_tasks' => Task::where('user_id', $userId)
                    ->where('status', 'pending')
                    ->count(),

                'recent_alerts' => $projects
                    ->where('health', 'Em Alerta')
                    ->count(),
            ],

            /**
             * Projetos com métricas normalizadas para UI.
             *
             * @var \Illuminate\Support\Collection<int, array<string, mixed>>
             */
            'projects' => $projects,

            /**
             * Notificações recentes do usuário.
             */
            'notifications' => [],
        ]);
    }
}
