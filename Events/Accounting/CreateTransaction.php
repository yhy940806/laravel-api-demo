<?php

namespace App\Events\Accounting;

use Log;
use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Broadcasting\PrivateChannel;
use App\Models\Accounting\AccountingInvoice;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class CreateTransaction {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Model
     */
    public Model $instance;
    /**
     * @var AccountingInvoice
     */
    public AccountingInvoice $objInvoice;
    /**
     * @var array
     */
    public array $arrOptions;
    /**
     * @var User|null
     */
    public ?User $objOfficeUser;

    /**
     * Create a new event instance.
     * @param Model $instance
     * @param AccountingInvoice $objInvoice
     * @param array $arrOptions
     * @param User|null $objOfficeUser
     * @return void
     * @throws \Exception
     */
    public function __construct(Model $instance, AccountingInvoice $objInvoice, array $arrOptions, ?User $objOfficeUser = null) {
        if (!method_exists($instance, "accountingInvoiceTransaction")) {
            throw new \Exception("Invalid Parameter.", 400);
        }

        $this->instance = $instance;
        $this->objInvoice = $objInvoice;
        $this->arrOptions = $arrOptions;
        $this->objOfficeUser = $objOfficeUser;

        Log::info('Create-Contract', [$this->instance->transaction_id]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn() {
        return new PrivateChannel('channel-name');
    }
}
