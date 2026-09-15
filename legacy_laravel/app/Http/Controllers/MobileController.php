<?php

namespace App\Http\Controllers;
use App\Models\News;
use App\Models\Article;
use App\Models\Video;
use Illuminate\Http\Request;

class MobileController extends Controller
{
    public function news($id=null)
    {
        $items = News::all();
        if(isset($id))
        {
            $items = News::find($id);
        }
        
        return response($items );

    }

    public function articles($id=null)
    {
        $items = Article::all();
        if(isset($id))
        {
            $items = Article::find($id);
        }
        return response($items);
    }

    public function videos($id=null)
    {
        $items = Video::all();
        if(isset($id))
        {
            $items = Video::find($id);
        }
        return response($items);
    }

    public function contacts(Request $request)  
    {
        
            $rules = $this->contactFormValidation();
            $message = $this->contactMessageValidation();
            $this->validate($request, $rules, $message);
            $data=[
                'email' =>  $request->email,
                'name' => $request->name,

                'subject'=>$request->subject,
                'text'=>$request->text,
            ];
            Mail::send('front-end.contact_mail',$data,function($message) use ($data){

                $message->from( $data['email'] , $data['name']);
                $message->to("info@tibaroyal.com");
                $message->subject($data['subject']);
            });
     

    }


    function contactFormValidation()
    {


        return array(
            'name' => 'required|regex:/^[\pL\s\d\-]+$/u||max:99',

            'email' => 'required|email',

            'text' => 'required|regex:/^[\pL\s\-]+$/u||max:99',

        );
    }

    function contactMessageValidation()
    {
        return array(
            'name.required' => 'هذا الحقل (الاسم) مطلوب ',
            'name.*' => 'هذا الحقل (الاسم) يجب يحتوي ع حروف وارقام فقط',

            'text.required' => 'هذا الحقل (الرساله) مطلوب ',
            'text.*' => 'هذا الحقل (الرساله) يجب يحتوي ع حروف وارقام فقط',

            'email.required' => 'هذا الحقل (البريد) مطلوب ',
            'email.*' => 'هذا الحقل (البريد)يجب ان يكون بريد صحيح',

            'phone.required' => 'هذا الحقل (التلفون) مطلوب ',
            'phone.min' => 'هذا الحقل (التلفون) يجب الا يقل عن 11 رقم ',
            'phone.*' => 'هذا الحقل (التلفون) يجب يحتوي ع ارقام فقط',


        );
    }

}
