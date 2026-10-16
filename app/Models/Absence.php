<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    protected $table = 'absence';
    protected $primaryKey = 'idAbsence';
    public $timestamps = false;

    protected $fillable = [
        'dateAbsence', 'heureDebutAbsence', 'heureFinAbsence', 'motif', 'justifiee',
        'Eleve_idEleve', 'Seance_idSeance', 'Evaluation_idEvaluation', 'Note_idNote',
    ];

    public function eleve()
    {
        return $this->belongsTo(Eleve::class, 'Eleve_idEleve', 'idEleve');
    }

    public function seance()
    {
        return $this->belongsTo(Seance::class, 'Seance_idSeance', 'idSeance');
    }
}