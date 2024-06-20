<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PenerimaanStokDetail extends MyModel
{
    use HasFactory;

    protected $table = "PenerimaanStokDetail";

    protected $casts = [
        "created_at" => "date:d-m-Y H:i:s",
        "updated_at" => "date:d-m-Y H:i:s"
    ];

    protected $guarded = [
        "id",
        "created_at",
        "updated_at",
    ];
    public function get()
    {
        $this->setRequestParameters();

        $from = request()->from ?? '';
        $nobukti = request()->nobukti ?? '';

        if ($nobukti == '') {
            $nobukti = request()->penerimaanstokheader_nobukti ?? '';
        }
        $query = DB::table("PenerimaanStokHeader");
        // $header = $query->where("id", request()->penerimaanstokheader_id)->first();
        $header = $query->where("nobukti", $nobukti)->first();


        $tempvulkan = '##tempvulkan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempvulkan, function ($table) {
            $table->integer('stok_id')->nullable();
            $table->integer('vulkan')->nullable();
        });
        if ($header) {
            DB::table($tempvulkan)->insertUsing([
                'stok_id',
                'vulkan',
            ], (new Stok())->getVulkan($header->tglbukti));
        }


        $temtabelpenerimaandetail = 'temppenerimaandetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;
        Schema::create($temtabelpenerimaandetail, function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->unsignedBigInteger('penerimaanstokheader_id')->nullable();
            $table->unsignedBigInteger('stok_id')->nullable();
            $table->double('qty', 15, 2)->nullable();
            $table->double('harga', 15, 2)->nullable();
            $table->double('persentasediscount', 15, 2)->nullable();
            $table->double('nominaldiscount', 15, 2)->nullable();
            $table->double('total', 15, 2)->nullable();
            $table->longtext('keterangan')->nullable();
            $table->unsignedBigInteger('vulkanisirke')->nullable();
            $table->string('penerimaanstok_nobukti', 50)->nullable();
            $table->double('qtykeluar', 15, 2)->nullable();
            $table->longtext('info')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->datetime('created_at')->nullable();
            $table->datetime('updated_at')->nullable();
            $table->double('qtyterpakai', 15, 2)->nullable();
            $table->string('pengeluaranstokproses_nobukti', 50)->nullable();
        });

        $querypenerimaandetail = db::table("penerimaanstokdetail")->from(db::raw("penerimaanstokdetail a  with (readuncommitted)"))
            ->select(
                'a.id',
                'a.nobukti',
                'a.penerimaanstokheader_id',
                'a.stok_id',
                'a.qty',
                'a.harga',
                'a.persentasediscount',
                'a.nominaldiscount',
                'a.total',
                'a.keterangan',
                'a.vulkanisirke',
                'a.penerimaanstok_nobukti',
                'a.qtykeluar',
                'a.info',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
                'a.qtyterpakai',
                'a.pengeluaranstokproses_nobukti',
            );
            if ($nobukti != "") {
                $querypenerimaandetail->where('a.nobukti', $nobukti);
            }

        DB::table($temtabelpenerimaandetail)->insertUsing([
            'id',
            'nobukti',
            'penerimaanstokheader_id',
            'stok_id',
            'qty',
            'harga',
            'persentasediscount',
            'nominaldiscount',
            'total',
            'keterangan',
            'vulkanisirke',
            'penerimaanstok_nobukti',
            'qtykeluar',
            'info',
            'modifiedby',
            'created_at',
            'updated_at',
            'qtyterpakai',
            'pengeluaranstokproses_nobukti',
        ], $querypenerimaandetail);

        $querypenerimaandetail = db::table("kartustoklama")->from(db::raw("kartustoklama a  with (readuncommitted)"))
            ->select(
                'a.id',
                'a.nobukti',
                db::raw("0 as penerimaanstokheader_id"),
                'a.stok_id',
                'a.qtymasuk as qty',
                db::raw("round((a.nilaimasuk/a.qtymasuk),2) as harga"),
                db::raw("0 as persentasediscount"),
                db::raw("0 as nominaldiscount"),
                'a.nilaimasuk as total',
                db::raw("'' as keterangan"),
                db::raw("'' as vulkanisirke"),
                db::raw("'' as penerimaanstok_nobukti"),
                db::raw("0 as qtykeluar"),
                db::raw("'' as info"),
                db::raw("a.modifiedby"),
                db::raw("a.created_at"),
                db::raw("a.updated_at"),
                db::raw("0 as qtyterpakai"),
                db::raw("'' as pengeluaranstokproses_nobukti"),
            )
            ->where('a.nobukti', $nobukti)
            ->whereraw("isnull(a.qtymasuk,0)<>0");

        DB::table($temtabelpenerimaandetail)->insertUsing([
            'id',
            'nobukti',
            'penerimaanstokheader_id',
            'stok_id',
            'qty',
            'harga',
            'persentasediscount',
            'nominaldiscount',
            'total',
            'keterangan',
            'vulkanisirke',
            'penerimaanstok_nobukti',
            'qtykeluar',
            'info',
            'modifiedby',
            'created_at',
            'updated_at',
            'qtyterpakai',
            'pengeluaranstokproses_nobukti',
        ], $querypenerimaandetail);


        // $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));
        $query = DB::table($temtabelpenerimaandetail)->from(DB::raw($temtabelpenerimaandetail . " penerimaanstokdetail "));
        $spbp = DB::table('penerimaanstok')->where('kodepenerimaan', 'SPBP')->first();
        $rtr = DB::table('pengeluaranstok')->where('kodepengeluaran', 'RTR')->first();
        $spbs = Parameter::where('grp', 'REUSE STOK')->where('subgrp', 'REUSE STOK')->first();

        if (isset(request()->id)) {
            $query->where("$this->table.id", request()->id);
        }
  
        // if (isset(request()->penerimaanstokheader_id)) {
        //     $query->where("$this->table.penerimaanstokheader_id", request()->penerimaanstokheader_id);
        // }
        if (isset(request()->nobukti)) {
            $query->where("$this->table.nobukti", request()->nobukti);
        }

        $temtabelpenerimaan = 'temppenerimaan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;

        Schema::create($temtabelpenerimaan, function (Blueprint $table) {
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
        });
        if (isset(request()->forReport) && request()->forReport) {

            $idheader = request()->penerimaanstokheader_id ?? 0;

            $queryheader = db::table("penerimaanstokheader")->from(db::raw("penerimaanstokheader a with (readuncommitted)"))
                ->select(
                    'a.nobukti',
                    'a.tglbukti'
                )
                ->where('a.nobukti', $nobukti);

            DB::table($temtabelpenerimaan)->insertUsing([
                'nobukti',
                'tglbukti',
            ], $queryheader);

            $queryheader = db::table("kartustoklama")->from(db::raw("kartustoklama a with (readuncommitted)"))
                ->select(
                    'a.nobukti',
                    db::raw("max(a.tglbukti) as tglbukti")
                )
                ->groupBy('a.nobukti')
                ->where('a.nobukti', $nobukti);

            DB::table($temtabelpenerimaan)->insertUsing([
                'nobukti',
                'tglbukti',
            ], $queryheader);

            $querytgl = db::table($temtabelpenerimaan)->from(db::raw($temtabelpenerimaan . " a "))
                ->select(
                    db::raw("format(a.tglbukti,'yyyy/MM/dd') as tglbukti"),
                )
                // ->where('a.id', $idheader)
                ->first()->tglbukti ?? '1900/1/1';


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

            $hariaki = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                ->select(
                    'a.text as id'
                )
                ->where('a.grp', 'HARIAKI')
                ->where('a.subgrp', 'HARIAKI')
                ->where('a.text', 'TANGGAL')
                ->first();
            if (isset($hariaki)) {
                $bytgl = 1;
            } else {
                $bytgl = 0;
            }
            //update total vulkanisir
            $reuse = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                ->select('a.id')
                ->where('grp', 'STATUS REUSE')
                ->where('subgrp', 'STATUS REUSE')
                ->where('text', 'REUSE')
                ->first()->id ?? 0;


            $tempvulkan = '##tempvulkan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempvulkan, function ($table) {
                $table->integer('stok_id')->nullable();
                $table->integer('vulkan')->nullable();
            });

            $tempvulkanplus = '##tempvulkanplus' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempvulkanplus, function ($table) {
                $table->integer('stok_id')->nullable();
                $table->integer('vulkan')->nullable();
            });


            $tempvulkanminus = '##tempvulkanminus' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempvulkanminus, function ($table) {
                $table->integer('stok_id')->nullable();
                $table->integer('vulkan')->nullable();
            });


            $queryvulkanplus = db::table("stok")->from(db::raw("stok a with (readuncommitted)"))
                ->select(
                    db::raw("a.id as stok_id"),
                    db::raw("sum(b.vulkanisirke) as vulkan"),
                )
                ->join(db::raw($temtabelpenerimaandetail . " b "), 'a.id', 'b.stok_id')
                ->join(db::raw($temtabelpenerimaan . " c "), 'b.nobukti', 'c.nobukti')
                ->where('a.statusreuse', $reuse)
                ->whereraw("c.tglbukti<='" . $querytgl . "'")
                ->groupby('a.id');

            DB::table($tempvulkanplus)->insertUsing([
                'stok_id',
                'vulkan',
            ],  $queryvulkanplus);

            $queryvulkanminus = db::table("stok")->from(db::raw("stok a with (readuncommitted)"))
                ->select(
                    db::raw("a.id as stok_id"),
                    db::raw("sum(b.vulkanisirke) as vulkan"),
                )
                ->join(db::raw("pengeluaranstokdetail b with (readuncommitted)"), 'a.id', 'b.stok_id')
                ->join(db::raw("pengeluaranstokheader c with (readuncommitted)"), 'b.nobukti', 'c.nobukti')
                ->where('a.statusreuse', $reuse)
                ->whereraw("c.tglbukti<='" . $querytgl . "'")
                ->groupby('a.id');

            DB::table($tempvulkanminus)->insertUsing([
                'stok_id',
                'vulkan',
            ],  $queryvulkanminus);


            $queryvulkan = db::table("stok")->from(db::raw("stok a with (readuncommitted)"))
                ->select(
                    db::raw("a.id  as stok_id"),
                    db::raw("((isnull(a.vulkanisirawal,0)+isnull(b.vulkan,0))-isnull(c.vulkan,0)) as vulkan"),
                )
                ->leftjoin(db::raw($tempvulkanplus . " b "), 'a.id', 'b.stok_id')
                ->leftjoin(db::raw($tempvulkanminus . " c "), 'a.id', 'c.stok_id')
                ->where('a.statusreuse', $reuse);

            DB::table($tempvulkan)->insertUsing([
                'stok_id',
                'vulkan',
            ],  $queryvulkan);





            // end update vulkanisir


            $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp', 'JUDULAN LAPORAN')
                ->where('subgrp', 'JUDULAN LAPORAN')
                ->first();
            $query->select(
                "$this->table.penerimaanstokheader_id",
                "$this->table.nobukti",
                "$this->table.stok_id",
                db::raw("trim(stok.namastok)+
                (case when isnull(c.stok_id,0)<>0 then ' ( '+
                    (case when " . $bytgl . "=1 then 'TGL PAKAI '+format(c.tglawal,'dd-MM-yyyy')+',' else '' end)+
                    'UMUR AKI : '+format(isnull(c.jumlahhari,0),'#,#0')+' HARI )' 
                      when isnull(stok.kelompok_id,0)=1 then ' ( VULKANISIR KE-'+format(isnull(d.vulkan,0),'#,#0')+', STATUS BAN :'+isnull(parameter.text,'') +' )' 
                else '' end)
                as stok"),
                "$this->table.qty",
                "$this->table.harga",
                "$this->table.persentasediscount",
                "$this->table.penerimaanstok_nobukti",
                "$this->table.nominaldiscount",
                "$this->table.total",
                "$this->table.keterangan",
                "$this->table.vulkanisirke",
                "$this->table.modifiedby",
                DB::raw("'Laporan Purchase Order (PO)' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
                ->leftJoin(db::raw($temtabelpenerimaan . " penerimaanstokheader"), "$this->table.nobukti", "penerimaanstokheader.nobukti")
                ->leftJoin("stok", "$this->table.stok_id", "stok.id")
                ->leftJoin("parameter", "stok.statusban", "parameter.id")
                ->leftJoin(db::raw($tempumuraki . " c"), "$this->table.stok_id", "c.stok_id")
                ->leftJoin(db::raw($tempvulkan . " d"), "$this->table.stok_id", "d.stok_id");

            $totalRows =  $query->count();
            $penerimaanStokDetail = $query->get();
        } else {
            // dd('test');
            $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp', 'JUDULAN LAPORAN')
                ->where('subgrp', 'JUDULAN LAPORAN')
                ->first();
            $temtabelpenerimaan = "penerimaanstokheader";
            $query->select(
                "$this->table.penerimaanstokheader_id",
                "$this->table.nobukti",
                "$this->table.stok_id",
                "stok.namastok as stok",
                "satuan.satuan as satuan",
                "$this->table.qty",
                "$this->table.harga",
                "$this->table.persentasediscount",
                "$this->table.penerimaanstok_nobukti",
                "$this->table.nominaldiscount",
                "$this->table.total",
                "$this->table.keterangan",
                "statusban.text as statusban",
                DB::raw("isnull(d1.vulkan,0) as vulkanisirke"),
                "$this->table.modifiedby",
                DB::raw("'Laporan Purchase Order (PO)' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )

                ->leftJoin(db::raw($tempvulkan . " d1"), "$this->table.stok_id", "d1.stok_id")
                ->leftJoin(db::raw($temtabelpenerimaan . " penerimaanstokheader"), "$this->table.nobukti", "penerimaanstokheader.nobukti")
                ->leftJoin("stok", "$this->table.stok_id", "stok.id")
                ->leftJoin("satuan", "stok.satuan_id", "satuan.id")
                ->leftJoin('parameter as statusban', 'stok.statusban', 'statusban.id');

            if (request()->penerimaanstok_id == $spbp->id) {
                if (request()->stok_id) {
                    $nobukti = request()->nobukti;
                    $query->where("penerimaanstokheader.penerimaanstok_id", $spbs->text)
                        ->where("$this->table.stok_id", request()->stok_id)
                        ->whereNotIn("$this->table.stok_id", function ($query) use ($nobukti) {
                            $query->select(
                                DB::raw('DISTINCT penerimaanstokdetail.stok_id'),
                            )
                                ->from('penerimaanstokdetail')
                                ->whereNotNull('penerimaanstokdetail.penerimaanstok_nobukti')
                                ->where('penerimaanstokdetail.penerimaanstok_nobukti', '!=', '')
                                ->where('penerimaanstokdetail.penerimaanstok_nobukti', '!=', $nobukti)
                                ->where('penerimaanstokdetail.penerimaanstok_nobukti', 'like', 'SPBS%');
                            // dd($query->get());
                        });
                }
            }
            if ($from == 'klaim') {
                $nobuktiStok = DB::table("penerimaanstokheader")->where('id', request()->penerimaanstokheader_id)->first()->nobukti ?? '';
                if ($nobuktiStok != '') {
                    $stok_id = request()->stok_id ?? 0;
                    $query->whereRaw("penerimaanstokdetail.stok_id not in (select stok_id from pengeluarantruckingdetail where penerimaanstok_nobukti='$nobuktiStok' and stok_id != $stok_id)");
                }
            }
            // if (request()->pengeluaranstok_id == $rtr->id) {

            //     $query->select(
            //         DB::raw('SUM(pengeluaranstokdetail.qty) as qty'),
            //         "$this->table.nobukti",
            //         "$this->table.stok_id",
            //         'stok.namastok as stok',
            //         // "$this->table.qty"', 
            //         DB::raw("$this->table.qty - COALESCE(SUM(pengeluaranstokdetail.qty), 0) as qty"),

            //         "$this->table.harga",
            //         "$this->table.persentasediscount",
            //         "$this->table.penerimaanstok_nobukti",
            //         "$this->table.nominaldiscount",
            //         "$this->table.total",
            //         "$this->table.keterangan"
            //     )
            //         ->leftJoin('pengeluaranstokdetail', 'PenerimaanStokDetail.stok_id', '=', 'pengeluaranstokdetail.stok_id')
            //         ->groupBy(
            //             "$this->table.nobukti",
            //             "$this->table.stok_id",
            //             'stok.namastok',
            //             "$this->table.qty",
            //             "$this->table.harga",
            //             "$this->table.persentasediscount",
            //             "$this->table.penerimaanstok_nobukti",
            //             "$this->table.nominaldiscount",
            //             "$this->table.total",
            //             "$this->table.keterangan"
            //         )
            //         ->havingRaw("$this->table.qty > COALESCE(SUM(pengeluaranstokdetail.qty), 0)");
            //     return $query->get();
            // }

            $this->totalNominal = $query->sum($this->table . '.total');
            $this->filter($query);
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->sort($query);
            $this->paginate($query);
        }
        return $query->get();
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

            ->get($server . "penerimaanstokdetail?limit=0&penerimaanstokheader_id=" . request()->penerimaanstokheader_id);

        $data = $getTrado->json()['data'];
        $class = 'PenerimaanStokDetailController';
        $user = auth('api')->user()->name;
        $temtabel = 'temppgdettnl' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;

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
            $table->integer('penerimaanstokheader_id')->nullable();
            $table->string('nobukti', 300)->nullable();
            $table->integer('stok_id')->nullable();
            $table->string('stok', 300)->nullable();
            $table->double('qty', 15, 2)->nullable();
            $table->double('harga', 15, 2)->nullable();
            $table->double('persentasediscount', 15, 2)->nullable();
            $table->string('penerimaanstok_nobukti', 300)->nullable();
            $table->double('nominaldiscount', 15, 2)->nullable();
            $table->double('total', 15, 2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->unsignedBigInteger('vulkanisirke')->nullable();
            $table->string('modifiedby', 300)->nullable();
        });

        foreach ($data as $row) {
            unset($row['judulLaporan']);
            unset($row['judul']);
            unset($row['tglcetak']);
            unset($row['usercetak']);
            DB::table($temtabel)->insert($row);
        }

        return $temtabel;
    }


    public function getAll($id)
    {
        $query = DB::table("PenerimaanStokDetail");
        $query = $query->select(
            "PenerimaanStokDetail.id",
            "PenerimaanStokDetail.penerimaanstokheader_id",
            "PenerimaanStokDetail.nobukti",
            "stok.namastok as stok",
            "stok.kelompok_id as kelompok_id",
            "satuan.satuan as satuan",
            "PenerimaanStokDetail.stok_id",
            "PenerimaanStokDetail.qty",
            "PenerimaanStokDetail.qtyterpakai",
            "PenerimaanStokDetail.harga",
            "PenerimaanStokDetail.persentasediscount",
            "PenerimaanStokDetail.penerimaanstok_nobukti",
            "PenerimaanStokDetail.nominaldiscount",
            "PenerimaanStokDetail.total",
            "PenerimaanStokDetail.keterangan",
            "PenerimaanStokDetail.vulkanisirke",
            "PenerimaanStokDetail.modifiedby",
        )
            ->leftJoin("stok", "penerimaanstokdetail.stok_id", "stok.id")
            ->leftJoin("satuan", "stok.satuan_id", "satuan.id");

        $data = $query->where("penerimaanstokheader_id", $id)->get();

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

    public function processStore(PenerimaanStokHeader $penerimaanStokHeader, array $data): PenerimaanStokDetail
    {
        $stok = Stok::where('id', $data['stok_id'])->first();
        $stokreuse = Parameter::where('grp', 'STATUS REUSE')->where('subgrp', 'STATUS REUSE')->where('text', 'REUSE')->first();

        $reuse = false;
        if ($stok->statusreuse == $stokreuse->id) {
            $reuse = true;
        }

        $datahitungstok = PenerimaanStok::select('statushitungstok as statushitungstok_id')
            ->where('format', '=', $penerimaanStokHeader->statusformat)
            ->first();
        $statushitungstok = Parameter::where('grp', 'STATUS HITUNG STOK')->where('text', 'HITUNG STOK')->first();

        $do = Parameter::where('grp', 'DO STOK')->where('subgrp', 'DO STOK')->first();
        $spb = Parameter::where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();
        $kor = Parameter::where('grp', 'KOR STOK')->where('subgrp', 'KOR STOK')->first();
        $spbs = Parameter::where('grp', 'REUSE STOK')->where('subgrp', 'REUSE STOK')->first();
        $pg = Parameter::where('grp', 'PG STOK')->where('subgrp', 'PG STOK')->first();
        $pst = Parameter::where('grp', 'PST STOK')->where('subgrp', 'PST STOK')->first();
        $pspk = Parameter::where('grp', 'PSPK STOK')->where('subgrp', 'PSPK STOK')->first();
        $korv = DB::table('penerimaanstok')->where('kodepenerimaan', 'KORV')->first();
        $spbp = DB::table('penerimaanstok')->where('kodepenerimaan', 'SPBP')->first();

        if ($penerimaanStokHeader->penerimaanstok_id == $spbp->id) {
            $penerimaanStokDetailNobukti = PenerimaanStokDetail::where('nobukti', $data['detail_penerimaanstoknobukti'])->where('stok_id', $data['stok_id'])->first();
            if (!$penerimaanStokDetailNobukti) {
                throw ValidationException::withMessages(["detail_penerimaanstoknobukti" => "penerimaan stok No Bukti tidak valid"]);
            }
        }
        if ($penerimaanStokHeader->penerimaanstok_id == $spb->text) {
            $this->updateStokBanMasak($stok);
        }
        if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {
            if (($penerimaanStokHeader->penerimaanstok_id == $spb->text) || ($penerimaanStokHeader->penerimaanstok_id == $kor->text) || ($penerimaanStokHeader->penerimaanstok_id == $pst->text) || ($penerimaanStokHeader->penerimaanstok_id == $pspk->text)) {
                $persediaan = $this->persediaan($penerimaanStokHeader->gudang_id, $penerimaanStokHeader->trado_id, $penerimaanStokHeader->gandengan_id);
                $this->persediaanKe($data['stok_id'], $persediaan['column'] . '_id', $persediaan['value'], $data['qty']);
            }

            if (($penerimaanStokHeader->penerimaanstok_id == $spbs->text) || ($penerimaanStokHeader->penerimaanstok_id == $do->text)) {
                if (!$reuse) {
                    throw ValidationException::withMessages(["qty" => "bukan stok reuse"]);
                }
                $persediaanDari = $this->persediaan($penerimaanStokHeader->gudangdari_id, $penerimaanStokHeader->tradodari_id, $penerimaanStokHeader->gandengandari_id);
                $dari = $this->persediaanDari($data['stok_id'], $persediaanDari['column'] . '_id', $persediaanDari['value'], $data['qty']);
                if (!$dari) {
                    throw ValidationException::withMessages(['qty' => $stok->namastok . ' - qty tidak cukup ']);
                }
                $persediaanKe = $this->persediaan($penerimaanStokHeader->gudangke_id, $penerimaanStokHeader->tradoke_id, $penerimaanStokHeader->gandenganke_id);
                $ke = $this->persediaanKe($data['stok_id'], $persediaanKe['column'] . '_id', $persediaanKe['value'], $data['qty']);
                if ($penerimaanStokHeader->penerimaanstok_id == $spbs->text) {
                    $this->vulkanStokPlus($data['stok_id'], 1);
                    $data['vulkanisirke'] = 1;
                }
            }

            if ($penerimaanStokHeader->penerimaanstok_id == $pg->text) {

                $persediaanDari = $this->persediaan($penerimaanStokHeader->gudangdari_id, $penerimaanStokHeader->tradodari_id, $penerimaanStokHeader->gandengandari_id);
                $dari = $this->persediaanDari($data['stok_id'], $persediaanDari['column'] . '_id', $persediaanDari['value'], $data['qty']);

                if (!$dari) {
                    // dd()
                    throw ValidationException::withMessages(['qty' => $stok->namastok . ' - qty tidak cukup ']);
                }
                $persediaanKe = $this->persediaan($penerimaanStokHeader->gudangke_id, $penerimaanStokHeader->tradoke_id, $penerimaanStokHeader->gandenganke_id);
                $ke = $this->persediaanKe($data['stok_id'], $persediaanKe['column'] . '_id', $persediaanKe['value'], $data['qty']);
            }
        }
        if ($korv->id == $penerimaanStokHeader->penerimaanstok_id) {
            $vulkan = $this->vulkanStokPlus($data['stok_id'], $data['vulkanisirke']);
            if (!$vulkan) {
                throw ValidationException::withMessages(['vulkanisirke' => 'vulkannisir tidak cukup']);
            }
        }

        $total = ceil(($data['qty'] * $data['harga']));
        $nominaldiscount = $data['totalsebelum'] * ($data['persentasediscount'] / 100);
        // dd($data['totalItem'],$data['totalsebelum'],$nominaldiscount,$total);
        // dd($data['nominaldiscount'],$nominaldiscount);
        // $total -= $nominaldiscount;
        $penerimaanStokDetail = new PenerimaanStokDetail();
        $penerimaanStokDetail->penerimaanstokheader_id = $data['penerimaanstokheader_id'];
        $penerimaanStokDetail->nobukti = $data['nobukti'];
        $penerimaanStokDetail->stok_id = $data['stok_id'];
        $penerimaanStokDetail->qty = $data['qty'];
        $penerimaanStokDetail->qtyterpakai = $data['qtyterpakai'];
        $penerimaanStokDetail->harga = $data['harga'];
        $penerimaanStokDetail->nominaldiscount = $nominaldiscount;
        $penerimaanStokDetail->total = $data['totalItem'];
        $penerimaanStokDetail->penerimaanstok_nobukti = $data['detail_penerimaanstoknobukti'];
        $penerimaanStokDetail->persentasediscount = $data['persentasediscount'] ?? 0;
        $penerimaanStokDetail->vulkanisirke = $data['vulkanisirke'] ?? '';
        $penerimaanStokDetail->keterangan = $data['detail_keterangan'];

        $penerimaanStokDetail->modifiedby = auth('api')->user()->name;
        $penerimaanStokDetail->info = html_entity_decode(request()->info);

        if (!$penerimaanStokDetail->save()) {
            throw new \Exception("Error storing Penerimaan Stok Detail.");
        }

        return $penerimaanStokDetail;
    }

    public function updateStokBanMasak(Stok $stok)
    {
        $kelompokBan = DB::table('kelompok')->select('id')->where('kelompok.kodekelompok', 'BAN')->first();
        if (!$stok) {
            return false;
        }
        if ($stok->kelompok_id == $kelompokBan->id) {
            $kondisiBanMasak = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS KONDISI BAN')->where('subgrp', 'STATUS KONDISI BAN')->where('text', 'MASAK')->first();
            $stok->statusban = $kondisiBanMasak->id;
            $stok->totalvulkanisir = 0;
            $stok->save();
            return true;
        }
    }


    public function vulkanStokPlus($stok_id, $vulkan)
    {
        $stok = Stok::find($stok_id);
        if (!$stok) {
            return false;
        }

        $kondisiBanMasak = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS KONDISI BAN')->where('subgrp', 'STATUS KONDISI BAN')->where('text', 'MASAK')->first();
        $kondisiBanMentah = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS KONDISI BAN')->where('subgrp', 'STATUS KONDISI BAN')->where('text', 'MENTAH')->first();
        if ($stok->statusban == $kondisiBanMentah->id) {
            $stok->statusban = $kondisiBanMasak->id;
        }
        $total = $stok->totalvulkanisir + $vulkan;
        $stok->totalvulkanisir = $total;
        $stok->save();
        return true;
    }

    public function vulkanStokMinus($stok_id, $vulkan)
    {
        $stok = Stok::find($stok_id);
        if (!$stok) {
            return false;
        }
        $total = $stok->totalvulkanisir - $vulkan;
        if ($total < 0) {
            return false;
        }

        $stok->totalvulkanisir = $total;
        $stok->save();
        return $stok;
    }

    public function persediaan($gudang, $trado, $gandengan)
    {
        $kolom = null;
        $nama = null;
        $value = 0;
        if (!empty($gudang)) {
            $kolom = "gudang";
            $nama = "GUDANG";
            $value = $gudang;
        } elseif (!empty($trado)) {
            $kolom = "trado";
            $nama = "TRADO";
            $value = $trado;
        } elseif (!empty($gandengan)) {
            $kolom = "gandengan";
            $nama = "GANDENGAN";
            $value = $gandengan;
        }
        return [
            "column" => $kolom,
            "value" => $value,
            "nama" => $nama,
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

        // $stokpersediaangudang = $this->checkTempat($stokId, $persediaan, $persediaanId); //stok persediaan 
        // if (!$stokpersediaangudang) {
        //     return false;
        // }
        // $stokpersediaan = StokPersediaan::lockForUpdate()->find($stokpersediaangudang->id);
        // if ($qty > $stokpersediaan->qty) { 
        //     return false;
        // }
        // $result = $stokpersediaan->qty - $qty;
        // $stokpersediaan->update(['qty' => $result]);
        // return $stokpersediaan;
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
        // return $stokpersediaangudang;
        return true;
    }

    public function persediaanDariReturn($stokId, $persediaan, $persediaanId, $qty)
    {
        // $stokpersediaangudang = $this->checkTempat($stokId, $persediaan, $persediaanId); //stok persediaan 
        // if (!$stokpersediaangudang) {
        //     return false;
        // }

        // $stok = db::table('kartustok')->from(db::raw("kartustok a with (readuncommitted)"))
        // ->select(
        //     db::raw("sum(isnull(qtymasuk,0)-isnull(qtykeluar,0)) as qty")
        // )
        // ->where("stok_id", $stokId)->where("$persediaan", $persediaanId)->first()
        // ->qty ?? 0;

        // // $stokpersediaan = StokPersediaan::lockForUpdate()->find($stokpersediaangudang->id);
        // // $result = $stokpersediaan->qty + $qty;
        // $result = $stok + $qty;
        // // $stokpersediaan->update(['qty' => $result]);
        return true;
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

        // dd($stok->toSql());

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
        // return $stokpersediaan;
        return true;
    }

    public function checkTempat($stokId, $persediaan, $persediaanId)
    {
        $result = StokPersediaan::lockForUpdate()->where("stok_id", $stokId)->where("$persediaan", $persediaanId)->first();
        return (!$result) ? false : $result;
    }

    public function returnStokPenerimaan($id)
    {
        $penerimaanStokHeader = PenerimaanStokHeader::findOrFail($id);

        // $penerimaanStokHeader = PenerimaanStokHeader::find($id);
        $spb = Parameter::where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();
        $pg = Parameter::where('grp', 'PG STOK')->where('subgrp', 'PG STOK')->first();
        $do = Parameter::where('grp', 'DO STOK')->where('subgrp', 'DO STOK')->first();
        $spbs = Parameter::where('grp', 'REUSE STOK')->where('subgrp', 'REUSE STOK')->first();
        $kor = Parameter::where('grp', 'KOR STOK')->where('subgrp', 'KOR STOK')->first();
        $pst = Parameter::where('grp', 'PST STOK')->where('subgrp', 'PST STOK')->first();
        $pspk = Parameter::where('grp', 'PSPK STOK')->where('subgrp', 'PSPK STOK')->first();

        $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
        $pengeluaranStokDetail = PenerimaanStokDetail::where('penerimaanstokheader_id', $id)->get();
        $gudang_id = $penerimaanStokHeader->gudang_id;
        $trado_id = $penerimaanStokHeader->trado_id;
        $gandengan_id = $penerimaanStokHeader->gandengan_id;
        $gudangke_id = $penerimaanStokHeader->gudangke_id;
        $tradoke_id = $penerimaanStokHeader->tradoke_id;
        $gandenganke_id = $penerimaanStokHeader->gandenganke_id;
        $gudangdari_id = $penerimaanStokHeader->gudangdari_id;
        $tradodari_id = $penerimaanStokHeader->tradodari_id;
        $gandengandari_id = $penerimaanStokHeader->gandengandari_id;
        foreach ($pengeluaranStokDetail as $item) {
            if (($penerimaanStokHeader->penerimaanstok_id == $spb->text) || ($penerimaanStokHeader->penerimaanstok_id == $kor->text) || ($penerimaanStokHeader->penerimaanstok_id == $pst->text) || ($penerimaanStokHeader->penerimaanstok_id == $pspk->text)) {
                $persediaan = $this->persediaan($gudang_id, $trado_id, $gandengan_id);
                $this->persediaanKeReturn($item['stok_id'], $persediaan['column'] . '_id', $persediaan['value'], $item['qty']);
            }

            if (($penerimaanStokHeader->penerimaanstok_id == $spbs->text) || ($penerimaanStokHeader->penerimaanstok_id == $do->text) || ($penerimaanStokHeader->penerimaanstok_id == $pg->text)) {
                $persediaanDari = $this->persediaan($gudangdari_id, $tradodari_id, $gandengandari_id);
                $dari = $this->persediaanDariReturn($item['stok_id'], $persediaanDari['column'] . '_id', $persediaanDari['value'], $item['qty']);
                if (!$dari) {
                    throw ValidationException::withMessages(["qty" => "qty tidak cukup dari"]);
                }

                $persediaanKe = $this->persediaan($gudangke_id, $tradoke_id, $gandenganke_id);
                $ke = $this->persediaanKeReturn($item['stok_id'], $persediaanKe['column'] . '_id', $persediaanKe['value'], $item['qty']);
                // dd($ke);
                if (!$ke) {
                    throw ValidationException::withMessages(["qty" => "qty tidak cukup ke"]);
                }
                // dd('test');
            }
        }
    }

    public function validasiSPBMinus($penerimaanStokHeader_id, $stok_id, $qtyInput)
    {
        $penerimaanStokDetail = PenerimaanStokDetail::select('penerimaanstokdetail.qty as qty', 'stok.namastok as stok', 'penerimaanstokdetail.nobukti as nobukti')
            ->where('penerimaanstokheader_id', $penerimaanStokHeader_id)
            ->where('penerimaanstokdetail.stok_id', $stok_id)
            ->leftJoin("stok", "penerimaanstokdetail.stok_id", "stok.id")
            ->first();
        $penerimaanStokHeader = PenerimaanStokHeader::findOrFail($penerimaanStokHeader_id);

        $spb = Parameter::where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();

        if (($penerimaanStokHeader->penerimaanstok_id == $spb->text)) {
            $ks = KartuStok::select('stok_id', DB::raw('SUM(qtymasuk) - SUM(qtykeluar) AS qty'))
                ->where('stok_id', $stok_id)
                ->where('nobukti', '<>', $penerimaanStokDetail->nobukti)
                ->groupBy('stok_id')->first();
            if ($ks) {
                $hasilAkhir = $ks->qty + $qtyInput;
                if ($hasilAkhir < 0) {
                    throw ValidationException::withMessages(["qty" => "Qty $penerimaanStokDetail->stok stok terakhir minus, proses tidak bisa dilanjutkan"]);
                }
            }
        }
        return true;
    }
    public function returnVulkanisir($id)
    {
        $penerimaanStokHeader = PenerimaanStokHeader::findOrFail($id);
        $penerimaanStokDetail = PenerimaanStokDetail::where('penerimaanstokheader_id', $id)->get();
        $korv = DB::table('penerimaanstok')->where('kodepenerimaan', 'KORV')->first();
        $spbs = Parameter::where('grp', 'REUSE STOK')->where('subgrp', 'REUSE STOK')->first();
        foreach ($penerimaanStokDetail as $item) {
            if (($penerimaanStokHeader->penerimaanstok_id == $korv->id) || ($penerimaanStokHeader->penerimaanstok_id == $spbs->text)) {
                $dari = $this->vulkanStokMinus($item->stok_id, $item->vulkanisirke);
            }
        }
    }
}
