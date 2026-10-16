@extends('layouts.app')

@section('titre', 'Enseignants')

@section('contenu')
    <h1>Enseignants ({{ $enseignants->count() }})</h1>

    <div class="carte-formulaire" style="margin-bottom: 26px;">
        <h2>{{ $edition ? 'Modifier l\'enseignant' : 'Nouvel enseignant' }}</h2>

        @unless($edition)
            <div class="montant-auto" style="margin-bottom: 20px;">Le matricule est généré automatiquement.</div>
        @endunless

        <form method="POST" action="{{ $edition ? '/enseignants/' . $edition->idEnseignant : '/enseignants' }}">
            @csrf
            @if($edition) @method('PUT') @endif

            <div class="grille-champs">
                @if($edition)
                    <div class="champ">
                        <label>Matricule</label>
                        <input value="{{ $edition->matriculeEnseignant }}" readonly>
                    </div>
                @endif

                <div class="champ">
                    <label for="nomEnseignant">Nom</label>
                    <input id="nomEnseignant" name="nomEnseignant" value="{{ old('nomEnseignant', $edition->nomEnseignant ?? '') }}"
                           class="@error('nomEnseignant') invalide @enderror" required>
                    @error('nomEnseignant') <div class="erreur">{{ $message }}</div> @enderror
                </div>
                <div class="champ">
                    <label for="prenomEnseignant">Prénom</label>
                    <input id="prenomEnseignant" name="prenomEnseignant" value="{{ old('prenomEnseignant', $edition->prenomEnseignant ?? '') }}"
                           class="@error('prenomEnseignant') invalide @enderror" required>
                    @error('prenomEnseignant') <div class="erreur">{{ $message }}</div> @enderror
                </div>
                <div class="champ">
                    <label for="sexeEnseignant">Sexe</label>
                    <select id="sexeEnseignant" name="sexeEnseignant">
                        <option value="M" {{ old('sexeEnseignant', $edition->sexeEnseignant ?? '') == 'M' ? 'selected' : '' }}>Homme</option>
                        <option value="F" {{ old('sexeEnseignant', $edition->sexeEnseignant ?? '') == 'F' ? 'selected' : '' }}>Femme</option>
                    </select>
                </div>
                <div class="champ">
                    <label for="statutEnseignant">Statut</label>
                    <select id="statutEnseignant" name="statutEnseignant">
                        @foreach($statuts as $s)
                            <option value="{{ $s }}" {{ old('statutEnseignant', $edition->statutEnseignant ?? '') == $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="champ">
                    <label for="emailEnseignant">Email</label>
                    <input id="emailEnseignant" type="email" name="emailEnseignant" value="{{ old('emailEnseignant', $edition->emailEnseignant ?? '') }}"
                           class="@error('emailEnseignant') invalide @enderror" required>
                    @error('emailEnseignant') <div class="erreur">{{ $message }}</div> @enderror
                </div>
                <div class="champ">
                    <label for="contactEnseignant">Contact</label>
                    <input id="contactEnseignant" name="contactEnseignant" value="{{ old('contactEnseignant', $edition->contactEnseignant ?? '') }}"
                           class="@error('contactEnseignant') invalide @enderror" required>
                    @error('contactEnseignant') <div class="erreur">{{ $message }}</div> @enderror
                </div>
                <div class="champ">
                    <label for="domaine">Domaine</label>
                    <select id="domaine" name="Domaine_idDomaine" required>
                        <option value="">-- Choisir --</option>
                        @foreach($domaines as $d)
                            <option value="{{ $d->idDomaine }}" {{ old('Domaine_idDomaine', $edition->Domaine_idDomaine ?? '') == $d->idDomaine ? 'selected' : '' }}>{{ $d->libelleDomaine }}</option>
                        @endforeach
                    </select>
                    @error('Domaine_idDomaine') <div class="erreur">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="actions-formulaire">
                <button type="submit" class="btn btn-principal">{{ $edition ? 'Enregistrer' : 'Ajouter' }}</button>
                @if($edition) <a href="/enseignants" class="btn btn-secondaire">Annuler</a> @endif
            </div>
        </form>
    </div>

    <div class="carte" style="padding: 18px;">
        <table id="tableEnseignants" class="display" style="width:100%">
            <thead>
                <tr><th>Matricule</th><th>Enseignant</th><th>Domaine</th><th>Statut</th><th>Contact</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @foreach($enseignants as $e)
                    <tr>
                        <td>{{ $e->matriculeEnseignant }}</td>
                        <td><strong>{{ $e->nomEnseignant }}</strong> {{ $e->prenomEnseignant }}</td>
                        <td><span class="badge badge-solde">{{ $e->domaine->libelleDomaine }}</span></td>
                        <td>{{ $e->statutEnseignant }}</td>
                        <td>{{ $e->contactEnseignant }}</td>
                        <td class="actions">
                            <a href="/enseignants?edit={{ $e->idEnseignant }}" class="btn btn-modifier">Modifier</a>
                            <form action="/enseignants/{{ $e->idEnseignant }}" method="POST" style="display:inline"
                                  onsubmit="return confirm('Supprimer cet enseignant ?')">
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
            $('#tableEnseignants').DataTable({
                pageLength: 10,
                order: [[1, 'asc']],
                columnDefs: [{ orderable: false, targets: 5 }],
                language: {
                    search: 'Rechercher :', lengthMenu: 'Afficher _MENU_ enseignants',
                    info: 'Enseignants _START_ à _END_ sur _TOTAL_', infoEmpty: 'Aucun enseignant',
                    infoFiltered: '(filtré sur _MAX_ au total)', zeroRecords: 'Aucun enseignant trouvé',
                    emptyTable: 'Aucun enseignant enregistré',
                    paginate: { first: 'Début', previous: 'Précédent', next: 'Suivant', last: 'Fin' }
                }
            });
        });
    </script>
    @endpush
@endsection