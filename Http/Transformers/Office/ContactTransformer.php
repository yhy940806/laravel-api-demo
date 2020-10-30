<?php

namespace App\Http\Transformers\Office;

use App\Http\Transformers\BaseTransformer;
use App\Http\Transformers\User\UserTransformer;
use App\Models\Office\Contact;
use App\Traits\StampCache;

class ContactTransformer extends BaseTransformer
{
    use StampCache;

    public function transform(Contact $contact)
    {
        $contact = $contact->with(["access_users:office_contact_users.user_uuid,name_first,name_last,flag_read,flag_archive,flag_delete",
                "access_groups:office_contact_groups.group_uuid,group_name,group_memo,flag_read,flag_archive,flag_delete"])->first();
        $response = [
            "contact_uuid" => $contact->contact_uuid,
            "contact_name_first" => $contact->contact_name_first,
            "contact_name_last" => $contact->contact_name_last,
            "contact_business" => $contact->contact_business,
            "contact_subject" => $contact->contact_subject,
            "contact_email" => $contact->contact_email,
            "contact_memo" => $contact->contact_memo,
            "contact_json" => $contact->contact_json,
            "access_groups" => $contact->access_groups,
            "access_users" => $contact->access_users
        ];

        return(array_merge($response, $this->stamp($contact)));
    }

    public function includeUser(Contact $contact)
    {
        if ($contact->user) {
            return($this->item($contact->user, new UserTransformer));
        } else {
            return(null);
        }

    }
}
