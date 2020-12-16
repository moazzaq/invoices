<?php

namespace App\Http\Controllers;

use App\Exports\InvoicesExport;
use App\Invoice;
use App\InvoiceAttachment;
use App\InvoiceDetails;
use App\Notifications\Add_invocie_new;
use App\Notifications\AddInvoice;
use App\Section;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $invoices = Invoice::all();
        return view('invoices.invoices', compact('invoices'));
    }

    public function Invoice_Paid()
    {
        $invoices = Invoice::whereValueStatus(1)->get();
        return view('invoices.invoices_paid', compact('invoices'));
    }

    public function Invoice_unPaid()
    {
        $invoices = Invoice::where('Value_Status',2)->get();
        return view('invoices.invoices_unpaid',compact('invoices'));
    }

    public function Invoice_Partial()
    {
        $invoices = Invoice::where('Value_Status',3)->get();
        return view('invoices.invoices_Partial',compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $sections = Section::all();
        return view('invoices.add_invoice', compact('sections'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $invoice_id = Invoice::insertGetId([
                'invoice_number' => $request->invoice_number,
                'invoice_Date' => $request->invoice_Date,
                'Due_date' => $request->Due_date,
                'product' => $request->product,
                'section_id' => $request->Section,
                'Amount_collection' => $request->Amount_collection,
                'Amount_Commission' => $request->Amount_Commission,
                'Discount' => $request->Discount,
                'Value_VAT' => $request->Value_VAT,
                'Rate_VAT' => $request->Rate_VAT,
                'Total' => $request->Total,
                'Status' => 'غير مدفوعة',
                'Value_Status' => 2,
                'note' => $request->note,
            ]);

            InvoiceDetails::create([
                'invoice_id' => $invoice_id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'Section' => $request->Section,
                'Status' => 'غير مدفوعة',
                'Value_Status' => 2,
                'note' => $request->note,
                'user' => Auth::user()->name,
            ]);

            if ($request->hasFile('pic')) {
                $image = $request->file('pic');
                $file_name = $image->getClientOriginalName();

                $invoice_number = $request->invoice_number;

                $attachments = new InvoiceAttachment();
                $attachments->file_name = $file_name;
                $attachments->invoice_number = $invoice_number;
                $attachments->Created_by = Auth::user()->name;
                $attachments->invoice_id = $invoice_id;
                $attachments->save();

                // move pic
                $imageName = $request->pic->getClientOriginalName();
                $request->pic->move(public_path('Attachments/' . $invoice_number), $imageName);
            }

//             $user = User::first();
//             $user->notify(new AddInvoice($invoice_id));

//             Notification::send($user, new AddInvoice($invoice_id));

            $user = User::get();
//            $user = User::find(Auth::user()->id);
            $invoices = Invoice::latest()->first();
            Notification::send($user, new Add_invocie_new($invoices));

            DB::commit();
            session()->flash('Add', 'تم اضافة الفاتورة بنجاح');
            return back();

        }catch (\Exception $exception) {
            DB::rollback();
            dd($exception);
            return redirect()->back();
        }

    }
//    public function store(Request $request)
//    {
//        Invoice::create([
//            'invoice_number' => $request->invoice_number,
//            'invoice_Date' => $request->invoice_Date,
//            'Due_date' => $request->Due_date,
//            'product' => $request->product,
//            'section_id' => $request->Section,
//            'Amount_collection' => $request->Amount_collection,
//            'Amount_Commission' => $request->Amount_Commission,
//            'Discount' => $request->Discount,
//            'Value_VAT' => $request->Value_VAT,
//            'Rate_VAT' => $request->Rate_VAT,
//            'Total' => $request->Total,
//            'Status' => 'غير مدفوعة',
//            'Value_Status' => 2,
//            'note' => $request->note,
//        ]);
//
//        $invoice_id = Invoice::latest()->first()->id;
//        InvoiceDetails::create([
//            'invoice_id' => $invoice_id,
//            'invoice_number' => $request->invoice_number,
//            'product' => $request->product,
//            'Section' => $request->Section,
//            'Status' => 'غير مدفوعة',
//            'Value_Status' => 2,
//            'note' => $request->note,
//            'user' => (Auth::user()->name),
//        ]);
//
//        if ($request->hasFile('pic')) {
//
//            $invoice_id = Invoice::latest()->first()->id;
//            $image = $request->file('pic');
//            $file_name = $image->getClientOriginalName();
//            $invoice_number = $request->invoice_number;
//
//            $attachments = new InvoiceAttachment();
//            $attachments->file_name = $file_name;
//            $attachments->invoice_number = $invoice_number;
//            $attachments->Created_by = Auth::user()->name;
//            $attachments->invoice_id = $invoice_id;
//            $attachments->save();
//
//            // move pic
//            $imageName = $request->pic->getClientOriginalName();
//            $request->pic->move(public_path('Attachments/' . $invoice_number), $imageName);
//        }
//
//
//        // $user = User::first();
//        // Notification::send($user, new AddInvoice($invoice_id));
//
//        $user = User::get();
//        $invoices = Invoice::latest()->first();
//        Notification::send($user, new Add_invocie_new($invoices));
//
//        session()->flash('Add', 'تم اضافة الفاتورة بنجاح');
//        return back();
//    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $invoices = Invoice::findOrFail($id);
        return view('invoices.status_update', compact('invoices'));
    }

    public function Status_Update(Request $request,$id)
    {
        $invoices = Invoice::findOrFail($id);
        if ($request->Status === 'مدفوعة') {
            $invoices->update([
                'Value_Status' => 1,
                'Status' => $request->Status,
                'Payment_Date' => $request->Payment_Date,
            ]);

            InvoiceDetails::create([
                'invoice_id' => $request->invoice_id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'Section' => $request->Section,
                'Status' => $request->Status,
                'Value_Status' => 1,
                'note' => $request->note,
                'Payment_Date' => $request->Payment_Date,
                'user' => Auth::user()->name,
            ]);

        }else{
            $invoices->update([
                'Value_Status' => 3,
                'Status' => $request->Status,
                'Payment_Date' => $request->Payment_Date,
            ]);
            InvoiceDetails::create([
                'invoice_id' => $request->invoice_id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'Section' => $request->Section,
                'Status' => $request->Status,
                'Value_Status' => 3,
                'note' => $request->note,
                'Payment_Date' => $request->Payment_Date,
                'user' => (Auth::user()->name),
            ]);
        }
        session()->flash('Status_Update');
        return redirect('/invoices');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $invoices = Invoice::findOrFail($id);
        $sections = Section::all();
        return view('invoices.edit_invoice', compact('sections', 'invoices'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $invoices = Invoice::findOrFail($request->invoice_id);
        $invoices->update([
            'invoice_number' => $request->invoice_number,
            'invoice_Date' => $request->invoice_Date,
            'Due_date' => $request->Due_date,
            'product' => $request->product,
            'section_id' => $request->Section,
            'Amount_collection' => $request->Amount_collection,
            'Amount_Commission' => $request->Amount_Commission,
            'Discount' => $request->Discount,
            'Value_VAT' => $request->Value_VAT,
            'Rate_VAT' => $request->Rate_VAT,
            'Total' => $request->Total,
            'note' => $request->note,
        ]);

        session()->flash('edit', 'تم تعديل الفاتورة بنجاح');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $id = $request->invoice_id;
        $invoice = Invoice::whereId($id)->first();

        $Details = InvoiceAttachment::where('invoice_id', $id)->first();

        $id_page =$request->id_page;

        if (!$id_page==2) {

            if (!empty($Details->invoice_number)) {

                Storage::disk('public_uploads')->deleteDirectory($Details->invoice_number);
            }

            $invoice->ForceDelete();
            session()->flash('delete_invoice');
            return redirect('/invoices');

        }

        else {

            $invoice->delete();
            session()->flash('archive_invoice');
            return redirect('/Archive');
        }
    }

    public function getproducts($id)
    {
        $products = DB::table("products")->where("section_id", $id)->pluck("Product_name", "id");
        return json_encode($products);
    }

    public function Print_invoice($id)
    {
        $invoices = Invoice::whereId($id)->first();
        return view('invoices.Print_invoice',compact('invoices'));
    }

    public function export()
    {
        return Excel::download(new InvoicesExport(), 'invoices.xlsx');
    }

    public function MarkAsRead_all()
    {
        $userUnreadNotification = auth()->user()->unreadNotifications;

        if($userUnreadNotification) {
            $userUnreadNotification->markAsRead();
            return back();
        }

    }

}
