@extends('layouts.app')

@section('titre', $classe ? 'Modifier la classe' : 'Nouvelle classe')

@section('contenu')
    <h1>{{ $classe ? 'Modifier la classe' : 'Nouvelle classe' }}</h1>

    <div class="carte-formulaire">
        <form method="POST" action="{{ $classe ? '/classes/' . $classe->idClasse : '/classes' }}">
            @csrf
            @if($classe) @method('PUT') @endif

            @unless($classe)
                <div class="montant-auto" style="margin-bottom: 22px;">
                    Le code de la classe est généré automatiquement. La classe est créée pour l'année en cours.
                </div>
            @endunless

            <div class="grille-champs">
                @if($classe)
                    <div class="champ">
                        <label>Code</label>
                        <input value="{{ $classe->codeClasse }}" readonly>
                    </div>
                @endif

                <div class="champ">
                    <label for="libelleClasse">Nom de la classe</label>
                    <input id="libelleClasse" name="libelleClasse"
                           value="{{ old('libelleClasse', $classe->libelleClasse ?? '') }}"
                           class="@error('libelleClasse') invalide @enderror" required>
                    @error('libelleClasse') <div class="erreur">{{ $message }}</div> @enderror
                </div>

                <div class="champ">
                    <label for="niveau">Niveau</label>
                    <select id="niveau" name="Niveau_idNiveau" class="@error('Niveau_idNiveau') invalide @enderror" required>
                        <option value="">-- Choisir un niveau --</option>
                        @foreach($niveaux as $n)
                            <option value="{{ $n->idNiveau }}"
                                {{ old('Niveau_idNiveau', $classe->Niveau_idNiveau ?? '') == $n->idNiveau ? 'selected' : '' }}>
                                {{ $n->libelleNiveau }}
                            </option>
                        @endforeach
                    </select>
                    @error('Niveau_idNiveau') <div class="erreur">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="actions-formulaire">
                <button type="submit" class="btn btn-principal">Enregistrer</button>
                <a href="/classes" class="btn btn-secondaire">Annuler</a>
            </div>
        </form>
    </div>
@endsection