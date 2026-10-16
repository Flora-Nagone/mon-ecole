<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeEvaluation extends Model
{
    protected $table = 'typeevaluation';
    protected $primaryKey = 'idTypeEvaluation';
    public $timestamps = false;

    protected $fillable = ['libelleTypeEvaluation', 'ponderation'];
}