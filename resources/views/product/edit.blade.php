@extends('layouts.master')
@section('title')
    {{ trans_choice('general.add',1) }} {{ trans_choice('general.product',1) }}
@endsection

@section('content')
    <div class="panel panel-white">
        <div class="panel-heading">
            <h6 class="panel-title">{{ trans_choice('general.add',1) }} {{ trans_choice('general.product',1) }}</h6>

            <div class="heading-elements">

            </div>
        </div>
        {!! Form::open(array('url' => url('product/'.$product->id.'/update'), 'method' => 'post','class'=>'', 'name' => 'form',"enctype"=>"multipart/form-data")) !!}
        <div class="panel-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        {!! Form::label('name',trans_choice('general.name',1),array('class'=>'')) !!}
                        {!! Form::text('name',$product->name, array('class' => 'form-control', 'placeholder'=>"",'required'=>'required')) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('code',trans_choice('general.code',1),array('class'=>'')) !!}
                        {!! Form::text('code',$product->code, array('class' => 'form-control', 'placeholder'=>"",''=>'')) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('cost_price',trans_choice('general.cost_price',1),array('class'=>'')) !!}
                        {!! Form::text('cost_price',$product->cost_price, array('class' => 'form-control touchspin', 'placeholder'=>"",'required'=>'required')) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('selling_price',trans_choice('general.selling_price',1),array('class'=>'')) !!}
                        {!! Form::text('selling_price',$product->selling_price, array('class' => 'form-control touchspin', 'placeholder'=>"",''=>'')) !!}
                    </div>
                    <div class="form-group" id="qtyDiv">
                        {!! Form::label('qty',trans_choice('general.qty',1),array('class'=>'')) !!}
                        {!! Form::number('qty',$product->qty, array('class' => 'form-control', 'placeholder'=>"",'id'=>'qty')) !!}
                    </div>
                    <div class="form-group" id="qtyDv">
                        {!! Form::label('alert_qty',trans_choice('general.alert',1).' '.trans_choice('general.qty',1),array('class'=>'')) !!}
                        {!! Form::number('alert_qty',$product->alert_qty, array('class' => 'form-control', 'placeholder'=>"",'id'=>'alert_qty')) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('notes',trans_choice('general.description',2),array('class'=>'')) !!}
                        {!! Form::textarea('notes',$product->notes, array('class' => 'form-control tinymce','rows'=>'3')) !!}
                    </div>

                    <div class="form-group">
                        <hr>
                    </div>
                    <p class="bg-navy disabled color-palette">{{trans_choice('general.custom_field',2)}}</p>
                    @foreach($custom_fields as $key)

                        <div class="form-group">
                            {!! Form::label($key->id,$key->name,array('class'=>'')) !!}
                            @if($key->field_type=="number")
                                <input type="number" class="form-control" name="{{$key->id}}"
                                       @if($key->required==1) required
                                       @endif value="@if(!empty(\App\Models\CustomFieldMeta::where('custom_field_id',$key->id)->where('parent_id',$product->id)->where('category','products')->first())){{\App\Models\CustomFieldMeta::where('custom_field_id',$key->id)->where('parent_id',$product->id)->where('category','products')->first()->name}} @endif">
                            @endif
                            @if($key->field_type=="textfield")
                                <input type="text" class="form-control" name="{{$key->id}}"
                                       @if($key->required==1) required
                                       @endif value="@if(!empty(\App\Models\CustomFieldMeta::where('custom_field_id',$key->id)->where('parent_id',$product->id)->where('category','products')->first())){{\App\Models\CustomFieldMeta::where('custom_field_id',$key->id)->where('parent_id',$product->id)->where('category','products')->first()->name}} @endif">
                            @endif
                            @if($key->field_type=="date")
                                <input type="text" class="form-control date-picker" name="{{$key->id}}"
                                       @if($key->required==1) required
                                       @endif value="@if(!empty(\App\Models\CustomFieldMeta::where('custom_field_id',$key->id)->where('parent_id',$product->id)->where('category','products')->first())){{\App\Models\CustomFieldMeta::where('custom_field_id',$key->id)->where('parent_id',$product->id)->where('category','products')->first()->name}} @endif">
                            @endif
                            @if($key->field_type=="textarea")
                                <textarea class="form-control" name="{{$key->id}}"
                                          @if($key->required==1) required @endif>@if(!empty(\App\Models\CustomFieldMeta::where('custom_field_id',$key->id)->where('parent_id',$product->id)->where('category','products')->first())){{\App\Models\CustomFieldMeta::where('custom_field_id',$key->id)->where('parent_id',$product->id)->where('category','products')->first()->name}} @endif</textarea>
                            @endif
                            @if($key->field_type=="decimal")
                                <input type="text" class="form-control touchspin" name="{{$key->id}}"
                                       @if($key->required==1) required
                                       @endif value="@if(!empty(\App\Models\CustomFieldMeta::where('custom_field_id',$key->id)->where('parent_id',$product->id)->where('category','products')->first())){{\App\Models\CustomFieldMeta::where('custom_field_id',$key->id)->where('parent_id',$product->id)->where('category','products')->first()->name}} @endif">
                            @endif
                            @if($key->field_type=="select")
                                <select class="form-control touchspin" name="{{$key->id}}"
                                        @if($key->required==1) required @endif>
                                    @if($key->required!=1)
                                        <option value=""></option>
                                    @else
                                        <option value="" disabled selected>Select...</option>
                                    @endif
                                    @foreach(explode(',',$key->select_values) as $v)
                                        @if(!empty(\App\Models\CustomFieldMeta::where('custom_field_id',$key->id)->where('parent_id',$product->id)->where('category','products')->first()))
                                            @if(\App\Models\CustomFieldMeta::where('custom_field_id',$key->id)->where('parent_id',$product->id)->where('category','products')->first()->name==$v)
                                                <option selected>{{$v}}</option>
                                            @else
                                                <option>{{$v}}</option>
                                            @endif
                                        @else
                                            <option>{{$v}}</option>
                                        @endif

                                    @endforeach
                                </select>
                            @endif
                            @if($key->field_type=="radiobox")
                                @foreach(explode(',',$key->radio_box_values) as $v)
                                    <div class="radio">
                                        <label>
                                            @if(!empty(\App\Models\CustomFieldMeta::where('custom_field_id',$key->id)->where('parent_id',$product->id)->where('category','products')->first()))
                                                @if(\App\Models\CustomFieldMeta::where('custom_field_id',$key->id)->where('parent_id',$product->id)->where('category','products')->first()->name==$v)
                                                    <input type="radio" name="{{$key->id}}" id="{{$key->id}}"
                                                           value="{{$v}}"
                                                           @if($key->required==1) required @endif checked>
                                                @else
                                                    <input type="radio" name="{{$key->id}}" id="{{$key->id}}"
                                                           value="{{$v}}"
                                                           @if($key->required==1) required @endif>
                                                @endif
                                            @else
                                                <input type="radio" name="{{$key->id}}" id="{{$key->id}}" value="{{$v}}"
                                                       @if($key->required==1) required @endif>
                                            @endif

                                            <b>{{$v}}</b>
                                        </label>
                                    </div>
                                @endforeach
                            @endif
                            @if($key->field_type=="checkbox")
                                @if(!empty(\App\Models\CustomFieldMeta::where('custom_field_id',$key->id)->where('parent_id',$product->id)->where('category','products')->first()))
                                    <?php $c = unserialize(\App\Models\CustomFieldMeta::where('custom_field_id',
                                        $key->id)->where('parent_id', $product->id)->where('category',
                                        'products')->first()->name); ?>

                                    @foreach(explode(',',$key->checkbox_values) as $v)
                                        <div class="checkbox">
                                            <label>
                                                @if(array_key_exists($v,$c))
                                                    @if($c[$v]==$v)
                                                        <input type="checkbox" name="{{$key->id}}[{{$v}}]"
                                                               id="{{$key->id}}"
                                                               value="{{$v}}"
                                                               @if($key->required==1) required @endif checked>
                                                    @else
                                                        <input type="checkbox" name="{{$key->id}}[{{$v}}]"
                                                               id="{{$key->id}}"
                                                               value="{{$v}}"
                                                               @if($key->required==1) required @endif>
                                                    @endif
                                                @else
                                                    <input type="checkbox" name="{{$key->id}}[{{$v}}]" id="{{$key->id}}"
                                                           value="{{$v}}"
                                                           @if($key->required==1) required @endif>
                                                @endif
                                                <b>{{$v}}</b>
                                            </label>
                                        </div>
                                    @endforeach
                                @else
                                    @foreach(explode(',',$key->checkbox_values) as $v)
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="{{$key->id}}[{{$v}}]" id="{{$key->id}}"
                                                       value="{{$v}}"
                                                       @if($key->required==1) required @endif>
                                                <b>{{$v}}</b>
                                            </label>
                                        </div>
                                    @endforeach
                                @endif
                            @endif

                        </div>
                    @endforeach
                    <p style="text-align:center; font-weight:bold;">
                        <small><a href="{{url('custom_field/create')}}" target="_blank">Click here to add custom fields
                                on
                                this page</a></small>
                    </p>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('categories',trans_choice('general.category',2),array('class'=>' control-label')) !!}
                        <select class="form-control select2" name="categories[]" id="categories" multiple>
                            {{\App\Helpers\GeneralHelper::printTree($tree)}}
                        </select>
                    </div>
                    <div class="form-group">
                        @if(!empty($product->picture))
                            <a class="fancybox" rel="group"
                               href="{{ url(asset('uploads/'.$product->picture)) }}"> <img
                                        src="{{ url(asset('uploads/'.$product->picture)) }}"
                                        style="max-width: 40px!important;"/></a><br>
                        @endif
                        {!! Form::label('picture',trans_choice('general.picture',2),array('class'=>'')) !!}
                        {!! Form::file('picture', array('class' => 'form-control')) !!}

                    </div>
                </div>
            </div>


        </div>

        <div class="panel-footer">
            <button type="submit" class="btn btn-primary  pull-right" name="save_return"
                    value="save_return">{{ trans_choice('general.save',1) }}
            </button>

        </div>
    {!! Form::close() !!}
    <!-- /.panel-body -->
    </div>
    <!-- /.box -->
    @foreach($product->categories as $category)
        @if(!empty($category->category))
            <script>
                $('#categories option[value="{{$category->category->id}}"]').attr('selected', true)
            </script>
        @endif
    @endforeach
@endsection
@section('footer-scripts')
    <script>

        $(document).ready(function (e) {
            if ($('#enable_stock_management').val() == 1) {
                $('#qty').attr('required', 'required')
                $('#qtyDiv').show();
            } else {
                $('#qty').removeAttr('required')
                $('#qtyDiv').hide();
            }
            $('#enable_stock_management').change(function (e) {
                if ($('#enable_stock_management').val() == 1) {
                    $('#qty').attr('required', 'required')
                    $('#qtyDiv').show();
                } else {
                    $('#qty').removeAttr('required')
                    $('#qtyDiv').hide();
                }
            })
        })

    </script>
    <script>
        function delete_file(e) {
            var id = e.id;
            swal({
                title: 'Are you sure?',
                text: '',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ok',
                cancelButtonText: 'Cancel'
            }).then(function () {
                $.ajax({
                    type: 'GET',
                    url: "{!!  url('product/'.$product->id) !!}/delete_file?id=" + id,
                    success: function (data) {
                        $("#file_" + id + "_span").remove();
                        swal({
                            title: 'Deleted',
                            text: 'File successfully deleted',
                            type: 'success',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Ok',
                            timer: 2000
                        })
                    }
                });
            })

        }
    </script>
@endsection

