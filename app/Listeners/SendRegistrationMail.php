<?php

namespace App\Listeners;

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
        $data = array('name'=>"Virat Gandhi");
        Mail::send(['text'=>'mail'], $data, function($message) {
            $message->to('filippo@localidautore.it', 'Tutorials Point')->subject
            ('Laravel Basic Testing Mail');
            $message->from('luca@localidautore.it','Virat Gandhi');
        });
        echo "Basic Email Sent. Check your inbox.";
    }
}
