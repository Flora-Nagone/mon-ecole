@extends('layouts.app')

@section('titre', 'Tarifs')

@section('contenu')
    <h1>Tarifs de scolarité {{ $annee->libelleAnnee }}</h1>

    <div class="montant-auto" style="margin-bottom: 22px; max-width: 700px;">
        Un tarif s'applique aux <strong>nouvelles inscriptions</strong>.
        Les dossiers déjà créés gardent leur montant d'origine.
    </div>

    <div class="carte-formulaire" style="max-width: 700px;">
        <form method="POST" action="/tarifs">
            @csrf

            <table>
                <thead>
                    <tr>
                        <th>Niveau</th>
                        <th>Montant (FCFA)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($niveaux as $n)
                        <tr>
                            <td><strong>{{ $n->libelleNiveau }}</strong></td>
                            <td>
                                <div class="champ">
                                    <input type="number" min="0" step="1000"
                                           name="montants[{{ $n->idNiveau }}]"
                                           value="{{ old('montants.' . $n->idNiveau, $tarifs[$n->idNiveau] ?? '') }}"
                                           placeholder="Non défini">
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="actions-formulaire">
                <button type="submit" class="btn btn-principal">Enregistrer les tarifs</button>
            </div>
        </form>

        <form method="POST" action="/tarifs/generer" style="margin-top: 14px;"
              onsubmit="return confirm('Remplacer les tarifs par la règle 50 000 + 10 000 par degré ?')">
            @csrf
            <button type="submit" class="btn btn-ajout">Appliquer la règle 50 000 + 10 000 par degré</button>
        </form>
    </div>
@endsection