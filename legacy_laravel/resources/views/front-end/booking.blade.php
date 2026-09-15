@extends('front-end.ar.layout.app')
@section('content')
	
	
	<!--==================================================================== 
			Start Booking
	   =====================================================================-->
	
	<div class="booking">
		<div class="container">
			<div class="page-header text-center">
				<h1>احجز الان</h1>
			
			</div>
		</div>
	</div>
	
	<div class="booking-form text-right">
		<div class="container">
				@if ($errors->any())
				<div class="alert alert-danger">
					<ul>
						@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
			@endif
			{{-- @if (!isset(session('status'))) --}}
			@if ( session('status') != null )
			  <div class="alert alert-success">
				{{session('status')}}
			  </div>
			  @endif
			<form action="{{url('ar/booking')}}" method="POST">
				@csrf
			  <div class="form-row">
				<div class="form-group col-md-6">
				  <label for="inputEmail4">الاسم</label>
				  <input type="text" name="name" class="form-control" id="inputEmail4" required>
				</div>
				<div class="form-group col-md-6">
				  <label for="inputPassword4">رقم الموبيل</label>
				  <input type="text" name="phone" class="form-control" id="inputPassword4" required>
				</div>
			  </div>
				<div class="form-group">
					  <textarea name="note" class="form-control" id="exampleFormControlTextarea1" rows="6" placeholder="ملاحظات" required></textarea>
				  </div>
				  <button type="submit" class="btn btn-primary sign">احجز الان</button>
				 
		  </form>
		</div>
	</div>
@endsection