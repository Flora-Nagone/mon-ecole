<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Connecte
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('utilisateur')) {
            return redirect('/login')->with('erreur', 'Veuillez vous connecter.');
        }

        return $next($request);
    }
}