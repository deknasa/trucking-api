<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\ApprovalOpname;
use App\Http\Requests\StoreApprovalOpnameRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateApprovalOpnameRequest;
use App\Models\Parameter;
use Illuminate\Support\Facades\DB;

class ApprovalOpnameController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $parameter = Parameter::where('grp', 'OPNAME STOK')->where('subgrp', 'OPNAME STOK')->first();
        $text = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('id', $parameter->text)->first();
        $statusNonApproval = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        if ($parameter->text == $statusNonApproval->id) {

            $statusApproval = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
            $status = $statusApproval->id;
        } else {
            $status = $statusNonApproval->id;
        }
        return response([
            'terakhir' => $text->text,
            'status' => $status,
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StoreApprovalOpnameRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'statusopname' => $request->statusopname,
            ];
            $parameter = (new ApprovalOpname())->processStore($data);
            $text = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('id', $parameter->text)->first();

            $statusNonApproval = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
            if ($parameter->text == $statusNonApproval->id) {

                $statusApproval = Parameter::from(
                    DB::raw("parameter with (readuncommitted)")
                )->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
                $status = $statusApproval->id;
            } else {
                $status = $statusNonApproval->id;
            }

            DB::commit();
            return response([
                'status' => true,
                'message' => 'Proses '.$text->text.' opname stok berhasil',
                'text' => $text->text,
                'statusapproval' => $status,
                'data' => $parameter
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
