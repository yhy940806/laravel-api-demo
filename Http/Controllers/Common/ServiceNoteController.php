<?php

namespace App\Http\Controllers\Common;

use Exception;
use App\Http\Controllers\Controller;
use App\Http\Transformers\Soundblock\ServiceNoteTransformer;
use App\Http\Requests\Office\Service\{CreateServiceNoteRequest, GetServiceNoteRequest};
use App\Services\{AuthService, Core\Auth\AuthPermissionService, Soundblock\ServiceNoteService};

class ServiceNoteController extends Controller {
    /** @var ServiceNoteService */
    protected ServiceNoteService $noteService;
    /** @var AuthService */
    protected AuthService $authService;
    /** @var AuthPermissionService */
    protected AuthPermissionService $authPermService;

    /**
     * @param ServiceNoteService $noteService
     * @param AuthService $authService
     * @param AuthPermissionService $authPermService
     * @return void
     */
    public function __construct(ServiceNoteService $noteService, AuthService $authService, AuthPermissionService $authPermService) {
        $this->noteService = $noteService;
        $this->authService = $authService;
        $this->authPermService = $authPermService;
    }

    /**
     * @responseFile responses/office/soundblock/serviceplan/create-notes.post.json
     * @bodyParam service uuid required Service UUID
     * @bodyParam service_notes string required
     * @bodyParam files array required
     * @bodyParam files.* file required
     *
     * @param CreateServiceNoteRequest $objRequest
     * @return \Dingo\Api\Http\Response
     * @throws Exception
     */
    public function store(CreateServiceNoteRequest $objRequest) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objNote = $this->noteService->create($objRequest->all());
                return ($this->response->item($objNote, new ServiceNoteTransformer(["attachments"])));
            } else {
                abort(403, "You have not required permission.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @responseFile responses/office/soundblock/service/get-notes-by-service.get.json
     * @queryParam service uuid required Service UUID
     *
     * @param GetServiceNoteRequest $objRequest
     * @return \Dingo\Api\Http\Response
     * @throws Exception
     */
    public function index(GetServiceNoteRequest $objRequest) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $arrNotes = $this->noteService->findAllByService($objRequest->service);
                return ($this->response->paginator($arrNotes, new ServiceNoteTransformer(["attachments"])));
            } else {
                abort(403, "You have not required permission.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
}
