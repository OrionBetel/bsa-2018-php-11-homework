<?php

namespace App\Entity;

use App\User;
use App\Entity\Money;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        "id",
        "user_id"
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function money()
    {
        return $this->hasMany(Money::class);
    }
}
