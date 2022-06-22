@extends($layout)

@section('title', __('superadmin::lang.subscription'))

@section('content')

<!-- Main content -->
<section class="content">

	@include('superadmin::layouts.partials.currency')
@php
	$currency_symbol = App\Currency::where('id', $package->currency_id)->first();
@endphp
	<div class="box box-success">
        <div class="box-header">
            <h3 class="box-title">@lang('superadmin::lang.pay_and_subscribe')</h3>
        </div>

        <div class="box-body">
    		<div class="col-md-8">
        		<h3>
        			{{$package->name}}

        			(<span class="" >{{!empty($currency_symbol)? $currency_symbol->symbol : '' }} @if(!empty($custom_price)) {{number_format($custom_price,2)}} @else{{number_format($package->price, 2)}}@endif</span>

					<small>
						/ {{$package->interval_count}} {{ucfirst($package->interval)}}
					</small>)
        		</h3>
        		<ul>
					@if(!request()->session()->get('business.is_patient'))
					<li>
						@if($package->location_count == 0)
							@lang('superadmin::lang.unlimited')
						@else
							@if(!empty($options['locations'])){{$options['locations']}} @else {{$package->location_count}} @endif
						@endif

						@lang('business.business_locations')
					</li>
					@endif

					@if($package->visitors_registration_module == 0)
					<li>
						@if($package->user_count == 0)
							@lang('superadmin::lang.unlimited')
						@else
						@if(!empty($options['users'])){{$options['users']}} @else {{$package->user_count}} @endif
						@endif
						@if(request()->session()->get('business.is_patient'))
						@lang('superadmin::lang.members')
						@else
						@lang('superadmin::lang.users')
						@endif
					</li>

					@if(!request()->session()->get('business.is_patient'))
					<li>
						@if($package->product_count == 0)
							@lang('superadmin::lang.unlimited')
						@else
						@if(!empty($options['products'])){{$options['products']}} @else {{$package->product_count}} @endif
						@endif

						@lang('superadmin::lang.products')
					</li>
					@endif

					@if(!request()->session()->get('business.is_patient'))
					<li>
						@if($package->invoice_count == 0)
							@lang('superadmin::lang.unlimited')
						@else
							{{$package->invoice_count}}
						@endif

						@lang('superadmin::lang.invoices')
					</li>
					@endif
					@endif

					@if($package->trial_days != 0)
						<li>
							{{$package->trial_days}} @lang('superadmin::lang.trial_days')
						</li>
					@endif
				</ul>

				<ul class="list-group">
					@foreach($gateways as $k => $v)
						<div class="list-group-item">
							<b>@lang('superadmin::lang.pay_via', ['method' => $v])</b>

							<div class="row" id="paymentdiv_{{$k}}">
								@php
									$view = 'superadmin::subscription.partials.pay_'.$k;
								@endphp
								@includeIf($view)
							</div>
						</div>
					@endforeach
				</ul>
			</div>
        </div>
    </div>
</section>
@endsection

@section('javascript')
<script>
	$('.btn-pay-online').click(function (e) {
		$('#pay_online_details_modal').modal('show');
	});
	$('.btn-confirm-ok').click(function (e) {
		$('form.form-pay-online').submit();
	});

	$('.btn-pay-offline').click(function (e) {
		$('#pay_offline_details_modal').modal('show');
	});
	$('.btn-confirm-ok').click(function (e) {
		$('form.form-pay-offline').submit();
	});
</script>
@endsection
