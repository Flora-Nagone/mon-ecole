@extends('layouts.app')

@section('titre', 'Classes')

@section('contenu')
    @php
        $totInscrits = $classes->sum(fn ($c) => $c->inscriptions->filter(fn ($i) => $i->est_inscrit)->count());
        $totDossiers = $classes->sum(fn ($c) => $c->inscriptions->count());
    @endphp

    <h1>Classes {{ $annee->libelleAnnee }}</h1>

    <div class="stats">
        <div class="stat">
            <span class="stat-nombre" data-cible="{{ $classes->count() }}">0</span>
            <span class="stat-label">Classes</span>
        </div>
        <div class="stat">
            <span class="stat-nombre" data-cible="{{ $totInscrits }}">0</span>
            <span class="stat-label">Élèves inscrits</span>
        </div>
        <div class="stat">
            <span class="stat-nombre" data-cible="{{ $totDossiers - $totInscrits }}">0</span>
            <span class="stat-label">En attente de versement</span>
        </div>
    </div>

    <div class="barre-outils">
        <span></span>
        <a href="/classes/create" class="btn btn-ajout">+ Nouvelle classe</a>
    </div>

    <div class="carte" style="padding: 18px;">
        <table id="tableClasses" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Classe</th>
                    <th>Niveau</th>
                    <th>Inscrits</th>
                    <th>En attente</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($classes as $c)
                    @php
                        $ins = $c->inscriptions->filter(fn ($i) => $i->est_inscrit)->count();
                        $att = $c->inscriptions->count() - $ins;
                    @endphp
                    <tr>
                        <td>{{ $c->codeClasse }}</td>
                        <td><strong>{{ $c->libelleClasse }}</strong></td>
                        <td>{{ $c->niveau->libelleNiveau }}</td>
                        <td><span class="badge badge-inscrit">{{ $ins }}</span></td>
                        <td><span class="badge badge-attente">{{ $att }}</span></td>
                        <td class="actions">
                            <a href="/classes/{{ $c->idClasse }}" class="btn btn-principal" style="padding:7px 14px; font-size:0.8rem;">Liste</a>
                            <a href="/classes/{{ $c->idClasse }}/edit" class="btn btn-modifier">Modifier</a>
                            <form action="/classes/{{ $c->idClasse }}" method="POST" style="display:inline"
                                  onsubmit="return confirm('Supprimer cette classe ?')">
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
            $('#tableClasses').DataTable({
                pageLength: 10,
                order: [[0, 'asc']],
                columnDefs: [{ orderable: false, targets: 5 }],
                language: {
                    search: 'Rechercher :',
                    lengthMenu: 'Afficher _MENU_ classes',
                    info: 'Classes _START_ à _END_ sur _TOTAL_',
                    infoEmpty: 'Aucune classe',
                    infoFiltered: '(filtré sur _MAX_ au total)',
                    zeroRecords: 'Aucune classe trouvée',
                    emptyTable: 'Aucune classe cette année',
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