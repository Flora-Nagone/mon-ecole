<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Eleve extends Model
{
    protected $table = 'eleve';
    protected $primaryKey = 'idEleve';
    public $timestamps = false;

    protected $fillable = [
        'matriculeEleve',
        'nomEleve',
        'prenomEleve',
        'sexeEleve',
        'dateNaissance',
        'lieuNaissance',
        'adresseEleve',
        'telephoneEleve',
    ];

    public function inscriptions()
    {
        return $this->hasMany(Inscription::class, 'Eleve_idEleve', 'idEleve');
    }
}