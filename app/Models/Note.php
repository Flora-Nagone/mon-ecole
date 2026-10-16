<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $table = 'note';
    protected $primaryKey = 'idNote';
    public $timestamps = false;

    protected $fillable = [
        'valeurNote', 'observationNote', 'estRattrapage',
        'Eleve_idEleve', 'Evaluation_idEvaluation',
    ];

    public function eleve()
    {
        return $this->belongsTo(Eleve::class, 'Eleve_idEleve', 'idEleve');
    }

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class, 'Evaluation_idEvaluation', 'idEvaluation');
    }
}