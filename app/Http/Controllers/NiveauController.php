<?php

namespace App\Http\Controllers;

use App\Models\Niveau;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NiveauController extends Controller
{
    public function index(Request $request)
    {
        $niveaux = Niveau::orderBy('idNiveau')->get();
        $edition = $request->filled('edit') ? Niveau::find($request->edit) : null;

        return view('niveaux', compact('niveaux', 'edition'));
    }

    public function store(Request $request)
    {
        $donnees = $request->validate([
            'libelleNiveau' => 'required|string|max:45|unique:niveau,libelleNiveau',
        ], [
            'libelleNiveau.unique' => 'Ce niveau existe déjà.',
        ]);

        Niveau::create($donnees);

        return redirect('/niveaux')->with('succes', 'Niveau ajouté. Pensez à définir son tarif dans « Tarifs ».');
    }

    public function update(Request $request, $id)
    {
        $niveau = Niveau::findOrFail($id);

        $donnees = $request->validate([
            'libelleNiveau' => [
                'required', 'string', 'max:45',
                Rule::unique('niveau', 'libelleNiveau')->ignore($niveau->idNiveau, 'idNiveau'),
            ],
        ], [
            'libelleNiveau.unique' => 'Ce niveau existe déjà.',
        ]);

        $niveau->update($donnees);

        return redirect('/niveaux')->with('succes', 'Niveau modifié.');
    }

    public function destroy($id)
    {
        try {
            Niveau::findOrFail($id)->delete();
            return redirect('/niveaux')->with('succes', 'Niveau supprimé.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect('/niveaux')->with('erreur',
                'Impossible de supprimer : ce niveau est utilisé par des classes, des tarifs ou des coefficients.');
        }
    }
}