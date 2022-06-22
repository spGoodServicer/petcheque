@extends('layouts.app')
@section('title', __('Product stock history'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('Product stock history')</h1>
</section>
<!-- Main content -->
<section class="content">
<div class="row">
    <div class="col-md-12">
    @component('components.widget', ['title' => $product->name])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('location_id',  __('purchase.business_location') . ':') !!}
                {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('store_id',  __('Store') . ':') !!}
                <select class="select2 form-control store-id" name="store_id" style="width:100%">
                    @foreach($allStores as $store)
                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @if($product->type == 'variable')
            <div class="col-md-3">
                <div class="form-group">
                    <label for="variation_id">@lang('product.variations'):</label>
                    <select class="select2 form-control" name="variation_id" id="variation_id">
                        @foreach($product->variations as $variation)
                            <option value="{{$variation->id}}">{{$variation->product_variation->name}} - {{$variation->name}} ({{$variation->sub_sku}})</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @else
            <input type="hidden" id="variation_id" name="variation_id" value="{{$product->variations->first()->id}}">
        @endif
    @endcomponent
    @component('components.widget')
        <div id="product_stock_history" style="display: none;"></div>
    @endcomponent
    </div>
</div>

</section>
<!-- /.content -->
@endsection

@section('javascript')
   <script type="text/javascript">
        $(document).ready( function(){
            load_stock_history($('#variation_id').val(), $('#location_id').val());
            changeStore($('#location_id').val());
        });

       function load_stock_history(variation_id, location_id) {
            $('#product_stock_history').fadeOut();
            $.ajax({
                url: '/products/stock-history/' + variation_id + "?location_id=" + location_id + "&store_id=" + $('.store-id').val(),
                dataType: 'html',
                success: function(result) {
                    $('#product_stock_history')
                        .html(result)
                        .fadeIn();

                    __currency_convert_recursively($('#product_stock_history'));

                    $('#stock_history_table').DataTable({
                        searching: false,
                        ordering: false
                    });
                },
            });
       }
       
       function changeStore(location_id)
       {
           $.ajax({
                url: '/products/stock-history/get-stores/' + location_id,
                dataType: 'json',
                success: function(result) {
                    if(result.success == 1){
                        $('.store-id').empty();
                        result.data.map(function (v, i){
                            $('.store-id').append(`
                                <option value="${v.id}">${v.name}</option>
                            `); 
                        });
                    }
                },
            });
       }

       $(document).on('change', '#variation_id, #location_id, .store-id', function(){
            load_stock_history($('#variation_id').val(), $('#location_id').val());
            // changeStore($('#location_id').val());
       });
   </script>
@endsection