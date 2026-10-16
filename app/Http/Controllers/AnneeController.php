<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AnneeController extends Controller
{
    public function index(Request $request)
    {
        $annees = AnneeScolaire::orderByDesc('dateDebutAnnee')->get();
        $edition = $request->filled('edit') ? AnneeScolaire::find($request->edit) : null;

        return view('annees', compact('annees', 'edition'));
    }

    public function store(Request $request)
    {
        $donnees = $request->validate($this->regles(), $this->messages());
        $enCours = $request->boolean('anneeEnCours');

        DB::transaction(function () use ($donnees, $enCours) {
            if ($enCours) {
                AnneeScolaire::query()->update(['anneeEnCours' => 0]);
            }
            AnneeScolaire::create($donnees + ['anneeEnCours' => $enCours ? 1 : 0]);
        });

        return redirect('/annees')->with('succes', 'Année scolaire ajoutée.');
    }

    public function update(Request $request, $id)
    {
        $annee = AnneeScolaire::findOrFail($id);
        $donnees = $request->validate($this->regles($annee->idAnnee), $this->messages());

        // Une année en cours ne peut pas être « désactivée » : on active une autre année à la place
        $enCours = $request->boolean('anneeEnCours') || $annee->anneeEnCours;

        DB::transaction(function () use ($annee, $donnees, $enCours) {
            if ($enCours) {
                AnneeScolaire::where('idAnnee', '!=', $annee->idAnnee)->update(['anneeEnCours' => 0]);
            }
            $annee->update($donnees + ['anneeEnCours' => $enCours ? 1 : 0]);
        });

        return redirect('/annees')->with('succes', 'Année scolaire modifiée.');
    }

    public function activer($id)
    {
        $annee = AnneeScolaire::findOrFail($id);

        DB::transaction(function () use ($annee) {
            AnneeScolaire::query()->update(['anneeEnCours' => 0]);
            $annee->update(['anneeEnCours' => 1]);
        });

        return redirect('/annees')->with('succes', $annee->libelleAnnee . ' est maintenant l\'année en cours.');
    }

    public function destroy($id)
    {
        $annee = AnneeScolaire::findOrFail($id);

        if ($annee->anneeEnCours) {
            return redirect('/annees')->with('erreur', "Impossible de supprimer l'année en cours.");
        }

        try {
            $annee->delete();
            return redirect('/annees')->with('succes', 'Année supprimée.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect('/annees')->with('erreur',
                'Impossible de supprimer : cette année a des classes ou des périodes.');
        }
    }

    private function regles($ignoreId = null)
    {
        return [
            'libelleAnnee' => [
                'bail', 'required', 'regex:/^\d{4}-\d{4}$/',
                Rule::unique('anneescolaire', 'libelleAnnee')->ignore($ignoreId, 'idAnnee'),
                function ($attribut, $valeur, $echec) {
                    [$a, $b] = explode('-', $valeur);
                    if ((int) $b !== (int) $a + 1) {
                        $echec('Les deux années doivent se suivre (exemple : 2026-2027).');
                    }
                },
            ],
            'dateDebutAnnee' => 'required|date',
            'dateFinAnnee' => 'required|date|after:dateDebutAnnee',
        ];
    }

    private function messages()
    {
        return [
            'libelleAnnee.regex' => 'Format attendu : 2026-2027.',
            'libelleAnnee.unique' => 'Cette année scolaire existe déjà.',
            'dateFinAnnee.after' => 'La date de fin doit être après la date de début.',
        ];
    }
}