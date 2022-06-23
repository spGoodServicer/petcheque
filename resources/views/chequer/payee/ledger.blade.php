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
        <div class="box-body">
            <div class="row">
                <div class="col-md-12 col-sm-12 @if(!empty($for_pdf)) width-100 text-center @endif">
                    <p class="text-center"><strong>{{$contact->business->name}}</strong><br>{{$location_details->city}}
                        , {{$location_details->state}}<br>{!!
                        $location_details->mobile !!}</p>
                    <hr>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-6 @if(!empty($for_pdf)) width-50 f-left @endif">
                    <p class="bg_color" style="width: 40%">@lang('lang_v1.to'):</p>
                    <p><strong>{{$contact->name}}</strong><br> {!! $contact->contact_address !!} @if(!empty($contact->email))
                            <br>@lang('business.email'): {{$contact->email}} @endif
                        <br>@lang('contact.mobile'): {{$contact->mobile}}
                        @if(!empty($contact->tax_number)) <br>@lang('contact.tax_no'): {{$contact->tax_number}} @endif
                    </p>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-6 text-right align-right @if(!empty($for_pdf)) width-50 f-left @endif">
                    <p class=" bg_color" style="margin-top: @if(!empty($for_pdf)) 20px @else 0px @endif; font-weight: 500;">
                        @lang('lang_v1.account_summary')</p>
                        <hr>
                    <table class="table table-condensed text-left align-left no-border @if(!empty($for_pdf)) table-pdf @endif">
                        <tr>
                            <td>@lang('lang_v1.opening_balance')</td>
                            <td id="opening_balance">{{number_format($opening_bal,  $currency_precision, session('currency')['decimal_separator'], session('currency')['thousand_separator'])}}
                            </td>
                        </tr>
                        <tr>
                            <td>@lang('lang_v1.beginning_balance')</td>
                            <td>
                            {{-- {{number_format($ledger_details['beginning_balance'],  $currency_precision, session('currency')['decimal_separator'], session('currency')['thousand_separator'])}} --}}
                            </td>
                        </tr>
                        @if( $contact->type == 'supplier' || $contact->type == 'both')
                            <tr>
                                <td>@lang('report.total_purchase')</td>
                                <td>
                                {{-- {{number_format($ledger_details['total_purchase'],  $currency_precision, session('currency')['decimal_separator'], session('currency')['thousand_separator'])}} --}}
                                </td>
                            </tr>
                        @endif
                        @if( $contact->type == 'customer' || $contact->type == 'both')
                            <tr>
                                <td>@lang('lang_v1.total_sales')</td>
                                <td id="total_invoice">
                                {{-- {{number_format($ledger_details['total_invoice'],  $currency_precision, session('currency')['decimal_separator'], session('currency')['thousand_separator'])}} --}}
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td>@lang('sale.total_paid')</td>
                            <td id="total_paid">
                            {{-- {{number_format($ledger_details['total_paid'],  $currency_precision, session('currency')['decimal_separator'], session('currency')['thousand_separator'])}} --}}
                            </td>
                        </tr>
                        <tr>
                            <td><strong>@lang('lang_v1.balance_due')</strong></td>
                            <td id="total_due">
                            {{-- {{number_format(($ledger_details['balance_due']),  $currency_precision, session('currency')['decimal_separator'], session('currency')['thousand_separator'])}} --}}
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-12 col-sm-12 @if(!empty($for_pdf)) width-100 @endif">
                    <p style="text-align: center !important; float: left; width: 100%;">
                        <strong>
                            {{-- @lang('lang_v1.ledger_table_heading',['start_date' =>$ledger_details['start_date'], 'end_date' => $ledger_details['end_date']]) --}}
                        </strong>
                    </p>
                    <table class="table table-striped @if(!empty($for_pdf)) table-pdf td-border @endif" id="ledger_table">
                        <thead>
                        <tr class="row-border">
                                <th>@lang('lang_v1.date')</th>
                                <th>@lang('purchase.ref_no')</th>
                                <th>@lang('lang_v1.type')</th>
                                <th>@lang('sale.location')</th>
                                <th>@lang('sale.payment_status')</th>
                                <th>@lang('account.debit')</th>
                                <th>@lang('account.credit')</th>
                                <th>@lang('lang_v1.balance')</th>
                                <th>@lang('lang_v1.payment_method')</th>
                        </tr>
                        </thead>
                        <tbody>
                            @php
                                // $balance = $ledger_details['balance']??$ledger_details['beginning_balance'];
                                // $balance = $ledger_details['bf_balance'];
                            @endphp
                            <!--tr>
                                <td class="row-border"></td>
                                <td colspan="6" class="row-border">B/F Balance</td>
                                <td class="row-border">
                                    {{-- {{number_format($ledger_details['bf_balance'],  $currency_precision, session('currency')['decimal_separator'], session('currency')['thousand_separator'])}} --}}
                                </td>
                                <td class="row-border"></td>
                            </tr-->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection