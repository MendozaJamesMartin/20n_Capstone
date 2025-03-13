<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConcessionaireBill extends Model
{
    protected $table = 'concessionaire_bills';
    use HasFactory;

    public function concessionaire() {
        return $this->belongsTo(Concessionaire::class, 'concessionaire_id', 'id')->withDefault([
            'name' => 'N/A'
        ]);
    }
}
