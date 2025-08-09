<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditingAuditable;
use OwenIt\Auditing\Contracts\Auditable;

class Fee extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $auditInclude = ['fee_name', 'amount'];
    protected $softDelete = true; // to track deletion/restoration

    protected $table = 'fees';
    protected $primaryKey = 'id';
    protected $fillable = ['fee_name','amount'];

    use SoftDeletes;
}