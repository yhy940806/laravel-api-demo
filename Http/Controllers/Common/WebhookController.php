<?php

namespace App\Http\Controllers\Common;

use Log;
use Illuminate\Http\Request;
use App\Http\Middleware\Webhook;
use App\Http\Controllers\Controller;
use App\Services\User\UserCorrespondenceService;

class WebhookController extends Controller {

    public function __construct() {
        $this->middleware(Webhook::class);
    }

    /**
     * @param Request $request
     * @param UserCorrespondenceService $correspondenceService
     * @return void
     * @throws \Exception
     */
    public function __invoke(Request $request, UserCorrespondenceService $correspondenceService) {
        $data = $request->get("event-data");
        $emailId = $data["message"]["headers"]["message-id"];
        $correspondence = $correspondenceService->findByEmail($emailId);
        if ($correspondence) {
            Log::info('event', ["data" => $data]);
            if ($data["event"] === "delivered") {
                $correspondenceService->update($correspondence, ["flag_received" => true]);
            }

            if ($data["event"] === "opened" || $data["event"] === "clicked") {
                $correspondenceService->update($correspondence, ["flag_read" => true]);
            }
        }
    }
}
