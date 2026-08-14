<?php

namespace App\Http\Controllers;

use App\Actions\SalesInvoicePayment\SalesInvoicePaymentActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Enums\PaymentTypeEnum;
use App\Helpers\HashidsHelper;
use App\Http\Resources\SalesInvoicePaymentResource;
use App\Models\SalesInvoicePayment;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SalesInvoicePaymentController extends BaseController
{
    public function __construct(
        private readonly SalesInvoicePaymentActions $salesInvoicePaymentActions,
    ) {
        parent::__construct();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', SalesInvoicePayment::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'sales_invoice_id' => $request->filled('sales_invoice_id') ? HashidsHelper::decodeId($request->sales_invoice_id) : null,
            'cash_account_id' => $request->filled('cash_account_id') ? HashidsHelper::decodeId($request->cash_account_id) : null,
            'sales_order_payment_id' => $request->filled('sales_order_payment_id') ? HashidsHelper::decodeId($request->sales_order_payment_id) : null,
            'sales_return_id' => $request->filled('sales_return_id') ? HashidsHelper::decodeId($request->sales_return_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],

            'start_date' => ['nullable', 'string', new IsValidDate('Y-m-d H:i:s')],
            'end_date' => ['nullable', 'string', new IsValidDate('Y-m-d H:i:s'), 'after_or_equal:start_date'],
            'sales_invoice_id' => ['nullable', 'integer', new ExistsForCompany('sales_invoices', $request->company_id)],
            'payment_type' => ['nullable', Rule::enum(PaymentTypeEnum::class)],
            'cash_account_id' => ['nullable', 'integer', new ExistsForCompany('cash_accounts', $request->company_id)],
            'sales_order_payment_id' => ['nullable', 'integer', new ExistsForCompany('sales_order_payments', $request->company_id)],
            'sales_return_id' => ['nullable', 'integer', new ExistsForCompany('sales_returns', $request->company_id)],

            'refresh' => ['required', 'boolean'],
            'paginate' => ['nullable', 'array', 'required_without:get', 'prohibits:get'],
            'paginate.page' => ['required_with:paginate', 'integer', 'min:1'],
            'paginate.per_page' => ['required_with:paginate', 'integer', 'min:1'],
            'get' => ['nullable', 'array', 'required_without:paginate', 'prohibits:paginate'],
            'get.limit' => ['required_with:get', 'integer', 'min:1'],
        ]);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->salesInvoicePaymentActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,
                startDate: $validatedRequest['start_date'] ?? null,
                endDate: $validatedRequest['end_date'] ?? null,
                salesInvoiceId: $validatedRequest['sales_invoice_id'] ?? null,
                paymentType: $validatedRequest['payment_type'] ?? null,
                cashAccountId: $validatedRequest['cash_account_id'] ?? null,
                salesOrderPaymentId: $validatedRequest['sales_order_payment_id'] ?? null,
                salesReturnId: $validatedRequest['sales_return_id'] ?? null,
                execute: new ExecuteDTO(
                    useCache: ! $validatedRequest['refresh'],
                    pagination: isset($validatedRequest['paginate'])
                        ? new ExecutePaginationDTO(
                            page: $validatedRequest['paginate']['page'],
                            perPage: $validatedRequest['paginate']['per_page'],
                        )
                        : null,
                    get: isset($validatedRequest['get'])
                        ? new ExecuteGetDTO(limit: $validatedRequest['get']['limit'])
                        : null,
                ),
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result)
            ? response()->error($errorMsg)
            : SalesInvoicePaymentResource::collection($result);
    }

    public function read(SalesInvoicePayment $salesInvoicePayment)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $salesInvoicePayment);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->salesInvoicePaymentActions->read($salesInvoicePayment);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result)
            ? response()->error($errorMsg)
            : new SalesInvoicePaymentResource($result);
    }
}
