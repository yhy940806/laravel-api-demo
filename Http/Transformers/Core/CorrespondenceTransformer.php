<?php

namespace App\Http\Transformers\Core;

use App\Traits\StampCache;
use App\Models\Core\Correspondence;
use League\Fractal\TransformerAbstract;

class CorrespondenceTransformer extends TransformerAbstract {

    use StampCache;

    public function transform(Correspondence $correspondence)
    {
        $response = [
            "correspondence_uuid" => $correspondence->correspondence_uuid,
            "email_address"       => $correspondence->email_address,
            "email_subject"       => $correspondence->email_subject,
            "email_json"          => $correspondence->email_json,
            "remote_addr"         => $correspondence->remote_addr,
            "remote_host"         => $correspondence->remote_host,
            "flag_read"           => $correspondence->flag_read,
            "flag_archived"       => $correspondence->flag_archived,
            "flag_received"       => $correspondence->flag_received,
            "app"                 => $correspondence->app,
            "contact_email"       => $correspondence->contact_email,
            "attachments"         => $correspondence->attachments
        ];

        return(array_merge($response, $this->stamp($correspondence)));
    }
}
