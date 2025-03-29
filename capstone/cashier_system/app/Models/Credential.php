<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Credential extends Model
{
    protected $table = 'credentials';
    protected $fillable = [
        'user_id',
        'password',
    ];
}
