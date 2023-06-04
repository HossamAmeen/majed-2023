
    <div class="container" id="GFG">
            <div class="ticket-content" id="design-1">
                    <h3> مجدى
                      <br>
                      <span class="date">{{now()}}</span>
                    </h3>
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

