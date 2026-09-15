<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromArray;

class ProductsExport implements FromArray
{
    /**
     * The rows exported to the spreadsheet.
     *
     * @return array<int, mixed>
     */
    public function array(): array
    {
        $data[] = ['الاسم', 'سعر الشراء', 'سعر البيع', 'الكيمة', 'السعر الكلي', ' الكود', 'الخصم'];

        $data[] = Product::whereYear('created_at', date('Y'))->get([
            'name', 'purchasing_price', 'selling_price', 'quantity',
            'total_price', 'code', 'discount',
        ])->toArray();

        return $data;
    }
}
