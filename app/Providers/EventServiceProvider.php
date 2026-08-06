<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Events\PasswordReset;
use App\Listeners\LogAuthenticationEvents;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class => [
            LogAuthenticationEvents::class,
        ],
        Logout::class => [
            LogAuthenticationEvents::class,
        ],
        Failed::class => [
            LogAuthenticationEvents::class,
        ],
        Verified::class => [
            LogAuthenticationEvents::class,
        ],
        PasswordReset::class => [
            LogAuthenticationEvents::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}