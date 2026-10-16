@extends('layouts.app')

@section('titre', 'Feuilles de notes')

@section('contenu')
    <h1>Feuilles de notes {{ $annee->libelleAnnee }}</h1>

    <div class="montant-auto" style="margin-bottom: 22px;">
        Choisissez une classe et une matière : toutes ses évaluations, sur toute l'année, sont réunies sur une seule feuille.
    </div>

    <div class="carte" style="padding: 18px;">
        <table id="tableNotes" class="display" style="width:100%">
            <thead>
                <tr><th>Classe</th><th>Matière</th><th>Enseignant</th><th>Évaluations</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @foreach($enseignements as $e)
                    <tr>
                        <td><strong>{{ $e->classe->libelleClasse }}</strong></td>
                        <td>{{ $e->matiere->libelleMatiere }}</td>
                        <td>{{ mb_strtoupper($e->enseignant->nomEnseignant) }} {{ $e->enseignant->prenomEnseignant }}</td>
                        <td><span class="badge badge-solde">{{ $nbEvals[$e->idEnseignement] ?? 0 }}</span></td>
                        <td class="actions">
                            <a href="/notes/{{ $e->idEnseignement }}" class="btn btn-principal" style="padding:7px 14px; font-size:0.8rem;">Feuille de notes</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function () {
            $('#tableNotes').DataTable({
                pageLength: 10,
                order: [[0, 'asc']],
                columnDefs: [{ orderable: false, targets: 4 }],
                language: {
                    search: 'Rechercher :', lengthMenu: 'Afficher _MENU_ lignes',
                    info: 'Lignes _START_ à _END_ sur _TOTAL_', infoEmpty: 'Aucune matière',
                    infoFiltered: '(filtré sur _MAX_ au total)', zeroRecords: 'Aucun résultat',
                    emptyTable: 'Aucun enseignement enregistré',
                    paginate: { first: 'Début', previous: 'Précédent', next: 'Suivant', last: 'Fin' }
                }
            });
        });
    </script>
    @endpush
@endsection