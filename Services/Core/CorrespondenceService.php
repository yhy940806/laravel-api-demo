<?php

namespace App\Services\Core;

use App\Helpers\Util;
use App\Helpers\Client;
use App\Models\Core\Auth\AuthGroup;
use Illuminate\Support\Facades\Mail;
use App\Mail\Core\CorrespondenceMail;
use Illuminate\Support\Facades\Storage;
use App\Events\Common\PrivateNotification;
use App\Repositories\Core\CorrespondenceRepository;
use App\Repositories\User\UserContactEmailRepository;
use App\Repositories\Core\CorrespondenceAttachmentsRepository;

class CorrespondenceService {
    /**
     * @var CorrespondenceRepository
     */
    private $correspondenceRepository;
    /**
     * @var UserContactEmailRepository
     */
    private $contactEmailRepository;
    /**
     * @var CorrespondenceAttachmentsRepository
     */
    private $correspondenceAttachmentsRepository;

    /**
     * CorrespondenceService constructor.
     * @param CorrespondenceRepository $correspondenceRepository
     * @param UserContactEmailRepository $contactEmailRepository
     * @param CorrespondenceAttachmentsRepository $correspondenceAttachmentsRepository
     */
    public function __construct(CorrespondenceRepository $correspondenceRepository, UserContactEmailRepository $contactEmailRepository,
                                CorrespondenceAttachmentsRepository $correspondenceAttachmentsRepository){
        $this->contactEmailRepository              = $contactEmailRepository;
        $this->correspondenceRepository            = $correspondenceRepository;
        $this->correspondenceAttachmentsRepository = $correspondenceAttachmentsRepository;
    }

    /**
     * @param array $requestData
     * @param string $clientIp
     * @param string $clientHost
     * @param array $attachments
     * @return mixed
     * @throws \Exception
     */
    public function create(array $requestData, string $clientIp, string $clientHost, array $attachments){
        /* Creating Insert Data Array */
        $arrInsertData = [];
        $objApp = Client::app();
        $arrInsertData["app_id"]              = $objApp->app_id;
        $arrInsertData["app_uuid"]            = $objApp->app_uuid;
        $arrInsertData["remote_addr"]         = $clientIp;
        $arrInsertData["remote_host"]         = $clientHost;
        $arrInsertData["email_subject"]       = $requestData["subject"];
        $arrInsertData["correspondence_uuid"] = Util::uuid();

        $objContactEmail = $this->contactEmailRepository->find($requestData["email"]);

        if (is_null($objContactEmail)) {
            $arrInsertData["email_address"] = $requestData["email"];
        } else {
            $arrInsertData["email_id"]   = $objContactEmail->row_id;
            $arrInsertData["email_uuid"] = $objContactEmail->row_uuid;
        }

        unset($requestData["email"], $requestData["subject"]);
        $arrInsertData["email_json"] = $requestData;

        /* Insert Data */
        $objCorrespondence = $this->correspondenceRepository->create($arrInsertData);

        /* Insert Attachments */
        foreach ($attachments as $attachment) {
            $strFileName = $attachment->getClientOriginalName();
            $strFilePath = "correspondence" . DIRECTORY_SEPARATOR . $objCorrespondence->correspondence_uuid;

            Storage::disk("s3-core")->putFileAs($strFilePath, $attachment, $strFileName, "public");

            $arrFileData["file_name"]           = $strFileName;
            $arrFileData["file_type"]           = $attachment->getMimeType();
            $arrFileData["correspondence_id"]   = $objCorrespondence->correspondence_id;
            $arrFileData["correspondence_uuid"] = $objCorrespondence->correspondence_uuid;

            $this->correspondenceAttachmentsRepository->create($arrFileData);
        }

        /* Send Mail */
        Mail::to([$objCorrespondence->email, "swhite@arena.com"])->send(new CorrespondenceMail($objCorrespondence->app, $objCorrespondence));

        /* Send Notification */
        $flags = [
            "notification_state" => "unread",
            "flag_canarchive"    => true,
            "flag_candelete"     => true,
            "flag_email"         => false,
        ];
        $startContract = [
            "notification_name"   => "Correspondence",
            "notification_memo"   => "New correspondence email.",
        ];
        $objAuthGroup = AuthGroup::where("group_name", "App.Office.Admin")->first();
        $objOfficeUsers = $objAuthGroup->users;
        foreach ($objOfficeUsers as $user) {
            event(new PrivateNotification($user, $startContract, $flags, $objApp));
        }

        return ($objCorrespondence);
    }
}
