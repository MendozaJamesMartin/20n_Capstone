<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fee extends Model
{
    protected $table = 'fees';
    protected $primaryKey = 'id';
    protected $fillable = ['fee_name','amount'];

    use SoftDeletes;
}