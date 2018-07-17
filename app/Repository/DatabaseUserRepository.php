<?php

namespace App\Repository;

use App\User;
use App\Repository\Contracts\UserRepository;

class DatabaseUserRepository implements UserRepository
{
    public function getById(int $id) : ?User
    {
        return User::find($id);
    }
}
