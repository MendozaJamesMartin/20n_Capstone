<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Deposit extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $auditInclude = ['deposit_date', 'amount'];
    protected $softDelete = true; // to track deletion/restoration

    protected $table = 'deposits';
    protected $primaryKey = 'id';
    protected $fillable = ['deposit_date', 'reference_number', 'account_number', 'amount'];

}
