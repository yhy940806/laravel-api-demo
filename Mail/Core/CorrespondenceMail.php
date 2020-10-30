<?php

namespace App\Mail\Core;

use App\Models\Core\App;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Events\Common\MessageSent;
use App\Models\Core\Correspondence;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CorrespondenceMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    /**
     * @var Correspondence
     */
    private $correspondence;
    /**
     * @var App
     */
    private App $app;

    /**
     * CorrespondenceMail constructor.
     * @param App $app
     * @param Correspondence $correspondence
     */
    public function __construct(App $app, Correspondence $correspondence){
        $this->correspondence = $correspondence;
        $this->app = $app;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->withSwiftMessage(function ($message) {
            $message->app = $this->app;
        });
        $arrJsonData = $this->correspondence->email_json;

        return ($this->view("mail.core.correspondence")->with(["arrJsonData" => $arrJsonData]));
    }
}
