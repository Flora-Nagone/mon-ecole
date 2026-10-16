<?php

namespace App\Http\Controllers;

use App\Models\TypeEvaluation;
use Illuminate\Http\Request;

class TypeEvaluationController extends Controller
{
    public function index(Request $request)
    {
        $types = TypeEvaluation::orderBy('libelleTypeEvaluation')->get();
        $edition = $request->filled('edit') ? TypeEvaluation::find($request->edit) : null;
        $total = $types->sum('ponderation');

        return view('types-evaluation', compact('types', 'edition', 'total'));
    }

    public function store(Request $request)
    {
        TypeEvaluation::create($this->valider($request));

        return redirect('/types-evaluation')->with('succes', 'Type d\'évaluation ajouté.');
    }

    public function update(Request $request, $id)
    {
        TypeEvaluation::findOrFail($id)->update($this->valider($request));

        return redirect('/types-evaluation')->with('succes', 'Type d\'évaluation modifié.');
    }

    public function destroy($id)
    {
        try {
            TypeEvaluation::findOrFail($id)->delete();
            return redirect('/types-evaluation')->with('succes', 'Type supprimé.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect('/types-evaluation')->with('erreur', 'Impossible de supprimer : des évaluations utilisent ce type.');
        }
    }

    private function valider(Request $request)
    {
        return $request->validate([
            'libelleTypeEvaluation' => 'required|string|max:45',
            'ponderation' => 'required|integer|min:0|max:100',
        ], [
            'ponderation.max' => 'La pondération est un pourcentage (0 à 100).',
        ]);
    }
}