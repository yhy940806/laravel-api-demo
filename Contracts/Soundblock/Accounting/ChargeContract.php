<?php

namespace App\Contracts\Soundblock\Accounting;

use App\Models\{Core\App, Soundblock\Service, Soundblock\ServiceTransaction};

interface ChargeContract {
    public function chargeService(Service $service, string $chargeType, App $app): ServiceTransaction;
}
