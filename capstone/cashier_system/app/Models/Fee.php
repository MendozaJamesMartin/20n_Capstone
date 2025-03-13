<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    protected $table = 'fees';
    protected $primaryKey = 'id';
    protected $fillable = ['fee_name','amount'];

    public function studentTransactionDetail() {
        return $this->belongsTo(StudentTransactionDetail::class, 'fee_id', 'id')->withDefault([
            'name' => 'N/A'
        ]);;
    }
}
