<?php

namespace App\Policies;

use App\User;
use App\Trade;
use Illuminate\Auth\Access\HandlesAuthorization;

class TradePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the trade.
     *
     * @param  \App\User  $user
     * @param  \App\Trade  $trade
     * @return mixed
     */
    public function view(User $user, Trade $trade)
    {
        //
    }

    /**
     * Determine whether the user can create trades.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user;
    }

    /**
     * Determine whether the user can update the trade.
     *
     * @param  \App\User  $user
     * @param  \App\Trade  $trade
     * @return mixed
     */
    public function update(User $user, Trade $trade)
    {
        //
    }

    /**
     * Determine whether the user can delete the trade.
     *
     * @param  \App\User  $user
     * @param  \App\Trade  $trade
     * @return mixed
     */
    public function delete(User $user, Trade $trade)
    {
        //
    }

    /**
     * Determine whether the user can restore the trade.
     *
     * @param  \App\User  $user
     * @param  \App\Trade  $trade
     * @return mixed
     */
    public function restore(User $user, Trade $trade)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the trade.
     *
     * @param  \App\User  $user
     * @param  \App\Trade  $trade
     * @return mixed
     */
    public function forceDelete(User $user, Trade $trade)
    {
        //
    }
}
