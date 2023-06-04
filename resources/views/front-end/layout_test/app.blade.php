<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">

  {{-- <title>{{$brefs->title}} - {{$pageTitle}}</title> --}}
  <title> {{ config('app.name' . $pageTitle , $brefs->title .'-'. $pageTitle) }}</title>
  <!-- Favicon -->
  <link rel="icon" href="{{asset('web/img/favicon.png')}}" sizes="16x16" type="image/png">
  <!-- Bootstrap core CSS -->
  <link href="{{asset('web/vendor/bootstrapRTL/css/bootstrap-rtl.min.css')}}" rel="stylesheet">
  <!-- fontawesome -->
  <link href="{{asset('web/vendor/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet">
    <!-- Light Box Master -->
    <link href="{{asset('web/vendor/lightbox2-master/css/lightbox.min.css')}}" rel="stylesheet">
  <!-- Animate css -->
  <link href="{{asset('web/vendor/wow/animate.css')}}" rel="stylesheet">

  <!-- Custom styles for this template -->
  <link href="{{asset('web/css/style.css')}}" rel="stylesheet">
  <!-- Media Queries -->
  <link rel="stylesheet" href="{{asset('web/css/media-query.css')}}">

</head>

<body>
       <!-- Navigation -->
        <nav class="topHead"></nav>
        <nav class="navbar mainNav navbar-expand-lg">
            <div class="container">
              <a class="navbar-brand" href="{{route('index')}}">
                <img src="{{asset('web/img/logo.png')}}" alt="Logo">
              </a>
              <button class="navbar-toggler navbar-toggler-left ml-auto" type="button" data-toggle="collapse"
                data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="fa fa-bars"></span>
              </button>
              <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav ml-auto">
                  <li class="nav-item">
                    <a class="nav-link {{is_active_web('index')}}" href="{{route('index')}}">الرئيسية</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link {{is_active_web('about')}} " href="{{route('about')}}">عن المشروع</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link {{is_active_web('news')}}" href="{{route('news')}}">الأخبار</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link {{is_active_web('timeline')}}" href="{{route('timeline')}}">الجدول الزمنى</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link {{is_active_web('gallery')}}" href="{{route('gallery')}}">معرض الصور</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link {{is_active_web('faq')}}" href="{{route('faq')}}">الأسئلة الشائعة</a>
                  </li>
                </ul>
              </div>
            </div>
          </nav>

        <div class="social-media-box">
        <ul>
          @if(isset($brefs->facebook) )
          <li>
          <a href="{{$brefs->facebook}}" target="_blank"><i class="fa fa-facebook"></i></a>
          </li>
          @endif
          @if(isset($brefs->phone) )
          <li>
          <a href="tel:{{$brefs->phone}}" target="_blank"><i class="fa fa-phone"></i></a>
          </li>
          @endif
          @if(isset($brefs->instagram) )
          <li>
          <a href="{{$brefs->instagram}}" target="_blank"><i class="fa fa-instagram"></i></a>
          </li>
          @endif
          @if(isset($brefs->phone2) )
          <li>
          <a href="https://wa.me/{{$brefs->phone}}" target="_blank"><i class="fa fa-whatsapp"></i></a>
          </li>
          @endif
        
        </ul>
        </div>
        <!-- Page Content -->
          @yield('content')
  <!-- Footer -->
  <footer class="pt-5 pb-3">
      <div class="container">
        <div class="row align-items-baseline">
          <div class="col-lg-5 col-12 text-center">
            <img src="{{asset('web/img/logo.png')}}" width="100" class="img-fluid mb-3" alt="Footer Logo">
            <p class="text-white">
              مشروع إنشاء اكبرمجمع طبي تخصصى بوسط مدينة أسيوط احدى مشروعات مجموعة ( استثمر) للاقتصاد التشاركي
            </p>
          </div>
          <div class="col-lg-3 col-md-6 col-12">
            <h4 class="text-white mb-3">روابط هامة</h4>
            <ul class="text-white">
              <li>
                <a class="text-white" href="{{route('index')}}">الرئيسية</a>
              </li>
              <li>
                <a class="text-white" href="{{route('about')}}">عن المشروع</a>
              </li>
              <li>
                <a class="text-white" href="{{route('news')}}">الأخبار</a>
              </li>
              <li>
                <a class="text-white" href="{{route('timeline')}}">الجدول الزمنى</a>
              </li>
              <li>
                <a class="text-white" href="{{route('gallery')}}">معرض الصور</a>
              </li>
              <li>
                <a class="text-white"href="{{route('faq')}}">الأسئلة الشائعة</a>
              </li>
            </ul>
          </div>
          <div class="col-lg-4 col-md-6 col-12">
            <h4 class="text-white mb-3">تواصل معنا</h4>
            <p class="text-white">
              <i class="fa fa-map-marker m-1"></i>
              @if(isset($brefs->address))
              <span>المقر الأدارى : {{ $brefs->address}}</span>
              @endif
            </p>
            @if(isset($brefs->phone))
            <a href="tel:{{$brefs->phone}}" class="text-white d-block">
              <i class="fa fa-phone border py-1 px-2 mb-1 rounded-circle"></i>
              {{ $brefs->phone  }}
            </a>
            @endif
           
          
            @if(isset($brefs->email))
            <p href="javascript:void(0);" class="text-white d-block">
              <i class="fa fa-envelope-o px-2"></i>
              {{ $brefs->email  }}
            </p>
            @endif
          </div>
          <div class="col-12 text-center border-top mt-2">
            <p class="text-white mt-2">كل الحقوق محفوظة لمستشفى صحتك &copy; 2019 | صمم بواسطة
               <a href="http://www.z-edy.com/" class="text-blue">Zedy</a></p>
          </div>
        </div>
      </div>
      <!-- /.container -->
    </footer>
  
  <!-- Bootstrap core JavaScript -->
  <script src="{{asset('web/vendor/jquery/jquery.min.js')}}"></script>
  <script src="{{asset('web/vendor/bootstrapRTL/js/bootstrap.bundle.min.js')}}"></script>
  <script src="{{asset('web/vendor/sticky/jquery.sticky.js')}}"></script>
  <script src="{{asset('web/vendor/lightbox2-master/js/lightbox.min.js')}}"></script>
  <script src="{{asset('web/vendor/wow/wow.min.js')}}"></script>
  <script src="{{asset('web/js/scripts.js')}}"></script>

</body>

</html>
