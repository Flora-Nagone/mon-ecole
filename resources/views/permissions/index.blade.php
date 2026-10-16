@extends('layouts.app')

@section('titre', 'Permissions')

@push('styles')
<style>
    .form-perm { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; }
    .form-perm label { display: block; font-size: .85rem; font-weight: 600; margin-bottom: 5px; }
    .form-perm input, #fenetre-perm input {
        padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;
        font-size: .95rem; font-family: inherit; min-width: 280px;
    }
    .form-aide { font-size: .78rem; color: #6b7280; margin-top: 4px; }
    .perm-nom strong { display: block; }
    .perm-nom small { color: #6b7280; }
    .badge-groupe { background: #ede9fe; color: #5b21b6; }
    .badge-systeme { background: #e5e7eb; color: #4b5563; }
    .badge-perso { background: #dcfce7; color: #166534; }
    .badge-usage { background: #f3f4f6; color: #374151; }
    .actions form { display: inline; }
    dialog#fenetre-perm {
        border: 0; border-radius: 14px; padding: 24px; width: min(480px, 94vw);
        box-shadow: 0 12px 40px rgba(0, 0, 0, .28);
    }
    dialog#fenetre-perm::backdrop { background: rgba(30, 20, 60, .45); }
    .ligne-boutons { margin-top: 16px; display: flex; gap: 8px; justify-content: flex-end; }
</style>
@endpush

@section('contenu')
    <h1>{{ $permissions->count() }} permission(s)</h1>
    <p style="color:#4b5563; margin-top:-6px;">Gérez les permissions disponibles pour les rôles. L'attribution aux rôles se fait dans « Rôles et permissions ».</p>

    @if ($errors->any())
        <div class="message message-erreur">
            @foreach ($errors->all() as $erreur)
                <div>{{ $erreur }}</div>
            @endforeach
        </div>
    @endif

    {{-- ---------- Nouvelle permission ---------- --}}
    <div class="carte" style="padding: 20px; margin-bottom: 22px;">
        <h2 style="margin-top: 0;">Nouvelle permission</h2>
        <form method="POST" action="/permissions" class="form-perm">
            @csrf
            <div>
                <label>Nom technique</label>
                <input name="name" value="{{ old('name') }}" placeholder="ex. paiements.gerer" required maxlength="80">
                <div class="form-aide">Format : domaine.action, en minuscules (ex. bibliotheque.voir).</div>
            </div>
            <button type="submit" class="btn btn-ajout">+ Nouvelle permission</button>
        </form>
    </div>

    {{-- ---------- Liste ---------- --}}
    <div class="carte" style="padding: 18px;">
        <table id="tablePermissions" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Permission</th>
                    <th>Groupe</th>
                    <th>Type</th>
                    <th>Utilisation</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($permissions as $i => $p)
                    @php $estSysteme = isset($systeme[$p->name]); @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="perm-nom">
                            <strong>{{ $p->name }}</strong>
                            <small>{{ $estSysteme ? $systeme[$p->name][1] : 'Permission personnalisée' }}</small>
                        </td>
                        <td><span class="badge badge-groupe">{{ $estSysteme ? $systeme[$p->name][0] : 'Autres' }}</span></td>
                        <td>
                            <span class="badge {{ $estSysteme ? 'badge-systeme' : 'badge-perso' }}">
                                {{ $estSysteme ? 'De base' : 'Personnalisée' }}
                            </span>
                        </td>
                        <td data-order="{{ $nbRoles[$p->id] ?? 0 }}">
                            <span class="badge badge-usage">{{ $nbRoles[$p->id] ?? 0 }} rôle(s)</span>
                        </td>
                        <td class="actions">
                            @if ($estSysteme)
                                <span title="Utilisée par le code de l'application">🔒</span>
                            @else
                                <button type="button" class="btn btn-modifier"
                                        data-id="{{ $p->id }}" data-nom="{{ $p->name }}"
                                        onclick="ouvrirPerm(this)">Modifier</button>
                                <form method="POST" action="/permissions/{{ $p->id }}"
                                      data-message="Supprimer la permission {{ $p->name }} ?"
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

    {{-- ---------- Fenêtre de modification ---------- --}}
    <dialog id="fenetre-perm">
        <h2 style="margin-top: 0;">Modifier la permission</h2>
        <form method="POST" id="formulaire-perm">
            @csrf
            @method('PUT')
            <label style="display:block; font-size:.85rem; font-weight:600; margin-bottom:5px;">Nom technique</label>
            <input name="name" id="p-nom" required maxlength="80" style="width:100%;">
            <div class="ligne-boutons">
                <button type="button" class="btn" onclick="document.getElementById('fenetre-perm').close()">Annuler</button>
                <button type="submit" class="btn btn-ajout">Enregistrer</button>
            </div>
        </form>
    </dialog>

    @push('scripts')
    <script>
        function ouvrirPerm(bouton) {
            document.getElementById('formulaire-perm').action = '/permissions/' + bouton.dataset.id;
            document.getElementById('p-nom').value = bouton.dataset.nom;
            document.getElementById('fenetre-perm').showModal();
        }

        $(document).ready(function () {
            $('#tablePermissions').DataTable({
                pageLength: 10,
                lengthMenu: [10, 25, 50],
                order: [[1, 'asc']],
                columnDefs: [{ orderable: false, targets: 5 }],
                language: {
                    search: 'Rechercher :',
                    lengthMenu: 'Afficher _MENU_ lignes',
                    info: 'Permissions _START_ à _END_ sur _TOTAL_',
                    infoEmpty: 'Aucune permission',
                    infoFiltered: '(filtré sur _MAX_ au total)',
                    zeroRecords: 'Aucune permission trouvée',
                    paginate: { first: 'Début', previous: 'Précédent', next: 'Suivant', last: 'Fin' }
                }
            });
        });
    </script>
    @endpush
@endsection