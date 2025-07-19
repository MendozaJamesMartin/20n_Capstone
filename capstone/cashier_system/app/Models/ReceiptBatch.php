<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceiptBatch extends Model
{
    protected $table = 'receipt_batches';
    protected $fillable = ['start_number', 'end_number', 'next_number'];
    protected $casts = [
        'exhausted_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    // Accessor for used_count
    public function getUsedCountAttribute() {
        return $this->next_number - $this->start_number;
    }

    // Accessor for remaining_count
    public function getRemainingCountAttribute() {
        return max(0, $this->end_number - $this->next_number + 1);
    }

    public function getDisplayNextNumberAttribute() {
        return $this->next_number > $this->end_number ? '—' : $this->next_number;
    }

}
