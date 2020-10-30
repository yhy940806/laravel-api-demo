<?php

namespace App\Http\Transformers\Common;

use App\Http\Transformers\BaseTransformer;
use App\Models\Common\QueueJob;
use Util;

class JobTransformer extends BaseTransformer
{

    public function transform(QueueJob $objJob)
    {
        $objUser = $objJob->user;
        $res = [
            "job_uuid" => $objJob->job_uuid,
            "flag_silentalert" => $objJob->flag_silentalert,
            "flag_status" => $objJob->flag_status,
            QueueJob::STAMP_START => $objJob->{QueueJob::STAMP_START},
            QueueJob::STAMP_STOP => $objJob->{QueueJob::STAMP_STOP},
        ];

        if ($this->resType == "office")
        {
            $res["job_name"] = $objJob->job_name;
            $res["job_memo"] = $objJob->job_memo;
            $res["job_script"] = $objJob->job_script;
            $res["user"] = [
                "data" => [
                    "user_uuid" => $objUser->user_uuid,
                    "avatar_url" => Util::avatar_url($objUser),
                    "name_first" => $objUser->name_first,
                    "name_middle" => $objUser->name_middle,
                    "name_last" => $objUser->name_last,
                ]
            ];
        }

        return($res);
    }
}
