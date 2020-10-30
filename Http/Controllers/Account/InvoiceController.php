<?php

namespace App\Http\Controllers\Account;

use Auth;
use Dingo\Api\Contract\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Transformers\Account\InvoiceTransformer;
use App\Repositories\Accounting\AccountingInvoiceRepository;
use App\Http\Transformers\Accounting\{
    AccountingInvoiceTransformer,
    AccountingInvoiceTypeTransformer
};

class InvoiceController extends Controller {
    /**
     * @var AccountingInvoiceRepository
     */
    private AccountingInvoiceRepository $accountingInvoiceRepository;

    /**
     * InvoiceController constructor.
     * @param AccountingInvoiceRepository $accountingInvoiceRepository
     */
    public function __construct(AccountingInvoiceRepository $accountingInvoiceRepository) {
        $this->accountingInvoiceRepository = $accountingInvoiceRepository;
    }

    /**
     * @group Accounting
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     */
    public function getUserInvoices(Request $request) {
        $perPage = $request->input('per_page', 10);

        $objInvoices = $this->accountingInvoiceRepository->getUserInvoices(Auth::user()->user_uuid, $perPage);

        return ($this->response->paginator($objInvoices, new AccountingInvoiceTransformer(["app"])));
    }

    /**
     * @group Accounting
     * @urlParam InvoiceUUID required Invoice UUID
     * @param string $strInvoiceUUID
     * @return \Dingo\Api\Http\Response
     */
    public function getInvoiceByUUID(string $strInvoiceUUID) {
        $objInvoice = $this->accountingInvoiceRepository->getInvoiceByUUID($strInvoiceUUID);

        if (is_null($objInvoice)) {
            abort(404, "Invoice doesn't exist");
        }

        return ($this->response->item($objInvoice, new InvoiceTransformer(["app"])));
    }

    /**
     * @group Accounting
     * @urlParam InvoiceUUID required Invoice UUID
     * @param string $strInvoiceUUID
     * @return \Dingo\Api\Http\Response
     */
    public function getInvoiceType(string $strInvoiceUUID) {
        $objInvoice = $this->accountingInvoiceRepository->getInvoiceByUUID($strInvoiceUUID);

        if (is_null($objInvoice)) {
            abort(404, "Invoice doesn't exist");
        }

        return ($this->response->item($objInvoice->invoiceType, new AccountingInvoiceTypeTransformer));
    }
}
