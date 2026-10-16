@extends('layouts.app')

@section('titre', 'Appel')

@section('contenu')
    <h1>Appel {{ $annee->libelleAnnee }}</h1>

    <div class="montant-auto" style="margin-bottom: 22px;">
        Choisissez une séance pour faire l'appel. Seuls les élèves <strong>réellement inscrits</strong> de la classe sont listés.
    </div>

    <div class="carte" style="padding: 18px;">
        <table id="tableAppels" class="display" style="width:100%">
            <thead>
                <tr><th>Date</th><th>Horaire</th><th>Classe</th><th>Matière</th><th>Enseignant</th><th>Absents</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @foreach($seances as $s)
                    @php $n = $absents[$s->idSeance] ?? 0; @endphp
                    <tr>
                        <td>{{ $s->dateSeance }}</td>
                        <td>{{ substr($s->heureDebutSeance, 0, 5) }} - {{ substr($s->heureFinSeance, 0, 5) }}</td>
                        <td><strong>{{ $s->enseignement->classe->libelleClasse }}</strong></td>
                        <td>{{ $s->enseignement->matiere->libelleMatiere }}</td>
                        <td>{{ $s->enseignement->enseignant->nomEnseignant }} {{ $s->enseignement->enseignant->prenomEnseignant }}</td>
                        <td>
                            @if($n > 0) <span class="badge badge-attente">{{ $n }}</span>
                            @else <span class="badge badge-inscrit">0</span> @endif
                        </td>
                        <td class="actions">
                            @if($s->dateSeance > now()->toDateString())
                                <span class="badge badge-solde">À venir</span>
                            @else
                                <a href="/appels/{{ $s->idSeance }}" class="btn btn-principal" style="padding:7px 14px; font-size:0.8rem;">Faire l'appel</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function () {
            $('#tableAppels').DataTable({
                pageLength: 10,
                order: [[0, 'desc']],
                columnDefs: [{ orderable: false, targets: 6 }],
                language: {
                    search: 'Rechercher :', lengthMenu: 'Afficher _MENU_ séances',
                    info: 'Séances _START_ à _END_ sur _TOTAL_', infoEmpty: 'Aucune séance',
                    infoFiltered: '(filtré sur _MAX_ au total)', zeroRecords: 'Aucune séance trouvée',
                    emptyTable: 'Aucune séance programmée',
                    paginate: { first: 'Début', previous: 'Précédent', next: 'Suivant', last: 'Fin' }
                }
            });
        });
    </script>
    @endpush
@endsection