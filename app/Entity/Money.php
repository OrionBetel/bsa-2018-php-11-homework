<?php

namespace App\Entity;

use App\Entity\{ Wallet, Currency };

use Illuminate\Database\Eloquent\Model;

class Money extends Model
{
    protected $fillable = [
        "wallet_id",
        "currency_id",
        "amount"
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
