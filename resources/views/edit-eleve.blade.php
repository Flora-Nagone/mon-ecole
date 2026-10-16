@extends('layouts.app')

@section('titre', 'Modifier un élève')

@section('contenu')
    <h1>Modifier l'élève</h1>

    <div class="carte-formulaire">
        <form method="POST" action="/eleves/edit/{{ $eleve->idEleve }}">
            @csrf

            <div class="grille-champs">

                <div class="champ">
                    <label for="matriculeEleve">Matricule</label>
                    <input id="matriculeEleve" name="matriculeEleve"
                           value="{{ old('matriculeEleve', $eleve->matriculeEleve) }}"
                           class="@error('matriculeEleve') invalide @enderror" readonly required>
                    @error('matriculeEleve') <div class="erreur">{{ $message }}</div> @enderror
                </div>

                <div class="champ">
                    <label for="sexeEleve">Sexe</label>
                    <select id="sexeEleve" name="sexeEleve" class="@error('sexeEleve') invalide @enderror">
                        <option value="M" {{ old('sexeEleve', $eleve->sexeEleve) == 'M' ? 'selected' : '' }}>Garçon</option>
                        <option value="F" {{ old('sexeEleve', $eleve->sexeEleve) == 'F' ? 'selected' : '' }}>Fille</option>
                    </select>
                    @error('sexeEleve') <div class="erreur">{{ $message }}</div> @enderror
                </div>

                <div class="champ">
                    <label for="nomEleve">Nom</label>
                    <input id="nomEleve" name="nomEleve"
                           value="{{ old('nomEleve', $eleve->nomEleve) }}"
                           class="@error('nomEleve') invalide @enderror" required>
                    @error('nomEleve') <div class="erreur">{{ $message }}</div> @enderror
                </div>

                <div class="champ">
                    <label for="prenomEleve">Prénom</label>
                    <input id="prenomEleve" name="prenomEleve"
                           value="{{ old('prenomEleve', $eleve->prenomEleve) }}"
                           class="@error('prenomEleve') invalide @enderror" required>
                    @error('prenomEleve') <div class="erreur">{{ $message }}</div> @enderror
                </div>

                <div class="champ">
                    <label for="dateNaissance">Date de naissance</label>
<input id="dateNaissance" type="date" name="dateNaissance"
       min="{{ now()->subYears(30)->toDateString() }}"
       max="{{ now()->subYears(9)->toDateString() }}"
                    value="{{ old('dateNaissance', $eleve->dateNaissance) }}"
                           class="@error('dateNaissance') invalide @enderror" required>
                    @error('dateNaissance') <div class="erreur">{{ $message }}</div> @enderror
                </div>

                <div class="champ">
                    <label for="lieuNaissance">Lieu de naissance</label>
                    <input id="lieuNaissance" name="lieuNaissance"
                           value="{{ old('lieuNaissance', $eleve->lieuNaissance) }}"
                           class="@error('lieuNaissance') invalide @enderror" required>
                    @error('lieuNaissance') <div class="erreur">{{ $message }}</div> @enderror
                </div>

                <div class="champ">
                    <label for="adresseEleve">Adresse</label>
                    <input id="adresseEleve" name="adresseEleve"
                           value="{{ old('adresseEleve', $eleve->adresseEleve) }}"
                           class="@error('adresseEleve') invalide @enderror" required>
                    @error('adresseEleve') <div class="erreur">{{ $message }}</div> @enderror
                </div>

                <div class="champ">
                    <label for="telephoneEleve">Téléphone <small>(facultatif)</small></label>
                    <input id="telephoneEleve" name="telephoneEleve"
                           value="{{ old('telephoneEleve', $eleve->telephoneEleve) }}"
                           class="@error('telephoneEleve') invalide @enderror">
                    @error('telephoneEleve') <div class="erreur">{{ $message }}</div> @enderror
                </div>

            </div>

            <div class="actions-formulaire">
                <button type="submit" class="btn btn-principal">Enregistrer les modifications</button>
                <a href="/eleves" class="btn btn-secondaire">Annuler</a>
            </div>
        </form>
    </div>
@endsection