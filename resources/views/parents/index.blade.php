@extends('layouts.app')

@section('titre', 'Parents')

@section('contenu')
    <h1>Parents ({{ $parents->count() }})</h1>

    <div class="barre-outils">
        <span></span>
        <a href="/parents/create" class="btn btn-ajout">+ Nouveau parent</a>
    </div>

    <div class="carte" style="padding: 18px;">
        <table id="tableParents" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Parent</th>
                    <th>Téléphone</th>
                    <th>Profession</th>
                    <th>Enfants</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($parents as $p)
                    <tr>
                        <td><strong>{{ $p->nomParent }}</strong> {{ $p->prenomParent }}</td>
                        <td>{{ $p->telephoneParent }}</td>
                        <td>{{ $p->professionParent }}</td>
                        <td>
                            @foreach($p->eleves as $e)
                                <div>{{ $e->nomEleve }} {{ $e->prenomEleve }}
                                    <span class="badge badge-solde">{{ $e->pivot->lienParente }}</span>
                                </div>
                            @endforeach
                        </td>
                        <td class="actions">
                            <a href="/parents/{{ $p->idParent }}" class="btn btn-principal" style="padding:7px 14px; font-size:0.8rem;">Fiche</a>
                            <a href="/parents/{{ $p->idParent }}/edit" class="btn btn-modifier">Modifier</a>
                            <form action="/parents/{{ $p->idParent }}" method="POST" style="display:inline"
                                  onsubmit="return confirm('Supprimer ce parent ?')">
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
            $('#tableParents').DataTable({
                pageLength: 10,
                order: [[0, 'asc']],
                columnDefs: [{ orderable: false, targets: [3, 4] }],
                language: {
                    search: 'Rechercher :',
                    lengthMenu: 'Afficher _MENU_ parents',
                    info: 'Parents _START_ à _END_ sur _TOTAL_',
                    infoEmpty: 'Aucun parent',
                    infoFiltered: '(filtré sur _MAX_ au total)',
                    zeroRecords: 'Aucun parent trouvé',
                    emptyTable: 'Aucun parent enregistré',
                    paginate: { first: 'Début', previous: 'Précédent', next: 'Suivant', last: 'Fin' }
                }
            });
        });
    </script>
    @endpush
@endsection