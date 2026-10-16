<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enseignement extends Model
{
    protected $table = 'enseignement';
    protected $primaryKey = 'idEnseignement';
    public $timestamps = false;

    protected $fillable = ['Enseignant_idEnseignant', 'Matiere_idMatiere', 'Classe_idClasse'];

    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class, 'Enseignant_idEnseignant', 'idEnseignant');
    }

    public function matiere()
    {
        return $this->belongsTo(Matiere::class, 'Matiere_idMatiere', 'idMatiere');
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'Classe_idClasse', 'idClasse');
    }
}