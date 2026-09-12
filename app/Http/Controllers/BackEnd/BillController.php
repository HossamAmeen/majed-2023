<?php

namespace App\Http\Controllers\BackEnd;


use App\Models\Bill;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillController extends BackEndController
{
    public function __construct(Bill $model)
    {
        parent::__construct($model);
    }

    public function store(Request $request){
        $requestArray = $request->all();
        $requestArray['user_id'] = Auth::user()->id;
        $data = [];
        if(!is_array($request->products)){
            return response(['error'=>"لا يوجد منتجات في هذه الفاتوره"] , 400);
        }
        $bill = $this->model->create($requestArray);
        for($i=0; $i<count($request->products) ; $i++){
            if($request->products[$i] == null || $request->products[$i] == 'null'){
                continue ;
            }
            $product = Product::where('code',$request->products[$i])->first();
            if(!isset($product)){
                $this->destroy($bill->id);
                return response(['error'=>"كود هذا المنتج غير صالح " .$request->products[$i] ] , 400);
                // return json_encode(['id' => $bill->id] , 400);
                return redirect()->back()->withErrors(['errorProduct' => "كود هذا المنتج غير صالح " .$request->products[$i] ])->withInput();
            }
            $price = $request->costs[$i] ?? $product->selling_price  ;

            // Cast explicitly: PHP 8 is strict about arithmetic on non numeric input.
            $quantity = (float) ($request->quantity[$i] ?? 1);
            if($product->quantity < $quantity){
                $this->destroy($bill->id);
                return response(['error'=> "هذا المنتج($product->name) اقل من الكمية المطلوبة او ناقص"] , 400);
                return json_encode(['id' => $bill->id],400);
                return redirect()->back()->withErrors(['errorProduct' => "هذا المنتج اقل من الكمية المطلوبة او ناقص" .$product->name ])->withInput();
            }
            else
            {
                $product->quantity -= $quantity ;
                $product->save(); 
            }
            array_push($data, [
                'product_price'=> $product->selling_price,
                'price'=> $price,
                'product_name'=>  $product->name,
                'quantity'=>$quantity,
                'date'=>date('Y-m-d'),
                'discount'=>$request->discounts[$i]?? 0,
                'user_id'=>Auth::user()->id,
                'product_id'=>$product->id,
                'bill_id'=>$bill->id,
            ]);
            
            
        }
        Order::insert($data);
        $bill = Bill::where('id', $bill->id)->with('orders')->first();
        return view('back-end.bills.bill2' , compact('bill'));
        return response(['id' => $bill->id], 200);
        return json_encode(['id' => $bill->id]);
        return redirect()->back()->withInput();
        return redirect()->route($this->getClassNameFromModel().'.index');
    }

    public function update($id , Request $request){
        $row = $this->model->FindOrFail($id);
        $requestArray = $request->all();
        $requestArray['user_id'] = Auth::user()->id;
        $row->update($requestArray);
        // return $request->orders;
        if(is_array($request->orders))
        for($i=0; $i<count($request->orders) ; $i++){
           
           
            $order = Order::where('id' ,$request->orders[$i] )->first();

            // Cast explicitly: PHP 8 is strict about arithmetic on non numeric input.
            $quantity = (float) ($request->quantity[$i] ?? 0);

            $product = Product::find($order->product_id);
            if(!isset($product)){
                $requestArray['code'] =  $this->generateRandomNumber(5);
                while( $this->checkNumber( $requestArray['code'] )  ) {
                    $requestArray['code'] =  $this->generateRandomNumber(5);
                }
            
                $product = Product::create([
                    'name'=>$order->product_name,
                    'code'=> $requestArray['code'],
                    'quantity'=>abs($order->quantity - $quantity ),
                    'user_id'=> Auth::user()->id
                ]);
               
            }
            else
            {
                if(($product->quantity + $order->quantity ) < $quantity){
                    return redirect()->back()->withErrors(['errorProduct' => " هذا المنتج اقل من الكمية المطلوبة او ناقص "  .$product->name ])->withInput();
                }
                else
                {
                    $product->quantity = ($product->quantity + $order->quantity ) - $quantity ;
                    $product->save(); 
                }
               
            }
            if($quantity == 0){
                $order->delete();
            }
            else
                $order->update(['quantity' =>$quantity , 'product_id'=> $product->id]);
        }


        session()->flash('action', 'تم التحديث بنجاح');
        return redirect()->route($this->getClassNameFromModel().'.index');
    }

    public function destroy($id)
    {
        $this->model->FindOrFail($id)->delete();
        $orders = Order::where('bill_id' , $id)->get();
        foreach($orders as $order){
            $product = Product::find($order->product_id);
            if(isset($product)){
                $product->quantity += $order->quantity;
                $product->save();
            }
            else
            {
                // $requestArray['code'] =  $this->generateRandomNumber(5);
                // while( $this->checkNumber( $requestArray['code'] )  ) {
                //     $requestArray['code'] =  $this->generateRandomNumber(5);
                // }
            
                // Product::create([
                //     'name'=>$order->product_name,
                //     'code'=> $requestArray['code'],
                //     'quantity'=>$order->quantity,
                //     'user_id'=> Auth::user()->id
                // ]);
                $product = Product::withTrashed()->where('id',$order->product_id)->first();
                $product->quantity = $order->quantity;
                $product->restore();
                $product->save();
                // return $product;
            }
            $order->delete();
        }
        
        return redirect()->back();
        
        // return redirect()->route($this->getClassNameFromModel() . '.index', );
    }
    public function bill()
    {
        $data['rows']=Bill::all();
        return view('back-end.bills.bill');
    }

    public function printBill($id)
    {
      
        $bill=Bill::with('orders')->where('id',$id)->first();
       if(isset($bill))
       return view('back-end.bills.bill' , compact('bill'));
    }
    public function printBill2($id)
    {
      
        $bill=Bill::with('orders')->where('id',$id)->first();
       if(isset($bill))
       return view('back-end.bills.bill2' , compact('bill'));
    }
    function generateRandomNumber($length)
    {
        $str = rand(0, 9); // first number (0 not allowed)
        for ($i = 1; $i < $length; $i++)
            $str .= rand(0, 9);

        return $str;
    }

    public function checkNumber($code)
    {
        $shippingCard = Product::where('code' , $code)->first();
        if($shippingCard){
            return true;
        }
        else
        return false;
    }
    public function with()
    {
        return ['orders'];
    }
    public function filter($rows)
    {
        if(request('dateSearch') != null)
        {
            // $dateSearch = Carbon::createFromFormat('Y-m-d', request('dateSearch'));
            $dateSearch = request('dateSearch');
            $rows = $rows->whereYear('created_at' ,'>=',  $dateSearch );
        }
        
        return $rows;
    }
    public function search()
    {
        $bills = Bill::with('orders')->where('name', 'like', '%' . request('search') . '%')->get();
        return response()->json([
            'message' => $bills,
        ]);
    }
}
