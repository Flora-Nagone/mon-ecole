@extends('layouts.app')

@section('titre', 'Rôles et permissions')

@push('styles')
<style>
    .matrice { width: 100%; border-collapse: collapse; }
    .matrice th, .matrice td { padding: 11px 10px; border-bottom: 1px solid #ececf3; text-align: left; }
    .matrice th.col-role, .matrice td.case { text-align: center; width: 150px; }
    .matrice tr.ligne-groupe td { background: #f5f3ff; font-weight: 700; letter-spacing: .02em; }
    .matrice input[type="checkbox"] { width: 19px; height: 19px; cursor: pointer; }
    .matrice input[type="checkbox"][disabled] { cursor: not-allowed; opacity: .6; }
    .matrice .nb-perm { display: block; font-weight: 400; font-size: .78rem; opacity: .75; }
    .aide-roles { margin: 0 0 18px; color: #4b5563; line-height: 1.6; }
    .barre-enregistrer { margin-top: 18px; display: flex; justify-content: flex-end; }
    .form-role { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; }
    .form-role label { display: block; font-size: .85rem; font-weight: 600; margin-bottom: 5px; }
    .form-role input { padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: .95rem; font-family: inherit; min-width: 260px; }
    .liste-roles { width: 100%; border-collapse: collapse; margin-top: 6px; }
    .liste-roles th, .liste-roles td { padding: 10px; border-bottom: 1px solid #ececf3; text-align: left; }
    .badge-systeme { background: #ede9fe; color: #5b21b6; }
    .badge-perso { background: #dcfce7; color: #166534; }
    .badge-usage { background: #f3f4f6; color: #374151; }
    .actions form { display: inline; }
    dialog#fenetre-role {
        border: 0; border-radius: 14px; padding: 24px; width: min(640px, 94vw);
        max-height: 90vh; overflow-y: auto; box-shadow: 0 12px 40px rgba(0, 0, 0, .28);
    }
    dialog#fenetre-role::backdrop { background: rgba(30, 20, 60, .45); }
    #fenetre-role input[type="text"] { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: .95rem; font-family: inherit; }
    #fenetre-role .groupe-titre { margin: 16px 0 6px; font-weight: 700; color: #5b21b6; }
    #fenetre-role .perm-ligne { display: flex; gap: 8px; align-items: center; padding: 4px 0; }
    #fenetre-role .ligne-boutons { margin-top: 18px; display: flex; gap: 8px; justify-content: flex-end; }
</style>
@endpush

@section('contenu')
    <h1>Rôles et permissions</h1>

    @if ($errors->any())
        <div class="message message-erreur">
            @foreach ($errors->all() as $erreur)
                <div>{{ $erreur }}</div>
            @endforeach
        </div>
    @endif

    {{-- ---------- Création d'un rôle ---------- --}}
    <div class="carte" style="padding: 20px; margin-bottom: 22px;">
        <h2 style="margin-top: 0;">Nouveau rôle</h2>
        <form method="POST" action="/roles" class="form-role">
            @csrf
            <div>
                <label>Nom du rôle</label>
                <input name="name" value="{{ old('name') }}" placeholder="Ex. : Comptable, Directeur des études" required maxlength="50">
            </div>
            <button type="submit" class="btn btn-ajout">Créer le rôle</button>
        </form>
    </div>

    {{-- ---------- Liste des rôles ---------- --}}
    <div class="carte" style="padding: 20px; margin-bottom: 22px;">
        <h2 style="margin-top: 0;">Liste des rôles</h2>
        <table id="tableRoles" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Rôle</th>
                    <th>Type</th>
                    <th>Autorisations</th>
                    <th>Utilisateurs</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $i => $role)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><strong>{{ $role->name }}</strong></td>
                        <td>
                            @if (in_array($role->name, $systeme, true))
                                <span class="badge badge-systeme">De base</span>
                            @else
                                <span class="badge badge-perso">Personnalisé</span>
                            @endif
                        </td>
                        <td data-order="{{ $role->name === 'Administrateur' ? 999 : $role->permissions->count() }}">
                            <span class="badge badge-usage">
                                {{ $role->name === 'Administrateur' ? 'Toutes' : $role->permissions->count() . ' autorisation(s)' }}
                            </span>
                        </td>
                        <td>{{ $nbUtilisateurs[$role->id] ?? 0 }}</td>
                        <td class="actions">
                            @if ($role->name === 'Administrateur')
                                <span title="Tous les droits, non modifiable">🔒</span>
                            @else
                                <button type="button" class="btn btn-modifier"
                                        data-id="{{ $role->id }}"
                                        data-nom="{{ $role->name }}"
                                        data-systeme="{{ in_array($role->name, $systeme, true) ? '1' : '0' }}"
                                        data-perms='@json($role->permissions->pluck("name"))'
                                        onclick="ouvrirRole(this)">Modifier</button>
                            @endif

                            @if (! in_array($role->name, $systeme, true))
                                <form method="POST" action="/roles/{{ $role->id }}"
                                      data-message="Supprimer le rôle {{ $role->name }} ?"
                                      onsubmit="return confirm(this.dataset.message)">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-supprimer">Supprimer</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ---------- Matrice des permissions ---------- --}}
    <p class="aide-roles">
        Cochez ce que chaque rôle a le droit de faire. Les changements s'appliquent dès l'enregistrement,
        à la prochaine page ouverte par chaque utilisateur. L'Administrateur a toujours tous les droits
        et ne peut pas être modifié.
    </p>

    <form method="POST" action="/roles">
        @csrf
        @method('PUT')

        <div class="carte" style="padding: 18px; overflow-x: auto;">
            <table class="matrice">
                <thead>
                    <tr>
                        <th>Permission</th>
                        @foreach ($roles as $role)
                            <th class="col-role">
                                {{ $role->name }}
                                <span class="nb-perm">
                                    {{ $role->name === 'Administrateur' ? 'Tous les droits' : $role->permissions->count() . ' permission(s)' }}
                                </span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($groupes as $titre => $permissions)
                        <tr class="ligne-groupe">
                            <td colspan="{{ $roles->count() + 1 }}">{{ $titre }}</td>
                        </tr>

                        @foreach ($permissions as $nom => $libelle)
                            <tr>
                                <td>{{ $libelle }}</td>

                                @foreach ($roles as $role)
                                    <td class="case">
                                        @if ($role->name === 'Administrateur')
                                            <input type="checkbox" checked disabled title="L'Administrateur a tous les droits">
                                        @else
                                            <input type="checkbox"
                                                   name="permissions[{{ $role->id }}][]"
                                                   value="{{ $nom }}"
                                                   @checked($role->permissions->contains('name', $nom))>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="barre-enregistrer">
            <button type="submit" class="btn btn-ajout">Enregistrer les permissions</button>
        </div>
    </form>

    {{-- ---------- Fenêtre de modification d'un rôle ---------- --}}
    <dialog id="fenetre-role">
        <h2 style="margin-top: 0;">Modifier le rôle</h2>
        <form method="POST" id="formulaire-role">
            @csrf
            @method('PUT')

            <label style="display:block; font-size:.85rem; font-weight:600; margin-bottom:5px;">Nom du rôle</label>
            <input type="text" name="name" id="r-nom" maxlength="50">
            <div id="r-aide" style="font-size:.78rem; color:#6b7280; margin-top:4px;"></div>

            @foreach ($groupes as $titre => $permissions)
                <div class="groupe-titre">{{ $titre }}</div>
                @foreach ($permissions as $nom => $libelle)
                    <label class="perm-ligne">
                        <input type="checkbox" name="permissions[]" value="{{ $nom }}" class="r-perm">
                        <span>{{ $libelle }}</span>
                    </label>
                @endforeach
            @endforeach

            <div class="ligne-boutons">
                <button type="button" class="btn" onclick="document.getElementById('fenetre-role').close()">Annuler</button>
                <button type="submit" class="btn btn-ajout">Enregistrer</button>
            </div>
        </form>
    </dialog>

    @push('scripts')
    <script>
        function ouvrirRole(bouton) {
            var d = bouton.dataset;
            var perms = JSON.parse(d.perms || '[]');
            var champ = document.getElementById('r-nom');

            document.getElementById('formulaire-role').action = '/roles/' + d.id;
            champ.value = d.nom;
            champ.disabled = (d.systeme === '1');   // un rôle de base ne se renomme pas
            document.getElementById('r-aide').textContent =
                d.systeme === '1' ? 'Rôle de base : le nom ne peut pas être modifié.' : '';

            document.querySelectorAll('.r-perm').forEach(function (c) {
                c.checked = perms.indexOf(c.value) !== -1;
            });
            document.getElementById('fenetre-role').showModal();
        }

        $(document).ready(function () {
            $('#tableRoles').DataTable({
                pageLength: 10,
                lengthMenu: [10, 25, 50],
                order: [[0, 'asc']],
                columnDefs: [{ orderable: false, targets: 5 }],
                language: {
                    search: 'Rechercher :',
                    lengthMenu: 'Afficher _MENU_ lignes',
                    info: 'Rôles _START_ à _END_ sur _TOTAL_',
                    infoEmpty: 'Aucun rôle',
                    infoFiltered: '(filtré sur _MAX_ au total)',
                    zeroRecords: 'Aucun rôle trouvé',
                    paginate: { first: 'Début', previous: 'Précédent', next: 'Suivant', last: 'Fin' }
                }
            });
        });
    </script>
    @endpush
@endsection