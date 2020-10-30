<?php

namespace App\Mail\Arena;

use App\Models\Core\App;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SignInMail extends Mailable implements ShouldQueue {
    use Queueable, SerializesModels;

    /**
     * @var App
     */
    private App $app;

    /**
     * Create a new message instance.
     * @param App $app
     * @return void
     */
    public function __construct(App $app) {
        $this->app = $app;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        $this->withSwiftMessage(function ($message) {
            $message->app = $this->app;
        });
        return $this->view('mail.arena.signin');
    }
}
