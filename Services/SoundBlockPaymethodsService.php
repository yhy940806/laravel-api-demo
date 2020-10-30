<?php

namespace App\Services;

use Auth;
use App\Models\UsersPaymethods;

class SoundBlockPaymethodsService
{
    public function create(array $arrPaymethods) : UsersPaymethods
    {
        $objPaymethods = new UsersPaymethods();
        $objPaymethods->user_uuid = Auth::user()->uuid;

        return ($this->update($objPaymethods, $arrPaymethods));
    }

    public function update(UsersPaymethods $objPaymethods, array $arrPaymethods) : UsersPaymethods
    {
        $objPaymethods->paypal_email = $arrPaymethods["paypal_email"];
        $objPaymethods->account_number = $arrPaymethods["account_number"];
        $objPaymethods->routing_number = $arrPaymethods["routing_number"];

        $objPaymethods->save();

        return ($objPaymethods);
    }
}
