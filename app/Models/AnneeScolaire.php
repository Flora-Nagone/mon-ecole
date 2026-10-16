<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnneeScolaire extends Model
{
    protected $table = 'anneescolaire';
    protected $primaryKey = 'idAnnee';
    public $timestamps = false;

    protected $fillable = ['libelleAnnee', 'dateDebutAnnee', 'dateFinAnnee', 'anneeEnCours'];

    // L'année scolaire actuellement en cours (ou null)
    public static function enCours()
    {
        return static::where('anneeEnCours', 1)->first();
    }
}