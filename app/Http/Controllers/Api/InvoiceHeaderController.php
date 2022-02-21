<?php

namespace App\Http\Controllers;

use App\Models\InvoiceHeader;
use App\Http\Requests\StoreInvoiceHeaderRequest;
use App\Http\Requests\UpdateInvoiceHeaderRequest;

class InvoiceHeaderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreInvoiceHeaderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInvoiceHeaderRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InvoiceHeader  $invoiceHeader
     * @return \Illuminate\Http\Response
     */
    public function show(InvoiceHeader $invoiceHeader)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InvoiceHeader  $invoiceHeader
     * @return \Illuminate\Http\Response
     */
    public function edit(InvoiceHeader $invoiceHeader)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInvoiceHeaderRequest  $request
     * @param  \App\Models\InvoiceHeader  $invoiceHeader
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInvoiceHeaderRequest $request, InvoiceHeader $invoiceHeader)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InvoiceHeader  $invoiceHeader
     * @return \Illuminate\Http\Response
     */
    public function destroy(InvoiceHeader $invoiceHeader)
    {
        //
    }
}
