@php $input = "note"; @endphp
<div class="form-group">
    <label class="col-lg-2 control-label">الملحوظه</label>
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