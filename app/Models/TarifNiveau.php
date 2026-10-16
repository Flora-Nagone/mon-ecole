<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TarifNiveau extends Model
{
    protected $table = 'tarif_niveau';
    protected $primaryKey = 'idTarif';
    public $timestamps = false;

    protected $fillable = ['Niveau_idNiveau', 'AnneeScolaire_idAnnee', 'montant'];

    // Retourne le montant du niveau pour une année, ou null s'il n'est pas défini
    public static function montantPour($niveauId, $anneeId)
    {
        return static::where('Niveau_idNiveau', $niveauId)
            ->where('AnneeScolaire_idAnnee', $anneeId)
            ->value('montant');
    }
}