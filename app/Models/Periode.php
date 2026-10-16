<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periode extends Model
{
    protected $table = 'periode';
    protected $primaryKey = 'idPeriode';
    public $timestamps = false;

    protected $fillable = ['libellePeriode', 'dateDebutPeriode', 'dateFinPeriode', 'AnneeScolaire_idAnnee'];

    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class, 'AnneeScolaire_idAnnee', 'idAnnee');
    }
}