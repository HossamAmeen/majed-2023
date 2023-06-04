@extends('back-end.layout.app')
@php $row_num = 1; $pageTitle = "عرض الطلبات" @endphp
@section('title')
{{$pageTitle}}
@endsection

@section('content')

<a class="btn btn-info" href="{{$routeName.'?day=' . date('Y-m-d')}}">طلبتا اليوم</a>
<a class="btn btn-info" href="{{$routeName.'?month=' . date('m')}}">طلبتا الشهر</a>
<a class="btn btn-info" href="{{$routeName.'?year=' . date('Y')}}">طلبتا السنة</a>
<br><br>
<a class="btn btn-danger" href="{{$routeName.'?returnedDay=' . date('Y-m-d')}}&returned='true'">المرتجعات اليوم</a>
<a class="btn btn-danger" href="{{$routeName.'?returnedDay=' . date('m')}}&returned='true'">طلبتا الشهر</a>
<a class="btn btn-danger" href="{{$routeName.'?returnedDay=' . date('Y')}}&returned='true'">طلبتا السنة</a>
<br><br>
<form action="{{$routeName}}" method="GET">
<input  class="form controll" type="date" name="dateSearch" value="{{session('dateSearch') ?? ''}}"  required>
<button type="submit"  class="btn btn-info">بحث </button>
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
            <th># </th>
            <th>المنتج</th>
            <th>كود المنتج</th>
            <th>السعر</th>
            <th>الخصم</th>
            <th>الكمية</th>
            <th>التاريخ   @if( str_contains( url()->full()  ,  'returned')  === true ) - <span style="color: red"> تاريخ الاسترجاع </span> @endif </th>
            <th> اسم صاحب الفاتوره ( رقم الفاتوره )</th>
            <th>المسؤول</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @php
            $totalCost = 0 ;
            $totalQuantity = 0 ;
        @endphp
        @foreach ($rows as $item)
        <tr>
            <td> {{$row_num++}}</td>
            <td>{{$item->product_name ?? " "}}</td>
            <td style="color:{{ $item->product->trashed() ? 'red' : ' '}}">{{$item->product->code ?? " "}}</td>
            <td>{{$item->price}}</td>
            <td>{{$item->discount}}</td>
            <td>{{$item->quantity}}</td>
            <td>{{$item->date}} @if( str_contains( url()->full()  ,  'returned')  === true ) - <span style="color: red">{{$item->deleted_at->format('Y-m-d') }}</span> @endif </td>
            <td>{{$item->bill->name ?? " "}}({{$item->bill->id ?? " "}})</td>
            <td>{{$item->user->user_name ?? " "}}</td>
            <td>
                    <form action="{{ route($routeName.'.destroy' , ['id' => $item]) }}" method="post">
                        {{ csrf_field() }}
                        {{ method_field('delete') }}
                        <button type="submit" rel="tooltip" title=""  onclick="check()" class="btn btn-xs btn-danger"><i
                            class="fa fa-minus"></i></button>
                    @php
                        $billId = $item->bill->id ?? 0;
                    @endphp
                  
                    </form>
            </td>
            @php
                $totalCost += $item->price * $item->quantity  - $item->discount; 
                $totalQuantity += $item->quantity;
            @endphp
        </tr>
        @endforeach
    </tbody>
</table>
<div style="text-align: center ; margin: 5% 20%">
    <p>   اجمالي الفلوس : {{$totalCost}}</p>
    <p>   اجمالي الكمية : {{$totalQuantity}}</p>
    <p>اجمالي الاصناف : {{$row_num - 1 }}</p>   

</div>
 
 
        
  
@endcomponent
@endsection

@push('js')

<script type="text/javascript">
    $(document).ready(function(){
            $("#{{$routeName}}").addClass('active');
        });

    function printPageWithAjax(){
        var route= $(event.target).attr("data-route");
        var mywindow = window.open(route, 'PRINT', 'height=600,width=800');
        mywindow.focus(); // necessary for IE >= 10
        mywindow.print();
    }
</script>

@endpush
