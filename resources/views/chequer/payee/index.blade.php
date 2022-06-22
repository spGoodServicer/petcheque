@extends('layouts.app')
@section('title', __('cheque.templates'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Payees</h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => 'Payees List'])
    <div class="row">
     {!! Form::open(['url' => url('create-payees'), 'method' => 'post', 'id' => 'payee_form' ]) !!}
       <div class="col-6">
    		<div class="col-md-4">
    			<div class="form-group">
    				<label>Enter Payee Name</label>
    				<input type="text" class="form-control"  id="payee_name" name="payee_name" placeholder="enter payee name" required>
    			</div>
    		</div>
    		<div class="col-md-2">
    			<div class="form-group" style="margin: 25px 0px 15px 0px;">
    				<button class="btn btn-primary" id="submit" name="submit">Enter</button>
    			</div>
    		</div>
    	</div>
    	  {!! Form::close() !!}
	</div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="templates_table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Payee Name</th>
                    <th>@lang('cheque.last_changed_date')</th>
                    <th>@lang('cheque.action')</th>
                </tr>
            </thead>

        </table>
    </div>

    @endcomponent
</section>


<div class="modal" tabindex="-1" role="dialog" id="myModal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Payee</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      {!! Form::open(['url' => url('create-payees'), 'method' => 'post', 'id' => 'payee_form' ]) !!}
       <div class="row">
    
       <div class="col-6">
    		<div class="col-md-6">
    			<div class="form-group">
    				<label>Enter Payee Name</label>
    				<input type="text" class="form-control"  id="editpayee_name" name="payee_name" placeholder="enter payee name" required>
    			</div>
    		</div>
    	</div>
         <input type="hidden" id='id' name='id'>
	    </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Update</button>
      </div>
      {!! Form::close() !!}
    </div>
  </div>
</div>
@endsection

@section('javascript')
<script>
    $('#location_id').change(function () {
        templates_table.ajax.reload();
    });
    //employee list
    templates_table = $('#templates_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: `{{url('payees')}}`,
            data: function (d) {
                d.location_id = $('#location_id').val();
                // d.contact_type = $('#contact_type').val();
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            
            { data: 'created_date', name: 'created_date' },
            { data: 'action', name: 'action' },
        ],
        fnDrawCallback: function (oSettings) {
          
        },
    });

    $(document).on('click', 'a.delete_payee_button', function(e) {
        e.preventDefault();
         swal({
                title: LANG.sure,
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete)=>{
                if(willDelete){
                     var url = $(this).data('url');
                     $.ajax({
                         method: "get",
                         url: url,
                         dataType: "json",
                         success: function(result){
                             if(result.success == true){
                                toastr.success(result.msg);
                                templates_table.ajax.reload();
                             }else{
                                toastr.error(result.msg);
                            }

                        }
                    });
                }
            });
    });
    $(document).on('click', 'a.edit_payee_button', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var name = $(this).data('name');
        $('#editpayee_name').val(name);
        $('#id').val(id);
    });
    $('#filter_business').select2();
</script>
@endsection