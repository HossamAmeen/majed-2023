@extends('back-end.layout.my')
@section('content')<br><br>

<table class="table table-bordered table-striped table-bottomless" id="ls-editable-table">
    <thead>
        <tr>

            <th>اسم المشتري</th>
            <th>رقم الموبايل</th>


        </tr>
    </thead>
    {{--<tbody>
        @foreach ($rows as $item)
        <tr>

            <td>{{$item->price}}</td>
            <td>{{$item->quantity}}</td>

        </tr>
        @endforeach
    </tbody>--}}
</table>
@endsection

