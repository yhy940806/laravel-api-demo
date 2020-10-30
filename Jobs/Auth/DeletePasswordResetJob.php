<?php

namespace App\Jobs\Auth;

use App\Models\PasswordReset;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};

class DeletePasswordResetJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var PasswordReset
     */
    private PasswordReset $passwordReset;

    /**
     * Create a new job instance.
     * @param PasswordReset $passwordReset
     *
     * @return void
     */
    public function __construct(PasswordReset $passwordReset) {
        $this->passwordReset = $passwordReset;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle() {
        $this->passwordReset->delete();
    }
}
