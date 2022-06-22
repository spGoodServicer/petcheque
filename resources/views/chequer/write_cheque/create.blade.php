@extends('layouts.app')
@section('title', __('cheque.write_cheque'))

@section('content')
<style>
    label.error {
        color: red
    }

    #template {
        margin: auto;
        background-position: -3px 10px;
        background-size: cover;
        background-repeat: repeat;
        background-color: #b0e5ea;
        width: 850px;
        border: 1px solid #000;
        height: 325px
    }

    .Bank-a #pay {
        float: left;
        position: relative;
        top: 110px;
        left: 108px;
        color: #000
    }

    .Bank-a #date {
        float: right;
        position: relative;
        right: 55px;
        top: 50px;
        color: #000
    }

    .Bank-a #amount {
        float: right;
        position: relative;
        top: 110px;
        margin-right: -30px;
        color: #000
    }

    .Bank-b {
        background-image: url({{asset('chequer/img/cheque2.png')}});
        margin: auto;
        background-position: center;
        background-size: cover;
        background-repeat: no-repeat;
        background-color: #b0e5ea;
        width: 670px;
        border: 1px solid #000;
        height: 300px;
        margin-top: 50px
    }

    .Bank-b #pay {
        float: left;
        position: relative;
        top: 86px;
        left: 108px;
        color: #000
    }

    .Bank-b #date {
        float: right;
        position: relative;
        right: 190px;
        top: 29px;
        color: #000
    }

    .Bank-b #amount {
        float: right;
        position: relative;
        top: 85px;
        margin-right: 50px;
        color: #000
    }

    .Bank-c {
        background-image: url({{asset('chequer/img/cheque3.png')}});
        margin: auto;
        background-position: center;
        background-size: cover;
        background-repeat: repeat;
        background-color: #b0e5ea;
        width: 670px;
        border: 1px solid #000;
        height: 300px;
        margin-top: 50px
    }

    .Bank-c #pay {
        float: left;
        position: relative;
        top: 107px;
        left: 100px;
        color: #000
    }

    .Bank-c #date {
        float: right;
        position: relative;
        right: 140px;
        top: 57px
    }

    .Bank-c #amount {
        float: right;
        position: relative;
        top: 110px;
        margin-right: 0;
        color: #000
    }

    .Bank-d {
        background-image: url({{asset('chequer/img/cheque4.png')}});
        margin: auto;
        background-position: center;
        background-size: cover;
        background-repeat: no-repeat;
        background-color: #b0e5ea;
        width: 670px;
        border: 1px solid #000;
        height: 300px;
        margin-top: 50px
    }

    .Bank-d #pay {
        float: left;
        position: relative;
        top: 100px;
        left: 110px;
        color: #000
    }

    .Bank-d #date {
        float: right;
        position: relative;
        right: 55px;
        top: 28px;
        color: #000
    }

    .Bank-d #amount {
        float: right;
        position: relative;
        top: 100px;
        margin-right: 25px;
        color: #000
    }

    #errorP {
        display: none;
    }

    .margbotm {
        margin-bottom: 10px;
        max-height: 78px;
        min-height: 80px;
    }

 
</style>
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('cheque.write_cheque')</h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'cheque.write_cheque')])
	<?php

    if(count($get_defultvalu) > 0){
        foreach($get_defultvalu as $values){}
        $def_tempid			= $values->def_tempid;
        $def_curnctname		= $values->def_curnctname;
        $def_stampid		= $values->def_stampid;
        $def_entrydt		= $values->def_entrydt;
        $def_currency		= $values->def_currency;
        $def_stamp			= $values->def_stamp;
        $def_cheque_templete= $values->def_cheque_templete;
        $def_bank_account	= $values->def_bank_account;
        $def_font			= $values->def_font;
        $def_font_size		= $values->def_font_size;
    }else{
        $def_tempid			= '';
        $def_curnctname		= '';
        $def_stampid		= '';
        $def_entrydt		= '';
        $def_currency		= '';
        $def_stamp			= '';
        $def_cheque_templete= '';
        $def_bank_account	= '';
        $def_font			= '';
        $def_font_size		= '';
    }
    ?>
    <div class="col-sm-12 col-lg-12 col-md-12 main">
        <div class="row panel-body">
            <div class="col-md-2 margbotm">
                <label>Select Payee Name:</label>
                <select id="myText" class="form-control">
                    <option vlaue="">Select Payee</option>
                    <?php foreach ($results as $row) { ?>
                    <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-2 margbotm">
                <label>Payment For:</label>
                <select id="payment_for" name="payment_for" class="form-control">
                    <option vlaue="">Payment For</option>
					@foreach(config('view.payment4') as $val=>$lbl)
						<option value="{{$val}}">{{$lbl}}</option>
					@endforeach
                </select>
                <p id="errorP" style="color: red;"></p>
            </div>
			<div class="col-md-2 margbotm">
                <label>Payment Type:</label>
                <select id="payment_type" name="payment_type" class="form-control">
                    <option vlaue="">Payment Type</option>
					@foreach(config('view.payment_type') as $val=>$lbl)
						<option value="{{$val}}">{{$lbl}}</option>
					@endforeach
                </select>
                <p id="errorP" style="color: red;"></p>
            </div>
			<div class="col-md-2 margbotm">
                <label id="purchase_id_label">Select Purchase Order:</label>
                <select id="purchase_id" name="purchase_id" class="form-control"
                    onchange="getSupplierBillNoAndOrderNo()">
                    <option vlaue="">Select Order NO</option>
                </select>
                <p id="errorP" style="color: red;">No Purchase Order yet.</p>
            </div>
            <div class="col-md-2 margbotm">
                <label id="purchse_bill_no_label">Bill Number:</label>
                <input type="text" class="form-control" name="purchse_bill_no" id="purchse_bill_no" readonly="">
            </div>
            <div class="col-md-2 margbotm">
                <label>Supplier Order Number:</label>
                <input type="text" class="form-control" name="supplier_order_no" id="supplier_order_no" readonly="">
            </div>
            <div class="col-md-2 margbotm">
                <label>Unpaid Amount:</label>
                <input type="text" class="form-control" name="payable_amount" id="payable_amount" readonly="">
            </div>
            <div class="col-md-2 margbotm">
                <label>Payment Status:</label>
                <select class="form-control" id="paid_to_supplier" name="paid_to_supplier">
                    <option value="">Select Payment Status</option>
                    <option value="Full Payment">Full Payment</option>
                    <option value="Partial Payment">Partial Payment</option>
                    <option value="Last Payment">Last Payment</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label>Select Cheque Template:</label>
                <select id="mySelect" class="form-control" onchange="temFunction()">
                    <option>Select Cheque Template</option>
                    <option>Bank-a</option>
                   
                    @if ($templates){
						@foreach ($templates as $row) 
						@php
                            $id = $row->id;
						@endphp
                      
					<option @if(!empty($get_defultvalu)) @if($def_cheque_templete == $id) selected="selected" @endif @endif  
					value="{{ $row->id }}">{{ $row->template_name }}</option>
				   @endforeach
				   @endif
                </select>
            </div>
            <div class="col-md-2">
                <label>Select Bank Account:</label>
                <select id="bankacount" class="form-control" onchange="getBankNextChequedNO();">
                    <option value="">None</option>
                    @foreach($get_bankacount as $bankacount)
                    <option
                        @if(!empty($get_defultvalu)) @if($def_bank_account == $bankacount['account_number']) selected="selected" @endif @endif
                         value="{{ $bankacount['account_number'] }}">
                       {{ $bankacount['account_number'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2" id="paydive">
                <label>Enter Amount:</label>
                <input type="number" id="mynumber" class="form-control get_num" value="">
            </div>
            <div class="col-md-2">
                <label>Select Stamp / Seal:</label>
                <select id="addStamps" class="form-control" onchange="checkstamp(this.value);">
                    <option value="">None</option>
					@foreach ($stamps as $row) 
					@php
                        $stamp_image = $row['stamp_name'];
					@endphp
                  
                    <option @if(!empty($get_defultvalu))  @if($stamp_image == $def_stamp) selected="selected" @endif @endif
                        value="{{asset('chequer/'.$row['stamp_image'])}}">{{ $row['stamp_name'] }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
		<div class="row panel-body">
			<div class="col-md-2">
				<label>Date Condition:</label>
				<select id="dateCondition" class="form-control">
					<option selected="selected" value="select_date">Select Date</option>
					<option value="print_date_later">Print Date Later</option>
				</select>
			</div>
			<div class="col-md-2" id="select_date_part">
				<label>Select Date:</label>
				<input type="text" id="mydate" class="form-control" value="">
			</div>
			<div class="col-md-2">
				<label>Currency:</label>
				<select id="currencyType" onchange="checkcurrency();" class="form-control">
					<option value="">None</option>
					<?php
					foreach($get_currency as $currency){
						$curname = $currency['country'].' '.$currency['currency'];  
					?>
					<option <?php if(!empty($get_defultvalu)){ if($curname == $def_currency){ ?>selected="selected"
						<?php } } ?> value="<?php echo $currency['country'].' '.$currency['currency']; ?>"><?php echo ucwords($currency['country'].' '.$currency['currency']); ?>
					</option>
					<?php } ?>
				</select>
			</div>
			<div class="col-md-2">
				<label>On Account Of:</label>
				<input type="text" id="acountof" name="acountof" onkeyup="printacountfo();" class="form-control">
			</div>
			<div class="col-md-2">
				<label>Cheque No:</label>
				<input type="text" name="chequeNo" id="chequeNo" onkeyup="getchaqudata();" class="form-control" value="" @if ($package_manage->cheque_number_list==1) readonly @endif>
			</div>
			<div class="col-md-2">
				<label>Date & Time :</label>
				<input type="text" id="mydate2" class="form-control" readonly value="{{date('d-m-Y H:i')}}">
			</div>
		</div>
        <div class="row">
			<div class="col-md-6">
				<div class="row">
					<div class="col-md-3">
						<input type="checkbox" id="addnotnegotiable" style="" onclick="stamps()"> <label>Not Negotiable</label>
					</div>
					<div class="col-md-3">
						<input type="checkbox" id="addpayonly" style="" onclick="stamps()"> <label>A/C pay only</label>
					</div>
					<div class="col-md-3">
						<input type="checkbox" id="addBearer" style=" " onclick="stamps()"> <label>Strike Bearer</label>
					</div>
					<div class="col-md-3">
						<input type="checkbox" id="addcross" style="" onclick="stamps()"> <label>Cross Cheque</label>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="row">
					
					<div class="col-md-3">
						<input type="checkbox" id="addprifix" onclick="addsufix()" style=""> <label>prefix</label>
					</div>
					<div class="col-md-4">
						<input type="checkbox" id="adddoublecross" style="" onclick="stamps()"> <label>Double Cross Cheque</label>
					</div>
					<div class="col-md-2">
						<input type="checkbox" id="addsuffix" onclick="addsufix()" style=""> <label>Suffix</label>
					</div>
					
					<div class="col-md-3">
						<input type="checkbox" id="remove_currency" style=""> <label>Remove Currency</label>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-2"></div>
			<div class="col-md-2" style="margin-top: 20px;">
				<button type="button" id="print_date_only" class="btn btn-sm btn-primary btn_get_word">Print Date Only </button>
			</div>
			<div class="col-md-2" style="margin-top: 20px;">
				<button type="button" id="printprevie" class="btn btn-sm btn-warning btn_get_word" onclick="print();">Re-Print </button>
			</div>
			<div class="col-md-2" style="margin-top: 20px;">
				<button type="button" id="printchaque" class="btn btn-sm btn-primary btn_get_word"
					onclick="myFunctionPrint();">Print Cheque</button>
			</div>
			<div class="col-md-2" style="margin-top: 20px;">
				<button type="button" id="printvoucher" class="btn btn-sm btn-primary btn_get_word"
					onclick="myFunctionPrint1()">Print Voucher</button>
			</div>
			<div class="col-md-2"></div>
		</div>
        <div id="cheque-container" class="cheque-container" style="margin-top: 20px; ">
            <div id="template">
                <input type="hidden" id="amount_word" name="amount_word">
                <div>
                    <p id="pay" style="position:absolute;color: black;font-size:14px;"></p>
                </div>
                <div style="display: none;">
                    <p id="date" style="position:absolute;color:black;"></p>
                </div>
                <div>
                    <p id="amount" style="position:absolute;color:black;font-size:13px;font-family:sans-serif;"></p>
                </div>
                <div>
                    <p id="inWords1" class="" style="position:absolute; color: black;font-size: 13px;"></p>
                </div>
                <div>
                    <p id="inWords2" class="" style="position:absolute; color: black;font-size: 13px;"></p>
                </div>
                <div>
                    <p id="inWords3" class="" style="position:absolute; color: black;font-size: 13px;"></p>
                </div>
                <div>
                    <div>
                        <p id="d1" style="position:  absolute; color: black; font-family: sans-serif;font-size: 13px;">
                        </p>
                    </div>
                    <div>
                        <p id="d2" style="position:  absolute; color: black;font-family: sans-serif;font-size: 13px;">
                        </p>
                    </div>
                    <div>
                        <p id="m1" style="position:  absolute; color: black;font-family: sans-serif;font-size: 13px;">
                        </p>
                    </div>
                    <div>
                        <p id="m2" style="position:  absolute; color: black;font-family: sans-serif;font-size: 13px;">
                        </p>
                    </div>
                    <div>
                        <p id="y1" style="position:  absolute; color: black;font-family: sans-serif;font-size: 13px;">
                        </p>
                    </div>
                    <div>
                        <p id="y2" style="position:  absolute; color: black;font-family: sans-serif;font-size: 13px;">
                        </p>
                    </div>
                    <div>
                        <p id="y3" style="position:  absolute; color: black;font-family: sans-serif;font-size: 13px;">
                        </p>
                    </div>
                    <div>
                        <p id="y4" style="position:  absolute; color: black;font-family: sans-serif;font-size: 13px;">
                        </p>
                    </div>
                    <div>
                        <p id="ds1" style="position:  absolute; color: black;font-family: sans-serif;font-size: 13px;">
                        </p>
                    </div>
                    <div>
                        <p id="ds2" style="position:  absolute; color: black;font-family: sans-serif;font-size: 13px;">
                        </p>
                    </div>
                </div>
                <div id="negotiable" style="position:absolute;display: none;">
                    <img src="{{asset('chequer/img/Not-Negotiable.png')}}" style="width: 100%;">
                </div>
                <div id="payonly" style="position:absolute;display: none;">
                    <img src="{{asset('chequer/img/pay-only.png')}}" style="width: 100%;">
                </div>
                <div id="cross" style="position:absolute; display: none;">
                    <img src="{{asset('chequer/img/cross.png')}}" style="width: 100%;">
                </div>
                <div id="doublecross" style="position:absolute; display: none;">
                    <img src="{{asset('chequer/img/cross.png')}}" style="width: 110%;">
                </div>
                <div id="strikeBearer" class="" contenteditable="false"
                    style="position:absolute;color:black;display: none;">
                    <hr style="border: 1px solid #000;width: 100px;">
                    </hr>
                </div>
                <div id="Stamp" class="" contenteditable="false" style="display:none;position:absolute;color:black; ">
                    <img src="" id="stamimage">
                </div>
            </div>
        </div>
		<div class="row" id='printvoucherdive'
            style="display:none;background-color:white;height:450px;width:750px;margin-left:150px;">
            <div class="col-md-12">
                <br />
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    @php
                    foreach($getvoucher as $vaoucherid){}
                    $Currentid=(!empty($vaoucherid['id'])? $vaoucherid['id'] : 0 )+1;
                    $settingData = App\SiteSettings::first();
                 
                    @endphp
                    <center>
                        <h2>{{ $settingData->site_name }}</h2>
                    </center>
                </div>
                <div class="col-md-2"></div>
            </div>
            <div class="col-md-12">
                <div class="col-md-4"></div>
                <div class="col-md-4">
                    <h4>PAYMENT VOUCHER</h4>
                </div>
                <div class="col-md-1"></div>
                <div class="col-md-3 ">
                    <h5 id="voc_no"><b>No:</b> {{ $Currentid }}</h5>
                    <h5 id="datew_id"><b>Date:</b>{{date('d-m-Y') }}</h5>
                </div>
            </div>
            <div class="col-md-12">
                <div class="col-md-3">
                    <label><b>Payee name:</b></label>
                </div>
                <div class="col-md-7">
                    <p id="vou_pyeename"></p>
                </div>
                <div class="col-md-2"></div>
                <br />
            </div>
            <div class="col-md-12">
                <div class="col-md-3">
                    <label><b>Amount Number:</b></label>
                </div>
                <div class="col-md-7">
                    <p id="vou_payamnum"></p>
                </div>
                <div class="col-md-2"></div>
            </div>
            <div class="col-md-12">
                <div class="col-md-3">
                    <label><b>Amount in word:</b></label>
                </div>
                <div class="col-md-7">
                    <p id="vou_payamnumword"></p>
                </div>
                <div class="col-md-2"></div>
                <br />
            </div>
            <div class="col-md-12">
                <div class="col-md-3">
                    <label><b>Chaque no.:</b></label>
                </div>
                <div class="col-md-7">
                    <div class="col-md-7">
                        <p id="vou_chaqno"></p>
                    </div>
                </div>
                <div class="col-md-2"></div>
                <br />
            </div>
            <div class="col-md-12">
                <div class="col-md-3">
                    <label><b>Chaque Date:</b></label>
                </div>
                <div class="col-md-7">
                    <div class="col-md-7">
                        <p id="vou_date"><?php echo date('d-m-Y'); ?></p>
                    </div>
                </div>
                <div class="col-md-2"></div>
                <br />
            </div>
            <div class="col-md-12">
                <div class="col-md-3">
                    <label><b>On Account of:</b></label>
                </div>
                <div class="col-md-7">
                    <label id="vou_amountof" style="border-bottom: 1px dashed;"></label>
                </div>
                <div class="col-md-2"></div>
                <br />
            </div>
            <input type="hidden" id="def_curnctname" name="def_curnctname" value="<?php echo $def_currency; ?>">
            <div class="col-md-12"></div>
            <div class="col-md-12"><br /><br /><br /><br /></div>
            <div class="col-md-12">
                <div class="col-md-3">
                    <?php // echo $this->session->userdata('user_role_name'); ?>
                    <label style="border-top: 1px dashed;"><b>Prepared by</b></label>
                </div>
                <div class="col-md-3">
                    <label style="border-top: 1px dashed;"><b>Approved by</b></label>
                </div>
                <div class="col-md-2">
                    <label style="border-top: 1px dashed;"><b>signature 1</b></label>
                </div>
                <div class="col-md-2">
                    <label style="border-top: 1px dashed;"><b>signature 2</b></label>
                </div>
                <div class="col-md-2">
                    <label style="border-top: 1px dashed;"><b>Received By</b></label>
                </div>
            </div>
        </div>
        <div class="row"><br /> <br /> <br /> <br /> <br /></div>
        <div class="canvasDiv"></div>


        @endcomponent
</section>
@endsection

@section('javascript')
<script src="{{asset('js/jquery.num2words.js')}}"></script>
<script src="{{asset('js/html2canvas.js')}}"></script>
<script src="{{asset('js/html2canvas.min.js')}}"></script>
<script src="{{asset('js/currency.js')}}"></script> 
<script type="text/javascript">
	$(document).ready(function(){
		$("#mynumber").on('keyup',function(){
			convertNumberToWords();
		});
		checkstamp('<?php echo $def_stamp; ?>');
		temFunction();
		
		$.each(currency, function (i, item) {
			$('#currencyTyp').append($('<option>', {
				value: item.name,
				text : item.name,
				symbol : item.symbol
			}));
		});

		$(document).on('blur', '#mydate', function(event){
			temFunction();       
		});

		$(document).on('change', '#currencyTyp', function(event) {
			$('#remove_currency').prop('checked', false);
		});

		$(document).on('change', '#remove_currency', function(event) {
			if($(this).prop("checked") == true){
				$('#currencyTyp').val('');
			}
		});
	});
</script>

<script type="text/javascript">
	var th = ['','thousand','million', 'billion','trillion'];
var dg = ['zero','one','two','three','four', 'five','six','seven','eight','nine']; var tn = ['ten','eleven','twelve','thirteen', 'fourteen','fifteen','sixteen', 'seventeen','eighteen','nineteen']; var tw = ['twenty','thirty','forty','fifty', 'sixty','seventy','eighty','ninety']; function toWords(s){s = s.toString(); s = s.replace(/[\, ]/g,''); if (s != parseFloat(s)) return 'not a number'; var x = s.indexOf('.'); if (x == -1) x = s.length; if (x > 15) return 'too big'; var n = s.split(''); var str = ''; var sk = 0; for (var i=0; i < x; i++) {if ((x-i)%3==2) {if (n[i] == '1') {str += tn[Number(n[i+1])] + ' '; i++; sk=1;} else if (n[i]!=0) {str += tw[n[i]-2] + ' ';sk=1;}} else if (n[i]!=0) {str += dg[n[i]] +' '; if ((x-i)%3==0) str += 'hundred ';sk=1;} if ((x-i)%3==1) {if (sk) str += th[(x-i-1)/3] + ' ';sk=0;}} if (x != s.length) {var y = s.length; str += 'point '; for (var i=x+1; i<y; i++) str += dg[n[i]] +' ';} return str.replace(/\s+/g,' ');}
function printpayname(){
    var supplier_id = $("#myText").val();
    var paymentFor = $("#payment_for").val();
    var paymentType = $("#payment_type").val();
    if(supplier_id>0)
        $("#myText").prop("disabled", true);

    if(supplier_id>0 && paymentFor=='payment_only' && paymentType=='new_payment'){
        $("#purchse_bill_no").prop('readonly',false);
        $("#supplier_order_no").prop('readonly',false);
        $("#payable_amount").prop('readonly',false);
        //$("#payment_type").prop( "disabled", true );
    }else{
        $("#purchse_bill_no").prop('readonly',true);
        $("#supplier_order_no").prop('readonly',true);
        $("#payable_amount").prop('readonly',true);
        //$("#payment_type").prop( "disabled", false );
    }
    if(supplier_id>0 && paymentFor=='purchases' && paymentType=='balance_payment' && $('#purchase_id').val()>0)
    {
        $('#mynumber').prop('readonly', true);
    }else{
        $('#mynumber').prop('readonly', false);
    }
    if(paymentFor=='expenses'){
        $("#purchse_bill_no_label").text("Expense Ref No:");
        $("#purchase_id_label").text("Select Expense Number:");
    }else{
        $("#purchse_bill_no_label").text("Bill Number:");
        if(paymentType=='new_payment')
            $("#purchase_id_label").text("No Purchase order");    
        else
            $("#purchase_id_label").text("Select Purchase Order:");    
    }
    
    // $("#purchse_bill_no").removeAttr('readonly');
    // $("#supplier_order_no").removeAttr('readonly');
    // $("#payable_amount").removeAttr('readonly');
    $('#purchase_id').prop('disabled',true);
    if(supplier_id>0 && paymentFor=='payment_only' && paymentType=='new_payment'){
        var options2= '<option value="">Select No</option>';
        $('#purchase_id').html(options2);
        $('#purchase_id').prop('disabled',false);
    }else{
        $.ajax({
            url: "{{action('Chequer\ChequeWriteController@listOfPayeeTemp')}}",
            type: 'post',
            dataType: 'json',
            data:{supplier_id:supplier_id,paymentFor:paymentFor,paymentType:paymentType},
            success: function(result) {
                // var data = result.results.length;
                if(supplier_id>0 && paymentFor=='payment_only' && paymentType=='balance_payment' && $('#purchase_id').val()==''){
                    $('#payable_amount').val(result.balence_amount);
                }
                var data2 = result.get_purchase_order.length;
                if(data2 == 0){
                    var options2 = '';
                    options2 += '<option value="">No Purchase Order yet.</option>';
                    
                }else{
                    var options2 = '';
                    options2 += '<option value="">Select No</option>';
                    if(paymentFor=='expenses'){
                        for(var i=0; i<data2; i++) {
                            options2 += '<option value="'+result.get_purchase_order[i].id+'">'+result.get_purchase_order[i].ref_no+'</option>';
                        }
                    }else{
                        options2 += '<option value="no_purchase_order">No Purchase Order.</option>';
                        for(var i=0; i<data2; i++) {
                            options2 += '<option value="'+result.get_purchase_order[i].id+'">'+result.get_purchase_order[i].invoice_no+'</option>';
                        }
                    } 
                        
                }

                // Append to the html
                // $('#payee_temlist').html(options);
                $('#purchase_id').html(options2);
                $('#purchase_id').prop('disabled',false);
            }
        });
    }
    $("#pay").html($("#myText :selected").text());
    $("#vou_pyeename").html(myText);
}
function convertNumberToWords(){
    var valus = $("#mynumber").val();
    var Unpaid = $("#payable_amount").val();
    if(parseFloat(Unpaid) > parseFloat(valus)){
        if(valus.indexOf('.') !== -1){
            $("#amount").html(commaSeparateNumber(valus));
            $("#vou_payamnum").html(commaSeparateNumber(valus));
        }else{
            $("#amount").html(commaSeparateNumber(valus)+".00");
            $("#vou_payamnum").html(commaSeparateNumber(valus)+".00");
        }
    }else if((parseFloat(Unpaid) == parseFloat(valus)) && (parseFloat(valus) > 0)){
        $("#amount").html(commaSeparateNumber(valus)+".00");
        $("#vou_payamnum").html(commaSeparateNumber(valus)+".00");
    }else if(Unpaid == "" && valus != ""){
        var purchased = $("#purchase_id option:selected").text();
        if(purchased == 'No Purchase Order yet.'){
            alert("Please enter less or equal of Unpaid Amount!");
        }else{
            alert("Please Select Purchase Order first!");
        }
        $("#mynumber").val('');
    }else if(parseFloat(Unpaid) < parseFloat(valus)){
        alert("Please enter less or equal of Unpaid Amount!");
        $("#mynumber").val('');
    }
    return false;
}
function myFunction() {
    var x = document.getElementById("myText");
    var defaultVal = x.defaultValue;
    var currentVal = x.value;
    var d = document.getElementById("mydate");
    var currentdate = d.value;
    var a = document.getElementById("mynumber");
    var currentnumber = a.value;
    if (defaultVal == currentVal) {
        document.getElementById("demo").innerHTML = "Default value and current value is the same: "
        + x.defaultValue + " and " + x.value
        + "<br>Change the value of the text field to see the difference!";
    } else {
        document.getElementById("pay").innerHTML =currentVal;
        document.getElementById("date").innerHTML = currentdate;
        if($("#currencyType option:selected").attr('symbol') && currentnumber){
            document.getElementById("amount").innerHTML = currentnumber;
        }else{
            document.getElementById("amount").innerHTML = currentnumber;
        }
    }
    $(".addCurrency").html($("#currencyType option:selected").val());
    // myFunctionPrint();
}
function myFunctionPrint(){
    var supplierid = $('#getSupplierDate').val();
    var chequeNo = $('#chequeNo').val();
    var purchase_id = $('#purchase_id').val();
    var payable_amount = $('#payable_amount').val();
    var unpaid = $('#payable_amount').val();
    var paymentFor = $("#payment_for").val();
    var paid_to_supplier = $('#paid_to_supplier option:selected').val();
    var paid_amounts = $('#mynumber').val();
    if(chequeNo=="")
    {
        alert("Please enter Cheque No.");
        return;
    }
    if(purchase_id=="")
    {
        alert("Please Select Order NO.");
        return;
    }
    if($('#mynumber').val()=="" || $('#mynumber').val()==0)
    {
        alert("Please Enter Amount.");
        return;
    }
    if(unpaid != 0 ){
        if(paid_to_supplier == ""){
            alert("Please Select Payment Status.");
            return;
        }
        if(parseFloat(unpaid) < parseFloat(paid_amounts)){
            alert("Paid To Supplier amount can't be greater then Unpaid.");
            return;
        }
    }

    if(supplierid != ""){
        $.ajax({
            type:'POST',
            url:"{{action('Chequer\ChequeWriteController@getChequeNoUniqueOrNotCheck')}}",
            data: {supplierid: supplierid, chequeNo : chequeNo},
            dataType: 'JSON',
            success:function(data){
                /* console.log(data); */
                if(data.cheque_check != null){
                    alert("please Enter right Cheque No");
                }else{
                    var template_id = $('#mySelect option:selected').val();
                    var cheque_amount  = $('#mynumber').val();
                    /* var payee = $('#myText option:selected').text(); */
                    var payee = $('#myText').val();
                    var cheque_no = $('#chequeNo').val();
                    var cheque_date = $('#mydate').val();
                    var payee_tempname=$("#payee_tempname").val();
                    var stampvalu=$("#addStamps").val();
                    var amount_word=$("#amount_word").val();
                    var bankacount=$("#bankacount").val();
                    var purchse_bill_no=$("#purchse_bill_no").val();
                    var acoof=$("#acountof").val();
                    $("#vou_date").html(cheque_date);
           
                    $.ajax({
                        url: "{{ url('cheque-write') }}",
                        type    : 'POST', 
                        dataType: 'json',
                        data: {paymentFor:paymentFor,purchse_bill_no:purchse_bill_no,payable_amount:payable_amount,purchase_id:purchase_id,paid_to_supplier:paid_to_supplier,template_id: template_id,cheque_amount: cheque_amount,payee: payee,cheque_no: cheque_no,cheque_date: cheque_date,payee_tempname:payee_tempname,stampvalu:stampvalu,amount_word:amount_word,bankacount:bankacount,acoof:acoof},
                        error: function (jqXHR, exception) {
                            console.log(jqXHR, exception);
                        }
                    }).done(function(data) {
                    
                        var divToPrint=document.getElementById('template');
                        var newWin=window.open('','Print-Window');

                        newWin.document.open();
                        newWin.document.write('<html><body onload="window.print()">'+divToPrint.innerHTML+'</body></html>');
                        newWin.document.close();
                        location.reload();
                    });
                }
            }
        });
    }else{
        alert("please Enter right Cheque No");
    }
}
function myFunctionPrint333(){
    var template_id = $('#mySelect option:selected').val();
    var cheque_amount  = $('#mynumber').val();
    // var payee = $('#myText option:selected').text();
    var payee = $('#myText').val();
    var cheque_no = $('#chequeNo').val();
    var cheque_date = $('#mydate').val();
    var paymentFor = $('#payment_for').val();
    var payee_tempname=$("#payee_tempname").val();
    var stampvalu=$("#addStamps").val();
    var amount_word=$("#amount_word").val();
    var bankacount=$("#bankacount").val();
    var acoof=$("#acountof").val();
    $("#vou_date").html(cheque_date);

    $.ajax({
        url: "{{ url('cheque-write') }}",
        type: 'post',
        dataType: 'json',
        data: {paymentFor:paymentFor,template_id: template_id,cheque_amount: cheque_amount ,payee: payee,cheque_no: cheque_no,cheque_date: cheque_date,payee_tempname:payee_tempname,stampvalu:stampvalu,amount_word:amount_word,bankacount:bankacount,acoof:acoof},
    }).done(function(data) {
        location.reload();
        var divToPrint=document.getElementById('template');
        var newWin=window.open('','Print-Window');

        newWin.document.open();
        newWin.document.write('<html><body onload="window.print()">'+divToPrint.innerHTML+'</body></html>');
        newWin.document.close();
    });
}

//Print Voucher
function myFunctionPrint1(){
    var supplierid = $('#getSupplierDate').val();
    var chequeNo = $('#chequeNo').val();
    var unpaid = $('#payable_amount').val();
    var paid_to_supplier = $('#paid_to_supplier').val();
    var purchase_id = $('#purchase_id').val();
    var paymentFor = $("#payment_for").val();
    var payable_amount = $('#payable_amount').val();
    if(unpaid != 0 ){
        if(paid_to_supplier == ""){
            alert("Please select Payment Status.");
            paid_to_supplier = 0;
            return;
        }
        if(parseFloat(unpaid) < parseFloat(paid_to_supplier)){
            alert("Paid To Supplier amount can't be greater then Unpaid.");
            return;
        }
    }

    if(supplierid != ""){
        $.ajax({
            type:'POST',
            url:"{{action('Chequer\ChequeWriteController@getChequeNoUniqueOrNotCheck')}}",
            data: {supplierid: supplierid, chequeNo : chequeNo},
            dataType: 'JSON',
            success:function(data){
                /* console.log(data); */
                if(data.cheque_check != null){
                    alert("please Enter right Cheque No");
                }else{
                    $("#printvoucherdive").css('display','block');
                    var printvoucherdive=$("#printvoucherdive").html();
                    var template_id = $('#mySelect option:selected').val();
                    var cheque_amount  = $('#mynumber').val();
                    var payee = $('#myText option:selected').val();
                    var cheque_no = $('#chequeNo').val();
                    var cheque_date = $('#mydate').val();

                    $.ajax({
                        url: "{{ url('cheque-write') }}",
                        type: 'post',
                        dataType: 'json',
                        data: {paymentFor:paymentFor,payable_amount:payable_amount,purchase_id:purchase_id,paid_to_supplier:paid_to_supplier,template_id: template_id,cheque_amount: cheque_amount ,payee: payee,cheque_no: cheque_no,cheque_date: cheque_date},
                        success:function(data){
                        },
                        error:function(data){
                        }
                    }).done(function(data) {
                        html2canvas(document.querySelector("#printvoucherdive")).then(canvas => {
                            $("#printvoucherdive").css('display','none');
                            var myImage = canvas.toDataURL("image/png");
                            var tWindow = window.open("", "", "width=900, height=600");
                            tWindow.document.write('<img id="Image" src=' + myImage + ' style="width:100%;"></img>');
                            $(tWindow.document.body).html('<img id="Image" src=' + myImage + ' style="width:100%;"></img>').ready(function() {
                                tWindow.print(); 
                                tWindow.focus();
                            });
                        });
                    });
                }
            }
        });
    }else{
        alert("please Enter right Cheque No");
    }
}

function getchaqudata(){
    var chaqdata=$("#chequeNo").val();
    $("#vou_chaqno").html(chaqdata);
}

function stamps() {
    var checkBox = document.getElementById("addnotnegotiable");
    var text = document.getElementById("negotiable");
    if (checkBox.checked == true){
        negotiable.style.display = "block";
    } else {
        negotiable.style.display = "none";
    }

    var checkBox = document.getElementById("addpayonly");
    var text = document.getElementById("payonly");
    if (checkBox.checked == true){
        payonly.style.display = "block";
    } else {
        payonly.style.display = "none";
    }

    var checkBox = document.getElementById("addcross");
    var text = document.getElementById("cross");
    if (checkBox.checked == true){
        cross.style.display = "block";
    } else {
        cross.style.display = "none";
    }

    var checkBox = document.getElementById("adddoublecross");
    var text = document.getElementById("doublecross");
    if (checkBox.checked == true){
        cross.style.display = "block";
        $("#doublecross").show()
    } else {
        $("#doublecross").hide()
    }

    var checkBox = document.getElementById("addBearer");
    if (checkBox.checked == true){
        $("#strikeBearer").show()
    } else {
        $("#strikeBearer").hide()
    }
}

function checkstamp(value){
    var addStamps = $("#addStamps :selected").val();
    if (addStamps!=""){
        $("#Stamp").show();
        $("#stamimage").attr("src",addStamps);
    } else {
        $("#Stamp").hide();
    }
}
function temFunction() {
    var templtID = $('#mySelect :selected').val();
    if(templtID > 0){
        $.ajax({
            url: "{{action('Chequer\ChequeWriteController@getTempleteWiseBankAccounts')}}",
            type: 'post',
            dataType: 'json',
            data: {templtID: templtID},
        }).done(function(data) {
            $('#bankacount').html(data.banks);
        }).fail(function() {
            console.log("error");
        }).always(function() {
            console.log("complete");
        });
    }
    
    var t = document.getElementById("mySelect").value;
    var element = document.getElementById("template");
    element.className=t;
    var id = $('#mySelect :selected').val();
    $.ajax({
        url: "{{action('Chequer\ChequeTemplateController@getTemplateValues')}}",
        type: 'post',
        dataType: 'json',
        data: {id: id},
    }).done(function(data) {
        if (!$.trim(data))
            return false;
        var template_size = data.template_size.split(',');
        $('#template').css('width', template_size[0]);
        $('#template').css('height', template_size[1]);
        $('#template').css('background-image', 'url({{url('/public/chequer')}}/uploads/' + data.image_url + ')');
        var pay_name = data.pay_name.split(',');
        $('#pay').css('margin-top', pay_name[0]);
        $('#pay').css('margin-left', pay_name[1]);
        var date_pos = data.date_pos.split(',');
        $('#date').css('margin-top', date_pos[0]);
        $('#date').css('margin-left', date_pos[1]);
        var amount = data.amount.split(',');
        $('#amount').css('margin-top', amount[0]);
        $('#amount').css('margin-left', amount[1]);
        var amount_in_w1 = data.amount_in_w1.split(',');
        $('#inWords1').css('margin-top', amount_in_w1[0]);
        $('#inWords1').css('margin-left', amount_in_w1[1]);
        var amount_in_w2 = data.amount_in_w2.split(',');
        $('#inWords2').css('margin-top', amount_in_w2[0]);
        $('#inWords2').css('margin-left', amount_in_w2[1]);
        var amount_in_w3 = data.amount_in_w3.split(',');
        $('#inWords3').css('margin-top', amount_in_w3[0]);
        $('#inWords3').css('margin-left', amount_in_w3[1]);
        var not_negotiable = data.not_negotiable.split(',');
        $('#negotiable').css('margin-top', not_negotiable[0]);
        $('#negotiable').css('margin-left', not_negotiable[1]);
        var pay_only = data.pay_only.split(',');
        $('#payonly').css('margin-top', pay_only[0]);
        $('#payonly').css('margin-left', pay_only[1]);
        var template_cross = data.template_cross.split(',');
        $('#cross').css('margin-top', template_cross[0]);
        $('#cross').css('margin-left', template_cross[1]);
        var template_cross = data.template_cross.split(',');
        $('#doublecross').css('margin-top', template_cross[0]);
        $('#doublecross').css('margin-left', template_cross[1]);
        var template_signature_stamp = data.signature_stamp.split(',');
        var template_signature_stamp_area = data.signature_stamp_area.split(',');

        $('#Stamp').css('margin-top', template_signature_stamp[0]);
        $('#Stamp').css('margin-left', template_signature_stamp[1]);
        $('#Stamp').css('height', template_signature_stamp_area[0]);
        $('#Stamp').css('width', template_signature_stamp_area[1]);
        $('#stamimage').css('height', template_signature_stamp_area[0]);
        $('#stamimage').css('width', template_signature_stamp_area[1]);

        if(data.strikeBearer){
            var template_strikeBearer = data.strikeBearer.split(',');
            $('#strikeBearer').css('margin-top', template_strikeBearer[0]);
            $('#strikeBearer').css('margin-left', template_strikeBearer[1]);
        }
        
        if($("#mydate").is(":visible") && $("#mydate").val()){
            var date = $("#mydate").val();
            var parts = date.split('-');
            var day = parts[0];
            var month = parts[1];
            var year = parts[2];

            var dateParts = day.split('');

            var d1 = data.d1.split(',');
            $('#d1').text(dateParts[0]);
            $('#d1').css('margin-top', d1[0]);
            $('#d1').css('margin-left', d1[1]);

            var d2 = data.d2.split(',');
            $('#d2').text(dateParts[1]);
            $('#d2').css('margin-top', d2[0]);
            $('#d2').css('margin-left', d2[1]);

            var monthParts = month.split('');

            var m1 = data.m1.split(',');
            $('#m1').text(monthParts[0]);
            $('#m1').css('margin-top', m1[0]);
            $('#m1').css('margin-left', m1[1]);

            var m2 = data.m2.split(',');
            $('#m2').text(monthParts[1]);
            $('#m2').css('margin-top', m2[0]);
            $('#m2').css('margin-left', m2[1]);

            var yearParts = year.split('');

            var y1 = data.y1.split(',');
            if(y1=='0px,0px'){
                $('#y1').hide();
            }else{
                $('#y1').text(yearParts[0]);
                $('#y1').css('margin-top', y1[0]);
                $('#y1').css('margin-left', y1[1]);
            }

            var y2 = data.y2.split(',');
            if(y2=='0px,0px'){
                $('#y2').hide();
            }else{
                $('#y2').text(yearParts[1]);
                $('#y2').css('margin-top', y2[0]);
                $('#y2').css('margin-left', y2[1]);
            }

            var y3 = data.y3.split(',');
            $('#y3').text(yearParts[2]);
            $('#y3').css('margin-top', y3[0]);
            $('#y3').css('margin-left', y3[1]);

            var y4 = data.y4.split(',');
            $('#y4').text(yearParts[3]);
            $('#y4').css('margin-top', y4[0]);
            $('#y4').css('margin-left', y4[1]);

            if(data.seprator){
                var ds1 = data.ds1.split(',');
                $('#ds1').text(data.seprator);
                $('#ds1').css('margin-top', ds1[0]);
                $('#ds1').css('margin-left', ds1[1]);

                var ds2 = data.ds2.split(',');
                $('#ds2').text(data.seprator);
                $('#ds2').css('margin-top', ds2[0]);
                $('#ds2').css('margin-left', ds2[1]);
            }
            console.log(dateParts);
        }else{
            $('#y1').text('');
            $('#y2').text('');
            $('#y3').text('');
            $('#y4').text('');
            $('#m1').text('');
            $('#m2').text('');
            $('#d1').text('');
            $('#d2').text('');
            $('#ds1').text('');
            $('#ds2').text('');

        }
    }).fail(function() {
        console.log("error");
    }).always(function() {
        console.log("complete");
    });
}
function printacountfo(){
    var acoof=$("#acountof").val();
    $("#vou_amountof").html(acoof);
}
function commaSeparateNumber(val){
	while (/(\d+)(\d{3})/.test(val.toString())){
		val = val.toString().replace(/(\d+)(\d{3})/, '$1'+','+'$2');
	}
	return val;
}
function checkcurrency(){
    $("#inWords1").show();
    $("#amount").show();
    var currencyType = $("#currencyType :selected").val(); 
    var def_curnctname = $("#def_curnctname").val(); 

    if ($("#remove_currency").is(':checked')){
    }else{
        var intl = $("#amount_word").val();
        var amouintwpord1 = intl.replace(def_curnctname,'').replace("Only",'');
        var newamount = amouintwpord1+' '+currencyType+' '+"Only"
        $("#amount_word").val(newamount);
        var words = $.trim(newamount).split(" ");
        var wordlen = words.length;

        if(wordlen==1){
            $("#inWords1").html("");
            $("#inWords2").html("");	
            $("#inWords3").html("");
        }else if(wordlen>18){
            var all = newamount.split(" ");
            var first = all.slice(0,8).join();
            var second = all.slice(8,16).join();
            var three = all.slice(16).join();

            $("#inWords1").html(first.replace(/,/g , ' '));
            $("#inWords2").html(second.replace(/,/g , ' '));
            $("#inWords3").html(three.replace(/,/g , ' '));
        }else if( wordlen>10 && wordlen<=18 ){
            var all = newamount.split(" ");
            var first = all.slice(0,8).join();
            var second = all.slice(8).join();

            $("#inWords1").html(first.replace(/,/g , ' '));
            $("#inWords2").html(second.replace(/,/g , ' '));
            $("#inWords3").html("");
        }else{
            $("#inWords1").html(newamount);
            $("#inWords2").html("");	
            $("#inWords3").html("");
        }		
    }
}
function print(){
	var divToPrint=document.getElementById('template');
	var newWin=window.open('','Print-Window');

	newWin.document.open();
	newWin.document.write('<html><body onload="window.print()" style="margin-top:15px,margin-left:35px;">'+divToPrint.innerHTML+'</body></html>');
	newWin.document.close();
}

function getSupplierBillNoAndOrderNo(){
	var purchase_id=$("#purchase_id").val();
	if(purchase_id=='no_purchase_order'){
		$("#purchse_bill_no").prop('readonly',false);
		$("#supplier_order_no").prop('readonly',false);
		$("#payable_amount").prop('readonly',false);
	}
	if($("#myText").val()>0 && $("#payment_for").val()=='purchases' && $("#payment_type").val()=='balance_payment' && $('#purchase_id').val()>0)
	{
		$('#mynumber').prop('readonly', true);
	}else{
		$('#mynumber').prop('readonly', false);
	}
	if(purchase_id > 0){
		$.ajax({
			url: "{{action('Chequer\ChequeWriteController@getPurchaseOrderDataById')}}",
			type: 'post',
			dataType: 'json',
			data:{purchase_id:purchase_id},
			success: function(result) {
				
				if(result.purchase_bill_no){
					$("#purchse_bill_no").val(result.purchase_bill_no);
					$("#purchse_bill_no").attr("style", "pointer-events: none;");
					$("#purchse_bill_no").attr("readonly","readonly");
				}else{
					$("#purchse_bill_no").val("");
					$("#purchse_bill_no").removeAttr("style");
					$("#purchse_bill_no").removeAttr("readonly");
				}

				if(result.supplier_order_no){
					$("#supplier_order_no").val(result.results[0].supplier_order_no);
					$("#supplier_order_no").attr("style", "pointer-events: none;");
					$("#supplier_order_no").attr("readonly","readonly");
				}else{
					$("#supplier_order_no").val("");
					$("#supplier_order_no").removeAttr("style");
					$("#supplier_order_no").removeAttr("readonly");
				}
				$("#payable_amount").val(result.dueamount);
			}
		});
	}
	
}

function loadprintpage(){
	var t = document.getElementById("mySelect").value;
	var element = document.getElementById("template");
	element.className=t;
	var id = $('#mySelect :selected').val();
	$.ajax({
		url: "{{action('Chequer\ChequeTemplateController@getTemplateValues')}}",
		type: 'post',
		dataType: 'json',
		data: {id: id},
	}).done(function(data) {
		if (!$.trim(data))
			return false;
		var template_size = data.template_size.split(',');
		$('#template').css('width', template_size[0]);
		$('#template').css('height', template_size[1]);
		$('#template').css('background-image', 'url({{url("/public/chequer")}}/uploads/' + data.image_url + ')');
		var pay_name = data.pay_name.split(',');
		$('#pay').css('margin-top', pay_name[0]);
		$('#pay').css('margin-left', pay_name[1]);
		var date_pos = data.date_pos.split(',');
		$('#date').css('margin-top', date_pos[0]);
		$('#date').css('margin-left', date_pos[1]);
		var amount = data.amount.split(',');

		$('#amount').css('margin-top', amount[0]);
		$('#amount').css('margin-left', amount[1]);
		var amount_in_w1 = data.amount_in_w1.split(',');

		$('#inWords1').css('margin-top', amount_in_w1[0]);
		$('#inWords1').css('margin-left', amount_in_w1[1]);
		var amount_in_w2 = data.amount_in_w2.split(',');
		$('#inWords2').css('margin-top', amount_in_w2[0]);
		$('#inWords2').css('margin-left', amount_in_w2[1]);
		var amount_in_w3 = data.amount_in_w3.split(',');
		$('#inWords3').css('margin-top', amount_in_w3[0]);
		$('#inWords3').css('margin-left', amount_in_w3[1]);
		var not_negotiable = data.not_negotiable.split(',');
		$('#negotiable').css('margin-top', not_negotiable[0]);
		$('#negotiable').css('margin-left', not_negotiable[1]);
		var pay_only = data.pay_only.split(',');
		$('#payonly').css('margin-top', pay_only[0]);
		$('#payonly').css('margin-left', pay_only[1]);
		var template_cross = data.template_cross.split(',');
		$('#cross').css('margin-top', template_cross[0]);
		$('#cross').css('margin-left', template_cross[1]);
		var template_cross = data.template_cross.split(',');
		$('#doublecross').css('margin-top', template_cross[0]);
		$('#doublecross').css('margin-left', template_cross[1]);
		var template_signature_stamp = data.signature_stamp.split(',');
		var template_signature_stamp_area = data.signature_stamp_area.split(',');

		$('#Stamp').css('margin-top', template_signature_stamp[0]);
		$('#Stamp').css('margin-left', template_signature_stamp[1]);
		$('#Stamp').css('height', template_signature_stamp_area[0]);
		$('#Stamp').css('width', template_signature_stamp_area[1]);
		$('#stamimage').css('height', template_signature_stamp_area[0]);
		$('#stamimage').css('width', template_signature_stamp_area[1]);
		if(data.strikeBearer){
			var template_strikeBearer = data.strikeBearer.split(',');
			$('#strikeBearer').css('margin-top', template_strikeBearer[0]);
			$('#strikeBearer').css('margin-left', template_strikeBearer[1]);
		}
		if($("#mydate").val()){
			var date = $("#mydate").val();
			var parts = date.split('-');
			var day = parts[0];
			var month = parts[1];
			var year = parts[2];

			var dateParts = day.split('');

			var d1 = data.d1.split(',');
			$('#d1').text(dateParts[0]);
			$('#d1').css('margin-top', d1[0]);
			$('#d1').css('margin-left', d1[1]);

			var d2 = data.d2.split(',');
			$('#d2').text(dateParts[1]);
			$('#d2').css('margin-top', d2[0]);
			$('#d2').css('margin-left', d2[1]);

			var monthParts = month.split('');

			var m1 = data.m1.split(',');
			$('#m1').text(monthParts[0]);
			$('#m1').css('margin-top', m1[0]);
			$('#m1').css('margin-left', m1[1]);

			var m2 = data.m2.split(',');
			$('#m2').text(monthParts[1]);
			$('#m2').css('margin-top', m2[0]);
			$('#m2').css('margin-left', m2[1]);

			var yearParts = year.split('');

			var y1 = data.y1.split(',');
			if(y1=='0px,0px'){
				$('#y1').hide();
			}else{
				$('#y1').text(yearParts[0]);
				$('#y1').css('margin-top', y1[0]);
				$('#y1').css('margin-left', y1[1]);
			}

			var y2 = data.y2.split(',');
			if(y2=='0px,0px'){
				$('#y2').hide();
			}else{
				$('#y2').text(yearParts[1]);
				$('#y2').css('margin-top', y2[0]);
				$('#y2').css('margin-left', y2[1]);
			}

			var y3 = data.y3.split(',');
			$('#y3').text(yearParts[2]);
			$('#y3').css('margin-top', y3[0]);
			$('#y3').css('margin-left', y3[1]);

			var y4 = data.y4.split(',');
			$('#y4').text(yearParts[3]);
			$('#y4').css('margin-top', y4[0]);
			$('#y4').css('margin-left', y4[1]);

			if(data.seprator){
				var ds1 = data.ds1.split(',');
				$('#ds1').text(data.seprator);
				$('#ds1').css('margin-top', ds1[0]);
				$('#ds1').css('margin-left', ds1[1]);

				var ds2 = data.ds2.split(',');
				$('#ds2').text(data.seprator);
				$('#ds2').css('margin-top', ds2[0]);
				$('#ds2').css('margin-left', ds2[1]);
			}
			/* console.log(dateParts); */
		}
	}).fail(function() {
		console.log("error");
	}).always(function() {
		console.log("complete");
	});

	var valusesstamp=$("#addStamps").val();
}


function paytemfetch(id,chaqueid){
	var t = document.getElementById("mySelect").value;
	var element = document.getElementById("template");
	element.className=t;

	var id = id;
	$.ajax({
		url: "{{action('Chequer\ChequeWriteController@getTemplatechaque')}}",
		type: 'post',
		dataType: 'json',
		data: {id: id,chaqueid:chaqueid},
	}).done(function(data) {
		if (!$.trim(data))
			return false;
		var template_size = data.template_size.split(',');
		$('#template').css('width', template_size[0]);
		$('#template').css('height', template_size[1]);
		$('#template').css('background-image', 'url(url("/public/chequer")}}uploads/' + data.image_url + ')');
		var pay_name = data.pay_name.split(',');
		$('#pay').css('margin-top', pay_name[0]);
		$('#pay').css('margin-left', pay_name[1]);
		var date_pos = data.date_pos.split(',');
		$('#date').css('margin-top', date_pos[0]);
		$('#date').css('margin-left', date_pos[1]);
		var amount = data.amount.split(',');
		$('#amount').css('margin-top', amount[0]);
		$('#amount').css('margin-left', amount[1]);
		var amount_in_w1 = data.amount_in_w1.split(',');
		$('#inWords1').css('margin-top', amount_in_w1[0]);
		$('#inWords1').css('margin-left', amount_in_w1[1]);
		var amount_in_w2 = data.amount_in_w2.split(',');
		$('#inWords2').css('margin-top', amount_in_w2[0]);
		$('#inWords2').css('margin-left', amount_in_w2[1]);
		var amount_in_w3 = data.amount_in_w3.split(',');
		$('#inWords3').css('margin-top', amount_in_w3[0]);
		$('#inWords3').css('margin-left', amount_in_w3[1]);
		var not_negotiable = data.not_negotiable.split(',');
		$('#negotiable').css('margin-top', not_negotiable[0]);
		$('#negotiable').css('margin-left', not_negotiable[1]);
		var pay_only = data.pay_only.split(',');
		$('#payonly').css('margin-top', pay_only[0]);
		$('#payonly').css('margin-left', pay_only[1]);
		var template_cross = data.template_cross.split(',');
		$('#cross').css('margin-top', template_cross[0]);
		$('#cross').css('margin-left', template_cross[1]);
		var template_cross = data.template_cross.split(',');
		$('#doublecross').css('margin-top', template_cross[0]);
		$('#doublecross').css('margin-left', template_cross[1]);
		var template_signature_stamp = data.signature_stamp.split(',');
		var template_signature_stamp_area = data.signature_stamp_area.split(',');

		$('#Stamp').css('margin-top', template_signature_stamp[0]);
		$('#Stamp').css('margin-left', template_signature_stamp[1]);
		$('#Stamp').css('height', template_signature_stamp_area[0]);
		$('#Stamp').css('width', template_signature_stamp_area[1]);
		$('#stamimage').css('height', template_signature_stamp_area[0]);
		$('#stamimage').css('width', template_signature_stamp_area[1]);

		if(data.strikeBearer){
			var template_strikeBearer = data.strikeBearer.split(',');
			$('#strikeBearer').css('margin-top', template_strikeBearer[0]);
			$('#strikeBearer').css('margin-left', template_strikeBearer[1]);
		}

		if($("#mydate").val()){
			var date = $("#mydate").val();
			var parts = date.split('-');
			var day = parts[0];
			var month = parts[1];
			var year = parts[2];

			var dateParts = day.split('');

			var d1 = data.d1.split(',');
			$('#d1').text(dateParts[0]);
			$('#d1').css('margin-top', d1[0]);
			$('#d1').css('margin-left', d1[1]);

			var d2 = data.d2.split(',');
			$('#d2').text(dateParts[1]);
			$('#d2').css('margin-top', d2[0]);
			$('#d2').css('margin-left', d2[1]);

			var monthParts = month.split('');

			var m1 = data.m1.split(',');
			$('#m1').text(monthParts[0]);
			$('#m1').css('margin-top', m1[0]);
			$('#m1').css('margin-left', m1[1]);

			var m2 = data.m2.split(',');
			$('#m2').text(monthParts[1]);
			$('#m2').css('margin-top', m2[0]);
			$('#m2').css('margin-left', m2[1]);

			var yearParts = year.split('');

			var y1 = data.y1.split(',');
			if(y1=='0px,0px'){
				$('#y1').hide();
			}else{
				$('#y1').text(yearParts[0]);
				$('#y1').css('margin-top', y1[0]);
				$('#y1').css('margin-left', y1[1]);
			}

			var y2 = data.y2.split(',');
			if(y2=='0px,0px'){
				$('#y2').hide();
			}else{
				$('#y2').text(yearParts[1]);
				$('#y2').css('margin-top', y2[0]);
				$('#y2').css('margin-left', y2[1]);
			}

			var y3 = data.y3.split(',');
			$('#y3').text(yearParts[2]);
			$('#y3').css('margin-top', y3[0]);
			$('#y3').css('margin-left', y3[1]);

			var y4 = data.y4.split(',');
			$('#y4').text(yearParts[3]);
			$('#y4').css('margin-top', y4[0]);
			$('#y4').css('margin-left', y4[1]);

			if(data.seprator){
				var ds1 = data.ds1.split(',');
				$('#ds1').text(data.seprator);
				$('#ds1').css('margin-top', ds1[0]);
				$('#ds1').css('margin-left', ds1[1]);

				var ds2 = data.ds2.split(',');
				$('#ds2').text(data.seprator);
				$('#ds2').css('margin-top', ds2[0]);
				$('#ds2').css('margin-left', ds2[1]);
			}
			console.log(dateParts);
		}
	}).fail(function() {
		console.log("error");
	}).always(function() {
		console.log("complete");
	});
}

function addsufix(){
	var checkBox = document.getElementById("addsuffix");
	var checkBox1 = document.getElementById("addprifix");
	if (checkBox.checked == true && checkBox1.checked == true){
		var addsuffix="**";
		var addprefix="**";
		var amouintwpord=$("#amount_word").val();
		var amountnum=$("#mynumber").val();
		if(amountnum.indexOf('.') !== -1){
			var numsifix=addprefix+amountnum+addsuffix;
			$("#amount").html(commaSeparateNumber(amountnum));
			$("#vou_payamnum").html(commaSeparateNumber(amountnum));
		}else{
			var numsifix=addprefix+amountnum+".00"+addsuffix;
			$("#amount").html(commaSeparateNumber(amountnum)+".00");
			$("#vou_payamnum").html(commaSeparateNumber(amountnum)+".00");
		} 

		var newamount=addprefix+' '+amouintwpord+' '+addsuffix;
		$("#amount").html(numsifix);
		$("#amount_word").val();

		var words = $.trim(newamount).split(" ");
		var wordlen=words.length;
		if(wordlen==1){
			$("#inWords1").html("");
			$("#inWords2").html("");	
			$("#inWords3").html("");
		}else if(wordlen>18){
			var all = newamount.split(" ");
			var first = all.slice(0,8).join();
			var second = all.slice(8,16).join();
			var three = all.slice(16).join();

			$("#inWords1").html(first.replace(/,/g , ' '));
			$("#inWords2").html(second.replace(/,/g , ' '));
			$("#inWords3").html(three.replace(/,/g , ' '));
		}else if( wordlen>10 && wordlen<=18 ){
			var all = newamount.split(" ");
			var first = all.slice(0,8).join();
			var second = all.slice(8).join();

			$("#inWords1").html(first.replace(/,/g , ' '));
			$("#inWords2").html(second.replace(/,/g , ' '));
			$("#inWords3").html("");
		}else{
			$("#inWords1").html(newamount);
			$("#inWords2").html("");	
			$("#inWords3").html("");
		}
	}else if (checkBox.checked == true && checkBox1.checked == false){
		var addsuffix="***";
		var amouintwpord=$("#amount_word").val();
		var amountnum=$("#mynumber").val();
		var newamount=amouintwpord+' '+addsuffix;
		if(amountnum.indexOf('.') !== -1){
			var numsifix=amountnum+addsuffix;
			$("#amount").html(commaSeparateNumber(amountnum));
			$("#vou_payamnum").html(commaSeparateNumber(amountnum));
		}else{
			var numsifix=amountnum+".00"+addsuffix;
			$("#amount").html(commaSeparateNumber(amountnum)+".00");
			$("#vou_payamnum").html(commaSeparateNumber(amountnum)+".00");
		}

		$("#amount").html(numsifix);
		$("#amount_word").val();
		var words = $.trim(newamount).split(" ");
		var wordlen=words.length;
		if(wordlen==1){
			$("#inWords1").html("");
			$("#inWords2").html("");	
			$("#inWords3").html("");
		}else if(wordlen>18){
			var all = newamount.split(" ");
			var first = all.slice(0,8).join();
			var second = all.slice(8,16).join();
			var three = all.slice(16).join();

			$("#inWords1").html(first.replace(/,/g , ' '));
			$("#inWords2").html(second.replace(/,/g , ' '));
			$("#inWords3").html(three.replace(/,/g , ' '));
		}else if( wordlen>10 && wordlen<=18 ){
			var all = newamount.split(" ");
			var first = all.slice(0,8).join();
			var second = all.slice(8).join();

			$("#inWords1").html(first.replace(/,/g , ' '));
			$("#inWords2").html(second.replace(/,/g , ' '));
			$("#inWords3").html("");
		}else{
			$("#inWords1").html(newamount);
			$("#inWords2").html("");
			$("#inWords3").html("");
		}
	} else if (checkBox.checked == false && checkBox1.checked == true){
		var addsuffix="***";
		var addprefix="**";

		var amouintwpord=$("#amount_word").val();
		var amountnum=$("#mynumber").val();
		var newamount=addprefix+''+amouintwpord;
		if(amountnum.indexOf('.') !== -1){
			var numsifix=addprefix+amountnum;
			$("#amount").html(commaSeparateNumber(amountnum));
			$("#vou_payamnum").html(commaSeparateNumber(amountnum));
		}else{
			var numsifix=addprefix+amountnum+".00";
			$("#amount").html(commaSeparateNumber(amountnum)+".00");
			$("#vou_payamnum").html(commaSeparateNumber(amountnum)+".00");
		}

		$("#amount").html(numsifix);
		$("#amount_word").val();
		var words = $.trim(newamount).split(" ");
		var wordlen=words.length;
		if(wordlen==1){
			$("#inWords1").html("");
			$("#inWords2").html("");	
			$("#inWords3").html("");
		}else if(wordlen>18){
			var all = newamount.split(" ");
			var first = all.slice(0,8).join();
			var second = all.slice(8,16).join();
			var three = all.slice(16).join();

			$("#inWords1").html(first.replace(/,/g , ' '));
			$("#inWords2").html(second.replace(/,/g , ' '));
			$("#inWords3").html(three.replace(/,/g , ' '));
		}else if( wordlen>10 && wordlen<=18 ){
			var all = newamount.split(" ");
			var first = all.slice(0,8).join();
			var second = all.slice(8).join();

			$("#inWords1").html(first.replace(/,/g , ' '));
			$("#inWords2").html(second.replace(/,/g , ' '));
			$("#inWords3").html("");
		}else{
			$("#inWords1").html(newamount);
			$("#inWords2").html("");	
			$("#inWords3").html("");
		}
	} else {
		var addsuffix="***";
		var amouintwpord=$("#amount_word").val();
		var amountnum=$("#mynumber").val();
		$("#amount").html(amountnum);
		var newamount=amouintwpord;
		$("#amount_word").val();

		var words = $.trim(newamount).split(" ");
		var wordlen=words.length;
		if(wordlen==1){
			$("#inWords1").html("");
			$("#inWords2").html("");
			$("#inWords3").html("");
		}else if(wordlen>18){
			var all = newamount.split(" ");
			var first = all.slice(0,8).join();
			var second = all.slice(8,16).join();
			var three = all.slice(16).join();

			$("#inWords1").html(first.replace(/,/g , ' '));
			$("#inWords2").html(second.replace(/,/g , ' '));
			$("#inWords3").html(three.replace(/,/g , ' '));
		}else if( wordlen>10 && wordlen<=18 ){
			var all = newamount.split(" ");
			var first = all.slice(0,8).join();
			var second = all.slice(8).join();

			$("#inWords1").html(first.replace(/,/g , ' '));
			$("#inWords2").html(second.replace(/,/g , ' '));
			$("#inWords3").html("");
		}else{
			$("#inWords1").html(newamount);
			$("#inWords2").html("");	
			$("#inWords3").html("");
		}
	}
}
</script>
<script>
	$(document).ready(function(){
		
		checkcurrency();

	$("#inWords1").html("");
	$("#amount").html("");
	$('#remove_currency').click(function(){
		$("#inWords1").show();
		$("#amount").show();
		if($(this).is(":checked")){
			var amouintwpord=$("#amount_word").val();
			var def_curnctname=$("#def_curnctname").val();
			var currencyType=$("#currencyType option:selected").val();
			$("#currencyType").val('');
			var amouintwpord1=amouintwpord.replace(currencyType,'');
			var amouintwpord=$("#amount_word").val(amouintwpord1);

			var words = $.trim(amouintwpord1).split(" ");
			var wordlen=words.length;
			if(wordlen==1){
				$("#inWords1").html("");
				$("#inWords2").html("");
				$("#inWords3").html("");
			}else if(wordlen>18){
				var all = amouintwpord1.split(" ");
				var first = all.slice(0,8).join();
				var second = all.slice(8,16).join();
				var three = all.slice(16).join();

				$("#inWords1").html(first.replace(/,/g , ' '));
				$("#inWords2").html(second.replace(/,/g , ' '));
				$("#inWords3").html(three.replace(/,/g , ' '));
			}else if( wordlen>10 && wordlen<=18 ){
				var all = amouintwpord1.split(" ");
				var first = all.slice(0,8).join();
				var second = all.slice(8).join();

				$("#inWords1").html(first.replace(/,/g , ' '));
				$("#inWords2").html(second.replace(/,/g , ' '));
				$("#inWords3").html("");
			}else{
				$("#inWords1").html(amouintwpord1);
				$("#inWords2").html("");	
				$("#inWords3").html("");
			}		
		}else if($(this).is(":not(:checked)")){
			var amouintwpord=$("#amount_word").val();
			var def_curnctname=$("#def_curnctname").val();
			var currencyType=$("#currencyType").val(def_curnctname);
			var amouintwpord1=amouintwpord.replace("Only",'');
			var newamount=amouintwpord1+' '+def_curnctname+' '+"Only";

			var words = $.trim(newamount).split(" ");
			var wordlen=words.length;
			if(wordlen==1){
				$("#inWords1").html("");
				$("#inWords2").html("");
				$("#inWords3").html("");
			}else if(wordlen>18){
				var all = newamount.split(" ");
				var first = all.slice(0,8).join();
				var second = all.slice(8,16).join();
				var three = all.slice(16).join();

				$("#inWords1").html(first.replace(/,/g , ' '));
				$("#inWords2").html(second.replace(/,/g , ' '));
				$("#inWords3").html(three.replace(/,/g , ' '));
			}else if( wordlen>10 && wordlen<=18 ){
				var all = newamount.split(" ");
				var first = all.slice(0,8).join();
				var second = all.slice(8).join();

				$("#inWords1").html(first.replace(/,/g , ' '));
				$("#inWords2").html(second.replace(/,/g , ' '));
				$("#inWords3").html("");
			}else{
				$("#inWords1").html(newamount);
				$("#inWords2").html("");	
				$("#inWords3").html("");
			}
		}
	});
		$('#myText').on( 'change', function(){
			printpayname();
		} );
		$('#payment_for').on( 'change', function(){
			printpayname();
		} );
		$('#payment_type').on( 'change', function(){
			printpayname();
		} );
		$('#mydate').datepicker({
			format: 'yyyy-mm-dd',
			autoclose: true
		});
		$('#mydate').datepicker('setDate', 'today');

		// $('#mydate2').datepicker({
		// 	format: 'dd-mm-yyyy',
		// 	autoclose: true
		// });
		// $('#mydate2').datepicker('setDate', 'today');
		$("#dateCondition").on('change',function(){
			if($(this).val()=='select_date')
				$('#select_date_part').show();
			else
				$('#select_date_part').hide();
			temFunction();
		});
	$("#print_date_only").on('click',function(){
		var supplierid = $('#getSupplierDate').val();
		var chequeNo = $('#chequeNo').val();
		var purchase_id = $('#purchase_id').val();
		var payable_amount = $('#payable_amount').val();
		var unpaid = $('#payable_amount').val();
		var paid_to_supplier = $('#paid_to_supplier option:selected').val();
		var paid_amounts = $('#mynumber').val();
		if(chequeNo=="")
		{
			alert("Please enter Cheque No.");
			return;
		}
		if(purchase_id=="")
		{
			alert("Please Select Order NO.");
			return;
		}
		if($('#mynumber').val()=="" || $('#mynumber').val()==0)
		{
			alert("Please Enter Amount.");
			return;
		}
		if(unpaid != 0 ){
			if(paid_to_supplier == ""){
				alert("Please Select Payment Status.");
				paid_to_supplier = 0;
				return;
			}
			if(parseFloat(unpaid) < parseFloat(paid_amounts)){
				alert("Paid To Supplier amount can't be greater then Unpaid.");
				return;
			}
		}

			if(supplierid != ""){
				$.ajax({
					type:'POST',
					url:"{{action('Chequer\ChequeWriteController@getChequeNoUniqueOrNotCheck')}}",
					data: {supplierid: supplierid, chequeNo : chequeNo},
					dataType: 'JSON',
					success:function(data){
						/* console.log(data); */
						if(data.cheque_check != null){
							alert("please Enter right Cheque No");
						}else{
							var template_id = $('#mySelect option:selected').val();
							var cheque_amount  = $('#mynumber').val();
							/* var payee = $('#myText option:selected').text(); */
							var payee = $('#myText').val();
							var cheque_no = $('#chequeNo').val();
							var cheque_date = $('#mydate').val();
							var payee_tempname=$("#payee_tempname").val();
							var stampvalu=$("#addStamps").val();
							var amount_word=$("#amount_word").val();
							var bankacount=$("#bankacount").val();
							var purchse_bill_no=$("#purchse_bill_no").val();
							var acoof=$("#acountof").val();
							$("#vou_date").html(cheque_date);
				
							$.ajax({
								url: "{{ url('cheque-write') }}",
								type    : 'POST', 
								dataType: 'json',
								data: {purchse_bill_no:purchse_bill_no,print_status:'dateonly',payable_amount:payable_amount,purchase_id:purchase_id,paid_to_supplier:paid_to_supplier,template_id: template_id,cheque_amount: cheque_amount,payee: payee,cheque_no: cheque_no,cheque_date: cheque_date,payee_tempname:payee_tempname,stampvalu:stampvalu,amount_word:amount_word,bankacount:bankacount,acoof:acoof},
								error: function (jqXHR, exception) {
									console.log(jqXHR, exception);
								}
							}).done(function(data) {
								var dateHtml = $("#d1").parent().parent().wrap('<p/>').parent().html();
								var templateHtmlOld = $("div#template").html();
								$("div#template").html('');
								$("div#template").append(dateHtml);
								
								var divToPrint=document.getElementById('template').innerHTML;
								$("div#template").html(templateHtmlOld);
								var newWin=window.open('','Print-Window');
								newWin.document.open();
								newWin.document.write('<html><body onload="window.print()" style="margin-top:15px,margin-left:35px;">'+divToPrint+'</body></html>');
								newWin.document.close();
								location.reload();
							});
						}
					}
				});
			}else{
				alert("please Select Purchase Order");
			}
		});
		$('#paid_to_supplier').on('change',function(){
			if($(this).val()=='Full Payment' || $(this).val()=='Last Payment'){
				$('#mynumber').val($('#payable_amount').val());
				// $('#mynumber').prop('readonly',true);
			}
            //else
				// $('#mynumber').prop('readonly',false);
		});
		function getBankNextChequedNO(){
			var bankacounts = $("#bankacount :selected").val();
			if(bankacounts){
				$.ajax({
					url: "{{action('Chequer\ChequeWriteController@getNextChequedNO')}}",
					type: 'post',
					data: {bankacount: bankacounts},
					success:function(data){
						if(data > 0){
							if(data.length == 1){
								$("#chequeNo").val('00'+data);
							}else if(data.length == 2){
								$("#chequeNo").val('0'+data);
							}else{
								$("#chequeNo").val(data);
							}
							$("#chequeNo").attr("style", "pointer-events: none;");
							$("#chequeNo").attr("readonly","readonly");
						}else{
							$("#chequeNo").val("");
							$("#chequeNo").removeAttr("style");
							$("#chequeNo").removeAttr("readonly");
						}
					}
				});
			}
		}
		getBankNextChequedNO();
		$('#myText').select2();
		$('#purchase_id').select2();
		$('#paid_to_supplier').select2();
		// $('#payee_temlist').select2();
		$('#mySelect').select2();
		$('#bankacount').select2();
		$('#addStamps').select2();
		$('#currencyType').select2();
		$('#mynumber').focus();
			$('#paydive').num2words();

});
</script>

@endsection