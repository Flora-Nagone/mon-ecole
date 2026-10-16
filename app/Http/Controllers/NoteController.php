<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\AnneeScolaire;
use App\Models\Enseignement;
use App\Models\Evaluation;
use App\Models\Inscription;
use App\Models\Note;
use App\Models\TypeEvaluation;
use App\Services\BulletinService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NoteController extends Controller
{
    // Liste des matières (une feuille de notes par classe et par matière)
    public function index()
    {
        $annee = AnneeScolaire::enCours();
        if (!$annee) {
            return redirect('/')->with('erreur', "Aucune année scolaire n'est en cours.");
        }

        $enseignements = Enseignement::with(['enseignant', 'matiere', 'classe'])
            ->whereHas('classe', fn ($q) => $q->where('AnneeScolaire_idAnnee', $annee->idAnnee))
            ->get()
            ->sortBy(fn ($e) => $e->classe->libelleClasse . $e->matiere->libelleMatiere)
            ->values();

        // Un enseignant ne voit que ses propres matières
        $user = auth()->user();

        if ($user->hasRole('Enseignant') && !$user->hasRole('Administrateur')) {
            $enseignements = $enseignements
                ->filter(fn ($e) => $e->Enseignant_idEnseignant == $user->Enseignant_idEnseignant)
                ->values();
        }

        $nbEvals = Evaluation::selectRaw('Enseignement_idEnseignement, COUNT(*) as n')
            ->groupBy('Enseignement_idEnseignement')
            ->pluck('n', 'Enseignement_idEnseignement');

        return view('notes-index', compact('annee', 'enseignements', 'nbEvals'));
    }

    // Ancien lien depuis une évaluation : on ouvre la feuille de sa matière
    public function depuisEvaluation($id)
    {
        $evaluation = Evaluation::findOrFail($id);

        return redirect('/notes/' . $evaluation->Enseignement_idEnseignement);
    }

    // Page de la feuille de notes
    public function feuille($id, BulletinService $service)
    {
        $data = $this->donnees($id, $service);

        if (is_string($data)) {
            return redirect('/notes')->with('erreur', $data);
        }

        return view('notes', $data);
    }

    /**
     * Toutes les notes d'une matière : utilisé par la page et par le PDF.
     * Retourne un texte (message) en cas de refus d'accès.
     */
    public function donnees($id, BulletinService $service)
    {
        $enseignement = Enseignement::with(['classe.anneeScolaire', 'matiere', 'enseignant'])->findOrFail($id);

        if ($refus = $this->verifier($enseignement)) {
            return $refus;
        }

        $evaluations = Evaluation::with(['type', 'periode'])
            ->where('Enseignement_idEnseignement', $id)
            ->orderBy('dateEvaluation')
            ->orderBy('idEvaluation')
            ->get();

        $inscrits = $this->inscrits($enseignement->Classe_idClasse);
        $ponderations = TypeEvaluation::pluck('ponderation', 'idTypeEvaluation');

        $notes = Note::whereIn('Evaluation_idEvaluation', $evaluations->pluck('idEvaluation'))
            ->get()
            ->groupBy('Evaluation_idEvaluation')
            ->map(fn ($g) => $g->keyBy('Eleve_idEleve')->all())
            ->all();

        // Contenu de chaque case : valeur, et statut (normal, rattrapage, zero)
        $cases = [];
        $valeurs = [];
        foreach ($evaluations as $ev) {
            $absences = $ev->absences();

            foreach ($inscrits as $i) {
                $eid = $i->Eleve_idEleve;
                $note = $notes[$ev->idEvaluation][$eid] ?? null;
                $abs = $absences->get($eid);

                if (!$note && $abs && !$abs->justifiee) {
                    $valeur = 0;
                    $statut = 'zero';
                } else {
                    $valeur = $note ? $note->valeurNote : null;
                    $statut = ($abs && $abs->justifiee) ? 'rattrapage' : 'normal';
                }

                $cases[$ev->idEvaluation][$eid] = ['valeur' => $valeur, 'statut' => $statut];

                if ($valeur !== null) {
                    $valeurs[$eid][$ev->Periode_idPeriode][$ev->TypeEvaluation_idTypeEvaluation][] = $valeur;
                }
            }
        }

        // Colonnes regroupées par période
        $groupes = $evaluations
            ->groupBy('Periode_idPeriode')
            ->map(fn ($evs) => ['periode' => $evs->first()->periode, 'evals' => $evs])
            ->sortBy(fn ($g) => $g['periode']->dateDebutPeriode)
            ->values();

        // Moyennes par période, moyenne annuelle de la matière et rang
        $moyP = [];
        $moyAn = [];
        foreach ($inscrits as $i) {
            $eid = $i->Eleve_idEleve;
            $liste = [];

            foreach ($groupes as $g) {
                $pid = $g['periode']->idPeriode;
                $m = isset($valeurs[$eid][$pid]) ? $service->moyenneMatiere($valeurs[$eid][$pid], $ponderations) : null;
                $moyP[$pid][$eid] = $m;
                if ($m !== null) {
                    $liste[] = $m;
                }
            }

            $moyAn[$eid] = count($liste) ? array_sum($liste) / count($liste) : null;
        }
        $rangs = $service->rangs($moyAn);

        $stats = function (array $vals) {
            $v = array_values(array_filter($vals, fn ($x) => $x !== null));
            return count($v) ? ['moy' => array_sum($v) / count($v), 'min' => min($v), 'max' => max($v)] : null;
        };

        $statsP = [];
        foreach ($groupes as $g) {
            $pid = $g['periode']->idPeriode;
            $statsP[$pid] = $stats($moyP[$pid] ?? []);
        }
        $statsAn = $stats($moyAn);

        $aujourdhui = now()->toDateString();

        return compact(
            'enseignement', 'evaluations', 'inscrits', 'groupes', 'cases',
            'moyP', 'moyAn', 'rangs', 'statsP', 'statsAn', 'aujourdhui'
        );
    }

    public function enregistrer(Request $request, $id)
    {
        $enseignement = Enseignement::findOrFail($id);

        if ($refus = $this->verifier($enseignement)) {
            return redirect('/notes')->with('erreur', $refus);
        }

        $request->validate([
            'notes.*.*' => 'nullable|integer|min:0|max:20',
        ], [
            'notes.*.*.integer' => 'Une note est un nombre entier.',
            'notes.*.*.min' => 'Une note ne peut pas être négative.',
            'notes.*.*.max' => 'Une note ne dépasse pas 20.',
        ]);

        $evaluations = Evaluation::where('Enseignement_idEnseignement', $id)->get();
        $inscrits = $this->inscrits($enseignement->Classe_idClasse);
        $aujourdhui = now()->toDateString();

        DB::transaction(function () use ($request, $evaluations, $inscrits, $aujourdhui) {
            foreach ($evaluations as $ev) {
                // Pas de note avant l'évaluation
                if ($ev->dateEvaluation > $aujourdhui) {
                    continue;
                }

                $absences = $ev->absences();

                foreach ($inscrits as $i) {
                    $eid = $i->Eleve_idEleve;
                    $abs = $absences->get($eid);

                    // Absent non justifié : compté 0, rien à saisir
                    if ($abs && !$abs->justifiee) {
                        continue;
                    }

                    $valeur = $request->input("notes.{$ev->idEvaluation}.$eid");
                    $existante = Note::where('Eleve_idEleve', $eid)
                        ->where('Evaluation_idEvaluation', $ev->idEvaluation)
                        ->first();

                    if ($valeur === null || $valeur === '') {
                        if ($existante) {
                            Absence::where('Note_idNote', $existante->idNote)->update(['Note_idNote' => null]);
                            $existante->delete();
                        }
                        continue;
                    }

                    $note = Note::updateOrCreate(
                        ['Eleve_idEleve' => $eid, 'Evaluation_idEvaluation' => $ev->idEvaluation],
                        ['valeurNote' => (int) $valeur, 'estRattrapage' => $abs ? 1 : 0]
                    );

                    // Absence justifiée : on garde la trace de la note de rattrapage
                    if ($abs) {
                        $abs->update([
                            'Evaluation_idEvaluation' => $ev->idEvaluation,
                            'Note_idNote' => $note->idNote,
                        ]);
                    }
                }
            }
        });

        return redirect('/notes/' . $id)->with('succes', 'Notes enregistrées.');
    }

    private function verifier($enseignement)
    {
        $user = auth()->user();

        if ($user->hasRole('Enseignant') && !$user->hasRole('Administrateur')
            && $enseignement->Enseignant_idEnseignant != $user->Enseignant_idEnseignant) {
            return 'Vous ne pouvez saisir que les notes de vos propres matières.';
        }

        return null;
    }

    private function inscrits($classeId)
    {
        return Inscription::with('eleve')
            ->withSum('versements', 'montantVersement')
            ->where('Classe_idClasse', $classeId)
            ->get()
            ->filter(fn ($i) => $i->est_inscrit)
            ->sortBy(fn ($i) => $i->eleve->nomEleve . ' ' . $i->eleve->prenomEleve)
            ->values();
    }
}
