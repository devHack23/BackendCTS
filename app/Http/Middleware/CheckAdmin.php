<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    /**
     * Gère une requête entrante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. On récupère l'utilisateur via le guard API (JWT)
        $user = auth('api')->user();

        // 2. On vérifie s'il est connecté ET si son rôle est 'admin'
        // Selon votre migration : $table->enum('role', ['electeur','admin'])
        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'error' => 'Accès refusé. Privilèges administrateur requis.'
            ], 403);
        }

        return $next($request);
    }
}