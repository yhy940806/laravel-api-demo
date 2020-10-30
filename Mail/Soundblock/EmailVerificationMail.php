<?php

namespace App\Mail\Soundblock;

use App\Models\Core\App;
use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use App\Models\UserContactEmail;
use Illuminate\Queue\SerializesModels;

class EmailVerificationMail extends Mailable {
    use Queueable, SerializesModels;

    /**
     * @var UserContactEmail
     */
    private UserContactEmail $objEmail;
    /**
     * @var App
     */
    private App $app;
    /**
     * @var array
     */
    private ?array $option;

    /**
     * Create a new message instance.
     *
     * @param UserContactEmail $objEmail
     * @param App $app
     * @param array $option
     *  $option = [
     *      'required_add_project_group' => (bool) This value represents if the user has to be added to project group after singup by invite email.
     *  ]
     */
    public function __construct(UserContactEmail $objEmail, App $app, ?array $option = ["required_add_project_group" => false]) {
        $this->objEmail = $objEmail;
        $this->app = $app;
        $this->option = $option;
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
        $frontendUrl = app_url("soundblock", "http://localhost:8200") . "profile/contact?token={$this->objEmail->verification_hash}";

        return $this->view("mail.soundblock.verification")->subject("Confirm your email address!")
                    ->with(["link" => $frontendUrl]);
    }
}
