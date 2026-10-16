@extends('layouts.app')

@section('titre', 'Bulletins')

@section('contenu')
    @php
        $fmt = fn ($x) => $x !== null ? number_format($x, 2, ',', ' ') : '—';
        $ord = fn ($n) => \App\Services\BulletinService::ordinal($n);
    @endphp

    <h1>Bulletins {{ $annee->libelleAnnee }}</h1>

    <div class="carte-formulaire" style="max-width: 820px; margin-bottom: 26px;">
        <form method="GET" action="/bulletins">
            <div class="grille-champs">
                <div class="champ">
                    <label for="classe">Classe</label>
                    <select id="classe" name="classe" required>
                        <option value="">-- Choisir --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->idClasse }}" {{ request('classe') == $c->idClasse ? 'selected' : '' }}>{{ $c->libelleClasse }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="champ">
                    <label for="periode">Période</label>
                    <select id="periode" name="periode" required>
                        <option value="">-- Choisir --</option>
                        @foreach($periodes as $p)
                            <option value="{{ $p->idPeriode }}" {{ request('periode') == $p->idPeriode ? 'selected' : '' }}>{{ $p->libellePeriode }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="actions-formulaire">
                <button type="submit" class="btn btn-principal">Afficher les résultats</button>
            </div>
        </form>
    </div>

    @if($res)
        @if($periode->dateFinPeriode >= now()->toDateString())
            <div class="message message-erreur">
                Cette période n'est pas terminée (fin le {{ $periode->dateFinPeriode }}) : les bulletins sont <strong>provisoires</strong>.
            </div>
        @endif

        @if($res['effectif'] === 0)
            <div class="message message-erreur">Aucun élève réellement inscrit dans cette classe.</div>
        @else
            <h2>{{ $periode->libellePeriode }}</h2>
            <div class="stats">
                <div class="stat"><span class="stat-nombre">{{ $res['effectif'] }}</span><span class="stat-label">Élèves</span></div>
                <div class="stat"><span class="stat-nombre">{{ $fmt($res['moyenneClasse']) }}</span><span class="stat-label">Moyenne de la classe</span></div>
                <div class="stat"><span class="stat-nombre">{{ $fmt($res['moyMax']) }}</span><span class="stat-label">Moyenne maximale</span></div>
                <div class="stat"><span class="stat-nombre">{{ $fmt($res['moyMin']) }}</span><span class="stat-label">Moyenne minimale</span></div>
            </div>

            <h2>Année {{ $annuel['provisoire'] ? '(provisoire)' : '' }}</h2>
            <div class="stats">
                <div class="stat"><span class="stat-nombre">{{ $fmt($annuel['moyenneClasse']) }}</span><span class="stat-label">Moyenne annuelle de la classe</span></div>
                <div class="stat"><span class="stat-nombre">{{ $fmt($annuel['moyMax']) }}</span><span class="stat-label">Moyenne annuelle maximale</span></div>
                <div class="stat"><span class="stat-nombre">{{ $fmt($annuel['moyMin']) }}</span><span class="stat-label">Moyenne annuelle minimale</span></div>
            </div>

            <p class="no-print">
                <a href="/bulletins/{{ $classe->idClasse }}/{{ $periode->idPeriode }}/tous" class="btn btn-ajout" target="_blank">🖨 Imprimer tous les bulletins</a>
                <a href="/pdf/bulletins/{{ $classe->idClasse }}/{{ $periode->idPeriode }}" class="btn btn-principal">Télécharger tous les bulletins (PDF)</a>
            </p>

            <div class="carte" style="padding: 18px; margin-top: 14px;">
                <table>
                    <thead>
                        <tr>
                            <th>Rang</th><th>Matricule</th><th>Élève</th><th>Moyenne</th>
                            <th>Moy. annuelle</th><th>Rang annuel</th><th>Appréciation</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(collect($res['eleves'])->sortBy(fn ($r) => $r['rang'] ?? 9999) as $r)
                            @php $eid = $r['eleve']->idEleve; @endphp
                            <tr>
                                <td>{{ $ord($r['rang']) }}</td>
                                <td>{{ $r['eleve']->matriculeEleve }}</td>
                                <td><strong>{{ $r['eleve']->nomEleve }}</strong> {{ $r['eleve']->prenomEleve }}</td>
                                <td>{{ $fmt($r['moyenne']) }}</td>
                                <td>{{ $fmt($annuel['annuelle'][$eid] ?? null) }}</td>
                                <td>{{ $ord($annuel['rangs'][$eid] ?? null) }}</td>
                                <td>{{ \App\Services\BulletinService::appreciation($r['moyenne']) }}</td>
                                <td class="actions">
                                    <a href="/bulletins/{{ $classe->idClasse }}/{{ $periode->idPeriode }}/eleve/{{ $eid }}"
                                       class="btn btn-principal" style="padding:7px 14px; font-size:0.8rem;" target="_blank">Bulletin</a>
                                       <a href="/pdf/bulletin/{{ $classe->idClasse }}/{{ $periode->idPeriode }}/{{ $eid }}" class="btn btn-ajout" style="padding:7px 14px; font-size:0.8rem;">PDF</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif
@endsection