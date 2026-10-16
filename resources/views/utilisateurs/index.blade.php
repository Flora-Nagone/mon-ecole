@extends('layouts.app')

@section('titre', 'Utilisateurs')

@push('styles')
<style>
    .form-grille { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 14px; }
    .form-grille label { display: block; font-size: .85rem; font-weight: 600; margin-bottom: 5px; }
    .form-grille input, .form-grille select {
        width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;
        font-size: .95rem; font-family: inherit; background: #fff;
    }
    .form-grille input:focus, .form-grille select:focus { outline: 2px solid #c4b5fd; border-color: #8b5cf6; }
    .form-aide { font-size: .78rem; color: #6b7280; margin-top: 4px; }
    .ligne-boutons { margin-top: 16px; display: flex; gap: 8px; justify-content: flex-end; }
    .badge-role { background: #ede9fe; color: #5b21b6; }
    .badge-actif { background: #dcfce7; color: #166534; }
    .badge-inactif { background: #e5e7eb; color: #4b5563; }
    .actions form { display: inline; }
    dialog#fenetre-modif {
        border: 0; border-radius: 14px; padding: 24px; width: min(620px, 94vw);
        box-shadow: 0 12px 40px rgba(0, 0, 0, .28);
    }
    dialog#fenetre-modif::backdrop { background: rgba(30, 20, 60, .45); }
</style>
@endpush

@section('contenu')
    <h1>Utilisateurs</h1>

    @if ($errors->any())
        <div class="message message-erreur">
            @foreach ($errors->all() as $erreur)
                <div>{{ $erreur }}</div>
            @endforeach
        </div>
    @endif

    {{-- ---------- Ajout ---------- --}}
    <div class="carte" style="padding: 20px; margin-bottom: 22px;">
        <h2 style="margin-top: 0;">Nouvel utilisateur</h2>

        <form method="POST" action="/utilisateurs">
            @csrf
            <div class="form-grille">
                <div>
                    <label>Nom</label>
                    <input name="nomUtilisateur" value="{{ old('nomUtilisateur') }}" required>
                </div>
                <div>
                    <label>Prénom</label>
                    <input name="prenomUtilisateur" value="{{ old('prenomUtilisateur') }}" required>
                </div>
                <div>
                    <label>Identifiant de connexion</label>
                    <input name="login" value="{{ old('login') }}" required>
                </div>
                <div>
                    <label>Rôle</label>
                    <select name="role" id="a-role" required onchange="majObligatoire('a-role', 'a-ens')">
                        <option value="">-- Choisir --</option>
                        @foreach ($roles as $r)
                            <option value="{{ $r }}" @selected(old('role') === $r)>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Mot de passe</label>
                    <input type="password" name="motDePasse" required>
                    <div class="form-aide">6 caractères minimum.</div>
                </div>
                <div>
                    <label>Confirmer le mot de passe</label>
                    <input type="password" name="motDePasse_confirmation" required>
                </div>
                <div>
                    <label>Enseignant lié</label>
                    <select name="Enseignant_idEnseignant" id="a-ens">
                        <option value="">-- Aucun --</option>
                        @foreach ($enseignants as $e)
                            <option value="{{ $e->idEnseignant }}"
                                    @selected((string) old('Enseignant_idEnseignant') === (string) $e->idEnseignant)>
                                {{ strtoupper($e->nomEnseignant) }} {{ $e->prenomEnseignant }} ({{ $e->matriculeEnseignant }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-aide">Obligatoire pour le rôle Enseignant.</div>
                </div>
            </div>
            <div class="ligne-boutons">
                <button type="submit" class="btn btn-ajout">Créer l'utilisateur</button>
            </div>
        </form>
    </div>

    {{-- ---------- Liste ---------- --}}
    <div class="carte" style="padding: 18px;">
        <table id="tableUtilisateurs" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Nom complet</th>
                    <th>Identifiant</th>
                    <th>Rôle</th>
                    <th>Enseignant lié</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($utilisateurs as $u)
                    <tr>
                        <td data-order="{{ $u->nomUtilisateur }} {{ $u->prenomUtilisateur }}">
                            <div class="identite">
                                <span class="avatar">{{ mb_strtoupper(mb_substr($u->prenomUtilisateur, 0, 1) . mb_substr($u->nomUtilisateur, 0, 1)) }}</span>
                                <div>
                                    <strong>{{ $u->nomUtilisateur }}</strong><br>
                                    {{ $u->prenomUtilisateur }}
                                </div>
                            </div>
                        </td>
                        <td>{{ $u->login }}</td>
                        <td>
                            @foreach ($u->roles as $role)
                                <span class="badge badge-role">{{ $role->name }}</span>
                            @endforeach
                        </td>
                        <td>
                            @if ($u->enseignant)
                                {{ strtoupper($u->enseignant->nomEnseignant) }} {{ $u->enseignant->prenomEnseignant }}
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $u->actif ? 'badge-actif' : 'badge-inactif' }}">
                                {{ $u->actif ? 'Actif' : 'Désactivé' }}
                            </span>
                        </td>
                        <td class="actions">
                            <button type="button" class="btn btn-modifier"
                                    data-id="{{ $u->idUtilisateur }}"
                                    data-nom="{{ $u->nomUtilisateur }}"
                                    data-prenom="{{ $u->prenomUtilisateur }}"
                                    data-login="{{ $u->login }}"
                                    data-role="{{ $u->roles->first()->name ?? '' }}"
                                    data-enseignant="{{ $u->Enseignant_idEnseignant }}"
                                    onclick="ouvrirModification(this)">Modifier</button>

                            <form method="POST" action="/utilisateurs/{{ $u->idUtilisateur }}/basculer">
                                @csrf
                                <button type="submit" class="btn">{{ $u->actif ? 'Désactiver' : 'Activer' }}</button>
                            </form>

                            <form method="POST" action="/utilisateurs/{{ $u->idUtilisateur }}"
                                  data-message="Supprimer définitivement {{ $u->nom_complet }} ?"
                                  onsubmit="return confirm(this.dataset.message)">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-supprimer">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ---------- Fenêtre de modification ---------- --}}
    <dialog id="fenetre-modif">
        <h2 style="margin-top: 0;">Modifier l'utilisateur</h2>

        <form method="POST" id="formulaire-modif">
            @csrf
            @method('PUT')

            <div class="form-grille">
                <div>
                    <label>Nom</label>
                    <input name="nomUtilisateur" id="m-nom" required>
                </div>
                <div>
                    <label>Prénom</label>
                    <input name="prenomUtilisateur" id="m-prenom" required>
                </div>
                <div>
                    <label>Identifiant</label>
                    <input name="login" id="m-login" required>
                </div>
                <div>
                    <label>Rôle</label>
                    <select name="role" id="m-role" required onchange="majObligatoire('m-role', 'm-ens')">
                        @foreach ($roles as $r)
                            <option value="{{ $r }}">{{ $r }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Nouveau mot de passe</label>
                    <input type="password" name="motDePasse">
                    <div class="form-aide">Laisser vide pour ne pas le changer.</div>
                </div>
                <div>
                    <label>Confirmer le nouveau mot de passe</label>
                    <input type="password" name="motDePasse_confirmation">
                </div>
                <div>
                    <label>Enseignant lié</label>
                    <select name="Enseignant_idEnseignant" id="m-ens">
                        <option value="">-- Aucun --</option>
                        @foreach ($enseignants as $e)
                            <option value="{{ $e->idEnseignant }}">
                                {{ strtoupper($e->nomEnseignant) }} {{ $e->prenomEnseignant }} ({{ $e->matriculeEnseignant }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-aide">Obligatoire pour le rôle Enseignant.</div>
                </div>
            </div>

            <div class="ligne-boutons">
                <button type="button" class="btn" onclick="document.getElementById('fenetre-modif').close()">Annuler</button>
                <button type="submit" class="btn btn-ajout">Enregistrer</button>
            </div>
        </form>
    </dialog>

    @push('scripts')
    <script>
        // L'enseignant lié devient obligatoire quand le rôle est "Enseignant"
        function majObligatoire(idRole, idEnseignant) {
            var role = document.getElementById(idRole).value;
            document.getElementById(idEnseignant).required = (role === 'Enseignant');
        }

        function ouvrirModification(bouton) {
            var d = bouton.dataset;
            document.getElementById('formulaire-modif').action = '/utilisateurs/' + d.id;
            document.getElementById('m-nom').value = d.nom;
            document.getElementById('m-prenom').value = d.prenom;
            document.getElementById('m-login').value = d.login;
            document.getElementById('m-role').value = d.role;
            document.getElementById('m-ens').value = d.enseignant || '';
            majObligatoire('m-role', 'm-ens');
            document.getElementById('fenetre-modif').showModal();
        }

        $(document).ready(function () {
            majObligatoire('a-role', 'a-ens');

            // Tableau interactif (recherche, tri, pagination)
            $('#tableUtilisateurs').DataTable({
                pageLength: 10,
                lengthMenu: [10, 25, 50],
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: false, targets: 5 }
                ],
                language: {
                    search: 'Rechercher :',
                    lengthMenu: 'Afficher _MENU_ utilisateurs',
                    info: 'Utilisateurs _START_ à _END_ sur _TOTAL_',
                    infoEmpty: 'Aucun utilisateur',
                    infoFiltered: '(filtré sur _MAX_ au total)',
                    zeroRecords: 'Aucun utilisateur trouvé',
                    emptyTable: 'Aucun utilisateur enregistré',
                    paginate: { first: 'Début', previous: 'Précédent', next: 'Suivant', last: 'Fin' }
                }
            });
        });
    </script>
    @endpush
@endsection