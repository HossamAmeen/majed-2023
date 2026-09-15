@php $input = "name"; @endphp
<div class="form-group">
    <label class="col-lg-2 control-label">اسم المنتج</label>
    <div class="col-lg-10">
        <input type="text" name="{{ $input }}" value="{{ isset($row) ? $row->{$input} : '' }}" class="form-control"
            required style="width: 420px; height: 40px" >
        @error($input)
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>

@php $input = "purchasing_price"; @endphp
<div class="form-group">
    <label class="col-lg-2 control-label"> سعر الشراء</label>
    <div class="col-lg-10">
        <input type="text" name="{{ $input }}" value="{{ isset($row) ? $row->{$input} :0 }}" class="form-control"
            required style="width: 420px; height: 40px" id="purchasing_price" onkeyup="myFunction()">
        @error($input)
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>
@php $input = "selling_price"; @endphp
<div class="form-group">
    <label class="col-lg-2 control-label"> سعر البيع</label>
    <div class="col-lg-10">
        <input type="text" name="{{ $input }}" value="{{ isset($row) ? $row->{$input} : '' }}" class="form-control"
            required style="width: 420px; height: 40px">
        @error($input)
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>

@php $input = "quantity"; @endphp
<div class="form-group">
    <label class="col-lg-2 control-label">الكمية</label>
    <div class="col-lg-10">
        <input type="quantity" name="{{ $input }}" value="{{ isset($row) ? $row->{$input} :0}}" class="form-control"
            required style="width: 420px; height: 40px" id="quantity" onkeyup="myFunction()">
        @error($input)
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>
@php $input = "total_price"; @endphp
<div class="form-group">
    <label class="col-lg-2 control-label"> سعر الشراء الكلى </label>

    <div class="col-lg-10">
        <input type="text"  class="form-control"
            required style="width: 420px; height: 40px"  id="total_price" readonly >
        @error($input)
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>
{{-- @php $input = "code"; @endphp
<div class="form-group">
    <label class="col-lg-2 control-label"> الكود </label>
    <div class="col-lg-10">
        <input type="text" name="{{ $input }}" value="{{ isset($row) ? $row->{$input} : '' }}" class="form-control"
            required style="width: 420px; height: 40px" readonly>
        @error($input)
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div> --}}

{{-- @php $input = "discount"; @endphp
<div class="form-group">
    <label class="col-lg-2 control-label"> الخصم </label>
    <div class="col-lg-10">
        <input type="text" name="{{ $input }}" value="{{ isset($row) ? $row->{$input} : 0 }}" class="form-control"
             style="width: 420px; height: 40px">
        @error($input)
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div> --}}

@push('js')

<script>
    myFunction();
    function myFunction() {
  var quantity = parseInt(document.getElementById('quantity').value);

  var purchasing_price = parseInt(document.getElementById('purchasing_price').value);

  var total =0;
  total= purchasing_price * quantity;

var getValue = document.getElementById('total_price');
if(total == NaN){
    getValue.value = 0;
}
else
getValue.value = total;

}


</script>
@endpush




