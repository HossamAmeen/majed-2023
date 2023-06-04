<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Client;

class ClientController extends Controller
{
    public function index()
    {
        $data['rows'] = Client::orderBy('id','DESC')->get();
        return view('clients')->with($data);
    }


    public function store(Request $request)
    {
        if($request->ajax())
        {
            $request->validate([
                'name' =>'required|string|max:50',
                'email' =>'required|string|max:50',
                'position' =>'required|string|max:50',
                'mobile' =>'required|string|max:15',
            ]);

            // dd($request->name);

            $data = new Client;
            $data->name = $request->name;
            $data->email = $request->email;
            $data->position = $request->position;
            $data->mobile = $request->mobile;
            $data->save();

            $respond['row'] = $data;
            return view('row')->with($respond);
        }
    }









    public function delete($id)
    {
        Client::findOrfail($id)->delete();
        return response()->json(['success'=>'Deleted Success','id'=>$id]);
    }





    public function edit($id)
    {
        $data = Client::findOrfail($id);
        return response()->json($data);
    }



    public function update(Request $request)
    {
        if($request->ajax())
        {
            $request->validate([
                'name' =>'required|string|max:50',
                'email' =>'required|string|max:50',
                'position' =>'required|string|max:50',
                'mobile' =>'required|string|max:15',
            ]);

            // dd($request->name);

            $data = Client::findOrFail($request->id);
            $data->name = $request->name;
            $data->email = $request->email;
            $data->position = $request->position;
            $data->mobile = $request->mobile;
            $data->save();

            $respond['row'] = $data;
            return view('rowEdit')->with($respond);
        }
    }





}
