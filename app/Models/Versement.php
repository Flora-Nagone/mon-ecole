<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Versement extends Model
{
    protected $table = 'versement';
    protected $primaryKey = 'idVersement';
    public $timestamps = false;

    protected $fillable = [
        'dateVersement', 'montantVersement', 'modePaiement', 'numRecu',
        'observationVersement', 'Inscription_idInscription', 'Eleve_idEleve',
    ];

    public function inscription()
    {
        return $this->belongsTo(Inscription::class, 'Inscription_idInscription', 'idInscription');
    }
}