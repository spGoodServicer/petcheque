@extends('layouts.app')
@section('title', __('cheque.templates'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Printed Cheque</h1>
    <div class="box box-info">
        <div class="box-header">
            <i class="fa fa-filter" aria-hidden="true"></i>
            <h3 class="box-title">Filters</h3>
        </div>
        <div class="box-body">
            {{ Form::open(array('id' => 'filterForm')) }}
            <div class="row">
                <div class="col-md-3">
                    {!! Form::label('bank_acount_no',__('lang_v1.bank_account_no') . ':') !!}
                    {!! Form::select('bank_acount_no', $bankAcounts, ($defaultVal)?$defaultVal['bank_acount_no']:null, ['placeholder' =>__('report.all'), 'class' => 'form-control select2 filter-control', 'style' => 'width:100%', 'id' =>'bank_acount_no']); !!}
                </div>
                <div class="col-md-3">
                    {!! Form::label('payee_no','Supplier/Payee :') !!}
                    {!! Form::select('payee_no', $payeeList, ($defaultVal)?$defaultVal['payee_no']:null, ['placeholder' =>__('report.all'), 'class' => 'form-control select2 filter-control', 'style' => 'width:100%', 'id' =>'payee_no']); !!}
                </div>
                <div class="col-md-3">
                    {!! Form::label('cheque_no',__('cheque.cheque_number') . ':') !!}
                    {!! Form::select('cheque_no', $chequeNumbers, ($defaultVal)?$defaultVal['cheque_no']:null, ['placeholder' =>__('report.all'), 'class' => 'form-control select2 filter-control', 'style' => 'width:100%', 'id' =>'cheque_no']); !!}
                </div>
                <div class="col-md-3">
                    {!! Form::label('payment_status',__('purchase.payment_status').':') !!}
                    {!! Form::select('payment_status', $paymentStatus, ($defaultVal)?$defaultVal['payment_status']:null, ['placeholder' =>__('report.all'), 'class' => 'form-control select2 filter-control', 'style' => 'width:100%', 'id' =>'payment_status']); !!}
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    {!! Form::label('date_range', __('report.date_range') . ':') !!}
                    {!! Form::text('date_range',($defaultVal)? $defaultVal['startDate'].' - '.$defaultVal['endDate']: @date('m/01/Y').' - '.@date('m/t/Y') , ['placeholder' => __('lang_v1.select_a_date_range'), 'class' =>'form-control filter-control', 'id' => 'date_range', 'readonly']); !!}

                </div>
                <div class="col-md-3"></div>
                <div class="col-md-3"></div>
                <div class="col-md-3"></div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => 'Printed Cheque List'])

   
    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="templates_table">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Bank Account No</th>
                    <th>Payment For</th>
                    <th>Supplier/Payee</th>
                    <th>Purchase Order Number</th>
                    <!--<th>Purchase Bill Number</th>-->
                    <!--<th>Supplier Bill Number</th>-->
                    <th>Cheque No</th>
                    <th>Cheque Amount</th>
                    <th>Cheque Date</th>
                    <th>Reference/Invoice Number</th>
                    <th>Payment Status</th>
                    <th>User</th>
                </tr>
            </thead>
            <tbody>
                @foreach($printedcheque as $data)
                    <tr>
                        <td>{{date("Y-m-d", strtotime($data->created_at))}}</td>
                        <td>{{$data->bank_account_no}}</td>
                        <td>{{$data->type}}</td>
                        <td>{{$data->name}}</td>
                        <td>
                            @if($data->type=='purchase')
                                {{$data->invoice_no}}
                            @elseif($data->type=='expense')
                                {{$data->ref_no}}
                            @endif
                        </td>
                        <!--<td></td>-->
                        <!--<td></td>-->
                        <td>
                            {{$data->cheque_no}}
                            @if($data->print_type=='dateonly')
                            <span class="badge badge-danger navbar-badge">Printed Date Only</span>
                            @endif
                        </td>
                        <td>{{$data->cheque_amount}}</td>
                        <td>{{$data->cheque_date}}</td>
                        <td>{{$data->refrence}}</td>
                        <td>{{$data->supplier_paid_amount}}</td>
                        <td>{{$data->username}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @endcomponent
</section>
@endsection
@section('javascript')
<script>
    $(document).ready(function(){
        $('input#date_range').daterangepicker(
            dateRangeSettings
        );
        $('.filter-control').on('change',function(){
            $('#filterForm').submit();
        })
    });
     $('#templates_table').DataTable({
        
    });
</script>
@endsection