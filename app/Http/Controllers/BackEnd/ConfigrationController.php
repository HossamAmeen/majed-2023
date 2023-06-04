<?php

namespace App\Http\Controllers\BackEnd;
use App\Models\User;
use App\Models\Configration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\{Order,Product,Bill};
use Illuminate\Support\Facades\Response;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
class ConfigrationController extends BackEndController
{
    public function __construct(Configration $model)
    {
        parent::__construct($model);
    }

    public function index()
    {
        // $yesterdayOrders= Order::where('status' , 'sold-out')
        // ->whereYear('date', now()->year)
        // ->whereMonth('date', now()->month)
        // ->whereDay('date', date('d',strtotime("-1 days")))
        // ->get();
        // return $yesterdayOrders->pluck('product_id');
       
        return redirect()->route("configrations.edit", ['id' => 1]);
    }
    public function edits($id)               /////////// for test (remove s from name)
    {
       
        // $dayOrders= Order::with(['product'])
        // ->where('status' , 'sold-out')
        // ->whereYear('date', now()->year)
        // ->whereMonth('date', now()->month)
        // ->whereDay('date', now()->day)
        // ->get();
        

        // // return $dayOrders->product; 
        // $dayProductBuyingPrice = $dayOrders->sum(function($t){ 
        // return $t->quantity * $t->product->purchasing_price; 
        // });

        // return  $dayProductBuyingPrice ;
        // $amount = Order::select(DB::raw('sum(quantity * product_price) as total , sum(quantity + product_price) as totals ') )->get();
        // return $amount ;
        // return $dayOrders ; 
        // $amount = Transaction::all()->sum(function($t){ 
        //     return $t->jumlah * $t->harga; 
        // });
        //  $dayProductBuyingPrice =0;
        // // $productArray = $dayOrders->get(['product_id' , "quantity"]) ; 
        // // return $productArray;
        // // for($i=0;$i<count($dayOrders) ; $i++){
        // //     $dayProductBuyingPrice+=Product::find($dayOrders[$i])->purchasing_price;
        // // }
        // foreach($dayOrders as $item){
        //    $product =  Product::find($item->product_id);
        //     if(isset($product))
        //     {

        //     }
        //     $dayProductBuyingPrice+= $product->purchasing_price ; 
        // }
    }
    public function update(Request $request, $id)
    {

        $pref = Configration::find($id);
        if (!empty($pref)) {
            $pref->fill($request->all());
            $pref->save();
        }

        session()->flash('action', 'تم التحديث بنجاح');
        return back()->withInput();
    }

    public function test()
    {
        
         Artisan::call('db:backup');
     
         dd(Artisan::output());
    }
    public function sendToken(Request $request)
    {

        if ($request->isMethod('post')) {

            $user = User::where('email', $request->email)->first();

            if (!empty($user)) {

                $user->remember_token = md5(rand(1, 10) . microtime());
                $user->save();

                $data = [
                    'email' => $request->email,
                    'token' => $user->remember_token,
                    'id' => $user->id,

                ];
                Mail::send('back-end.mail_send_token', $data, function ($message) use ($data) {

                    $message->from('info@tibaroyal.com');
                    $message->to($data['email']);
                    $message->subject('reset password');
                });
                $request->session()->flash('error-email', 'check your email to reset password , please!');
                return redirect()->route('login');
            }
            $request->session()->flash('error-email', "this email $request->email not found");
            return redirect()->route('login');
        }

    }

    public function paswordreset(Request $request, $id, $token)
    {
        if ($request->isMethod('post')) {
            $user = User::find($id);
            if ($user->remember_token == $token) {

                $user->password = Hash::make($request->password);
                $user->save();
                return redirect()->route('login');
            }
        }
        $user = User::find($id);
        if ($user->remember_token == $token) {
            return view('back-end.resetpassword', compact('id', 'token'));
        }
        return redirect()->route('login');
    }

    public function append($row)
    {
        $dayBillDiscount= Bill::whereYear('created_at', now()->year)
        ->whereMonth('created_at', now()->month)
        ->whereDay('created_at', now()->day)
        ->get()->sum('discount');
        
        $dayOrders= Order::with(['product'])
        ->where('status' , 'sold-out')
        ->whereYear('date', now()->year)
        ->whereMonth('date', now()->month)
        ->whereDay('date', now()->day)
        ->get();
        // return $dayOrders->product; 
        $dayProductBuyingPrice = $dayOrders->sum(function($t){ 
        return $t->quantity * ( $t->product->purchasing_price ); 
        });
       
        
        $dayOrdersReturned= Order::onlyTrashed()
        ->with(['product'])
        ->where('status' , 'sold-out')
        ->whereYear('date', now()->year)
        ->whereMonth('date', now()->month)
        ->whereDay('date', now()->day)
        ->get();

        $dayBillReturned= Bill::onlyTrashed()
        ->whereYear('created_at', now()->year)
        ->whereMonth('created_at', now()->month)
        ->whereDay('created_at', now()->day)
        ->get();

            ///////////////////// yesterday /////////////////////
        $yesterday = date("Y-m-d", strtotime( '-1 days' ) );
        $yesterdayBillDiscount= Bill::whereYear('created_at', now()->year)
        // ->whereMonth('created_at', now()->month)
        // ->whereDay('created_at', date('d',strtotime("-1 days")))
        ->whereDate('created_at', $yesterday )
        ->get()->sum('discount');
       
        $yesterdayOrders= Order::with(['product'])
        ->where('status' , 'sold-out')
        // ->whereYear('date', now()->year)
        // ->whereMonth('date', now()->month)
        // ->whereDay('date', date('d',strtotime("-1 days")))
        ->whereDate('date', $yesterday )
        ->get();
    

        // $yesterdayOrders= Order::where('status' , 'sold-out')
        //             ->whereYear('date', now()->year)
        //             ->whereMonth('date', now()->month)
        //             ->whereDay('date', date('d',strtotime("-1 days")))
        //             ->where('deleted_at' , '==' , null)
        //             ->get();
        // return $yesterdayOrders;

      
         $yesterDayProductBuyingPrice = $yesterdayOrders->sum(function($t){ 
        return $t->quantity * ( $t->product->purchasing_price ); 
        });   
             
        $monthOrders= Order::where('status' , 'sold-out')->whereYear('date', now()->year)->whereMonth('date', now()->month)->get();

        $yearOrders=Order::where('status' , 'sold-out')->whereYear('date', now()->year)->get();
        $expiredProduct=Product::where('quantity', 0)->get();

        $data['dayBillDiscount']= $dayBillDiscount ;
        $data['yesterdayBillDiscount']= $yesterdayBillDiscount ;

        $data['dayOrderDiscount']= $dayOrders->sum('discount');
        $data['yesterdayOrderDiscount']= $yesterdayOrders->sum('discount');

        $data['dayOrders']= count( $dayOrders) ; 
        
        $data['dayOrdersMoney']= $dayOrders->sum(function($t){ 
            return $t->quantity *( $t->price )- $t->discount; 
            });
        $data['dayProductBuyingPrice']= $dayProductBuyingPrice;

        $data['dayOrdersReturned']= count( $dayOrdersReturned) ;
        $data['dayOrdersReturnedMoney']=  $dayOrdersReturned->sum(function($t){ 
            return $t->quantity *( $t->price) - $t->discount; 
            });
        $data['dayBillReturned']= $dayBillReturned->sum('discount');
                        ////////////// yesterday
        $data['yesterdayOrders']= count( $yesterdayOrders) ; 
        $data['yesterdayOrdersMoney']= $yesterdayOrders->sum(function($t){ 
            return $t->quantity *( $t->price) - $t->discount; 
            });

        $data['yesterDayProductBuyingPrice']= $yesterDayProductBuyingPrice;

        $data['monthOrders']=count( $monthOrders);
        $data['monthOrdersMoney']= $monthOrders->sum(function($t){ 
            return $t->quantity *( $t->price) - $t->discount;  
            });

        $data['yearOrders']=count($yearOrders);
        $data['yearOrdersMoney']=$yearOrders->sum(function($t){ 
            return $t->quantity *( $t->price) - $t->discount; 
            });
        $data['expiredProduct']=count($expiredProduct);
        return $data;
    }
    public function getAllOrder()
    {
        $dayOrder= count(Order::whereDay('created_at', now()->day)->get());

        $monthOrder= count(Order::whereMonth('created_at', now()->month)->get());

        $yearOrder=count(Order::whereYear('created_at', now()->year)->get());
    }

    public function getAllPrice()
    {

        $dayPrice=DB::table('orders')
        ->select(DB::raw('sum(price) AS total'))->whereDay('created_at', now()->day)->get();

        $monthPrice=DB::table('orders')
        ->select(DB::raw('sum(price) AS total'))->whereMonth('created_at', now()->month)->get();

        $yearPrice=DB::table('orders')
        ->select(DB::raw('sum(price) AS total'))->whereYear('created_at', now()->year)->get();


        //return $x;


    }

   public function allOrder()
   {
        $results=count(Order::orderBy('id', 'DESC')->get());
        return $results;
   }


}
