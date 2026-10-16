<?php

namespace App\Http\Controllers;

use App\Models\Domaine;
use App\Models\Enseignant;
use App\Models\Enseignement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnseignantController extends Controller
{
    const STATUTS = ['Titulaire', 'Vacataire', 'Contractuel'];

    public function index(Request $request)
    {
        return view('enseignants', [
            'enseignants' => Enseignant::with('domaine')->orderBy('nomEnseignant')->get(),
            'domaines' => Domaine::orderBy('libelleDomaine')->get(),
            'edition' => $request->filled('edit') ? Enseignant::find($request->edit) : null,
            'statuts' => self::STATUTS,
        ]);
    }

    public function store(Request $request)
    {
        $donnees = $request->validate($this->regles(), $this->messages());

        $donnees['matriculeEnseignant'] = $this->genererMatricule($donnees['nomEnseignant']);

        $enseignant = Enseignant::create($donnees);

        return redirect('/enseignants')->with('succes',
            'Enseignant ajouté. Matricule : ' . $enseignant->matriculeEnseignant);
    }

    public function update(Request $request, $id)
    {
        $enseignant = Enseignant::findOrFail($id);
        $donnees = $request->validate($this->regles(), $this->messages());

        if ($donnees['Domaine_idDomaine'] != $enseignant->Domaine_idDomaine
            && Enseignement::where('Enseignant_idEnseignant', $id)->exists()) {
            return back()->withInput()->with('erreur',
                'Le domaine ne peut plus être changé : cet enseignant a déjà des enseignements.');
        }

        $enseignant->update($donnees);

        return redirect('/enseignants')->with('succes', 'Enseignant modifié.');
    }

    public function destroy($id)
    {
        try {
            Enseignant::findOrFail($id)->delete();
            return redirect('/enseignants')->with('succes', 'Enseignant supprimé.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect('/enseignants')->with('erreur', 'Impossible de supprimer : cet enseignant a des enseignements.');
        }
    }

    private function regles()
    {
        return [
            'nomEnseignant' => 'required|string|max:100',
            'prenomEnseignant' => 'required|string|max:100',
            'sexeEnseignant' => 'required|in:M,F',
            'statutEnseignant' => 'required|in:' . implode(',', self::STATUTS),
            'emailEnseignant' => 'required|email|max:150',
            'contactEnseignant' => ['required', 'max:15', 'regex:/^[0-9 +]{8,15}$/'],
            'Domaine_idDomaine' => 'required|exists:domaine,idDomaine',
        ];
    }

    private function messages()
    {
        return ['contactEnseignant.regex' => 'Numéro invalide (8 à 15 chiffres).'];
    }

    // Exemple : 27092026ENE001-D (date du jour + ENE + ordre + initiale du nom)
    private function genererMatricule($nom)
    {
        $lettres = preg_replace('/[^A-Z]/', '', Str::upper(Str::ascii($nom)));
        $initiale = substr($lettres, 0, 1) ?: 'X';

        $dernier = Enseignant::max(DB::raw('CAST(SUBSTRING(matriculeEnseignant, 12, 3) AS UNSIGNED)'));
        $ordre = str_pad(($dernier ?? 0) + 1, 3, '0', STR_PAD_LEFT);

        return now()->format('dmY') . 'ENE' . $ordre . '-' . $initiale;
    }
}