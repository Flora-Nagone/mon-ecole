<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function eleves()
    {
        return $this->exporterCsv('eleve', 'eleves');
    }

    public function inscriptions()
    {
        return $this->exporterCsv('inscription', 'inscriptions');
    }

    public function versements()
    {
        return $this->exporterCsv('versement', 'versements');
    }

    /**
     * Exporte toutes les colonnes d'une table en CSV lisible par Excel
     * (séparateur point-virgule, accents corrects).
     */
    private function exporterCsv(string $table, string $nomFichier): StreamedResponse
    {
        $lignes = DB::table($table)->get();
        $fichier = $nomFichier . '_' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($lignes) {
            $sortie = fopen('php://output', 'w');

            // BOM UTF-8 pour qu'Excel affiche bien les accents
            fwrite($sortie, "\xEF\xBB\xBF");

            if ($lignes->isNotEmpty()) {
                // En-têtes = noms des colonnes de la table
                fputcsv($sortie, array_keys((array) $lignes->first()), ';');

                foreach ($lignes as $ligne) {
                    fputcsv($sortie, (array) $ligne, ';');
                }
            }

            fclose($sortie);
        }, $fichier, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}