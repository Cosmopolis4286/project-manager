<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\Project\ProjectService;
use App\Services\Project\ProjectMetricsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function __construct(
        protected ProjectService $service,
        protected ProjectMetricsService $projectMetrics
    ) {}

    /**
     * Lista os projetos do usuário autenticado.
     *
     * Responsabilidades:
     * - Orquestrar filtros vindos da query string
     * - Delegar leitura ao read model (CQRS light)
     * - Não conter lógica de domínio
     *
     * Filtros suportados:
     * - status:
     *   - all | null → todos
     *   - Em Alerta
     *   - Saudável
     * - search:
     *   - Busca textual por nome ou descrição
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $userId = (int) $request->user()->id;

        /**
         * Filtro de status de saúde do projeto.
         */
        $status = $request->query('status', 'all');

        /**
         * Termo de busca textual.
         */
        $search = trim((string) $request->query('search', ''));

        /**
         * Leitura via read model (cacheado por usuário).
         */
        $projects = $this->projectMetrics
            ->getProjectsWithFilters(
                userId: $userId,
                search: $search,
                healthStatus: $status
            );

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Exibe detalhes de um projeto específico.
     *
     * @param Project $project
     * @return Response
     */
    public function show(Project $project): Response
    {
        $this->authorize('view', $project);

        $project->load('tasks');

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'createdTask' => session('task'),
            'success' => session('success'),
        ]);
    }

    /**
     * Formulário para criar novo projeto.
     *
     * @return Response
     */
    public function create(): Response
    {
        return Inertia::render('Projects/Create');
    }

    /**
     * Armazena um novo projeto para o usuário autenticado.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        return $this->handleService(function () use ($request) {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
            ]);

            $this->authorize('create', Project::class);

            $this->service->createProject($validated);

            return redirect()
                ->route('projects.index')
                ->with('success', 'Projeto criado com sucesso!');
        }, 'Erro ao criar o projeto. Por favor, tente novamente.');
    }

    /**
     * Formulário para editar projeto.
     *
     * @param Project $project
     * @return Response
     */
    public function edit(Project $project): Response
    {
        $this->authorize('update', $project);

        return Inertia::render('Projects/Edit', [
            'project' => $project,
        ]);
    }

    /**
     * Atualiza projeto existente.
     *
     * @param Request $request
     * @param Project $project
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Project $project)
    {
        return $this->handleService(function () use ($request, $project) {
            $this->authorize('update', $project);

            $validated = $request->validate([
                'name'        => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
            ]);

            $this->service->updateProject($project, $validated);

            return redirect()
                ->route('projects.index')
                ->with('success', 'Projeto atualizado com sucesso!');
        }, 'Erro ao atualizar o projeto. Por favor, tente novamente.');
    }
    /**
     * Reordena projetos via drag & drop.
     *
     * Espera payload com array de objetos contendo 'id' e 'position'.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reorder(Request $request)
    {
        return $this->handleService(function () use ($request) {
            $request->validate([
                'projects'            => ['required', 'array'],
                'projects.*.id'       => ['required', 'integer', 'exists:projects,id'],
                'projects.*.position' => ['required', 'integer'],
            ]);

            // Buscar projetos pelo IDs recebidos
            $projectIds = array_column($request->projects, 'id');
            $projects = Project::whereIn('id', $projectIds)->get();

            if ($projects->count() !== count($projectIds)) {
                abort(404, 'Um ou mais projetos não encontrados.');
            }

            // Autorizar reorder via policy, passando coleção de projetos
            $this->authorize('reorder', $projects);

            $this->service->reorderProjects($request->projects);

            return back()->with('success', 'Ordem dos projetos atualizada com sucesso!');
        }, 'Erro ao reordenar os projetos. Por favor, tente novamente.');
    }

    /**
     * Remove um projeto.
     *
     * @param Project $project
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Project $project)
    {
        return $this->handleService(function () use ($project) {

            $this->authorize('delete', $project);

            try {
                $project->delete();

                return redirect()
                    ->route('projects.index')
                    ->with('success', 'Projeto excluído com sucesso!');
            } catch (\Exception $e) {
                return redirect()
                    ->route('projects.index')
                    ->with('error', 'Erro ao excluir o projeto. Por favor, tente novamente.');
            }
        }, 'Erro ao excluir o projeto. Por favor, tente novamente.');
    }
}
