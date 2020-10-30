<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Log;
use Symfony\Component\Process\Process;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run phpunit tests.';

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

        Log::debug("Test Command message " . microtime(true));
        $intTimeLimit = 320;
        set_time_limit($intTimeLimit);

        $objProcess = new Process(array("vendor".DIRECTORY_SEPARATOR."bin".DIRECTORY_SEPARATOR."phpunit -c phpunit.xml"));
        $objProcess->setWorkingDirectory(base_path());
        $objProcess->setTimeout($intTimeLimit);

        return($objProcess->run(function($type, string $strBuffer)
        {
            echo($strBuffer);
        }));
    }
}
