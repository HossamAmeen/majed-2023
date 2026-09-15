@extends('front-end.layout.app')
@section('content')
  <header>
    <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
      <ol class="carousel-indicators">
        <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
        <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
        <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
      </ol>
      <div class="carousel-inner" role="listbox">
        <!-- Slide One - Set the background image for this slide in the line below -->
        <div class="carousel-item active" style="background-image: url('{!! asset('web/img/slider-img1.png') !!}')">
          <div class="overlay"></div>
                <div class="slide-content">
                  <h3 class="wow bounceInUp">لماذا مشروع صحتك</h3>
                  <p class="wow bounceInUp">التكلفة الإجمالية للمجمع بجميع التجهيزات 50000000 جنيه مصري ( خمسون مليون جنيه
                    مصري) وتشمل تكلفة شراء
                    العقار الخاص بالمجمع وتكلفة كافة التجهيزات و شراء الأجهزة الطبية والمبلغ قابلة للزيادة والنقصان في حالة
                    تغير أسعار التجهيزات الخاصة بالمجمع</p>
                </div>
        </div>
        <!-- Slide Two - Set the background image for this slide in the line below -->
        <div class="carousel-item " style="background-image: url('{!! asset('web/img/slider-img1.png') !!}')">
          <div class="overlay"></div>
                <div class="slide-content">
                  <h3 class="wow bounceInUp">لماذا مشروع صحتك</h3>
                  <p class="wow bounceInUp">التكلفة الإجمالية للمجمع بجميع التجهيزات 50000000 جنيه مصري ( خمسون مليون جنيه
                    مصري) وتشمل تكلفة شراء
                    العقار الخاص بالمجمع وتكلفة كافة التجهيزات و شراء الأجهزة الطبية والمبلغ قابلة للزيادة والنقصان في حالة
                    تغير أسعار التجهيزات الخاصة بالمجمع</p>
                </div>
        </div>
        <!-- Slide Three - Set the background image for this slide in the line below -->
        <div class="carousel-item " style="background-image: url('{!! asset('web/img/slider-img1.png') !!}')">
          <div class="overlay"></div>
                <div class="slide-content">
                  <h3 class="wow bounceInUp">لماذا مشروع صحتك</h3>
                  <p class="wow bounceInUp">التكلفة الإجمالية للمجمع بجميع التجهيزات 50000000 جنيه مصري ( خمسون مليون جنيه
                    مصري) وتشمل تكلفة شراء
                    العقار الخاص بالمجمع وتكلفة كافة التجهيزات و شراء الأجهزة الطبية والمبلغ قابلة للزيادة والنقصان في حالة
                    تغير أسعار التجهيزات الخاصة بالمجمع</p>
                </div>
        </div>
      </div>
    </div>
  </header>
  {{-- {{ isset($brefs->description) ? $brefs->description : '' }} --}}

  <!-- Page Content -->
  <section class="section-dark info-section">
    <div class="container">
      <div class="row">
        <div class="col-lg-6 col-xs-12 my-auto wow bounceInUp">
          <h2 class="text-center mb-4 text-blue">مقدمة عن المشروع</h2>
          <p class="text-white">

              {{ isset($brefs->description) ? $brefs->description : '' }}
              </p>
        </div>
        <div class="col-lg-6 col-xs-12 wow bounceInUp">
          <iframe width="100%" height="315" src=" {{ isset($brefs->video) ? $brefs->video : '' }}" frameborder="0"
            allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
      </div>
    </div>
  </section>
  <section class="why-section">
    <div class="container-fluid">
      <div class="row wow bounceInUp">
        <div class="col px-0 py-1">
          <h2>لماذا مشروع صحتك</h2>
        </div>
      </div>
    </div>
    <div class="container-fluid pt-3 pb-5">
      <div class="row">
        <div class="col-lg-3 col-6 wow bounceInUp">
          <div class="box-info">
            <img src="{{asset('web/img/money.png')}}" class="img-fluid" alt="عائد ربح سنوى">
            <h5 class="mt-4">عائد ربح سنوى</h5>
          </div>
        </div>
        <div class="col-lg-3 col-6 wow bounceInUp mb-2">
          <div class="box-primary">
            <img src="{{asset('web/img/partner.png')}}" class="img-fluid" alt="شريك فى العقار">
            <h5 class="mt-4">شريك فى العقار</h5>
          </div>
        </div>
        <div class="col-lg-3 col-6 wow bounceInUp mb-2">
          <div class="box-info">
            <img src="{{asset('web/img/stethoscope.png')}}" class="img-fluid" alt="مظلة طبية">
            <h5 class="mt-4">مظلة طبية</h5>
          </div>
        </div>
        <div class="col-lg-3 col-6 wow bounceInUp mb-2">
          <div class="box-primary">
            <img src="{{asset('web/img/Installment.png')}}" class="img-fluid" alt="بالتقسيط">
            <h5 class="mt-4">بالتقسيط</h5>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="news-section">
    <div class="container-fluid">
      <div class="row">
        <div class="col px-0 py-1">
          <h2>أخبار المشروع</h2>
        </div>
      </div>
    </div>
    <div class="container pt-3 pb-5">
      <div class="row">
        @foreach ($news as $item)

        <div class="col-lg-4 col-sm-6 mx-auto news-item wow bounceInUp">
            <div class="card h-100">
                <a href="{{route('news' ,  $item->id ) }}"><img class="card-img-top" src="{{asset('uploads/news/'.$item->image)}}" alt="" height="347" width="283"></a>
              <div class="card-body">
                <h3><a class="text-center text-primary" href="{{route('news' ,  $item->id ) }}">{{ $item->title }}</a></h3>
                <small class="d-block text-muted mb-2">{{ $item->date }}</small>
                <!-- <strong class="card-text text-primary">{{ $item->ar_sub_des  }}</strong> -->
  <strong class="card-text text-primary">	{{ \Illuminate\Support\Str::limit($item->ar_sub_des, 150 ) }}</strong>
              </div>
            </div>
          </div>
        @endforeach
        <div class="col-12 wow bounceIn">
          <a href="{{route('news')}}"
            class="more-btn btn btn-lg btn-light rounded-pill text-primary font-weight-bold">المزيد</a>
        </div>
      </div>
    </div>
  </section>

  <section class="timeline-section">
    <div class="container-fluid">
      <div class="row">
        <div class="col px-0 py-1">
          <h2>الجدول الزمنى</h2>
        </div>
      </div>
    </div>
    <div class="container-fluid pt-3 pb-5">
      <div class="row">
        <div class="col-md-12 col-8 px-0">
          <div class="timeline-item wow bounceInUp">
            <div class="container">
              <div class="row align-items-baseline">
                <div class="col-md-1 col-3">
                  <img src="{{asset('web/img/timeline-img1.png')}}" class="img-fluid" alt="">
                </div>
                <div class="col-md-11 col">
                  <h5 class="text-white">ستة اشهر لتسويق المشروع</h5>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-12 col-10 px-0">
          <div class="timeline-item wow bounceInUp">
            <div class="container">
              <div class="row align-items-baseline">
                <div class="col-md-1 col-3">
                  <img src="{{asset('web/img/timeline-img2.png')}}" class="img-fluid" alt="">
                </div>
                <div class="col-md-11 col">
                  <h5 class="text-white">سنة ونصف لتجهيز المشروع وبداية التشغيل</h5>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-12 col-12 px-0">
          <div class="timeline-item wow bounceInUp">
            <div class="container">
              <div class="row align-items-baseline">
                <div class="col-md-1 col-3">
                  <img src="{{asset('web/img/timeline-img3.png')}}" class="img-fluid" alt="">
                </div>
                <div class="col-md-11 col">
                  <h5 class="text-white">عام على المشروع لعمل تقييم شامل للأرباح وتوزيعها على المساهمين</h5>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="gallery-section">
    <div class="container-fluid">
      <div class="row">
        <div class="col px-0 py-1">
          <h2>صور من أرض الواقع</h2>
        </div>
      </div>
    </div>
    <div class="container pt-3 pb-5">
      <div class="row">
        <div class="col-lg-7 mb-3 wow bounceInUp">
          <div id="carouselGallery" class="carousel slide" data-ride="carousel">
            <ol class="carousel-indicators">
              <li data-target="#carouselGallery" data-slide-to="0" class="active"></li>
              <li data-target="#carouselGallery" data-slide-to="1"></li>
              <li data-target="#carouselGallery" data-slide-to="2"></li>
            </ol>
            <div class="carousel-inner" role="listbox">
              <!-- Slide One - Set the background image for this slide in the line below -->
              <div class="carousel-item active" style="background-image: url('{!! asset('uploads/galleries/'.$image->image) !!}">

              </div>
            @foreach($images as $image)
              <!-- Slide Two - Set the background image for this slide in the line below -->
              <div class="carousel-item" style="background-image:  url('{!! asset('uploads/galleries/'.$image->image) !!}">
              </div>
            @endforeach
            </div>
            <a class="carousel-control-prev" href="#carouselGallery" role="button" data-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carouselGallery" role="button" data-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="sr-only">Next</span>
            </a>
          </div>
        </div>
        <div class="col-lg-5 mb-3 wow bounceInUp">
          <iframe width="100%" height="300" src=" {{ isset($brefs->video) ? $brefs->video : '' }}" frameborder="0"
            allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
      </div>
    </div>
  </section>

  <section class="twoGrid-section">
    <div class="container-fluid  px-0">
      <div class="row no-gutters">
        <div class="col-12 box-primary d-lg-none d-block">
          <h3 class="text-white text-center">الاقتصاد التشاركي</h3>
        </div>
        <div class="col-lg-8 box-info px-5">
          <h3 class="text-white text-center mb-5 d-lg-block d-none wow bounceIn">الأقتصاد الأشتراكى</h3>
          <p class="text-white wow bounceIn">نظام الاقتصاد التشاركي التعاوني Sharing Economy هو نظام اقتصادي (ذو طابع اجتماعي) يقوم
            على - مشاركة الأصول
            بين الأفراد عن طريق أسهم بقيم متوسطة ذات عائد ربح مرتفع ويهدف إلى تشجيع الافراد على الإبداع في خلق فرص ربح
            جديدة بطرق غير تقليدية - استخدام الموارد والأصول فى مشروعات اقتصادية بدراسات جدوى علمية و بشكل تشاركي بين
            الافراد .</p>
        </div>
        <div class="col-lg-4">
          <img src="{{asset('web/img/coEconomy.png')}}" class="img-fluid" alt="coEconomy">
        </div>
      </div>
    </div>
  </section>

  @endsection
