<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class LaporanRitasiGandengan extends MyModel
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

    public function Export($periode)
    {
        // $pengeluaranStok = PengeluaranStok::where('kodepengeluaran', '=', 'SPK')->first();
        // $pengeluaranStok_id = $pengeluaranStok->id;

        //TempTrip
        $tempTrip = '##tempTrip' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempTrip, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->datetime('tglbukti')->nullable();
            $table->BigInteger('gandengan_id')->nullable();
        });

        $queryTempTrip = DB::table("suratpengantar AS A")
            ->select("A.nobukti", "A.tglbukti", "A.gandengan_id")
            ->whereRaw("MONTH(A.tglbukti) = CAST(LEFT(?, 2) AS integer)", [$periode])
            ->whereRaw("YEAR(A.tglbukti) = CAST(RIGHT(?, 4) AS integer)", [$periode]);

        DB::table($tempTrip)->insertUsing(['nobukti', 'tglbukti', 'gandengan_id'], $queryTempTrip);

        
        //JumlahTripGandengan
        $jumlahTripGandengan = '##jumlahTripGandengan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($jumlahTripGandengan, function ($table) {
            $table->string('gandengan_id', 50)->nullable();
            $table->integer('tanggal')->nullable();
            $table->integer('jumlah')->nullable();
        });

        $queryJumlahTripGandengan = DB::table($tempTrip)
            ->select('gandengan_id', DB::raw('DAY(tglbukti) AS tanggal'), DB::raw('COUNT(nobukti) AS jumlah'))
            ->groupBy('gandengan_id', DB::raw('DAY(tglbukti)'));

        DB::table($jumlahTripGandengan)->insertUsing(['gandengan_id', 'tanggal', 'jumlah'], $queryJumlahTripGandengan);

        //TempGandengan
        $tempGandengan = '##tempGandengan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempGandengan, function ($table) {
            $table->integer('id');
            $table->string('gandengan', 100)->nullable();
        });

        $queryTempGandengan = DB::table('gandengan')
            ->select('id', 'kodegandengan')
            ->where('statusaktif', 1);

        DB::table($tempGandengan)->insertUsing([
            'id',
            'gandengan'
        ], $queryTempGandengan);
        

        //TempTglGandengan
        $tgl1 = Carbon::createFromFormat('m-Y', substr($periode, 0, 2) . '-' . substr($periode, -4))->startOfMonth();
        $tgl2 = Carbon::parse($tgl1)->addDays(33)->toDateString();
        $tgl3 = Carbon::parse($tgl2)->startOfMonth()->subDay()->toDateString();

        $atgl1 = Carbon::parse($tgl1)->day;
        $atgl2 = Carbon::parse($tgl3)->day;

        $tempTglGandengan = '##tempTglGandengan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempTglGandengan, function ($table) {
            $table->integer('gandengan_id')->nullable();
            $table->integer('tgl')->nullable();
            $table->datetime('tglbukti')->nullable();
        });

        $btglbukti = $tgl1->copy();

        while ($atgl1 <= $atgl2) {
            $btglbukti->day = $atgl1;
            $queryTempGandengan = DB::table($tempGandengan)->select('id')->get();

            foreach ($queryTempGandengan as $row) {
                DB::table($tempTglGandengan)->insert([
                    'gandengan_id' => $row->id,
                    'tgl' => $atgl1,
                    'tglbukti' => $btglbukti,
                ]);
            }
            $atgl1++;
        }
        // $data = DB::table($tempTglGandengan)->get();

        //Absensi
        $absensi = '##absensi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($absensi, function ($table) {
            $table->integer('absen_id');
            $table->integer('tgl')->nullable();
            $table->integer('gandengan_id')->nullable();
            
        });

        DB::table($absensi)->where('absen_id', 0)->delete(); 

        //TempRekap    
        $tempRekap = '##tempRekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempRekap, function ($table) {
            $table->string('gandengan', 500)->nullable();
            $table->integer('tgl')->nullable();
            $table->string('jumlah', 10)->nullable();
        });
        
        $queryTempRekap = DB::table($tempTglGandengan .' AS A')
            ->leftJoin($jumlahTripGandengan .' AS B', function ($join) {
                $join->on('A.gandengan_id', '=', 'B.gandengan_id')
                    ->on('A.tgl', '=', 'B.tanggal');
            })
            ->leftJoin($absensi .' AS C', function ($join) {
                $join->on('A.tgl', '=', 'C.tgl')
                    ->on('A.gandengan_id', '=', 'C.gandengan_id');
            })
            ->leftJoin('harilibur AS E', 'A.tglbukti', '=', 'E.tgl')
            ->join('gandengan AS F', 'A.gandengan_id', '=', 'F.id')
            ->select('F.keterangan AS nopol', 'A.tgl', DB::raw("CASE
                WHEN ISNULL(E.keterangan, '') <> '' THEN 'L'
                WHEN DATEPART(dw, A.tglbukti) = 1 THEN 'L'
                WHEN ISNULL(C.gandengan_id, 0) = 0 THEN FORMAT(ISNULL(B.jumlah, 0), '#')
                ELSE 'L' END AS jumlah"))
            ->orderBy('F.keterangan')
            ->orderBy('A.tgl');

        DB::table($tempRekap)->insertUsing(['gandengan', 'tgl', 'jumlah'], $queryTempRekap);
        
        //TempRekapTanggal
        $tempRkpTgl = '##tempRkpTgl' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempRkpTgl, function ($table) {
            $table->integer('tgl')->nullable();
        });

        $queryTempRkpTgl = DB::table($tempRekap)
            ->select('tgl')
            ->groupBy('tgl');

        DB::table($tempRkpTgl)->insertUsing(['tgl'], $queryTempRkpTgl);

        //Pivot
        $columnstgl = '';
        $result = DB::table($tempRkpTgl)
            ->select(DB::raw("
                COALESCE((
                    SELECT STUFF((
                        SELECT ',[' + CAST(tgl AS VARCHAR) + ']'
                        FROM $tempRkpTgl
                        ORDER BY tgl ASC
                        FOR XML PATH(''), TYPE
                    ).value('.', 'VARCHAR(MAX)'), 1, 1, '')
                ), '') AS columnstgl
            "))
            ->first();

        if ($result) {
            $columnstgl = $result->columnstgl;
        }  

        $query = "select gandengan as gandengan, " . $columnstgl . " 
        from (select A.gandengan, A.tgl, A.jumlah from " . $tempRekap . " A) 
        as SourceTable 
        Pivot (
            Max(jumlah) for tgl IN (" . $columnstgl . ")
        ) as PivotTable order by gandengan ";
        
        $data = DB::select(DB::raw($query));

        return $data;
    }

    public function getHeader($periode)
    {
        $exportQuery = $this->Export($periode);
        $data = [];
        if (!empty($exportQuery)) {
            $headers = array_keys((array) $exportQuery[0]);
            $data = array_merge( $headers);
        }
        return $data;
    }

    public function getData($periode)
    {
        $data = $this->Export($periode);
        $result = [];
        foreach ($data as $item) 
        {
            foreach ($item as $value) 
            {
                $result[] = $value;
            }
        }
        return $result;
    }
}
