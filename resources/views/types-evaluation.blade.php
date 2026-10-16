@extends('layouts.app')

@section('titre', 'Types d\'évaluation')

@section('contenu')
    <h1>Types d'évaluation</h1>

    @if($types->count() > 0)
        @if($total == 100)
            <div class="message message-succes" style="max-width:700px;">Total des pondérations : 100 %. C'est cohérent.</div>
        @else
            <div class="message message-erreur" style="max-width:700px;">Total des pondérations : {{ $total }} %. Il devrait être de 100 %.</div>
        @endif
    @endif

    <div class="carte-formulaire" style="max-width: 700px; margin-bottom: 26px;">
        <h2>{{ $edition ? 'Modifier le type' : 'Nouveau type' }}</h2>
        <form method="POST" action="{{ $edition ? '/types-evaluation/' . $edition->idTypeEvaluation : '/types-evaluation' }}">
            @csrf
            @if($edition) @method('PUT') @endif

            <div class="grille-champs">
                <div class="champ">
                    <label for="libelleTypeEvaluation">Libellé (ex. Devoir)</label>
                    <input id="libelleTypeEvaluation" name="libelleTypeEvaluation"
                           value="{{ old('libelleTypeEvaluation', $edition->libelleTypeEvaluation ?? '') }}"
                           class="@error('libelleTypeEvaluation') invalide @enderror" required>
                    @error('libelleTypeEvaluation') <div class="erreur">{{ $message }}</div> @enderror
                </div>
                <div class="champ">
                    <label for="ponderation">Pondération (%)</label>
                    <input id="ponderation" type="number" min="0" max="100" name="ponderation"
                           value="{{ old('ponderation', $edition->ponderation ?? '') }}"
                           class="@error('ponderation') invalide @enderror" required>
                    @error('ponderation') <div class="erreur">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="actions-formulaire">
                <button type="submit" class="btn btn-principal">{{ $edition ? 'Enregistrer' : 'Ajouter' }}</button>
                @if($edition) <a href="/types-evaluation" class="btn btn-secondaire">Annuler</a> @endif
            </div>
        </form>
    </div>

    <div class="carte" style="padding: 18px; max-width: 700px;">
        <table>
            <thead><tr><th>Type</th><th>Pondération</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($types as $t)
                    <tr>
                        <td><strong>{{ $t->libelleTypeEvaluation }}</strong></td>
                        <td>{{ $t->ponderation }} %</td>
                        <td class="actions">
                            <a href="/types-evaluation?edit={{ $t->idTypeEvaluation }}" class="btn btn-modifier">Modifier</a>
                            <form action="/types-evaluation/{{ $t->idTypeEvaluation }}" method="POST" style="display:inline"
                                  onsubmit="return confirm('Supprimer ce type ?')">
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
@endsection