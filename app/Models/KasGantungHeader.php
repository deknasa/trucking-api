<?php

namespace App\Models;

use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Api\PengeluaranHeaderController;
use App\Http\Requests\UpdatePengeluaranHeaderRequest;
use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KasGantungHeader extends MyModel
{
    use HasFactory;

    protected $table = 'kasgantungheader';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    // protected $casts = [
    //     'tgl' => 'date:d-m-Y',
    //     'tglkaskeluar' => 'date:d-m-Y',
    //     'created_at' => 'date:d-m-Y H:i:s',
    //     'updated_at' => 'date:d-m-Y H:i:s'
    // ];     

    public function kasgantungDetail()
    {
        return $this->hasMany(KasGantungDetail::class, 'kasgantung_id');
    }

    // public function bank() {
    //     return $this->belongsTo(Bank::class, 'bank_id');
    // }

    // public function penerima() {
    //     return $this->belongsTo(Penerima::class, 'penerima_id');
    // }


    public function cekvalidasiaksi($nobukti)
    {
        $error = new Error();
        $keteranganerror = $error->cekKeteranganError('TDT') ?? '';
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';

        $absensiSupir = DB::table('absensisupirheader')
            ->from(
                DB::raw("absensisupirheader as a with (readuncommitted)")
            )
            ->select(
                'a.kasgantung_nobukti',
                'a.nobukti'
            )
            ->where('a.kasgantung_nobukti', '=', $nobukti)
            ->first();
        if (isset($absensiSupir)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> Absensi Supir <b>' . $absensiSupir->nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Absensi Supir ' . $absensiSupir->nobukti,
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('SATL2');

        $pengembalianKasgantung = DB::table('pengembaliankasgantungdetail')
            ->from(
                DB::raw("pengembaliankasgantungdetail as a with (readuncommitted)")
            )
            ->select(
                'a.kasgantung_nobukti',
                'a.nobukti'
            )
            ->where('a.kasgantung_nobukti', '=', $nobukti)
            ->first();
        if (isset($pengembalianKasgantung)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti Pengembalian Kas Gantung <b>' . $pengembalianKasgantung->nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Pengembalian Kas Gantung ' . $pengembalianKasgantung->nobukti,
                'kodeerror' => 'SATL2'
            ];
            goto selesai;
        }


        $keteranganerror = $error->cekKeteranganError('SAPP');

        $jurnal = DB::table('kasgantungheader')
            ->from(
                DB::raw("kasgantungheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.pengeluaran_nobukti'
            )
            ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'a.pengeluaran_nobukti', 'b.nobukti')
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($jurnal)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti Approval Jurnal <b>' . $jurnal->pengeluaran_nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Approval Jurnal ' . $jurnal->pengeluaran_nobukti,
                'kodeerror' => 'SAPP'
            ];
            goto selesai;
        }

        $data = [
            'kondisi' => false,
            'keterangan' => '',
        ];
        selesai:
        return $data;
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('bank', 255)->nullable();
        });


        $bank = DB::table('bank')->from(
            DB::raw('bank with (readuncommitted)')
        )
            ->select(
                'id as bank_id',
                'namabank as bank',

            )
            ->where('tipe', '=', 'KAS')
            ->where('statusaktif', 1)
            ->first();

        DB::table($tempdefault)->insert(
            ["bank_id" => $bank->bank_id, "bank" => $bank->bank]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'bank_id',
                'bank',
            );

        $data = $query->first();

        return $data;
    }

    public function get()
    {
        $this->setRequestParameters();

        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';

        $tempTable = '##tempTable' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempTable, function ($table) {
            $table->string('nobukti')->nullable();
            $table->longText('nobukti_asal')->nullable();
            $table->longText('url_asal')->nullable();
        });
        $petik = '"';
        $url = config('app.url_fe') . 'pengembaliankasgantungheader';
        $getDataLain = DB::table("pengembaliankasgantungdetail")->from(DB::raw("pengembaliankasgantungdetail as a with (readuncommitted)"))
            ->select(DB::raw("b.nobukti, STRING_AGG(cast(a.nobukti as nvarchar(max)), ', ') as nobukti_asal, STRING_AGG(cast('<a href=$petik" . $url . "?tgldari='+(format(c.tglbukti,'yyyy-MM')+'-1')+'&tglsampai='+(format(c.tglbukti,'yyyy-MM')+'-31')+'&nobukti='+a.nobukti+'$petik class=$petik link-color $petik target=$petik _blank $petik>'+a.nobukti+'</a>' as nvarchar(max)), ',') as url_asal"))
            ->join(DB::raw("kasgantungheader as b with (readuncommitted)"), 'a.kasgantung_nobukti', 'b.nobukti')
            ->join(DB::raw("pengembaliankasgantungheader as c with (readuncommitted)"), 'c.nobukti', 'a.nobukti')
            ->groupBy("b.nobukti");
        if (request()->tgldari && request()->tglsampai) {
            $getDataLain->whereBetween('b.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        
        DB::table($tempTable)->insertUsing(['nobukti', 'nobukti_asal', 'url_asal'], $getDataLain);

        $query = DB::table($this->table)->from(DB::raw("kasgantungheader with (readuncommitted)"))
            ->select(
                'kasgantungheader.id',
                'kasgantungheader.nobukti',
                'kasgantungheader.tglbukti',
                'kasgantungheader.penerima',
                'penerima.namapenerima as penerima_id',
                'bank.namabank as bank_id',
                'kasgantungheader.pengeluaran_nobukti',
                'kasgantungheader.coakaskeluar',
                db::raw("(case when year(isnull(kasgantungheader.tglkaskeluar,'1900/1/1'))=1900 then null else kasgantungheader.tglkaskeluar end) as tglkaskeluar"),
                db::raw("(case when year(isnull(kasgantungheader.tglbukacetak,'1900/1/1'))=1900 then null else kasgantungheader.tglbukacetak end) as tglbukacetak"),
                'kasgantungheader.postingdari',
                'kasgantungheader.userbukacetak',
                'kasgantungheader.jumlahcetak',
                'statuscetak.memo as statuscetak',
                'kasgantungheader.modifiedby',
                'kasgantungheader.created_at',
                'kasgantungheader.updated_at',
                db::raw("cast((format(pengeluaran.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpengeluaranheader"),
                db::raw("cast(cast(format((cast((format(pengeluaran.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpengeluaranheader"),
                'kasgantungheader.bank_id as pengeluaranbank_id',
                DB::raw("cast(isnull(asal.nobukti_asal, '') as nvarchar(max)) as nobukti_asal"),
                DB::raw("cast(isnull(asal.url_asal, '') as nvarchar(max)) as url_asal")

            )

            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'kasgantungheader.statuscetak', 'parameter.id')
            ->leftJoin(DB::raw("penerima with (readuncommitted)"), 'kasgantungheader.penerima_id', 'penerima.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'kasgantungheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("pengeluaranheader as pengeluaran with (readuncommitted)"), 'kasgantungheader.pengeluaran_nobukti', '=', 'pengeluaran.nobukti')
            ->leftJoin(DB::raw("$tempTable as asal with (readuncommitted)"), 'kasgantungheader.nobukti', 'asal.nobukti')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'kasgantungheader.bank_id', 'bank.id');
        if (request()->tgldari && request()->tglsampai) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(kasgantungheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(kasgantungheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("kasgantungheader.statuscetak", $statusCetak);
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function findUpdate($id)
    {
        $query = KasGantungHeader::from(DB::raw("kasgantungheader with (readuncommitted)"))
            ->select(
                'kasgantungheader.id',
                'kasgantungheader.nobukti',
                'kasgantungheader.tglbukti',
                'kasgantungheader.penerima',
                DB::raw("(case when kasgantungheader.penerima_id=0 then null else kasgantungheader.penerima_id end) as penerima_id"),
                'kasgantungheader.bank_id',
                'bank.namabank as bank',
                'kasgantungheader.pengeluaran_nobukti',
                'kasgantungheader.statuscetak',
                'kasgantungheader.coakaskeluar',
                'kasgantungheader.tglkaskeluar',
                'kasgantungheader.tglbukacetak',
                'kasgantungheader.statuscetak',
                'kasgantungheader.userbukacetak',
                'kasgantungheader.jumlahcetak',
                'kasgantungheader.modifiedby',
                'kasgantungheader.created_at',
                'kasgantungheader.updated_at'
            )
            ->leftJoin(DB::raw("penerima with (readuncommitted)"), 'kasgantungheader.penerima_id', 'penerima.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'kasgantungheader.bank_id', 'bank.id')
            ->where('kasgantungheader.id', $id);

        $data = $query->first();

        return $data;
    }

    public function selectColumns($query)
    {
        $tempTable = '##tempTable' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempTable, function ($table) {
            $table->string('nobukti')->nullable();
            $table->longText('nobukti_asal')->nullable();
        });
        $getDataLain = DB::table("pengembaliankasgantungdetail")->from(DB::raw("pengembaliankasgantungdetail as a with (readuncommitted)"))
            ->select(DB::raw("b.nobukti, STRING_AGG(cast(a.nobukti as nvarchar(max)), ', ') as nobukti_asal"))
            ->join(DB::raw("kasgantungheader as b with (readuncommitted)"), 'a.kasgantung_nobukti', 'b.nobukti')
            ->groupBy("b.nobukti");
        if (request()->tgldariheader && request()->tglsampaiheader) {
            $getDataLain->whereBetween('b.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        }
        
        DB::table($tempTable)->insertUsing(['nobukti', 'nobukti_asal'], $getDataLain);
        
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "$this->table.id,
            $this->table.nobukti,
            $this->table.tglbukti,
            'bank.namabank as bank_id',
            $this->table.pengeluaran_nobukti,
            $this->table.coakaskeluar,
            $this->table.tglkaskeluar,
            'statuscetak.text as statuscetak',
            $this->table.userbukacetak,
            $this->table.tglbukacetak,
            $this->table.jumlahcetak,
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at,
            $this->table.penerima,
            cast(isnull(asal.nobukti_asal, '') as nvarchar(max)) as nobukti_asal
            "

                )
            )
            ->leftJoin(DB::raw("penerima with (readuncommitted)"), 'kasgantungheader.penerima_id', 'penerima.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'kasgantungheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("$tempTable as asal with (readuncommitted)"), 'kasgantungheader.nobukti', 'asal.nobukti')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'kasgantungheader.bank_id', 'bank.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('bank_id', 1000)->nullable();
            $table->string('pengeluaran_nobukti', 1000)->nullable();
            $table->string('coakaskeluar', 1000)->nullable();
            $table->date('tglkaskeluar')->nullable();
            $table->string('statuscetak', 1000)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->longText('penerima')->nullable();
            $table->longText('nobukti_asal')->nullable();
            $table->increments('position');
        });

        // if ((date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tgldariheader))) || (date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tglsampaiheader)))) {
        //     request()->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
        //     request()->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
        // }

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        $models =  $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        // dd( $models);
        DB::table($temp)->insertUsing(
            [
                'id', 'nobukti', 'tglbukti',  'bank_id', 'pengeluaran_nobukti', 'coakaskeluar',
                'tglkaskeluar', 'statuscetak', 'userbukacetak', 'tglbukacetak', 'jumlahcetak', 'modifiedby', 'created_at', 'updated_at', 'penerima','nobukti_asal'
            ],
            $models
        );

        return  $temp;
    }




    public function getKasGantung($dari, $sampai)
    {

        $tempPribadi = $this->createTempKasGantung($dari, $sampai);
        $query = DB::table($tempPribadi)->from(DB::raw("$tempPribadi with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $tempPribadi.nobukti) as id,$tempPribadi.tglbukti,$tempPribadi.nobukti,$tempPribadi.sisa,$tempPribadi.keterangan as keterangandetail"))
            ->where(function ($query) use ($tempPribadi) {
                $query->whereRaw("$tempPribadi.sisa != 0")
                    ->orWhereRaw("$tempPribadi.sisa is null");
            });
        return $query->get();
    }

    public function createTempKasGantung($dari, $sampai)
    {
        $bank_id = request()->bank_id;

        $isTangki = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'ABSENSI TANGKI')->first()->text ?? 'TIDAK';
        $tempabsensi = '##tempabsensi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempabsensi, function ($table) {
            $table->string('nobukti')->nullabble();
        });

        if ($isTangki == 'YA') {
            $fetchAbsensi = DB::table("absensisupirproses")->from(DB::raw("absensisupirproses as p with (readuncommitted)"))
            ->select(DB::raw("p.kasgantung_nobukti as nobukti"))
            ->leftJoin(DB::raw("absensisupirheader as a with (readuncommitted)"), 'p.nobukti', 'a.nobukti')
            ->leftJoin(DB::raw("kasgantungheader as k with (readuncommitted)"), 'p.kasgantung_nobukti', 'k.nobukti')
            ->whereBetween('a.tglbukti', [$dari, $sampai])
            ->where('k.bank_id', $bank_id);
        } else {
            $fetchAbsensi = DB::table("absensisupirheader")->from(DB::raw("absensisupirheader as a with (readuncommitted)"))
            ->select(DB::raw("a.kasgantung_nobukti as nobukti"))
            ->leftJoin(DB::raw("kasgantungheader as k with (readuncommitted)"), 'a.kasgantung_nobukti', 'k.nobukti')
            ->whereBetween('a.tglbukti', [$dari, $sampai]);
        }
        
        DB::table($tempabsensi)->insertUsing(['nobukti'], $fetchAbsensi);

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = DB::table('kasgantungdetail')
            ->from(
                DB::raw("kasgantungdetail with (readuncommitted)")
            )
            ->select(DB::raw("kasgantungdetail.nobukti,kasgantungheader.tglbukti,(SELECT (sum(kasgantungdetail.nominal) - coalesce(SUM(pengembaliankasgantungdetail.nominal),0)) FROM pengembaliankasgantungdetail WHERE pengembaliankasgantungdetail.kasgantung_nobukti= kasgantungdetail.nobukti) AS sisa, MAX(kasgantungdetail.keterangan)"))
            ->leftJoin('kasgantungheader', 'kasgantungheader.id', 'kasgantungdetail.kasgantung_id')
            ->leftJoin(DB::raw("$tempabsensi as c with (readuncommitted)"), 'kasgantungheader.nobukti', 'c.nobukti')
            ->whereBetween('kasgantungheader.tglbukti', [$dari, $sampai])
            ->where('kasgantungheader.bank_id', $bank_id)
            ->whereRaw("isnull(c.nobukti,'')=''")
            ->groupBy('kasgantungdetail.nobukti', 'kasgantungheader.tglbukti')
            ->orderBy('kasgantungheader.tglbukti', 'asc')
            ->orderBy('kasgantungdetail.nobukti', 'asc');

        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->date('tglbukti');
            $table->bigInteger('sisa')->nullable();
            $table->longText('keterangan')->nullabble();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'tglbukti', 'sisa', 'keterangan'], $fetch);
        //dd($tes);
        return $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'bank_id') {
            return $query->orderBy('bank.namabank', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'penerima_id') {
            return $query->orderBy('penerima.namapenerima', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statuscetak') {
                                $query = $query->where('statuscetak.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'penerima_id') {
                                $query = $query->where('penerima.namapenerima', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'bank_id') {
                                $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglbukacetak') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'url_asal') {
                                $query = $query->where('asal.nobukti_asal', 'LIKE', "%$filters[data]%");
                            } else {
                                // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'statuscetak') {
                                    $query = $query->orWhere('statuscetak.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'penerima_id') {
                                    $query = $query->orWhere('penerima.namapenerima', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'bank_id') {
                                    $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglbukacetak') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'url_asal') {
                                    $query = $query->orWhere('asal.nobukti_asal', 'LIKE', "%$filters[data]%");
                                } else {
                                    // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                                }
                            }
                        }
                    });
                    break;
                default:

                    break;
            }

            $this->totalRows = $query->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }
        if (request()->cetak && request()->periode) {
            $query->where('kasgantungheader.statuscetak', '<>', request()->cetak)
                ->whereYear('kasgantungheader.tglbukti', '=', request()->year)
                ->whereMonth('kasgantungheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function getSisaPengembalianForValidasi($nobukti)
    {

        $fetch = DB::table('kasgantungdetail')
            ->from(
                DB::raw("kasgantungdetail with (readuncommitted)")
            )
            ->select(DB::raw("kasgantungdetail.nobukti,(SELECT (sum(kasgantungdetail.nominal) - coalesce(SUM(pengembaliankasgantungdetail.nominal),0)) FROM pengembaliankasgantungdetail WHERE pengembaliankasgantungdetail.kasgantung_nobukti= kasgantungdetail.nobukti) AS sisa"))
            ->whereRaw("kasgantungdetail.nobukti = '$nobukti'")
            ->groupBy('kasgantungdetail.nobukti');
        // ->first();

        return $fetch->first();
    }

    public function processStore(array $data): KasGantungHeader
    {
        $tanpaprosesnobukti = $data['tanpaprosesnobukti'] ?? 0;

        /* Store header */
        $bank = Bank::find($data['bank_id']);
        $coakaskeluar = $bank->coa ?? null;
        $group = 'KAS GANTUNG';
        $subgroup = 'NOMOR KAS GANTUNG';
        $format = DB::table('parameter')
            ->where('grp', $group)
            ->where('subgrp', $subgroup)
            ->first();

        $content['tgl'] = date('Y-m-d', strtotime($data['tglbukti']));


        $coaKasKeluar = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', 'COA KAS GANTUNG')->first();

        $kasgantungHeader = new KasGantungHeader();

        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $kasgantungHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti'])) ?? '1900/1/1';
        $kasgantungHeader->penerima = $data['penerima'] ?? '';
        $kasgantungHeader->bank_id = $data['bank_id'] ?? 0;
        $kasgantungHeader->pengeluaran_nobukti = $data['pengeluaran_nobukti'];
        $kasgantungHeader->coakaskeluar = $data['coakaskeluar'] ?? $coakaskeluar;
        $kasgantungHeader->postingdari = $data['postingdari'] ?? 'ENTRY KAS GANTUNG';
        $kasgantungHeader->tglkaskeluar = date('Y-m-d', strtotime($data['tglbukti']));
        $kasgantungHeader->modifiedby = auth('api')->user()->name;
        $kasgantungHeader->info = html_entity_decode(request()->info);
        $kasgantungHeader->statusformat = $format->id ?? $data['statusformat'];
        $kasgantungHeader->statuscetak = $statusCetak->id ?? 0;
        $kasgantungHeader->userbukacetak = '';
        $kasgantungHeader->tglbukacetak = '';
        $kasgantungHeader->nobukti = (new RunningNumberService)->get($group, $subgroup, $kasgantungHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$kasgantungHeader->save()) {
            throw new \Exception("Error storing kas gantung header.");
        }

        $pengeluaranHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($kasgantungHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? 'ENTRY KAS GANTUNG HEADER',
            'idtrans' => $kasgantungHeader->id,
            'nobuktitrans' => $kasgantungHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $kasgantungHeader->toArray(),
            'modifiedby' => $kasgantungHeader->modifiedby
        ]);

        $detaillog = [];

        $noWarkat = [];
        $tglJatuhTempo = [];
        $detail = [];
        $coaDebet = [];
        $coaKredit = [];
        $keterangan_detail = [];
        $nominal = [];
        $total = 0;

        $dataCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'JURNAL KAS GANTUNG')->where('subgrp', 'DEBET')->first();
        $memo = json_decode($dataCoaDebet->memo, true);
        $memo['JURNAL'] = $bank->coagantung;

        for ($i = 0; $i < count($data['nominal']); $i++) {

            $kasgantungDetail = (new KasGantungDetail())->processStore($kasgantungHeader, [
                'kasgantung_id' => $kasgantungHeader->id,
                'nobukti' => $kasgantungHeader->nobukti,
                'nominal' => $data['nominal'][$i],
                'coa' => $coakaskeluar,
                'keterangan' => $data['keterangan_detail'][$i],
                'modifiedby' => auth('api')->user()->name,
            ]);

            $detaillog[] = $kasgantungDetail;

            $total += $data['nominal'][$i];
            $noWarkat[] = 0;
            $tglJatuhTempo[] = $data['tglbukti'];
            $coaKredit[] = $data['coakredit'][$i] ?? $coakaskeluar;
            $coaDebet[] = $data['coadebet'][$i] ?? $memo['JURNAL'];
            $keterangan_detail[] = "($kasgantungHeader->nobukti) " . $data['keterangan_detail'][$i];
            $nominal[] = $data['nominal'][$i];
        }
        $prosesLain = $data['proseslain'] ?? '';

        if ($prosesLain == '') {

            $parameterController = new ParameterController;
            $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');

            if ($bank->tipe == 'KAS') {
                $jenisTransaksi = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', 'JENIS TRANSAKSI')->where('text', 'KAS')->first();
            }
            if ($bank->tipe == 'BANK') {
                $jenisTransaksi = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', 'JENIS TRANSAKSI')->where('text', 'BANK')->first();
            }

            $namaPenerima = ($data['penerima'] != null) ? $data['penerima'] : '';


            $alatbayar = AlatBayar::from(DB::raw("alatbayar with (readuncommitted)"))->where('bank_id', $bank->id)->first();
            if ($bank->tipe == 'KAS') {
                $alatbayar = AlatBayar::from(DB::raw("alatbayar with (readuncommitted)"))->where('tipe', $bank->tipe)->first();
            }


            (new LogTrail())->processStore([
                'namatabel' => strtoupper($kasgantungHeader->getTable()),
                'postingdari' => 'ENTRY KAS GANTUNG DETAIL',
                'idtrans' =>  $kasgantungHeader->id,
                'nobuktitrans' => $kasgantungHeader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => auth('api')->user()->name,
            ]);


            $pengeluaranRequest = [
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'pelanggan_id' => 0,
                'postingdari' => 'ENTRY KAS GANTUNG',
                'dibayarke' => $namaPenerima ?? '',
                'alatbayar_id' => $alatbayar->id,
                'bank_id' => $bank->id,
                'nowarkat' => $noWarkat,

                'tgljatuhtempo' =>  $tglJatuhTempo,
                'coadebet' => $coaDebet,
                'coakredit' => $coaKredit,
                'keterangan_detail' => $keterangan_detail,
                'nominal_detail' => $nominal
            ];

            $pengeluaranHeader = (new PengeluaranHeader())->processStore($pengeluaranRequest);
            $kasgantungHeader->pengeluaran_nobukti = $pengeluaranHeader->nobukti;
            $kasgantungHeader->save();
        }

        return $kasgantungHeader;
    }

    public function processUpdate(KasGantungHeader $kasgantungHeader, array $data): KasGantungHeader
    {
        $nobuktiOld = $kasgantungHeader->nobukti;
        $getTgl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'EDIT TANGGAL BUKTI')->where('subgrp', 'KAS GANTUNG')->first();
        if (trim($getTgl->text) == 'YA') {
            $group = 'KAS GANTUNG';
            $subgroup = 'NOMOR KAS GANTUNG';

            $querycek = DB::table('kasgantungheader')->from(
                DB::raw("kasgantungheader a with (readuncommitted)")
            )
                ->select(
                    'a.nobukti'
                )
                ->where('a.id', $kasgantungHeader->id)
                ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
                ->first();


            if (isset($querycek)) {
                $nobukti = $querycek->nobukti;
            } else {
                $nobukti = (new RunningNumberService)->get($group, $subgroup, $kasgantungHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
            }

            $kasgantungHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
            $kasgantungHeader->nobukti = $nobukti;
        }

        $isUpdateUangJalan = $data['isUpdateUangJalan'] ?? 0;
        $bank_id = $data['bank_id'] ?? $kasgantungHeader->bank_id;
        $bank_id = $bank_id->id ?? $bank_id;
        $bank = Bank::from(DB::raw("bank with (readuncommitted)"))->find($bank_id);
        $coakaskeluar = $bank->coa ?? null;
        $kasgantungHeader->penerima = $data['penerima'] ?? '';
        $kasgantungHeader->coakaskeluar = $data['coakaskeluar'] ?? $coakaskeluar;
        $kasgantungHeader->postingdari = $data['postingdari'] ?? 'EDIT KAS GANTUNG';
        $kasgantungHeader->modifiedby = auth('api')->user()->name;
        $kasgantungHeader->info = html_entity_decode(request()->info);

        if (!$kasgantungHeader->save()) {
            throw new \Exception("Error Update kas gantung header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($kasgantungHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? 'EDIT KAS GANTUNG HEADER',
            'idtrans' => $kasgantungHeader->id,
            'nobuktitrans' => $kasgantungHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $kasgantungHeader->toArray(),
            'modifiedby' => $kasgantungHeader->modifiedby
        ]);

        KasgantungDetail::where('kasgantung_id', $kasgantungHeader->id)->lockForUpdate()->delete();

        $detaillog = [];

        $noWarkat = [];
        $tglJatuhTempo = [];
        $detail = [];
        $coaDebet = [];
        $coaKredit = [];
        $keterangan_detail = [];
        $nominal = [];
        $total = 0;

        $coakredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'JURNAL KAS GANTUNG')->where('subgrp', 'DEBET')->first();
        $memo =  json_decode($coakredit->memo, true);
        $memo['JURNAL'] = $bank->coagantung;

        // $penerima = Penerima::from(DB::raw("penerima with (readuncommitted)"))->where("id", $request->penerima_id)->first();
        $namaPenerima = ($data['penerima'] != null) ? $data['penerima'] : '';
        $alatbayar = ($bank != null) ? AlatBayar::from(DB::raw("alatbayar with (readuncommitted)"))->where('bank_id', $bank->id)->first() : '';


        for ($i = 0; $i < count($data['nominal']); $i++) {

            $kasgantungDetail = (new KasGantungDetail())->processStore($kasgantungHeader, [
                'nobukti' => $kasgantungHeader->nobukti,
                'nominal' => ($isUpdateUangJalan != 0) ? $data['datadetail'][$i]['nominal'] : $data['nominal'][$i],
                'coa' => ($isUpdateUangJalan != 0) ? '' : $coakaskeluar,
                'keterangan' => ($isUpdateUangJalan != 0) ? $data['datadetail'][$i]['keterangan'] : $data['keterangan_detail'][$i],
                'modifiedby' => auth('api')->user()->name,
            ]);


            $detaillog[] = $kasgantungDetail;

            $noWarkat[] = 0;
            $tglJatuhTempo[] = $data['tglbukti'];
            $coaKredit[] = $data['coakredit'][$i] ?? $coakaskeluar;
            $coaDebet[] = $data['coadebet'][$i] ?? $memo['JURNAL'];
            $keterangan_detail[] = "($kasgantungHeader->nobukti) " . $data['keterangan_detail'][$i];
            $nominal[] = $data['nominal'][$i];

            $total += $data['nominal'][$i];
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($kasgantungHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? 'EDIT KAS GANTUNG DETAIL',
            'idtrans' =>  $kasgantungHeader->id,
            'nobuktitrans' => $kasgantungHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $detaillog,
            'modifiedby' => auth('api')->user()->name,
        ]);

        $pengeluaranRequest = [
            'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
            'pelanggan_id' => 0,
            'postingdari' => 'ENTRY KAS GANTUNG',
            'dibayarke' => $namaPenerima ?? '',
            'alatbayar_id' => $alatbayar->id ?? '',
            'bank_id' => $bank->id ?? '',
            'nowarkat' => $noWarkat,

            'tgljatuhtempo' =>  $tglJatuhTempo,
            'coadebet' => $coaDebet,
            'coakredit' => $coaKredit,
            'keterangan_detail' => $keterangan_detail,
            'nominal_detail' => $nominal
        ];




        if ($bank && $bank->tipe == 'KAS') {
            $jenisTransaksi = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'JENIS TRANSAKSI')->where('text', 'KAS')->first();
        }
        if ($bank && $bank->tipe == 'BANK') {
            $jenisTransaksi = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'JENIS TRANSAKSI')->where('text', 'BANK')->first();
        }

        $get = PengeluaranHeader::from(DB::raw("pengeluaranheader with (readuncommitted)"))->where('nobukti', $kasgantungHeader->pengeluaran_nobukti)->first();
        $approvalabsensisupir = $data['approvalabsensisupir'] ?? false;
        $data['from'] = $data['from'] ?? false;

        if ($data['from'] == "AbsensiSupirApprovalHeader") {
            $querysubgrppengeluaran = DB::table('bank')->from(DB::raw("bank with (readuncommitted)"))
                ->select(
                    'parameter.grp',
                    'parameter.subgrp',
                    'bank.formatpengeluaran',
                    'bank.coa'
                )
                ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpengeluaran', 'parameter.id')
                ->whereRaw("bank.id = $bank_id")
                ->first();


            if (!$kasgantungHeader->save()) {
                throw new \Exception("Error storing Hutang header.");
            }

            $alatbayar = AlatBayar::from(DB::raw("alatbayar with (readuncommitted)"))->where('bank_id', $bank->id)->first();
            if ($bank->tipe == 'KAS') {
                $alatbayar = AlatBayar::from(DB::raw("alatbayar with (readuncommitted)"))->where('tipe', $bank->tipe)->first();
            }



            $pengeluaranRequest = [
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'pelanggan_id' => 0,
                'postingdari' => 'ENTRY KAS GANTUNG',
                'dibayarke' => $namaPenerima ?? '',
                'alatbayar_id' => $alatbayar->id,
                'bank_id' => $bank->id,
                'nowarkat' => $noWarkat,

                'tgljatuhtempo' =>  $tglJatuhTempo,
                'coadebet' => $coaDebet,
                'coakredit' => $coaKredit,
                'keterangan_detail' => $keterangan_detail,
                'nominal_detail' => $nominal
            ];

            $pengeluaran = (new PengeluaranHeader())->processStore($pengeluaranRequest);
            $kasgantungHeader->pengeluaran_nobukti = $pengeluaran->nobukti;
            $kasgantungHeader->save();
        } else {
            if ($get) {
                $newPengeluaran = new PengeluaranHeader();
                $newPengeluaran = $newPengeluaran->find($get->id);
                $pengeluaran = (new PengeluaranHeader())->processUpdate($newPengeluaran, $pengeluaranRequest);
                $kasgantungHeader->pengeluaran_nobukti = $pengeluaran->nobukti;
                $kasgantungHeader->save();
            }
        }

        return $kasgantungHeader;
    }

    public function processDestroy($id, $postingDari = ''): KasGantungHeader
    {
        $getDetail = KasGantungDetail::lockForUpdate()->where('kasgantung_id', $id)->get();


        $kasgantungHeader = new KasGantungHeader();
        $kasgantungHeader = $kasgantungHeader->lockAndDestroy($id);

        $kasgantungHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($kasgantungHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? 'EDIT KAS GANTUNG DETAIL',
            'idtrans' =>  $kasgantungHeader->id,
            'nobuktitrans' => $kasgantungHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $kasgantungHeader->toArray(),
            'modifiedby' => auth('api')->user()->name,
        ]);


        (new LogTrail())->processStore([
            'namatabel' => 'KASGANTUNGDETAIL',
            'postingdari' => $postingDari,
            'idtrans' => $kasgantungHeaderLogTrail['id'],
            'nobuktitrans' => $kasgantungHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $getDetail->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        $getPengeluaran = PengeluaranHeader::from(DB::raw("pengeluaranheader with (readuncommitted)"))->where('nobukti', $kasgantungHeader->pengeluaran_nobukti)->first();
        if ($getPengeluaran) {
            $pengeluaranHeader = (new PengeluaranHeader())->processDestroy($getPengeluaran->id, $postingDari);
        }
        return $kasgantungHeader;
    }

    public function getExport($id)
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($this->table)->from(DB::raw("kasgantungheader with (readuncommitted)"))
            ->select(
                'kasgantungheader.id',
                'kasgantungheader.nobukti',
                'kasgantungheader.tglbukti',
                'kasgantungheader.penerima',
                'penerima.namapenerima as penerima_id',
                'bank.namabank as bank_id',
                'kasgantungheader.pengeluaran_nobukti',
                'kasgantungheader.coakaskeluar',
                'kasgantungheader.postingdari',
                'kasgantungheader.jumlahcetak',
                'statuscetak.memo as statuscetak',
                'statuscetak.id as  statuscetak_id',
                DB::raw("'Bukti Kas Gantung' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'kasgantungheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("penerima with (readuncommitted)"), 'kasgantungheader.penerima_id', 'penerima.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'kasgantungheader.bank_id', 'bank.id');

        $data = $query->first();
        return $data;
    }
}
