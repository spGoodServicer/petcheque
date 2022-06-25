<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open(['url' => action('AccountController@store'), 'method' => 'post', 'id' => 'payment_account_form'
        ]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang( 'account.add_account' )</h4>
        </div>

        <div class="modal-body">
            <div class="form-group">
                {!! Form::label('name', __( 'lang_v1.name' ) .":*") !!}
                {!! Form::text('name', null, ['class' => 'form-control', 'required','placeholder' => __( 'lang_v1.name'
                ) ]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('account_type_id', __( 'account.account_type' ) .":") !!}
                <select name="account_type_id" class="form-control select2" id="account_type_id" required>
                    <option>@lang('messages.please_select')</option>
                    @foreach($account_types as $account_type)
                    <optgroup label="{{$account_type->name}}">
                        <option value="{{$account_type->id}}">{{$account_type->name}}</option>
                        @foreach($account_type->sub_types as $sub_type)
                        <option value="{{$sub_type->id}}">{{$sub_type->name}}</option>
                        @endforeach
                    </optgroup>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('is_main_account', 1, false, ['class' => 'input-icheck check_main_or_sub', 'id' => 'is_main_account']); !!}
                        {{__('lang_v1.main_account_dsc')}}
                    </label>
                </div>
            </div>

            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('sub_type', 1, false, ['class' => 'input-icheck check_main_or_sub', 'id' => 'sub_type']); !!}
                        {{__('lang_v1.sub_type')}}
                    </label>
                </div>
            </div>
            <div class="form-group show_in_balance_sheet  hide">
                {!! Form::label('show_in_balance_sheet', __( 'lang_v1.show_in_bal_sheet' ) .":") !!}
                {!! Form::select('show_in_balance_sheet', ['1'=>'Yes','0'=>'No'], 1, ['placeholder' => 
                __('messages.please_select'), 'style' => 'width: 100%', 'class' => 'form-control select2','required', 'data-minimum-results-for-search'=>'3' ]) !!}
            </div>
            <div class="form-group parent_account hide">
                {!! Form::label('parent_account_id', __( 'lang_v1.parent_account' ) .":") !!}
                {!! Form::select('parent_account_id', $parentAccounts, null, ['placeholder' =>
                __('messages.please_select'), 'style' => 'width: 100%', 'class' => 'form-control select2', 'data-minimum-results-for-search'=>'1' ]) !!}
            </div>
  

            <div class="form-group asset_type">
                {!! Form::label('asset_type', __( 'account.account_group' ) .":") !!}
                {!! Form::select('asset_type', $account_groups, null, ['placeholder' =>
                __('messages.please_select'), 'class' => 'form-control']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('account_number', __( 'account.account_number' ) .":*") !!}
                {!! Form::text('account_number', null, ['class' => 'form-control', 'required','placeholder' => __(
                'account.account_number' ) ]); !!}
            </div>
            <div id="part_need_cheque" class="form-group form-md-radios" style="display: none">
                {!! Form::label('show_cheque', __( 'lang_v1.show_cheque' ) .":") !!}
                <div class="md-radio-list">
                  <div class="md-radio">
                    <input type="radio" id="radio1" name="is_need_cheque" value="Y"/>
                    <label for="radio1">
                    <span></span>
                    <span class="check"></span>
                    <span class="box"></span>
                    Yes </label>
                  </div>
                  <div class="md-radio">
                    <input type="radio" id="radio2" name="is_need_cheque" checked value="N"/>
                    <label for="radio2">
                    <span></span>
                    <span class="check"></span>
                    <span class="box"></span>
                    No </label>
                  </div>
                </div>
              </div>
            <div class="form-group opening_balance_div hide">
                {!! Form::label('opening_balance', __( 'account.opening_balance' ) .":") !!}
                {!! Form::text('opening_balance', 0, ['class' => 'form-control input_number','placeholder' => __(
                'account.opening_balance' ) ]); !!}
            </div>


            <div class="form-group">
                {!! Form::label('note', __( 'brand.note' )) !!}
                {!! Form::textarea('note', null, ['class' => 'form-control', 'placeholder' => __( 'brand.note' ), 'rows'
                => 4]); !!}
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
        </div>

        {!! Form::close() !!}

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script>
    function showHideCheque(){
        if(  $('#asset_type option:selected').data('show-cheque') == 'Y'){
            $('#part_need_cheque').show();
        }else{
            $('#part_need_cheque').hide();
        }
    }  
    $(document).ready(function(){

        $('#account_type_id').trigger('change');
        showHideCheque();
        $('#account_type_id').change(function(){
        showHideCheque();
        });
        $('#asset_type').change(function(){
        showHideCheque()
        });
    })
    $('#is_main_account').change(function(){
        if($(this).prop('checked') == true){
            $('.opening_balance_div').addClass('hide');
            $('.parent_account').addClass('hide');
            $('.show_in_balance_sheet').removeClass('hide');           
        }else{
            $('.opening_balance_div').removeClass('hide');
            $('.parent_account').removeClass('hide');
            $('.show_in_balance_sheet').addClass('hide');
        }
    });
    $('#sub_type').change(function(){
        if($(this).prop('checked') == true){
            $('.opening_balance_div').removeClass('hide');
            $('.parent_account').removeClass('hide');
            $('.show_in_balance_sheet').addClass('hide');
          
        }else{
            $('.opening_balance_div').addClass('hide');
            $('.parent_account').addClass('hide');
            $('.show_in_balance_sheet').removeClass('hide');
        }
    });

    $('#account_type_id').change(function(){
        account_type_id = $(this).val();
        $.ajax({
            method: 'get',
            'content-Type' : 'html',
            url: '/get-account-groups/'+account_type_id,
            data: {  },
            success: function(result) {
                $('#asset_type').empty();
                $('#asset_type').append(result);
            },
        });
        $.ajax({
            method: 'get',
            'content-Type' : 'html',
            url: '/accounting-module/get-parent-account-by-type/'+account_type_id,
            data: {  },
            success: function(result) {
                $('#parent_account_id').empty();
                $('#parent_account_id').append(result);
            },
        });
    });

    $('#payment_account_form').validate();

    $('.check_main_or_sub').change(function(){
        id = $(this).attr('id');
        console.log(id);
        $('.check_main_or_sub').not(this).prop('checked', false);  
    })
</script>