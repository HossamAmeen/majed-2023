@php $input = "user_name"; @endphp
<div class="form-group">
    <label class="col-lg-2 control-label">اسم المستخدم</label>
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
@php $input = "email"; @endphp
<div class="form-group">
    <label class="col-lg-2 control-label">البريد</label>
    <div class="col-lg-10">
        <input type="email" name="{{ $input }}" value="{{ isset($row) ? $row->{$input} : '' }}" class="form-control"
            required style="width: 420px; height: 40px">
        @error($input)
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>
@php $input = "full_name"; @endphp
<div class="form-group">
    <label class="col-lg-2 control-label">الاسم بالكامل</label>
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
@php $input = "password"; @endphp
<div class="form-group">
    <label class="col-lg-2 control-label">كلمة السر</label>
    <div class="col-lg-10">
        <input type="password" name="{{ $input }}" class="form-control" id="password"  @iF(!isset($row)) required @endif style="width: 420px; height: 40px">
        @error($input)
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>
@php $input = "password_confirmation"; @endphp
<div class="form-group">
    <label class="col-lg-2 control-label"> تاكيد كلمة السر</label>
    <div class="col-lg-10">
        <input type="password" name="{{ $input }}" class="form-control"
        id="password_confirmation"  @iF(!isset($row)) required @endif style="width: 420px; height: 40px">
        @error($input)
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>

@if(isset($row))
@if( Auth::user()->id != $row->id  && Auth::user()->id < $row->id )
@php $input = "role"; @endphp
<div class="form-group">
    <label class="col-md-2 col-sm-2 col-xs-12 control-label">الصلاحيه</label>

    <div class="col-md-5 col-sm-5 col-xs-12 ls-group-input">
        <div class="radio">
            <label class="radio">
                <input type="radio" name="{{ $input }}" id="optionsRadios1" value="2" @if ( isset($row)) @if($row->role
                == 2 ) checked @endif
                @else
                checked
                @endif >
                مسؤول
            </label>
        </div>
        <div class="radio">
            <label class="radio">
                <input type="radio" name="{{ $input }}" id="optionsRadios2" value="3" @if ( isset($row)) @if($row->role
                == 3 ) checked @endif
                @endif
                >
                موظف
            </label>
        </div>
    </div>
    @error($input)
    <span class="invalid-feedback" role="alert">
        <strong>{{ $message }}</strong>
    </span>
    @enderror
</div>
@endif
@else
{{-- create  --}}
@php $input = "role"; @endphp
<div class="form-group">
    <label class="col-md-2 col-sm-2 col-xs-12 control-label">الصلاحيه</label>

    <div class="col-md-5 col-sm-5 col-xs-12 ls-group-input">
        <div class="radio">
            <label class="radio">
                <input type="radio" name="{{ $input }}" id="optionsRadios1" value="2" @if ( isset($row)) @if($row->role
                == 2 ) checked @endif
                @else
                checked
                @endif >
                مسؤول
            </label>
        </div>
        <div class="radio">
            <label class="radio">
                <input type="radio" name="{{ $input }}" id="optionsRadios2" value="3" @if ( isset($row)) @if($row->role
                == 3 ) checked @endif
                @endif
                >
                موظف
            </label>
        </div>
    </div>
    @error($input)
    <span class="invalid-feedback" role="alert">
        <strong>{{ $message }}</strong>
    </span>
    @enderror
</div>

@endif



{{-- @php $input = "image"; @endphp
<div class="form-group">
    <label class="col-md-2 control-label">الصورة</label>
    <div class="col-md-10 ls-group-input">
        <input name="{{ $input }}" id="file-3" type="file">
    </div>
    @error($input)
    <span class="invalid-feedback" role="alert">
        <strong>{{ $message }}</strong>
    </span>
    @enderror
    <span style="margin-right: 15%">يفضل رفع الصوره 400 * 400 </span>
</div> --}}
