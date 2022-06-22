<div class="modal-dialog modal-lg no-print" role="document" style="width: 70%">
    <div class="modal-content">
        @php $sale = $sell[0] @endphp
        <div class="modal-header">
            <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="modalTitle">
                @lang('sale.sell_details') (<b>@lang('sale.invoice_no'):</b> {{ $sale->payment_ref_no }})
            </h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-sm-6">
                    @if(!empty($sale->contact))
                        <b>{{ __('customer.customer') }}:</b>
                        <br> {{ $sale->contact->name }}
                        <br>
                    @endif
                    {{ __('business.address') }}:
                    <br>
                    @if(!empty($sale->billing_address()))
                        {{$sale->billing_address()}}
                    @else
                        @if(!empty($sale->contact))
                            @if($sale->contact->landmark)
                                {{ $sale->contact->landmark }},
                            @endif
                            {{ $sale->contact->city }}
                            @if($sale->contact->state)
                                {{ ',' . $sale->contact->state }}
                                <br>
                            @endif
                            @if($sale->contact->country)
                                {{ $sale->contact->country }}
                                <br>
                            @endif
                            @if($sale->contact->mobile)
                                {{__('contact.mobile')}}: {{ $sale->contact->mobile }}
                            @endif
                            @if($sale->contact->alternate_number)
                                <br> {{__('contact.alternate_contact_number')}}: {{ $sale->contact->alternate_number }}
                            @endif
                            @if($sale->contact->landline)
                                <br> {{__('contact.landline')}}: {{ $sale->contact->landline }}
                            @endif
                        @endif
                    @endif
                </div>
                <div class="col-sm-6">
                    <b>@lang('sale.invoice_no')</b> : {{ $sale->payment_ref_no }}
                    <br>
                    <b>@lang('messages.date'):</b> {{ @format_date($sale->paid_on) }}
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-6">
                    @php
                        $final_total = 0;
                        foreach ($sell as $value) {
                            $final_total += $value->amount;
                        }
                    @endphp
                    <b>@lang('sale.amount')</b> : {{ number_format($final_total,$company->currency_precision) }}
                    <br>
                    <b>@lang('sale.payment_method')</b> : @php $paid_in_types = ['customer_page' => 'Customer Page', 'all_sale_page' => 'All Sale Page', 'settlement' => 'Settlement']; @endphp
                    {{$sale->method}}
                    <br>
                    @if($sale->method == "cheque")
                        <b>@lang('sale.cheque_number')</b> : {{ $sale->cheque_number }}
                        <br>
                        <b>@lang('sale.bank_name')</b> : {{ $sale->bank_name }}
                        <br>
                        <b>@lang('sale.cheque_date')</b> : {{ $sale->created_at->format('m/d/Y') }}
                        <br>
                    @endif
                    </br>
                    <div class="col-sm-4"><b>@lang('sale.bill_no')</b></div>
                    <div class="col-sm-4"><b>@lang('lang_v1.interest')</b></div>
                    <div class="col-sm-4"><b>@lang('sale.amount')</b></div>
                    @foreach($sell as $payment_line)
                        <div class="col-sm-4">{{$payment_line->transaction_id}}</div>
                        <div class="col-sm-4">{{number_format(($payment_line->account_transctions() ? $payment_line->account_transctions()->interest : 0), $company->currency_precision)}}</div>
                        <div class="col-sm-4">{{number_format($payment_line->amount, $company->currency_precision)}}</div>
                    @endforeach
                </div>
                <div class="col-md-6">
                    <b>@lang('sale.payment_note')</b> : {{ $sale->payment_note }}
                    <br>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <a href="#" class="print-invoice btn btn-primary" data-href="{{route('sell.printOustandingInvoice', [
                'ref_number' => $sale->payment_ref_no,
                'paid_on' => $sale->paid_on
            ])}}"><i class="fa fa-print" aria-hidden="true"></i> @lang("messages.print")</a>
            <button type="button" class="btn btn-default no-print" data-dismiss="modal">@lang( 'messages.close' )</button>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        var element = $('div.modal-xl');
        __currency_convert_recursively(element);
    });
</script>
