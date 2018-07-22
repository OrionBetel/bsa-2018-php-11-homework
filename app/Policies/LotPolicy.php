<?php

namespace App\Policies;

use App\User;
use App\Enitty\Lot;
use Illuminate\Auth\Access\HandlesAuthorization;

class LotPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the lot.
     *
     * @param  \App\User  $user
     * @param  \App\Lot  $lot
     * @return mixed
     */
    public function view(User $user, Lot $lot)
    {
        //
    }

    /**
     * Determine whether the user can create lots.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user; 
    }

    /**
     * Determine whether the user can update the lot.
     *
     * @param  \App\User  $user
     * @param  \App\Lot  $lot
     * @return mixed
     */
    public function update(User $user, Lot $lot)
    {
        //
    }

    /**
     * Determine whether the user can delete the lot.
     *
     * @param  \App\User  $user
     * @param  \App\Lot  $lot
     * @return mixed
     */
    public function delete(User $user, Lot $lot)
    {
        //
    }

    /**
     * Determine whether the user can restore the lot.
     *
     * @param  \App\User  $user
     * @param  \App\Lot  $lot
     * @return mixed
     */
    public function restore(User $user, Lot $lot)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the lot.
     *
     * @param  \App\User  $user
     * @param  \App\Lot  $lot
     * @return mixed
     */
    public function forceDelete(User $user, Lot $lot)
    {
        //
    }
}
