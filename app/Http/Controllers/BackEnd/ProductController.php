<?php
namespace App\Http\Controllers\BackEnd;
use Illuminate\Http\Request;
use App\Models\{Product ,Order , Bill};
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Exports\ProductsExport;
use Maatwebsite\Excel\Facades\Excel;
use DNS1D;
use Image ,DB;
use PDF;
class ProductController extends BackEndController
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }
  
    public function store(Request $request){

        $requestArray = $request->all();
        $total =$request->purchasing_price * $request->quantity;
        $requestArray['total_price'] =$total;
        $requestArray['user_id'] = Auth::user()->id;
        $requestArray['code'] =  $this->generateRandomNumber(5);
        while( $this->checkNumber( $requestArray['code'] )  ) {
            $requestArray['code'] =  $this->generateRandomNumber(5);
        }
        $this->model->create($requestArray);
        session()->flash('action', 'تم الاضافه بنجاح');

        return redirect()->route($this->getClassNameFromModel().'.index');
    }

    public function show($code)
    {
        $row =  $this->model->where('code',$code)->get(['name','selling_price','quantity'])->first();
        if(isset($row)){
           $message = " ";

        }
        else
        {
            $message = "لا يوجد";

        }
        return response()->json([
            'product' => $row,
            'message' => $message,
        ]);
    }
    public function update($id , Request $request){



        $row = $this->model->FindOrFail($id);
        $requestArray = $request->all();
        $total =$request->purchasing_price * $request->quantity;
        $requestArray['total_price'] =$total;
      
        $requestArray['user_id'] = Auth::user()->id;
        $row->update($requestArray);


        session()->flash('action', 'تم التحديث بنجاح');
        return redirect()->route($this->getClassNameFromModel().'.index');
    }

    public function destoryRelationShip($id)
    {
        // Order::where('product_id' , $id)->update([
        //         'product_id' => null
        // ]);
        // Bill::where('product_id' , $id)->update([
        //     'product_id' => null
        // ]);
        return ; 
    }
    function generateRandomNumber($length)
    {
        $str = rand(0, 9); // first number (0 not allowed)
        for ($i = 1; $i < $length; $i++)
            $str .= rand(0, 9);

        return $str;
    }
    public function printBarCode($productId)
    {
        
        $product=Product::find($productId);
        
        if(isset($product))
        return view('back-end.products.printBarCode' , compact('product'));
    }
    public function appendIndex($rows)
    {
        // $products = $this->model->get();  
        $data['productsCount'] =   count($rows);
        $data['totalQuantity'] =   $rows->sum('quantity');
        $data['totalBuyCost'] =   Product::value(DB::raw("SUM(quantity * purchasing_price )"));
        $data['totalSellCost'] =   Product::value(DB::raw("SUM(quantity * selling_price )")) ; 
        return $data;
    }
    public function checkNumber($code)
    {
        $shippingCard = $this->model->where('code' , $code)->first();
        if($shippingCard){
            return true;
        }
        else
        return false;
    }
    public function filter($rows)
    {
        if(request('quantity'))
        $rows = $rows->where('quantity' ,'<', request('quantity'));
        return  $rows;
    }

    public function excelExportSheet()
    {
        
        return Excel::download(new ProductsExport, 'products.xlsx');
    }
    public function exportPDF()
    {
      ini_set("memory_limit", "10056M");
        $rows = $this->model;
        $rows = $rows->orderBy('id', 'DESC')
        ->whereYear('created_at' ,  date('Y'))
        // ->take(400)
        ->get();
        // ->paginate(400);
        //  return "test";
        // return $rows;
        $data = [
			'rows' => $rows
		];

		$pdf = PDF::loadView('back-end.products.pdf', $data);
        // return PDF::loadView('print', $data)->download('print.pdf');
        // return view('back-end.products.pdf' , $data );
		// return $pdf->stream('back-end.products.pdf');
        // $pdf->Output("test", 'D');
        // $pdf->Output('yourFileName.pdf', 'I');
        return $pdf->download('منتجات');
    }
}
