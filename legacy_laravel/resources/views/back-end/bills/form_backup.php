@if( isset($row) )
    @foreach($row->orders as $key => $value)
    <input type="hidden" name="orders[]" value="{{$value->id}}" class="form-control" >
    <div class="form-group">
        @php $input = "products[]"; @endphp
        <label class="col-lg-2 control-label"> المنتج</label>
        <div class="col-lg-2">
            <input type="text" name="{{ $input }}" value="{{$value->product->code??" "}}" class="form-control"
                readonly>
            
                <span style="color: blue">{{$value->product_name}}</span>
        </div>
    </div>
    <div class="form-group">

        @php $input = "quantity[]"; @endphp
        <label class="col-lg-2 control-label">كمية</label>
        <div class="col-lg-2">
            <input type="text" name="{{ $input }}" value="{{$value->quantity}}" class="form-control">
        </div>
        {{-- @php $input = "costs[]"; @endphp
        <label class="col-lg-2 control-label">سعر</label>
        <div class="col-lg-2">
            <input type="text" name="{{ $input }}" value="{{$value->product_name}}" class="form-control">
        </div> --}}
        {{-- @php $input = "discounts[]"; @endphp
        <label class="col-lg-2 control-label">الخصم</label>
        <div class="col-lg-2">
            <input type="text" name="{{ $input }}" value="{{$value->discount}}" class="form-control">
        </div> --}}
    </div>
    @endforeach
@else
    <div class="form-group">
        @php $input = "products[]"; @endphp
        <label class="col-lg-2 control-label"> المنتج</label>
        <div class="col-lg-2">
            <input type="text" name="{{ $input }}" value="{{ isset($row) ? $row->{$input} : '' }}" class="form-control"
                required>
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
@endif