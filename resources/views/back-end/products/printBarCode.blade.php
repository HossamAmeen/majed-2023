<!DOCTYPE html>
<html>
  <head>
    <script>
      function printDiv() {
        var divContents = document.getElementById("print-div").innerHTML;
        var a = window.open('', '', 'height=500, width=600');
        a.document.write('<html>');
        a.document.write('<body style="font-size:12px;text-align:center;">');
        a.document.write(divContents);
        a.document.write('</body></html>');
        a.document.close();
        setTimeout(function () {
          a.print();
        }, 500);
      } 
    </script>
  </head>
  <body style="font-size:12px;text-align:center;">
    <div id="print-div">
      <span style="
      text-align:center;
      margin-bottom:-10px;
      overflow:hidden;
      text-overflow:ellipsis;
      font-size: 10px;
      display:-webkit-box;
      -webkit-line-clamp: 2; 
      /* number of lines to show /-webkit-box-orient: vertical;/ display: block; */
      ">{{$product->name}}</span>
      
      
      <br>
     
     @php
 

     echo '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG($product->code, 'C39E') . '
     " style="width:120px" height="30" width="190"    />';
  
     @endphp
      <br>
    <div style="width: 100%; ">
     <span style="text-align: right;font-size: 10px !important;">الكود {{$product->code}} </span>
     <span style="text-align: left;font-size: 10px;">السعر {{$product->selling_price}} جنية</span>
    
    </div>
  </div>
    <input type="button" value="اطبع" onclick="printDiv()">
  </body>
</html>