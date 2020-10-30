<?php

namespace App\Mail\Apparel;

use Log;
use App\Models\Core\App;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TourMaskMail extends Mailable implements ShouldQueue {
    use Queueable, SerializesModels;

    /**
     * @var App
     */
    private App $app;

    /**
     * Create a new message instance.
     * @param App $app
     *
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
            Log::info("MessageSent event fired", [$message->getId()]);
            $message->app = $this->app;
        });

        return ($this->view('mail.apparel.tourmask'));
    }
}
