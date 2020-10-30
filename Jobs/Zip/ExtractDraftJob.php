<?php

namespace App\Jobs\Zip;

use Illuminate\Bus\Queueable;
use App\Services\Common\ZipService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Soundblock\{ProjectDraft, Service};
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};

class ExtractDraftJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $storagePath;

    protected Service $objService;

    protected ProjectDraft $objDraft;

    /**
     * Create a new job instance.
     *
     * @param string $storagePath
     * @param Service $objService
     * @param ProjectDraft $objDraft
     */
    public function __construct(string $storagePath, Service $objService, ProjectDraft $objDraft) {
        $this->storagePath = $storagePath;
        $this->objService = $objService;
        $this->objDraft = $objDraft;
    }

    /**
     * Execute the job.
     *
     * @param ZipService $zipService
     * @return void
     */
    public function handle(ZipService $zipService) {
        //
        $zipService->unzipDraft($this->storagePath, $this->objService, $this->objDraft);
    }
}
