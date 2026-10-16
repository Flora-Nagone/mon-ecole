@extends('layouts.app')

@section('titre', 'Salles')

@section('contenu')
    <h1>Salles ({{ $salles->count() }})</h1>

    <div class="carte-formulaire" style="max-width: 700px; margin-bottom: 26px;">
        <h2>{{ $edition ? 'Modifier la salle' : 'Nouvelle salle' }}</h2>

        <form method="POST" action="{{ $edition ? '/salles/' . $edition->idSalle : '/salles' }}">
            @csrf
            @if($edition) @method('PUT') @endif

            <div class="grille-champs">
                <div class="champ">
                    <label for="libelleSalle">Nom de la salle</label>
                    <input id="libelleSalle" name="libelleSalle"
                           value="{{ old('libelleSalle', $edition->libelleSalle ?? '') }}"
                           class="@error('libelleSalle') invalide @enderror" required>
                    @error('libelleSalle') <div class="erreur">{{ $message }}</div> @enderror
                </div>

                <div class="champ">
                    <label for="capacite">Capacité <small>(facultatif)</small></label>
                    <input id="capacite" type="number" min="1" max="500" name="capacite"
                           value="{{ old('capacite', $edition->capacite ?? '') }}"
                           class="@error('capacite') invalide @enderror">
                    @error('capacite') <div class="erreur">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="actions-formulaire">
                <button type="submit" class="btn btn-principal">{{ $edition ? 'Enregistrer' : 'Ajouter' }}</button>
                @if($edition) <a href="/salles" class="btn btn-secondaire">Annuler</a> @endif
            </div>
        </form>
    </div>

    <div class="carte" style="padding: 18px;">
        <table id="tableSalles" class="display" style="width:100%">
            <thead>
                <tr><th>Salle</th><th>Capacité</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @foreach($salles as $s)
                    <tr>
                        <td><strong>{{ $s->libelleSalle }}</strong></td>
                        <td>{{ $s->capacite ? $s->capacite . ' places' : '—' }}</td>
                        <td class="actions">
                            <a href="/salles?edit={{ $s->idSalle }}" class="btn btn-modifier">Modifier</a>
                            <form action="/salles/{{ $s->idSalle }}" method="POST" style="display:inline"
                                  onsubmit="return confirm('Supprimer cette salle ?')">
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
            $('#tableSalles').DataTable({
                pageLength: 10,
                order: [[0, 'asc']],
                columnDefs: [{ orderable: false, targets: 2 }],
                language: {
                    search: 'Rechercher :',
                    lengthMenu: 'Afficher _MENU_ salles',
                    info: 'Salles _START_ à _END_ sur _TOTAL_',
                    infoEmpty: 'Aucune salle',
                    infoFiltered: '(filtré sur _MAX_ au total)',
                    zeroRecords: 'Aucune salle trouvée',
                    emptyTable: 'Aucune salle enregistrée',
                    paginate: { first: 'Début', previous: 'Précédent', next: 'Suivant', last: 'Fin' }
                }
            });
        });
    </script>
    @endpush
@endsection