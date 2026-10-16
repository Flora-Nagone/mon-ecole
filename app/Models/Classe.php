<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    protected $table = 'classe';
    protected $primaryKey = 'idClasse';
    public $timestamps = false;

    protected $fillable = ['codeClasse', 'libelleClasse', 'Niveau_idNiveau', 'AnneeScolaire_idAnnee'];

    public function niveau()
    {
        return $this->belongsTo(Niveau::class, 'Niveau_idNiveau', 'idNiveau');
    }

    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class, 'AnneeScolaire_idAnnee', 'idAnnee');
    }

    public function inscriptions()
    {
        return $this->hasMany(Inscription::class, 'Classe_idClasse', 'idClasse');
    }
}