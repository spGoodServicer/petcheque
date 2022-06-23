<?php

namespace App\Http\Controllers\Chequer;

use App\Account;
use App\Chequer\PrintedChequeDetail;
use App\Chequer\ChequerBankAccount;
use App\Chequer\ChequeNumber;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\DataTables;
use App\Chequer\CancelCheque;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DeletedChequeController extends Controller
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
    public function index(Request $request)
    {
        if($request){
            $defaultVal=array();
            $defaultVal['startDate'] = date('m/01/Y');
            $defaultVal['endDate'] = date('m/t/Y');
            $defaultVal['filter_account_number']='';
            $defaultVal['filter_cheque_number']='';
            if($request->date_range){
                $dates = explode(' - ', $request->date_range);
                $defaultVal['startDate'] = $dates[0];
                $defaultVal['endDate'] = $dates[1];
            }
            if($request->filter_account_number)
                $defaultVal['filter_account_number'] = $request->filter_account_number;
            if($request->filter_cheque_number)
                $defaultVal['filter_cheque_number'] = $request->filter_cheque_number;
        } 
        $maxref=1;
         $business_id = request()->session()->get('business.id');
         $deletedcheque = CancelCheque::leftjoin('users', 'cancel_cheque.user_id', 'users.id')
                                                ->where('cancel_cheque.business_id',$business_id);
        if($request->date_range)
        {
            $deletedcheque = $deletedcheque->where('cancel_cheque.reg_datetime','>=',date('Y-m-d',strtotime($defaultVal['startDate'])));
            $deletedcheque = $deletedcheque->where('cancel_cheque.reg_datetime','<=',date('Y-m-d',strtotime($defaultVal['endDate'])));
        }
        if($request->filter_account_number)
            $deletedcheque = $deletedcheque->where('cancel_cheque.account_id',$request->filter_account_number);
        if($request->filter_cheque_number)
            $deletedcheque = $deletedcheque->where('cancel_cheque.cheque_no',$request->filter_cheque_number);
        // \DB::enableQueryLog();
        $deletedcheque = $deletedcheque->orderBy('cancel_cheque.id','DESC')->get();
        // dd(\DB::getQueryLog());
        $refno=CancelCheque::select('id')->orderBy('id','ASC')->first();
        if($refno)
           $maxref= $refno->id+1;
        $account=ChequerBankAccount::orderBy('account_number')->get();
        $accounts=[];
        foreach($account as $datarow){
            $accounts[$datarow->account_id] = $datarow->account->account_number;
        }
       $chequenolist = CancelCheque::where('business_id',$business_id)->groupBy('cheque_no')->get();
       $chequenolists=[];
        foreach($chequenolist as $datarow){
            $chequenolists[$datarow->cheque_no] = $datarow->cheque_no;
        }
         return view('chequer/deleted_cheque/index')->with(compact('deletedcheque','maxref','accounts','chequenolists','defaultVal'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBank(Request $request)
    {
        $business_id = request()->session()->get('business.id');  
        $account_no=$request->account_no;
        $chequeStatus=$request->chequeStatus;
        if($chequeStatus=="printed"){
            $account = Account::find($account_no);
            $chequeNumbers = PrintedChequeDetail::where('business_id', $business_id)->where('status','!=','Cancelled')->where('bank_account_no', $account->account_number)->orderBy('id', 'desc')->pluck('cheque_no');
        }else
            $chequeNumbers = ChequeNumber::where('business_id', $business_id)->where('account_no', $account_no)->pluck('reference_no');
        return json_encode($chequeNumbers);
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
                'business_id' => $business_id,
                'account_id' => $request->account_no,
                'cheque_no' => $request->cheque_no,
                'note' => $request->note,
                'user_id' => Auth::user()->id
            );
            
            CancelCheque::create($data);
            PrintedChequeDetail::where('cheque_no', $request->cheque_no)->update(['status'=>'Cancelled']);
            $output = [
                'success' => 1,
                'msg' => __('cheque.cheque_number_add_succuss')
            ];
            
        } catch (\Exception $e) {
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            //echo "File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage();
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
   
}
