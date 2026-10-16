@extends('layouts.app')

@section('titre', 'Utilisateurs')

@section('contenu')
    <h1>Utilisateurs ({{ $utilisateurs->count() }})</h1>

    <div class="carte-formulaire" style="max-width: 820px; margin-bottom: 26px;">
        <h2>Nouvel utilisateur</h2>
        <form method="POST" action="/utilisateurs">
            @csrf
            <div class="grille-champs">
                <div class="champ">
                    <label for="nomUtilisateur">Nom</label>
                    <input id="nomUtilisateur" name="nomUtilisateur" value="{{ old('nomUtilisateur') }}"
                           class="@error('nomUtilisateur') invalide @enderror" required>
                    @error('nomUtilisateur') <div class="erreur">{{ $message }}</div> @enderror
                </div>
                <div class="champ">
                    <label for="prenomUtilisateur">Prénom</label>
                    <input id="prenomUtilisateur" name="prenomUtilisateur" value="{{ old('prenomUtilisateur') }}"
                           class="@error('prenomUtilisateur') invalide @enderror" required>
                    @error('prenomUtilisateur') <div class="erreur">{{ $message }}</div> @enderror
                </div>
                <div class="champ">
                    <label for="login">Identifiant</label>
                    <input id="login" name="login" value="{{ old('login') }}"
                           class="@error('login') invalide @enderror" required>
                    @error('login') <div class="erreur">{{ $message }}</div> @enderror
                </div>
                <div class="champ">
                    <label for="motDePasse">Mot de passe (6 caractères minimum)</label>
                    <input id="motDePasse" type="password" name="motDePasse"
                           class="@error('motDePasse') invalide @enderror" required>
                    @error('motDePasse') <div class="erreur">{{ $message }}</div> @enderror
                </div>
                <div class="champ">
                    <label for="role">Rôle</label>
                    <select id="role" name="role" class="@error('role') invalide @enderror" required>
                        @foreach($roles as $r)
                            <option value="{{ $r }}" {{ old('role') == $r ? 'selected' : '' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                    @error('role') <div class="erreur">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="actions-formulaire">
                <button type="submit" class="btn btn-principal">Créer l'utilisateur</button>
            </div>
        </form>
    </div>

    <div class="carte" style="padding: 18px;">
        <table>
            <thead>
                <tr><th>Utilisateur</th><th>Identifiant</th><th>Rôle</th><th>Statut</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @foreach($utilisateurs as $u)
                    <tr>
                        <td><strong>{{ $u->nomUtilisateur }}</strong> {{ $u->prenomUtilisateur }}</td>
                        <td>{{ $u->login }}</td>
<td>
    <form action="/utilisateurs/{{ $u->idUtilisateur }}/role" method="POST">
        @csrf
        <select name="role" onchange="this.form.submit()"
                style="padding:6px 10px; border:2px solid var(--bordure); border-radius:10px;">
            @foreach($roles as $r)
                <option value="{{ $r }}" {{ $u->hasRole($r) ? 'selected' : '' }}>{{ $r }}</option>
            @endforeach
        </select>
    </form>
</td>
                        <td>
                            @if($u->actif)
                                <span class="badge badge-inscrit">Actif</span>
                            @else
                                <span class="badge badge-attente">Désactivé</span>
                            @endif
                        </td>
                        <td class="actions">
                            <form action="/utilisateurs/{{ $u->idUtilisateur }}/basculer" method="POST" style="display:inline">
                                @csrf
                                <button type="submit" class="btn {{ $u->actif ? 'btn-supprimer' : 'btn-modifier' }}">
                                    {{ $u->actif ? 'Désactiver' : 'Activer' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection