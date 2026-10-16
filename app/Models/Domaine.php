<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Domaine extends Model
{
    protected $table = 'domaine';
    protected $primaryKey = 'idDomaine';
    public $timestamps = false;

    protected $fillable = ['libelleDomaine'];
}