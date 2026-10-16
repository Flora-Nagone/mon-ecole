<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matiere extends Model
{
    protected $table = 'matiere';
    protected $primaryKey = 'idMatiere';
    public $timestamps = false;

    protected $fillable = ['codeMatiere', 'libelleMatiere', 'Domaine_idDomaine'];

    public function domaine()
    {
        return $this->belongsTo(Domaine::class, 'Domaine_idDomaine', 'idDomaine');
    }
}