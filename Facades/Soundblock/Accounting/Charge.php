<?php


namespace App\Facades\Soundblock\Accounting;

use App\Models\Core\App;
use App\Models\Soundblock\{Service, ServiceTransaction};
use Illuminate\Support\Facades\Facade;

/**
 * @method static ServiceTransaction chargeService(Service $service, string $chargeType, App $app)
 */
class Charge extends Facade
{
    protected static function getFacadeAccessor()
    {
        return "charge";
    }
}
