<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
        <h4 class="modal-title">Add Bank Account</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class='row'>
                <div class='col-md-5'>
                    {{ Form::hidden('temp_id', $temp_id,array('id' => 'temp_id')) }}
                    <label>@lang('account.date')</label>
                    <div class="input-group date" data-provide="datepicker" data-date-format="yyyy-mm-dd">
                        <input type="text" id="regDate" value="{{date('Y-m-d')}}" class="form-control">
                        <div class="input-group-addon">
                            <span class="glyphicon glyphicon-th"></span>
                        </div>
                    </div>
                </div>
                <div class='col-md-5'>
                    <label>@lang('account.account_name')</label>
                    <select id="account_id" class="form-control">
                        <option value="">None</option>
                        @foreach($get_bankacount as $bankacount)
                        <option value="{{ $bankacount['id'] }}">
                        {{ $bankacount['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class='col-md-2 mt-10'>
                    <button class="btn btn-primary mt-15" id="addBankBtn"><i class='fa fa-plus'></i> Add</button>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-1'></div>
                <div class='col-md-10'>
                    <table class="table">
                        <thead>
                            <th>No</th>
                            <th>@lang('account.date')</th>
                            <th>@lang('account.account_name')</th>
                            <th>@lang('account.action')</th>
                        </thead>
                        <tbody id="linkBankTbody">
                            @foreach($getLinkBankAcounts as $row)
                            <tr>
                                <td>{{$loop->index+1}}</td>
                                <td>{{$row->cheque_temp_regdate}}</td>
                                <td>{{$row->account->name}}</td>
                                <td><button data-url={{url('get-templates-delete-bank', [$row->id])}} class='btn btn-sm btn-danger delLinkBank'><i class='fa fa-trash'></button></td>
                                
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class='col-md-1'></div>
            </div>
        </div>
        <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
        function reloadLinkBank(data){
            $("#linkBankTbody").empty();
            $("#linkBankTbody").html(data);
        }
        $('#addBankBtn').on('click',function(){
            if($('#account_id').val()==""){
                toastr.error('Please check Account Name');
                return false;
            }
            $.ajax({
                method: 'post',
                url: '/get-templates-add-bank',
                data: { tempId:$('#temp_id').val(), regDate:$('#regDate').val(),accountId:$('#account_id').val() },
                success: function(result) {
                    toastr.success('success');
                    reloadLinkBank(result);
                },
            });
        });
        $('#linkBankTbody').on('click', '.delLinkBank', function() {
            // code here
            swal({
                title: LANG.sure,
                text: 'This template will be deleted.',
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(willDelete => {
                if (willDelete) {
                    var url = $(this).data('url');
                    var data = $(this).serialize();
    
                    $.ajax({
                        method: 'DELETE',
                        url: url,
                        dataType : 'html',
                        data: data,
                        success: function(result) {
                            toastr.success('success');
                            reloadLinkBank(result);
                        },
                    });
                }
            });
         });
        
    });
</script>