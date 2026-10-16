<?php

namespace App\Http\Controllers;

use App\Models\Eleve;
use App\Models\ParentEleve;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ParentController extends Controller
{
    const LIENS = ['Père', 'Mère', 'Tuteur', 'Tutrice', 'Autre'];

    public function index()
    {
        $parents = ParentEleve::with('eleves')->orderBy('nomParent')->get();

        return view('parents.index', compact('parents'));
    }

    public function create()
    {
        return view('parents.form', [
            'parent' => null,
            'eleves' => Eleve::orderBy('nomEleve')->get(),
            'liens' => self::LIENS,
        ]);
    }

    public function store(Request $request)
    {
        $donnees = $request->validate([
            'nomParent' => 'required|string|max:45',
            'prenomParent' => 'required|string|max:100',
            'telephoneParent' => 'required|string|max:15|unique:parent,telephoneParent',
            'emailParent' => 'required|email|max:100',
            'adresseParent' => 'required|string|max:45',
            'professionParent' => 'nullable|string|max:45',
            'Eleve_idEleve' => 'required|exists:eleve,idEleve',
            'lienParente' => ['required', Rule::in(self::LIENS)],
        ], [
            'telephoneParent.unique' => 'Ce numéro existe déjà : ouvrez la fiche du parent pour lui rattacher un autre élève.',
            'Eleve_idEleve.required' => 'Un parent doit être responsable d\'au moins un élève.',
        ]);

        // Le parent et son lien avec l'élève sont créés ensemble, ou pas du tout
        $parent = DB::transaction(function () use ($donnees) {
            $parent = ParentEleve::create([
                'nomParent' => $donnees['nomParent'],
                'prenomParent' => $donnees['prenomParent'],
                'telephoneParent' => $donnees['telephoneParent'],
                'emailParent' => $donnees['emailParent'],
                'adresseParent' => $donnees['adresseParent'],
                'professionParent' => $donnees['professionParent'] ?? null,
            ]);

            $parent->eleves()->attach($donnees['Eleve_idEleve'], ['lienParente' => $donnees['lienParente']]);

            return $parent;
        });

        return redirect('/parents/' . $parent->idParent)->with('succes', 'Parent enregistré.');
    }

    public function edit($id)
    {
        return view('parents.form', [
            'parent' => ParentEleve::findOrFail($id),
            'eleves' => collect(),
            'liens' => self::LIENS,
        ]);
    }

    public function update(Request $request, $id)
    {
        $parent = ParentEleve::findOrFail($id);

        $donnees = $request->validate([
            'nomParent' => 'required|string|max:45',
            'prenomParent' => 'required|string|max:100',
            'telephoneParent' => [
                'required', 'string', 'max:15',
                Rule::unique('parent', 'telephoneParent')->ignore($parent->idParent, 'idParent'),
            ],
            'emailParent' => 'required|email|max:100',
            'adresseParent' => 'required|string|max:45',
            'professionParent' => 'nullable|string|max:45',
        ]);

        $parent->update($donnees);

        return redirect('/parents/' . $parent->idParent)->with('succes', 'Parent modifié.');
    }

    public function show($id)
    {
        $parent = ParentEleve::with('eleves')->findOrFail($id);

        // Élèves pas encore rattachés à ce parent
        $disponibles = Eleve::whereNotIn('idEleve', $parent->eleves->pluck('idEleve'))
            ->orderBy('nomEleve')
            ->get();

        return view('parents.show', [
            'parent' => $parent,
            'disponibles' => $disponibles,
            'liens' => self::LIENS,
        ]);
    }

    // Rattacher un autre élève à un parent existant
    public function rattacher(Request $request, $id)
    {
        $parent = ParentEleve::findOrFail($id);

        $donnees = $request->validate([
            'Eleve_idEleve' => 'required|exists:eleve,idEleve',
            'lienParente' => ['required', Rule::in(self::LIENS)],
        ]);

        $parent->eleves()->syncWithoutDetaching([
            $donnees['Eleve_idEleve'] => ['lienParente' => $donnees['lienParente']],
        ]);

        return redirect('/parents/' . $parent->idParent)->with('succes', 'Élève rattaché.');
    }

    // Retirer un élève : interdit s'il est le dernier
    public function retirer($id, $eleveId)
    {
        $parent = ParentEleve::findOrFail($id);

        if ($parent->eleves()->count() <= 1) {
            return redirect('/parents/' . $parent->idParent)->with('erreur',
                'Un parent doit rester responsable d\'au moins un élève. Supprimez le parent à la place.');
        }

        $parent->eleves()->detach($eleveId);

        return redirect('/parents/' . $parent->idParent)->with('succes', 'Élève retiré.');
    }

    public function destroy($id)
    {
        $parent = ParentEleve::findOrFail($id);

        try {
            DB::transaction(function () use ($parent) {
                $parent->eleves()->detach();
                $parent->delete();
            });
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect('/parents')->with('erreur', 'Impossible de supprimer ce parent.');
        }

        return redirect('/parents')->with('succes', 'Parent supprimé.');
    }
}