@extends('layouts.app')

@section('titre', 'Évaluations')

@section('contenu')
    <h1>Évaluations {{ $annee->libelleAnnee }}</h1>

    <div class="carte-formulaire" style="max-width: 900px; margin-bottom: 26px;">
        <h2>{{ $edition ? 'Modifier l\'évaluation' : 'Nouvelle évaluation' }}</h2>

        <form method="POST" action="{{ $edition ? '/evaluations/' . $edition->idEvaluation : '/evaluations' }}">
            @csrf
            @if($edition) @method('PUT') @endif

            <div class="grille-champs">
                <div class="champ" style="grid-column: 1 / -1;">
                    <label for="enseignement">Classe · Matière · Enseignant</label>
                    <select id="enseignement" name="Enseignement_idEnseignement" required>
                        <option value="">-- Choisir --</option>
                        @foreach($enseignements as $e)
                            <option value="{{ $e->idEnseignement }}"
                                {{ old('Enseignement_idEnseignement', $edition->Enseignement_idEnseignement ?? '') == $e->idEnseignement ? 'selected' : '' }}>
                                {{ $e->classe->libelleClasse }} · {{ $e->matiere->libelleMatiere }} · {{ $e->enseignant->nomEnseignant }} {{ $e->enseignant->prenomEnseignant }}
                            </option>
                        @endforeach
                    </select>
                    @error('Enseignement_idEnseignement') <div class="erreur">{{ $message }}</div> @enderror
                </div>

                <div class="champ">
                    <label for="type">Type</label>
                    <select id="type" name="TypeEvaluation_idTypeEvaluation" required>
                        <option value="">-- Choisir --</option>
                        @foreach($types as $t)
                            <option value="{{ $t->idTypeEvaluation }}"
                                {{ old('TypeEvaluation_idTypeEvaluation', $edition->TypeEvaluation_idTypeEvaluation ?? '') == $t->idTypeEvaluation ? 'selected' : '' }}>
                                {{ $t->libelleTypeEvaluation }} ({{ $t->ponderation }} %)
                            </option>
                        @endforeach
                    </select>
                    @error('TypeEvaluation_idTypeEvaluation') <div class="erreur">{{ $message }}</div> @enderror
                </div>

                <div class="champ">
                    <label for="periode">Période</label>
                    <select id="periode" name="Periode_idPeriode" required>
                        <option value="">-- Choisir --</option>
                        @foreach($periodes as $p)
                            <option value="{{ $p->idPeriode }}"
                                {{ old('Periode_idPeriode', $edition->Periode_idPeriode ?? '') == $p->idPeriode ? 'selected' : '' }}>
                                {{ $p->libellePeriode }} ({{ $p->dateDebutPeriode }} → {{ $p->dateFinPeriode }})
                            </option>
                        @endforeach
                    </select>
                    @error('Periode_idPeriode') <div class="erreur">{{ $message }}</div> @enderror
                </div>

                <div class="champ">
                    <label for="dateEvaluation">Date</label>
                    <input id="dateEvaluation" type="date" name="dateEvaluation"
                           value="{{ old('dateEvaluation', $edition->dateEvaluation ?? '') }}"
                           class="@error('dateEvaluation') invalide @enderror" required>
                    @error('dateEvaluation') <div class="erreur">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="actions-formulaire">
                <button type="submit" class="btn btn-principal">{{ $edition ? 'Enregistrer' : 'Créer l\'évaluation' }}</button>
                @if($edition) <a href="/evaluations" class="btn btn-secondaire">Annuler</a> @endif
            </div>
        </form>
    </div>

    <div class="carte" style="padding: 18px;">
        <table id="tableEvaluations" class="display" style="width:100%">
            <thead>
                <tr><th>Date</th><th>Classe</th><th>Matière</th><th>Type</th><th>Période</th><th>Enseignant</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @foreach($evaluations as $ev)
                    <tr>
                        <td>{{ $ev->dateEvaluation }}</td>
                        <td><strong>{{ $ev->enseignement->classe->libelleClasse }}</strong></td>
                        <td>{{ $ev->enseignement->matiere->libelleMatiere }}</td>
                        <td><span class="badge badge-solde">{{ $ev->type->libelleTypeEvaluation }}</span></td>
                        <td>{{ $ev->periode->libellePeriode }}</td>
                        <td>{{ $ev->enseignement->enseignant->nomEnseignant }} {{ $ev->enseignement->enseignant->prenomEnseignant }}</td>
                        <td class="actions">
                            <a href="/evaluations?edit={{ $ev->idEvaluation }}" class="btn btn-modifier">Modifier</a>
                            <form action="/evaluations/{{ $ev->idEvaluation }}" method="POST" style="display:inline"
                                  onsubmit="return confirm('Supprimer cette évaluation ?')">
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
            $('#tableEvaluations').DataTable({
                pageLength: 10,
                order: [[0, 'desc']],
                columnDefs: [{ orderable: false, targets: 6 }],
                language: {
                    search: 'Rechercher :', lengthMenu: 'Afficher _MENU_ évaluations',
                    info: 'Évaluations _START_ à _END_ sur _TOTAL_', infoEmpty: 'Aucune évaluation',
                    infoFiltered: '(filtré sur _MAX_ au total)', zeroRecords: 'Aucune évaluation trouvée',
                    emptyTable: 'Aucune évaluation enregistrée',
                    paginate: { first: 'Début', previous: 'Précédent', next: 'Suivant', last: 'Fin' }
                }
            });
        });
    </script>
    @endpush
@endsection