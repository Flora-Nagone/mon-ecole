<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoefficientMatiere extends Model
{
    protected $table = 'coefficient_matiere';
    protected $primaryKey = 'idCoefficient';
    public $timestamps = false;

    protected $fillable = ['Niveau_idNiveau', 'Matiere_idMatiere', 'coefficient'];

    public function niveau()
    {
        return $this->belongsTo(Niveau::class, 'Niveau_idNiveau', 'idNiveau');
    }

    public function matiere()
    {
        return $this->belongsTo(Matiere::class, 'Matiere_idMatiere', 'idMatiere');
    }
}