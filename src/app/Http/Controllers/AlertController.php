<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Controlador responsable de las alertas del usuario.
 *
 * Maneja:
 * - Listado de alertas recientes
 * - Preparación de datos para Inertia
 *
 * IMPORTANTE:
 * - Todas las alertas se filtran por el usuario autenticado
 * - No se devuelve lógica de presentación aquí
 */
class AlertController extends Controller
{
    /**
     * Muestra las alertas recientes del usuario autenticado.
     *
     * @param  Request  $request
     * @return \Inertia\Response
     */
    public function recent(Request $request)
    {
        $user = $request->user();

        /**
         * ⚠️ Ejemplo base sin modelo Alert aún.
         * Si luego creas App\Models\Alert, esto se reemplaza
         * por una consulta Eloquent real.
         */
        $alerts = [
            'Tarefa atrasada no projeto X',
            'Novo comentário em uma tarefa',
            'Projeto Y foi atualizado',
        ];

        return Inertia::render('Alerts/Recent', [
            'alerts' => $alerts,
        ]);
    }
}
