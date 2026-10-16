<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class Utilisateur extends Authenticatable
{
    use HasRoles; // ajoute roles, permissions, hasRole(), can()...

    protected $table = 'utilisateur';
    protected $primaryKey = 'idUtilisateur';
    public $timestamps = false;
    protected $guard_name = 'web';

    protected $fillable = [
        'login', 'motDePasse', 'role', 'actif',
        'Enseignant_idEnseignant', 'nomUtilisateur', 'prenomUtilisateur',
    ];

    protected $hidden = ['motDePasse'];

    // Fiche enseignant liée à ce compte (vide pour Administrateur / Secrétariat)
    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class, 'Enseignant_idEnseignant', 'idEnseignant');
    }

    // Le mot de passe est dans la colonne "motDePasse" et non "password"
    public function getAuthPasswordName()
    {
        return 'motDePasse';
    }

    public function getAuthPassword()
    {
        return $this->motDePasse;
    }

    // Pas de colonne "remember_token" dans ta table
    public function getRememberTokenName()
    {
        return '';
    }

    public function getNomCompletAttribute()
    {
        return trim($this->prenomUtilisateur . ' ' . $this->nomUtilisateur);
    }
}