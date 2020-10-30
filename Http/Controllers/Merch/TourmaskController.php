<?php

namespace App\Http\Controllers\Merch;

use Illuminate\Support\Facades\Mail;
use App\Mail\Tourmask\HandleOrderMail;
use App\Http\{Controllers\Controller, Requests\Tourmask\HandleOrderRequest};

class TourmaskController extends Controller
{
    /**
     * @group Merch
     * @bodyParam first_name string required
     * @bodyParam last_name string required
     * @bodyParam organization string required
     * @bodyParam email string required
     * @bodyParam message string required
     *
     * @param HandleOrderRequest $handleOrderRequest
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function handleOrder(HandleOrderRequest $handleOrderRequest) {
        try {
            Mail::to(env("MERCH_ORDER_EMAIL"))->send(new HandleOrderMail($handleOrderRequest->all()));

            return response()->json("");
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
