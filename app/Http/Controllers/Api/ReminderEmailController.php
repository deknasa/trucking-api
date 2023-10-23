<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Models\ReminderEmail;
use App\Http\Requests\StoreReminderEmailRequest;
use App\Http\Requests\UpdateReminderEmailRequest;

class ReminderEmailController extends Controller
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
     * @param  \App\Http\Requests\StoreReminderEmailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreReminderEmailRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ReminderEmail  $reminderEmail
     * @return \Illuminate\Http\Response
     */
    public function show(ReminderEmail $reminderEmail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ReminderEmail  $reminderEmail
     * @return \Illuminate\Http\Response
     */
    public function edit(ReminderEmail $reminderEmail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateReminderEmailRequest  $request
     * @param  \App\Models\ReminderEmail  $reminderEmail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateReminderEmailRequest $request, ReminderEmail $reminderEmail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReminderEmail  $reminderEmail
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReminderEmail $reminderEmail)
    {
        //
    }
}
