<div class="col-md-12">
	<form class="form-pay-online" action="{{action('\Modules\Superadmin\Http\Controllers\SubscriptionController@confirm', [$package->id])}}" method="POST">
	 	{{ csrf_field() }}
	 	<input type="hidden" name="gateway" value="{{$k}}">
	 	<input type="hidden" name="custom_price" value="@if(!empty($custom_price)){{$custom_price}}@else{{$package->price}}@endif">
	 	<input type="hidden" name="option_variables_selected" value="@if(!empty($option_variables_selected)){{$option_variables_selected}}@endif">
	 	<input type="hidden" name="module_selected" value="@if(!empty($module_selected)){{$module_selected}}@endif">

	 	<button type="button" class="btn btn-success btn-pay-online">{{$v}}</button>
	</form>

	<div class="modal" tabindex="-1" role="dialog" id="pay_online_details_modal">
	    <div class="modal-dialog" role="document" style="width: 40% !important;">
	        <div class="modal-content">
	            <div class="modal-body text-center">
					{{env('PAY_ONLINE_STARTING_NO')}}
	            </div>
	            <div class="modal-footer">
	                <button type="button" class="btn btn-secondary btn-confirm-ok" data-dismiss="modal">Ok</button>
	            </div>
	        </div>
	    </div>
	</div>
</div>
