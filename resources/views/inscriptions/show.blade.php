@extends('layouts.app')

@section('titre', 'Dossier ' . $inscription->codeInscription)

@section('contenu')
    @php
        $pct = $inscription->montantDu > 0
            ? min(100, round($inscription->total_verse * 100 / $inscription->montantDu))
            : 0;
    @endphp

    <h1>Dossier {{ $inscription->codeInscription }}</h1>

    <div class="carte-formulaire" style="max-width: 900px;">
        <div class="fiche-entete">
            <div>
                <h2 style="margin:0;">{{ $inscription->eleve->nomEleve }} {{ $inscription->eleve->prenomEleve }}</h2>
                <p class="fiche-sous">
                    {{ $inscription->eleve->matriculeEleve }} · {{ $inscription->classe->libelleClasse }}
                    ({{ $inscription->classe->niveau->libelleNiveau }})
                </p>
            </div>
            <div>
                @if($inscription->est_inscrit)
                    <span class="badge badge-inscrit">Inscrit</span>
                @else
                    <span class="badge badge-attente">En attente de versement</span>
                @endif
                @if($inscription->est_solde)
                    <span class="badge badge-solde">Soldé</span>
                @endif
            </div>
        </div>

        <div class="chiffres">
            <div><span>Montant dû</span><strong>{{ number_format($inscription->montantDu, 0, ',', ' ') }} FCFA</strong></div>
            <div><span>Déjà versé</span><strong>{{ number_format($inscription->total_verse, 0, ',', ' ') }} FCFA</strong></div>
            <div><span>Reste à payer</span><strong>{{ number_format($inscription->reste_a_payer, 0, ',', ' ') }} FCFA</strong></div>
        </div>

        <div class="progression">
            <div class="progression-barre" style="width: {{ $pct }}%"></div>
        </div>
        <p class="fiche-sous">
            {{ $pct }} % payé · seuil d'inscription : {{ number_format($inscription->seuil, 0, ',', ' ') }} FCFA
        </p>
    </div>

    <h2 style="margin-top:30px;">Versements</h2>
    <div class="carte" style="max-width: 900px;">
        <table>
            <thead>
                <tr><th>Reçu</th><th>Date</th><th>Montant</th><th>Mode</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($inscription->versements->sortByDesc('idVersement') as $v)
                    <tr>
                        <td>{{ $v->numRecu }}</td>
                        <td>{{ $v->dateVersement }}</td>
                        <td>{{ number_format($v->montantVersement, 0, ',', ' ') }} FCFA</td>
                        <td>{{ $v->modePaiement }}</td>
                        <td class="actions">
                            <a href="/versements/{{ $v->idVersement }}/recu" class="btn btn-modifier" target="_blank">Reçu</a>
                            <a href="/pdf/recu/{{ $v->idVersement }}" class="btn btn-ajout" style="padding:7px 14px; font-size:0.8rem;">PDF</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="vide">Aucun versement pour l'instant.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(!$inscription->est_solde)
        <h2 style="margin-top:30px;">Enregistrer un versement</h2>
        <div class="carte-formulaire" style="max-width: 900px;">
            <form method="POST" action="/inscriptions/{{ $inscription->idInscription }}/versements">
                @csrf
                <div class="grille-champs">
                    <div class="champ">
                        <label for="montantVersement">Montant (FCFA, minimum 10 000)</label>
                        <input id="montantVersement" type="number" name="montantVersement" min="10000"
                               max="{{ $inscription->reste_a_payer }}" step="1"
                               value="{{ old('montantVersement') }}"
                               class="@error('montantVersement') invalide @enderror" required>
                        @error('montantVersement') <div class="erreur">{{ $message }}</div> @enderror
                    </div>

                    <div class="champ">
                        <label for="modePaiement">Mode de paiement</label>
                        <select id="modePaiement" name="modePaiement">
                            @foreach(['Espèces', 'Mobile Money', 'Virement', 'Chèque'] as $mode)
                                <option value="{{ $mode }}" {{ old('modePaiement') == $mode ? 'selected' : '' }}>{{ $mode }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="actions-formulaire">
                    <button type="submit" class="btn btn-principal">Enregistrer le versement</button>
                    <a href="/inscriptions" class="btn btn-secondaire">Retour</a>
                </div>
            </form>
        </div>
    @else
        <p style="margin-top:20px;"><a href="/inscriptions" class="lien-simple">← Retour aux inscriptions</a></p>
    @endif
@endsection