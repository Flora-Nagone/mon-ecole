<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    // Rôles de base : on ne peut ni les supprimer ni les renommer
    private const ROLES_SYSTEME = ['Administrateur', 'Secrétariat', 'Enseignant'];

    // Permissions regroupées par domaine, avec leur libellé en français
    private const GROUPES = [
        'Élèves & Familles' => [
            'eleves.voir'        => 'Consulter les élèves',
            'eleves.gerer'       => 'Ajouter, modifier, supprimer des élèves',
            'parents.gerer'      => 'Gérer les parents',
            'inscriptions.gerer' => 'Gérer les inscriptions',
            'versements.gerer'   => 'Gérer les versements, reçus et chiffres financiers',
        ],
        'Scolarité' => [
            'classes.voir'  => 'Consulter les classes et les listes',
            'classes.gerer' => 'Créer, modifier, supprimer des classes',
        ],
        'Suivi pédagogique' => [
            'seances.gerer'     => 'Gérer le planning des séances',
            'appel.faire'       => "Faire l'appel (présences et absences)",
            'evaluations.gerer' => 'Gérer les évaluations',
            'notes.saisir'      => 'Saisir les notes',
            'bulletins.voir'    => 'Consulter et imprimer les bulletins',
        ],
        'Données' => [
            'exports.faire' => 'Exporter les données (Excel / CSV)',
        ],
        'Administration' => [
            'parametrage.gerer'  => "Paramétrer l'établissement (années, niveaux, tarifs, matières...)",
            'utilisateurs.gerer' => 'Gérer les utilisateurs, les rôles et les permissions',
        ],
    ];

    public function index()
    {
        $roles = Role::with('permissions')->get()->sortBy(function ($role) {
            $position = array_search($role->name, self::ROLES_SYSTEME, true);
            return $position === false ? 99 : $position;
        })->values();

        // Nombre d'utilisateurs par rôle
        $nbUtilisateurs = DB::table('model_has_roles')
            ->select('role_id', DB::raw('COUNT(*) as n'))
            ->groupBy('role_id')
            ->pluck('n', 'role_id');

        $existantes = Permission::pluck('name')->all();

        $groupes = [];
        $connues = [];
        foreach (self::GROUPES as $titre => $permissions) {
            foreach ($permissions as $nom => $libelle) {
                if (in_array($nom, $existantes, true)) {
                    $groupes[$titre][$nom] = $libelle;
                    $connues[] = $nom;
                }
            }
        }

        foreach (array_diff($existantes, $connues) as $nom) {
            $groupes['Autres'][$nom] = $nom;
        }

        $systeme = self::ROLES_SYSTEME;

        return view('roles.index', compact('roles', 'groupes', 'nbUtilisateurs', 'systeme'));
    }

    // Création d'un nouveau rôle
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('roles', 'name')],
        ], [
            'name.required' => 'Le nom du rôle est obligatoire.',
            'name.max'      => 'Le nom du rôle est trop long (50 caractères maximum).',
            'name.unique'   => 'Ce rôle existe déjà.',
        ]);

        Role::create(['name' => trim($request->name), 'guard_name' => 'web']);

        return redirect('/roles')->with('succes', 'Le rôle « ' . trim($request->name) . ' » a été créé. Cochez ses permissions ci-dessous.');
    }

    // Enregistrement de la matrice des permissions
    public function update(Request $request)
    {
        $request->validate([
            'permissions'     => ['nullable', 'array'],
            'permissions.*'   => ['nullable', 'array'],
            'permissions.*.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        foreach (Role::all() as $role) {
            if ($role->name === 'Administrateur') {
                continue;
            }
            $role->syncPermissions($request->input("permissions.{$role->id}", []));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect('/roles')->with('succes', 'Les permissions ont été enregistrées.');
    }

    // Modification d'un rôle : nom (rôles personnalisés) + permissions
    public function modifier(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        if ($role->name === 'Administrateur') {
            return redirect('/roles')->with('erreur', "L'Administrateur a toujours tous les droits : il ne peut pas être modifié.");
        }

        $systeme = in_array($role->name, self::ROLES_SYSTEME, true);

        $regles = [
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ];
        if (! $systeme) {
            $regles['name'] = ['required', 'string', 'max:50', Rule::unique('roles', 'name')->ignore($role->id)];
        }

        $request->validate($regles, [
            'name.required' => 'Le nom du rôle est obligatoire.',
            'name.unique'   => 'Ce rôle existe déjà.',
            'name.max'      => 'Le nom du rôle est trop long (50 caractères maximum).',
        ]);

        if (! $systeme) {
            $role->update(['name' => trim($request->name)]);
        }
        $role->syncPermissions($request->input('permissions', []));
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect('/roles')->with('succes', 'Le rôle « ' . $role->name . ' » a été modifié.');
    }

    // Suppression d'un rôle personnalisé
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        if (in_array($role->name, self::ROLES_SYSTEME, true)) {
            return redirect('/roles')->with('erreur', 'Ce rôle de base ne peut pas être supprimé.');
        }

        $nb = DB::table('model_has_roles')->where('role_id', $role->id)->count();
        if ($nb > 0) {
            return redirect('/roles')->with('erreur', "Impossible : $nb utilisateur(s) ont encore ce rôle. Changez d'abord leur rôle.");
        }

        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect('/roles')->with('succes', 'Le rôle a été supprimé.');
    }
}