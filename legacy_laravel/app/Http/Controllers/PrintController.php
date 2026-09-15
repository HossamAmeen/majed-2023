<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BackEnd\BackEndController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Product;

use App\Models\Bills_Product;
class PrintController extends Controller
{
    public function index($id)
    {

       $data['rows']=Bill::with('billedProducts')->where('id',$id)->get();

        return view('back-end.bills.bill')->with($data);

    }



}
