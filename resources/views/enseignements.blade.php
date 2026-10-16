@extends('layouts.app')

@section('titre', 'Enseignements')

@section('contenu')
    <h1>Enseignements {{ $annee->libelleAnnee }}</h1>

    <div class="carte-formulaire" style="max-width: 900px; margin-bottom: 26px;">
        <div class="montant-auto" style="margin-bottom: 20px;">
            Un enseignant n'enseigne que dans son domaine : la liste des enseignants se filtre selon la matière choisie.
        </div>

        <form method="POST" action="/enseignements">
            @csrf
            <div class="grille-champs">
                <div class="champ">
                    <label for="classe">Classe</label>
                    <select id="classe" name="Classe_idClasse" required>
                        <option value="">-- Choisir --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->idClasse }}" {{ old('Classe_idClasse') == $c->idClasse ? 'selected' : '' }}>{{ $c->libelleClasse }}</option>
                        @endforeach
                    </select>
                    @error('Classe_idClasse') <div class="erreur">{{ $message }}</div> @enderror
                </div>
                <div class="champ">
                    <label for="matiere">Matière</label>
                    <select id="matiere" name="Matiere_idMatiere" required>
                        <option value="">-- Choisir --</option>
                        @foreach($matieres as $m)
                            <option value="{{ $m->idMatiere }}" data-domaine="{{ $m->Domaine_idDomaine }}"
                                {{ old('Matiere_idMatiere') == $m->idMatiere ? 'selected' : '' }}>{{ $m->libelleMatiere }}</option>
                        @endforeach
                    </select>
                    @error('Matiere_idMatiere') <div class="erreur">{{ $message }}</div> @enderror
                </div>
                <div class="champ">
                    <label for="enseignant">Enseignant</label>
                    <select id="enseignant" name="Enseignant_idEnseignant" required>
                        <option value="">-- Choisir --</option>
                        @foreach($enseignants as $e)
                            <option value="{{ $e->idEnseignant }}" data-domaine="{{ $e->Domaine_idDomaine }}"
                                {{ old('Enseignant_idEnseignant') == $e->idEnseignant ? 'selected' : '' }}>{{ $e->nomEnseignant }} {{ $e->prenomEnseignant }}</option>
                        @endforeach
                    </select>
                    @error('Enseignant_idEnseignant') <div class="erreur">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="actions-formulaire">
                <button type="submit" class="btn btn-principal">Affecter</button>
            </div>
        </form>
    </div>

    <div class="carte" style="padding: 18px;">
        <table id="tableEnseignements" class="display" style="width:100%">
            <thead>
                <tr><th>Classe</th><th>Matière</th><th>Enseignant</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @foreach($enseignements as $e)
                    <tr>
                        <td><strong>{{ $e->classe->libelleClasse }}</strong></td>
                        <td>{{ $e->matiere->libelleMatiere }}</td>
                        <td>{{ $e->enseignant->nomEnseignant }} {{ $e->enseignant->prenomEnseignant }}</td>
                        <td class="actions">
                            <form action="/enseignements/{{ $e->idEnseignement }}" method="POST" style="display:inline"
                                  onsubmit="return confirm('Supprimer cette affectation ?')">
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
            $('#tableEnseignements').DataTable({
                pageLength: 10,
                order: [[0, 'asc']],
                columnDefs: [{ orderable: false, targets: 3 }],
                language: {
                    search: 'Rechercher :', lengthMenu: 'Afficher _MENU_ lignes',
                    info: 'Lignes _START_ à _END_ sur _TOTAL_', infoEmpty: 'Aucun enseignement',
                    infoFiltered: '(filtré sur _MAX_ au total)', zeroRecords: 'Aucun résultat',
                    emptyTable: 'Aucun enseignement enregistré',
                    paginate: { first: 'Début', previous: 'Précédent', next: 'Suivant', last: 'Fin' }
                }
            });
        });

        // Filtre : seuls les enseignants du domaine de la matière sont proposés
        var matiere = document.getElementById('matiere');
        var enseignant = document.getElementById('enseignant');

        function filtrer() {
            var domaine = matiere.options[matiere.selectedIndex].dataset.domaine;
            Array.from(enseignant.options).forEach(function (o) {
                if (!o.value) { return; }
                var ok = !domaine || o.dataset.domaine === domaine;
                o.hidden = !ok;
                o.disabled = !ok;
            });
            var choisi = enseignant.options[enseignant.selectedIndex];
            if (choisi && choisi.disabled) { enseignant.value = ''; }
        }

        matiere.addEventListener('change', filtrer);
        filtrer();
    </script>
    @endpush
@endsection