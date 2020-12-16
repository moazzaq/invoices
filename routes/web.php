<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


    Route::get('/', function () {
        return view('auth.login');
    })->middleware('guest');


Auth::routes();
//Auth::routes(['register'=>false]);

Route::get('/home', 'HomeController@index')->name('home');


Route::resource('invoices', 'InvoiceController');
Route::get('Invoice_Paid','InvoiceController@Invoice_Paid');
Route::get('Invoice_UnPaid','InvoiceController@Invoice_UnPaid');
Route::get('Invoice_Partial','InvoiceController@Invoice_Partial');
Route::get('/section/{id}', 'InvoiceController@getproducts');
Route::get('/edit_invoice/{id}', 'InvoiceController@edit');
Route::get('/Status_show/{id}', 'InvoiceController@show')->name('Status_show');
Route::post('/Status_Update/{id}', 'InvoiceController@Status_Update')->name('Status_Update');
Route::get('Print_invoice/{id}','InvoiceController@Print_invoice');
Route::get('export_invoices', 'InvoiceController@export');

Route::resource('Archive', 'InvoiceAchiveController');



Route::resource('sections', 'SectionController');

Route::resource('products', 'ProductController');

Route::get('/InvoicesDetails/{id}', 'InvoiceDetailsController@edit');
Route::get('download/{invoice_number}/{file_name}', 'InvoiceDetailsController@get_file');
Route::get('View_file/{invoice_number}/{file_name}', 'InvoiceDetailsController@open_file');
Route::post('delete_file', 'InvoiceDetailsController@destroy')->name('delete_file');


Route::resource('InvoiceAttachments', 'InvoiceAttachmentController');

Route::group(['middleware' => ['auth']], function() {
    Route::resource('roles','RoleController');
    Route::resource('users','UserController');
});

Route::get('invoices_report', 'Invoices_Report@index');

Route::post('Search_invoices', 'Invoices_Report@Search_invoices');

Route::get('customers_report', 'Customers_Report@index')->name("customers_report");

Route::post('Search_customers', 'Customers_Report@Search_customers');

Route::get('MarkAsRead_all','InvoiceController@MarkAsRead_all')->name('MarkAsRead_all');


//Route::get('/{page}', 'AdminController@index');
