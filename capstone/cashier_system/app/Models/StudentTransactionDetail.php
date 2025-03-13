<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentTransactionDetail extends Model
{
    protected $table = 'student_transaction_details';

    public function fee() {
        return $this->hasOne(Fee::class, 'fee_id', 'id');
    }
}
