<?php

namespace App\Entity;

use App\Entity\{ Money, Lot };

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        "id",
        "name"
    ];

    public function money()
    {
        return $this->hasMany(Money::class);
    }
    
    public function lots()
    {
        return $this->hasMany(Lot::class);
    }
}
