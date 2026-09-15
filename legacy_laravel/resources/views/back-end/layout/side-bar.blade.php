<section id="left-navigation">
    <!--Left navigation user details start-->
    <div class="user-image">
        <img class='img-responsive'
            {{-- src="{{asset( isset(Auth::user()->image) ? Auth::user()->image : 'panel/assets/images/demo/avatar-80.png')}}" --}}
            src="{{asset('4md.jpg')}}"
            alt="" width="100" height="100" />
        <div class="user-online-status"><span class="user-status is-online  "></span> </div>
    </div>
    <ul class="social-icon">
        {{-- <li><a href="javascript:void(0)"><i class="fa fa-facebook"></i></a></li>
        <li><a href="javascript:void(0)"><i class="fa fa-twitter"></i></a></li>
        <li><a href="javascript:void(0)"><i class="fa fa-github"></i></a></li>
        <li><a href="javascript:void(0)"><i class="fa fa-bitbucket"></i></a></li> --}}
    </ul>
    <!--Left navigation user details end-->

    <!--Phone Navigation Menu icon start-->
    <div class="phone-nav-box visible-xs">
        <a class="phone-logo" href="index.html" title="">
            <h1>{{$briefs->name}}</h1>
        </a>
        <a class="phone-nav-control" href="javascript:void(0)">
            <span class="fa fa-bars"></span>
        </a>
        <div class="clearfix"></div>
    </div>

    <!--Phone Navigation Menu icon start-->

    <!--Left navigation start-->
    <!--Left navigation start-->
    <ul class="mainNav">
        @if( Auth::user()->role == 1 || Auth::user()->role == 2 )
        <li>
            <a href="{{route('configrations.edit' , ['id' => '1'])}}" class="{{is_active('configrations')}}">
                <i class="fas fa-edit"></i><span>    احصائيات السستم                </span>
            </a>
        </li>
        @endif
        <li>
            <a href="{{route('edit-account')}}" id="edit-account">
                <i class="fas fa-edit"></i><span>تعديل بيانات الحساب</span>
            </a>
        </li>
        @if( Auth::user()->role == 1 )
        <li>
            <a id="users" href="{{route('users.index')}}" class="{{is_active('users')}}">
                <i class="fa fa-group"></i><span>المستخدمين</span>
            </a>
        </li>
        <li>
            <a id="products" href="{{route('products.index')}}" class="{{is_active('products')}}">
                <i class="fas fa-tshirt	"></i><span>المنتجات</span>
            </a>
        </li>
        @endif
        <li>
            <a id="orders" href="{{route('orders.index')}}" class="{{is_active('orders')}}">
                <i class="fas fa-shopping-cart"></i><span>الطلبات</span>

            </a>
        </li>
        <li>
            <a id="bills" href="{{url('admin/bills?dateSearch='.date("Y").'&limit=500')}}" class="{{is_active('bills')}}">
                <i class="fas fa-envelope-open-text"></i><span>الفواتير</span>
            </a>
        </li>
        <li>
            <a id="bills" href="{{route('notes.index')}}" class="{{is_active('notes')}}">
                <i class="fas fa-pencil-square-o"></i><span>ملحوظات</span>
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();
                          document.getElementById('logout-form').submit();">
                <i class="fa fa-power-off"></i><span>تسجيل الخروج</span>
            </a>
        </li>
    </ul>
    <!--Left navigation end-->
</section>