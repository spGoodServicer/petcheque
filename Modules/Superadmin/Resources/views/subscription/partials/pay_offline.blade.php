<div class="col-md-12">
	<form class="form-pay-offline" action="{{action('\Modules\Superadmin\Http\Controllers\SubscriptionController@confirm', [$package->id])}}" method="POST">
	 	{{ csrf_field() }}
	 	<input type="hidden" name="gateway" value="{{$k}}">
	 	<input type="hidden" name="custom_price" value="@if(!empty($custom_price)){{$custom_price}}@else{{$package->price}}@endif">
	 	<input type="hidden" name="option_variables_selected" value="@if(!empty($option_variables_selected)){{$option_variables_selected}}@endif">
	 	<input type="hidden" name="module_selected" value="@if(!empty($module_selected)){{$module_selected}}@endif">

	 	<button type="button" class="btn btn-success btn-pay-offline"> <i class="fa fa-hand-grab-o"></i> {{$v}}</button>
	</form>
	<p class="help-block">@lang('superadmin::lang.offline_pay_helptext')</p>

	<div class="modal" tabindex="-1" role="dialog" id="pay_offline_details_modal">
	    <div class="modal-dialog" role="document" style="width: 40% !important;">
	        <div class="modal-content">
	            <div class="modal-body text-center">
					<table class="table table-condensed">
				        <tbody>
				            <tr>
				                <td>
				                    <strong>@lang('superadmin::lang.bank_name')</strong>
				                </td>
				                <td>
				                    {{env('PAY_ONLINE_BANK_NAME')}}
				                </td>
				            </tr>
				            <tr>
				                <td>
				                    <strong>@lang('superadmin::lang.branch_name')</strong>
				                </td>
				                <td>
				                    {{env('PAY_ONLINE_BRANCH_NAME')}}
				                </td>
				            </tr>
				            <tr>
				                <td>
				                    <strong>@lang('superadmin::lang.account_no')</strong>
				                </td>
				                <td>
				                    {{env('PAY_ONLINE_ACCOUNT_NO')}}
				                </td>
				            </tr>
				            <tr>
				                <td>
				                    <strong>@lang('superadmin::lang.account_name')</strong>
				                </td>
				                <td>
				                    {{env('PAY_ONLINE_ACCOUNT_NAME')}}
				                </td>
				            </tr>
				            <tr>
				                <td>
				                    <strong>@lang('superadmin::lang.swift_code')</strong>
				                </td>
				                <td>
				                    {{env('PAY_ONLINE_SWIFT_CODE')}}
				                </td>
				            </tr>
				        </tbody>
				    </table>
	            </div>
	            <div class="modal-footer">
	                <button type="button" class="btn btn-secondary btn-confirm-ok" data-dismiss="modal">Ok</button>
	            </div>
	        </div>
	    </div>
	</div>
</div>
