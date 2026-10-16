<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Enseignant;
use App\Models\Enseignement;
use App\Models\Matiere;
use Illuminate\Http\Request;

class EnseignementController extends Controller
{
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

        return view('enseignements', [
            'annee' => $annee,
            'enseignements' => $enseignements,
            'classes' => Classe::where('AnneeScolaire_idAnnee', $annee->idAnnee)->orderBy('libelleClasse')->get(),
            'matieres' => Matiere::orderBy('libelleMatiere')->get(),
            'enseignants' => Enseignant::orderBy('nomEnseignant')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $donnees = $request->validate([
            'Classe_idClasse' => 'required|exists:classe,idClasse',
            'Matiere_idMatiere' => 'required|exists:matiere,idMatiere',
            'Enseignant_idEnseignant' => 'required|exists:enseignant,idEnseignant',
        ]);

        $matiere = Matiere::findOrFail($donnees['Matiere_idMatiere']);
        $enseignant = Enseignant::findOrFail($donnees['Enseignant_idEnseignant']);

        if ($matiere->Domaine_idDomaine != $enseignant->Domaine_idDomaine) {
            return back()->withInput()->with('erreur',
                'Cet enseignant ne peut pas enseigner une matière hors de son domaine.');
        }

        $existe = Enseignement::where('Matiere_idMatiere', $donnees['Matiere_idMatiere'])
            ->where('Classe_idClasse', $donnees['Classe_idClasse'])
            ->exists();

        if ($existe) {
            return back()->withInput()->with('erreur', 'Cette matière a déjà un enseignant dans cette classe.');
        }

        try {
            Enseignement::create($donnees);
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->withInput()->with('erreur', 'Enregistrement refusé par la base : vérifiez le domaine de l\'enseignant.');
        }

        return redirect('/enseignements')->with('succes', 'Enseignement ajouté.');
    }

    public function destroy($id)
    {
        try {
            Enseignement::findOrFail($id)->delete();
            return redirect('/enseignements')->with('succes', 'Enseignement supprimé.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect('/enseignements')->with('erreur', 'Impossible de supprimer : des séances ou des évaluations en dépendent.');
        }
    }
}