<?php

namespace App\Http\Controllers;

use App\Models\Domaine;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DomaineController extends Controller
{
    public function index(Request $request)
    {
        $domaines = Domaine::orderBy('libelleDomaine')->get();
        $edition = $request->filled('edit') ? Domaine::find($request->edit) : null;

        return view('domaines', compact('domaines', 'edition'));
    }

    public function store(Request $request)
    {
        $donnees = $request->validate([
            'libelleDomaine' => 'required|string|max:45|unique:domaine,libelleDomaine',
        ], ['libelleDomaine.unique' => 'Ce domaine existe déjà.']);

        Domaine::create($donnees);

        return redirect('/domaines')->with('succes', 'Domaine ajouté.');
    }

    public function update(Request $request, $id)
    {
        $domaine = Domaine::findOrFail($id);

        $donnees = $request->validate([
            'libelleDomaine' => [
                'required', 'string', 'max:45',
                Rule::unique('domaine', 'libelleDomaine')->ignore($domaine->idDomaine, 'idDomaine'),
            ],
        ], ['libelleDomaine.unique' => 'Ce domaine existe déjà.']);

        $domaine->update($donnees);

        return redirect('/domaines')->with('succes', 'Domaine modifié.');
    }

    public function destroy($id)
    {
        try {
            Domaine::findOrFail($id)->delete();
            return redirect('/domaines')->with('succes', 'Domaine supprimé.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect('/domaines')->with('erreur', 'Impossible de supprimer : des enseignants ou des matières utilisent ce domaine.');
        }
    }
}