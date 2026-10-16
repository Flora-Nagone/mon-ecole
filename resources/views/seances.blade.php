@extends('layouts.app')

@section('titre', 'Séances')

@section('contenu')
    <h1>Séances {{ $annee->libelleAnnee }}</h1>

    <div class="carte-formulaire" style="max-width: 900px; margin-bottom: 26px;">
        <h2>{{ $edition ? 'Modifier la séance' : 'Programmer une séance' }}</h2>

        <form method="POST" action="{{ $edition ? '/seances/' . $edition->idSeance : '/seances' }}">
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
                    <label for="salle">Salle</label>
                    <select id="salle" name="Salle_idSalle" required>
                        <option value="">-- Choisir --</option>
                        @foreach($salles as $s)
                            <option value="{{ $s->idSalle }}"
                                {{ old('Salle_idSalle', $edition->Salle_idSalle ?? '') == $s->idSalle ? 'selected' : '' }}>
                                {{ $s->libelleSalle }}
                            </option>
                        @endforeach
                    </select>
                    @error('Salle_idSalle') <div class="erreur">{{ $message }}</div> @enderror
                </div>

                <div class="champ">
                    <label for="dateSeance">Date</label>
                    <input id="dateSeance" type="date" name="dateSeance"
                           value="{{ old('dateSeance', $edition->dateSeance ?? '') }}"
                           class="@error('dateSeance') invalide @enderror" required>
                    @error('dateSeance') <div class="erreur">{{ $message }}</div> @enderror
                </div>

                <div class="champ">
                    <label for="heureDebutSeance">Début (06h00 - 20h00)</label>
                    <input id="heureDebutSeance" type="time" name="heureDebutSeance" min="06:00" max="20:00"
                           value="{{ old('heureDebutSeance', isset($edition) ? substr($edition->heureDebutSeance, 0, 5) : '') }}"
                           class="@error('heureDebutSeance') invalide @enderror" required>
                    @error('heureDebutSeance') <div class="erreur">{{ $message }}</div> @enderror
                </div>

                <div class="champ">
                    <label for="heureFinSeance">Fin</label>
                    <input id="heureFinSeance" type="time" name="heureFinSeance" min="06:00" max="20:00"
                           value="{{ old('heureFinSeance', isset($edition) ? substr($edition->heureFinSeance, 0, 5) : '') }}"
                           class="@error('heureFinSeance') invalide @enderror" required>
                    @error('heureFinSeance') <div class="erreur">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="actions-formulaire">
                <button type="submit" class="btn btn-principal">{{ $edition ? 'Enregistrer' : 'Programmer' }}</button>
                @if($edition) <a href="/seances" class="btn btn-secondaire">Annuler</a> @endif
            </div>
        </form>
    </div>

    <div class="carte" style="padding: 18px;">
        <table id="tableSeances" class="display" style="width:100%">
            <thead>
                <tr><th>Date</th><th>Horaire</th><th>Classe</th><th>Matière</th><th>Enseignant</th><th>Salle</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @foreach($seances as $s)
                    <tr>
                        <td>{{ $s->dateSeance }}</td>
                        <td>{{ substr($s->heureDebutSeance, 0, 5) }} - {{ substr($s->heureFinSeance, 0, 5) }}</td>
                        <td><strong>{{ $s->enseignement->classe->libelleClasse }}</strong></td>
                        <td>{{ $s->enseignement->matiere->libelleMatiere }}</td>
                        <td>{{ $s->enseignement->enseignant->nomEnseignant }} {{ $s->enseignement->enseignant->prenomEnseignant }}</td>
                        <td><span class="badge badge-solde">{{ $s->salle->libelleSalle }}</span></td>
                        <td class="actions">
                            <a href="/seances?edit={{ $s->idSeance }}" class="btn btn-modifier">Modifier</a>
                            <form action="/seances/{{ $s->idSeance }}" method="POST" style="display:inline"
                                  onsubmit="return confirm('Supprimer cette séance ?')">
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
            $('#tableSeances').DataTable({
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