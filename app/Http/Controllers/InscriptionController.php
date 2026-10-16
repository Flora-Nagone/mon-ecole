<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Inscription;
use App\Models\TarifNiveau;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InscriptionController extends Controller
{
    public function index()
    {
        $annee = AnneeScolaire::enCours();
        if (!$annee) {
            return redirect('/eleves')->with('erreur', "Aucune année scolaire n'est en cours.");
        }

        $inscriptions = Inscription::with(['eleve', 'classe'])
            ->withSum('versements', 'montantVersement')
            ->whereHas('classe', fn ($q) => $q->where('AnneeScolaire_idAnnee', $annee->idAnnee))
            ->orderByDesc('idInscription')
            ->get();

        return view('inscriptions.index', compact('inscriptions', 'annee'));
    }

    public function create()
    {
        $annee = AnneeScolaire::enCours();
        if (!$annee) {
            return redirect('/eleves')->with('erreur', "Aucune année scolaire n'est en cours.");
        }

        // Élèves pas encore inscrits cette année
        $eleves = Eleve::whereDoesntHave('inscriptions', function ($q) use ($annee) {
                $q->whereHas('classe', fn ($c) => $c->where('AnneeScolaire_idAnnee', $annee->idAnnee));
            })
            ->orderBy('nomEleve')
            ->get();

        $classes = Classe::with('niveau')
            ->where('AnneeScolaire_idAnnee', $annee->idAnnee)
            ->orderBy('libelleClasse')
            ->get();

        $tarifs = TarifNiveau::where('AnneeScolaire_idAnnee', $annee->idAnnee)
            ->pluck('montant', 'Niveau_idNiveau');

        return view('inscriptions.create', compact('eleves', 'classes', 'tarifs', 'annee'));
    }

    public function store(Request $request)
    {
        $annee = AnneeScolaire::enCours();
        if (!$annee) {
            return redirect('/eleves')->with('erreur', "Aucune année scolaire n'est en cours.");
        }

        $donnees = $request->validate([
            'Eleve_idEleve' => 'required|exists:eleve,idEleve',
            'Classe_idClasse' => 'required|exists:classe,idClasse',
        ]);

        $classe = Classe::findOrFail($donnees['Classe_idClasse']);

        if ($classe->AnneeScolaire_idAnnee != $annee->idAnnee) {
            return back()->withInput()->with('erreur', "Cette classe n'appartient pas à l'année en cours.");
        }

        $dejaInscrit = Inscription::where('Eleve_idEleve', $donnees['Eleve_idEleve'])
            ->whereHas('classe', fn ($q) => $q->where('AnneeScolaire_idAnnee', $annee->idAnnee))
            ->exists();

        if ($dejaInscrit) {
            return back()->withInput()->with('erreur', 'Cet élève est déjà inscrit cette année.');
        }

        $montant = TarifNiveau::montantPour($classe->Niveau_idNiveau, $annee->idAnnee);
        if ($montant === null) {
            return back()->withInput()->with('erreur', "Aucun tarif n'est défini pour ce niveau.");
        }

        // Code : 2026ENE-001, 2026ENE-002...
        $prefixe = substr($annee->libelleAnnee, 0, 4) . 'ENE';
        $dernier = Inscription::where('codeInscription', 'like', $prefixe . '-%')
            ->max(DB::raw("CAST(SUBSTRING_INDEX(codeInscription, '-', -1) AS UNSIGNED)"));
        $code = $prefixe . '-' . str_pad(($dernier ?? 0) + 1, 3, '0', STR_PAD_LEFT);

        $inscription = Inscription::create([
            'codeInscription' => $code,
            'dateInscription' => now()->toDateString(),
            'statutInscription' => 'En cours',
            'montantDu' => $montant,
            'chefClasse' => 0,
            'Eleve_idEleve' => $donnees['Eleve_idEleve'],
            'Classe_idClasse' => $donnees['Classe_idClasse'],
        ]);

        return redirect('/inscriptions/' . $inscription->idInscription)
            ->with('succes', 'Dossier créé. L\'élève sera officiellement inscrit après un versement d\'au moins '
                . number_format($inscription->seuil, 0, ',', ' ') . ' FCFA.');
    }

    public function show($id)
    {
        $inscription = Inscription::with(['eleve', 'classe.niveau', 'versements'])->findOrFail($id);

        return view('inscriptions.show', compact('inscription'));
    }
}