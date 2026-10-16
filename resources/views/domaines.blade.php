@extends('layouts.app')

@section('titre', 'Domaines')

@section('contenu')
    <h1>Domaines ({{ $domaines->count() }})</h1>

    <div class="carte-formulaire" style="max-width: 700px; margin-bottom: 26px;">
        <h2>{{ $edition ? 'Modifier le domaine' : 'Nouveau domaine' }}</h2>
        <form method="POST" action="{{ $edition ? '/domaines/' . $edition->idDomaine : '/domaines' }}">
            @csrf
            @if($edition) @method('PUT') @endif

            <div class="grille-champs">
                <div class="champ">
                    <label for="libelleDomaine">Libellé</label>
                    <input id="libelleDomaine" name="libelleDomaine" value="{{ old('libelleDomaine', $edition->libelleDomaine ?? '') }}"
                           class="@error('libelleDomaine') invalide @enderror" required>
                    @error('libelleDomaine') <div class="erreur">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="actions-formulaire">
                <button type="submit" class="btn btn-principal">{{ $edition ? 'Enregistrer' : 'Ajouter' }}</button>
                @if($edition) <a href="/domaines" class="btn btn-secondaire">Annuler</a> @endif
            </div>
        </form>
    </div>

    <div class="carte" style="padding: 18px; max-width: 700px;">
        <table>
            <thead><tr><th>Domaine</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($domaines as $d)
                    <tr>
                        <td><strong>{{ $d->libelleDomaine }}</strong></td>
                        <td class="actions">
                            <a href="/domaines?edit={{ $d->idDomaine }}" class="btn btn-modifier">Modifier</a>
                            <form action="/domaines/{{ $d->idDomaine }}" method="POST" style="display:inline"
                                  onsubmit="return confirm('Supprimer ce domaine ?')">
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