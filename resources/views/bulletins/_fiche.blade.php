@php
    $eid = $r['eleve']->idEleve;
    $fmt = fn ($x) => $x !== null ? number_format($x, 2, ',', ' ') : '—';
    $ord = fn ($n) => \App\Services\BulletinService::ordinal($n);
    $moyAn = $annuel['annuelle'][$eid] ?? null;
    $rangAn = $annuel['rangs'][$eid] ?? null;
@endphp

<div class="bulletin">
    <div class="bulletin-entete">
        <h2>Excellence Nagoné École</h2>
        <p><strong>BULLETIN DE NOTES : {{ strtoupper($periode->libellePeriode) }}</strong> · Année scolaire {{ $classe->anneeScolaire->libelleAnnee }}</p>
    </div>

    <div class="bulletin-infos">
        <div><span>Élève :</span> <strong>{{ $r['eleve']->nomEleve }} {{ $r['eleve']->prenomEleve }}</strong></div>
        <div><span>Matricule :</span> {{ $r['eleve']->matriculeEleve }}</div>
        <div><span>Né(e) le :</span> {{ \Carbon\Carbon::parse($r['eleve']->dateNaissance)->format('d/m/Y') }} à {{ $r['eleve']->lieuNaissance }}</div>
        <div><span>Sexe :</span> {{ $r['eleve']->sexeEleve == 'M' ? 'Garçon' : 'Fille' }}</div>
        <div><span>Classe :</span> <strong>{{ $classe->libelleClasse }}</strong> ({{ $classe->niveau->libelleNiveau }})</div>
        <div><span>Effectif :</span> {{ $res['effectif'] }} élèves</div>
    </div>

    <table class="bulletin-matieres">
        <thead>
            <tr>
                <th>Matière</th><th>Enseignant</th><th>Coef.</th><th>Moy. /20</th><th>Rang</th>
                <th>Points</th><th>Moy. classe</th><th>Min</th><th>Max</th><th>Appréciation</th>
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
                    <td>{{ $m['coef'] }}</td>
                    <td><strong>{{ $fmt($moy) }}</strong></td>
                    <td>{{ $ord($r['rangsMatiere'][$id] ?? null) }}</td>
                    <td>{{ $moy !== null ? $fmt($moy * $m['coef']) : '—' }}</td>
                    <td>{{ $fmt($st['moy'] ?? null) }}</td>
                    <td>{{ $fmt($st['min'] ?? null) }}</td>
                    <td>{{ $fmt($st['max'] ?? null) }}</td>
                    <td>{{ \App\Services\BulletinService::appreciation($moy) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">TOTAL</td>
                <td>{{ $r['totalCoef'] }}</td>
                <td></td>
                <td></td>
                <td>{{ $fmt($r['totalPoints']) }}</td>
                <td colspan="4"></td>
            </tr>
        </tfoot>
    </table>

    <h3 class="bulletin-titre">Résultats du {{ $periode->libellePeriode }}</h3>
    <div class="bulletin-resume">
        <div><span>Moyenne générale</span><strong>{{ $r['moyenne'] !== null ? $fmt($r['moyenne']) . ' / 20' : '—' }}</strong></div>
        <div><span>Rang</span><strong>{{ $r['rang'] ? $ord($r['rang']) . ' / ' . $res['effectif'] : '—' }}</strong></div>
        <div><span>Moyenne de la classe</span><strong>{{ $fmt($res['moyenneClasse']) }}</strong></div>
        <div><span>Moyenne maximale</span><strong>{{ $fmt($res['moyMax']) }}</strong></div>
        <div><span>Moyenne minimale</span><strong>{{ $fmt($res['moyMin']) }}</strong></div>
        <div><span>Absences justifiées</span><strong>{{ number_format($r['absJust'], 1, ',', ' ') }} h</strong></div>
        <div><span>Absences non justifiées</span><strong>{{ number_format($r['absNon'], 1, ',', ' ') }} h</strong></div>
    </div>

    <h3 class="bulletin-titre">Bilan de l'année {{ $annuel['provisoire'] ? '(provisoire)' : '' }}</h3>
    <table class="bulletin-bilan">
        <thead>
            <tr><th>Période</th><th>Moyenne /20</th><th>Rang</th></tr>
        </thead>
        <tbody>
            @foreach($annuel['periodes'] as $p)
                <tr>
                    <td>{{ $p->libellePeriode }}</td>
                    <td>{{ $fmt($annuel['moyennes'][$eid][$p->idPeriode] ?? null) }}</td>
                    <td>{{ $ord($annuel['rangsPeriode'][$p->idPeriode][$eid] ?? null) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>MOYENNE ANNUELLE</td>
                <td>{{ $fmt($moyAn) }}</td>
                <td>{{ $rangAn ? $ord($rangAn) . ' / ' . $res['effectif'] : '—' }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="bulletin-resume">
        <div><span>Moyenne annuelle de la classe</span><strong>{{ $fmt($annuel['moyenneClasse']) }}</strong></div>
        <div><span>Moyenne annuelle maximale</span><strong>{{ $fmt($annuel['moyMax']) }}</strong></div>
        <div><span>Moyenne annuelle minimale</span><strong>{{ $fmt($annuel['moyMin']) }}</strong></div>
    </div>

    <p><strong>Appréciation du conseil de classe :</strong> {{ \App\Services\BulletinService::appreciation($r['moyenne']) }}</p>

    @if(collect($res['matieres'])->contains('sansCoef', true))
        <p class="fiche-sous no-print">Attention : certaines matières n'ont pas de coefficient défini pour ce niveau (coefficient 1 appliqué).</p>
    @endif

    <div class="signatures">
        <div>Le parent</div>
        <div>Le professeur principal</div>
        <div>Le directeur</div>
    </div>
</div>