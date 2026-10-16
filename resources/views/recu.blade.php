<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reçu {{ $versement->numRecu }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Lora:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    @php $i = $versement->inscription; @endphp

    <div class="conteneur" style="max-width: 700px;">
        <div class="recu">
            <div class="recu-entete">
                <h2>Excellence Nagoné École</h2>
                <p>Reçu de versement n° <strong>{{ $versement->numRecu }}</strong></p>
            </div>

            <div class="recu-ligne"><span>Date</span><strong>{{ $versement->dateVersement }}</strong></div>
            <div class="recu-ligne"><span>Élève</span><strong>{{ $i->eleve->nomEleve }} {{ $i->eleve->prenomEleve }}</strong></div>
            <div class="recu-ligne"><span>Matricule</span><strong>{{ $i->eleve->matriculeEleve }}</strong></div>
            <div class="recu-ligne"><span>Classe</span><strong>{{ $i->classe->libelleClasse }}</strong></div>
            <div class="recu-ligne"><span>Dossier</span><strong>{{ $i->codeInscription }}</strong></div>
            <div class="recu-ligne"><span>Mode de paiement</span><strong>{{ $versement->modePaiement }}</strong></div>
            <div class="recu-ligne recu-montant">
                <span>Montant versé</span>
                <strong>{{ number_format($versement->montantVersement, 0, ',', ' ') }} FCFA</strong>
            </div>

            <hr>
            <div class="recu-ligne"><span>Scolarité due</span><strong>{{ number_format($i->montantDu, 0, ',', ' ') }} FCFA</strong></div>
            <div class="recu-ligne"><span>Total versé à ce jour</span><strong>{{ number_format($i->total_verse, 0, ',', ' ') }} FCFA</strong></div>
            <div class="recu-ligne">
                <span>Reste à payer</span>
                <strong>{{ number_format($i->reste_a_payer, 0, ',', ' ') }} FCFA
                    @if($i->est_solde) (soldé) @endif
                </strong>
            </div>
        </div>

        <p class="no-print" style="text-align:center; margin-top:20px;">
            <button onclick="window.print()" class="btn btn-principal">🖨 Imprimer</button>
            <a href="/inscriptions/{{ $i->idInscription }}" class="btn btn-secondaire">Retour</a>
        </p>
    </div>
</body>
</html>