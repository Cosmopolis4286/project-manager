<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function __construct(
        protected ProjectService $service
    ) {}

    /**
     * Lista todos os projetos do usuário autenticado,
     * com contagem de tarefas e status de saúde.
     *
     * @return Response
     */
    public function index(): Response
    {
        $userId = Auth::id();

        $projects = Project::query()
            ->where('user_id', $userId)
            ->withCount('tasks')
            ->orderBy('position', 'asc')
            ->get(['id', 'name', 'description', 'position']);

        return Inertia::render('Projects/Index', [
            'projects' => $projects->map(fn(Project $project) => [
                'id'          => $project->id,
                'name'        => $project->name,
                'description' => $project->description,
                'health'      => $project->health_status,
                'tasks_count' => $project->tasks_count,
                'position'    => $project->position,
            ]),
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
        $this->authorizeProjectOwner($project);

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
                'name'        => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
            ]);

            Project::create($validated);

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
        $this->authorizeProjectOwner($project);

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
            $this->authorizeProjectOwner($project);

            $validated = $request->validate([
                'name'        => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
            ]);

            $project->update($validated);

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

            $userId = Auth::id();

            foreach ($request->projects as $projectData) {
                Project::where('id', $projectData['id'])
                    ->where('user_id', $userId)
                    ->update(['position' => $projectData['position']]);
            }

            return back()->with('success', 'Ordem dos projetos atualizada com sucesso!');
        }, 'Erro ao reordenar os projetos. Por favor, tente novamente.');
    }

    /**
     * Garante que o projeto pertence ao usuário autenticado.
     *
     * @param Project $project
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function authorizeProjectOwner(Project $project): void
    {
        if ($project->user_id !== Auth::id()) {
            abort(403, 'Acesso negado.');
        }
    }
}
