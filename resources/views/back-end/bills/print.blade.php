@extends('back-end.layout.my')
@section('content')<br><br>
<button id="print" onclick="printContent('ls-editable-table');" >Print</button>
<script type="text/javascript">
  function printContent(element){
         window.print(); window.close();
          }
</script>
<table class="table table-bordered table-striped table-bottomless" id="ls-editable-table">
    <thead>
        <tr>
            <th>السعر </th>
            <th>الكمية </th>

            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $item)
        <tr>

            <td>{{$item->name}}</td>
            <td>{{$item->phone}}</td>
            <td>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection





<!--<a href="  url('/print')}}" class="btnprn btn">Print</a>
<a href=" url('/print')}}" class="btnPrint">Print</a>-->

/*  function PrintElem(elem)
{
    var mywindow = window.open('', 'PRINT', 'height=500,width=800');
    mywindow.document.write('<html><head><title>' + document.title  + '</title>');
    mywindow.document.write('</head><body >');
    mywindow.document.write('<h1>' + document.title  + '</h1>');
    mywindow.document.write(document.getElementById('elem').innerHTML);
    mywindow.document.write('</body></html>');

    mywindow.document.close(); // necessary for IE >= 10
    mywindow.focus(); // necessary for IE >= 10

    mywindow.print();
    mywindow.close();

    return true;
}*/
