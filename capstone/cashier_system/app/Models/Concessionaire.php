<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Concessionaire extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $auditInclude = ['name', 'status'];

    protected $table = 'concessionaires';
    protected $fillable = ['name','contact','status'];

    public function billing() {
        return $this->hasMany(ConcessionaireBill::class, 'concessionaire_id', 'id');
    }
}
