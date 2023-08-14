<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\SaldoReminderPergantian;
use App\Http\Requests\StoreSaldoReminderPergantianRequest;
use App\Http\Requests\UpdateSaldoReminderPergantianRequest;

class SaldoReminderPergantianController extends Controller
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
     * @param  \App\Http\Requests\StoreSaldoReminderPergantianRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSaldoReminderPergantianRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SaldoReminderPergantian  $saldoReminderPergantian
     * @return \Illuminate\Http\Response
     */
    public function show(SaldoReminderPergantian $saldoReminderPergantian)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SaldoReminderPergantian  $saldoReminderPergantian
     * @return \Illuminate\Http\Response
     */
    public function edit(SaldoReminderPergantian $saldoReminderPergantian)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSaldoReminderPergantianRequest  $request
     * @param  \App\Models\SaldoReminderPergantian  $saldoReminderPergantian
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSaldoReminderPergantianRequest $request, SaldoReminderPergantian $saldoReminderPergantian)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SaldoReminderPergantian  $saldoReminderPergantian
     * @return \Illuminate\Http\Response
     */
    public function destroy(SaldoReminderPergantian $saldoReminderPergantian)
    {
        //
    }
}
