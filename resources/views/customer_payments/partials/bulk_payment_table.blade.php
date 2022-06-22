{{$xxamount =  0}}
@if (!empty($sells) && $sells->count() > 0)
    @foreach ($sells as $sell)
        @if($sell->type == 'opening_balance')

        {{$opblance =  (float) str_replace(',', '', @num_format($sell->final_total)) }}
        @if($opblance < 0 )
        {{$xxamount =  $opblance}}

        @endif
        @endif
    @endforeach
@endif

<tr>
    <td colspan="4"></td>
    <td>@lang('lang_v1.excess_amount')</td>
    <td class="interest_selection_checkbox hide"></td>
    <td>
        <div class="row">
            <div class="col-md-1">
                {!! Form::checkbox('pay_all', 1, false, ['class' => 'input-icheck pay_all','style' => 'margin-top: 10px;transform: scale(2)']) !!}
            </div>
            <div class="col-md-4">
                {!! Form::number('excess_amount', abs($xxamount) , ['class' => 'form-control', 'id' => 'excess_amount', 'step'=>'0.01', 'style' => 'width: 150px !important']) !!}
            </div>
        </div>
    </td>
</tr>

@if (!empty($sells) && $sells->count() > 0)
    @foreach ($sells as $sell)

    @php
        $outstandingRow = 0;
        if($sell->cheque_number != null){
            $outstandingRow = abs($sell->final_total + $sell->cheque_return_charges - $sell->total_paid);
        }else{
            $outstandingRow = abs($sell->final_total - $sell->total_paid);
        }
    @endphp

    {{$sell->type}}
    @if($sell->type == 'opening_balance')
        @if($xxamount >= 0 and $outstandingRow >= 1)

        <!-- Show the row only if there is outstanding amount is greater than 0-->
            <tr>
                <td>{{ @format_date($sell->transaction_date)}}</td>
                <td>{{ $sell->invoice_no }}</td>
                <td>{{ $sell->type == 'opening_balance' ?
                    'Opening Balance' : $sell->order_no}}
                </td>
                <td>{{ @num_format(abs($sell->final_total)) }}</td>
                <td>{{ @num_format(abs($sell->final_total-$sell->total_paid)) }}
                    {!! Form::hidden('outstanding['.$sell->transaction_id.']', $sell->final_total-$sell->total_paid, ['id' => 'outstanding_amount_'.$sell->transaction_id, 'class' => 'form-control outstanding_'.$sell->transaction_id , 'data-id' => $sell->transaction_id ]) !!}
                </td>
                <td class="interest_selection_checkbox hide">
                    <div class="row">
                        <div class="col-md-6 ">
                            {!! Form::number('interest['.$sell->transaction_id.']', null, ['id' => 'interest_amount_'.$sell->transaction_id , 'class' => 'form-control interest_column_total interest_selection_checkbox hide interest_'.$sell->transaction_id ,'step'=>'0.01', 'data-id' => $sell->transaction_id ]) !!}
                        </div>
                    </div>
                </td>
                <td>
                    <div class="row">
                        <div class="col-md-1">
                            {!! Form::checkbox('paying['.$sell->transaction_id.']', 1, false, ['class' => 'input-icheck paying_checkbox paying_'.$sell->transaction_id, 'data-id' => $sell->transaction_id ,'style' => 'margin-top: 10px;transform: scale(2)']) !!}
                        </div>
                        <div class="col-md-4">
                            {!! Form::number('amount['.$sell->transaction_id.']', null, ['id' => 'total_amount_'.$sell->transaction_id, 'class' => 'form-control amount_column_total amount_selection_box amount_'.$sell->transaction_id ,'step'=>'any', 'data-id' => $sell->transaction_id, 'style' => 'width: 150px !important' ]) !!}
                        </div>
                    </div>
                </td>
            </tr>
        @endif
    @elseif ($outstandingRow >= 1)
         <!-- Show the row only if there is outstanding amount is greater than 0-->
            <tr>
            <td>{{ @format_date($sell->transaction_date)}}</td>
            <td>{{ $sell->invoice_no }}</td>
            <td>
                @if($sell->is_return and $sell->cheque_number != null)
                <p>
                    <span class="text-danger">
                        Return Cheque Number:
                    </span>
                    <span class="font-weight-bold">
                        {{ $sell->cheque_number }}
                    </span>
                </p>
                <p>
                    <span class="text-danger">
                        Bank Name:
                    </span>
                    <span class="font-weight-bold">
                        {{ $sell->bank_name }}
                    </span>
                </p>
                @else
                {{ $sell->order_no}}
                @endif
            </td>
            <td>
                @if($sell->is_return and $sell->cheque_number != null)
                <p>
                    <span class="text-danger">
                        Cheque Return Amount:
                    </span>
                    <span class="font-weight-bold">
                        {{ @num_format(abs($sell->final_total)) }}
                    </span>
                </p>
                <p>
                    <span class="text-danger">
                        Cheque Return Charge:
                    </span>
                    <span class="font-weight-bold">
                        {{ @num_format(abs($sell->cheque_return_charges )) }}
                    </span>

                </p>
                @else
                    {{ @num_format(abs($sell->final_total)) }}
                @endif
            </td>
            <td>
                @if($sell->cheque_number != null)
                    {{ @num_format(abs($sell->final_total + $sell->cheque_return_charges - $sell->total_paid)) }}
                @else
                    {{ @num_format(abs($sell->final_total-$sell->total_paid)) }}
                @endif
                {!! Form::hidden('outstanding['.$sell->transaction_id.']',$sell->cheque_number != null ? 2 * $sell->final_total + $sell->cheque_return_charges - $sell->total_paid : $sell->final_total-$sell->total_paid, ['id' => 'outstanding_amount_'.$sell->transaction_id, 'class' => 'form-control outstanding_'.$sell->transaction_id , 'data-id' => $sell->transaction_id ]) !!}
            </td>
            <td class="interest_selection_checkbox hide">
                <div class="row">
                    <div class="col-md-6 ">
                        {!! Form::number('interest['.$sell->transaction_id.']', null, ['id' => 'interest_amount_'.$sell->transaction_id ,'step'=>'0.01', 'class' => 'form-control interest_column_total interest_selection_checkbox hide interest_'.$sell->transaction_id , 'data-id' => $sell->transaction_id ]) !!}
                    </div>
                </div>
            </td>
            <td>
                <div class="row">
                    <div class="col-md-1">
                        {!! Form::checkbox('paying['.$sell->transaction_id.']', 1, false, ['class' => 'input-icheck paying_checkbox paying_'.$sell->transaction_id, 'data-id' => $sell->transaction_id ,'style' => 'margin-top: 10px;transform: scale(2)']) !!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::number('amount['.$sell->transaction_id.']', null, ['id' => 'total_amount_'.$sell->transaction_id, 'class' => 'form-control amount_column_total amount_selection_box amount_'.$sell->transaction_id ,'step'=>'any', 'data-id' => $sell->transaction_id, 'style' => 'width: 150px !important' ]) !!}
                    </div>
                </div>
            </td>
        </tr>
    @endif
    @endforeach
@else
    <tr class="text-center">
        <td colspan="6">@lang('lang_v1.no_data_available_in_table')</td>
    </tr>
@endif
