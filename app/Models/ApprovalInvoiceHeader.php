<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApprovalInvoiceHeader extends MyModel
{
    use HasFactory;

    protected $table = 'invoiceheader';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'tglbukti' => 'date:d-m-Y',
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];


    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('approve')->nullable();
            $table->unsignedBigInteger('invoice')->nullable();
        });

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS APPROVAL')
            ->where('subgrp', '=', 'STATUS APPROVAL')
            ->where('default', '=', 'YA')
            ->first();

        $idstatusapproval = $status->id ?? 0;

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS INVOICE')
            ->where('subgrp', '=', 'STATUS INVOICE')
            ->where('default', '=', 'YA')
            ->first();

        $idstatusinvoice = $status->id ?? 0;


        DB::table($tempdefault)->insert(
            ["approve" => $idstatusapproval, "invoice" => $idstatusinvoice]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'approve',
                'invoice',
            );

        $data = $query->first();

        return $data;
    }


}
