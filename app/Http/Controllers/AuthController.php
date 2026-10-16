<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function formulaire()
    {
        if (Auth::check()) {
            return redirect('/');
        }

        return view('login');
    }

    public function connexion(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'motDePasse' => 'required|string',
        ]);

        $utilisateur = Utilisateur::where('login', $request->login)->first();

        if (!$utilisateur || !Hash::check($request->motDePasse, $utilisateur->motDePasse)) {
            return back()->withInput($request->only('login'))
                ->with('erreur', 'Identifiant ou mot de passe incorrect.');
        }

        if (!$utilisateur->actif) {
            return back()->withInput($request->only('login'))
                ->with('erreur', 'Ce compte est désactivé. Contactez l\'administrateur.');
        }

        // Connexion Laravel : Auth::user() est disponible partout ensuite
        Auth::login($utilisateur);
        $request->session()->regenerate();

        // Informations d'affichage et de contrôle de propriété (ses propres séances, notes...)
        $role = match (true) {
            $utilisateur->hasRole('Administrateur') => 'admin',
            $utilisateur->hasRole('Secrétariat') => 'secretariat',
            default => 'enseignant',
        };

        session(['utilisateur' => [
            'id' => $utilisateur->idUtilisateur,
            'nom' => $utilisateur->nom_complet,
            'role' => $role,
            'libelleRole' => $utilisateur->getRoleNames()->first() ?? $utilisateur->role,
            'enseignantId' => $utilisateur->Enseignant_idEnseignant,
        ]]);

        return redirect('/')->with('succes', 'Bienvenue, ' . $utilisateur->nom_complet . '.');
    }

    public function deconnexion(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('succes', 'Vous êtes déconnecté.');
    }
}