<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Niveau;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClasseController extends Controller
{
    public function index()
    {
        $annee = AnneeScolaire::enCours();
        if (!$annee) {
            return redirect('/')->with('erreur', "Aucune année scolaire n'est en cours.");
        }

        $classes = Classe::with([
                'niveau',
                'inscriptions' => fn ($q) => $q->withSum('versements', 'montantVersement'),
            ])
            ->where('AnneeScolaire_idAnnee', $annee->idAnnee)
            ->orderBy('Niveau_idNiveau')
            ->orderBy('libelleClasse')
            ->get();

        return view('classes.index', compact('classes', 'annee'));
    }

    public function create()
    {
        return view('classes.form', [
            'classe' => null,
            'niveaux' => Niveau::orderBy('idNiveau')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $annee = AnneeScolaire::enCours();
        if (!$annee) {
            return redirect('/')->with('erreur', "Aucune année scolaire n'est en cours.");
        }

        $donnees = $request->validate([
            'libelleClasse' => [
                'required', 'string', 'max:45',
                Rule::unique('classe', 'libelleClasse')->where('AnneeScolaire_idAnnee', $annee->idAnnee),
            ],
            'Niveau_idNiveau' => 'required|exists:niveau,idNiveau',
        ], [
            'libelleClasse.unique' => 'Une classe porte déjà ce nom cette année.',
        ]);

        $niveau = Niveau::findOrFail($donnees['Niveau_idNiveau']);

        // Le code contient l'identifiant : on crée avec un code provisoire, puis on le calcule
        DB::transaction(function () use ($donnees, $annee, $niveau) {
            $classe = Classe::create([
                'codeClasse' => 'T' . mt_rand(1000000, 9999999),
                'libelleClasse' => $donnees['libelleClasse'],
                'Niveau_idNiveau' => $donnees['Niveau_idNiveau'],
                'AnneeScolaire_idAnnee' => $annee->idAnnee,
            ]);

            $classe->update([
                'codeClasse' => $this->genererCode($niveau->libelleNiveau, $annee->libelleAnnee, $classe->idClasse),
            ]);
        });

        return redirect('/classes')->with('succes', 'Classe créée.');
    }

    public function edit($id)
    {
        return view('classes.form', [
            'classe' => Classe::findOrFail($id),
            'niveaux' => Niveau::orderBy('idNiveau')->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $classe = Classe::withCount('inscriptions')->findOrFail($id);

        $donnees = $request->validate([
            'libelleClasse' => [
                'required', 'string', 'max:45',
                Rule::unique('classe', 'libelleClasse')
                    ->where('AnneeScolaire_idAnnee', $classe->AnneeScolaire_idAnnee)
                    ->ignore($classe->idClasse, 'idClasse'),
            ],
            'Niveau_idNiveau' => 'required|exists:niveau,idNiveau',
        ], [
            'libelleClasse.unique' => 'Une classe porte déjà ce nom cette année.',
        ]);

        if ($classe->inscriptions_count > 0 && $donnees['Niveau_idNiveau'] != $classe->Niveau_idNiveau) {
            return back()->withInput()->with('erreur',
                'Le niveau ne peut plus être changé : des élèves sont déjà inscrits (leur tarif en dépend).');
        }

        $classe->update($donnees);

        return redirect('/classes')->with('succes', 'Classe modifiée.');
    }

    public function show($id)
    {
        $classe = Classe::with([
                'niveau',
                'anneeScolaire',
                'inscriptions' => fn ($q) => $q->with('eleve')->withSum('versements', 'montantVersement'),
            ])->findOrFail($id);

        $inscrits = $classe->inscriptions
            ->filter(fn ($i) => $i->est_inscrit)
            ->sortBy(fn ($i) => $i->eleve->nomEleve . ' ' . $i->eleve->prenomEleve)
            ->values();

        $enAttente = $classe->inscriptions
            ->filter(fn ($i) => !$i->est_inscrit)
            ->sortBy(fn ($i) => $i->eleve->nomEleve)
            ->values();

        return view('classes.show', compact('classe', 'inscrits', 'enAttente'));
    }

    public function destroy($id)
    {
        try {
            Classe::findOrFail($id)->delete();
            return redirect('/classes')->with('succes', 'Classe supprimée.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect('/classes')->with('erreur',
                'Impossible de supprimer : cette classe a des inscriptions ou des enseignements liés.');
        }
    }

    /**
     * Code : 3 caractères du niveau + année sur 2 chiffres + identifiant sur 3 chiffres
     * Exemple : 6e, 2026-2027, id 1 -> 6E-26-001
     */
    private function genererCode($libelleNiveau, $libelleAnnee, $id)
    {
        $niveau = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $libelleNiveau), 0, 3));
        $annee = substr($libelleAnnee, 2, 2);

        return $niveau . '-' . $annee . '-' . str_pad($id, 3, '0', STR_PAD_LEFT);
    }
}