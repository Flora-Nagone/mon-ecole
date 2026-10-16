<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\AnneeScolaire;
use App\Models\Evaluation;
use App\Models\Inscription;
use App\Models\Seance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppelController extends Controller
{
    public function index()
    {
        $annee = AnneeScolaire::enCours();
        if (!$annee) {
            return redirect('/')->with('erreur', "Aucune année scolaire n'est en cours.");
        }

        $seances = Seance::with(['enseignement.classe', 'enseignement.matiere', 'enseignement.enseignant', 'salle'])
            ->whereHas('enseignement.classe', fn ($q) => $q->where('AnneeScolaire_idAnnee', $annee->idAnnee))
            ->orderByDesc('dateSeance')
            ->orderBy('heureDebutSeance')
            ->get();

        // Un enseignant ne voit que ses séances
        $user = auth()->user();

        if ($user->hasRole('Enseignant') && !$user->hasRole('Administrateur')) {
            $seances = $seances
                ->filter(fn ($s) => $s->enseignement->Enseignant_idEnseignant == $user->Enseignant_idEnseignant)
                ->values();
        }

        $absents = Absence::selectRaw('Seance_idSeance, COUNT(*) as n')
            ->groupBy('Seance_idSeance')
            ->pluck('n', 'Seance_idSeance');

        return view('appels', compact('annee', 'seances', 'absents'));
    }

    public function show($id)
    {
        $seance = Seance::with(['enseignement.classe', 'enseignement.matiere', 'enseignement.enseignant', 'salle'])
            ->findOrFail($id);

        if ($refus = $this->verifier($seance)) {
            return redirect('/appels')->with('erreur', $refus);
        }

        $inscrits = $this->inscrits($seance->enseignement->Classe_idClasse);
        $absences = Absence::where('Seance_idSeance', $id)->get()->keyBy('Eleve_idEleve');

        return view('appel', compact('seance', 'inscrits', 'absences'));
    }

    public function store(Request $request, $id)
    {
        $seance = Seance::with('enseignement')->findOrFail($id);

        if ($refus = $this->verifier($seance)) {
            return redirect('/appels')->with('erreur', $refus);
        }

        $request->validate(['motif.*' => 'nullable|string|max:45'], [
            'motif.*.max' => 'Un motif ne dépasse pas 45 caractères.',
        ]);

        $inscrits = $this->inscrits($seance->enseignement->Classe_idClasse);
        $coches = $request->input('absent', []);
        $justifiees = $request->input('justifiee', []);
        $motifs = $request->input('motif', []);

        // Évaluation de la même matière, le même jour : l'absence y est liée
        $evaluation = Evaluation::where('Enseignement_idEnseignement', $seance->Enseignement_idEnseignement)
            ->where('dateEvaluation', $seance->dateSeance)
            ->first();

        $bloques = 0;

        DB::transaction(function () use ($inscrits, $coches, $justifiees, $motifs, $seance, $evaluation, &$bloques) {
            foreach ($inscrits as $i) {
                $eleveId = $i->Eleve_idEleve;

                $existante = Absence::where('Seance_idSeance', $seance->idSeance)
                    ->where('Eleve_idEleve', $eleveId)
                    ->first();

                // Une note de rattrapage dépend de cette absence : on n'y touche plus
                if ($existante && $existante->Note_idNote) {
                    $bloques++;
                    continue;
                }

                if (isset($coches[$eleveId])) {
                    $donnees = [
                        'dateAbsence' => $seance->dateSeance,
                        'heureDebutAbsence' => $seance->heureDebutSeance,
                        'heureFinAbsence' => $seance->heureFinSeance,
                        'motif' => trim($motifs[$eleveId] ?? '') ?: 'Non précisé',
                        'justifiee' => isset($justifiees[$eleveId]) ? 1 : 0,
                        'Evaluation_idEvaluation' => $evaluation?->idEvaluation,
                    ];

                    if ($existante) {
                        $existante->update($donnees);
                    } else {
                        Absence::create($donnees + [
                            'Eleve_idEleve' => $eleveId,
                            'Seance_idSeance' => $seance->idSeance,
                        ]);
                    }
                } elseif ($existante) {
                    $existante->delete();
                }
            }
        });

        $nb = Absence::where('Seance_idSeance', $seance->idSeance)->count();
        $message = 'Appel enregistré : ' . $nb . ' absent(s).';
        if ($bloques > 0) {
            $message .= ' ' . $bloques . ' élève(s) non modifié(s) car une note de rattrapage dépend de leur absence.';
        }

        return redirect('/appels')->with('succes', $message);
    }

    private function verifier($seance)
    {
        $user = auth()->user();

        if ($user->hasRole('Enseignant') && !$user->hasRole('Administrateur')
            && $seance->enseignement->Enseignant_idEnseignant != $user->Enseignant_idEnseignant) {
            return "Vous ne pouvez faire l'appel que de vos propres séances.";
        }

        if ($seance->dateSeance > now()->toDateString()) {
            return "L'appel ne peut pas être fait avant la séance (prévue le " . $seance->dateSeance . ').';
        }

        return null;
    }

    // Élèves réellement inscrits dans la classe
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
