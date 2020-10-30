<?php

namespace App\Mail\Auth;

use App\Models\Core\App;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable {
    use Queueable, SerializesModels;

    /**
     * @var App
     */
    private App $app;
    /**
     * @var string
     */
    private string $resetToken;

    /**
     * Create a new message instance.
     * @param App $app
     * @param string $resetToken
     */
    public function __construct(App $app, string $resetToken) {
        $this->app = $app;
        $this->resetToken = $resetToken;
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
        $frontendUrl = app_url("account", "http://localhost:4200");
        $frontendUrl .= "password-reset/{$this->resetToken}";

        return $this->view("mail.auth.password-reset")->with(['frontendUrl' => $frontendUrl]);
    }
}
