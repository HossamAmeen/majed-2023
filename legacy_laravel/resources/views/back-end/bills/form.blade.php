<div class="form-group">
    @php $input = "name"; @endphp
    <label class="col-lg-2 control-label">اسم المشتري</label>
    <div class="col-lg-2">
        <input type="text" name="{{ $input }}" value="{{ isset($row) ? $row->{$input} : '' }}" class="form-control"
            >
        @error($input)
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
    @php $input = "phone"; @endphp
    <label class="col-lg-2 control-label">رقم الموبايل</label>
    <div class="col-lg-2">
        <input type="text" name="{{ $input }}" value="{{ isset($row) ? $row->{$input} : '' }}" class="form-control">
        @error($input)
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
    @php $input = "discount"; @endphp
    <label class="col-lg-2 control-label">خصم ع الفاتوره</label>
    <div class="col-lg-2">
        <input type="text" name="{{ $input }}" value="{{ isset($row) ? $row->{$input} : '' }}" class="form-control" >
        @error($input)
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>



    <div class="form-group">
        @php $input = "products[]"; @endphp
        <label class="col-lg-2 control-label"> المنتج</label>
        <div class="col-lg-2">
            <input type="number" 
            onkeypress="return (event.charCode > 47 && event.charCode < 58)"
              name="{{ $input }}" value="{{ isset($row) ? $row->{$input} : '' }}" id="productId"
                          class="form-control bcode bcodess barcode1" onchange="getProduct(this.value)"
                >
               
        </div>
    </div>

    <div class="form-group">

        @php $input = "quantity[]"; @endphp
        <label class="col-lg-2 control-label">كمية</label>
        <div class="col-lg-2">
            <input type="text" name="{{ $input }}" value="{{ isset($row) ? $row->{$input} : 1 }}" class="form-control">
        </div>
      
        @php $input = "discounts[]"; @endphp
        <label class="col-lg-2 control-label">الخصم</label>
        <div class="col-lg-2">
            <input type="text" name="{{ $input }}" value="{{ isset($row) ? $row->{$input} : 0 }}" class="form-control">
        </div>
    </div>

                 {{-- //////////////////////////////////////////////////////////////////////////////////////////////// --}}
                 {{-- <div class="form-group">
                    @php $input = "products[]"; @endphp
                    <label class="col-lg-2 control-label"> المنتج</label>
                    <div class="col-lg-2">
                        <input type="text" name="{{ $input }}" value="{{ isset($row) ? $row->{$input} : '' }}" id="productId"
                                     onkeypress="return (event.charCode > 47 && event.charCode < 58)" class="form-control bcode barcode2"
                                     onchange="getProduct(this.value)"
                            >
                           
                    </div>
                </div>
             --}}
                {{-- <div class="form-group">
            
                    @php $input = "quantity[]"; @endphp
                    <label class="col-lg-2 control-label">كمية</label>
                    <div class="col-lg-2">
                        <input type="text" name="{{ $input }}" value="{{ isset($row) ? $row->{$input} : 1 }}" class="form-control">
                    </div>
                  
                    @php $input = "discounts[]"; @endphp
                    <label class="col-lg-2 control-label">الخصم</label>
                    <div class="col-lg-2">
                        <input type="text" name="{{ $input }}" value="{{ isset($row) ? $row->{$input} : 0 }}" class="form-control">
                    </div>
                </div> --}}

                {{-- <div class="form-group">
                    @php $input = "products[]"; @endphp
                    <label class="col-lg-2 control-label"> المنتج</label>
                    <div class="col-lg-2">
                        <input type="text" name="{{ $input }}" value="{{ isset($row) ? $row->{$input} : '' }}" id="productId"
                                     onkeypress="return (event.charCode > 47 && event.charCode < 58)" class="form-control bcode barcode3" onchange="getProduct(this.value)"
                            >
                           
                    </div>
                </div>
            
                <div class="form-group">
            
                    @php $input = "quantity[]"; @endphp
                    <label class="col-lg-2 control-label">كمية</label>
                    <div class="col-lg-2">
                        <input type="text" name="{{ $input }}" value="{{ isset($row) ? $row->{$input} : 1 }}" class="form-control">
                    </div>
                  
                    @php $input = "discounts[]"; @endphp
                    <label class="col-lg-2 control-label">الخصم</label>
                    <div class="col-lg-2">
                        <input type="text" name="{{ $input }}" value="{{ isset($row) ? $row->{$input} : 0 }}" class="form-control">
                    </div>
                </div> --}}


<div class="newRow"></div>


<div id="toggle" style="display: none">
    <div class="form-group">
        @php $input = "products[]"; @endphp
        <label class="col-lg-2 control-label"> المنتج</label>
        <div class="col-lg-2">
            <input type="text"  name="{{ $input }}"
             value="{{ isset($row) ? $row->{$input} : '' }}" class="form-control"
            onchange="getProduct(this.value)">
        </div>
    </div>

    <div class="form-group">
        @php $input = "quantity[]"; @endphp
        <label class="col-lg-2 control-label">كمية</label>
        <div class="col-lg-2">
            <input type="text" name="{{ $input }}" value="{{ isset($row) ? $row->{$input} : 1 }}" class="form-control">
        </div>
        {{-- @php $input = "costs[]"; @endphp
        <label class="col-lg-2 control-label">سعر</label>
        <div class="col-lg-2">
            <input type="text" name="{{ $input }}" value="{{ isset($row) ? $row->{$input} : '' }}" class="form-control">
        </div> --}}
        @php $input = "discounts[]"; @endphp
        <label class="col-lg-2 control-label">الخصم</label>
        <div class="col-lg-2">
            <input type="text" name="{{ $input }}" value="{{ isset($row) ? $row->{$input} : 0 }}" class="form-control">
        </div>
    </div>
</div>

@if(!isset($row))
<div class="form-group">
    <span class="col-lg-offset-2 col-lg-10" style="color: blue" id="productDetials"></span>
    <div class="col-lg-offset-2 col-lg-10">
        <input type="button" class="btn btn-info edit" value="زيادة منتج" onclick="showDiv()">
    </div>
</div>
@endif
@push('js')


@endpush
<script type="text/javascript">
      
    function showDiv(code = null){   

            $(".newRow").prepend(
                '<div class="form-group">@php $input = "products[]"; @endphp <label class="col-lg-2 control-label"> المنتج</label><div class="col-lg-2"> '+
    '<input type="text" onkeypress="return (event.charCode > 47 && event.charCode < 58)" name="{{ $input }}" '+
    ' value="'+code+'" class="form-control bcode barcode" onchange="getProductOldProduct(this.value)"> </div>  </div> '+
    '<div class="form-group"> @php $input = "quantity[]"; @endphp <label class="col-lg-2 control-label">كمية</label><div class="col-lg-2">'+
    '<input type="text" name="{{ $input }}" value="{{ isset($row) ? $row->{$input} : 1 }}" class="form-control"></div>'+
    '@php $input = "discounts[]"; @endphp <label class="col-lg-2 control-label">الخصم</label><div class="col-lg-2">'+
    '<input type="text" name="{{ $input }}" value="{{ isset($row) ? $row->{$input} : 0 }}" class="form-control"></div></div>'


                                ) 
            // $("#toggle").html()
        }
function changeInput(){
      
        $('#productId').focus();
        
        // PUT 13 for barode, space is 32
        // PUT 13 for barode, space is 32
        // PUT 13 for barode, space is 32
        // PUT 13 for barode, space is 32
        // PUT 13 for barode, space is 32
        // PUT 13 for barode, space is 32
        // PUT 13 for barode, space is 32
        // if (e.which == 13){
        //     showDiv(index)
        //     // setTimeout( 2000);
            
           
        //     e.preventDefault();
        //     return false;
        // }
}
</script>


