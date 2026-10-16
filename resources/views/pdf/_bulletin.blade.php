@php
    $eid = $r['eleve']->idEleve;
    $fmt = fn ($x) => $x !== null ? number_format($x, 2, ',', ' ') : '-';
    $ord = fn ($n) => \App\Services\BulletinService::ordinal($n);
    $moyAn = $annuel['annuelle'][$eid] ?? null;
    $rangAn = $annuel['rangs'][$eid] ?? null;
@endphp

<div class="entete">
    <h1>Excellence Nagoné École</h1>
    <p><strong>BULLETIN DE NOTES : {{ strtoupper($periode->libellePeriode) }}</strong> · Année scolaire {{ $classe->anneeScolaire->libelleAnnee }}</p>
</div>

<table class="sans-bordure">
    <tr>
        <td><span class="gris">Élève :</span> <strong>{{ $r['eleve']->nomEleve }} {{ $r['eleve']->prenomEleve }}</strong></td>
        <td><span class="gris">Matricule :</span> {{ $r['eleve']->matriculeEleve }}</td>
    </tr>
    <tr>
        <td><span class="gris">Né(e) le :</span> {{ \Carbon\Carbon::parse($r['eleve']->dateNaissance)->format('d/m/Y') }} à {{ $r['eleve']->lieuNaissance }}</td>
        <td><span class="gris">Sexe :</span> {{ $r['eleve']->sexeEleve == 'M' ? 'Garçon' : 'Fille' }}</td>
    </tr>
    <tr>
        <td><span class="gris">Classe :</span> <strong>{{ $classe->libelleClasse }}</strong> ({{ $classe->niveau->libelleNiveau }})</td>
        <td><span class="gris">Effectif :</span> {{ $res['effectif'] }} élèves</td>
    </tr>
</table>

<table style="margin-top: 8px; font-size: 8px;">
    <thead>
        <tr>
            <th>Matière</th><th>Enseignant</th><th class="c">Coef.</th><th class="c">Moy.</th><th class="c">Rang</th>
            <th class="c">Points</th><th class="c">Moy. cl.</th><th class="c">Min</th><th class="c">Max</th><th>Appréciation</th>
        </tr>
    </thead>
    <tbody>
        @foreach($res['matieres'] as $m)
            @php
                $id = $m['id'];
                $moy = $r['moyennes'][$id] ?? null;
                $st = $res['statsMatiere'][$id] ?? null;
            @endphp
            <tr>
                <td><strong>{{ $m['libelle'] }}</strong></td>
                <td>{{ $m['enseignant'] }}</td>
                <td class="c">{{ $m['coef'] }}</td>
                <td class="c"><strong>{{ $fmt($moy) }}</strong></td>
                <td class="c">{{ $ord($r['rangsMatiere'][$id] ?? null) }}</td>
                <td class="c">{{ $moy !== null ? $fmt($moy * $m['coef']) : '-' }}</td>
                <td class="c">{{ $fmt($st['moy'] ?? null) }}</td>
                <td class="c">{{ $fmt($st['min'] ?? null) }}</td>
                <td class="c">{{ $fmt($st['max'] ?? null) }}</td>
                <td>{{ \App\Services\BulletinService::appreciation($moy) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2">TOTAL</td>
            <td class="c">{{ $r['totalCoef'] }}</td>
            <td></td><td></td>
            <td class="c">{{ $fmt($r['totalPoints']) }}</td>
            <td colspan="4"></td>
        </tr>
    </tfoot>
</table>

<p class="titre">Résultats du {{ $periode->libellePeriode }}</p>
<table style="font-size: 9px;">
    <thead>
        <tr>
            <th class="c">Moyenne générale</th><th class="c">Rang</th><th class="c">Moyenne classe</th>
            <th class="c">Moyenne max.</th><th class="c">Moyenne min.</th>
            <th class="c">Abs. justifiées</th><th class="c">Abs. non justifiées</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="c"><strong>{{ $r['moyenne'] !== null ? $fmt($r['moyenne']) . ' / 20' : '-' }}</strong></td>
            <td class="c"><strong>{{ $r['rang'] ? $ord($r['rang']) . ' / ' . $res['effectif'] : '-' }}</strong></td>
            <td class="c">{{ $fmt($res['moyenneClasse']) }}</td>
            <td class="c">{{ $fmt($res['moyMax']) }}</td>
            <td class="c">{{ $fmt($res['moyMin']) }}</td>
            <td class="c">{{ number_format($r['absJust'], 1, ',', ' ') }} h</td>
            <td class="c">{{ number_format($r['absNon'], 1, ',', ' ') }} h</td>
        </tr>
    </tbody>
</table>

<p class="titre">Bilan de l'année {{ $annuel['provisoire'] ? '(provisoire)' : '' }}</p>
<table style="font-size: 9px;">
    <thead>
        <tr><th>Période</th><th class="c">Moyenne /20</th><th class="c">Rang</th></tr>
    </thead>
    <tbody>
        @foreach($annuel['periodes'] as $p)
            <tr>
                <td>{{ $p->libellePeriode }}</td>
                <td class="c">{{ $fmt($annuel['moyennes'][$eid][$p->idPeriode] ?? null) }}</td>
                <td class="c">{{ $ord($annuel['rangsPeriode'][$p->idPeriode][$eid] ?? null) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td>MOYENNE ANNUELLE</td>
            <td class="c">{{ $fmt($moyAn) }}</td>
            <td class="c">{{ $rangAn ? $ord($rangAn) . ' / ' . $res['effectif'] : '-' }}</td>
        </tr>
    </tfoot>
</table>

<table style="margin-top: 6px; font-size: 9px;">
    <thead>
        <tr><th class="c">Moyenne annuelle de la classe</th><th class="c">Moyenne annuelle max.</th><th class="c">Moyenne annuelle min.</th></tr>
    </thead>
    <tbody>
        <tr>
            <td class="c">{{ $fmt($annuel['moyenneClasse']) }}</td>
            <td class="c">{{ $fmt($annuel['moyMax']) }}</td>
            <td class="c">{{ $fmt($annuel['moyMin']) }}</td>
        </tr>
    </tbody>
</table>

<p style="margin-top: 8px;"><strong>Appréciation du conseil de classe :</strong> {{ \App\Services\BulletinService::appreciation($r['moyenne']) }}</p>

<table class="signatures">
    <tr>
        <td><span>Le parent</span></td>
        <td><span>Le professeur principal</span></td>
        <td><span>Le directeur</span></td>
    </tr>
</table>