<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Core\CorrespondenceService;
use App\Repositories\Core\CorrespondenceRepository;
use App\Http\Requests\Core\UpdateCorrespondenceRequest;
use App\Http\Transformers\Core\CorrespondenceTransformer;

class CorrespondenceController extends Controller
{
    /**
     * @var CorrespondenceService
     */
    private $correspondenceService;
    /**
     * @var CorrespondenceRepository
     */
    private $correspondenceRepository;

    /**
     * CorrespondenceController constructor.
     * @param CorrespondenceService $correspondenceService
     * @param CorrespondenceRepository $correspondenceRepository
     */
    public function __construct(CorrespondenceService $correspondenceService, CorrespondenceRepository $correspondenceRepository){
        $this->correspondenceService    = $correspondenceService;
        $this->correspondenceRepository = $correspondenceRepository;
    }

    /**
     * @group Core
     *
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function createCorrespondence(Request $request){
        $attachments = $request->allFiles();
        $clientIp    = $request->getClientIp();
        $clientHost  = $request->getHost();

        $objCorrespondence = $this->correspondenceService->create($request->post(), $clientIp, $clientHost, $attachments);

        if (is_null($objCorrespondence)) {
            return ($this->apiReject(null, "Correspondence hasn't created.", 400));
        }

        return ($this->apiReply($objCorrespondence, "Correspondence has created.", 200));
    }

    /**
     * @group Core
     *
     * @return \Dingo\Api\Http\Response
     */
    public function getCorrespondences(){
        return ($this->apiReply($this->correspondenceRepository->all()));
    }

    /**
     * @group Core
     *
     * @urlParam correspondence_uuid required Correspondence UUID
     *
     * @param string $correspondenceUUID
     * @return \Dingo\Api\Http\Response
     */
    public function getCorrespondenceByUUID(string $correspondenceUUID){
        $objCorrespondence = $this->correspondenceRepository->findByUUID($correspondenceUUID);

        return ($this->response->item($objCorrespondence, new CorrespondenceTransformer));
    }

    /**
     * @group Core
     *
     * @urlParam correspondence_uuid required Correspondence UUID
     *
     * @bodyParam flag_read boolean optional Flag Read
     * @bodyParam flag_archived boolean optional Flag Archived
     * @bodyParam flag_received boolean optional Flag Received
     *
     * @param string $correspondenceUUID
     * @param UpdateCorrespondenceRequest $request
     * @return mixed
     */
    public function updateCorrespondenceByUUID(string $correspondenceUUID, UpdateCorrespondenceRequest $request){
        $boolResult = $this->correspondenceRepository->updateByUUID(
            $correspondenceUUID,
            $request->only(["flag_read", "flag_archived", "flag_received"])
        );

        if ($boolResult) {
            return ($this->apiReply(null, "Correspondence updated successfully."));
        }

        return ($this->apiReject(null, "Correspondence hasn't updated."));
    }
}
