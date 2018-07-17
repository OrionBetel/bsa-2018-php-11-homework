<?php

namespace App\Repository;

use App\Entity\Lot;
use App\Repository\Contracts\LotRepository;

class DatabaseLotRepository implements LotRepository
{
    public function add(Lot $lot) : Lot
    {
        $lot-save();

        return $lot;
    }

    public function getById(int $id) : ?Lot
    {
        return Lot::find($id);
    }

    /**
     * @return Lot[]
     */
    public function findAll()
    {
        return Lot::all();
    }

    public function findActiveLot(int $userId) : ?Lot
    {
        return Lot::where('seller_id', $userId)->first();
    }
}
