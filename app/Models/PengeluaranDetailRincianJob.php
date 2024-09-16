<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\RunningNumberService;
use Illuminate\Database\Schema\Blueprint;


class PengeluaranDetailRincianJob extends MyModel
{
    use HasFactory;
    protected $table = 'pengeluarandetailrincianjob';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function processStore(PengeluaranHeader $pengeluaranHeader, array $data): PengeluaranDetailRincianJob
    {


        $keteranganjob=$data['keteranganjob'] ?? '';

        $querydetailjob=db::table("a")->from(db::raw("openjson ( '".$keteranganjob ."')  "))
        ->select(
            db::raw("[value] ")
        )
        ->orderby(db::raw("[key]"),'asc');

        $datadetail = json_decode($querydetailjob->get(), true);
        // dd($datadetail);
        foreach ($datadetail as $item) {
            $keteranganjobdetail=$item['value'];
             $jobemkl_nobukti=db::table("a")->from(db::raw("openjson ( '".$keteranganjobdetail ."')  "))
                ->select(
                    db::raw("[value] ")
                )
                ->whereraw("[key]='job_emkl'")
                ->first()->value ?? '';
            $nominal=db::table("a")->from(db::raw("openjson ( '".$keteranganjobdetail ."')  "))
            ->select(
                db::raw("[value] ")
            )
            ->whereraw("[key]='nominal'")
            ->first()->value ?? '';         
            
            
                $pengeluaranDetailRincianJob = new PengeluaranDetailRincianJob();
                $pengeluaranDetailRincianJob->pengeluaran_id = $data['pengeluaran_id'];
                $pengeluaranDetailRincianJob->pengeluarandetail_id = $data['pengeluarandetail_id'];
                $pengeluaranDetailRincianJob->nobukti = $data['nobukti'];
                $pengeluaranDetailRincianJob->jobemkl_nobukti = $jobemkl_nobukti ?? '';
                $pengeluaranDetailRincianJob->nominal = $nominal ?? '';
                $pengeluaranDetailRincianJob->modifiedby = $data['modifiedby'];
               
                $pengeluaranDetailRincianJob->save();
                

        
        }
        if (!$pengeluaranDetailRincianJob->save()) {
            throw new \Exception("Error storing Pengeluaran Detail.");
        }
     
        return $pengeluaranDetailRincianJob;
    }

}
