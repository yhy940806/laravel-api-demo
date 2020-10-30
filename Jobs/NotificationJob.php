<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\Common\NotificationService;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};

class NotificationJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $objMsg;

    /**
     * Create a new job instance.
     *
     * @param $objMsg
     */
    public function __construct($objMsg) {
        //
        $this->objMsg = $objMsg;
    }

    /**
     * Execute the job.
     *
     * @param NotificationService $notificationService
     * @return void
     */
    public function handle(NotificationService $notificationService) {
        $notificationService->sendPublicNotification($this->objMsg);
    }
}
