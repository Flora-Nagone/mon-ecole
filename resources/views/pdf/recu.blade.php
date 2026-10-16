<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu {{ $versement->numRecu }}</title>
    @include('pdf._style')
</head>
<body>
    @php
        $i = $versement->inscription;
        $fcfa = fn ($x) => number_format($x, 0, ',', ' ') . ' FCFA';
    @endphp

    <div class="entete">
        <h1>Excellence Nagoné École</h1>
        <p>Reçu de versement n° <strong>{{ $versement->numRecu }}</strong></p>
    </div>
    <a href="/pdf/recu/{{ $versement->idVersement }}" class="btn btn-ajout">Télécharger en PDF</a>

    <table class="sans-bordure">
        <tr><td class="gris">Date</td><td><strong>{{ \Carbon\Carbon::parse($versement->dateVersement)->format('d/m/Y') }}</strong></td></tr>
        <tr><td class="gris">Élève</td><td><strong>{{ $i->eleve->nomEleve }} {{ $i->eleve->prenomEleve }}</strong></td></tr>
        <tr><td class="gris">Matricule</td><td>{{ $i->eleve->matriculeEleve }}</td></tr>
        <tr><td class="gris">Classe</td><td>{{ $i->classe->libelleClasse }}</td></tr>
        <tr><td class="gris">Dossier</td><td>{{ $i->codeInscription }}</td></tr>
        <tr><td class="gris">Mode de paiement</td><td>{{ $versement->modePaiement }}</td></tr>
    </table>

    <div class="cadre" style="margin: 10px 0;">
        <table class="sans-bordure">
            <tr>
                <td class="gris">Montant versé</td>
                <td style="text-align:right; font-size:14px;"><strong>{{ $fcfa($versement->montantVersement) }}</strong></td>
            </tr>
        </table>
    </div>

    <table class="sans-bordure">
        <tr><td class="gris">Scolarité due</td><td style="text-align:right;">{{ $fcfa($i->montantDu) }}</td></tr>
        <tr><td class="gris">Total versé à ce jour</td><td style="text-align:right;">{{ $fcfa($i->total_verse) }}</td></tr>
        <tr>
            <td class="gris">Reste à payer</td>
            <td style="text-align:right;"><strong>{{ $fcfa($i->reste_a_payer) }}</strong>{{ $i->est_solde ? ' (soldé)' : '' }}</td>
        </tr>
    </table>

    <table class="signatures">
        <tr><td><span>Le parent</span></td><td><span>La comptabilité</span></td></tr>
    </table>
</body>
</html>