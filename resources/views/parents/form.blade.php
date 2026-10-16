@extends('layouts.app')

@section('titre', $parent ? 'Modifier le parent' : 'Nouveau parent')

@section('contenu')
    <h1>{{ $parent ? 'Modifier le parent' : 'Nouveau parent' }}</h1>

    <div class="carte-formulaire">
        <form method="POST" action="{{ $parent ? '/parents/' . $parent->idParent : '/parents' }}">
            @csrf
            @if($parent) @method('PUT') @endif

            <div class="grille-champs">
                <div class="champ">
                    <label for="nomParent">Nom</label>
                    <input id="nomParent" name="nomParent"
                           value="{{ old('nomParent', $parent->nomParent ?? '') }}"
                           class="@error('nomParent') invalide @enderror" required>
                    @error('nomParent') <div class="erreur">{{ $message }}</div> @enderror
                </div>

                <div class="champ">
                    <label for="prenomParent">Prénom</label>
                    <input id="prenomParent" name="prenomParent"
                           value="{{ old('prenomParent', $parent->prenomParent ?? '') }}"
                           class="@error('prenomParent') invalide @enderror" required>
                    @error('prenomParent') <div class="erreur">{{ $message }}</div> @enderror
                </div>

                <div class="champ">
                    <label for="telephoneParent">Téléphone</label>
                    <input id="telephoneParent" name="telephoneParent"
                           value="{{ old('telephoneParent', $parent->telephoneParent ?? '') }}"
                           class="@error('telephoneParent') invalide @enderror" required>
                    @error('telephoneParent') <div class="erreur">{{ $message }}</div> @enderror
                </div>

                <div class="champ">
                    <label for="emailParent">Email</label>
                    <input id="emailParent" type="email" name="emailParent"
                           value="{{ old('emailParent', $parent->emailParent ?? '') }}"
                           class="@error('emailParent') invalide @enderror" required>
                    @error('emailParent') <div class="erreur">{{ $message }}</div> @enderror
                </div>

                <div class="champ">
                    <label for="adresseParent">Adresse</label>
                    <input id="adresseParent" name="adresseParent"
                           value="{{ old('adresseParent', $parent->adresseParent ?? '') }}"
                           class="@error('adresseParent') invalide @enderror" required>
                    @error('adresseParent') <div class="erreur">{{ $message }}</div> @enderror
                </div>

                <div class="champ">
                    <label for="professionParent">Profession <small>(facultatif)</small></label>
                    <input id="professionParent" name="professionParent"
                           value="{{ old('professionParent', $parent->professionParent ?? '') }}"
                           class="@error('professionParent') invalide @enderror">
                    @error('professionParent') <div class="erreur">{{ $message }}</div> @enderror
                </div>

                @unless($parent)
                    <div class="champ">
                        <label for="eleve">Responsable de l'élève</label>
                        <select id="eleve" name="Eleve_idEleve" class="@error('Eleve_idEleve') invalide @enderror" required>
                            <option value="">-- Choisir un élève --</option>
                            @foreach($eleves as $e)
                                <option value="{{ $e->idEleve }}" {{ old('Eleve_idEleve') == $e->idEleve ? 'selected' : '' }}>
                                    {{ $e->matriculeEleve }} - {{ $e->nomEleve }} {{ $e->prenomEleve }}
                                </option>
                            @endforeach
                        </select>
                        @error('Eleve_idEleve') <div class="erreur">{{ $message }}</div> @enderror
                    </div>

                    <div class="champ">
                        <label for="lienParente">Lien de parenté</label>
                        <select id="lienParente" name="lienParente" class="@error('lienParente') invalide @enderror" required>
                            @foreach($liens as $lien)
                                <option value="{{ $lien }}" {{ old('lienParente') == $lien ? 'selected' : '' }}>{{ $lien }}</option>
                            @endforeach
                        </select>
                        @error('lienParente') <div class="erreur">{{ $message }}</div> @enderror
                    </div>
                @endunless
            </div>

            <div class="actions-formulaire">
                <button type="submit" class="btn btn-principal">Enregistrer</button>
                <a href="{{ $parent ? '/parents/' . $parent->idParent : '/parents' }}" class="btn btn-secondaire">Annuler</a>
            </div>
        </form>
    </div>
@endsection