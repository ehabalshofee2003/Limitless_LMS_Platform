<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id', 'type', 'amount', 'balance_after', 
        'description', 'transactionable_id', 'transactionable_type'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // علاقة متعددة الأشكال (MorphTo) لربط المعاملة بالدورة أو السحب
    public function transactionable()
    {
        return $this->morphTo();
    }
}