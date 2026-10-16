<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Niveau;
use App\Models\TarifNiveau;
use Illuminate\Http\Request;

class TarifController extends Controller
{
    public function index()
    {
        $annee = AnneeScolaire::enCours();
        if (!$annee) {
            return redirect('/')->with('erreur', "Aucune année scolaire n'est en cours.");
        }

        $niveaux = Niveau::orderBy('idNiveau')->get();
        $tarifs = TarifNiveau::where('AnneeScolaire_idAnnee', $annee->idAnnee)
            ->pluck('montant', 'Niveau_idNiveau');

        return view('tarifs', compact('annee', 'niveaux', 'tarifs'));
    }

    // Enregistre les montants saisis, niveau par niveau
    public function store(Request $request)
    {
        $annee = AnneeScolaire::enCours();
        if (!$annee) {
            return redirect('/')->with('erreur', "Aucune année scolaire n'est en cours.");
        }

        $request->validate([
            'montants' => 'required|array',
            'montants.*' => 'nullable|integer|min:0',
        ], [
            'montants.*.integer' => 'Chaque montant doit être un nombre entier.',
            'montants.*.min' => 'Un montant ne peut pas être négatif.',
        ]);

        $idsValides = Niveau::pluck('idNiveau')->all();

        foreach ($request->montants as $niveauId => $montant) {
            if ($montant === null || $montant === '' || !in_array((int) $niveauId, $idsValides)) {
                continue;
            }

            TarifNiveau::updateOrCreate(
                ['Niveau_idNiveau' => $niveauId, 'AnneeScolaire_idAnnee' => $annee->idAnnee],
                ['montant' => $montant]
            );
        }

        return redirect('/tarifs')->with('succes',
            "Tarifs enregistrés. Ils s'appliquent aux nouvelles inscriptions.");
    }

    // Règle : 50 000 FCFA pour la 6e, puis +10 000 à chaque degré suivant
    public function generer()
    {
        $annee = AnneeScolaire::enCours();
        if (!$annee) {
            return redirect('/')->with('erreur', "Aucune année scolaire n'est en cours.");
        }

        $rangs = ['6e' => 0, '5e' => 1, '4e' => 2, '3e' => 3, '2n' => 4, '1è' => 5, 'Tl' => 6];

        foreach (Niveau::all() as $niveau) {
            $cle = mb_substr($niveau->libelleNiveau, 0, 2);
            if (!isset($rangs[$cle])) {
                continue;
            }

            TarifNiveau::updateOrCreate(
                ['Niveau_idNiveau' => $niveau->idNiveau, 'AnneeScolaire_idAnnee' => $annee->idAnnee],
                ['montant' => 50000 + 10000 * $rangs[$cle]]
            );
        }

        return redirect('/tarifs')->with('succes',
            'Tarifs générés : 50 000 FCFA pour la 6e, +10 000 FCFA à chaque degré.');
    }
}