@extends('back-end.layout.app')

@section('title')
    احصائيات السستم
@endsection

@section('content')

    @component('back-end.layout.header')
        @slot('nav_title')
        احصائيات السستم
        @endslot
    @endcomponent

    <div class="row" style="  padding-right: 10%;">


            <div class="col-md-10">
                {{-- <div class="panel panel-default"> --}}

                    <div class="panel-body">
                        <form class="form-horizontal ls_form ls_form_horizontal" action="{{route('configrations.update', ['id' => $row])}}" method="POST">
                            @csrf
                            {{ method_field('put') }}

                            @php $input = "title"; @endphp
                            <div class="form-group">
                                <label class="col-lg-2 control-label">اسم الموقع</label>

                                <div class="col-lg-10">
                                    <input type="text" name="{{ $input }}"  value="{{ isset($row) ? $row->{$input} : Request::old($input) }} "
                                      class="form-control" required>
                                      @error($input)
                                      <span class="invalid-feedback" role="alert">
                                               <strong>{{ $message }}</strong>
                                      </span>
                                      @enderror
                                </div>
                            </div>
                            @php $input = "phone"; @endphp
                            <div class="form-group">
                                <label class="col-lg-2 control-label">الهاتف</label>

                                <div class="col-lg-10">
                                    <input type="text" name="{{ $input }}"  value="{{ isset($row) ? $row->{$input} : Request::old($input) }} "
                                      class="form-control">
                                      @error($input)
                                      <span class="invalid-feedback" role="alert">
                                               <strong>{{ $message }}</strong>
                                      </span>
                                      @enderror
                                </div>
                            </div>
                            @php $input = "address"; @endphp
                            <div class="form-group">
                                <label class="col-lg-2 control-label">العنوان </label>

                                <div class="col-lg-10">
                                    <input type="text" name="{{ $input }}"  value="{{ isset($row) ? $row->{$input} : Request::old($input) }} "
                                      class="form-control" required>
                                      @error($input)
                                      <span class="invalid-feedback" role="alert">
                                               <strong>{{ $message }}</strong>
                                      </span>
                                      @enderror
                                </div>
                            </div>
                            {{-- @php $input = "en_title"; @endphp
                            <div class="form-group">
                                <label class="col-lg-2 control-label"> اسم الموقع بالانجليزي</label>

                                <div class="col-lg-10">
                                    <input type="text" name="{{ $input }}"  value="{{ isset($row) ? $row->{$input} : Request::old($input) }} "
                                      class="form-control" required>
                                      @error($input)
                                      <span class="invalid-feedback" role="alert">
                                               <strong>{{ $message }}</strong>
                                      </span>
                                      @enderror
                                </div>
                            </div>
                            @php $input = "email"; @endphp
                            <div class="form-group">
                                <label class="col-lg-2 control-label">البريد</label>

                                <div class="col-lg-10">
                                    <input type="text" name="{{ $input }}"  value="{{ isset($row) ? $row->{$input} : Request::old($input) }} "
                                      class="form-control">
                                      @error($input)
                                      <span class="invalid-feedback" role="alert">
                                               <strong>{{ $message }}</strong>
                                      </span>
                                      @enderror
                                </div>
                            </div>
                            @php $input = "home_description"; @endphp
                            <div class="form-group">
                                <label class="col-lg-2 control-label">وصف الرئيسيه</label>

                                <div class="col-lg-10">
                                    <input type="text" name="{{ $input }}"  value="{{ isset($row) ? $row->{$input} : Request::old($input) }} "
                                      class="form-control">
                                      @error($input)
                                      <span class="invalid-feedback" role="alert">
                                               <strong>{{ $message }}</strong>
                                      </span>
                                      @enderror
                                </div>
                            </div>
                            @php $input = "en_home_description"; @endphp
                            <div class="form-group">
                                <label class="col-lg-2 control-label"> وصف الرئيسيه بالانجليزي</label>

                                <div class="col-lg-10">
                                    <input type="text" name="{{ $input }}"  value="{{ isset($row) ? $row->{$input} : Request::old($input) }} "
                                      class="form-control">
                                      @error($input)
                                      <span class="invalid-feedback" role="alert">
                                               <strong>{{ $message }}</strong>
                                      </span>
                                      @enderror
                                </div>
                            </div>



                           
                            @php $input = "video"; @endphp
                            <div class="form-group">
                                <label class="col-lg-2 control-label">رابط الفديو الأول</label>

                                <div class="col-lg-10">
                                    <input type="text" name="{{ $input }}"  value="{{ isset($row) ? $row->{$input} : Request::old($input) }} "
                                      class="form-control">
                                      @error($input)
                                      <span class="invalid-feedback" role="alert">
                                               <strong>{{ $message }}</strong>
                                      </span>
                                      @enderror
                                </div>
                            </div>
                             @php $input = "video2"; @endphp
                            <div class="form-group">
                                <label class="col-lg-2 control-label">رابط الفديو الثاني</label>

                                <div class="col-lg-10">
                                    <input type="text" name="{{ $input }}"  value="{{ isset($row) ? $row->{$input} : Request::old($input) }} "
                                      class="form-control">
                                      @error($input)
                                      <span class="invalid-feedback" role="alert">
                                               <strong>{{ $message }}</strong>
                                      </span>
                                      @enderror
                                </div>
                            </div>
                             @php $input = "youtube"; @endphp
                            <div class="form-group">
                                <label class="col-lg-2 control-label">رابط قناة اليوتيوب</label>

                                <div class="col-lg-10">
                                    <input type="text" name="{{ $input }}"  value="{{ isset($row) ? $row->{$input} : Request::old($input) }} "
                                      class="form-control">
                                      @error($input)
                                      <span class="invalid-feedback" role="alert">
                                               <strong>{{ $message }}</strong>
                                      </span>
                                      @enderror
                                </div>
                            </div>
                            @php $input = "facebook"; @endphp
                            <div class="form-group">
                                <label class="col-lg-2 control-label">facebook account</label>

                                <div class="col-lg-10">
                                    <input type="text" name="{{ $input }}"  value="{{ isset($row) ? $row->{$input} : Request::old($input) }} "
                                      class="form-control">
                                      @error($input)
                                      <span class="invalid-feedback" role="alert">
                                               <strong>{{ $message }}</strong>
                                      </span>
                                      @enderror
                                </div>
                            </div>
                            @php $input = "twitter"; @endphp
                            <div class="form-group">
                                <label class="col-lg-2 control-label">twitter account</label>

                                <div class="col-lg-10">
                                    <input type="text" name="{{ $input }}"  value="{{ isset($row) ? $row->{$input} : Request::old($input) }} "
                                      class="form-control">
                                      @error($input)
                                      <span class="invalid-feedback" role="alert">
                                               <strong>{{ $message }}</strong>
                                      </span>
                                      @enderror
                                </div>
                            </div>
                            @php $input = "instagram"; @endphp
                            <div class="form-group">
                                <label class="col-lg-2 control-label">instagram account</label>

                                <div class="col-lg-10">
                                    <input type="text" name="{{ $input }}"  value="{{ isset($row) ? $row->{$input} : Request::old($input) }} "
                                      class="form-control">
                                      @error($input)
                                      <span class="invalid-feedback" role="alert">
                                               <strong>{{ $message }}</strong>
                                      </span>
                                      @enderror
                                </div>
                            </div>    


                            <label class="col-md-2 control-label">من نحن</label><br><br>
                            @php $input = "description"; @endphp
                            <div class="panel-body no-padding" >
                                       <textarea class="summernote"style="margin-right: 25%"  name="{{ $input }}"  id="demo"  rows="10" cols="100" >
                                                {{ isset($row) ? $row->{$input} : '' }}
                               </textarea>
                            </div><br><br><br>
                            @error($input)
                            <span class="invalid-feedback" role="alert">
                                     <strong>{{ $message }}</strong>
                            </span>
                            @enderror

                            <label class="col-md-2 control-label">من نحن بالانجليزي</label><br><br>
                            @php $input = "en_description"; @endphp
                            <div class="panel-body no-padding" >
                                       <textarea class="summernote"style="margin-right: 25%"  name="{{ $input }}"  id="demo"  rows="10" cols="100" >
                                                {{ isset($row) ? $row->{$input} : '' }}
                               </textarea>
                            </div><br>
                            @error($input)
                            <span class="invalid-feedback" role="alert">
                                     <strong>{{ $message }}</strong>
                            </span>
                            @enderror --}}


                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <button  class="btn btn-primary" type="submit">تحديث</button>
                                </div>
                            </div>
                        </form>
                    </div>
                {{-- </div> --}}
            </div>
          
            <div class="col-md-3">
                <div class="current-status-widget">
                    <ul>
                        <li>
                            <div class="status-box">
                                <div class="status-box-icon label-light-green white">
                                    <i class="fa fa-shopping-cart"></i>
                                </div>
                            </div>
                            <div class="status-box-content">
                                <h5 id="sale-view">{{$dayOrders}}</h5>
                                <p class="lightGreen"> عدد طلبات اليوم</p>
                            </div>
                            <div class="clearfix"></div>
                        </li>
                        <li>
                            <div class="status-box">
                                <div class="status-box-icon label-light-green white">
                                    <i class="fa fa-dollar"></i>
                                </div>
                            </div>
                            <div class="status-box-content" >
                                <h5 id="sale-view" style="color: red">{{$dayBillDiscount + $dayOrderDiscount}}</h5>
                                <p class="lightGreen" style="color: red">مجموع خصومات اليوم </p>
                            </div>
                            <div class="clearfix"></div>
                        </li>
                        <li>
                            <div class="status-box">
                                <div class="status-box-icon label-light-green white">
                                    <i class="fa fa-dollar"></i>
                                </div>
                            </div>
                            <div class="status-box-content">
                                <h5 id="sale-view">{{$dayOrdersMoney}}</h5>
                                <p class="lightGreen" > فلوس طلبات اليوم</p>
                            </div>
                            <div class="clearfix"></div>
                        </li>
                        <li>
                            <div class="status-box">
                                <div class="status-box-icon label-light-green white">
                                    <i class="fa fa-dollar"></i>
                                </div>
                            </div>
                            <div class="status-box-content">
                                <h5 id="sale-view">{{$dayProductBuyingPrice}}</h5>
                                <p class="lightGreen">فلوس بضاعه هذا اليوم </p>
                            </div>
                            <div class="clearfix"></div>
                        </li>

                        <li>
                            <div class="status-box">
                                <div class="status-box-icon label-light-green white">
                                    <i class="fa fa-dollar"></i>
                                </div>
                            </div>
                            <div class="status-box-content">
                                <h5 id="sale-view">{{$dayOrdersMoney - $dayProductBuyingPrice - $dayBillDiscount}}</h5>
                                <p class="lightGreen"> صافي الربح اليوم</p>
                            </div>
                            <div class="clearfix"></div>
                        </li>

                        <li>
                            <div class="status-box">
                                <div class="status-box-icon label-light-green white">
                                    <i class="fa fa-shopping-cart"></i>
                                </div>
                            </div>
                            <div class="status-box-content">
                                <h5 id="sale-view">{{$dayOrdersReturned}}</h5>
                                <p class="lightGreen"> عدد المرتجعات اليوم</p>
                            </div>
                            <div class="clearfix"></div>
                        </li>
                        <li>
                            <div class="status-box">
                                <div class="status-box-icon label-light-green white">
                                    <i class="fa fa-dollar"></i>
                                </div>
                            </div>
                            <div class="status-box-content">
                                <h5 id="sale-view">{{$dayOrdersReturnedMoney - $dayBillReturned}}</h5>
                                <p class="lightGreen"> فلوس المترجعات اليوم</p>
                            </div>
                            <div class="clearfix"></div>
                        </li>

                        <li>
                            <div class="status-box">
                                <div class="status-box-icon label-light-green white">
                                    <i class="fa fa-shopping-cart"></i>
                                </div>
                            </div>
                            <div class="status-box-content">
                                <h5 id="sale-view">{{$yesterdayOrders}}</h5>
                                <p class="lightGreen"> عدد طلبات الامس</p>
                            </div>
                            <div class="clearfix"></div>
                        </li>
                        <li>
                            <div class="status-box">
                                <div class="status-box-icon label-light-green white">
                                    <i class="fa fa-dollar"></i>
                                </div>
                            </div>
                            <div class="status-box-content">
                                <h5 id="sale-view">{{$yesterdayBillDiscount + $yesterdayOrderDiscount}}</h5>
                                <p class="lightGreen" style="color: red">مجموع خصومات الامس </p>
                            </div>
                            <div class="clearfix"></div>
                        </li>
                        <li>
                            <div class="status-box">
                                <div class="status-box-icon label-light-green white">
                                    <i class="fa fa-dollar"></i>
                                </div>
                            </div>
                            <div class="status-box-content">
                                <h5 id="sale-view">{{$yesterdayOrdersMoney}}</h5>
                                <p class="lightGreen"> فلوس طلبات الامس</p>
                            </div>
                            <div class="clearfix"></div>
                        </li>

                        <li>
                            <div class="status-box">
                                <div class="status-box-icon label-light-green white">
                                    <i class="fa fa-dollar"></i>
                                </div>
                            </div>
                            <div class="status-box-content">
                                <h5 id="sale-view">{{$yesterDayProductBuyingPrice}}</h5>
                                <p class="lightGreen">فلوس بضاعه  امس </p>
                            </div>
                            <div class="clearfix"></div>
                        </li>

                        <li>
                            <div class="status-box">
                                <div class="status-box-icon label-light-green white">
                                    <i class="fa fa-dollar"></i>
                                </div>
                            </div>
                            <div class="status-box-content">
                                <h5 id="sale-view">{{$yesterdayOrdersMoney - $yesterDayProductBuyingPrice}}</h5>
                                <p class="lightGreen"> صافي الربح امس</p>
                            </div>
                            <div class="clearfix"></div>
                        </li>


                        <li>
                            <div class="status-box">
                                <div class="status-box-icon label-light-green white">
                                    <i class="fa fa-shopping-cart"></i>
                                </div>
                            </div>
                            <div class="status-box-content">
                                <h5 id="sale-view">{{$monthOrders}}</h5>
                                <p class="lightGreen">طلبات هذا الشهر</p>
                            </div>
                            <div class="clearfix"></div>
                        </li>
                        <li>
                            <div class="status-box">
                                <div class="status-box-icon label-light-green white">
                                    <i class="fa fa-dollar"></i>
                                </div>
                            </div>
                            <div class="status-box-content">
                                <h5 id="sale-view">{{$monthOrdersMoney}}</h5>
                                <p class="lightGreen"> فلوس طلبات هذا الشهر</p>
                            </div>
                            <div class="clearfix"></div>
                        </li>
                        <li>
                            <div class="status-box">
                                <div class="status-box-icon label-light-green white">
                                    <i class="fa fa-shopping-cart"></i>
                                </div>
                            </div>
                            <div class="status-box-content">
                                <h5 id="sale-view">{{$yearOrders}}</h5>
                                <p class="lightGreen">طلبات هذا السنه</p>
                            </div>
                            <div class="clearfix"></div>
                        </li>
                        <li>
                            <div class="status-box">
                                <div class="status-box-icon label-light-green white">
                                    <i class="fa fa-dollar"></i>
                                </div>
                            </div>
                            <div class="status-box-content">
                                <h5 id="sale-view">{{$yearOrdersMoney}}</h5>
                                <p class="lightGreen"> فلوس طلبات هذا السنه</p>
                            </div>
                            <div class="clearfix"></div>
                        </li>
                        <li>
                            <div class="status-box">
                                <div class="status-box-icon label-light-green white">
                                    <i class="fa fa-tshirt"></i>
                                </div>
                            </div>
                            <div class="status-box-content">
                                <h5 id="sale-view">{{$expiredProduct}}</h5>
                                <p class="lightGreen"> عدد المنتجات خلصانه</p>
                            </div>
                            <div class="clearfix"></div>
                        </li>
                       
                    </ul>
                </div>
        
            </div>
            
        
    </div>

    {{-- @component('back-end.shared.edit' , ['pageTitle' => $pageTitle , 'pageDes' => $pageDes])
        <form action="{{ route($routeName.'.update' , ['id' => $row]) }}" method="POST">
            {{ method_field('put') }}
            @include('back-end.'.$folderName.'.form')
            <button type="submit" class="btn btn-primary pull-right">Update {{ $moduleName }}</button>
            <div class="clearfix"></div>
        </form>
    @endcomponent --}}


    {{-- @push('js')
     <!--Masked Library Script Start-->
     <script src="{{asset('panel/assets/js/jquery.maskedinput.min.js')}}"></script>
     <script src="{{asset('panel/assets/js/jquery.autosize.js')}}"></script>
     <!--Masked Library Script End-->

     <!--validationEngine Library Script Start-->
     <script src="{{asset('panel/assets/js/validationEngine/languages/jquery.validationEngine-en.js')}}"></script>
     <script src="{{asset('panel/assets/js/validationEngine/jquery.validationEngine.js')}}"></script>
     <!--validationEngine Library Script End-->

     <!--bootstrap validation Library Script Start-->
     <script src="{{asset('panel/assets/js/bootstrapvalidator/bootstrapValidator.js')}}"></script>
     <!--bootstrap validation Library Script End-->

     <!--Demo form validation  Script Start-->
     <script src="{{asset('panel/assets/js/pages/formValidation.js')}}"></script>
     <!--Demo form validation  Script End-->
    @endpush

   @push('css')
   <link rel="stylesheet" href="assets/css/rtl-css/plugins/fileinput-rtl.css">
   @endpush
    --}}
    @push('css')
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
           console.log("test");
            document.getElementById('date').valueAsDate = new Date();
        </script>
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
    <!-- Demo Ck Editor Script For Layout ENd-->
    @endpush
@endsection
