<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = 'students';
    public function transactions() {
        return $this->hasMany(Transaction::class, 'entity_id', 'id');
    }
}
