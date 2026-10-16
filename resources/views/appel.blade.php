@extends('layouts.app')

@section('titre', 'Appel')

@section('contenu')
    <h1>Appel : {{ $seance->enseignement->classe->libelleClasse }} · {{ $seance->enseignement->matiere->libelleMatiere }}</h1>
    <p class="fiche-sous" style="margin-bottom: 18px;">
        {{ $seance->dateSeance }} · {{ substr($seance->heureDebutSeance, 0, 5) }} - {{ substr($seance->heureFinSeance, 0, 5) }}
        · {{ $seance->salle->libelleSalle }}
        · {{ $seance->enseignement->enseignant->nomEnseignant }} {{ $seance->enseignement->enseignant->prenomEnseignant }}
        · <strong>Effectif : {{ $inscrits->count() }}</strong>
    </p>

    <form method="POST" action="/appels/{{ $seance->idSeance }}">
        @csrf

        <div class="carte" style="padding: 18px;">
            <table>
                <thead>
                    <tr><th>N°</th><th>Matricule</th><th>Élève</th><th>Absent</th><th>Justifiée</th><th>Motif</th></tr>
                </thead>
                <tbody>
                    @forelse($inscrits as $i)
                        @php
                            $a = $absences->get($i->Eleve_idEleve);
                            $verrou = $a && $a->Note_idNote;
                        @endphp
                        <tr class="ligne-appel">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $i->eleve->matriculeEleve }}</td>
                            <td><strong>{{ $i->eleve->nomEleve }}</strong> {{ $i->eleve->prenomEleve }}</td>
                            <td>
                                <input type="checkbox" class="chk-absent" name="absent[{{ $i->Eleve_idEleve }}]" value="1"
                                       {{ $a ? 'checked' : '' }} {{ $verrou ? 'disabled' : '' }}>
                            </td>
                            <td>
                                <input type="checkbox" class="chk-justifiee" name="justifiee[{{ $i->Eleve_idEleve }}]" value="1"
                                       {{ $a && $a->justifiee ? 'checked' : '' }} {{ $verrou ? 'disabled' : '' }}>
                            </td>
                            <td>
                                <input type="text" class="champ-motif" name="motif[{{ $i->Eleve_idEleve }}]" maxlength="45"
                                       value="{{ $a && $a->motif !== 'Non précisé' ? $a->motif : '' }}"
                                       placeholder="Motif (facultatif)" {{ $verrou ? 'disabled' : '' }}
                                       style="width:100%; padding:8px 10px; border:2px solid var(--bordure); border-radius:10px;">
                                @if($verrou) <small class="fiche-sous">Note de rattrapage liée : non modifiable</small> @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="vide">Aucun élève réellement inscrit dans cette classe.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="actions-formulaire" style="border-top:none;">
            <button type="submit" class="btn btn-principal">Enregistrer l'appel</button>
            <a href="/appels" class="btn btn-secondaire">Retour</a>
        </div>
    </form>

    @push('scripts')
    <script>
        // Justifiée et motif ne sont actifs que si l'élève est absent
        document.querySelectorAll('.ligne-appel').forEach(function (ligne) {
            var absent = ligne.querySelector('.chk-absent');
            var justifiee = ligne.querySelector('.chk-justifiee');
            var motif = ligne.querySelector('.champ-motif');
            if (absent.disabled) { return; }

            function maj() {
                justifiee.disabled = !absent.checked;
                motif.disabled = !absent.checked;
                if (!absent.checked) { justifiee.checked = false; motif.value = ''; }
            }

            absent.addEventListener('change', maj);
            maj();
        });
    </script>
    @endpush
@endsection