<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Si es una solicitud preflight, responde inmediatamente
        if ($request->getMethod() === "OPTIONS") {
            return response()->json("OK", 200)
                ->header('Access-Control-Allow-Origin', $this->getAllowedOrigins())
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        }

        $response = $next($request);

        // Configurar los encabezados CORS en la respuesta real
        $response->headers->set('Access-Control-Allow-Origin', $this->getAllowedOrigins());
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');

        return $response;
    }

    protected function getAllowedOrigins()
    {
        return env('CORS_ALLOWED_ORIGINS', '*');
    }
}
