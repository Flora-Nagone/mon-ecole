@extends('layouts.app')

@section('titre', 'Inscriptions')

@section('contenu')
    <h1>Inscriptions {{ $annee->libelleAnnee }}</h1>

    <div class="stats">
        <div class="stat">
            <span class="stat-nombre" data-cible="{{ $inscriptions->count() }}">0</span>
            <span class="stat-label">Dossiers</span>
        </div>
        <div class="stat">
            <span class="stat-nombre" data-cible="{{ $inscriptions->filter(fn ($i) => $i->est_inscrit)->count() }}">0</span>
            <span class="stat-label">Élèves inscrits</span>
        </div>
        <div class="stat">
            <span class="stat-nombre" data-cible="{{ $inscriptions->filter(fn ($i) => !$i->est_inscrit)->count() }}">0</span>
            <span class="stat-label">En attente de versement</span>
        </div>
    </div>

    <div class="barre-outils">
        <span></span>
        <a href="/inscriptions/create" class="btn btn-ajout">+ Nouvelle inscription</a>
    </div>

    <div class="carte" style="padding: 18px;">
        <table id="tableInscriptions" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Élève</th>
                    <th>Classe</th>
                    <th>Montant dû</th>
                    <th>Versé</th>
                    <th>Reste</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($inscriptions as $i)
                    <tr>
                        <td>{{ $i->codeInscription }}</td>
                        <td>
                            <strong>{{ $i->eleve->nomEleve }}</strong> {{ $i->eleve->prenomEleve }}<br>
                            <small>{{ $i->eleve->matriculeEleve }}</small>
                        </td>
                        <td>{{ $i->classe->libelleClasse }}</td>
                        <td>{{ number_format($i->montantDu, 0, ',', ' ') }}</td>
                        <td>{{ number_format($i->total_verse, 0, ',', ' ') }}</td>
                        <td>{{ number_format($i->reste_a_payer, 0, ',', ' ') }}</td>
                        <td>
                            @if($i->est_inscrit)
                                <span class="badge badge-inscrit">Inscrit</span>
                            @else
                                <span class="badge badge-attente">En attente</span>
                            @endif
                            @if($i->est_solde)
                                <span class="badge badge-solde">Soldé</span>
                            @endif
                        </td>
                        <td class="actions">
                            <a href="/inscriptions/{{ $i->idInscription }}" class="btn btn-modifier">Ouvrir</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function () {
            $('#tableInscriptions').DataTable({
                pageLength: 10,
                order: [[0, 'desc']],
                columnDefs: [{ orderable: false, targets: 7 }],
                language: {
                    search: 'Rechercher :',
                    lengthMenu: 'Afficher _MENU_ dossiers',
                    info: 'Dossiers _START_ à _END_ sur _TOTAL_',
                    infoEmpty: 'Aucun dossier',
                    infoFiltered: '(filtré sur _MAX_ au total)',
                    zeroRecords: 'Aucun dossier trouvé',
                    emptyTable: 'Aucune inscription',
                    paginate: { first: 'Début', previous: 'Précédent', next: 'Suivant', last: 'Fin' }
                }
            });

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