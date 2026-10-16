<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Eleve;
use App\Models\Inscription;
use App\Models\Versement;

class DashboardController extends Controller
{
    public function index()
    {
        $annee = AnneeScolaire::enCours();

        $inscriptions = $annee
            ? Inscription::with('classe')
                ->withSum('versements', 'montantVersement')
                ->whereHas('classe', fn ($q) => $q->where('AnneeScolaire_idAnnee', $annee->idAnnee))
                ->get()
            : collect();

        $nbEleves = Eleve::count();
        $nbInscrits = $inscriptions->filter(fn ($i) => $i->est_inscrit)->count();
        $nbAttente = $inscriptions->count() - $nbInscrits;

        $totalDu = $inscriptions->sum('montantDu');
        $totalVerse = $inscriptions->sum(fn ($i) => $i->total_verse);
        $reste = max(0, $totalDu - $totalVerse);
        $taux = $totalDu > 0 ? round($totalVerse * 100 / $totalDu) : 0;

        // Élèves réellement inscrits, par classe
        $parClasse = $inscriptions
            ->filter(fn ($i) => $i->est_inscrit)
            ->groupBy(fn ($i) => $i->classe->libelleClasse)
            ->map->count()
            ->sortDesc();

        $derniers = Versement::with('inscription.eleve')
            ->orderByDesc('idVersement')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'annee', 'nbEleves', 'nbInscrits', 'nbAttente',
            'totalDu', 'totalVerse', 'reste', 'taux', 'parClasse', 'derniers'
        ));
    }
}