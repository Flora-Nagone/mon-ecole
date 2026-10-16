<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Periode;
use App\Services\BulletinService;
use Illuminate\Http\Request;

class BulletinController extends Controller
{
    public function index(Request $request, BulletinService $service)
    {
        $annee = AnneeScolaire::enCours();
        if (!$annee) {
            return redirect('/')->with('erreur', "Aucune année scolaire n'est en cours.");
        }

        $classes = Classe::where('AnneeScolaire_idAnnee', $annee->idAnnee)->orderBy('libelleClasse')->get();
        $periodes = Periode::where('AnneeScolaire_idAnnee', $annee->idAnnee)->orderBy('dateDebutPeriode')->get();

        $classe = $request->filled('classe') ? $classes->firstWhere('idClasse', $request->classe) : null;
        $periode = $request->filled('periode') ? $periodes->firstWhere('idPeriode', $request->periode) : null;

        $annuel = null;
        $res = null;
        if ($classe && $periode) {
            $annuel = $service->annuel($classe);
            $res = $annuel['parPeriode'][$periode->idPeriode];
        }

        return view('bulletins.index', compact('annee', 'classes', 'periodes', 'classe', 'periode', 'res', 'annuel'));
    }

    public function eleve($classeId, $periodeId, $eleveId, BulletinService $service)
    {
        [$classe, $periode] = $this->charger($classeId, $periodeId);

        $annuel = $service->annuel($classe);
        $res = $annuel['parPeriode'][$periode->idPeriode];
        $r = $res['eleves'][$eleveId] ?? abort(404);

        return view('bulletins.eleve', compact('classe', 'periode', 'res', 'r', 'annuel'));
    }

    public function tous($classeId, $periodeId, BulletinService $service)
    {
        [$classe, $periode] = $this->charger($classeId, $periodeId);

        $annuel = $service->annuel($classe);
        $res = $annuel['parPeriode'][$periode->idPeriode];

        return view('bulletins.tous', compact('classe', 'periode', 'res', 'annuel'));
    }

    private function charger($classeId, $periodeId)
    {
        $classe = Classe::with(['niveau', 'anneeScolaire'])->findOrFail($classeId);
        $periode = Periode::findOrFail($periodeId);

        abort_if($periode->AnneeScolaire_idAnnee != $classe->AnneeScolaire_idAnnee, 404);

        return [$classe, $periode];
    }
}