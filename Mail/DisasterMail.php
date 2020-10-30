<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Models\Common\LogError;
use Illuminate\Queue\SerializesModels;

class DisasterMail extends Mailable {
    use Queueable, SerializesModels;

    /**
     * @var LogError
     */
    private LogError $logError;
    /**
     * @var string
     */
    private string $trace;

    /**
     * Create a new message instance.
     *
     * @param LogError $logError
     * @param string $trace
     */
    public function __construct(LogError $logError, string $trace) {
        $this->logError = $logError;
        $this->trace = $trace;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        $mail = $this->subject("New Exception Has Been Handled!")->view('email.exceptions.disaster')->with([
            "error" => $this->logError,
        ]);

        if (!empty($this->trace)) {
            $fileName = $this->logError->log_uuid . '_trace.txt';

            $mail->attachData($this->trace, $fileName, [
                'mime' => 'text/plain',
            ]);
        }

        return $mail;
    }
}
