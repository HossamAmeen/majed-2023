@extends('back-end.layout.app')
@php $row_num = 1; $pageTitle = "عرض الملحوظات" @endphp
@section('title')
{{$pageTitle}}
@endsection

@section('content')
<form action="{{route('notes.store')}}" method="POST">
    @csrf
    <div class="col-md-12">
        <div class="row">
            @php $input = "note"; @endphp
            <div class="col-md-10 col-sm-4">
                <label class="">الملحوظه</label>
                <input type="text" class="form-control ls-group-input" name="{{ $input }}"
                    value="{{isset($row) ? $row->{$input}:Request::old($input)}}" required>
            </div>
            
            

        </div>
    </div>
    <div class="form-group">
        <div class="col-lg-offset-4">
            <button class="btn btn-info " type="submit"> اضافة </button>
        </div>
    </div>
</form>
@component('back-end.layout.header')

@slot('nav_title')
{{$pageTitle}}
{{-- <a href="{{ route($routeName.'.create') }}">
    <button class="alert-success"> <i class="fa fa-plus"></i> </button>
</a> --}}
@endslot
@endcomponent
@component('back-end.shared.table' )
@if (session()->get('action') )
<div class="alert alert-success">
    <strong>{{session()->get('action')}}</strong>
</div>
@endif
<table class="table table-bordered table-striped table-bottomless" id="ls-editable-table">
    <thead>
        <tr>
            <th>#</th>
            <th>الملحوظه</th>
            
            <th>المسؤول</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $item)
        <tr>
            <td> {{$row_num++}}</td>
            <td>{{$item->note}}</td>
            <td>{{$item->user->user_name ?? " "}}</td>
            <td>
                
                @if( Auth::user()->role == 1 )
                <form action="{{ route($routeName.'.destroy' , ['id' => $item]) }}" method="post">
                    {{ csrf_field() }}
                    {{ method_field('delete') }}
                    <a href="{{ route($routeName.'.edit' , ['id' => $item]) }}" rel="tooltip" title="" class="btn btn-xs btn-info"
                        data-original-title="Edit
                        {{ $sModuleName }}">
                        <i class="fa fa-pencil-square-o"></i>
                    </a>
                    <button type="submit" rel="tooltip" title=""  onclick="check()" class="btn btn-xs btn-danger"><i
                        class="fa fa-minus"></i></button>
                </form>
                @else
                ----
                @endif

            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endcomponent
@endsection

@push('js')

<script type="text/javascript">
    $(document).ready(function(){
            $("#{{$routeName}}").addClass('active');
        });
</script>

<script>
    $.ajaxSetup({
                    headers:
                    {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });


        $("#addForm").submit(function(e)
        {
            
            e.preventDefault();
            var formData  = new FormData(jQuery('#addForm')[0]);
           
           
            $.ajax({
                url:"{{route($routeName.'.store')}}",
                type:"POST",
                data:formData,
                contentType: false,
                processData: false,
                success:function(dataBack)
                {

                    console.log("success");
                    console.log(dataBack.id)
                    id =dataBack.id;
                    window.id=15;  
                    document.getElementById("addForm").reset();
                    docume

                }, error: function (xhr, status, error)
                {

                    // <button type="button" class="close" data-dismiss="alert"
                    //         aria-hidden="true">&times;</button>
                    console.log("errror " + xhr.responseJSON.error);
                    document.getElementById("errorDivMessage").style.display="block";
                    $("#errorDivMessage").html("<button class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>"+ xhr.responseJSON.error);
                    // $.each(xhr.responseJSON.errors,function(key,item)
                    // {

                    //     // $("#error").html("<li class='alert alert-danger text-center p-1'>"+ item +" </li>");
                    // })
                }
               
            })
            // var mywindow = window.open("{{url('/admin/print-bill/')}}"+'/'+ id, 'PRINT', 'height=600,width=800');
            // if(id != 0)
            // var mywindow = window.open("{{url('/admin/print-bill/')}}"+'/'+id, 'PRINT', 'height=600,width=800');
            // else
            // var mywindow = window.open("{{url('/admin/print-bill/')}}"+'/'+1, 'PRINT', 'height=600,width=800');
        })




</script>
@endpush
