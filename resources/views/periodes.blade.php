@extends('layouts.app')

@section('titre', 'Périodes')

@section('contenu')
    <h1>Périodes</h1>

    <div class="carte-formulaire" style="margin-bottom: 26px;">
        <h2>{{ $edition ? 'Modifier la période' : 'Nouvelle période' }}</h2>
        <form method="POST" action="{{ $edition ? '/periodes/' . $edition->idPeriode : '/periodes' }}">
            @csrf
            @if($edition) @method('PUT') @endif

            <div class="grille-champs">
                <div class="champ">
                    <label for="annee">Année scolaire</label>
                    <select id="annee" name="AnneeScolaire_idAnnee" required>
                        @foreach($annees as $a)
                            <option value="{{ $a->idAnnee }}"
                                {{ old('AnneeScolaire_idAnnee', $edition->AnneeScolaire_idAnnee ?? ($a->anneeEnCours ? $a->idAnnee : '')) == $a->idAnnee ? 'selected' : '' }}>
                                {{ $a->libelleAnnee }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="champ">
                    <label for="libellePeriode">Libellé (ex. 1er Trimestre)</label>
                    <input id="libellePeriode" name="libellePeriode" value="{{ old('libellePeriode', $edition->libellePeriode ?? '') }}"
                           class="@error('libellePeriode') invalide @enderror" required>
                    @error('libellePeriode') <div class="erreur">{{ $message }}</div> @enderror
                </div>
                <div class="champ">
                    <label for="dateDebutPeriode">Début</label>
                    <input id="dateDebutPeriode" type="date" name="dateDebutPeriode" value="{{ old('dateDebutPeriode', $edition->dateDebutPeriode ?? '') }}"
                           class="@error('dateDebutPeriode') invalide @enderror" required>
                    @error('dateDebutPeriode') <div class="erreur">{{ $message }}</div> @enderror
                </div>
                <div class="champ">
                    <label for="dateFinPeriode">Fin</label>
                    <input id="dateFinPeriode" type="date" name="dateFinPeriode" value="{{ old('dateFinPeriode', $edition->dateFinPeriode ?? '') }}"
                           class="@error('dateFinPeriode') invalide @enderror" required>
                    @error('dateFinPeriode') <div class="erreur">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="actions-formulaire">
                <button type="submit" class="btn btn-principal">{{ $edition ? 'Enregistrer' : 'Ajouter' }}</button>
                @if($edition) <a href="/periodes" class="btn btn-secondaire">Annuler</a> @endif
            </div>
        </form>
    </div>

    <div class="carte" style="padding: 18px;">
        <table>
            <thead><tr><th>Année</th><th>Période</th><th>Début</th><th>Fin</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($periodes as $p)
                    <tr>
                        <td>{{ $p->anneeScolaire->libelleAnnee }}</td>
                        <td><strong>{{ $p->libellePeriode }}</strong></td>
                        <td>{{ $p->dateDebutPeriode }}</td>
                        <td>{{ $p->dateFinPeriode }}</td>
                        <td class="actions">
                            <a href="/periodes?edit={{ $p->idPeriode }}" class="btn btn-modifier">Modifier</a>
                            <form action="/periodes/{{ $p->idPeriode }}" method="POST" style="display:inline"
                                  onsubmit="return confirm('Supprimer cette période ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-supprimer">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="vide">Aucune période.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection