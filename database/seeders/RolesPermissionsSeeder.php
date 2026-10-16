<?php

namespace Database\Seeders;

use App\Models\Utilisateur;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'eleves.voir', 'eleves.gerer',
            'parents.gerer',
            'inscriptions.gerer',
            'versements.gerer',
            'classes.voir', 'classes.gerer',
            'seances.gerer',
            'appel.faire',
            'evaluations.gerer',
            'notes.saisir',
            'bulletins.voir',
            'exports.faire',
            'parametrage.gerer',
            'utilisateurs.gerer',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // Administrateur : toutes les permissions
        Role::firstOrCreate(['name' => 'Administrateur', 'guard_name' => 'web'])
            ->syncPermissions(Permission::all());

        // Secrétariat : administratif et financier
        Role::firstOrCreate(['name' => 'Secrétariat', 'guard_name' => 'web'])
            ->syncPermissions([
                'eleves.voir', 'eleves.gerer', 'parents.gerer',
                'inscriptions.gerer', 'versements.gerer',
                'classes.voir', 'bulletins.voir', 'exports.faire',
            ]);

        // Enseignant : pédagogique
        Role::firstOrCreate(['name' => 'Enseignant', 'guard_name' => 'web'])
            ->syncPermissions([
                'eleves.voir', 'classes.voir', 'appel.faire',
                'evaluations.gerer', 'notes.saisir', 'bulletins.voir',
            ]);

        // Reprise des utilisateurs existants (ancienne colonne "role")
        foreach (Utilisateur::all() as $u) {
            if ($u->role && Role::where('name', $u->role)->exists()) {
                $u->syncRoles([$u->role]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}