<?php

namespace App\Console\Commands\Soundblock;

use App\Exceptions\PaymentTaskException;
use App\Facades\Exceptions\Disaster;
use Carbon\Carbon;
use App\Models\Soundblock\{Service, ServicePlan};
use App\Contracts\Soundblock\Accounting\AccountingContract;
use Illuminate\{Console\Command, Database\Eloquent\Builder};

class ServiceTransactions extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "charge";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Charge user";

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
     * @param ServicePlan $servicePlan
     * @param AccountingContract $accounting
     * @return void
     */
    public function handle(ServicePlan $servicePlan, AccountingContract $accounting): void {
        $today = Carbon::now()->day;

        $todayPlans = $servicePlan->whereHas("service", function (Builder $query) {
            $query->where("flag_status", "active");
        })->where("plan_day", $today)->orWhere("flag_status", "past due accounts")->latest()->toSql();

        $todayPlans = $todayPlans->unique("service_id");

        /** @var ServicePlan $plan */
        foreach ($todayPlans as $plan) {
            $status = "paid";
            /**  @var Service $service */
            $service = $plan->service;

            try {
                $successPayed = $accounting->makeCharge($service, $plan->plan_cost);

                if (!$successPayed) {
                    $status = "past due accounts";
                }
            } catch (PaymentTaskException $e) {
                Disaster::handleDisaster($e);
            }

            $accounting->setPaymentStatus($service, $status);
        }
    }
}
