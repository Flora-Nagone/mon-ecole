@extends('layouts.app')

@section('titre', 'Feuille de notes')

@section('contenu')
    @php
        $fmt = fn ($x) => $x !== null ? number_format($x, 2, ',', ' ') : '—';
        $ens = $enseignement;
    @endphp

    <h1>Notes : {{ $ens->classe->libelleClasse }} · {{ $ens->matiere->libelleMatiere }}</h1>
    <p class="fiche-sous" style="margin-bottom: 18px;">
        Enseignant : <strong>{{ mb_strtoupper($ens->enseignant->nomEnseignant) }} {{ $ens->enseignant->prenomEnseignant }}</strong>
        · Année {{ $ens->classe->anneeScolaire->libelleAnnee }}
        · Effectif : {{ $inscrits->count() }} élèves
    </p>

    @if($errors->any())
        <div class="message message-erreur">{{ $errors->first() }}</div>
    @endif

    <div class="montant-auto no-print" style="margin-bottom: 22px;">
        Toutes les évaluations de la matière sont sur cette feuille, regroupées par période. Notes sur 20 (entiers).
        Une case <strong style="color:#9a6700;">jaune</strong> est un <strong>rattrapage</strong> (absence justifiée).
        Un absent <strong>non justifié</strong> est compté <strong>0</strong>.
    </div>

    @if($evaluations->isEmpty())
        <div class="message message-erreur">
            Aucune évaluation pour cette matière.
            <a href="/evaluations">Créer une évaluation</a>
        </div>
    @else
        <p class="no-print">
            <button type="button" onclick="window.print()" class="btn btn-ajout">🖨 Imprimer la feuille</button>
            <a href="/pdf/notes/{{ $ens->idEnseignement }}" class="btn btn-ajout">Télécharger en PDF</a>
            <a href="/notes" class="btn btn-secondaire">Retour</a>
        </p>

        <form method="POST" action="/notes/{{ $ens->idEnseignement }}">
            @csrf

            <div class="carte feuille" style="padding: 18px; margin-top: 14px;">
                <table>
                    <thead>
                        <tr>
                            <th rowspan="2">N°</th>
                            <th rowspan="2">Matricule</th>
                            <th rowspan="2">Élève</th>
                            @foreach($groupes as $g)
                                <th colspan="{{ $g['evals']->count() + 1 }}" class="groupe">{{ $g['periode']->libellePeriode }}</th>
                            @endforeach
                            <th rowspan="2">Moy. annuelle</th>
                            <th rowspan="2">Rang</th>
                        </tr>
                        <tr>
                            @foreach($groupes as $g)
                                @foreach($g['evals'] as $ev)
                                    <th>
                                        {{ $ev->type->libelleTypeEvaluation }}<br>
                                        <small>{{ \Carbon\Carbon::parse($ev->dateEvaluation)->format('d/m/Y') }}</small>
                                        @if($ev->dateEvaluation > $aujourdhui)<br><small>à venir</small>@endif
                                    </th>
                                @endforeach
                                <th>Moy.</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inscrits as $i)
                            @php $eid = $i->Eleve_idEleve; @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $i->eleve->matriculeEleve }}</td>
                                <td><strong>{{ $i->eleve->nomEleve }}</strong> {{ $i->eleve->prenomEleve }}</td>

                                @foreach($groupes as $g)
                                    @foreach($g['evals'] as $ev)
                                        @php
                                            $c = $cases[$ev->idEvaluation][$eid];
                                            $futur = $ev->dateEvaluation > $aujourdhui;
                                        @endphp
                                        <td>
                                            @if($c['statut'] === 'zero')
                                                <span class="badge badge-absent" title="Absent non justifié : 0 compté">0 · abs.</span>
                                            @else
                                                <input type="number" min="0" max="20" step="1"
                                                       class="note-champ {{ $c['statut'] === 'rattrapage' ? 'note-ratt' : '' }}"
                                                       name="notes[{{ $ev->idEvaluation }}][{{ $eid }}]"
                                                       value="{{ old('notes.' . $ev->idEvaluation . '.' . $eid, $c['valeur']) }}"
                                                       {{ $futur ? 'disabled' : '' }}
                                                       title="{{ $c['statut'] === 'rattrapage' ? 'Rattrapage (absence justifiée)' : '' }}">
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="moy"><strong>{{ $fmt($moyP[$g['periode']->idPeriode][$eid] ?? null) }}</strong></td>
                                @endforeach

                                <td class="moy"><strong>{{ $fmt($moyAn[$eid] ?? null) }}</strong></td>
                                <td>{{ \App\Services\BulletinService::ordinal($rangs[$eid] ?? null) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="20" class="vide">Aucun élève réellement inscrit dans cette classe.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        @foreach([['Moyenne de la classe', 'moy'], ['Moyenne maximale', 'max'], ['Moyenne minimale', 'min']] as [$libelle, $cle])
                            <tr>
                                <td colspan="3">{{ $libelle }}</td>
                                @foreach($groupes as $g)
                                    <td colspan="{{ $g['evals']->count() }}"></td>
                                    <td>{{ $fmt($statsP[$g['periode']->idPeriode][$cle] ?? null) }}</td>
                                @endforeach
                                <td>{{ $fmt($statsAn[$cle] ?? null) }}</td>
                                <td></td>
                            </tr>
                        @endforeach
                    </tfoot>
                </table>
            </div>

            <div class="actions-formulaire no-print" style="border-top:none;">
                <button type="submit" class="btn btn-principal">Enregistrer les notes</button>
                <a href="/notes" class="btn btn-secondaire">Retour</a>
            </div>
        </form>
    @endif
@endsection