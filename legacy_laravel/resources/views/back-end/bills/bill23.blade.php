<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>طبع الفاتورة</title>
    <link rel="stylesheet" type="text/css" href="{{asset('css/style.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('css/style.css.map')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('css/style.scss')}}">
  <link rel="stylesheet" type="text/css" href="{{asset('css/bootstrap.css')}}">
  <script type="text/javascript" src="{{asset('js/bootstrap.min.js')}}"></script>
  <script type="text/javascript" src="{{asset('js/jquery-2.2.4.min.js')}}"></script>
  <script type="text/javascript" src="{{asset('js/jquery.printPage.js')}}"></script>
   <script type="text/javascript">
 /*function Print()
   {
        $(document).ready(function (){
             window.print();
        })
   }*/

    </script>
</head>
<body style="text-align:center;">
<center><br><br>

    <div class="container" id="GFG">
            <div class="ticket-content" id="design-1">
                    <h3> 4MD <img src="{{asset('4md.jpg')}}" width="30" height="30"> 
                      <br>
                      <span class="date">{{now()}}</span>
                    </h3>
                    <h4 class="name"><span>رقم الفاتورة :</span>
                        <span> {{$bill->id}}</span>
                    </h4>
                    <h4 class="name"><span>اسم العميل:</span>
                      <span> {{$bill->name}}</span>
                    </h4>

                    <h4 class="phone"><span>التلفون:</span>
                        <span>{{$bill->phone}}</span></h4>
                    <div class="table-header">
                      <p class="description">المنتج</p >
                      <p class="quantity">الكمية</p >
                      <p class="price">السعر</p>
                      <p class="price">الخصم</p>
                    </div>
                    @php
                        $sum=0;
                        $discount = 0;
                    @endphp
                    @foreach ($bill->orders as $order)
                   
                    <div class="table-data">
                      <p class="description">{{$order->product_name}}	</p>
                      <p class="quantity">{{$order->quantity}}</p>
                      <p class="selling_price">{{$order->price}}</p>
                      <p class="quantity">{{$order->discount}}</p>
                            @php
                                $sum+= $order->quantity * $order->price;
                                // $discountValue = ($products->product->quantity * ($products->product->selling_price * $products->product->discount / 100 ));
                                // $discount +=(  $discountValue);
                                $discount += $order->discount;
                            @endphp
                   </div >
                   
                    @endforeach
                 <div class="total-price">
                     <p class="title">اجمالى السعر</p>
                     <p class="cost">{{$sum}}</p>
                   </div>

                   <div class="total-sale">
                    <p class="title">اجمالى الخصم</p>
                    <p class="cost">{{$discount}}</p>
                  </div>
                  <div class="total-pay">
                    <p class="title">المطلوب دفعه</p>
                    <p class="cost">{{$sum-$discount}}</p>
                  </div>
                    <p class="table-footer">الأسعار تشمل ضريبة القيمة المضافة</p>

                  </div><br>

    </div>
</center>
</body>
</html>

{{--
<div class="contentSection">
  <div class="contentToPrint">
      <!-- content to be printed here -->
  </div>
</div>

<div class="contentSection">
  <a href="#" id="printOut">Print This</a>
</div>

<div class="contentSection termsToPrint">
  <h4>Terms & conditions</h4>
  <p>Management reserves the right to withdraw, amend or suspend this print job in the event of any unforeseen circumstances outside its reasonable control, with no liability to any third party.</p>
</div>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.0/jquery.min.js"></script>

<script type="text/javascript">
  $(function(){
      $('#printOut').click(function(e){
          e.preventDefault();
          var w = window.open();
          var printOne = $('.contentToPrint').html();
          var printTwo = $('.termsToPrint').html();
          w.document.write('<html><head><title>Copy Printed</title></head><body><h1>Copy Printed</h1><hr />' + printOne + '<hr />' + printTwo) + '</body></html>';
          w.window.print();
          w.document.close();
          return false;
      });
  });
</script> --}}

{{--
<!DOCTYPE html>
<html>

<head>
	<title>
		Print the content of a div
	</title>

	<!-- Script to print the content of a div -->
	<script>

		function printDiv() {
			// var divContents = document.getElementById("GFG").innerHTML;
			var a = window.open('', '', 'height=500, width=500');
			a.document.write('<html>');
			a.document.write('<body > <h1>Div contents are <br>');
			// a.document.write(divContents);
			a.document.write('</body></html>');
			a.document.close();
			a.print();
		}
	</script>
</head>

<body>
	<center>
		<div id="GFG" style="background-color: green;">

			<h2>Geeksforgeeks</h2>

			<table border="1px">
				<tr>
					<td>computer</td>
					<td>Algorithm</td>
				</tr>
				<tr>
					<td>Microwave</td>
					<td>Infrared</td>
				</tr>
			</table>
		</div>

		<p>
			The table is inside the div and will get
			printed on the click of button.
		</p>

		<input type="button" value="click"
					onclick="printDiv()">
	</center>
</body>

</html>
 --}}
