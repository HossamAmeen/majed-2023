@php $input = "price"; @endphp
<div class="form-group">
    <label class="col-lg-2 control-label"> السعر</label>
    <div class="col-lg-10">
        <input type="text" name="{{ $input }}" value="{{ isset($row) ? $row->{$input} : '' }}" class="form-control"
            required>
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
        <input type="text" name="{{ $input }}" value="{{ isset($row) ? $row->{$input} : '' }}" class="form-control"
            required>
        @error($input)
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>
@php $input = "date"; @endphp
<div class="form-group">
    <label class="col-lg-2 control-label">التاريخ  </label>
    <div class="col-lg-10">
        <input type="date" name="{{ $input }}" value="{{ isset($row) ? $row->{$input} : '' }}" class="form-control"
            required>
        @error($input)
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>
@php $input = "discount"; @endphp
<div class="form-group">
    <label class="col-lg-2 control-label"> الخصم</label>
    <div class="col-lg-10">
        <input type="text" name="{{ $input }}" class="form-control" required>
        @error($input)
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>


