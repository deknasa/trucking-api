<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\RunningNumberService;
use Illuminate\Database\Schema\Blueprint;

class JobEmklRincianBiaya extends MyModel
{
    use HasFactory;
    protected $table = 'jobemklrincianbiaya';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function processStore(JobEmkl $jobEmkl, array $data): JobEmklRincianBiaya
    {


        $keteranganbiaya=$data['keteranganbiaya'] ?? '';

        $querydetailjob=db::table("a")->from(db::raw("openjson ( '".$keteranganbiaya ."')  "))
        ->select(
            db::raw("[value] ")
        )
        ->orderby(db::raw("[key]"),'asc');

        $datadetail = json_decode($querydetailjob->get(), true);
        // dd($datadetail);
        foreach ($datadetail as $item) {
            $keteranganjobdetail=$item['value'];
             $biayaemkl=db::table("a")->from(db::raw("openjson ( '".$keteranganjobdetail ."')  "))
                ->select(
                    db::raw("[value] ")
                )
                ->whereraw("[key]='biaya_emkl'")
                ->first()->value ?? '';
            $nominal=db::table("a")->from(db::raw("openjson ( '".$keteranganjobdetail ."')  "))
            ->select(
                db::raw("[value] ")
            )
            ->whereraw("[key]='nominal_biaya'")
            ->first()->value ?? '';         
            
            $keterangan=db::table("a")->from(db::raw("openjson ( '".$keteranganjobdetail ."')  "))
            ->select(
                db::raw("[value] ")
            )
            ->whereraw("[key]='keterangan_biaya'")
            ->first()->value ?? '';         

            $idbiayaemkl=db::table("biayaemkl")->from(db::raw("biayaemkl a with (readuncommitted)"))
            ->select(
                'a.id',
            )
            ->where('a.kodebiayaemkl',$biayaemkl)
            ->first()->id ?? 0;
                $jobEmklRincianBiaya = new JobEmklRincianBiaya();
                $jobEmklRincianBiaya->jobemkl_id = $data['jobemkl_id'];
                $jobEmklRincianBiaya->nobukti = $data['nobukti'];
                $jobEmklRincianBiaya->biayaemkl_id = $idbiayaemkl ?? 0;
                $jobEmklRincianBiaya->nominal = $nominal ?? 0;
                $jobEmklRincianBiaya->keterangan = $keterangan ?? '';
                $jobEmklRincianBiaya->modifiedby = $data['modifiedby'];
               
                $jobEmklRincianBiaya->save();
                

        
        }
        if (!$jobEmklRincianBiaya->save()) {
            throw new \Exception("Error storing Pengeluaran Detail.");
        }
     
        return $jobEmklRincianBiaya;
    }
}
