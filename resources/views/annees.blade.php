@extends('layouts.app')

@section('titre', 'Années scolaires')

@section('contenu')
    <h1>Années scolaires</h1>

    <div class="carte-formulaire" style="margin-bottom: 26px;">
        <h2>{{ $edition ? 'Modifier l\'année' : 'Nouvelle année scolaire' }}</h2>
        <form method="POST" action="{{ $edition ? '/annees/' . $edition->idAnnee : '/annees' }}">
            @csrf
            @if($edition) @method('PUT') @endif

            <div class="grille-champs">
                <div class="champ">
                    <label for="libelleAnnee">Libellé (ex. 2027-2028)</label>
                    <input id="libelleAnnee" name="libelleAnnee" value="{{ old('libelleAnnee', $edition->libelleAnnee ?? '') }}"
                           class="@error('libelleAnnee') invalide @enderror" required>
                    @error('libelleAnnee') <div class="erreur">{{ $message }}</div> @enderror
                </div>
                <div class="champ">
                    <label for="dateDebutAnnee">Début</label>
                    <input id="dateDebutAnnee" type="date" name="dateDebutAnnee" value="{{ old('dateDebutAnnee', $edition->dateDebutAnnee ?? '') }}"
                           class="@error('dateDebutAnnee') invalide @enderror" required>
                    @error('dateDebutAnnee') <div class="erreur">{{ $message }}</div> @enderror
                </div>
                <div class="champ">
                    <label for="dateFinAnnee">Fin</label>
                    <input id="dateFinAnnee" type="date" name="dateFinAnnee" value="{{ old('dateFinAnnee', $edition->dateFinAnnee ?? '') }}"
                           class="@error('dateFinAnnee') invalide @enderror" required>
                    @error('dateFinAnnee') <div class="erreur">{{ $message }}</div> @enderror
                </div>
                <div class="champ">
                    <label>
                        <input type="checkbox" name="anneeEnCours" value="1" style="width:auto;"
                               {{ old('anneeEnCours', $edition->anneeEnCours ?? 0) ? 'checked' : '' }}>
                        Année en cours
                    </label>
                </div>
            </div>

            <div class="actions-formulaire">
                <button type="submit" class="btn btn-principal">{{ $edition ? 'Enregistrer' : 'Ajouter' }}</button>
                @if($edition) <a href="/annees" class="btn btn-secondaire">Annuler</a> @endif
            </div>
        </form>
    </div>

    <div class="carte" style="padding: 18px;">
        <table>
            <thead><tr><th>Année</th><th>Début</th><th>Fin</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($annees as $a)
                    <tr>
                        <td><strong>{{ $a->libelleAnnee }}</strong></td>
                        <td>{{ $a->dateDebutAnnee }}</td>
                        <td>{{ $a->dateFinAnnee }}</td>
                        <td>
                            @if($a->anneeEnCours) <span class="badge badge-inscrit">En cours</span>
                            @else <span class="badge badge-solde">Inactive</span> @endif
                        </td>
                        <td class="actions">
                            @unless($a->anneeEnCours)
                                <form action="/annees/{{ $a->idAnnee }}/activer" method="POST" style="display:inline"
                                      onsubmit="return confirm('Définir cette année comme année en cours ?')">
                                    @csrf
                                    <button type="submit" class="btn btn-principal" style="padding:7px 14px; font-size:0.8rem;">Activer</button>
                                </form>
                            @endunless
                            <a href="/annees?edit={{ $a->idAnnee }}" class="btn btn-modifier">Modifier</a>
                            <form action="/annees/{{ $a->idAnnee }}" method="POST" style="display:inline"
                                  onsubmit="return confirm('Supprimer cette année ?')">
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