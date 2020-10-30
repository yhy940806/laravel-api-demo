<?php

namespace App\Repositories\Office;

use App\Repositories\BaseRepository;
use App\Models\{AuthGroup, User, Office\Contact};
use Illuminate\Support\Collection as SupportCollection;

class ContactRepository extends BaseRepository {

    /**
     * @param Contact $contact
     * @return void
     */
    public function __construct(Contact $contact) {
        $this->model = $contact;
    }

    /**
     * @param User $user
     * @param array $arrParams
     * @return SupportCollection
     */
    public function findAllByAccessUser(User $user, array $arrParams): SupportCollection {
        return ($this->model->whereHas("access_users", function ($query) use ($user, $arrParams) {
            $query->where("office_contact_users.user_id", $user->user_id);
            foreach ($arrParams as $key => $value) {
                $query->where("office_contact_users.{$key}", $value);
            }
            $query->select("office_contact.contact_uuid", "office_contact.contact_email", "office_contact.contact_subject");
        })->with(["access_users:flag_read,flag_archive,flag_delete"])
                            ->makeHidden(["contact_name_first", "contact_name_last", "contact_business", "contact_memo", "contact_phone", "contact_json", "contact_host", "contact_agent"])
                            ->get());
    }

    /**
     * @param AuthGroup $group
     * @param array $arrParams
     * @return SupportCollection
     */
    public function findAllByAccessGroup(AuthGroup $group, array $arrParams): SupportCollection {
        return ($this->model->whereHas("access_groups", function ($query) use ($group, $arrParams) {
            $query->where("office_contact_groups.group_id", $group->group_id);
            foreach ($arrParams as $key => $value) {
                $query->where("office_contact_groups.{$key}", $value);
            }
            $query->select("office_contact.contact_uuid", "office_contact.contact_email", "office_contact.contact_subject");
        })->with(["access_users:flag_read,flag_archive,flag_delete"])->get()
                            ->makeHidden(["contact_name_first", "contact_name_last", "contact_business", "contact_memo", "contact_phone", "contact_json", "contact_host", "contact_agent"]));
    }

    /**
     * @param Contact $contact
     * @param User $user
     * @param array $arrParams
     * @return Contact
     */
    public function updateAccessUser(Contact $contact, User $user, array $arrParams): Contact {
        $contact->access_users()->updateExistingPivot($user->user_id, $arrParams);
        return ($contact);
    }

    /**
     * @param Contact $contact
     * @param AuthGroup $group
     * @param array $arrParams
     * @return
     */
    public function updateAccessGroup(Contact $contact, AuthGroup $group, array $arrParams): Contact {
        $contact->access_users()->updateExistingPivot($group->group_id, $arrParams);
        return ($contact);
    }
}
