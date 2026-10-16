<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
    // Permissions utilisées dans le code (routes, menus) : on ne peut ni les renommer ni les supprimer
    private const SYSTEME = [
        'eleves.voir'        => ['Élèves & Familles', 'Consulter les élèves'],
        'eleves.gerer'       => ['Élèves & Familles', 'Ajouter, modifier, supprimer des élèves'],
        'parents.gerer'      => ['Élèves & Familles', 'Gérer les parents'],
        'inscriptions.gerer' => ['Élèves & Familles', 'Gérer les inscriptions'],
        'versements.gerer'   => ['Élèves & Familles', 'Gérer les versements et reçus'],
        'classes.voir'       => ['Scolarité', 'Consulter les classes'],
        'classes.gerer'      => ['Scolarité', 'Créer, modifier, supprimer des classes'],
        'seances.gerer'      => ['Suivi pédagogique', 'Gérer le planning des séances'],
        'appel.faire'        => ['Suivi pédagogique', "Faire l'appel"],
        'evaluations.gerer'  => ['Suivi pédagogique', 'Gérer les évaluations'],
        'notes.saisir'       => ['Suivi pédagogique', 'Saisir les notes'],
        'bulletins.voir'     => ['Suivi pédagogique', 'Consulter les bulletins'],
        'exports.faire'      => ['Données', 'Exporter les données'],
        'parametrage.gerer'  => ['Administration', "Paramétrer l'établissement"],
        'utilisateurs.gerer' => ['Administration', 'Gérer utilisateurs, rôles et permissions'],
    ];

    public function index()
    {
        $permissions = Permission::orderBy('name')->get();

        // Nombre de rôles qui possèdent chaque permission
        $nbRoles = DB::table('role_has_permissions')
            ->select('permission_id', DB::raw('COUNT(*) as n'))
            ->groupBy('permission_id')
            ->pluck('n', 'permission_id');

        $systeme = self::SYSTEME;

        return view('permissions.index', compact('permissions', 'nbRoles', 'systeme'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9_.-]+$/', Rule::unique('permissions', 'name')],
        ], [
            'name.required' => 'Le nom de la permission est obligatoire.',
            'name.regex'    => 'Utilisez uniquement des minuscules, chiffres, point, tiret : ex. paiements.gerer',
            'name.unique'   => 'Cette permission existe déjà.',
            'name.max'      => 'Le nom est trop long (80 caractères maximum).',
        ]);

        Permission::create(['name' => $request->name, 'guard_name' => 'web']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect('/permissions')->with('succes', 'La permission « ' . $request->name . ' » a été créée.');
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        if (isset(self::SYSTEME[$permission->name])) {
            return redirect('/permissions')->with('erreur', 'Cette permission est utilisée par le code : elle ne peut pas être renommée.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9_.-]+$/', Rule::unique('permissions', 'name')->ignore($permission->id)],
        ], [
            'name.required' => 'Le nom de la permission est obligatoire.',
            'name.regex'    => 'Utilisez uniquement des minuscules, chiffres, point, tiret : ex. paiements.gerer',
            'name.unique'   => 'Cette permission existe déjà.',
        ]);

        $permission->update(['name' => $request->name]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect('/permissions')->with('succes', 'La permission a été modifiée.');
    }

    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);

        if (isset(self::SYSTEME[$permission->name])) {
            return redirect('/permissions')->with('erreur', 'Cette permission est utilisée par le code : elle ne peut pas être supprimée.');
        }

        $permission->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect('/permissions')->with('succes', 'La permission a été supprimée.');
    }
}