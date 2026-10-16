@extends('layouts.app')

@section('titre', 'Élèves')

@section('contenu')
    <h1>Liste des élèves</h1>

    <div class="stats">
        <div class="stat">
            <span class="stat-nombre" data-cible="{{ $eleves->count() }}">0</span>
            <span class="stat-label">Élèves</span>
        </div>
        <div class="stat">
            <span class="stat-nombre" data-cible="{{ $eleves->where('sexeEleve', 'M')->count() }}">0</span>
            <span class="stat-label">Garçons</span>
        </div>
        <div class="stat">
            <span class="stat-nombre" data-cible="{{ $eleves->where('sexeEleve', 'F')->count() }}">0</span>
            <span class="stat-label">Filles</span>
        </div>
    </div>

    <div class="barre-outils">
        <span></span>
        <a href="/eleves/create" class="btn btn-ajout">+ Nouvel élève</a>
    </div>

    <div class="carte" style="padding: 18px;">
        <table id="tableEleves" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Matricule</th>
                    <th>Élève</th>
                    <th>Sexe</th>
                    <th>Naissance</th>
                    <th>Lieu</th>
                    <th>Adresse</th>
                    <th>Téléphone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($eleves as $e)
                    <tr>
                        <td>{{ $e->matriculeEleve }}</td>
                        <td data-order="{{ $e->nomEleve }} {{ $e->prenomEleve }}">
                            <div class="identite">
                                <span class="avatar">{{ mb_strtoupper(mb_substr($e->prenomEleve, 0, 1) . mb_substr($e->nomEleve, 0, 1)) }}</span>
                                <div>
                                    <strong>{{ $e->nomEleve }}</strong><br>
                                    {{ $e->prenomEleve }}
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $e->sexeEleve == 'M' ? 'badge-m' : 'badge-f' }}">
                                {{ $e->sexeEleve == 'M' ? 'Garçon' : 'Fille' }}
                            </span>
                        </td>
                        <td>{{ $e->dateNaissance }}</td>
                        <td>{{ $e->lieuNaissance }}</td>
                        <td>{{ $e->adresseEleve }}</td>
                        <td>{{ $e->telephoneEleve }}</td>
                        <td class="actions">
                            <a href="/eleves/edit/{{ $e->idEleve }}" class="btn btn-modifier">Modifier</a>
<form action="/eleves/{{ $e->idEleve }}" method="POST" style="display:inline"
      onsubmit="return confirm('Supprimer cet élève ?')">
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

    @push('scripts')
    <script>
        $(document).ready(function () {
            // Tableau interactif (recherche, tri, pagination)
            $('#tableEleves').DataTable({
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [[1, 'asc']],
                columnDefs: [
                    { orderable: false, targets: 7 }
                ],
                language: {
                    search: 'Rechercher :',
                    lengthMenu: 'Afficher _MENU_ élèves',
                    info: 'Élèves _START_ à _END_ sur _TOTAL_',
                    infoEmpty: 'Aucun élève',
                    infoFiltered: '(filtré sur _MAX_ au total)',
                    zeroRecords: 'Aucun élève trouvé',
                    emptyTable: 'Aucun élève enregistré',
                    paginate: { first: 'Début', previous: 'Précédent', next: 'Suivant', last: 'Fin' }
                }
            });

            // Compteurs animés
            document.querySelectorAll('[data-cible]').forEach(function (el) {
                var cible = parseInt(el.dataset.cible, 10);
                var courant = 0;
                var pas = Math.max(1, Math.ceil(cible / 30));
                var minuteur = setInterval(function () {
                    courant += pas;
                    if (courant >= cible) { courant = cible; clearInterval(minuteur); }
                    el.textContent = courant;
                }, 30);
            });
        });
    </script>
    @endpush
@endsection