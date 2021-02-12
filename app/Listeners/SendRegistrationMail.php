<?php

namespace App\Listeners;

use App\Mail\RegistrationEmail;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendRegistrationMail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(Verified $event)
    {
        $user = User::whereNotNull('email_verified_at')->get()->last();
        $administrators = User::whereRoleIs('superadministrator')->get();

        foreach ($administrators as $admin) {
            Mail::to($admin->email)->send(new \App\Mail\RegistrationEmail($user));
        }

    }
}
