<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salle extends Model
{
    protected $table = 'salle';
    protected $primaryKey = 'idSalle';
    public $timestamps = false;

    protected $fillable = ['libelleSalle', 'capacite'];
}