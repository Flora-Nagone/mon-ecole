<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Enseignement;
use App\Models\Evaluation;
use App\Models\Periode;
use App\Models\TypeEvaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EvaluationController extends Controller
{
    public function index(Request $request)
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

        $evaluations = Evaluation::with(['enseignement.classe', 'enseignement.matiere', 'enseignement.enseignant', 'periode', 'type'])
            ->whereHas('enseignement.classe', fn ($q) => $q->where('AnneeScolaire_idAnnee', $annee->idAnnee))
            ->orderByDesc('dateEvaluation')
            ->get();

        return view('evaluations', [
            'annee' => $annee,
            'evaluations' => $evaluations,
            'enseignements' => $enseignements,
            'periodes' => Periode::where('AnneeScolaire_idAnnee', $annee->idAnnee)->orderBy('dateDebutPeriode')->get(),
            'types' => TypeEvaluation::orderBy('libelleTypeEvaluation')->get(),
            'edition' => $request->filled('edit') ? Evaluation::find($request->edit) : null,
        ]);
    }

    public function store(Request $request)
    {
        $donnees = $this->valider($request);
        if (is_string($donnees)) {
            return back()->withInput()->with('erreur', $donnees);
        }

        Evaluation::create($donnees);

        return redirect('/evaluations')->with('succes', 'Évaluation enregistrée.');
    }

    public function update(Request $request, $id)
    {
        $evaluation = Evaluation::findOrFail($id);

        $donnees = $this->valider($request);
        if (is_string($donnees)) {
            return back()->withInput()->with('erreur', $donnees);
        }

        // Si des notes existent, on ne change plus la classe ni la matière
        if ($donnees['Enseignement_idEnseignement'] != $evaluation->Enseignement_idEnseignement
            && DB::table('note')->where('Evaluation_idEvaluation', $id)->exists()) {
            return back()->withInput()->with('erreur',
                'Impossible de changer la classe ou la matière : des notes sont déjà saisies.');
        }

        $evaluation->update($donnees);

        return redirect('/evaluations')->with('succes', 'Évaluation modifiée.');
    }

    public function destroy($id)
    {
        try {
            Evaluation::findOrFail($id)->delete();
            return redirect('/evaluations')->with('succes', 'Évaluation supprimée.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect('/evaluations')->with('erreur', 'Impossible de supprimer : des notes sont saisies pour cette évaluation.');
        }
    }

    private function valider(Request $request)
    {
        $d = $request->validate([
            'Enseignement_idEnseignement' => 'required|exists:enseignement,idEnseignement',
            'Periode_idPeriode' => 'required|exists:periode,idPeriode',
            'TypeEvaluation_idTypeEvaluation' => 'required|exists:typeevaluation,idTypeEvaluation',
            'dateEvaluation' => 'required|date',
        ]);

        $enseignement = Enseignement::with('classe')->findOrFail($d['Enseignement_idEnseignement']);
        $periode = Periode::findOrFail($d['Periode_idPeriode']);

        if ($periode->AnneeScolaire_idAnnee != $enseignement->classe->AnneeScolaire_idAnnee) {
            return "Cette période n'appartient pas à l'année scolaire de la classe.";
        }

        if ($d['dateEvaluation'] < $periode->dateDebutPeriode || $d['dateEvaluation'] > $periode->dateFinPeriode) {
            return 'La date doit être dans la période (du ' . $periode->dateDebutPeriode . ' au ' . $periode->dateFinPeriode . ').';
        }

        return $d;
    }
}