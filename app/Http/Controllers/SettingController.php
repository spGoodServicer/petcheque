<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DefaultSettings;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Currency;
use App\System;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;

class SettingController extends Controller
{
     public function index()
    {
        // if (!auth()->user()->can('superadmin')) {
        //     abort(403, 'Unauthorized action.');
        // }
        $business_id = request()->session()->get('user.business_id');
        $settings = DefaultSettings::where('business_id',$business_id)->first();
        $currency=Currency::orderBy('country','ASC')->get();
        $currencies=[];
        foreach($currency as $datarow){
            $currencies[$datarow['country'].' '.$datarow['currency']] =$datarow['country'].' '.$datarow['currency'];
        }
       
        $stamp=DB::table('chequer_stamps')->where('business_id',$business_id)->get(); 
        $stamps=[];
        foreach($stamp as $datarow){
            $stamps[$datarow->stamp_name] = $datarow->stamp_name;
        }
        $template=DB::table('cheque_templates')->where('business_id',$business_id)->get();
        $templates=[];
        foreach($template as $datarow){
            $templates[$datarow->id] = $datarow->template_name;
        }
        $account=DB::table('chequer_bank_accounts')->where('business_id',$business_id)->get();
        $accounts=[];
        foreach($account as $datarow){
            $accounts[$datarow->account_number] = $datarow->account_number;
        }
        if (!empty($settings)) {
            return view('chequer.default_settings', compact('settings','currencies','stamps','templates','accounts'));
        } else {
             return view('chequer.default_settings', compact('currencies','stamps','templates','accounts'));
        }
    }
    public function updateSettings(Request $request)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $settings = DefaultSettings::where('business_id',$business_id)->first();
            $msg='Added';
            if($settings)
                 $msg='Updated';
            else
                 $settings =new DefaultSettings();
            
            $settings->business_id=$business_id;
            $settings->def_currency=$request->def_currency; 
            $settings->def_stamp=$request->def_stamp;
            $settings->def_cheque_templete=$request->def_cheque_templete;
            $settings->def_bank_account=$request->def_bank_account;
            $settings->def_font=$request->def_font;
            $settings->def_font_size=$request->def_font_size;
            $settings->save();
           $output = [
                    'success' => 1,
                    'msg' => 'Default Settings '.$msg.' Successfully'
                ];
        } catch (\Exception $e) {
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            echo "File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage();
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong')
            ];
        }
       return back()->with('status', $output);
    }
}