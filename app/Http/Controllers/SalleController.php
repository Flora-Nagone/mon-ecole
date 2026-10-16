<?php

namespace App\Http\Controllers;

use App\Models\Salle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SalleController extends Controller
{
    public function index(Request $request)
    {
        $salles = Salle::orderBy('libelleSalle')->get();
        $edition = $request->filled('edit') ? Salle::find($request->edit) : null;

        return view('salles', compact('salles', 'edition'));
    }

    public function store(Request $request)
    {
        $donnees = $request->validate([
            'libelleSalle' => 'required|string|max:45|unique:salle,libelleSalle',
            'capacite' => 'nullable|integer|min:1|max:500',
        ], [
            'libelleSalle.unique' => 'Une salle porte déjà ce nom.',
        ]);

        Salle::create($donnees);

        return redirect('/salles')->with('succes', 'Salle ajoutée.');
    }

    public function update(Request $request, $id)
    {
        $salle = Salle::findOrFail($id);

        $donnees = $request->validate([
            'libelleSalle' => [
                'required', 'string', 'max:45',
                Rule::unique('salle', 'libelleSalle')->ignore($salle->idSalle, 'idSalle'),
            ],
            'capacite' => 'nullable|integer|min:1|max:500',
        ], [
            'libelleSalle.unique' => 'Une salle porte déjà ce nom.',
        ]);

        $salle->update($donnees);

        return redirect('/salles')->with('succes', 'Salle modifiée.');
    }

    public function destroy($id)
    {
        try {
            Salle::findOrFail($id)->delete();
            return redirect('/salles')->with('succes', 'Salle supprimée.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect('/salles')->with('erreur', 'Impossible de supprimer : cette salle est utilisée par des séances.');
        }
    }
}