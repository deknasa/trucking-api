<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class LaporanRitasiTrado extends MyModel
{
    use HasFactory;

    protected $table = '';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];



    public function getExport($periode)
    {
      
        //NOTE - table temptrip
        $TempTrip = '##TempTrip' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempTrip, function ($table) {
            $table->string('nobukti', 50);
            $table->date('tglbukti');
            $table->integer('trado_id');
        });
        $select_TempTrip = DB::table('suratpengantar')->from(DB::raw("suratpengantar AS A WITH (READUNCOMMITTED)"))
        ->select([
            'A.nobukti',
            'A.tglbukti',
            'A.trado_id'
        ])
        ->whereRaw('MONTH(A.tglbukti) = ?', [intval(substr($periode, 0, 2))])
        ->whereRaw('YEAR(A.tglbukti) = ?', [intval(substr($periode, -4) )]);
    
        DB::table($TempTrip)->insertUsing([
            'A.nobukti',
            'A.tglbukti',
            'A.trado_id'
        ], $select_TempTrip);
        // dd($select_TempTrip->get());
        //SECTION - selesai




        //NOTE - table Jumlahtriptrado
        $Jumlahtriptrado = '##Jumlahtriptrado' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($Jumlahtriptrado, function ($table) {
            $table->integer('trado_id');
            $table->integer('tanggal');
            $table->integer('jumlah');
        });

        $select_Jumlahtriptrado = DB::table('TempTrip')->from(DB::raw($TempTrip ." AS a"))
        ->select([
            'A.trado_id',
            DB::raw('day(A.tglbukti) as tglbukti'),
            DB::raw('count(A.nobukti) as jumlah')
        ])
        ->groupBy('A.trado_id', DB::raw('day(A.tglbukti)'));

          DB::table($Jumlahtriptrado)->insertUsing([
            'A.trado_id',
            'A.tanggal',
            'A.jumlah'
        ], $select_Jumlahtriptrado);
        // dd($select_Jumlahtriptrado->get());
        //SECTION - selesai

   //NOTE - table TempTRado
        $TempTRado = '##TempTRado' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($TempTRado, function ($table) {
            $table->integer('id');
            $table->string('nopol', 100);
        });

        $select_trado = DB::table('trado')->from(DB::raw("trado AS A WITH (READUNCOMMITTED)"))
        ->select([
            'A.id',
            'A.kodetrado'
        ])
        ->where('A.statusabsensisupir', '=', 439)
        ->where('A.statusaktif', '=', 1);

        DB::table($TempTRado)->insertUsing([
            'A.id',
            'A.nopol',
        ], $select_trado);
        // dd($select_trado->get());



        //SECTION - selesai
        //NOTE - declare
        $tgl1 = Carbon::createFromFormat('m-Y', substr($periode, 0, 2) . '-' . substr($periode, -4))->startOfMonth();
        $tgl2 = Carbon::parse($tgl1)->addDays(33)->toDateString();
        $tgl3 = Carbon::parse($tgl2)->startOfMonth()->subDay()->toDateString();

        $atgl1 = Carbon::parse($tgl1)->day;
        $atgl2 = Carbon::parse($tgl3)->day;
        
        // dd($tgl1, $tgl2, $tgl3, $atgl1, $atgl2);
        
        $tempTgltrado = '##tempTgltrado' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempTgltrado, function ($table) {
            $table->integer('trado_id');
            $table->integer('tgl');
            $table->dateTime('tglbukti');
        });

        $btglbukti = $tgl1;
        while ($atgl1 <= $atgl2) {
            $btglbukti = Carbon::createFromFormat('Y/m/d', $tgl1->format('Y/m/') . $atgl1)->toDateString();
        
            $select_TempTRado = DB::table('TempTRado')->from(DB::raw($TempTRado . " AS a"))
                ->select([
                    'id',
                    DB::raw($atgl1 . ' AS atgl1'),
                    DB::raw("'" . $btglbukti . "' AS btglbukti"),
                ]);
        
            DB::table($tempTgltrado)->insertUsing([
                'trado_id',
                'tgl',
                'tglbukti'
            ], $select_TempTRado);
        
            $atgl1++;
        }
        
// dd($select_TempTRado->get());

        



        //NOTE - ABSENSI
        $absensi = '##absensi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($absensi, function ($table) {
            $table->integer('tgl');
            $table->integer('trado_id');
            $table->integer('absen_id');
        });

        $select_absensi = DB::table('absensisupirheader')->from(DB::raw("absensisupirheader AS A WITH (READUNCOMMITTED)"))
        ->select([
            DB::raw('DAY(A.tglbukti) AS tgl'),
            'b.trado_id',
            DB::raw('MAX(B.absen_id) AS absen_id')
        ])
        ->join(DB::raw("absensisupirDetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti') //NOTE - kesalahan ada di baris 164 jumlah tadi tidak sesuai sekarang sudah sesuai
        ->whereRaw('MONTH(A.tglbukti) = ?', [intval(substr($periode, 0, 2))])
        ->whereRaw('YEAR(A.tglbukti) = ?', [intval(substr($periode, -4))])
        ->groupBy(DB::raw('DAY(A.tglbukti)'), 'B.trado_id');
            
        DB::table($absensi)->insertUsing([
            'tgl',
            'trado_id',
            'absen_id'
        ], $select_absensi);

        DB::table($absensi)
        ->where('absen_id', 0)
        ->delete();
        // dd($select_absensi->get());


       //NOTE - TEMPREKAP
       $Temprekap = '##Temprekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
       Schema::create($Temprekap, function ($table) {
           $table->string('nopol',500);
           $table->integer('tgl');
           $table->string('jumlah',10);
        
       });
    //    (CASE WHEN ISNULL(d.kodeabsen,'')='' THEN 'L' ELSE d.kodeabsen END) 
       $select_Temprekap = DB::table('Temprekap')->from(DB::raw($tempTgltrado . " AS a"))
       ->select([
        'f.kodetrado AS nopol',
        'A.tgl',
        DB::raw("(CASE WHEN (CASE 
            WHEN ISNULL(C.trado_id, 0) = 0 THEN FORMAT(ISNULL(B.jumlah, 0), '#')
            ELSE d.kodeabsen 
        END) = '' THEN 'L' ELSE
        (CASE 
            WHEN ISNULL(C.trado_id, 0) = 0 THEN FORMAT(ISNULL(B.jumlah, 0), '#')
            ELSE d.kodeabsen 
        END)
          END) as jumlah"),
       ])
       ->leftJoin(DB::raw($Jumlahtriptrado . " AS b"), function ($join) {
        $join->on('A.trado_id', '=', 'B.trado_id')
            ->on('A.tgl', '=', 'B.tanggal');
        })
        ->leftJoin(DB::raw($absensi . " AS C"), function ($join) {
            $join->on('A.tgl', '=', 'C.tgl')
                ->on('A.trado_id', '=', 'C.trado_id');
        })
        ->leftJoin('absentrado AS D', 'C.absen_id', '=', 'D.id')
        ->leftJoin('harilibur AS E', 'A.tglbukti', '=', 'E.tgl')
        ->join('trado AS F', 'A.trado_id', '=', 'F.id')
        ->orderBy('A.tgl','ASC')
        ->orderBy('A.trado_id','ASC');
        
        $result = $select_Temprekap->get();
        // dd($result);


// Format hasil ke dalam array yang diinginkan
// $output = [];
// foreach ($result as $item) {
//     $output[] = [
//         'nopol' => $item->nopol,
//         'tgl' => $item->tgl,
//         'jumlah' => $item->jumlah,
//     ];
// }

// dd($output);


        DB::table($Temprekap)->insertUsing([
            'nopol',
            'tgl',
            'jumlah'
        ], $select_Temprekap);



        //NOTE - TEMPRPKTGL
        $temprkptgl = '##temprkptgl' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprkptgl, function ($table) {
            $table->integer('tgl');
        });

        $select_temprkptgl = DB::table('Temprekap')->from(DB::raw($Temprekap))
        ->select([
         'tgl',
        ])
        ->groupBy(DB::raw('tgl'));
        // dd($select_temprkptgl->get());

        DB::table($temprkptgl)->insertUsing([
            'tgl',
        ], $select_temprkptgl);
       

        $columnstgl = DB::table('temprkptgl')
    ->from(DB::raw($temprkptgl))
    ->select([
        DB::raw("CONCAT('[', CAST(tgl AS VARCHAR), ']') AS columnstgl"),
    ])
    ->orderBy('tgl', 'ASC');

$columnstglResult = $columnstgl->pluck('columnstgl')->implode(', ');
// dd($columnstglResult);

  

            $query = "SELECT nopol AS nopol, ".$columnstglResult."
            FROM
            (
                SELECT 
                A.nopol, A.tgl, A.jumlah
                FROM " . $Temprekap . " AS A
            ) AS SourceTable
            PIVOT
            (
                MAX(jumlah)
                FOR tgl IN (".$columnstglResult.")
            ) AS PivotTable";
         
            $results = DB::select($query);
            return $results;
            
    



            

   



        // $pengeluaranStok = PengeluaranStok::where('kodepengeluaran', 'SPK')->first();
        // // data coba coba
        
        // $month = substr($periode, 0, 2);
        // $year = substr($periode, 3);
        // $query = PengeluaranStokHeader::from(
        //     DB::raw("pengeluaranstokheader with (readuncommitted)")
        // )->select(
        //     'pengeluaranstokheader.id',
        //     'pengeluaranstokheader.nobukti',
        //     'pengeluaranstokheader.tglbukti',
        //     'trado.kodetrado as nobk',
        //     'stok.namastok',
        //     'pengeluaranstokdetail.qty',
        //     'pengeluaranstokdetail.qty as satuan',
        //     'pengeluaranstokdetail.harga',
        //     'pengeluaranstokdetail.total as nominal',
        //     'pengeluaranstokdetail.total as saldo',
        //     'pengeluaranstokdetail.keterangan',
        // )
        // ->leftJoin(DB::raw("trado with (readuncommitted)"), 'pengeluaranstokheader.trado_id', 'trado.id')
        // ->leftJoin(DB::raw("pengeluaranstokdetail with (readuncommitted)"), 'pengeluaranstokdetail.pengeluaranstokheader_id', 'pengeluaranstokheader.id')
        // ->leftJoin(DB::raw("stok with (readuncommitted)"), 'pengeluaranstokdetail.stok_id', 'stok.id')
        // ->whereRaw("MONTH(pengeluaranstokheader.tglbukti) = $month")
        // ->whereRaw("YEAR(pengeluaranstokheader.tglbukti) = $year");

        // $data = $query->get();
        // return $data;
    }
}
