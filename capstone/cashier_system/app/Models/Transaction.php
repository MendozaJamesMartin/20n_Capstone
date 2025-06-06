<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';
    protected $primaryKey = 'id';
    protected $fillable = ['entity_type', 'total_amount', 'amount_paid', 'balance_due'];

    public function receipt() {
        return $this->hasOne(Receipt::class, 'transaction_id');
    }
}
