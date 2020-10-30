<?php

namespace App\Console\Commands\User;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\{BaseModel, UserContactEmail};

class RemoveNotVerifiedEmails extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "user:emails:clear";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Clear user emails table of not verified emails.";

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param UserContactEmail $objUserEmail
     * @return mixed
     */
    public function handle(UserContactEmail $objUserEmail) {
        $threeDaysAgo = Carbon::now()->subDays(3);
        $objUserEmail->notVerified()->whereDate(BaseModel::CREATED_AT, "<=", $threeDaysAgo)->delete();
    }
}
