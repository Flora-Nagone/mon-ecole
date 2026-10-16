<?php

namespace App\Http\Controllers;

use App\Models\CoefficientMatiere;
use App\Models\Matiere;
use App\Models\Niveau;
use Illuminate\Http\Request;

class CoefficientController extends Controller
{
    public function index(Request $request)
    {
        return view('coefficients', [
            'niveaux' => Niveau::orderBy('idNiveau')->get(),
            'matieres' => Matiere::orderBy('libelleMatiere')->get(),
            'parNiveau' => CoefficientMatiere::with('matiere')->get()->groupBy('Niveau_idNiveau'),
            'edition' => $request->filled('edit')
                ? CoefficientMatiere::with(['niveau', 'matiere'])->find($request->edit)
                : null,
        ]);
    }

    // Crée le coefficient, ou remplace celui du même niveau et de la même matière
    public function store(Request $request)
    {
        $donnees = $request->validate([
            'Niveau_idNiveau' => 'required|exists:niveau,idNiveau',
            'Matiere_idMatiere' => 'required|exists:matiere,idMatiere',
            'coefficient' => 'required|integer|min:1|max:20',
        ], [
            'coefficient.max' => 'Un coefficient ne dépasse pas 20.',
            'coefficient.min' => 'Un coefficient est au minimum de 1.',
        ]);

        CoefficientMatiere::updateOrCreate(
            ['Niveau_idNiveau' => $donnees['Niveau_idNiveau'], 'Matiere_idMatiere' => $donnees['Matiere_idMatiere']],
            ['coefficient' => $donnees['coefficient']]
        );

        return redirect('/coefficients')->with('succes', 'Coefficient enregistré.');
    }

    public function destroy($id)
    {
        CoefficientMatiere::findOrFail($id)->delete();

        return redirect('/coefficients')->with('succes', 'Coefficient supprimé.');
    }
}