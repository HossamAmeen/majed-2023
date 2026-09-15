<!DOCTYPE html>
<html lang="ar">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>4MD Bill</title>
    <link rel="stylesheet" href="style.css">
    <style>
      * {
        font-size: 10px;
        font-family: 'Times New Roman';
      }
      table {
        width: 100%;
       
      }
      th {
        text-align: right;
      }
      th.price-off {
        width: 40px;
      }
      td,
      th,
      tr,
      table {
        border-top: 1px solid black;
        border-collapse: collapse;
        font-size: 120%;
      }
      td.description,
      th.description {
        width: 170px;
        max-width: 170px;
        word-break: break-all;
      }
      td.quantity,
      th.quantity {
        width: 50px;
        max-width: 50px;
        word-break: break-all;
      }
      td.price,
      th.price {
        width: 60px;
        max-width: 60px;
        word-break: break-all;
      }
      td.price-off {
        width: 20px;
        word-break: break-all;
      }
      .centered {
        text-align: center;
        align-content: center;
      }
      .ticket {
        width: 280px;
      }
      .last-price {
        height: 35px;
      }
      .black-price {
        background-color: rgb(148, 148, 148);
        color: rgb(0, 0, 0);
      }
      img {
        max-width: inherit;
        width: inherit;
      }
      @media print {
        .hidden-print,
        .hidden-print * {
          display: none !important;
        }
        body.ticket {
          width: 58mm
        }
        thead {
          display: table-header-group;
        }
        thead,
        tfoot {
          break-inside: unset;
        }
      }
    </style>
  </head>
  <body dir="rtl">
    <div class="ticket">
      <h3  style="text-align: center;">فـاتــورة</h3>
      <h3 style="text-align: center;"> جولدن فنجرز للملابس الجاهزه
        <br>
        <img src="{{asset('4md.jpg')}}" width="25" height="25">
        <br>
        <span class="date">{{now()}}</span>
      </h3>
      <span style="font-weight: bold; padding-bottom: 0em">س . تجارى :49203 &nbsp;&nbsp; ب . ض : 11276</span>
      <span style="font-weight: bold"> &nbsp; ملف ض :3/6/527/4890/5 &nbsp;</span>
      <p class="centered">
        رقم الفاتورة : {{$bill->id}}<br>
        اسم العميل : {{$bill->name}}
        <br>
        تليفون : {{$bill->phone}}
      </p>
      <table>
        <thead>
          <tr>
            <th class="description">المنتج</th>
            <th class="quantity">الكمية</th>
            <th class="price">السعر</th>
            <th class="price-off">الخصم</th>
          </tr>
        </thead>
        <tbody>
        @php
          $sum=0;
          $discount = 0;
        @endphp
        @foreach ($bill->orders as $order)
          <tr>
            <td class="description">
            {{$order->product_name}}
            </td>
            <td class="quantity">
            {{$order->quantity}}
            </td>
            <td class="price">
            {{$order->price}}
            </td>
            <td class="price-off">
            {{$order->discount }}
            </td>
          </tr>
          @php
            $sum+= $order->quantity * $order->price;
            $discount += $order->discount;
            @endphp
          @endforeach
     
        </tbody>
      </table>
      <p class="last-price">
        <span class="end-all-price"><b> اجمالى السعر : {{$sum}}</b></span>
        <br>
        <span class="end-all-price"><b> اجمالى الخصم : {{$discount+ $bill->discount}}</b></span>
        <br>
        <span class="end-all-price"><b>المطلوب دفعه : {{$sum-$discount-$bill->discount}}</b></span>
      </p>
      <p style="font-weight: bold">سياسة الإستبدال و الإسترجاع لجهاز حماية المستهلك 14 يوم المنتج فى حالته الأصليه بأصل الفاتوره
        و 30 يوم عند وجود عيب صناعه
      </p>
      <p style="font-weight: bold">العنوان : {{$briefs->address}} </p>
      <p style="font-weight: bold">تليفون : {{$briefs->phone}}</p>
      {{-- <br><br> <button class="btn btn-info" onclick="printPageWithAjax({{$bill->id}})" >طباعة</button> --}}
    </div>
   
  </body>
</html>