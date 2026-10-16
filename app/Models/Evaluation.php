<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    protected $table = 'evaluation';
    protected $primaryKey = 'idEvaluation';
    public $timestamps = false;

    protected $fillable = [
        'dateEvaluation', 'Enseignement_idEnseignement',
        'Periode_idPeriode', 'TypeEvaluation_idTypeEvaluation',
    ];

    public function enseignement()
    {
        return $this->belongsTo(Enseignement::class, 'Enseignement_idEnseignement', 'idEnseignement');
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'Periode_idPeriode', 'idPeriode');
    }

    public function type()
    {
        return $this->belongsTo(TypeEvaluation::class, 'TypeEvaluation_idTypeEvaluation', 'idTypeEvaluation');
    }

    // Absences concernées : liées à l'évaluation, ou prises à la séance du même jour pour la même matière.
    // Si un élève a plusieurs absences, une absence non justifiée l'emporte.
    public function absences()
    {
        return Absence::where(function ($q) {
                $q->where('Evaluation_idEvaluation', $this->idEvaluation)
                  ->orWhereHas('seance', fn ($s) => $s
                      ->where('Enseignement_idEnseignement', $this->Enseignement_idEnseignement)
                      ->where('dateSeance', $this->dateEvaluation));
            })
            ->get()
            ->sortByDesc('justifiee')
            ->keyBy('Eleve_idEleve');
    }
}