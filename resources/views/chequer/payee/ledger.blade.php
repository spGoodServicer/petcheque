@extends('layouts.app')
@section('title', __('contact.view_contact'))
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>{{ __('lang_v1.View Payee ledger') }}</h1>
</section>
<section class="content no-print">
    <div class="box box-primary">
        <div class="box-header">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('ledger_date_range', __('report.date_range') . ':') !!}
                        {!! Form::text('ledger_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly', 'id' => 'ledger_date_range_new']); !!}
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('ledger_transaction_type', __('lang_v1.transaction_type') . ':') !!}
                        {!! Form::select('ledger_transaction_type', ['debit' => 'Debit', 'credit' => 'Credit'], null, ['placeholder' => __('lang_v1.please_select'), 'style' => 'width: 100%', 'class' => 'form-control select2', 'readonly', 'id' => 'ledger_transaction_type']); !!}
                    </div>
                </div>
               
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('ledger_transaction_amount', __('lang_v1.transaction_amount') . ':') !!}
                        {!! Form::select('ledger_transaction_amount', $transaction_amounts ,null, ['placeholder' => __('lang_v1.please_select'), 'style' => 'width: 100%', 'class' => 'form-control select2', 'readonly', 'id' => 'ledger_transaction_amount']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('ledger_general_search', __('lang_v1.search') . ':') !!}
                        {!! Form::text('ledger_general_search', null, ['placeholder' => __('lang_v1.search'), 'class' =>
                        'form-control', 'id' => 'ledger_general_search']); !!}
                    </div>
                </div>
            
            </div>
        </div>
    </div>
</section>
@endsection