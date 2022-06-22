@extends('layouts.app')
@section('title','Default Setting')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Default Settings</h1>
</section>

<!-- Main content -->
<section class="content-wrapper">
    
  @if (session('notification') || !empty($notification))
    <div class="row">
        <div class="col-sm-12">
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                @if(!empty($notification['msg']))
                    {{$notification['msg']}}
                @elseif(session('notification.msg'))
                    {{ session('notification.msg') }}
                @endif
              </div>
          </div>  
      </div>     
  @endif

  <div class="row" style='margin-right:25px'>
    <div class="modal-dialog-lg" role="document">
        <div class="modal-content">
    
            {!! Form::open(['url' => url('update_setting'), 'method' => 'post', 'id' => 'update__form'
            ]) !!}
    
            <div class="modal-body">
                @if(isset($settings))
                    <input type="hidden" name="id" value="{{$settings->id}}">
                	<div class="col-md-4">
                    	<div class="form-group">
							<label>Currency</label>
							 {!! Form::select('def_currency',$currencies, $settings->def_currency, ['placeholder' =>
                               __('messages.please_select'), 'style' => 'width: 100%', 'class' => 'form-control select2']) !!}
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Stamp</label>
							 {!! Form::select('def_stamp',$stamps, $settings->def_stamp, ['placeholder' =>
                               __('messages.please_select'), 'style' => 'width: 100%', 'class' => 'form-control select2']) !!}
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Cheque template</label>
						     {!! Form::select('def_cheque_templete',$templates,  $settings->def_cheque_templete, ['placeholder' =>
                               __('messages.please_select'), 'style' => 'width: 100%', 'class' => 'form-control select2']) !!}
						</div>
					</div>
					
						
					<div class="col-md-4">
						<div class="form-group">
							<label>Bank account</label>
					    	{!! Form::select('def_bank_account',$accounts, $settings->def_bank_account, ['placeholder' =>
                               __('messages.please_select'), 'style' => 'width: 100%', 'class' => 'form-control select2']) !!}
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Default font</label>
							<input type="text" name="def_font" value="{{$settings->def_font}}" class="form-control" placeholder="Default font" maxlength="499" />
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Default font size</label>
							<input type="text" name="def_font_size" value="{{$settings->def_font_size}}" class="form-control" placeholder="Default font size" maxlength="499" />
						</div>
					</div>
						
					<div class="row" style="margin-top: 20px;">
						<div class="col-md-4">
							<div class="form-group">
								<button class="btn btn-primary">Update Default Setting</button>
							</div>
						</div>
						<div class="col-md-4"></div>
						<div class="col-md-4"></div>
					</div>
            </div>
            @else
            	<div class="col-md-4">
                    	<div class="form-group">
							<label>Currency</label>
							 {!! Form::select('def_currency',$currencies, null, ['placeholder' =>
                               __('messages.please_select'), 'style' => 'width: 100%', 'class' => 'form-control select2']) !!}
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Stamp</label>
							 {!! Form::select('def_stamp',$stamps, null, ['placeholder' =>
                               __('messages.please_select'), 'style' => 'width: 100%', 'class' => 'form-control select2']) !!}
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Cheque template</label>
						     {!! Form::select('def_cheque_templete',$templates, null, ['placeholder' =>
                               __('messages.please_select'), 'style' => 'width: 100%', 'class' => 'form-control select2']) !!}
						</div>
					</div>
					
						
					<div class="col-md-4">
						<div class="form-group">
							<label>Bank account</label>
					    	{!! Form::select('def_bank_account',$accounts, null, ['placeholder' =>
                               __('messages.please_select'), 'style' => 'width: 100%', 'class' => 'form-control select2']) !!}
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Default font</label>
							<input type="text" name="def_font" class="form-control" placeholder="Default font" maxlength="499" />
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Default font size</label>
							<input type="text" name="def_font_size" class="form-control" placeholder="Default font size" maxlength="499" />
						</div>
					</div>
						
					<div class="row" style="margin-top: 20px;">
						<div class="col-md-4">
							<div class="form-group">
								<button class="btn btn-primary">Insert Default Setting</button>
							</div>
						</div>
						<div class="col-md-4"></div>
						<div class="col-md-4"></div>
					</div>
              </div>
            @endif
            
          
            {!! Form::close() !!}
      </div>
       </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->

  </div>
</section>
@endsection