<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Receipt extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'receipts';

    protected $fillable = ['status', 'cancelled_at'];

    protected $auditInclude = [
        'status',
        'cancelled_at',
    ];
}
