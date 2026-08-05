<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Events\PasswordReset;

class LogAuthenticationEvents
{
    public function handle($event): void
    {
        if ($event instanceof Login) {
            activity('auth')
                ->by($event->user)
                ->event('login')
                ->withProperties(['ip_address' => request()->ip(), 'user_agent' => request()->userAgent()])
                ->log('login');
        }

        if ($event instanceof Logout) {
            activity('auth')
                ->by($event->user)
                ->event('logout')
                ->withProperties(['ip_address' => request()->ip(), 'user_agent' => request()->userAgent()])
                ->log('logout');
        }

        if ($event instanceof Failed) {
            $email = data_get($event->credentials, 'email', 'unknown');

            activity('auth')
                ->event('failed_login')
                ->withProperties(['email' => $email, 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent()])
                ->log('failed_login');
        }

        if ($event instanceof Verified) {
            activity('auth')
                ->by($event->user)
                ->event('email_verified')
                ->log('email_verified');
        }

        if ($event instanceof PasswordReset) {
            activity('auth')
                ->by($event->user)
                ->event('password_reset')
                ->log('password_reset');
        }
    }
}