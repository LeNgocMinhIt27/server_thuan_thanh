<?php

namespace App\Observers;

use App\Models\Auth\User;
use App\Traits\ConvertTrait;
use Illuminate\Support\Facades\DB;

class UserObserver
{
    use ConvertTrait;
    /**
     * Handle the user "created" event.
     *
     * @param  \App\Models\Auth\User  $user
     * @return void
     */
    public function created(User $user)
    {
        //
        DB::table('users')->where('id', $user->id)->update(['fts' => $this->convert_vi_to_en($user->name)]);
    }

    /**
     * Handle the user "updated" event.
     *
     * @param  \App\Models\Auth\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        //
        if ($user->isDirty('name')) {
            DB::table('users')->where('id', $user->id)->update(['fts' => $this->convert_vi_to_en($user->name)]);
        }
    }

    /**
     * Handle the user "deleted" event.
     *
     * @param  \App\Models\Auth\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        //
    }

    /**
     * Handle the user "restored" event.
     *
     * @param  \App\Models\Auth\User  $user
     * @return void
     */
    public function restored(User $user)
    {
        //
    }

    /**
     * Handle the user "force deleted" event.
     *
     * @param  \App\Models\Auth\User  $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }
}
