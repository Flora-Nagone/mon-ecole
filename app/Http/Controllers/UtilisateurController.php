<?php

namespace App\Http\Controllers;

use App\Models\Enseignant;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UtilisateurController extends Controller
{
    public function index()
    {
        $utilisateurs = Utilisateur::with(['roles', 'enseignant'])->orderBy('nomUtilisateur')->get();
        $roles = Role::orderBy('name')->pluck('name');
        $enseignants = Enseignant::orderBy('nomEnseignant')->orderBy('prenomEnseignant')->get();

        return view('utilisateurs.index', compact('utilisateurs', 'roles', 'enseignants'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'login'                   => ['required', 'string', 'max:50', 'unique:utilisateur,login'],
            'nomUtilisateur'          => ['required', 'string', 'max:100'],
            'prenomUtilisateur'       => ['required', 'string', 'max:100'],
            'motDePasse'              => ['required', 'string', 'min:6', 'confirmed'],
            'role'                    => ['required', Rule::exists('roles', 'name')],
            'Enseignant_idEnseignant' => [
                'required_if:role,Enseignant',
                'nullable',
                'integer',
                Rule::exists('enseignant', 'idEnseignant'),
                Rule::unique('utilisateur', 'Enseignant_idEnseignant'),
            ],
        ], $this->messages());

        $utilisateur = Utilisateur::create([
            'login'                   => $data['login'],
            'nomUtilisateur'          => $data['nomUtilisateur'],
            'prenomUtilisateur'       => $data['prenomUtilisateur'],
            'motDePasse'              => Hash::make($data['motDePasse']),
            'role'                    => $data['role'], // ancienne colonne, à supprimer plus tard
            'actif'                   => 1,
            'Enseignant_idEnseignant' => $data['Enseignant_idEnseignant'] ?? null,
        ]);

        $utilisateur->syncRoles([$data['role']]);

        return back()->with('succes', 'Utilisateur créé.');
    }

    public function update(Request $request, $id)
    {
        $utilisateur = Utilisateur::findOrFail($id);

        $data = $request->validate([
            'login'                   => ['required', 'string', 'max:50',
                                          Rule::unique('utilisateur', 'login')->ignore($utilisateur->idUtilisateur, 'idUtilisateur')],
            'nomUtilisateur'          => ['required', 'string', 'max:100'],
            'prenomUtilisateur'       => ['required', 'string', 'max:100'],
            'motDePasse'              => ['nullable', 'string', 'min:6', 'confirmed'],
            'role'                    => ['required', Rule::exists('roles', 'name')],
            'Enseignant_idEnseignant' => [
                'required_if:role,Enseignant',
                'nullable',
                'integer',
                Rule::exists('enseignant', 'idEnseignant'),
                Rule::unique('utilisateur', 'Enseignant_idEnseignant')
                    ->ignore($utilisateur->idUtilisateur, 'idUtilisateur'),
            ],
        ], $this->messages());

        // On ne retire pas le rôle Administrateur au dernier administrateur actif
        if ($utilisateur->hasRole('Administrateur')
            && $data['role'] !== 'Administrateur'
            && $this->dernierAdmin($utilisateur)) {
            return back()->withErrors(['role' => "Impossible : c'est le dernier administrateur actif."]);
        }

        $utilisateur->fill([
            'login'                   => $data['login'],
            'nomUtilisateur'          => $data['nomUtilisateur'],
            'prenomUtilisateur'       => $data['prenomUtilisateur'],
            'role'                    => $data['role'],
            'Enseignant_idEnseignant' => $data['Enseignant_idEnseignant'] ?? null,
        ]);

        // Mot de passe changé seulement s'il est renseigné
        if (!empty($data['motDePasse'])) {
            $utilisateur->motDePasse = Hash::make($data['motDePasse']);
        }

        $utilisateur->save();
        $utilisateur->syncRoles([$data['role']]);

        return back()->with('succes', 'Utilisateur modifié.');
    }

    public function destroy($id)
    {
        $utilisateur = Utilisateur::findOrFail($id);

        if ($utilisateur->idUtilisateur === auth()->id()) {
            return back()->withErrors(['suppression' => 'Vous ne pouvez pas supprimer votre propre compte.']);
        }

        if ($utilisateur->hasRole('Administrateur') && $this->dernierAdmin($utilisateur)) {
            return back()->withErrors(['suppression' => "Impossible : c'est le dernier administrateur actif."]);
        }

        $utilisateur->syncRoles([]);
        $utilisateur->delete();

        return back()->with('succes', 'Utilisateur supprimé.');
    }

    public function basculer($id)
    {
        $utilisateur = Utilisateur::findOrFail($id);

        if ($utilisateur->idUtilisateur === auth()->id()) {
            return back()->withErrors(['suppression' => 'Vous ne pouvez pas désactiver votre propre compte.']);
        }

        if ($utilisateur->actif && $utilisateur->hasRole('Administrateur') && $this->dernierAdmin($utilisateur)) {
            return back()->withErrors(['suppression' => "Impossible : c'est le dernier administrateur actif."]);
        }

        $utilisateur->actif = $utilisateur->actif ? 0 : 1;
        $utilisateur->save();

        return back()->with('succes', 'Statut mis à jour.');
    }

    // Messages d'erreur en français pour le lien enseignant
    private function messages(): array
    {
        return [
            'Enseignant_idEnseignant.required_if' => "Choisissez l'enseignant lié à ce compte (obligatoire pour le rôle Enseignant).",
            'Enseignant_idEnseignant.exists'      => "Cet enseignant n'existe pas.",
            'Enseignant_idEnseignant.unique'      => 'Cet enseignant a déjà un compte utilisateur.',
        ];
    }

    // Vrai si $utilisateur est le seul administrateur actif
    private function dernierAdmin(Utilisateur $utilisateur): bool
    {
        return Utilisateur::role('Administrateur')
            ->where('actif', 1)
            ->where('idUtilisateur', '!=', $utilisateur->idUtilisateur)
            ->count() === 0;
    }
}