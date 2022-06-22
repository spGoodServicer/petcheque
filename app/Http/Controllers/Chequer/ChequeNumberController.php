<?php

namespace App\Http\Controllers\Chequer;

use App\Account;
use App\Contact;
use App\Chequer\PrintedChequeDetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\DataTables;
use App\Chequer\ChequeNumber;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChequeNumberController extends Controller
{
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
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
            $cheque_number = ChequeNumber::leftjoin('accounts', 'cheque_numbers.account_no', 'accounts.id')
                ->leftjoin('users', 'cheque_numbers.user_id', 'users.id')
                ->where('accounts.business_id', $business_id)
                ->select(
                    'cheque_numbers.*',
                    'users.username',
                    'accounts.name'
                )->groupBy('id');

            return Datatables::of($cheque_number)
                ->addColumn('action', function ($row) {

                    $html = '<div class="btn-group">
                    <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                        data-toggle="dropdown" aria-expanded="false">' .
                        __("messages.actions") .
                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    <li><a href="' . action('Chequer\ChequeNumberController@edit', [$row->id]) . '"><i class="glyphicon glyphicon-edit"></i> Edit</a></li>
                    
                    <li><a data-href="' . action('Chequer\ChequeNumberController@destroy', [$row->id]) . '" class="delete_employee"><i class="glyphicon glyphicon-trash" style="color:brown;"></i> Delete</a></li>
                    ';




                    $html .=  '</ul></div>';
                    return $html;
                })
                ->removeColumn('created_at')
                ->removeColumn('updated_at')
                ->editColumn('bank', function ($row) {
                    return $row->reference_no;
                })
                // ->rawColumns(['action'])
                ->make(true);
        }

        $accounts = Account::where('business_id', $business_id)->where('is_need_cheque', 'Y')->notClosed()->pluck('name', 'id');
        
        // // $accounts=array();
        // foreach($accountsData as $row){
        //      if($row->is_need_cheque=='Y')
        //          $accounts[]=array('account_number'=>$row->account_number.'('.$row->name.')','id'=>$row->id);
        //      else
        //          $accounts[]=array('account_number'=>$row->account_number,'id'=>$row->id);
        // }
        // $accounts = collect($accounts)->pluck('account_number', 'id');
        
        return view('chequer/cheque_number/index')->with(compact('accounts'));
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
        try {
            $business_id = $request->session()->get('business.id');
            $data = array(
                'date_time' => $request->date_time,
                'reference_no' => $request->reference_no,
                'business_id' => $business_id,
                'account_no' => $request->account_number,
                'first_cheque_no' => $request->first_cheque_no,
                'last_cheque_no' => $request->last_cheque_no,
                'no_of_cheque_leaves' => $request->no_of_cheque_leaves,
                'user_id' => Auth::user()->id
            );
            
            ChequeNumber::create($data);
            $output = [
                'success' => 1,
                'msg' => __('cheque.cheque_number_add_succuss')
            ];
            
        } catch (\Exception $e) {
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong')
            ];
        }
        return redirect()->back()->with('status', $output);
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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function printedcheque(Request $request)
    {
        $defaultVal=null;
        if($request){
            $defaultVal=array();
            $defaultVal['bank_acount_no'] = $request->bank_acount_no;
            $defaultVal['cheque_no'] = $request->cheque_no;
            $defaultVal['payment_status'] = $request->payment_status;
            $defaultVal['payee_no'] = $request->payee_no;
            $defaultVal['startDate'] = date('m/01/Y');
            $defaultVal['endDate'] = date("m/t/Y");
            if($request->date_range){
                $dates = explode(' - ', $request->date_range);
                $defaultVal['startDate'] = $dates[0];
                $defaultVal['endDate'] = $dates[1];
            }
        } 
        $business_id = request()->session()->get('business.id');
        $bankAcounts = Account::where('business_id', $business_id)->where('is_need_cheque','Y')->pluck('account_number','account_number');
        $payeeList = Contact::where('business_id', $business_id)->where('type', 'supplier')->pluck('name','id');
        $chequeNumbers = PrintedChequeDetail::groupBy('cheque_no')->pluck('cheque_no','cheque_no');
        $paymentStatus = array('Full Payment'=>'Full Payment','Partial Payment'=>'Partial Payment','Last Payment'=>'Last Payment');
        // \DB::connection()->enableQueryLog();
        $printedcheque = PrintedChequeDetail::where('printed_cheque_details.business_id', $business_id)
                                            //->where('printed_cheque_details.status','!=','Cancelled' )
                                             ->leftjoin('users', 'printed_cheque_details.user_id', 'users.id')
                                             ->leftjoin('contacts', 'printed_cheque_details.payee', 'contacts.id')
                                             ->leftjoin('transactions', 'printed_cheque_details.purchase_order_id', 'transactions.id');
        if($request->bank_acount_no && $request->bank_acount_no!="")
            $printedcheque = $printedcheque->where('printed_cheque_details.bank_account_no',$request->bank_acount_no);
        if($request->payment_status && $request->payment_status!="")
            $printedcheque = $printedcheque->where('printed_cheque_details.supplier_paid_amount',$request->payment_status);
        if($request->payee_no && $request->payee_no!="")
            $printedcheque = $printedcheque->where('printed_cheque_details.payee',$request->payee_no);
        if($request->cheque_no && $request->cheque_no!="")
            $printedcheque = $printedcheque->where('printed_cheque_details.cheque_no',$request->cheque_no);
        if($request->date_range){
            $printedcheque = $printedcheque->where('printed_cheque_details.cheque_date','>=',date('Y-m-d',strtotime($defaultVal['startDate'])));
            $printedcheque = $printedcheque->where('printed_cheque_details.cheque_date','<=',date('Y-m-d',strtotime($defaultVal['endDate'])));
        }
        $printedcheque = $printedcheque->select('printed_cheque_details.*','users.username','transactions.type','transactions.ref_no','transactions.invoice_no','contacts.name')
                                                ->orderBy('printed_cheque_details.id','DESC')
                                                ->get();
        // $queries = \DB::getQueryLog();
        // print_r($queries);
        return view('chequer/printedcheque/index')->with(compact('printedcheque','bankAcounts','payeeList','chequeNumbers','paymentStatus','defaultVal'));
    }
}
