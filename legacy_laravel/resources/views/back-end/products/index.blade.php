@extends('back-end.layout.app')
@php $row_num = 1; $pageTitle = "عرض المنتجات" @endphp
@section('title')
{{$pageTitle}}
@endsection

@section('content')
<a class="btn  btn-danger" href="{{$routeName.'?quantity=' . 5}}"> المنتجات المقتربة من النفاذ</a>
<br><br>
<button class="btn  btn-info">عدد الاصناف : {{$productsCount}} </button>
<button class="btn  btn-info">اجمالي القطع : {{$totalQuantity}} </button>
<button class="btn  btn-info">اجمالي سعر الشراء : {{$totalBuyCost}}</button>
<button class="btn  btn-info">اجمالي سعر البيع : {{$totalSellCost}} </button>
<button class="btn  btn-success">صافي مكسب: {{$totalSellCost - $totalBuyCost}}</button>
<a href="{{url('admin/products-export-excel-sheet')}}" class="btn  btn-success">Export to excel sheet</a>
<a href="{{url('admin/products-export-PDF')}}" class="btn  btn-success">Export to PDF file</a>
{{-- <p style="float: right">عدد القطع :  {{$productsCount}}</p>
<p style="margin: 0% 20% ; float: right">اجمالي سعر الشراء : {{$totalBuyCost}}</p>
<p style="margin: 0% 20%">اجمالي سعر البيع : {{$totalSellCost}}</p>
<h4 style="text-align: center">صافي مكسب: {{$totalSellCost - $totalBuyCost}}</h4> --}}
<br><br>


@component('back-end.layout.header')
@slot('nav_title')
{{$pageTitle}}
<a href="{{ route($routeName.'.create') }}">
    <button class="alert-success"> <i class="fa fa-plus"></i> </button>
</a>
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
            <th>الاسم</th>
            <th>سعر الشراء</th>
            <th>سعر البيع</th>
            <th>الكمية</th>
            <th>سعر الشراء الكلى</th>
            <th>سعر البيع الكلى</th>
            <th>الكود</th>
            <th>المسؤول</th>
            <th>وقت الاضافه</th>


            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $item)
        <tr @if($item->quantity < 3) style="color: red" @endif>
                <td> {{$row_num++}}</td>
                <td>{{$item->name}}</td>
                <td>{{$item->purchasing_price}}</td>
                <td>{{$item->selling_price}}</td>
                <td>{{$item->quantity}}</td>
                <td>{{$item->purchasing_price * $item->quantity}}</td>
                <td>{{$item->selling_price * $item->quantity}}</td>
                <td>{{$item->code}}</td>
                <td>{{$item->user->user_name ?? " "}}</td>
                <td>{{$item->updated_at}}</td>
                <td>



                    <form action="{{ route($routeName.'.destroy' , ['id' => $item]) }}" method="post">
                        {{ csrf_field() }}
                        {{ method_field('delete') }}
                        <a href="{{ route($routeName.'.edit' , ['id' => $item]) }}" rel="tooltip" title=""
                            class="btn btn-xs btn-info">
                            <i class="fa fa-pencil-square-o"></i>
                        </a>

                        <!-- <a href="#" rel="tooltip" title="طباعة" onclick="printCode( {{$item->code}} , {{$item->selling_price}})"
                            class="btn btn-xs btn-info">
                            <i class="fa fa-print" data-route="{{url('/admin/print-bill/'.$item->id)}}"></i>
                        </a> -->
                            <a href="{{url('/admin/print-barcode/'.$item->id)}}" rel="tooltip" title="عرض"
                                class="btn btn-xs btn-info">
                                <i class="fa fa-eye" data-route="{{url('/admin/print-barcode/'.$item->id)}}"></i>
                            </a>
                            <a href="#" rel="tooltip" title="طباعة" onclick="printPageWithAjax()"
                                class="btn btn-xs btn-info">
                                <i class="fa fa-print" data-route="{{url('/admin/print-barcode/'.$item->id)}}"></i>

                            </a>
                            <button type="submit" rel="tooltip" title="" onclick="check()"
                                class="btn btn-xs btn-danger"><i class="fa fa-minus"></i></button>
                            @yield('moreButton')
                    </form>





                </td>
        </tr>
        @endforeach
    </tbody>
</table>
<br><br>

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
            headers:{ 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });
    function printCode(code , price){
        var myWindow = window.open("", "MsgWindow", "width=400,height=200");
        myWindow.document.write("<h1 style='text-align:center'>"+code+"</h1>");
        myWindow.document.write("<h2 style='text-align:center'>السعر ( "+price+")</h2>");
        // myWindow.print();
    }
    
    function printPageWithAjax(){

        var route= $(event.target).attr("data-route");
        var mywindow = window.open(route, 'PRINT', 'height=600,width=800');
        mywindow.focus(); // necessary for IE >= 10
        // mywindow.print();
}
</script>
@endpush