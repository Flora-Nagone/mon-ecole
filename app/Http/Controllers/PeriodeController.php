<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Periode;
use Illuminate\Http\Request;

class PeriodeController extends Controller
{
    public function index(Request $request)
    {
        $periodes = Periode::with('anneeScolaire')
            ->orderByDesc('AnneeScolaire_idAnnee')
            ->orderBy('dateDebutPeriode')
            ->get();

        $annees = AnneeScolaire::orderByDesc('dateDebutAnnee')->get();
        $edition = $request->filled('edit') ? Periode::find($request->edit) : null;

        return view('periodes', compact('periodes', 'annees', 'edition'));
    }

    public function store(Request $request)
    {
        $donnees = $this->valider($request);
        if (is_string($donnees)) {
            return back()->withInput()->with('erreur', $donnees);
        }

        Periode::create($donnees);

        return redirect('/periodes')->with('succes', 'Période ajoutée.');
    }

    public function update(Request $request, $id)
    {
        $periode = Periode::findOrFail($id);

        $donnees = $this->valider($request);
        if (is_string($donnees)) {
            return back()->withInput()->with('erreur', $donnees);
        }

        $periode->update($donnees);

        return redirect('/periodes')->with('succes', 'Période modifiée.');
    }

    public function destroy($id)
    {
        try {
            Periode::findOrFail($id)->delete();
            return redirect('/periodes')->with('succes', 'Période supprimée.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect('/periodes')->with('erreur', 'Impossible de supprimer : des évaluations utilisent cette période.');
        }
    }

    // Retourne les données validées, ou un message d'erreur (texte)
    private function valider(Request $request)
    {
        $donnees = $request->validate([
            'libellePeriode' => 'required|string|max:45',
            'dateDebutPeriode' => 'required|date',
            'dateFinPeriode' => 'required|date|after:dateDebutPeriode',
            'AnneeScolaire_idAnnee' => 'required|exists:anneescolaire,idAnnee',
        ], [
            'dateFinPeriode.after' => 'La date de fin doit être après la date de début.',
        ]);

        $annee = AnneeScolaire::findOrFail($donnees['AnneeScolaire_idAnnee']);

        if ($donnees['dateDebutPeriode'] < $annee->dateDebutAnnee || $donnees['dateFinPeriode'] > $annee->dateFinAnnee) {
            return 'La période doit rester dans l\'année scolaire (du ' . $annee->dateDebutAnnee . ' au ' . $annee->dateFinAnnee . ').';
        }

        return $donnees;
    }
}