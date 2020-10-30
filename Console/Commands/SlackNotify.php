<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SlackNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'slack:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a notification to Slack.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        try {

            //TODO: Send notication.

        } catch (\Exception $exception)
        {

            $this->error($exception->getMessage());
            return(false);

        }

        $this->info("Notification sent.");

    }
}
