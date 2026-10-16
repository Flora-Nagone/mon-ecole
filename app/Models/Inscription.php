<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inscription extends Model
{
    protected $table = 'inscription';
    protected $primaryKey = 'idInscription';
    public $timestamps = false;

    protected $fillable = [
        'codeInscription', 'dateInscription', 'statutInscription',
        'montantDu', 'chefClasse', 'Eleve_idEleve', 'Classe_idClasse',
    ];

    // Pourcentage du montant dû à verser pour être réellement inscrit
    const SEUIL_POURCENT = 50;

    public function eleve()
    {
        return $this->belongsTo(Eleve::class, 'Eleve_idEleve', 'idEleve');
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'Classe_idClasse', 'idClasse');
    }

    public function versements()
    {
        return $this->hasMany(Versement::class, 'Inscription_idInscription', 'idInscription');
    }

    // Total des versements (utilise la somme déjà chargée si elle existe)
    public function getTotalVerseAttribute()
    {
        if (array_key_exists('versements_sum_montantVersement', $this->attributes)) {
            return (int) $this->attributes['versements_sum_montantVersement'];
        }
        return (int) $this->versements()->sum('montantVersement');
    }

    public function getResteAPayerAttribute()
    {
        return max(0, $this->montantDu - $this->total_verse);
    }

    // Montant minimum à verser pour être inscrit
    public function getSeuilAttribute()
    {
        return (int) ceil($this->montantDu * self::SEUIL_POURCENT / 100);
    }

    public function getEstSoldeAttribute()
    {
        return $this->reste_a_payer == 0;
    }

    // Réellement inscrit = a versé au moins le seuil
    public function getEstInscritAttribute()
    {
        return $this->total_verse >= $this->seuil;
    }
}