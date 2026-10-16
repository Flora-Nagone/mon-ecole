<?php

namespace App\Http\Controllers;

use App\Models\Inscription;
use App\Models\Versement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VersementController extends Controller
{
    public function store(Request $request, $id)
    {
        $inscription = Inscription::findOrFail($id);

        $donnees = $request->validate([
            'montantVersement' => 'required|integer|min:10000',
            'modePaiement' => 'required|in:Espèces,Mobile Money,Virement,Chèque',
            'observationVersement' => 'nullable|string|max:45',
        ], [
            'montantVersement.min' => 'Un versement doit être de 10 000 FCFA minimum.',
        ]);

        $etaitInscrit = $inscription->est_inscrit;
        $reste = $inscription->reste_a_payer;

        if ($donnees['montantVersement'] > $reste) {
            return back()->withInput()->with('erreur',
                'Le versement dépasse le reste à payer (' . number_format($reste, 0, ',', ' ') . ' FCFA).');
        }

        // Numéro de reçu : REC-2026-001, REC-2026-002...
        $dernier = Versement::max(DB::raw("CAST(SUBSTRING_INDEX(numRecu, '-', -1) AS UNSIGNED)"));
        $numRecu = 'REC-' . date('Y') . '-' . str_pad(($dernier ?? 0) + 1, 3, '0', STR_PAD_LEFT);

        $versement = Versement::create([
            'dateVersement' => now()->toDateString(),
            'montantVersement' => $donnees['montantVersement'],
            'modePaiement' => $donnees['modePaiement'],
            'numRecu' => $numRecu,
            'observationVersement' => $donnees['observationVersement'] ?? null,
            'Inscription_idInscription' => $inscription->idInscription,
        ]);

        $message = 'Versement enregistré (reçu ' . $versement->numRecu . ').';
        if (!$etaitInscrit && $inscription->est_inscrit) {
            $message .= ' L\'élève est maintenant officiellement inscrit.';
        }

        return redirect('/inscriptions/' . $inscription->idInscription)->with('succes', $message);
    }

    public function recu($id)
    {
        $versement = Versement::with(['inscription.eleve', 'inscription.classe'])->findOrFail($id);
        abort_if(!$versement->inscription, 404);

        return view('recu', compact('versement'));
    }
}