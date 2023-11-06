<?php

namespace App\Helpers;

class ObserverHelper
{
    protected static $observers = [
        '\App\Models\Auth\User' => \App\Observers\UserObserver::class,
    ];

    public static function register()
    {
        foreach (self::$observers as $model => $observer) {
            $model::observe($observer);
        }
    }
}
