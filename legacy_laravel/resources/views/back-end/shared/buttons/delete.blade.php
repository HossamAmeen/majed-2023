
@if( Auth::user()->role == 1 )
<form action="{{ route($routeName.'.destroy' , ['id' => $item]) }}" method="post">
    {{ csrf_field() }}
    {{ method_field('delete') }}
    <a href="{{ route($routeName.'.edit' , ['id' => $item]) }}" rel="tooltip" title="" class="btn btn-xs btn-info"
        data-original-title="Edit
        {{ $sModuleName }}">
        <i class="fa fa-pencil-square-o"></i>
    </a>

  {{-- <button  rel="tooltip" title="" class="btn btn-xs btn-info"  onclick="printContent($item->id)"
        data-original-title="Print {{ $sModuleName }}">
        <i class="fa fa-minus-square" ></i>
    </button>
     <button type="submit" rel="tooltip" title="" class="btn btn-danger" onclick="check()"
        data-original-title="Remove {{ $sModuleName }}">
        <i class="fa fa-minus-square"  aria-hidden="true"></i>
    </button> --}}
    <button type="submit" rel="tooltip" title=""  onclick="check()" class="btn btn-xs btn-danger"><i
        class="fa fa-minus"></i></button>
        @yield('moreButton')
</form>
@endif
