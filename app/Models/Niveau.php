<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Niveau extends Model
{
    protected $table = 'niveau';
    protected $primaryKey = 'idNiveau';
    public $timestamps = false;

    protected $fillable = ['libelleNiveau'];
}