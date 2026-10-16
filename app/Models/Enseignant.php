<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enseignant extends Model
{
    protected $table = 'enseignant';
    protected $primaryKey = 'idEnseignant';
    public $timestamps = false;

    protected $fillable = [
        'matriculeEnseignant', 'nomEnseignant', 'prenomEnseignant', 'statutEnseignant',
        'emailEnseignant', 'contactEnseignant', 'sexeEnseignant', 'Domaine_idDomaine',
    ];

    public function domaine()
    {
        return $this->belongsTo(Domaine::class, 'Domaine_idDomaine', 'idDomaine');
    }
}