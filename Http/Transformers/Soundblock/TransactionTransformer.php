<?php

namespace App\Http\Transformers\Soundblock;

use App\Models\User;
use App\Models\BaseModel;
use App\Http\Transformers\BaseTransformer;
use App\Http\Transformers\User\UserTransformer;
use App\Models\Soundblock\ServiceTransaction;
use App\Traits\StampCache;
use Cache;

class TransactionTransformer extends BaseTransformer
{

    use StampCache;

    public function transform(ServiceTransaction $objTransaction)
    {
        $objAccountingTransaction = $objTransaction->accountingTransaction;

        $response = [
            "transaction_uuid"   => $objTransaction->transaction_uuid,
            "ledger_uuid"        => $objTransaction->ledger_uuid,
            "transaction_amount" => $objAccountingTransaction->transaction_amount,
            "transaction_name"   => $objAccountingTransaction->transaction_name,
            "transaction_memo"   => $objAccountingTransaction->transaction_memo,
            "transaction_status" => $objAccountingTransaction->transaction_status,
            "transaction_type"   => $objAccountingTransaction->transaction_type,
        ];

        return(array_merge($response, $this->stamp($objTransaction)));
    }

    public function includeUser(ServiceTransaction $objTransaction)
    {
        return($this->item($objTransaction->user, new UserTransformer()));
    }
}

