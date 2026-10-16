<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleRequis
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!in_array(session('utilisateur.role'), $roles)) {
            return redirect('/')->with('erreur',
                "Accès refusé : votre rôle n'autorise pas cette page.");
        }

        return $next($request);
    }
}