<?php

namespace App\Providers;

use Blade;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Common\LogRepository;
use App\Services\{Cache\CacheService,
    Common\ServicePlanService,
    Core\ArenaService,
    Payment\PaymentService,
    Soundblock\InviteService,
    Soundblock\LedgerService,
    Ledger\LedgerCacheService,
    Exceptions\DisasterService,
    Soundblock\Accounting\ChargeService,
    Soundblock\Accounting\AccountingService,
    Soundblock\Contracts\ContractService,
    Accounting\AccountingInvoiceService
};
use App\Contracts\{Cache\CacheContract,
    Core\ArenaContract,
    Exceptions\DisasterContract,
    Accounting\InvoiceContract,
    Payment\PaymentContract,
    Soundblock\LedgerContract,
    Soundblock\Invite\InviteContract,
    Soundblock\Accounting\ChargeContract,
    Soundblock\Accounting\AccountingContract,
    Soundblock\Ledger\LedgerCacheContract,
    Soundblock\Service\ServicePlanContract,
    Soundblock\Contracts\SmartContractsContract
};
use Laravel\{Cashier\Cashier, Passport\Passport};
use App\Contracts\Accounting\InvoiceTransactionContract;
use App\Services\Accounting\AccountingInvoiceTransactionService;
use App\Repositories\Accounting\{AccountingFailedPaymentsRepository, AccountingInvoiceRepository};

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        Passport::ignoreMigrations();
        Cashier::ignoreMigrations();

        Passport::loadKeysFrom(storage_path("keys"));

        $this->app->bind(LedgerContract::class, function () {
            $token = config("ledger.token");

            if (is_null($token)) {
                abort(403, "Ledger Service Token did\"t set up");
            }
            return new LedgerService(config("ledger.host"), $token);
        });

        $this->app->bind(PaymentContract::class, PaymentService::class);

        $this->app->bind(AccountingContract::class, function () {
            $chargeNotDefault = (bool) env("CHARGE_NOT_DEFAULT", false);

            return new AccountingService($chargeNotDefault, resolve(AccountingInvoiceRepository::class), resolve(AccountingFailedPaymentsRepository::class));
        });

        $this->app->bind(SmartContractsContract::class, ContractService::class);
        $this->app->bind(LedgerCacheContract::class, LedgerCacheService::class);
        $this->app->bind(DisasterContract::class, function () {
            return new DisasterService(config("disaster.slack_webhook"), resolve(LogRepository::class));
        });
        $this->app->bind(ServicePlanContract::class, ServicePlanService::class);

        $this->app->bind("disaster", function () {
            return $this->app->make(DisasterContract::class);
        });

        $this->app->bind(InviteContract::class, InviteService::class);

        $this->app->bind(ChargeContract::class, ChargeService::class);

        $this->app->bind("charge", function () {
            return $this->app->make(ChargeContract::class);
        });

        $this->app->bind(InvoiceContract::class, AccountingInvoiceService::class);
        $this->app->bind("invoice", function () {
            return $this->app->make(InvoiceContract::class);
        });

        $this->app->bind(InvoiceTransactionContract::class, AccountingInvoiceTransactionService::class);

        $this->app->singleton(CacheContract::class, CacheService::class);

        $this->app->singleton("app-cache", function () {
            return $this->app->make(CacheContract::class);
        });

        $this->app->singleton(ArenaContract::class, ArenaService::class);

        $this->app->singleton("arena", function () {
            return $this->app->make(ArenaContract::class);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        Blade::component("mail.components.header", "header");
    }
}
