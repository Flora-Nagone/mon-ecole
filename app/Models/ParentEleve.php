<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentEleve extends Model
{
    protected $table = 'parent';
    protected $primaryKey = 'idParent';
    public $timestamps = false;

    protected $fillable = [
        'nomParent', 'prenomParent', 'telephoneParent',
        'emailParent', 'adresseParent', 'professionParent',
    ];

    // Table de liaison "responsabilite" : un parent peut avoir plusieurs enfants
    public function eleves()
    {
        return $this->belongsToMany(Eleve::class, 'responsabilite', 'Parent_idParent', 'Eleve_idEleve')
            ->withPivot('lienParente');
    }
}