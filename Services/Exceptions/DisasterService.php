<?php

namespace App\Services\Exceptions;

use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Models\Common\{Log, LogError};
use Illuminate\Database\QueryException;
use App\Repositories\Common\LogRepository;
use App\Contracts\Exceptions\{DisasterContract, ExceptionContract};

class DisasterService implements DisasterContract {
    /**
     * @var Log
     */
    private Log $log;
    /**
     * @var Client
     */
    private Client $http;
    /**
     * @var string|null
     */
    private ?string $slackUrl;
    /**
     * @var LogRepository
     */
    private LogRepository $logRepository;

    /**
     * DisasterService constructor.
     * @param string|null $slackUrl
     * @param LogRepository $logRepository
     */
    public function __construct(?string $slackUrl, LogRepository $logRepository) {
        $this->http = new Client();
        $this->slackUrl = $slackUrl;
        $this->logRepository = $logRepository;
    }

    public function handleDisaster(ExceptionContract $exception) {
        $timeLastException = Carbon::now()->subMinutes(15);
        try{
            if($this->logRepository->canSkipLog($timeLastException, $exception)) {
                return;
            }

            $log = $this->logRepository->createLog($exception);
            $logError = $log->logError;

            if (config("app.env") != "local") {
                if(!empty($this->slackUrl)) {
                    $this->sendSlackNotification($logError);
                }

//            Mail::to(config("disaster.email"))->send(new DisasterMail($logError, $exception->getTraceAsString()));
            }

            } catch(QueryException $exception){
                if($exception->getCode() == "42S02" || $exception->getCode() == "HY000") {
                    return;
                }

                throw new QueryException($exception->getSql(), $exception->getBindings(), $exception);
            }
    }

    private function sendSlackNotification(LogError $logError) {
        $fields = [
            [
                "type" => "mrkdwn",
                "text" => "*Code:*\n" . $logError->exception_code
            ],
            [
                "type" => "mrkdwn",
                "text" => "*When:*\n" . $logError->stamp_created_at
            ]
        ];

        if (!is_null($logError->log_url)) {
            $fields[] = [
                "type" => "mrkdwn",
                "text" => "*Endpoint:*\n" . $logError->log_url
            ];
            $fields[] = [
                "type" => "mrkdwn",
                "text" => "*Method:*\n" . $logError->log_method
            ];
        } elseif (!is_null($logError->log_command)) {
            $fields[] = [
                "type" => "mrkdwn",
                "text" => "*Command:*\n" . $logError->log_command
            ];
        }

        if(!is_null($logError->user_uuid)) {
            $fields[] = [
                "type" => "mrkdwn",
                "text" => "*User UUID:*\n" . $logError->user_uuid
            ];
        }

        $this->http->post($this->slackUrl, [
            "json" => [
                "blocks" => [
                    [
                        "type" => "section",
                        "text" => [
                            "type" => "mrkdwn",
                            "text" =>  "New exception has been handled! UUID: " . $logError->row_uuid,
                        ]
                    ],
                    [
                        "type" => "section",
                        "text" => [
                            "type" => "mrkdwn",
                            "text" => "*Message:*\n" . $logError->exception_message
                        ]
                    ],
                    [
                        "type" => "section",
                        "text" => [
                            "type" => "mrkdwn",
                            "text" => "*Exception Class:*\n" . $logError->exception_class
                        ]
                    ],
                    [
                        "type" => "section",
                        "fields" => $fields
                    ]
                ]
            ]
        ]);
    }
}
