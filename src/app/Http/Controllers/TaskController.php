<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{

    public function index()
    {
        // Busca todas as tarefas com dados mínimos para a visão Kanban
        $tasks = Task::select('id', 'title', 'description', 'status', 'deadline')
            ->orderBy('deadline')
            ->get();

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
        ]);
    }

    /**
     * Exibe o formulário para criar uma nova tarefa para um projeto.
     *
     * @param Project $project
     * @return \Inertia\Response
     */
    public function create(Project $project)
    {
        return Inertia::render('Tasks/Create', [
            'project' => $project->only('id', 'name'),
        ]);
    }

    /**
     * Armazena uma nova tarefa associada a um projeto.
     *
     * @param Request $request
     * @param Project $project
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:pending,in_progress,done',
            'deadline'    => 'required|date',
        ]);

        $task = $project->tasks()->create($validated);

        // Recarga el proyecto con las tareas incluyendo la recién creada
        $project->load('tasks');

        // Retorna la vista Projects/Show con el proyecto actualizado y la tarea creada aparte
        return Inertia::render('Projects/Show', [
            'project' => $project,
            'createdTask' => $task,
            'flash' => [
                'success' => 'Tarefa criada com sucesso!',
            ],
        ]);
    }

    /**
     * Exibe o formulário para editar uma tarefa existente.
     *
     * @param Task $task
     * @return \Inertia\Response
     */
    public function edit(Task $task)
    {
        // Carrega projeto para mostrar dados mínimos se necessário
        $task->load('project:id,name');

        return Inertia::render('Tasks/Edit', [
            'task'    => $task,
            'project' => $task->project->only('id', 'name'),
        ]);
    }

    /**
     * Atualiza os dados da tarefa.
     *
     * @param Request $request
     * @param Task $task
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:pending,in_progress,done',
            'deadline'    => 'nullable|date',
        ]);

        $task->update($validated);

        return redirect()
            ->route('projects.show', $task->project)
            ->with('success', 'Tarefa atualizada com sucesso!');
    }
}
