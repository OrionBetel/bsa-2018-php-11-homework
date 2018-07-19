<?php

namespace App\Entity;

use App\User;
use App\Entity\Lot;

use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    protected $fillable = [
        "id",
        "lot_id",
        "user_id",
        "amount"
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }
}
