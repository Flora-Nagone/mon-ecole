<?php

namespace App\Http\Controllers;

use App\Models\Domaine;
use App\Models\Enseignement;
use App\Models\Matiere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MatiereController extends Controller
{
    public function index(Request $request)
    {
        return view('matieres', [
            'matieres' => Matiere::with('domaine')->orderBy('codeMatiere')->get(),
            'domaines' => Domaine::orderBy('libelleDomaine')->get(),
            'edition' => $request->filled('edit') ? Matiere::find($request->edit) : null,
        ]);
    }

    public function store(Request $request)
    {
        $donnees = $request->validate($this->regles());
        $domaine = Domaine::findOrFail($donnees['Domaine_idDomaine']);

        // Le code contient l'identifiant : code provisoire, puis calcul
        DB::transaction(function () use ($donnees, $domaine) {
            $matiere = Matiere::create($donnees + ['codeMatiere' => 'T' . mt_rand(100000, 999999)]);
            $matiere->update(['codeMatiere' => $this->genererCode($domaine->libelleDomaine, $matiere->idMatiere)]);
        });

        return redirect('/matieres')->with('succes', 'Matière ajoutée.');
    }

    public function update(Request $request, $id)
    {
        $matiere = Matiere::findOrFail($id);
        $donnees = $request->validate($this->regles());

        if ($donnees['Domaine_idDomaine'] != $matiere->Domaine_idDomaine
            && Enseignement::where('Matiere_idMatiere', $id)->exists()) {
            return back()->withInput()->with('erreur',
                'Le domaine ne peut plus être changé : cette matière est déjà enseignée.');
        }

        $matiere->update($donnees);

        return redirect('/matieres')->with('succes', 'Matière modifiée.');
    }

    public function destroy($id)
    {
        try {
            Matiere::findOrFail($id)->delete();
            return redirect('/matieres')->with('succes', 'Matière supprimée.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect('/matieres')->with('erreur', 'Impossible de supprimer : cette matière a des coefficients ou des enseignements.');
        }
    }

    private function regles()
    {
        return [
            'libelleMatiere' => 'required|string|max:25',
            'Domaine_idDomaine' => 'required|exists:domaine,idDomaine',
        ];
    }

    // Exemple : SCI-014
    private function genererCode($libelleDomaine, $id)
    {
        $lettres = preg_replace('/[^A-Z]/', '', Str::upper(Str::ascii($libelleDomaine)));

        return str_pad(substr($lettres, 0, 3), 3, 'X') . '-' . str_pad($id, 3, '0', STR_PAD_LEFT);
    }
}