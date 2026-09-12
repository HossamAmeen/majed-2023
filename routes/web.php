<?php

use App\Http\Controllers\BackEnd\BillController;
use App\Http\Controllers\BackEnd\ConfigrationController;
use App\Http\Controllers\BackEnd\NoteController;
use App\Http\Controllers\BackEnd\OrderController;
use App\Http\Controllers\BackEnd\ProductController;
use App\Http\Controllers\BackEnd\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Controllers are referenced with the [Controller::class, 'method'] syntax,
| which is required since Laravel 8 dropped the implicit controller
| namespace prefix for string based actions.
|
*/

Route::prefix('admin')->group(function () {
    Auth::routes();

    Route::any('sendToken', [ConfigrationController::class, 'sendToken'])->name('forget.password');
    Route::any('paswordreset/{id}/{token}', [ConfigrationController::class, 'paswordreset']);

    // Must stay before the "bills" resource so it is not captured by bills/{bill}.
    Route::get('bills/search', [BillController::class, 'search']);

    Route::middleware('auth')->group(function () {

        Route::get('/', [ConfigrationController::class, 'index']);

        Route::get('/allorders', [ConfigrationController::class, 'getAllOrder']);
        Route::get('/sumprice', [ConfigrationController::class, 'getAllPrice']);

        Route::resource('configrations', ConfigrationController::class);
        Route::resource('users', UserController::class);
        Route::any('edit-account', [UserController::class, 'editAccount'])->name('edit-account');

        Route::resource('products', ProductController::class);
        Route::get('products-export-excel-sheet', [ProductController::class, 'excelExportSheet']);
        Route::get('products-export-PDF', [ProductController::class, 'exportPDF']);

        Route::resource('orders', OrderController::class);
        Route::resource('bills', BillController::class);
        Route::resource('notes', NoteController::class);

        Route::any('print-bill/{id}', [BillController::class, 'printBill'])->name('bills.print');
        Route::any('print-bill2/{id}', [BillController::class, 'printBill2'])->name('bills.print2');
        Route::get('print-barcode/{productId}', [ProductController::class, 'printBarCode'])->name('barcode.print');

        Route::get('test', [ConfigrationController::class, 'test']);
    });
});

Route::get('/', function () {
    return redirect()->route('users.index');
});
