<?php

namespace App\Http\Controllers\Chequer;

use App\Account;
use App\Chequer\ChequeNumberMaintain;
use App\Chequer\ChequerBankAccount;
use App\Chequer\ChequerCurrency;
use App\Chequer\ChequerDefaultSetting;
use App\Chequer\ChequerPurchaseOrder;
use App\Chequer\ChequerStamp;
use App\Chequer\ChequerSupplier;
use App\Chequer\ChequeTemplate;
use App\BusinessLocation;
use App\Chequer\PrintedChequeDetail;
use App\Utils\BusinessUtil;
use App\Contact;
use App\ContactLedger;
use App\Events\TransactionPaymentAdded;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Utils\TransactionUtil;
use App\Transaction;
use App\TransactionPayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Utils\ModuleUtil;
use App\Utils\Util;

use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
class ManagePayeeController extends Controller
{
    protected $moduleUtil;
    protected $businessUtil;
    protected $transactionUtil;
    protected $commonUtil;
    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(Util $commonUtil,ModuleUtil $moduleUtil,BusinessUtil $businessUtil,TransactionUtil $transactionUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->commonUtil = $commonUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        $business_id = request()->session()->get('business.id');
         
        if (request()->ajax()) {

            //Check if subscribed or not, then check for location quota
            if (!$this->moduleUtil->isSubscribed(request()->session()->get('business.id'))) {
                return $this->moduleUtil->expiredResponse();
            }
            $contacts = Contact::where('business_id', $business_id)->where('is_payee', 1)
                                ->where('type', 'supplier');

            return Datatables::of($contacts)
                ->addColumn('action', function ($row) {
                    if($row->is_payee)
                    {
                        $html = '<div class="btn-group">
                        <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                            data-toggle="dropdown" aria-expanded="false">' .
                            __("messages.actions") .
                            '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu">
                        <li><a href="javascript:void(0);" data-toggle="modal" data-target="#myModal" data-id="'.$row->id.'" data-name="'.$row->name.'" class="edit_payee_button"><i class="glyphicon glyphicon-edit"></i> Edit</a></li>
                        <li><a href="'.url('ledger', [$row->id]).'">
                        <i class="fa fa-anchor" aria-hidden="true"></i>Ledger</a></li>
                        <li><a href="javascript:void(0);" data-url="'.url('delete-payees', [$row->id]).'" class="delete_payee_button"><i class="fa fa-trash"></i> Delete</a></li>
                        ';
                        $html .=  '</ul></div>';
                    }
                    else
                      $html='';
                    return $html;
                })
                ->editColumn('created_date', '{{date("Y-m-d", strtotime($created_at))}}')
                // ->rawColumns(['action'])
                ->make(true);
        }
        return view('chequer/payee/index');

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
     
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $id=$request->id;
        try {
            $business_id = $request->session()->get('business.id');
            $msg='Added';
            if($id)
            {
                $contact=Contact::find($id);
                $msg='Updated';
            }
            else
                $contact =new  Contact();
            $contact->business_id=$business_id;
            $contact->name=$request->payee_name;
            $contact->is_payee=1;
            $contact->created_by=$request->session()->get('user.id');
            $contact->save();
            $output = [
                'success' => 1,
                'msg' => 'Default Setting '.$msg.' Successfully'
            ];
        } catch (\Exception $e) {
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong')
            ];
        }
       return back()->with('status', $output);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }
    public function getLedger($id)
    {
        // dd("ll");
        if (!auth()->user()->can('supplier.view') && !auth()->user()->can('customer.view')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $asset_account_id = Account::leftjoin('account_types', 'accounts.account_type_id', 'accounts.id')
            ->where('account_types.name', 'like', '%Assets%')
            ->where('accounts.business_id', $business_id)
            ->pluck('accounts.id')->toArray();
        $contact_id = $id;
        
        $ledger_date_range = request()->ledger_date_range;
        
        if($ledger_date_range){
            $dates = explode(' - ', $ledger_date_range);
            $start_date = date('Y-m-d',strtotime($dates[0]));
            $end_date = date('Y-m-d',strtotime($dates[1]));
        }else{
            $start_date = date('Y-m-d');
            $end_date = date('Y-m-d');
        }

        $transaction_type =  request()->transaction_type;
        $transaction_amount =  request()->transaction_amount;
        $contact = Contact::find($contact_id);
        $business_details = $this->businessUtil->getDetails($contact->business_id);
        $location_details = BusinessLocation::where('business_id', $contact->business_id)->first();
        $opening_balance = Transaction::where('contact_id', $contact_id)->where('type', 'opening_balance')->where('payment_status','due')->sum('final_total');

        // dd($opening_balance);

        $ledger_details = $this->__getLedgerDetails($contact_id, $start_date, $end_date);
        
            // dd("sup");
        $opening_balance_new = DB::select("select `cl`.`amount` as opening_balance
            from `transactions` t left join `contact_ledgers` cl on `cl`.`transaction_id` = `t`.`id`
            left join `business_locations` bl on `t`.`location_id` = `bl`.`id`
            where `cl`.`contact_id` = " . $contact_id . "
            and `cl`.`type` = 'debit'
            and `t`.`business_id` = " . $business_id . "
            and `t`.`type` = 'opening_balance'
            and date(`cl`.`operation_date`) >= '" . $start_date . "'
            and date(`cl`.`operation_date`) <= '" . $end_date . "'
            order by `cl`.`operation_date` limit 2");
        
        $query = ContactLedger::leftjoin('transactions', 'contact_ledgers.transaction_id', 'transactions.id')
                ->leftjoin('transaction_payments', 'contact_ledgers.transaction_payment_id', 'transaction_payments.id')
                ->leftjoin('business_locations', 'transactions.location_id', 'business_locations.id')
                ->leftjoin('account_transactions', 'transactions.id', 'account_transactions.transaction_id')
                ->leftjoin('accounts', 'account_transactions.account_id', 'accounts.id')
                ->where('transaction_payments.payment_for', $contact_id)
                ->where('transactions.business_id', $business_id)
                ->select(
                    'contact_ledgers.*',
                    'contact_ledgers.type as acc_transaction_type',
                    'business_locations.name as location_name',
                    'account_transactions.interest',
                    'transactions.ref_no',
                    'transactions.invoice_no',
                    'transactions.transaction_date',
                    'transactions.payment_status',
                    'transactions.pay_term_number',
                    'transactions.pay_term_type',
                    'transaction_payments.method as payment_method',
                    'transaction_payments.payment_type',
                    'transaction_payments.bank_name',
                    'transaction_payments.cheque_date',
                    'transaction_payments.cheque_number',
                    //'transaction_payments.interest',
                    'transactions.type as transaction_type',
                    'accounts.account_number',
                    'accounts.name as account_name'
                )->groupBy('contact_ledgers.id')->orderBy('contact_ledgers.id', 'asc');

				$ledger_details['bf_balance'] = 0;
		
        if (!empty($start_date)  && !empty($end_date)) {
            $query->whereDate('contact_ledgers.operation_date', '>=', $start_date);
            $query->whereDate('contact_ledgers.operation_date', '<=', $end_date);
            // $query->whareBetween("transaction_payments.paid_on", [$start_date, $end_date]);
        }
        if (!empty($transaction_type)) { // debit / credit type filter
            $query->where('contact_ledgers.type', $transaction_type);
        }
        if (!empty($transaction_amount)) {
            $query->where('contact_ledgers.amount', $transaction_amount);
        }
       $query->orderby('contact_ledgers.operation_date');
        // $query->skip(0)->take(5);
        // $ledger_transactions = $query->get();
        // \DB::connection()->enableQueryLog();
        
        $ledger_transactions = $query->get();
        // $queries = \DB::getQueryLog();
        // dd($queries);

        // dd($ledger_details['beginning_balance']);

                // dd($opening_balance_new);
        if ($contact->type == 'customer') {
            // dd($ledger_transactions);
            $total_paid = $skipped_cr = 0;
            $dateTimestamp1 = date('Y-m-d',strtotime($contact->created_at));
            foreach($ledger_transactions->toArray() as $val) {


               if($val['acc_transaction_type'] == 'credit') {
                    if(!empty($val['transaction_payment_id'])){
                        $transaction_payment = TransactionPayment::where('id', $val['transaction_payment_id'])->withTrashed()->first();
                    }
                    $amount = 0;
                    if(!empty($transaction_payment)){
                        if(empty($transaction_payment->transaction_id)){ // if empty then it will be parent payment
                            $amount = $transaction_payment->amount;  // show parent transaction payment amount
                        }else{
                            $amount = $val['amount']; // get the amount from contact ledger if not a payment
                        }
                    }else{
                        $amount = $val['amount'];
                    }
                    if($val['transaction_type'] === 'opening_balance' && $val['final_total'] < 0) {
                        if(strtotime($start_date) < strtotime($val['transaction_created_at'])) {
                            $ledger_details['opening_balance'] = $val['final_total'];
                        }
                    }
                    // fixed total amount
                    $total_paid = $total_paid + $amount;
                    // else {
                    //     $total_paid = $total_paid + $amount;
                    // }

                    // fixed total amount
					if($val['transaction_type'] == 'opening_balance' ){
                        $dateTimestamp1 = date('Y-m-d',strtotime($val['transaction_date'])) ;
                        $skipped_cr += $amount;
                       continue;
                    }

               }

            }
            //$opening_balance = $ledger_details['beginning_balance'];
            // dd($dateTimestamp1);
            // $dateTimestamp2 = strtotime($date2);
            // dd($ledger_details['beginning_balance']);
            $ledger_details['total_paid'] = $total_paid;
            $ledger_details['bf_balance'] = $ledger_details['beginning_balance'] = count($opening_balance_new) > 0 ? $opening_balance_new[0]->opening_balance : 0;
            // dd($opening_balance_new);
            // $ledger_details['beginning_balance'] = count($bg_bl) > 0 ? $bg_bl[0]->opening_balance : 0;
            $ledger_details['balance'] = $ledger_details['balance_due'] = $ledger_details['beginning_balance'] + $ledger_details['total_invoice'] - ($ledger_details['total_paid']);
            // dd($ledger_details);
            //    dd($ledger_details);
            if(!empty($start_date) && $dateTimestamp1 >= $start_date){
                $ledger_details['beginning_balance'] =0;
                 if(count($ledger_details['ledger']) > 0) { $ledger_details['bf_balance'] =0 ; } else{$ledger_details['bf_balance'] =$ledger_details['balance_due'];$ledger_details['beginning_balance'] =$ledger_details['balance_due'];};
                  //$ledger_details['balance_due'] = 0;
                $ledger_details['balance'] = 0;

            }
            if(!empty($start_date) && $dateTimestamp1 > $start_date){
                // echo "Inside Con<br>";
               $ledger_details['beginning_balance'] =0;
                if(count($ledger_details['ledger']) > 0) { $ledger_details['bf_balance'] =0 ; } else{$ledger_details['bf_balance'] =$ledger_details['balance_due'];$ledger_details['beginning_balance'] =$ledger_details['balance_due'];};
               $ledger_details['balance_due'] -=  $skipped_cr ;
            }else{
                 //$ledger_details['balance'] -=  $skipped_cr ;
            }
        }
        // dd($ledger_details['beginning_balance']);

        // dd($ledger_transactions);
        //  $ledger_details['bf_balance'] = 0;
        //   $ledger_details['beginning_balance'] =0;
	    //dd($ledger_transactions);
	    //dd((count($ledger_details['ledger']) > 0) ? 1 : 0  , $ledger_details);

	    $total_debit = 0;
	    $total_credit = 0;

	    foreach($ledger_transactions as $value){
	        if($value->type == "debit" && $value->payment_status != 'due'){
	            $total_debit += $value->amount;
	           // var_dump('debit', $value->amount , $total_debit);
	        }else if($value->type == "credit"){

	             $total_credit += $value->amount;
	             //var_dump('credit', $value->amount , $total_credit);
	        }
	    }

	   // dd($total_debit , $total_credit);

        if (request()->input('action') == 'pdf') {
            $for_pdf = true;
            $html = view('chequer.payee.ledger')
                ->with(compact('ledger_details', 'contact', 'opening_balance_new','for_pdf', 'ledger_transactions', 'business_details', 'location_details'))->render();
            $mpdf = $this->getMpdf();
            $mpdf->WriteHTML($html);
            $mpdf->Output();
            exit;
        }

        $customer_created_date = auth()->user()->created_at->toArray();
        if(!isset($ledger_details['opening_balance'])) {
            $ledger_details['opening_balance'] = 0;
        }
        if (request()->input('action') == 'print') {
            $for_pdf = true;
            return view('chequer.payee.ledger')
                ->with(compact('ledger_details', 'contact','opening_balance_new', 'for_pdf', 'ledger_transactions', 'business_details', 'location_details'))->render();
        }
        $transaction_amounts = ContactLedger::where('contact_id', $id)->distinct('amount')->pluck('amount');
        return view('chequer.payee.ledger')
        ->with(compact('ledger_details', 'contact','transaction_amounts', 'opening_balance','opening_balance_new', 'ledger_transactions', 'business_details', 'location_details','ledger_date_range'));
    }
    private function __getLedgerDetails($contact_id, $start, $end)
    {
        $contact = Contact::where('id', $contact_id)->first();
        //Get transaction totals between dates
        $transactions = $this->__transactionQuery($contact_id, $start, $end)
            ->with(['location'])->get();
        $transaction_types = Transaction::transactionTypes();
        //Get sum of totals before start date



        // \DB::connection()->enableQueryLog();
        $previous_transaction_sums = $this->__transactionQuery($contact_id, $start)
            ->select(
                DB::raw("SUM(IF(type = 'purchase', final_total, 0)) as total_purchase"),
                DB::raw("SUM(IF(type = 'sell' AND status = 'final', final_total, 0)) as total_invoice"),
                DB::raw("SUM(IF(type = 'sell_return', final_total, 0)) as total_sell_return"),
                DB::raw("SUM(IF(type = 'purchase_return', final_total, 0)) as total_purchase_return"),
                DB::raw("SUM(IF(type = 'opening_balance', final_total, 0)) as opening_balance")
            )->first();
        // $queries = \DB::getQueryLog();
        // dd($queries);



        $ledger = [];
        foreach ($transactions as $transaction) {
            $ledger[] = [
                'date' => $transaction->transaction_date,
                'ref_no' => in_array($transaction->type, ['sell', 'sell_return']) ? $transaction->invoice_no : $transaction->ref_no,
                'type' => $transaction_types[$transaction->type],
                'location' => $transaction->location->name,
                'payment_status' =>  __('lang_v1.' . $transaction->payment_status),
                'total' => $transaction->final_total,
                'payment_method' => '',
                'debit' => '',
                'credit' => '',
                'others' => $transaction->additional_notes
            ];
        }
        $invoice_sum = $transactions->where('type', 'sell')->sum('final_total');
        $purchase_sum = $transactions->where('type', 'purchase')->sum('final_total');
        $sell_return_sum = $transactions->where('type', 'sell_return')->sum('final_total');
        $purchase_return_sum = $transactions->where('type', 'purchase_return')->sum('final_total');
        $opening_balance_sum = $transactions->where('type', 'opening_balance')->sum('final_total');
        //Get payment totals between dates
        $payments = $this->__paymentQuery($contact_id, $start, $end)
            ->select('transaction_payments.*', 'bl.name as location_name', 't.type as transaction_type', 't.ref_no', 't.invoice_no')->get();
        // dd($payments);
        $paymentTypes = $this->transactionUtil->payment_types();
        //Get payment totals before start date
        $prev_payments_sum = $this->__paymentQuery($contact_id, $start)
            ->select(DB::raw("SUM(transaction_payments.amount) as total_paid"))
            ->first();
        foreach ($payments as $payment) {
            $ref_no = in_array($payment->transaction_type, ['sell', 'sell_return']) ?  $payment->invoice_no :  $payment->ref_no;
            $ledger[] = [
                'date' => $payment->paid_on,
                'ref_no' => $payment->payment_ref_no,
                'type' => $transaction_types['payment'],
                'location' => $payment->location_name,
                'payment_status' => '',
                'total' => '',
                'payment_method' => !empty($paymentTypes[$payment->method]) ? $paymentTypes[$payment->method] : '',
                'debit' => in_array($payment->transaction_type, ['purchase', 'sell_return']) ? $payment->amount : '',
                'credit' => in_array($payment->transaction_type, ['sell', 'purchase_return', 'opening_balance']) ? $payment->amount : '',
                'others' => $payment->note . '<small>' . __('account.payment_for') . ': ' . $ref_no . '</small>'
            ];
            if ($contact->type == "supplier") {
            }
        }
        
        //dd($payments);


        $total_ob_paid = $payments->where('transaction_type', 'opening_balance')->sum('amount');
        $total_invoice_paid = $payments->where('transaction_type', 'sell')->sum('amount');
        $total_purchase_paid = $payments->where('transaction_type', 'expense')->sum('amount');

        $start_date = $this->commonUtil->format_date($start);
        $end_date = $this->commonUtil->format_date($end);
        $total_invoice = $invoice_sum - $sell_return_sum;
        $total_purchase = $purchase_sum - $purchase_return_sum;
        $total_prev_invoice = $previous_transaction_sums->total_purchase + $previous_transaction_sums->total_invoice -  $previous_transaction_sums->total_sell_return -  $previous_transaction_sums->total_purchase_return;
        $total_prev_paid = $prev_payments_sum->total_paid;
        $beginning_balance = ($previous_transaction_sums->opening_balance + $total_prev_invoice) - $prev_payments_sum->amount - $total_prev_paid;
        $total_paid = $total_invoice_paid + $total_purchase_paid + $total_ob_paid;
        $curr_due =  ($beginning_balance + $total_invoice + $total_purchase) - $total_paid;
        //Sort by date
        if (!empty($ledger)) {
            usort($ledger, function ($a, $b) {
                $t1 = strtotime($a['date']);
                $t2 = strtotime($b['date']);
                return $t2 - $t1;
            });
        }
        $output = [
            'ledger' => $ledger,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'total_invoice' => $total_invoice,
            'total_purchase' => $total_purchase,
            'beginning_balance' => $beginning_balance,
            'total_paid' => $total_paid,
            'balance_due' => $curr_due
        ];
        return $output;
    }
    private function __transactionQuery($contact_id, $start, $end = null)
    {
        $business_id = request()->session()->get('user.business_id');
        $transaction_type_keys = array_keys(Transaction::transactionTypes());
        $query = Transaction::where('transactions.contact_id', $contact_id)
            ->where('transactions.business_id', $business_id)
            ->where('status', '!=', 'draft')
            ->whereIn('type', $transaction_type_keys);
        if (!empty($start)  && !empty($end)) {
            $query->whereDate(
                'transactions.transaction_date',
                '>=',
                $start
            )
                ->whereDate('transactions.transaction_date', '<=', $end)->get();
        }
        if (!empty($start)  && empty($end)) {
            $query->whereDate('transactions.transaction_date', '<', $start);
        }
        return $query;
    }
    private function __paymentQuery($contact_id, $start, $end = null)
    {
        $business_id = request()->session()->get('user.business_id');
        $query = TransactionPayment::join(
            'transactions as t',
            'transaction_payments.transaction_id',
            '=',
            't.id'
        )
            ->leftJoin('business_locations as bl', 't.location_id', '=', 'bl.id')
            ->where('t.contact_id', $contact_id)
            ->where('t.business_id', $business_id)
            ->where('t.status', '!=', 'draft');
        if (!empty($start)  && !empty($end)) {
            $query->whereDate('t.transaction_date', '>=', $start)
                ->whereDate('t.transaction_date', '<=', $end);
        }
        if (!empty($start)  && empty($end)) {
            $query->whereDate('t.transaction_date', '<', $start);
        }
        // dd($query->toSql());
        return $query;
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('business.id');
        $templates = ChequeTemplate::where('business_id', $business_id)->get();
        return view('chequewrite::templates.create')->with(compact('templates', 'id'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //to update template store method is use
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
 
        try {
                $business_id = request()->user()->business_id;
                //Check if any transaction related to this contact exists
                $count = Transaction::where('business_id', $business_id)
                    ->where('contact_id', $id)->where('final_total', '>', 0)
                    ->count();
                if ($count == 0) {
                    $contact = Contact::where('business_id', $business_id)->findOrFail($id);
                    $transactions = Transaction::where('business_id', $business_id)
                        ->where('contact_id', $id)->get();
                    foreach ($transactions as $transaction) {
                        AccountTransaction::where('transaction_id', $transaction->id)->forcedelete();
                        $transaction->delete();
                    }
                    if (!$contact->is_default) {
                        $contact->delete();
                    }
                    $output = [
                        'success' => true,
                        'msg' => 'Payee Deleted Successfully'
                    ];
                } else {
                    $output = [
                        'success' => false,
                        'msg' => __("lang_v1.you_cannot_delete_this_contact")
                    ];
                }
            } catch (\Exception $e) {
                Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
                $output = [
                    'success' => false,
                    'msg' => __("messages.something_went_wrong")
                ];
            }
         return  $output;
    }

 
}