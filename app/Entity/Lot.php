<?php

namespace App\Entity;

use App\User;
use App\Entity\{ Currency, Lot };

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Lot extends Model
{
    protected $fillable = [
        "id",
        'currency_id',
        'seller_id',
        'date_time_open',
        'date_time_close',
        'price'
    ];

    public function getDateTimeOpen() : int
    {
        if (is_int($this->date_time_open)) {
            return $this->date_time_open;
        } else {
            return (new Carbon($this->date_time_open))->getTimestamp();
        }
    }

    public function getDateTimeClose() : int
    {
        if (is_int($this->date_time_close)) {
            return $this->date_time_close;
        } else {
            return (new Carbon($this->date_time_close))->getTimestamp();
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function trades()
    {
        return $this->hasMany(Lot::class);
    }
}
