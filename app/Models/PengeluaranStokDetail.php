<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PengeluaranStokDetail extends MyModel
{
    use HasFactory;

    protected $table = 'PengeluaranStokDetail';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get()
    {
        $this->setRequestParameters();

        $from = request()->from ?? '';
        $cabang = request()->cabang ?? '';
        // dd(request());
        if ($cabang == 'TNL') {
            $query = $this->getForTnl();
             goto endTnl;
         }

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));
        if (isset(request()->id)) {
            $query->where("$this->table.id", request()->id);
        }

        if (isset(request()->pengeluaranstokheader_id)) {
            $query->where("$this->table.pengeluaranstokheader_id", request()->pengeluaranstokheader_id);
        }

        $tempumuraki = '##tempumuraki' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempumuraki, function ($table) {
                $table->Integer('stok_id')->nullable();
                $table->integer('jumlahhari')->nullable();
                $table->date('tglawal')->nullable();
            });
    
            DB::table($tempumuraki)->insertUsing([
                'stok_id',
                'jumlahhari',
                'tglawal',
            ], (new SaldoUmurAki())->getallstok());
    
            $tempumuraki2 = '##tempumuraki2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempumuraki2, function ($table) {
                $table->Integer('stok_id')->nullable();
                $table->integer('jumlahhari')->nullable();
                $table->date('tglawal')->nullable();
            });
    
            $queryaki = db::table($tempumuraki)->from(db::raw($tempumuraki . " a "))
                ->select(
                    'a.stok_id',
                    db::raw("max(a.jumlahhari) as jumlahhari"),
                    db::raw("max(a.tglawal) as tglawal"),
                )
                ->groupby('a.stok_id');
    
            DB::table($tempumuraki2)->insertUsing([
                'stok_id',
                'jumlahhari',
                'tglawal',
            ],  $queryaki);
    
            //update total vulkanisir
            $tempvulkan = '##tempvulkan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempvulkan, function ($table) {
                $table->integer('stok_id')->nullable();
                $table->integer('vulkan')->nullable();
            });
            
            DB::table($tempvulkan)->insertUsing([
                'stok_id',
                'vulkan',
            ],(new Stok())->getVulkan());

        if (isset(request()->forReport) && request()->forReport) {
            $query->select(
                "$this->table.pengeluaranstokheader_id",
                "$this->table.nobukti",
                db::raw("trim(stok.namastok)+
                (case
                      when isnull(stok.kelompok_id,0)=1 then ' ( VULKANISIR KE-'+format(isnull(d1.vulkan,0),'#,#0')+', STATUS BAN :'+isnull(parameter.text,'') +' )' 
                else '' end)
                as stok"),
                "$this->table.stok_id",
                db::raw("isnull(pengeluaranStokdetail.qty,0) as qty"),
                // "$this->table.qty",
                db::raw("isnull(pengeluaranStokdetail.harga,0) as harga"),
                // "$this->table.harga",
                "$this->table.persentasediscount",
                "$this->table.nominaldiscount",
                "$this->table.total",
                "$this->table.keterangan",
                "$this->table.vulkanisirke",
                "$this->table.modifiedby",
            )
            ->leftJoin("stok", "$this->table.stok_id", "stok.id")
            ->leftJoin(db::raw($tempvulkan . " d1"), "stok.id", "d1.stok_id")
            ->leftJoin(db::raw($tempumuraki2 . " c1"), "stok.id", "c1.stok_id")
            ->leftJoin("parameter", "stok.statusban", "parameter.id");

            $this->totalRows = $query->count();
        } else {

            $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp', 'JUDULAN LAPORAN')
                ->where('subgrp', 'JUDULAN LAPORAN')
                ->first();

            $query->select(
                "$this->table.pengeluaranstokheader_id",
                "$this->table.nobukti",
                "$this->table.stok_id",
                "$this->table.id",
                "satuan.satuan as satuan",
                db::raw("trim(stok.namastok)+
                (case when isnull(stok.kelompok_id,0)=1 then ' ( VULKANISIR KE-'+format(isnull(d1.vulkan,0),'#,#0')+', STATUS BAN :'+isnull(statusban.text,'') +' )' 
                else '' end)
                as stok"),
                'statusreuse.memo as statusreuse',    
                db::raw("isnull(pengeluaranStokdetail.qty,0) as qty"),
                // "$this->table.qty",
                db::raw("isnull(pengeluaranStokdetail.harga,0) as harga"),
                // "$this->table.harga",
                "$this->table.persentasediscount",
                "$this->table.nominaldiscount",
                "$this->table.total",
                "$this->table.keterangan",
                "$this->table.vulkanisirke",
                "$this->table.statusban",
                "parameter.text as statusoli",
                "$this->table.modifiedby",
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
                ->leftJoin("pengeluaranstokheader", "$this->table.pengeluaranstokheader_id", "pengeluaranstokheader.id")
                ->leftJoin("stok", "$this->table.stok_id", "stok.id")
                ->leftJoin("satuan", "stok.satuan_id", "satuan.id")
                ->leftJoin(DB::raw("parameter as statusreuse with (readuncommitted)"), 'stok.statusreuse', 'statusreuse.id')
                ->leftJoin("parameter", "$this->table.statusoli", "parameter.id")
                ->leftJoin(db::raw($tempvulkan . " d1"), "stok.id", "d1.stok_id")
                ->leftJoin(db::raw($tempumuraki2 . " c1"), "stok.id", "c1.stok_id")
                ->leftJoin("parameter as statusban", "stok.statusban", "statusban.id");
            if($from == 'klaim')
            {
                $nobuktiStok = DB::table("pengeluaranstokheader")->where('id', request()->pengeluaranstokheader_id)->first()->nobukti ?? '';
                if($nobuktiStok != ''){
                    $stok_id = request()->stok_id ?? 0;
                    $query->whereRaw("pengeluaranstokdetail.stok_id not in (select stok_id from pengeluarantruckingdetail where pengeluaranstok_nobukti='$nobuktiStok' and stok_id != $stok_id)");
                }
            }

            $this->totalNominal = $query->sum('total');
            $this->filter($query);
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->sort($query);
            $this->paginate($query);
            // dd($query->toSql());
        }
        endTnl:
        return $query->get();
    }
    public function getForTnl()
    {
        $this->setRequestParameters();

        $from = request()->from ?? '';

        $query = DB::connection('srvtnl')->table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));

        if (isset(request()->id)) {
            $query->where("$this->table.id", request()->id);
        }

        if (isset(request()->pengeluaranstokheader_id)) {
            $query->where("$this->table.pengeluaranstokheader_id", request()->pengeluaranstokheader_id);
        }

        $tempumuraki = '##tempumuraki' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::connection('srvtnl')->create($tempumuraki, function ($table) {
                $table->Integer('stok_id')->nullable();
                $table->integer('jumlahhari')->nullable();
                $table->date('tglawal')->nullable();
            });
    
            DB::connection('srvtnl')->table($tempumuraki)->insertUsing([
                'stok_id',
                'jumlahhari',
                'tglawal',
            ], (new SaldoUmurAki())->getallstoktnl());
    
            $tempumuraki2 = '##tempumuraki2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::connection('srvtnl')->create($tempumuraki2, function ($table) {
                $table->Integer('stok_id')->nullable();
                $table->integer('jumlahhari')->nullable();
                $table->date('tglawal')->nullable();
            });
    
            $queryaki = db::connection('srvtnl')->table($tempumuraki)->from(db::raw($tempumuraki . " a "))
                ->select(
                    'a.stok_id',
                    db::raw("max(a.jumlahhari) as jumlahhari"),
                    db::raw("max(a.tglawal) as tglawal"),
                )
                ->groupby('a.stok_id');
    
            DB::connection('srvtnl')->table($tempumuraki2)->insertUsing([
                'stok_id',
                'jumlahhari',
                'tglawal',
            ],  $queryaki);
    
            //update total vulkanisir
            $tempvulkan = '##tempvulkan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::connection('srvtnl')->create($tempvulkan, function ($table) {
                $table->integer('stok_id')->nullable();
                $table->integer('vulkan')->nullable();
            });
            
            DB::connection('srvtnl')->table($tempvulkan)->insertUsing([
                'stok_id',
                'vulkan',
            ],(new Stok())->getVulkanTnl());

        if (isset(request()->forReport) && request()->forReport) {
            $query->select(
                "$this->table.pengeluaranstokheader_id",
                "$this->table.nobukti",
                db::raw("trim(stok.namastok)+
                (case
                      when isnull(stok.kelompok_id,0)=1 then ' ( VULKANISIR KE-'+format(isnull(d1.vulkan,0),'#,#0')+', STATUS BAN :'+isnull(parameter.text,'') +' )' 
                else '' end)
                as stok"),
                "$this->table.stok_id",
                db::raw("isnull(pengeluaranStokdetail.qty,0) as qty"),
                // "$this->table.qty",
                db::raw("isnull(pengeluaranStokdetail.harga,0) as harga"),
                // "$this->table.harga",
                "$this->table.persentasediscount",
                "$this->table.nominaldiscount",
                "$this->table.total",
                "$this->table.keterangan",
                "$this->table.vulkanisirke",
                "$this->table.modifiedby",
            )
            ->leftJoin("stok", "$this->table.stok_id", "stok.id")
            ->leftJoin(db::raw($tempvulkan . " d1"), "stok.id", "d1.stok_id")
            ->leftJoin(db::raw($tempumuraki2 . " c1"), "stok.id", "c1.stok_id")
            ->leftJoin("parameter", "stok.statusban", "parameter.id");

            $this->totalRows = $query->count();
        } else {

            $getJudul = DB::connection('srvtnl')->table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp', 'JUDULAN LAPORAN')
                ->where('subgrp', 'JUDULAN LAPORAN')
                ->first();

            $query->select(
                "$this->table.pengeluaranstokheader_id",
                "$this->table.nobukti",
                "$this->table.stok_id",
                "satuan.satuan as satuan",
                db::raw("trim(stok.namastok)+
                (case when isnull(stok.kelompok_id,0)=1 then ' ( VULKANISIR KE-'+format(isnull(d1.vulkan,0),'#,#0')+', STATUS BAN :'+isnull(statusban.text,'') +' )' 
                else '' end)
                as stok"),
                'statusreuse.memo as statusreuse',    
                db::raw("isnull(pengeluaranStokdetail.qty,0) as qty"),
                // "$this->table.qty",
                db::raw("isnull(pengeluaranStokdetail.harga,0) as harga"),
                // "$this->table.harga",
                "$this->table.persentasediscount",
                "$this->table.nominaldiscount",
                "$this->table.total",
                "$this->table.keterangan",
                "$this->table.vulkanisirke",
                "$this->table.statusban",
                "parameter.text as statusoli",
                "$this->table.modifiedby",
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
                ->leftJoin("pengeluaranstokheader", "$this->table.pengeluaranstokheader_id", "pengeluaranstokheader.id")
                ->leftJoin("stok", "$this->table.stok_id", "stok.id")
                ->leftJoin("satuan", "stok.satuan_id", "satuan.id")
                ->leftJoin(DB::raw("parameter as statusreuse with (readuncommitted)"), 'stok.statusreuse', 'statusreuse.id')
                ->leftJoin("parameter", "$this->table.statusoli", "parameter.id")
                ->leftJoin(db::raw($tempvulkan . " d1"), "stok.id", "d1.stok_id")
                ->leftJoin(db::raw($tempumuraki2 . " c1"), "stok.id", "c1.stok_id")
                ->leftJoin("parameter as statusban", "stok.statusban", "statusban.id");
            if($from == 'klaim')
            {
                $nobuktiStok = DB::connection('srvtnl')->table("pengeluaranstokheader")->where('id', request()->pengeluaranstokheader_id)->first()->nobukti ?? '';
                if($nobuktiStok != ''){
                    $stok_id = request()->stok_id ?? 0;
                    $query->whereRaw("pengeluaranstokdetail.stok_id not in (select stok_id from pengeluarantruckingdetail where pengeluaranstok_nobukti='$nobuktiStok' and stok_id != $stok_id)");
                }
            }

            $this->totalNominal = $query->sum('total');
            $this->filter($query);
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->sort($query);
            $this->paginate($query);
            // dd($query->toSql());
        }
        return $query;
    }
    public function getTNLForKlaim()
    {
        $server = config('app.url_tnl');
        $getToken = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])
            ->post($server . 'token', [
                'user' => 'ADMIN',
                'password' => getenv('PASSWORD_TNL'),
                'ipclient' => '',
                'ipserver' => '',
                'latitude' => '',
                'longitude' => '',
                'browser' => '',
                'os' => '',
            ]);
        $access_token = json_decode($getToken, TRUE)['access_token'];

        $getTrado = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json',
        ])

            ->get($server . "pengeluaranstokdetail?limit=0&pengeluaranstokheader_id=" . request()->pengeluaranstokheader_id);

        $data = $getTrado->json()['data'];

        $user = auth('api')->user()->name;
        $class = 'PengeluaranStokDetailController';

        $temtabel = 'tempspkdettnl' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;

        DB::table('listtemporarytabel')->insert(
            [
                'class' => $class,
                'namatabel' => $temtabel,
                'modifiedby' => $user,
                'created_at' => date('Y/m/d H:i:s'),
                'updated_at' => date('Y/m/d H:i:s'),
            ]
        );

        Schema::create($temtabel, function (Blueprint $table) {
            $table->integer('pengeluaranstokheader_id')->nullable();
            $table->string('nobukti', 300)->nullable();
            $table->integer('stok_id')->nullable();
            $table->string('stok', 300)->nullable();
            $table->double('qty', 15, 2)->nullable();
            $table->double('harga', 15, 2)->nullable();
            $table->double('persentasediscount', 15, 2)->nullable();
            $table->double('nominaldiscount', 15, 2)->nullable();
            $table->double('total', 15, 2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->unsignedBigInteger('vulkanisirke')->nullable();
            $table->string('statusoli', 100)->nullable();
            $table->string('modifiedby', 300)->nullable();
        });

        foreach ($data as $row) {
            unset($row['judul']);
            unset($row['tglcetak']);
            unset($row['usercetak']);
            DB::table($temtabel)->insert($row);
        }

        return $temtabel;
    }



    public function getAll($id)
    {
        $query = DB::table('PengeluaranStokDetail');
        $query = $query->select(
            'PengeluaranStokDetail.id',
            'PengeluaranStokDetail.Pengeluaranstokheader_id',
            'PengeluaranStokDetail.nobukti',
            'stok.namastok as stok',
            "stok.kelompok_id as kelompok_id",
            "satuan.satuan as satuan",
            'stok.statusreuse as statusreuse',
            'PengeluaranStokDetail.jumlahhariaki as jlhhari',
            'PengeluaranStokDetail.stok_id',
            'PengeluaranStokDetail.qty',
            'PengeluaranStokDetail.harga',
            'parameter.text as statusservicerutin',
            'PengeluaranStokDetail.persentasediscount',
            'PengeluaranStokDetail.nominaldiscount',
            'PengeluaranStokDetail.total',
            'PengeluaranStokDetail.keterangan',
            'PengeluaranStokDetail.statusoli',
            'PengeluaranStokDetail.vulkanisirke',
            "PengeluaranStokDetail.statusban",
            'PengeluaranStokDetail.modifiedby',
        )
            ->leftJoin('stok', 'PengeluaranStokDetail.stok_id', 'stok.id')
            ->leftJoin("satuan", "stok.satuan_id", "satuan.id")
            ->leftJoin('parameter', 'PengeluaranStokDetail.statusservicerutin', 'parameter.id');

        $data = $query->where("Pengeluaranstokheader_id", $id)->get();

        return $data;
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'stok') {
                                $query = $query->where('stok.namastok', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'qty' || $filters['field'] == 'harga' || $filters['field'] == 'persentasediscount' || $filters['field'] == 'nominaldiscount' || $filters['field'] == 'total') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'stok') {
                                $query = $query->orWhere('stok.namastok', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'qty' || $filters['field'] == 'harga' || $filters['field'] == 'persentasediscount' || $filters['field'] == 'nominaldiscount' || $filters['field'] == 'total') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });
                    break;
                default:

                    break;
            }
        }
    }
    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'stok') {
            return $query->orderBy('stok.namastok', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function processStore(PengeluaranStokHeader $pengeluaranStokHeader, array $data): PengeluaranStokDetail
    {
        $total = $data['qty'] * $data['harga'];
        $nominaldiscount = $total * ($data['persentasediscount'] / 100);
        $total -= $nominaldiscount;
        $pengeluaranStokHeader = PengeluaranStokHeader::where('id', $data['pengeluaranstokheader_id'])->first();

        $stok = Stok::where('id', $data['stok_id'])->first();
        $stokreuse = Parameter::where('grp', 'STATUS REUSE')->where('subgrp', 'STATUS REUSE')->where('text', 'REUSE')->first();

        $reuse = false;
        if ($stok->statusreuse == $stokreuse->id) {
            $reuse = true;
        }

        $datahitungstok = PengeluaranStok::select('statushitungstok as statushitungstok_id')->where('format', '=', $pengeluaranStokHeader->statusformat)->first();
        $statushitungstok = Parameter::where('grp', 'STATUS HITUNG STOK')->where('text', 'HITUNG STOK')->first();

        $spk = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();
        $kor = Parameter::where('grp', 'KOR MINUS STOK')->where('subgrp', 'KOR MINUS STOK')->first();
        $rtr = Parameter::where('grp', 'RETUR STOK')->where('subgrp', 'RETUR STOK')->first();
        $pja = Parameter::where('grp', 'PENJUALAN STOK AFKIR')->where('subgrp', 'PENJUALAN STOK AFKIR')->first();
        $gst = Parameter::where('grp', 'GST STOK')->where('subgrp', 'GST STOK')->first();
        $korv = DB::table('pengeluaranstok')->where('kodepengeluaran', 'KORV')->first();
        $afkir = DB::table('pengeluaranstok')->where('kodepengeluaran', 'AFKIR')->first();

        $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
        $gudangsementara = Parameter::where('grp', 'GUDANG SEMENTARA')->where('subgrp', 'GUDANG SEMENTARA')->first();
        $gudangpihak3 = Parameter::where('grp', 'GUDANG PIHAK3')->where('subgrp', 'GUDANG PIHAK3')->first();

        if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {
            if ($pengeluaranStokHeader->pengeluaranstok_id == $kor->text) {
                $persediaan = $this->persediaan($pengeluaranStokHeader->gudang_id, $pengeluaranStokHeader->trado_id, $pengeluaranStokHeader->gandengan_id);
                $dari = $this->persediaanDari($data['stok_id'], $persediaan['column'] . '_id', $persediaan['value'], $data['qty']);
            } else if ($pengeluaranStokHeader->pengeluaranstok_id == $pja->text) {
                $dari = $this->persediaanDari($data['stok_id'], 'gudang_id', $gudangsementara->text, $data['qty']);
            } else if ($pengeluaranStokHeader->pengeluaranstok_id == $afkir->id) {
                $dari = $this->persediaanDari($data['stok_id'], 'gudang_id', $gudangsementara->text, $data['qty']);
            } else {
                $dari = $this->persediaanDari($data['stok_id'], 'gudang_id', $gudangkantor->text, $data['qty']);
            }
            if (!$dari) {
                throw ValidationException::withMessages(['qty' => $stok->namastok.' - qty tidak cukup ']);
            }
            // if (($pengeluaranStokHeader->pengeluaranstok_id != $spk->text) || ($pengeluaranStokHeader->pengeluaranstok_id != $gst->text)) {
            //     if (!$reuse) {
            //         throw new \Exception("bukan stok reuse");                

            //     }
            // }
            if (($pengeluaranStokHeader->pengeluaranstok_id == $spk->text) || $pengeluaranStokHeader->pengeluaranstok_id == $gst->text) {
                // if (!$reuse) {
                //     throw new \Exception("bukan stok reuse");                
                // }
                $persediaan = $this->persediaan($pengeluaranStokHeader->gudang_id, $pengeluaranStokHeader->trado_id, $pengeluaranStokHeader->gandengan_id);
                $ke = $this->persediaanKe($data['stok_id'], $persediaan['column'] . '_id', $persediaan['value'], $data['qty']);
            }
        }

        if ($pengeluaranStokHeader->pengeluaranstok_id == $spk->text) {

            $kelompok = DB::table('kelompok')->where('kodekelompok', "AKI")->first();
            if($stok->kelompok_id == $kelompok->id){
                if($pengeluaranStokHeader->trado_id){
                    $trado = (new Trado)->updateTglGantiAki($pengeluaranStokHeader->trado_id,$pengeluaranStokHeader->tglbukti);
                }
            }

            $tempstatusservice = '##tempstatusservice' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

            Schema::create($tempstatusservice, function ($table) {
                $table->integer('idstatus',)->nullable();
            });

            $querystatusservice = DB::table('parameter')->from(
                DB::raw("parameter a with (readuncommitted)")
            )
                ->select(
                    'a.id as idstatus',
                )
                ->where('a.grp', '=', 'STATUS SERVICE RUTIN')
                ->where('a.subgrp', '=', 'STATUS SERVICE RUTIN');


            DB::table($tempstatusservice)->insertUsing([
                'idstatus',
            ], $querystatusservice);


            $stokid = $data['stok_id'];
            $querystokstatusservis = DB::table('stok')->from(
                DB::raw("stok a with (readuncommitted)")
            )
                ->select(
                    'a.statusservicerutin',
                )
                ->Join(DB::raw($tempstatusservice . " as b"), 'a.statusservicerutin', '=', 'b.idstatus')
                ->where('a.id', '=', $stokid)
                ->first();

            if (isset($querystokstatusservis)) {
                $idstatusservicerutin = $querystokstatusservis->statusservicerutin;
            } else {
                $idstatusservicerutin = 0;
            }
        } else {
            $idstatusservicerutin = 0;
        }

        if ($korv->id == $pengeluaranStokHeader->pengeluaranstok_id) {
            $vulkan = $this->vulkanStokMinus($data['stok_id'], $data['vulkanisirke']);
            if (!$vulkan) {
                throw ValidationException::withMessages(['vulkanisirke' => 'vulkannisir tidak cukup']);
            }
            $stok = (new Stok())->find($data['stok_id']);
            $stok->statusban = $data['statusban'];
            $stok->save();
            
            
        }
        // dd($data['statusban']);




        $pengeluaranStokDetail = new PengeluaranStokDetail();
        $pengeluaranStokDetail->pengeluaranstokheader_id = $data['pengeluaranstokheader_id'];
        $pengeluaranStokDetail->nobukti = $data['nobukti'];
        $pengeluaranStokDetail->stok_id = $data['stok_id'];
        $pengeluaranStokDetail->qty = $data['qty'];
        $pengeluaranStokDetail->jumlahhariaki = $data['jlhhari'];
        $pengeluaranStokDetail->harga = $data['harga'];
        $pengeluaranStokDetail->nominaldiscount = $nominaldiscount;
        $pengeluaranStokDetail->total = $total;
        $pengeluaranStokDetail->persentasediscount = $data['persentasediscount'];
        $pengeluaranStokDetail->statusban = $data['statusban'];
        $pengeluaranStokDetail->vulkanisirke = $data['vulkanisirke'];
        $pengeluaranStokDetail->statusoli = $data['statusoli'];
        $pengeluaranStokDetail->statusban = $data['statusban'];
        $pengeluaranStokDetail->keterangan = $data['detail_keterangan'];
        $pengeluaranStokDetail->statusservicerutin = $idstatusservicerutin;

        $pengeluaranStokDetail->modifiedby = auth('api')->user()->name;
        $pengeluaranStokDetail->info = html_entity_decode(request()->info);

        if (!$pengeluaranStokDetail->save()) {
            throw new \Exception("Error storing pengeluaran Stok Detail.");
        }
        // dd($pengeluaranStokDetail);

        return $pengeluaranStokDetail;
    }

    public function processUpdate(PengeluaranStokDetail $pengeluaranStokDetail,PengeluaranStokHeader $pengeluaranStokHeader, array $data): PengeluaranStokDetail
    {
        $total = $data['qty'] * $data['harga'];
        $nominaldiscount = $total * ($data['persentasediscount'] / 100);
        $total -= $nominaldiscount;
        $pengeluaranStokHeader = PengeluaranStokHeader::where('id', $data['pengeluaranstokheader_id'])->first();

        $stok = Stok::where('id', $data['stok_id'])->first();
        $stokreuse = Parameter::where('grp', 'STATUS REUSE')->where('subgrp', 'STATUS REUSE')->where('text', 'REUSE')->first();

        $reuse = false;
        if ($stok->statusreuse == $stokreuse->id) {
            $reuse = true;
        }

        $datahitungstok = PengeluaranStok::select('statushitungstok as statushitungstok_id')->where('format', '=', $pengeluaranStokHeader->statusformat)->first();
        $statushitungstok = Parameter::where('grp', 'STATUS HITUNG STOK')->where('text', 'HITUNG STOK')->first();

        $spk = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();
        $kor = Parameter::where('grp', 'KOR MINUS STOK')->where('subgrp', 'KOR MINUS STOK')->first();
        $rtr = Parameter::where('grp', 'RETUR STOK')->where('subgrp', 'RETUR STOK')->first();
        $pja = Parameter::where('grp', 'PENJUALAN STOK AFKIR')->where('subgrp', 'PENJUALAN STOK AFKIR')->first();
        $gst = Parameter::where('grp', 'GST STOK')->where('subgrp', 'GST STOK')->first();
        $korv = DB::table('pengeluaranstok')->where('kodepengeluaran', 'KORV')->first();
        $afkir = DB::table('pengeluaranstok')->where('kodepengeluaran', 'AFKIR')->first();

        $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
        $gudangsementara = Parameter::where('grp', 'GUDANG SEMENTARA')->where('subgrp', 'GUDANG SEMENTARA')->first();
        $gudangpihak3 = Parameter::where('grp', 'GUDANG PIHAK3')->where('subgrp', 'GUDANG PIHAK3')->first();

        if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {
            if ($pengeluaranStokHeader->pengeluaranstok_id == $kor->text) {
                $persediaan = $this->persediaan($pengeluaranStokHeader->gudang_id, $pengeluaranStokHeader->trado_id, $pengeluaranStokHeader->gandengan_id);
                $dari = $this->persediaanDari($data['stok_id'], $persediaan['column'] . '_id', $persediaan['value'], $data['qty']);
            } else if ($pengeluaranStokHeader->pengeluaranstok_id == $pja->text) {
                $dari = $this->persediaanDari($data['stok_id'], 'gudang_id', $gudangsementara->text, $data['qty']);
            } else if ($pengeluaranStokHeader->pengeluaranstok_id == $afkir->id) {
                $dari = $this->persediaanDari($data['stok_id'], 'gudang_id', $gudangsementara->text, $data['qty']);
            } else {
                $dari = $this->persediaanDari($data['stok_id'], 'gudang_id', $gudangkantor->text, $data['qty']);
            }
            if (!$dari) {
                throw ValidationException::withMessages(['qty' => $stok->namastok.' - qty tidak cukup ']);
            }
            // if (($pengeluaranStokHeader->pengeluaranstok_id != $spk->text) || ($pengeluaranStokHeader->pengeluaranstok_id != $gst->text)) {
            //     if (!$reuse) {
            //         throw new \Exception("bukan stok reuse");                

            //     }
            // }
            if (($pengeluaranStokHeader->pengeluaranstok_id == $spk->text) || $pengeluaranStokHeader->pengeluaranstok_id == $gst->text) {
                // if (!$reuse) {
                //     throw new \Exception("bukan stok reuse");                
                // }
                $persediaan = $this->persediaan($pengeluaranStokHeader->gudang_id, $pengeluaranStokHeader->trado_id, $pengeluaranStokHeader->gandengan_id);
                $ke = $this->persediaanKe($data['stok_id'], $persediaan['column'] . '_id', $persediaan['value'], $data['qty']);
            }
        }

        if ($pengeluaranStokHeader->pengeluaranstok_id == $spk->text) {

            $kelompok = DB::table('kelompok')->where('kodekelompok', "AKI")->first();
            if($stok->kelompok_id == $kelompok->id){
                if($pengeluaranStokHeader->trado_id){
                    $trado = (new Trado)->updateTglGantiAki($pengeluaranStokHeader->trado_id,$pengeluaranStokHeader->tglbukti);
                }
            }

            $tempstatusservice = '##tempstatusservice' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

            Schema::create($tempstatusservice, function ($table) {
                $table->integer('idstatus',)->nullable();
            });

            $querystatusservice = DB::table('parameter')->from(
                DB::raw("parameter a with (readuncommitted)")
            )
                ->select(
                    'a.id as idstatus',
                )
                ->where('a.grp', '=', 'STATUS SERVICE RUTIN')
                ->where('a.subgrp', '=', 'STATUS SERVICE RUTIN');


            DB::table($tempstatusservice)->insertUsing([
                'idstatus',
            ], $querystatusservice);


            $stokid = $data['stok_id'];
            $querystokstatusservis = DB::table('stok')->from(
                DB::raw("stok a with (readuncommitted)")
            )
                ->select(
                    'a.statusservicerutin',
                )
                ->Join(DB::raw($tempstatusservice . " as b"), 'a.statusservicerutin', '=', 'b.idstatus')
                ->where('a.id', '=', $stokid)
                ->first();

            if (isset($querystokstatusservis)) {
                $idstatusservicerutin = $querystokstatusservis->statusservicerutin;
            } else {
                $idstatusservicerutin = 0;
            }
        } else {
            $idstatusservicerutin = 0;
        }

        if ($korv->id == $pengeluaranStokHeader->pengeluaranstok_id) {
            $vulkan = $this->vulkanStokMinus($data['stok_id'], $data['vulkanisirke']);
            if (!$vulkan) {
                throw ValidationException::withMessages(['vulkanisirke' => 'vulkannisir tidak cukup']);
            }
            $stok = (new Stok())->find($data['stok_id']);
            $stok->statusban = $data['statusban'];
            $stok->save();
            
            
        }
        // dd($data['statusban']);




        // $pengeluaranStokDetail = new PengeluaranStokDetail();
        $pengeluaranStokDetail->pengeluaranstokheader_id = $data['pengeluaranstokheader_id'];
        $pengeluaranStokDetail->nobukti = $data['nobukti'];
        $pengeluaranStokDetail->stok_id = $data['stok_id'];
        $pengeluaranStokDetail->qty = $data['qty'];
        $pengeluaranStokDetail->jumlahhariaki = $data['jlhhari'];
        $pengeluaranStokDetail->harga = $data['harga'];
        $pengeluaranStokDetail->nominaldiscount = $nominaldiscount;
        $pengeluaranStokDetail->total = $total;
        $pengeluaranStokDetail->persentasediscount = $data['persentasediscount'];
        $pengeluaranStokDetail->statusban = $data['statusban'];
        $pengeluaranStokDetail->vulkanisirke = $data['vulkanisirke'];
        $pengeluaranStokDetail->statusoli = $data['statusoli'];
        $pengeluaranStokDetail->statusban = $data['statusban'];
        $pengeluaranStokDetail->keterangan = $data['detail_keterangan'];
        $pengeluaranStokDetail->statusservicerutin = $idstatusservicerutin;

        $pengeluaranStokDetail->modifiedby = auth('api')->user()->name;
        $pengeluaranStokDetail->info = html_entity_decode(request()->info);

        if (!$pengeluaranStokDetail->save()) {
            throw new \Exception("Error storing pengeluaran Stok Detail.");
        }
        // dd($pengeluaranStokDetail);

        return $pengeluaranStokDetail;
    }

    public function persediaan($gudang, $trado, $gandengan)
    {
        $kolom = null;
        $value = 0;
        if (!empty($gudang)) {
            $kolom = "gudang";
            $value = $gudang;
        } elseif (!empty($trado)) {
            $kolom = "trado";
            $value = $trado;
        } elseif (!empty($gandengan)) {
            $kolom = "gandengan";
            $value = $gandengan;
        }
        return [
            "column" => $kolom,
            "value" => $value
        ];
    }

    public function persediaanDari($stokId, $persediaan, $persediaanId, $qty)
    {
        //check kartu stok
        $stok = db::table('kartustok')->from(db::raw("kartustok a with (readuncommitted)"))
            ->select(
                db::raw("sum(isnull(qtymasuk,0)-isnull(qtykeluar,0)) as qty")
            )
            ->where("stok_id", $stokId)->where("$persediaan", $persediaanId)->first()
            ->qty ?? 0;

        if ($stok == 0) {
            return false;
        }
        if ($qty > $stok) {
            return false;
        }
        // $stokpersediaangudang = $this->checkTempat($stokId,$persediaan,$persediaanId); //stok persediaan 
        // if (!$stokpersediaangudang) {
        //     return false;
        // }
        // $stokpersediaan = StokPersediaan::lockForUpdate()->find($stokpersediaangudang->id);
        // if ($qty > $stokpersediaan->qty){ 
        //     return false;
        // }
        // $result = $stokpersediaan->qty - $qty;
        // $stokpersediaan->update(['qty' => $result]);
        return true;
    }
    public function persediaanKe($stokId, $persediaan, $persediaanId, $qty)
    {
        // $stokpersediaangudang = $this->checkTempat($stokId, $persediaan, $persediaanId); //stok persediaan 
        // if (!$stokpersediaangudang) {
        //     $stokpersediaangudang = StokPersediaan::create(["stok_id" => $stokId, $persediaan => $persediaanId]);
        // }
        // $stokpersediaangudang->qty += $qty;
        // $stokpersediaangudang->save();
        return true;
    }
    public function checkTempat($stokId, $persediaan, $persediaanId, $qty)
    {
        // $result = StokPersediaan::lockForUpdate()->where("stok_id", $stokId)->where("$persediaan", $persediaanId)->first();
        $stok = db::table('kartustok')->from(db::raw("kartustok a with (readuncommitted)"))
            ->select(
                db::raw("sum(isnull(qtymasuk,0)-isnull(qtykeluar,0)) as qty")
            )
            ->where("stok_id", $stokId)->where("$persediaan", $persediaanId)->first()
            ->qty ?? 0;

        $qty = $qty + $stok;
        if ($qty <= 0) {
            return false;
        }
        return true;
    }

    public function vulkanStokMinus($stok_id, $vulkan)
    {
        $stok = Stok::find($stok_id);
        if (!$stok) {
            return false;
        }
        $getvulkanisir = (new Stok)->getvulkanisir($stok_id);

        $total = $getvulkanisir['totalvulkan'] - $vulkan;
        if ($total < 0) {
            return false;
        }
        $stok->totalvulkanisir = $total;
        $stok->save();
        return $stok;
    }

    public function vulkanStokPlus($stok_id, $vulkan)
    {
        $stok = Stok::find($stok_id);
        if (!$stok) {
            return false;
        }
        $total = $stok->totalvulkanisir + $vulkan;
        $stok->totalvulkanisir = $total;
        $stok->save();
        return true;
    }

    public function returnVulkanisir($id)
    {
        $pengeluaranStokHeader = PengeluaranStokHeader::findOrFail($id);
        $pengeluaranStokDetail = PengeluaranStokDetail::where('pengeluaranstokheader_id', $id)->get();
        $korv = DB::table('pengeluaranstok')->where('kodepengeluaran', 'KORV')->first();

        foreach ($pengeluaranStokDetail as $item) {
            if ($pengeluaranStokHeader->pengeluaranstok_id == $korv->id) {
                $dari = $this->vulkanStokPlus($item->stok_id, $item->vulkanisirke);
            }
        }

        // $stok = Stok::find($pengeluaranStokDetail[0]->stok_id);
        // dd($stok->totalvulkanisir);
    }


    public function resetQtyPenerimaan($id)
    {
        $pengeluaranStokHeader = PengeluaranStokHeader::findOrFail($id);

        // $pengeluaranStokHeader = PengeluaranStokHeader::find($id);
        $spk = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();
        $kor = Parameter::where('grp', 'KOR MINUS STOK')->where('subgrp', 'KOR MINUS STOK')->first();
        $rtr = Parameter::where('grp', 'RETUR STOK')->where('subgrp', 'RETUR STOK')->first();
        $pja = Parameter::where('grp', 'PENJUALAN STOK AFKIR')->where('subgrp', 'PENJUALAN STOK AFKIR')->first();
        $gst = Parameter::where('grp', 'GST STOK')->where('subgrp', 'GST STOK')->first();
        $korv = DB::table('pengeluaranstok')->where('kodepengeluaran', 'KORV')->first();
        $afkir = DB::table('pengeluaranstok')->where('kodepengeluaran', 'AFKIR')->first();

        $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
        $gudangsementara = Parameter::where('grp', 'GUDANG SEMENTARA')->where('subgrp', 'GUDANG SEMENTARA')->first();
        $gudangpihak3 = Parameter::where('grp', 'GUDANG PIHAK3')->where('subgrp', 'GUDANG PIHAK3')->first();
        $pengeluaranStokDetail = PengeluaranStokDetail::where('pengeluaranstokheader_id', $id)->get();
        foreach ($pengeluaranStokDetail as $detail) {
            /*Update  di stok persediaan*/
            $dari = true;
            if ($pengeluaranStokHeader->pengeluaranstok_id == $kor->text) {
                $persediaan = $this->persediaan($pengeluaranStokHeader->gudang_id, $pengeluaranStokHeader->trado_id, $pengeluaranStokHeader->gandengan_id);
                $dari = $this->persediaanDariReturn($detail->stok_id, $persediaan['column'] . '_id', $persediaan['value'], $detail->qty);
            } else if ($pengeluaranStokHeader->pengeluaranstok_id == $pja->text) {
                $dari = $this->persediaanDariReturn($detail->stok_id, 'gudang_id', $gudangsementara->text, $detail->qty);
            } else if ($pengeluaranStokHeader->pengeluaranstok_id == $afkir->id) {
                $dari = $this->persediaanDariReturn($detail->stok_id, 'gudang_id', $gudangsementara->text, $detail->qty);
            } else {
                $persediaan = $this->persediaan($pengeluaranStokHeader->gudang_id, $pengeluaranStokHeader->trado_id, $pengeluaranStokHeader->gandengan_id);
                $dari = $this->persediaanDariReturn($detail->stok_id, 'gudang_id', $gudangkantor->text, $detail->qty);
            }


            if (!$dari) {
                throw ValidationException::withMessages(['qty' => 'qty tidak cukup return']);
            }

            if (($pengeluaranStokHeader->pengeluaranstok_id == $spk->text) || ($pengeluaranStokHeader->pengeluaranstok_id == $gst->text)) {
                $persediaan = $this->persediaan($pengeluaranStokHeader->gudang_id, $pengeluaranStokHeader->trado_id, $pengeluaranStokHeader->gandengan_id);
                $dari = $this->persediaanKeReturn($detail->stok_id, $persediaan['column'] . '_id', $persediaan['value'], $detail->qty);
            }

            if ($pengeluaranStokHeader->pengeluaranstok_id == $korv->id) {
                $dari = $this->vulkanStokPlus($detail->stok_id, $detail->vulkanisirke);
                dd($dari);
            }
        }

        $pengeluaranStokDetailFifo = PengeluaranStokDetailFifo::where('nobukti', $pengeluaranStokHeader->nobukti)->get();
        if (isset($pengeluaranStokDetailFifo)) {
            foreach ($pengeluaranStokDetailFifo as $fifo) {
                $penerimaanStok = PenerimaanStokDetail::where('nobukti', $fifo->penerimaanstokheader_nobukti)->where('stok_id', $fifo->stok_id)->first();
                if (isset($penerimaanStok)) {

                    $qtyterimarekap = DB::table("pengeluaranstokdetailfifo")->from(db::raw("pengeluaranstokdetailfifo a with (readuncommitted)"))
                        ->select(
                            db::raw("sum(a.qty) as qty")
                        )
                        ->where("penerimaanstokheader_nobukti",  $fifo->penerimaanstokheader_nobukti)
                        ->where("stok_id",  $fifo->stok_id)
                        ->first()->qty ?? 0;

                    $penerimaanStok->qtykeluar = $qtyterimarekap;
                    $penerimaanStok->save();
                }
            }
        }
    }


    public function persediaanDariReturn($stokId, $persediaan, $persediaanId, $qty)
    {
        $stokpersediaangudang = $this->checkTempat($stokId, $persediaan, $persediaanId, $qty); //stok persediaan 
        return $stokpersediaangudang;
        // dd($stokpersediaangudang);
        // $stokpersediaan = StokPersediaan::lockForUpdate()->find($stokpersediaangudang->id);
        // $result = $stokpersediaan->qty + $qty;
        // $stokpersediaan->qty = $result;
        // $stokpersediaan->save();
        // return $stokpersediaan;
    }
    public function persediaanKeReturn($stokId, $persediaan, $persediaanId, $qty)
    {

        //check kartu stok
        $stok = db::table('kartustok')->from(db::raw("kartustok a with (readuncommitted)"))
            ->select(
                db::raw("sum(isnull(qtymasuk,0)-isnull(qtykeluar,0)) as qty")
            )
            ->where("stok_id", $stokId)->where("$persediaan", $persediaanId)->first()
            ->qty ?? 0;

        if ($stok == 0) {
            return false;
        }
        if ($qty > $stok) {
            return false;
        }

        // $stokpersediaangudang = $this->checkTempat($stokId, $persediaan, $persediaanId); //stok persediaan 
        // if (!$stokpersediaangudang) {
        //     return false;
        // }
        // $stokpersediaan = StokPersediaan::lockForUpdate()->find($stokpersediaangudang->id);
        // if ($qty > $stokpersediaan->qty) { 
        //     return false;
        // }
        // $stokpersediaan->qty -= $qty;
        // $stokpersediaan->save();
        return true;
    }
}
