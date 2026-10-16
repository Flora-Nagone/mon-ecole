<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Feuille de notes</title>
    @include('pdf._style')
</head>
<body>
    @php
        $fmt = fn ($x) => $x !== null ? number_format($x, 2, ',', ' ') : '-';
        $ens = $enseignement;
    @endphp

    <div class="entete">
        <h1>Excellence Nagoné École</h1>
        <p><strong>FEUILLE DE NOTES : {{ strtoupper($ens->classe->libelleClasse) }} · {{ strtoupper($ens->matiere->libelleMatiere) }}</strong></p>
        <p class="gris">
            Enseignant : {{ mb_strtoupper($ens->enseignant->nomEnseignant) }} {{ $ens->enseignant->prenomEnseignant }}
            · Année scolaire {{ $ens->classe->anneeScolaire->libelleAnnee }}
            · Effectif : {{ $inscrits->count() }} élèves
        </p>
    </div>

    @if($evaluations->isEmpty())
        <p>Aucune évaluation pour cette matière.</p>
    @else
        <table style="font-size: 8px;">
            <thead>
                <tr>
                    <th rowspan="2">N°</th>
                    <th rowspan="2">Matricule</th>
                    <th rowspan="2">Élève</th>
                    @foreach($groupes as $g)
                        <th colspan="{{ $g['evals']->count() + 1 }}" class="c">{{ $g['periode']->libellePeriode }}</th>
                    @endforeach
                    <th rowspan="2" class="c">Moy. annuelle</th>
                    <th rowspan="2" class="c">Rang</th>
                </tr>
                <tr>
                    @foreach($groupes as $g)
                        @foreach($g['evals'] as $ev)
                            <th class="c">{{ $ev->type->libelleTypeEvaluation }}<br>{{ \Carbon\Carbon::parse($ev->dateEvaluation)->format('d/m') }}</th>
                        @endforeach
                        <th class="c">Moy.</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($inscrits as $i)
                    @php $eid = $i->Eleve_idEleve; @endphp
                    <tr>
                        <td class="c">{{ $loop->iteration }}</td>
                        <td>{{ $i->eleve->matriculeEleve }}</td>
                        <td><strong>{{ $i->eleve->nomEleve }}</strong> {{ $i->eleve->prenomEleve }}</td>
                        @foreach($groupes as $g)
                            @foreach($g['evals'] as $ev)
                                @php $c = $cases[$ev->idEvaluation][$eid]; @endphp
                                <td class="c">
                                    @if($c['statut'] === 'zero') 0 (abs.)
                                    @elseif($c['valeur'] !== null) {{ $c['valeur'] }}{{ $c['statut'] === 'rattrapage' ? ' (R)' : '' }}
                                    @endif
                                </td>
                            @endforeach
                            <td class="c"><strong>{{ $fmt($moyP[$g['periode']->idPeriode][$eid] ?? null) }}</strong></td>
                        @endforeach
                        <td class="c"><strong>{{ $fmt($moyAn[$eid] ?? null) }}</strong></td>
                        <td class="c">{{ \App\Services\BulletinService::ordinal($rangs[$eid] ?? null) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                @foreach([['Moyenne de la classe', 'moy'], ['Moyenne maximale', 'max'], ['Moyenne minimale', 'min']] as [$libelle, $cle])
                    <tr>
                        <td colspan="3">{{ $libelle }}</td>
                        @foreach($groupes as $g)
                            <td colspan="{{ $g['evals']->count() }}"></td>
                            <td class="c">{{ $fmt($statsP[$g['periode']->idPeriode][$cle] ?? null) }}</td>
                        @endforeach
                        <td class="c">{{ $fmt($statsAn[$cle] ?? null) }}</td>
                        <td></td>
                    </tr>
                @endforeach
            </tfoot>
        </table>
        <p class="gris" style="margin-top: 6px;">(R) = note de rattrapage · (abs.) = absence non justifiée, comptée 0.</p>
    @endif
</body>
</html>