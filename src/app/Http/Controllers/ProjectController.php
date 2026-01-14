<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    /**
     * Lista todos os projetos.
     *
     * Retorna os projetos com:
     * - quantidade de tarefas
     * - status de saúde do projeto
     *
     * @return Response
     */
    public function index(): Response
    {
        $projects = Project::query()
            ->withCount('tasks')
            ->get(['id', 'name', 'health_status']);

        return Inertia::render('Projects/Index', [
            'projects' => $projects->map(fn(Project $project) => [
                'id'          => $project->id,
                'name'        => $project->name,
                'health'      => $project->health_status,
                'tasks_count' => $project->tasks_count,
            ]),
        ]);
    }

    /**
     * Exibe os detalhes de um projeto específico.
     *
     * @param Project $project
     * @return Response
     */
    public function show(Project $project): Response
    {
        $project->load('tasks');

        return Inertia::render('Projects/Show', [
            'project' => $project,
        ]);
    }

    /**
     * Exibe o formulário de criação de projeto.
     *
     * @return Response
     */
    public function create(): Response
    {
        return Inertia::render('Projects/Create');
    }

    /**
     * Armazena um novo projeto no banco de dados.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        Project::create($validated);

        return redirect()
            ->route('projects.index')
            ->with('success', 'Projeto criado com sucesso!');
    }

    /**
     * Exibe o formulário de edição de um projeto.
     *
     * @param Project $project
     * @return Response
     */
    public function edit(Project $project): Response
    {
        return Inertia::render('Projects/Edit', [
            'project' => $project,
        ]);
    }

    /**
     * Atualiza os dados de um projeto existente.
     *
     * @param Request $request
     * @param Project $project
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $project->update($validated);

        return redirect()
            ->route('projects.index')
            ->with('success', 'Projeto atualizado com sucesso!');
    }
}
