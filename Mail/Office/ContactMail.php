<?php

namespace App\Mail\Office;

use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use App\Models\Office\Contact;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ContactMail extends Mailable implements ShouldQueue {
    use Queueable, SerializesModels;

    /** @var Contact */
    protected Contact $contact;

    /**
     * Create a new message instance.
     *
     * @param Contact $contact
     */
    public function __construct(Contact $contact) {
        $this->contact = $contact;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        return $this->markdown("email.office.contact")->with(["contact" => $this->contact]);
    }
}
