@extends('layouts.app')
@section('title', __('cheque.templates'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>List Cancelled Cheques</h1>
</section>

<!-- Main content -->
<section class="content">
  @component('components.filters', ['title' => __('report.filters')])
  {!! Form::open(['method' => 'post','id'=>'filterForm']) !!}
    <div class="row">
        <div class="col-md-4">
              <div class="form-group">
                  {!! Form::label('filter_account_number', __('Account Number') . ':') !!}
                  {!! Form::select('filter_account_number', $accounts, $defaultVal['filter_account_number'], ['placeholder' =>
                  __('account.account_name'),'class' => 'form-control select2 filter','style' => 'width:100%']); !!}
              </div>
          </div>
          <div class="col-sm-4">
              <div class="form-group">
                  {!! Form::label('filter_cheque_number', __('Cheque Number').':') !!}
                  {!! Form::select('filter_cheque_number', $chequenolists, $defaultVal['filter_cheque_number'], ['placeholder' =>
                  __('cheque.cheque_number'),'class' => 'form-control select2 filter']); !!}
              </div>
          </div>
          <div class="col-md-4">
              <div class="form-group">
                  {!! Form::label('filter_date_range', __('report.date_range') . ':') !!}
                  {!! Form::text('filter_date_range',($defaultVal)? $defaultVal['startDate'].' - '.$defaultVal['endDate']: @date('m/01/Y').' - '.@date('m/t/Y') , ['placeholder' => __('lang_v1.select_a_date_range'), 'class' =>'form-control filter', 'id' => 'date_range', 'readonly']); !!}
              </div>
          </div>
    </div>
    {!! Form::close() !!}
   @endcomponent
    <div class="row">
      <div class="col-md-12">
        <div class="box">
          <div class="box-header">
            <a  class="btn btn-primary pull-right" data-toggle="modal" data-target="#myModal"
            href="#" onclick="return false;" >
            <i class="fa fa-plus"></i> Cancel Cheque</a>
          </div>
          <div class="box-body">
            <div class="table-responsive">
              <table class="table table-bordered table-striped" id="templates_table">
                  <thead>
                      <tr>
                          <th>Date & Time</th>
                          <th>Account Number</th>
                          <th>Cheque No</th>
                          <th>User</th>
                          <th>Note</th>
                      </tr>
                  </thead>
                  <tbody>
                      @foreach($deletedcheque as $data)
                          <tr>
                              <td>{{$data->reg_datetime}}</td>
                              <td>{{$data->account->account_number}}</td>
                              <td>{{$data->cheque_no}}</td>
                              <td>{{$data->username}}</td>
                              <td>{{$data->note}}</td>
                          </tr>
                      @endforeach
                  </tbody>
              </table>
          </div>
          </div>
        </div>
      </div>
    </div>
    

    
</section>
<div class="modal" tabindex="-1" role="dialog" id="myModal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Cancel Cheque</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      {!! Form::open(['url' => url('add_deleted_cheque'), 'method' => 'post', 'id' => 'payee_form' ]) !!}
        <div class="row">
					<div class="col-md-4">
						<div class="form-group">
							<label>Account Number</label>
								{!! Form::select('account_no',$accounts, null, ['placeholder' =>
                               __('messages.please_select'), 'style' => 'width: 100%','id'=>'account_no', 'class' => 'form-control select2 databind']) !!}
						</div>
					</div>
          <div class="col-md-4">
            <div class="form-group">
							<label>Cheque Status</label>
								<select class="form-control databind" id="chequeStatus">
                  <option value=""> @lang('messages.please_select') </option>
                  <option value="printed">Printed Cheques</option>
                  <option value="notprinted">Not Printed Cheques</option>
                </select>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Cheque Number</label>
							<select class="form-control" id="cheque_no" name="cheque_no">
                <option value=""> @lang('messages.please_select') </option>
              </select>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div class="form-group">
							<label style="text-align: left;">note</label>
							<input type="text" id="note" name="note" class="form-control"  value="">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
					<input type="submit" name="btn" value="Submit" id="sendBtn" data-toggle="modal" onclick="saveReport()" data-target="#confirm-submit" class="btn btn-primary" />
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div><!-- Panel Body // END -->
      {!! Form::close() !!}
    </div>
  </div>
</div>
@endsection
@section('javascript')
<script>
    jQuery(document).ready(function(){
      $('input#date_range').daterangepicker(
            dateRangeSettings
        );
      $('.filter').on('change',function(){
            $('#filterForm').submit();
        });
      $('.databind').on('change',function(){
        var account_no = $("#account_no").val();
        var chequeStatus = $("#chequeStatus").val();
        if(chequeStatus=="" || account_no=='')
          return;
        $("#cheque_no option").each(function(i,e) {
            if(i>0)
              $(this).remove();
        });
        $.ajax({
            method: "get",
            url: "{{url('getBank')}}",
            dataType: "json",
            data:{account_no:account_no,chequeStatus:chequeStatus},
            success: function(data){
              $.each(data, function(i, item) {
                $('#cheque_no').append(new Option(item, item))
              });
            }
        });
      });
      $('#templates_table').DataTable({});
    });
</script>
@endsection