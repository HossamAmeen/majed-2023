@extends('back-end.layout.app')
@php $pageTitle = "إضافه فاتورة " @endphp
@section('title')
{{ $pageTitle }}
@endsection

@section('content')

@component('back-end.layout.header')
@slot('nav_title')
{{ $pageTitle }}
@endslot
@endcomponent

@component('back-end.shared.create')
{{-- action="{{ route($routeName.'.store') }}" --}}
<form id="addForm" method="post" class="form-horizontal ls_form">
    @if (session()->get('action') )
    <div class="alert alert-success">
        <strong>{{session()->get('action')}}</strong>
    </div>
    @endif
    @error('errorProduct')
    <div class="alert alert-danger">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {{ $message }}
    </div>
    @enderror

    <div id="successDivMessage" class="alert alert-success" style="display: none">
        {{-- <button class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> --}}
        <strong> تم الاضافه بنجاح </strong>
    </div>

    <div id="errorDivMessage" class="alert alert-danger" style="display: none">
    </div>

    <div id="errorBox" class="alert alert-danger" style="display: none">
    </div>

    @csrf
    @include('back-end.'.$folderName.'.form')
    {{-- id  <input id="billID" > --}}


    <div class="form-group">
        <div class="col-lg-offset-2 col-lg-10">
            <button class="btn btn-info" type="submit" form="addForm" value="add" id="submitButton"> إضافة </button>

        </div>
        {{-- <div class="col-lg-offset-2 col-lg-10">
                        <button class="btn btn-info" type="button" onclick="printPageWithAjax()" >  test  </button>
                    </div> --}}
    </div>
</form>
<div id="printDivBill" style="display: none ;   text-align: right;">


</div>
@endcomponent

@endsection
@push('css')


{{-- <link rel="stylesheet" type="text/css" href="{{asset('css/style.css')}}">
<link rel="stylesheet" type="text/css" href="{{asset('css/style.css.map')}}">
<link rel="stylesheet" type="text/css" href="{{asset('css/style.scss')}}">
<link rel="stylesheet" type="text/css" href="{{asset('css/bootstrap.css')}}">
<script type="text/javascript" src="{{asset('js/bootstrap.min.js')}}"></script>
<script type="text/javascript" src="{{asset('js/jquery-2.2.4.min.js')}}"></script>
<script type="text/javascript" src="{{asset('js/jquery.printPage.js')}}"></script> --}}

<!-- Responsive Style For-->
<link href="{{asset('panel/assets/css/rtl-css/responsive-rtl.css')}}" rel="stylesheet">
<!-- Responsive Style For-->
<link rel="stylesheet" href="{{asset('panel/assets/css/rtl-css/plugins/summernote-rtl.css')}}">
<!-- Custom styles for this template -->


<!-- Plugin Css Put Here -->

<link rel="stylesheet" href="{{asset('panel/assets/css/rtl-css/plugins/fileinput-rtl.css')}}">
@endpush
@push('js')

<script>
    function printDiv(){
        var mywindow = window.open('', 'PRINT', 'height=400,width=600');
        mywindow.document.write( $('#printDivBill').html());
        mywindow.print();
    }

    function getProduct(id){
        console.log(id);
        console.log("get product");
        if( typeof id == "number" ){
            console.log("id is not number");
            return;
        }
        $.ajax({
               
                url:"{{url('admin/products')}}"+"/"+id,
                type:"get",
                contentType: false,
                processData: false,
                success:function(dataBack)
                {

                    console.log("success from get product");
                    console.log(dataBack.product);
                  
                    // changeInput()
                    if( dataBack.product!==null)
                    {
                        showDiv(id)
                    $('#productId').focus();
                        $('#productDetials').html("المنتج :" + dataBack.product.name + "-السعر:" + dataBack.product.selling_price + "جنيه - العدد :" + dataBack.product.quantity );
                        document.getElementById('productId').value = ''
                    }
                    else
                    $('#productDetials').html("لا يوجد");
                    // console.log(dataBack.product.name);
                  

                }, error: function (xhr, status, error)
                {
             
                }
               
            })
        console.log("test");
    }


    function getProductOldProduct(id){
        console.log(id);
        console.log("get product");
        if( typeof id == "number" ){
            console.log("id is not number");
            return;
        }
        $.ajax({
                url:"{{url('admin/products')}}"+"/"+id,
                type:"get",
                contentType: false,
                processData: false,
                success:function(dataBack)
                {

                    console.log("success from get product");
                    console.log(dataBack.product);
                    // showDiv(id)
                    if( dataBack.product!==null)
                    {
                        $('#productDetials').html("المنتج :" + dataBack.product.name + "-السعر:" + dataBack.product.selling_price + "جنيه - العدد :" + dataBack.product.quantity );
                        document.getElementById('productId').value = ''
                    }
                    else
                    $('#productDetials').html("لا يوجد");
                    // console.log(dataBack.product.name);
                  

                }, error: function (xhr, status, error)
                {
             
                }
               
            })
        console.log("test");
    }
function printPageWithAjax(id){

// var route= $(event.target).attr("data-route");
url = "{{url('/admin/print-bill/')}}" ;
var mywindow = window.open(url+"/" + id , 'PRINT', 'height=600,width=800');
mywindow.focus(); // necessary for IE >= 10
mywindow.print();
}


</script>
<script type="text/javascript">



</script>
<script>

    // `form` MUST exists as ID for FORM
    // `bcode` MUST exists as CLASS in all textboxes of barcode reader
    
    $('#addForm').find('.bcode').keypress(function (e) {
        console.log("swing input");
        // get index
        // this.className                                   =>  "bcode barcode1"
        // this.className.split(' ')                        =>  ["bcode", "barcode1"]
        // this.className.split(' ')[1]                     =>  "barcode1"
        // this.className.split(' ')[1].slice(-1)           =>  "1"
        // parseInt(this.className.split(' ')[1].slice(-1)) =>  1

        let index = parseInt(this.className.split(' ')[2].slice(-1)) + 1;
        let className = '.barcode' + index;
        console.log(className);
        // PUT 13 for barode, space is 32
        // PUT 13 for barode, space is 32
        // PUT 13 for barode, space is 32
        // PUT 13 for barode, space is 32
        // PUT 13 for barode, space is 32
        // PUT 13 for barode, space is 32
        // PUT 13 for barode, space is 32
        if (e.which == 13){
            // showDiv(index)
            // setTimeout( 2000);
            
            $(className).focus();
            e.preventDefault();
            return false;
        }
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
            $('#submitButton').prop('disabled', true);
            e.preventDefault();
           
            var formData  = new FormData(jQuery('#addForm')[0]);
            // console.log(formData);
            document.getElementById("successDivMessage").style.display="none";
            document.getElementById("errorBox").style.display="none";
            errorMessageDiv = document.getElementById("errorDivMessage");
            if(errorMessageDiv !== null)
            errorMessageDiv.style.display="none";
            $.ajax({
                url:"{{route($routeName.'.store')}}",
                type:"POST",
                data:formData,
                contentType: false,
                processData: false,
                success:function(dataBack)
                {

                    console.log("success from submit form");
                    document.getElementById("addForm").reset();
                    document.getElementById("successDivMessage").style.display="block";
                    document.getElementById("printDivBill").style.display="block";

                    $('#printDivBill').html(dataBack);
                    // $('#printDivBill').append("<br><br> <button onclick='printDiv()' >طباعة</button>")
                    $('#submitButton').prop('disabled', false);
               

                }, error: function (xhr, status, error)
                {
                    console.log("errror " + xhr.responseJSON.error);
                    errorMessageDiv = document.getElementById("errorDivMessage");
                    if(errorMessageDiv !== null)
                    errorMessageDiv.style.display="block";
                    else
                    alert( xhr.responseJSON.error)

                    document.getElementById("printDivBill").style.display="none";
                    $("#errorDivMessage").html("<button class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>"
                                        + xhr.responseJSON.error);
                }
               
            })
        
        });




</script>
{{-- <script>
    function printBill(){
        var printWindow = window.open("", 'PRINT', 'height=600,width=800');
            printWindow.document.write("test"); 
    }
</script> --}}
<!--Upload button Script Start-->
<script src="{{asset('panel/assets/js/fileinput.min.js')}}"></script>
<!--Upload button Script End-->

<!--Auto resize  text area Script Start-->
<script src="{{asset('panel/assets/js/jquery.autosize.js')}}"></script>
<!--Auto resize  text area Script Start-->
<script src="{{asset('panel/assets/js/pages/sampleForm.js')}}"></script>


<!-- summernote Editor Script For Layout start-->
<script src="{{asset('panel/assets/js/summernote.min.js')}}"></script>
<!-- summernote Editor Script For Layout End-->

<!-- Demo Ck Editor Script For Layout Start-->
<script src="{{asset('panel/assets/js/pages/editor.js')}}"></script>
<script type="text/javascript"></script>
<!-- Demo Ck Editor Script For Layout ENd-->
@endpush