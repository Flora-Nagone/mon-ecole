<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Enseignement;
use App\Models\Salle;
use App\Models\Seance;
use Illuminate\Http\Request;

class SeanceController extends Controller
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

        $seances = Seance::with(['enseignement.classe', 'enseignement.matiere', 'enseignement.enseignant', 'salle'])
            ->whereHas('enseignement.classe', fn ($q) => $q->where('AnneeScolaire_idAnnee', $annee->idAnnee))
            ->orderByDesc('dateSeance')
            ->orderBy('heureDebutSeance')
            ->get();

        return view('seances', [
            'annee' => $annee,
            'seances' => $seances,
            'enseignements' => $enseignements,
            'salles' => Salle::orderBy('libelleSalle')->get(),
            'edition' => $request->filled('edit') ? Seance::find($request->edit) : null,
        ]);
    }

    public function store(Request $request)
    {
        $donnees = $this->valider($request);
        if (is_string($donnees)) {
            return back()->withInput()->with('erreur', $donnees);
        }

        Seance::create($donnees);

        return redirect('/seances')->with('succes', 'Séance programmée.');
    }

    public function update(Request $request, $id)
    {
        $seance = Seance::findOrFail($id);

        $donnees = $this->valider($request, $seance->idSeance);
        if (is_string($donnees)) {
            return back()->withInput()->with('erreur', $donnees);
        }

        $seance->update($donnees);

        return redirect('/seances')->with('succes', 'Séance modifiée.');
    }

    public function destroy($id)
    {
        try {
            Seance::findOrFail($id)->delete();
            return redirect('/seances')->with('succes', 'Séance supprimée.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect('/seances')->with('erreur', 'Impossible de supprimer : des absences sont enregistrées sur cette séance.');
        }
    }

    // Retourne les données validées, ou un message d'erreur (texte)
    private function valider(Request $request, $ignoreId = null)
    {
        $d = $request->validate([
            'Enseignement_idEnseignement' => 'required|exists:enseignement,idEnseignement',
            'Salle_idSalle' => 'required|exists:salle,idSalle',
            'dateSeance' => 'required|date',
            'heureDebutSeance' => 'required|date_format:H:i',
            'heureFinSeance' => 'required|date_format:H:i|after:heureDebutSeance',
        ], [
            'heureFinSeance.after' => "L'heure de fin doit être après l'heure de début.",
            'heureDebutSeance.date_format' => "Heure de début invalide.",
            'heureFinSeance.date_format' => "Heure de fin invalide.",
        ]);

        if ($d['heureDebutSeance'] < '06:00' || $d['heureFinSeance'] > '20:00') {
            return 'Les séances ont lieu entre 06h00 et 20h00.';
        }

        $enseignement = Enseignement::with('classe.anneeScolaire')->findOrFail($d['Enseignement_idEnseignement']);
        $annee = $enseignement->classe->anneeScolaire;

        if ($d['dateSeance'] < $annee->dateDebutAnnee || $d['dateSeance'] > $annee->dateFinAnnee) {
            return "La date doit être dans l'année scolaire (du " . $annee->dateDebutAnnee . ' au ' . $annee->dateFinAnnee . ').';
        }

        // Chevauchement : même jour, créneaux qui se croisent
        $base = Seance::where('dateSeance', $d['dateSeance'])
            ->where('heureDebutSeance', '<', $d['heureFinSeance'])
            ->where('heureFinSeance', '>', $d['heureDebutSeance']);

        if ($ignoreId) {
            $base->where('idSeance', '!=', $ignoreId);
        }

        if ((clone $base)->where('Salle_idSalle', $d['Salle_idSalle'])->exists()) {
            return 'Conflit : cette salle est déjà occupée sur ce créneau.';
        }

        if ((clone $base)->whereHas('enseignement', fn ($q) =>
                $q->where('Enseignant_idEnseignant', $enseignement->Enseignant_idEnseignant))->exists()) {
            return 'Conflit : cet enseignant a déjà une séance sur ce créneau.';
        }

        if ((clone $base)->whereHas('enseignement', fn ($q) =>
                $q->where('Classe_idClasse', $enseignement->Classe_idClasse))->exists()) {
            return 'Conflit : cette classe a déjà une séance sur ce créneau.';
        }

        return $d;
    }
}