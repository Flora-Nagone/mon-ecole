<?php

namespace App\Http\Controllers;

use App\Models\Eleve;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EleveController extends Controller
{
    public function index(Request $request)
    {
        $query = Eleve::orderBy('nomEleve');

        if ($request->q) {
            $query->where(function ($q) use ($request) {
                $q->where('nomEleve', 'like', '%' . $request->q . '%')
                  ->orWhere('prenomEleve', 'like', '%' . $request->q . '%')
                  ->orWhere('matriculeEleve', 'like', '%' . $request->q . '%');
            });
        }

        return view('eleves', ['eleves' => $query->get()]);
    }

    public function create()
    {
        return view('create-eleve');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->regles(), $this->messages());

        // Le matricule est calculé, jamais saisi
        $validated['matriculeEleve'] = $this->genererMatricule(
            $validated['nomEleve'],
            $validated['prenomEleve'],
            $validated['dateNaissance']
        );

        $eleve = Eleve::create($validated);

        return redirect('/inscriptions/create?eleve=' . $eleve->idEleve)->with('succes',
            'Élève ajouté (matricule ' . $eleve->matriculeEleve . '). Choisissez sa classe pour créer son dossier d\'inscription.');
    }

    public function edit($id)
    {
        $eleve = Eleve::findOrFail($id);
        return view('edit-eleve', ['eleve' => $eleve]);
    }

    public function update(Request $request, $id)
    {
        $eleve = Eleve::findOrFail($id);

        // Le matricule n'est pas modifiable : il n'est donc pas validé ici
        $validated = $request->validate($this->regles(), $this->messages());

        $eleve->update($validated);

        return redirect('/eleves')->with('succes', 'Élève modifié avec succès.');
    }

    public function destroy($id)
    {
        try {
            Eleve::findOrFail($id)->delete();
            return redirect('/eleves')->with('succes', 'Élève supprimé.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect('/eleves')->with('erreur', 'Impossible de supprimer : cet élève a une inscription (ou d\'autres données) liée.');
        }
    }

    // Règles de validation communes à l'ajout et à la modification
    private function regles()
    {
        $min = now()->subYears(30)->toDateString();
        $max = now()->subYears(3)->toDateString();

        return [
            'nomEleve' => 'required|string|max:45',
            'prenomEleve' => 'required|string|max:100',
            'sexeEleve' => 'required|in:M,F',
            'dateNaissance' => "required|date|after_or_equal:$min|before_or_equal:$max",
            'lieuNaissance' => 'required|string|max:45',
            'adresseEleve' => 'required|string|max:45',
            'telephoneEleve' => 'nullable|string|max:45',
        ];
    }

    private function messages()
    {
        return [
            'dateNaissance.date' => 'La date de naissance n\'est pas valide.',
            'dateNaissance.before_or_equal' => 'Date de naissance incohérente : un élève doit avoir au moins 3 ans (pas de date dans le futur).',
            'dateNaissance.after_or_equal' => 'Date de naissance incohérente : un élève ne peut pas avoir plus de 30 ans.',
        ];
    }

    /**
     * Matricule : 3 lettres du nom + initiale du prénom + JJMMAA + ordre sur 3 chiffres
     * Exemple : Zika Lou Laurelle, née le 06/12/2014 -> ZIKL061214010
     */
    private function genererMatricule($nom, $prenom, $dateNaissance)
    {
        $lettresNom = preg_replace('/[^A-Z]/', '', Str::upper(Str::ascii($nom)));
        $lettresPrenom = preg_replace('/[^A-Z]/', '', Str::upper(Str::ascii($prenom)));

        $debutNom = str_pad(substr($lettresNom, 0, 3), 3, 'X');
        $initiale = substr($lettresPrenom, 0, 1) ?: 'X';
        $date = \Carbon\Carbon::parse($dateNaissance)->format('dmy');

        $dernier = Eleve::max(DB::raw('CAST(RIGHT(matriculeEleve, 3) AS UNSIGNED)'));
        $ordre = str_pad(($dernier ?? 0) + 1, 3, '0', STR_PAD_LEFT);

        return $debutNom . $initiale . $date . $ordre;
    }
}