<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
// use Collection;;
class ProductsExport implements FromArray
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // return Product::whereYear('created_at' ,  date('Y'))->get([
        //     'name' , 'purchasing_price','selling_price', 'quantity' ,
        // 'total_price', 'code' , 'discount' , 'user_id','category_id']);
      
        return new Collection([
            [1, 2, 3],
            [4, 5, 6]
        ]);
        return  $data ; 
    }
    public function array(): array
    {
        $data[] = ['الاسم', 'سعر الشراء', 'سعر البيع', 'الكيمة', 'السعر الكلي' , ' الكود' , 'الخصم'];
        $data[] = Product::whereYear('created_at' ,  date('Y'))->get([
                'name' , 'purchasing_price','selling_price', 'quantity' ,
            'total_price', 'code' , 'discount' ])->toArray();
        return $data;
    }
}
