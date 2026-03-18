<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/Payment.php
class Payment extends Model
{
    protected $fillable = ['user_id', 'cohort_id', 'transaction_id', 'amount', 'currency', 'status', 'payment_method'];

    public function user() { return $this->belongsTo(User::class); }
    public function cohort() { return $this->belongsTo(Cohort::class); }
}
