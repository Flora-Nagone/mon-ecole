@extends('layouts.app')

@section('titre', 'Nouvel élève')

@section('contenu')
    <h1>Ajouter un élève</h1>

    <div class="carte-formulaire">
        <form method="POST" action="/eleves">
            @csrf

                        <div class="montant-auto" style="margin-bottom: 22px;">
                Le matricule est généré automatiquement à l'enregistrement.
            </div>
            <div class="grille-champs">

               

                <div class="champ">
                    <label for="sexeEleve">Sexe</label>
                    <select id="sexeEleve" name="sexeEleve" class="@error('sexeEleve') invalide @enderror">
                        <option value="M" {{ old('sexeEleve') == 'M' ? 'selected' : '' }}>Garçon</option>
                        <option value="F" {{ old('sexeEleve') == 'F' ? 'selected' : '' }}>Fille</option>
                    </select>
                    @error('sexeEleve') <div class="erreur">{{ $message }}</div> @enderror
                </div>

                <div class="champ">
                    <label for="nomEleve">Nom</label>
                    <input id="nomEleve" name="nomEleve"
                           value="{{ old('nomEleve') }}"
                           class="@error('nomEleve') invalide @enderror" required>
                    @error('nomEleve') <div class="erreur">{{ $message }}</div> @enderror
                </div>

                <div class="champ">
                    <label for="prenomEleve">Prénom</label>
                    <input id="prenomEleve" name="prenomEleve"
                           value="{{ old('prenomEleve') }}"
                           class="@error('prenomEleve') invalide @enderror" required>
                    @error('prenomEleve') <div class="erreur">{{ $message }}</div> @enderror
                </div>

                <div class="champ">
                    <label for="dateNaissance">Date de naissance</label>

                    <input id="dateNaissance" type="date" name="dateNaissance"
       min="{{ now()->subYears(30)->toDateString() }}"
       max="{{ now()->subYears(9)->toDateString() }}"
                           value="{{ old('dateNaissance') }}"
                           class="@error('dateNaissance') invalide @enderror" required>
                    @error('dateNaissance') <div class="erreur">{{ $message }}</div> @enderror
                </div>

                <div class="champ">
                    <label for="lieuNaissance">Lieu de naissance</label>
                    <input id="lieuNaissance" name="lieuNaissance"
                           value="{{ old('lieuNaissance') }}"
                           class="@error('lieuNaissance') invalide @enderror" required>
                    @error('lieuNaissance') <div class="erreur">{{ $message }}</div> @enderror
                </div>

                <div class="champ">
                    <label for="adresseEleve">Adresse</label>
                    <input id="adresseEleve" name="adresseEleve"
                           value="{{ old('adresseEleve') }}"
                           class="@error('adresseEleve') invalide @enderror" required>
                    @error('adresseEleve') <div class="erreur">{{ $message }}</div> @enderror
                </div>

                <div class="champ">
                    <label for="telephoneEleve">Téléphone <small>(facultatif)</small></label>
                    <input id="telephoneEleve" name="telephoneEleve"
                           value="{{ old('telephoneEleve') }}"
                           class="@error('telephoneEleve') invalide @enderror">
                    @error('telephoneEleve') <div class="erreur">{{ $message }}</div> @enderror
                </div>

            </div>

            <div class="actions-formulaire">
                <button type="submit" class="btn btn-principal">Enregistrer</button>
                <a href="/eleves" class="btn btn-secondaire">Annuler</a>
            </div>
        </form>
    </div>
@endsection