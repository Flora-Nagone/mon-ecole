@extends('layouts.app')

@section('titre', 'Matières')

@section('contenu')
    <h1>Matières ({{ $matieres->count() }})</h1>

    <div class="carte-formulaire" style="max-width: 760px; margin-bottom: 26px;">
        <h2>{{ $edition ? 'Modifier la matière' : 'Nouvelle matière' }}</h2>

        @unless($edition)
            <div class="montant-auto" style="margin-bottom: 20px;">Le code est généré automatiquement. Les coefficients se définissent par niveau dans « Coefficients ».</div>
        @endunless

        <form method="POST" action="{{ $edition ? '/matieres/' . $edition->idMatiere : '/matieres' }}">
            @csrf
            @if($edition) @method('PUT') @endif

            <div class="grille-champs">
                @if($edition)
                    <div class="champ">
                        <label>Code</label>
                        <input value="{{ $edition->codeMatiere }}" readonly>
                    </div>
                @endif
                <div class="champ">
                    <label for="libelleMatiere">Libellé</label>
                    <input id="libelleMatiere" name="libelleMatiere" value="{{ old('libelleMatiere', $edition->libelleMatiere ?? '') }}"
                           class="@error('libelleMatiere') invalide @enderror" required>
                    @error('libelleMatiere') <div class="erreur">{{ $message }}</div> @enderror
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
                @if($edition) <a href="/matieres" class="btn btn-secondaire">Annuler</a> @endif
            </div>
        </form>
    </div>

    <div class="carte" style="padding: 18px; max-width: 760px;">
        <table>
            <thead><tr><th>Code</th><th>Matière</th><th>Domaine</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($matieres as $m)
                    <tr>
                        <td>{{ $m->codeMatiere }}</td>
                        <td><strong>{{ $m->libelleMatiere }}</strong></td>
                        <td><span class="badge badge-solde">{{ $m->domaine->libelleDomaine }}</span></td>
                        <td class="actions">
                            <a href="/matieres?edit={{ $m->idMatiere }}" class="btn btn-modifier">Modifier</a>
                            <form action="/matieres/{{ $m->idMatiere }}" method="POST" style="display:inline"
                                  onsubmit="return confirm('Supprimer cette matière ?')">
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