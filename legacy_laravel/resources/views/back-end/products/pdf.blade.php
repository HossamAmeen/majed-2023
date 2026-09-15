@php $row_num = 1; $pageTitle = "عرض المنتجات" @endphp
<html>

<head>
    <style>
        body {}

        table,
        th,
        td {
            border: 1px solid black;
        }
    </style>

</head>

<body>
    <h1 style="text-align: center">منتجات هذا السنه</h1>
    <table dir="rtl">
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

            </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>