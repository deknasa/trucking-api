<?php

namespace App\Http\Controllers\Api;

use App\Models\JobTrucking;
use App\Http\Requests\StoreJobTruckingRequest;
use App\Http\Requests\UpdateJobTruckingRequest;

use App\Http\Controllers\Controller;

class JobTruckingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $jobTrucking = new JobTrucking();
        return response([
            'data' => $jobTrucking->get(),
            'attributes' => [
                'totalRows' => $jobTrucking->totalRows,
                'totalPages' => $jobTrucking->totalPages
            ]
        ]);
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
     * @param  \App\Http\Requests\StoreJobTruckingRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreJobTruckingRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\JobTrucking  $jobTrucking
     * @return \Illuminate\Http\Response
     */
    public function show(JobTrucking $jobTrucking)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\JobTrucking  $jobTrucking
     * @return \Illuminate\Http\Response
     */
    public function edit(JobTrucking $jobTrucking)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateJobTruckingRequest  $request
     * @param  \App\Models\JobTrucking  $jobTrucking
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateJobTruckingRequest $request, JobTrucking $jobTrucking)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\JobTrucking  $jobTrucking
     * @return \Illuminate\Http\Response
     */
    public function destroy(JobTrucking $jobTrucking)
    {
        //
    }
}
