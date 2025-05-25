<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Concessionaire extends Model
{
    protected $table = 'concessionaires';
    protected $fillable = ['name','contact','status'];

    public function billing() {
        return $this->hasMany(ConcessionaireBill::class, 'concessionaire_id', 'id');
    }
}
