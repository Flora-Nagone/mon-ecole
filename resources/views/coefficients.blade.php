@extends('layouts.app')

@section('titre', 'Coefficients')

@section('contenu')
    <h1>Coefficients par niveau</h1>

    <div class="carte-formulaire" style="max-width: 820px; margin-bottom: 26px;">
        <h2>{{ $edition ? 'Modifier le coefficient' : 'Nouveau coefficient' }}</h2>

        <div class="montant-auto" style="margin-bottom: 20px;">
            Le coefficient d'une matière dépend du niveau. Si la combinaison existe déjà, elle est remplacée.
        </div>

        <form method="POST" action="/coefficients">
            @csrf
            <div class="grille-champs">

                @if($edition)
                    <input type="hidden" name="Niveau_idNiveau" value="{{ $edition->Niveau_idNiveau }}">
                    <input type="hidden" name="Matiere_idMatiere" value="{{ $edition->Matiere_idMatiere }}">

                    <div class="champ">
                        <label>Niveau</label>
                        <input value="{{ $edition->niveau->libelleNiveau }}" readonly>
                    </div>
                    <div class="champ">
                        <label>Matière</label>
                        <input value="{{ $edition->matiere->libelleMatiere }}" readonly>
                    </div>
                @else
                    <div class="champ">
                        <label for="niveau">Niveau</label>
                        <select id="niveau" name="Niveau_idNiveau" required>
                            <option value="">-- Choisir --</option>
                            @foreach($niveaux as $n)
                                <option value="{{ $n->idNiveau }}" {{ old('Niveau_idNiveau') == $n->idNiveau ? 'selected' : '' }}>{{ $n->libelleNiveau }}</option>
                            @endforeach
                        </select>
                        @error('Niveau_idNiveau') <div class="erreur">{{ $message }}</div> @enderror
                    </div>
                    <div class="champ">
                        <label for="matiere">Matière</label>
                        <select id="matiere" name="Matiere_idMatiere" required>
                            <option value="">-- Choisir --</option>
                            @foreach($matieres as $m)
                                <option value="{{ $m->idMatiere }}" {{ old('Matiere_idMatiere') == $m->idMatiere ? 'selected' : '' }}>{{ $m->libelleMatiere }}</option>
                            @endforeach
                        </select>
                        @error('Matiere_idMatiere') <div class="erreur">{{ $message }}</div> @enderror
                    </div>
                @endif

                <div class="champ">
                    <label for="coefficient">Coefficient (1 à 20)</label>
                    <input id="coefficient" type="number" min="1" max="20" name="coefficient"
                           value="{{ old('coefficient', $edition->coefficient ?? '') }}"
                           class="@error('coefficient') invalide @enderror" required {{ $edition ? 'autofocus' : '' }}>
                    @error('coefficient') <div class="erreur">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="actions-formulaire">
                <button type="submit" class="btn btn-principal">{{ $edition ? 'Enregistrer la modification' : 'Enregistrer' }}</button>
                @if($edition) <a href="/coefficients" class="btn btn-secondaire">Annuler</a> @endif
            </div>
        </form>
    </div>

    @foreach($niveaux as $n)
        @if(isset($parNiveau[$n->idNiveau]))
            <h2 style="margin-top: 22px;">{{ $n->libelleNiveau }}</h2>
            <div class="carte" style="padding: 14px 18px; max-width: 820px;">
                @foreach($parNiveau[$n->idNiveau]->sortBy(fn ($c) => $c->matiere->libelleMatiere) as $c)
                    <div class="recu-ligne">
                        <span>{{ $c->matiere->libelleMatiere }}</span>
                        <span>
                            <strong>coef. {{ $c->coefficient }}</strong>
                            <a href="/coefficients?edit={{ $c->idCoefficient }}" class="btn btn-modifier"
                               style="margin-left: 12px; padding:4px 10px; font-size:0.75rem;">Modifier</a>
                            <form action="/coefficients/{{ $c->idCoefficient }}" method="POST" style="display:inline; margin-left: 6px;"
                                  onsubmit="return confirm('Supprimer ce coefficient ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-supprimer" style="padding:4px 10px; font-size:0.75rem;">Supprimer</button>
                            </form>
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    @endforeach
@endsection