<?php

namespace App\Http\Controllers;

use App\AccountTransaction;
use App\ContactLedger;
use App\Transaction;
use App\AccountGroup;
use App\TransactionPayment;
use App\Utils\ModuleUtil;
use App\Utils\TransactionUtil;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerPaymentBulkController extends Controller
{
    protected $transactionUtil;
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param TransactionUtil $transactionUtil
     * @return void
     */
    public function __construct(TransactionUtil $transactionUtil, ModuleUtil $moduleUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $method = 'cash';
        $acGroup = AccountGroup::where(['id' => $request->customer_payment_bulk_payment_method])->first();
        if ($acGroup) {
            if (strpos(strtolower($acGroup->name), 'card') !== false) {
                $method = 'card';
            } else if (strpos(strtolower($acGroup->name), 'cheques') !== false) {
                $method = 'cheque';
            } else if (strpos(strtolower($acGroup->name), 'bank') !== false) {
                $method = 'bank_transfer';
            }
        }

        try {
            $business_id = request()->session()->get('business.id');
            $input['payment_ref_no'] = 'CPB-' . $request->customer_payment_bulk_payment_ref_no;
            $input['payment_for'] = $request->customer_payment_bulk_customer_id;
            $input['method'] = $method;
            $input['card_number'] = $request->customer_payment_bulk_card_number;
            $input['account_id'] = $request->customer_payment_bulk_accounting_module;
            $input['card_type'] = $request->customer_payment_bulk_card_name;
            $input['bank_name'] = $request->customer_payment_bulk_bank_name;
            $input['cheque_date'] = !empty($request->customer_payment_bulk_cheque_date) ? $this->transactionUtil->uf_date($request->customer_payment_bulk_cheque_date) : null;
            $input['cheque_number'] = $request->customer_payment_bulk_cheque_number;
            $input['bank_name'] = $request->customer_payment_bulk_bank_name;
            $input['paid_on'] = !empty($request->transaction_date) ? Carbon::parse($request->transaction_date)->format('Y-m-d') : date('Y-m-d');
            $input['business_id'] = $business_id;
            $input['created_by'] = Auth::user()->id;
            $input['paid_in_type'] = 'customer_bulk';

            DB::beginTransaction();

            $total_amount = $total_interest = 0;
            if(isset($request->paying)){
                foreach ($request->paying as $key => $paying) {
                    if ($paying != null) {
                        $total_interest += $request->interest[$key];
                    }

                    $input['transaction_id'] = $key;
                    $input['amount'] = $request->amount[$key];
                    $total_amount += $input['amount'];
                    $tp = TransactionPayment::create($input);
                    $transaction = Transaction::find($key);
                    $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);
                }
            }

            $account_id = $input['account_id'];
            $account_receivable_id = $this->transactionUtil->account_exist_return_id('Accounts Receivable');
            if(isset($request->paying)) {
                $account_transaction_data = [
                    'amount' => $total_amount,
                    'interest' => $total_interest,
                    'account_id' => $account_id,
                    'type' => 'debit',
                    'operation_date' => Carbon::parse($request->transaction_date)->format('Y-m-d H:i:s'),
                    'created_by' => Auth::user()->id,
                    'transaction_id' => !empty($transaction) ? $transaction->id : null,
                    'transaction_payment_id' => $tp->id,
                    'cheque_number' =>  @$input['cheque_number'],
                    'cheque_date' =>  @$input['cheque_date'],
                    'bank_name' =>  @$input['bank_name'],
                    'payment_method'=> @$input['method']
                ];

                $return = AccountTransaction::createAccountTransaction($account_transaction_data);
                $account_transaction_data['account_id'] = $account_receivable_id;
                $account_transaction_data['type'] = 'credit';
                $receivable = AccountTransaction::createAccountTransaction($account_transaction_data);//Accounts Receivable
                $account_customer_interest_id = $this->transactionUtil->account_exist_return_id('Customer Interest Account');

                if($account_customer_interest_id != 0 || $account_customer_interest_id != null){
                    $account_customer_interest_id = DB::table('contacts')->where('id', $request->customer_payment_bulk_customer_id)->first()->customer_group_id;
                }

                $account_transaction_data['account_id'] = $account_customer_interest_id;
                $account_transaction_data['amount'] = $total_interest;
                $account_transaction_data['interest']=0;
                $account_transaction_data['type'] = 'credit';

                $customerInterest = AccountTransaction::createAccountTransaction($account_transaction_data);//Customer Interest Income

                $account_transaction_data['amount'] = $total_amount;
                $account_transaction_data['contact_id'] = $transaction->contact_id;
                $account_transaction_data['sub_type'] = 'payment';
                $account_transaction_data['interest']=$total_interest;
                ContactLedger::createContactLedger($account_transaction_data);
            }
            DB::commit();

            $output = [
                'success' => true,
                'tab' => 'bulk',
                'msg' => __('lang_v1.payment_added_success')
            ];
        } catch (\Exception $e) {
            $error = 'File: ' . $e->getFile() . 'Line: ' . $e->getLine() . 'Message: ' . $e->getMessage();
            Log::emergency($error);
            $output = [
                'success' => false,
                'tab' => 'bulk',
                'msg' => __('messages.something_went_wrong')
            ];
        }

        return redirect()->back()->with('status', $output);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function bulkPaymentTable(Request $request)
    {
        $latest_ref_number_CPB = 0;
        try {
            $latest_ref_number_CPB = DB::table('transaction_payments')->where('paid_in_type', 'customer_bulk')->orderBy('created_at', 'DESC')->first()->payment_ref_no;
            $latest_ref_number_CPB = (int)explode('-', $latest_ref_number_CPB)[1];

        } catch (\Exception $exception) {}

        $latest_ref_number_CPB = $latest_ref_number_CPB+1;
        $sells = Transaction::leftjoin('transaction_payments', 'transactions.id', 'transaction_payments.transaction_id')
            ->whereIn('transactions.payment_status', ['due', 'partial'])
            ->where('transactions.contact_id', $request->customer_id)
            ->where('transactions.type','!=', 'security_deposit')
            ->select(
                'transactions.id as transaction_id',
                'transactions.transaction_date',
                'transactions.cheque_return_charges',
                'transaction_payments.cheque_number',
                'transaction_payments.bank_name',
                'transaction_payments.is_return',
                'transactions.invoice_no',
                'transactions.ref_no',
                'transactions.order_no',
                'transactions.final_total',
                'transactions.type',

                DB::raw('SUM(transaction_payments.amount) as total_paid')
            )->groupBy('transactions.id')->get();

        return view('customer_payments.partials.bulk_payment_table')->with(compact('sells', 'latest_ref_number_CPB'));
    }
}
