<?php

namespace App\Models\Soundblock;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class ContractHistory extends BaseModel
{
    protected $table = "soundblock_projects_contracts_history";

    protected $casts = [
        "contract_state" => "array",
    ];
}
