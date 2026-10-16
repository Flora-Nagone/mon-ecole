<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Periode;
use App\Models\Versement;
use App\Services\BulletinService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class PdfController extends Controller
{
    // Reçu de versement (format A5)
    public function recu($id)
    {
        $versement = Versement::with(['inscription.eleve', 'inscription.classe'])->findOrFail($id);
        abort_if(!$versement->inscription, 404);

        return Pdf::loadView('pdf.recu', compact('versement'))
            ->setPaper('a5', 'portrait')
            ->download('recu-' . Str::slug($versement->numRecu) . '.pdf');
    }

    // Liste officielle d'une classe (élèves réellement inscrits)
    public function classe($id)
    {
        $classe = Classe::with([
                'niveau',
                'anneeScolaire',
                'inscriptions' => fn ($q) => $q->with('eleve')->withSum('versements', 'montantVersement'),
            ])->findOrFail($id);

        $inscrits = $classe->inscriptions
            ->filter(fn ($i) => $i->est_inscrit)
            ->sortBy(fn ($i) => $i->eleve->nomEleve . ' ' . $i->eleve->prenomEleve)
            ->values();

        return Pdf::loadView('pdf.classe', compact('classe', 'inscrits'))
            ->setPaper('a4', 'portrait')
            ->download('liste-' . Str::slug($classe->libelleClasse) . '.pdf');
    }

    // Bulletin d'un élève
    public function bulletin($classeId, $periodeId, $eleveId, BulletinService $service)
    {
        [$classe, $periode] = $this->charger($classeId, $periodeId);

        $annuel = $service->annuel($classe);
        $res = $annuel['parPeriode'][$periode->idPeriode];
        $r = $res['eleves'][$eleveId] ?? abort(404);

        $nom = Str::slug($r['eleve']->nomEleve . ' ' . $r['eleve']->prenomEleve);

        return Pdf::loadView('pdf.bulletin', compact('classe', 'periode', 'res', 'r', 'annuel'))
            ->setPaper('a4', 'portrait')
            ->download('bulletin-' . $nom . '-' . Str::slug($periode->libellePeriode) . '.pdf');
    }

    // Tous les bulletins d'une classe dans un seul fichier
    public function bulletins($classeId, $periodeId, BulletinService $service)
    {
        set_time_limit(180);
        ini_set('memory_limit', '512M');

        [$classe, $periode] = $this->charger($classeId, $periodeId);

        $annuel = $service->annuel($classe);
        $res = $annuel['parPeriode'][$periode->idPeriode];

        return Pdf::loadView('pdf.bulletins', compact('classe', 'periode', 'res', 'annuel'))
            ->setPaper('a4', 'portrait')
            ->download('bulletins-' . Str::slug($classe->libelleClasse) . '-' . Str::slug($periode->libellePeriode) . '.pdf');
    }

    // Feuille de notes d'une matière (format paysage)
    public function notes($id, BulletinService $service)
    {
        $data = app(NoteController::class)->donnees($id, $service);

        if (is_string($data)) {
            return redirect('/notes')->with('erreur', $data);
        }

        $ens = $data['enseignement'];

        return Pdf::loadView('pdf.notes', $data)
            ->setPaper('a4', 'landscape')
            ->download('notes-' . Str::slug($ens->classe->libelleClasse . ' ' . $ens->matiere->libelleMatiere) . '.pdf');
    }

    private function charger($classeId, $periodeId)
    {
        $classe = Classe::with(['niveau', 'anneeScolaire'])->findOrFail($classeId);
        $periode = Periode::findOrFail($periodeId);

        abort_if($periode->AnneeScolaire_idAnnee != $classe->AnneeScolaire_idAnnee, 404);

        return [$classe, $periode];
    }
}