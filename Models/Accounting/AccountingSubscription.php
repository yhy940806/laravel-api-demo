<?php

namespace App\Models\Accounting;

use App\Models\BaseModel;
use App\Traits\BaseScalable;
use Laravel\Cashier\Subscription;

class AccountingSubscription extends Subscription
{
    use BaseScalable;

    protected $table = "users_accounting_stripe_subscriptions";

    protected $primaryKey = "subscription_id";

    protected $uuid = "subscription_uuid";

    protected $dates = [
        "trial_ends_at", "ends_at",
        BaseModel::CREATED_AT, BaseModel::UPDATED_AT
    ];
}
