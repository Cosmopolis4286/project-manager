<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Throwable;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Executa uma ação encapsulando o tratamento de exceções.
     *
     * Este método NÃO deve ser chamado diretamente.
     * É o núcleo compartilhado pelos handlers Web e JSON.
     *
     * @template TReturn
     *
     * @param callable(): TReturn $callback
     * @param string $errorMessage
     * @param callable(Throwable): mixed $onError
     *
     * @return TReturn|mixed
     */
    protected function executeSafely(
        callable $callback,
        string $errorMessage,
        callable $onError
    ) {
        try {
            return $callback();
        } catch (Throwable $e) {
            Log::error($errorMessage, [
                'exception' => $e,
            ]);

            return $onError($e);
        }
    }

    /**
     * Tratamento centralizado de erros para Controllers Web (redirect).
     *
     * @param callable $callback
     * @param string $errorMessage
     *
     * @return mixed|RedirectResponse
     */
    protected function handleService(
        callable $callback,
        string $errorMessage
    ) {
        return $this->executeSafely(
            $callback,
            $errorMessage,
            function () use ($errorMessage): RedirectResponse {
                Session::flash('error', $errorMessage);

                return redirect()
                    ->back()
                    ->withErrors(['error' => $errorMessage]);
            }
        );
    }
}
