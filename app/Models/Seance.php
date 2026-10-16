<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seance extends Model
{
    protected $table = 'seance';
    protected $primaryKey = 'idSeance';
    public $timestamps = false;

    protected $fillable = [
        'dateSeance', 'heureDebutSeance', 'heureFinSeance',
        'Enseignement_idEnseignement', 'Salle_idSalle',
    ];

    public function enseignement()
    {
        return $this->belongsTo(Enseignement::class, 'Enseignement_idEnseignement', 'idEnseignement');
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class, 'Salle_idSalle', 'idSalle');
    }
}