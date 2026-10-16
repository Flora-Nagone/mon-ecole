<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // L'Administrateur a accès à tout, sans lister chaque permission.
        // Pour les autres rôles, on retourne null : Laravel vérifie alors
        // les permissions normalement (celles de Spatie).
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Administrateur') ? true : null;
        });
    }
}