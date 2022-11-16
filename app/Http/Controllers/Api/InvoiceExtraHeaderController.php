<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoiceExtraHeader;
use App\Http\Requests\StoreInvoiceExtraHeaderRequest;
use App\Http\Requests\UpdateInvoiceExtraHeaderRequest;

class InvoiceExtraHeaderController extends Controller
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
     * @param  \App\Http\Requests\StoreInvoiceExtraHeaderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInvoiceExtraHeaderRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InvoiceExtraHeader  $invoiceExtraHeader
     * @return \Illuminate\Http\Response
     */
    public function show(InvoiceExtraHeader $invoiceExtraHeader)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InvoiceExtraHeader  $invoiceExtraHeader
     * @return \Illuminate\Http\Response
     */
    public function edit(InvoiceExtraHeader $invoiceExtraHeader)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInvoiceExtraHeaderRequest  $request
     * @param  \App\Models\InvoiceExtraHeader  $invoiceExtraHeader
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInvoiceExtraHeaderRequest $request, InvoiceExtraHeader $invoiceExtraHeader)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InvoiceExtraHeader  $invoiceExtraHeader
     * @return \Illuminate\Http\Response
     */
    public function destroy(InvoiceExtraHeader $invoiceExtraHeader)
    {
        //
    }
}
