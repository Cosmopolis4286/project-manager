<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Evitar error si el usuario no está autenticado (por seguridad)
        if (!$user) {
            abort(403, 'Usuário não autenticado.');
        }

        // Obtener estadísticas reales con validación de relaciones existentes
        $stats = [
            'active_projects' => method_exists($user, 'projects') ? $user->projects()->active()->count() : 0,
            'pending_tasks' => method_exists($user, 'tasks') ? $user->tasks()->pending()->count() : 0,
            'recent_alerts' => method_exists($user, 'alerts') ? $user->alerts()->recent()->count() : 0,
        ];

        $notifications = [
            'Projeto "Site Nova Marca" atualizado.',
            'Tarefa "Revisar contrato" está atrasada.',
            'Nova mensagem de João.',
        ];

        return inertia('Dashboard', [
            'stats' => $stats,
            'notifications' => $notifications,
        ]);
    }
}
