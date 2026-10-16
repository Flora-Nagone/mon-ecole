@extends('layouts.app')

@section('titre', 'Niveaux')

@section('contenu')
    <h1>Niveaux ({{ $niveaux->count() }})</h1>

    <div class="carte-formulaire" style="max-width: 700px; margin-bottom: 26px;">
        <h2>{{ $edition ? 'Modifier le niveau' : 'Nouveau niveau' }}</h2>

        <form method="POST" action="{{ $edition ? '/niveaux/' . $edition->idNiveau : '/niveaux' }}">
            @csrf
            @if($edition) @method('PUT') @endif

            <div class="grille-champs">
                <div class="champ">
                    <label for="libelleNiveau">Libellé du niveau</label>
                    <input id="libelleNiveau" name="libelleNiveau"
                           value="{{ old('libelleNiveau', $edition->libelleNiveau ?? '') }}"
                           class="@error('libelleNiveau') invalide @enderror" required>
                    @error('libelleNiveau') <div class="erreur">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="actions-formulaire">
                <button type="submit" class="btn btn-principal">{{ $edition ? 'Enregistrer' : 'Ajouter' }}</button>
                @if($edition) <a href="/niveaux" class="btn btn-secondaire">Annuler</a> @endif
            </div>
        </form>
    </div>

    <div class="carte" style="padding: 18px;">
        <table id="tableNiveaux" class="display" style="width:100%">
            <thead>
                <tr><th>N°</th><th>Niveau</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @foreach($niveaux as $n)
                    <tr>
                        <td>{{ $n->idNiveau }}</td>
                        <td><strong>{{ $n->libelleNiveau }}</strong></td>
                        <td class="actions">
                            <a href="/niveaux?edit={{ $n->idNiveau }}" class="btn btn-modifier">Modifier</a>
                            <form action="/niveaux/{{ $n->idNiveau }}" method="POST" style="display:inline"
                                  onsubmit="return confirm('Supprimer ce niveau ?')">
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
            $('#tableNiveaux').DataTable({
                pageLength: 10,
                order: [[0, 'asc']],
                columnDefs: [{ orderable: false, targets: 2 }],
                language: {
                    search: 'Rechercher :',
                    lengthMenu: 'Afficher _MENU_ niveaux',
                    info: 'Niveaux _START_ à _END_ sur _TOTAL_',
                    infoEmpty: 'Aucun niveau',
                    infoFiltered: '(filtré sur _MAX_ au total)',
                    zeroRecords: 'Aucun niveau trouvé',
                    emptyTable: 'Aucun niveau enregistré',
                    paginate: { first: 'Début', previous: 'Précédent', next: 'Suivant', last: 'Fin' }
                }
            });
        });
    </script>
    @endpush
@endsection