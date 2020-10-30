<?php

namespace App\Http\Controllers\Accounting;

use Auth;
use Client;
use App\Services\{AuthService,
    UserService,
    Common\CommonService,
    Soundblock\ServiceTransactionService,
    Accounting\AccountingInvoiceService};
use Illuminate\Support\Collection;
use App\Facades\Accounting\Invoice;
use App\Http\Controllers\Controller;
use App\Contracts\Accounting\InvoiceContract;
use App\Models\{Soundblock\Service, Accounting\AccountingInvoice};
use App\Http\Transformers\Accounting\AccountingInvoiceTypeTransformer;
use App\Http\Requests\Accounting\{CreateInvoiceRequest, GetInvoicesRequest};

class InvoiceController extends Controller
{
    /** @var AuthService */
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @param InvoiceContract $invoiceContract
     *
     * @return \Dingo\Api\Http\Response
     * @throws \Exception
     */
    public function getInvoiceTypes(InvoiceContract $invoiceContract)
    {
        try {
            $arrInvoiceType = $invoiceContract->getInvoiceTypes();

            return ($this->response->collection($arrInvoiceType, new AccountingInvoiceTypeTransformer));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param GetInvoicesRequest $objRequest
     * @param AccountingInvoiceService $invoiceService
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Pagination\Paginator
     * @throws \Exception
     */
    public function index(GetInvoicesRequest $objRequest, AccountingInvoiceService $invoiceService)
    {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];
            if (!$this->authService->checkAuth($reqOffice)) {
                abort(403, "You have not required permission.");
            }

            return($invoiceService->findAll([], $objRequest->input("per_page")));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param CreateInvoiceRequest $objRequest
     * @param UserService $userService
     * @param CommonService $commonService
     * @param ServiceTransactionService $transactionService
     *
     * @return \Dingo\Api\Http\Response
     * @throws \Exception
     */
    public function store(CreateInvoiceRequest $objRequest, UserService $userService, CommonService $commonService, ServiceTransactionService $transactionService)
    {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];
            if (!$this->authService->checkAuth($reqOffice)) {
                abort(403, "You have not required permission.");
            }
            $chargeTypeName = $objRequest->charge_type;
            if ($objRequest->has("user")) {
                $objReceiver = $userService->find($objRequest->user, true);
                return($this->apiReply());
            } else {
                /** @var Service */
                $objService = $commonService->find($objRequest->service);
                $objReceiver = $objService->user;
                // Create a Service Transaction
                $objServiceTransaction = $transactionService->create($objService, $chargeTypeName);
                /** @var array */
                $options = [];
                $arrLineItem = $objRequest->line_items;
                $discountTotal = intval($objRequest->discount);
                foreach ($arrLineItem as $lineItem) {
                    $unitAmount = intval(floatval($lineItem["cost"]) * 100);
                    Invoice::createInvoiceItem($objReceiver, $lineItem["name"], $unitAmount, intval($lineItem["quantity"]), intval($lineItem["discount"]), $discountTotal);
                }
                $options = (new Collection($objRequest->all()))->only(["coupon", "discount"])->toArray();
                /** @var AccountingInvoice */
                $objInvoice = Invoice::createInvoiceFor($objReceiver, $objServiceTransaction, Client::app(), $objRequest->invoice_type, $objRequest->line_items, Auth::user(), $options);

                return ($this->apiReply($objInvoice->load(["invoiceType.app", "transactions.transactionType"])));
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
