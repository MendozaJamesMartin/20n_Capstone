<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Concessionaire extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $auditInclude = ['name', 'status'];
    protected $softDelete = true; // to track deletion/restoration

    protected $table = 'concessionaires';
    protected $fillable = ['name','contact','status'];

    use SoftDeletes;

    public function billing() {
        return $this->hasMany(ConcessionaireBill::class, 'concessionaire_id', 'id');
    }
}
