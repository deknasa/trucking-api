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

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));
        $spbp = DB::table('penerimaanstok')->where('kodepenerimaan', 'SPBP')->first();
        $rtr = DB::table('pengeluaranstok')->where('kodepengeluaran', 'RTR')->first();
        $spbs = Parameter::where('grp', 'REUSE STOK')->where('subgrp', 'REUSE STOK')->first();

        if (isset(request()->id)) {
            $query->where("$this->table.id", request()->id);
        }

        if (isset(request()->penerimaanstokheader_id)) {
            $query->where("$this->table.penerimaanstokheader_id", request()->penerimaanstokheader_id);
        }
        if (isset(request()->forReport) && request()->forReport) {
            $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp', 'JUDULAN LAPORAN')
                ->where('subgrp', 'JUDULAN LAPORAN')
                ->first();
            $query->select(
                "$this->table.penerimaanstokheader_id",
                "$this->table.nobukti",
                "$this->table.stok_id",
                "stok.namastok as stok",
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
                ->leftJoin("penerimaanstokheader", "$this->table.penerimaanstokheader_id", "penerimaanstokheader.id")
                ->leftJoin("stok", "$this->table.stok_id", "stok.id");
            $totalRows =  $query->count();
            $penerimaanStokDetail = $query->get();
        } else {
            $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp', 'JUDULAN LAPORAN')
                ->where('subgrp', 'JUDULAN LAPORAN')
                ->first();

            $query->select(
                "$this->table.penerimaanstokheader_id",
                "$this->table.nobukti",
                "$this->table.stok_id",
                "stok.namastok as stok",
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
                ->leftJoin("penerimaanstokheader", "$this->table.penerimaanstokheader_id", "penerimaanstokheader.id")
                ->leftJoin("stok", "$this->table.stok_id", "stok.id");

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

            if (request()->pengeluaranstok_id == $rtr->id) {

                $query->select(
                    DB::raw('SUM(pengeluaranstokdetail.qty) as qty'),
                    "$this->table.nobukti",
                    "$this->table.stok_id",
                    'stok.namastok as stok',
                    // "$this->table.qty"', 
                    DB::raw("$this->table.qty - COALESCE(SUM(pengeluaranstokdetail.qty), 0) as qty"),

                    "$this->table.harga",
                    "$this->table.persentasediscount",
                    "$this->table.penerimaanstok_nobukti",
                    "$this->table.nominaldiscount",
                    "$this->table.total",
                    "$this->table.keterangan"
                )
                    ->leftJoin('pengeluaranstokdetail', 'PenerimaanStokDetail.stok_id', '=', 'pengeluaranstokdetail.stok_id')
                    ->groupBy(
                        "$this->table.nobukti",
                        "$this->table.stok_id",
                        'stok.namastok',
                        "$this->table.qty",
                        "$this->table.harga",
                        "$this->table.persentasediscount",
                        "$this->table.penerimaanstok_nobukti",
                        "$this->table.nominaldiscount",
                        "$this->table.total",
                        "$this->table.keterangan"
                    )
                    ->havingRaw("$this->table.qty > COALESCE(SUM(pengeluaranstokdetail.qty), 0)");
                return $query->get();
            }

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
            "PenerimaanStokDetail.penerimaanstokheader_id",
            "PenerimaanStokDetail.nobukti",
            "stok.namastok as stok",
            "PenerimaanStokDetail.stok_id",
            "PenerimaanStokDetail.qty",
            "PenerimaanStokDetail.harga",
            "PenerimaanStokDetail.persentasediscount",
            "PenerimaanStokDetail.penerimaanstok_nobukti",
            "PenerimaanStokDetail.nominaldiscount",
            "PenerimaanStokDetail.total",
            "PenerimaanStokDetail.keterangan",
            "PenerimaanStokDetail.vulkanisirke",
            "PenerimaanStokDetail.modifiedby",
        )
            ->leftJoin("stok", "penerimaanstokdetail.stok_id", "stok.id");

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
                    throw ValidationException::withMessages(["qty" => "qty tidak cukup"]);
                }
                $persediaanKe = $this->persediaan($penerimaanStokHeader->gudangke_id, $penerimaanStokHeader->tradoke_id, $penerimaanStokHeader->gandenganke_id);
                $ke = $this->persediaanKe($data['stok_id'], $persediaanKe['column'] . '_id', $persediaanKe['value'], $data['qty']);
            }

            if ($penerimaanStokHeader->penerimaanstok_id == $pg->text) {

                $persediaanDari = $this->persediaan($penerimaanStokHeader->gudangdari_id, $penerimaanStokHeader->tradodari_id, $penerimaanStokHeader->gandengandari_id);
                $dari = $this->persediaanDari($data['stok_id'], $persediaanDari['column'] . '_id', $persediaanDari['value'], $data['qty']);

                if (!$dari) {
                    // dd()
                    throw ValidationException::withMessages(["qty" => "qty tidak cukup"]);
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
        // $total -= $nominaldiscount;
        $penerimaanStokDetail = new PenerimaanStokDetail();
        $penerimaanStokDetail->penerimaanstokheader_id = $data['penerimaanstokheader_id'];
        $penerimaanStokDetail->nobukti = $data['nobukti'];
        $penerimaanStokDetail->stok_id = $data['stok_id'];
        $penerimaanStokDetail->qty = $data['qty'];
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


    public function returnVulkanisir($id)
    {
        $penerimaanStokHeader = PenerimaanStokHeader::findOrFail($id);
        $penerimaanStokDetail = PenerimaanStokDetail::where('penerimaanstokheader_id', $id)->get();
        $korv = DB::table('penerimaanstok')->where('kodepenerimaan', 'KORV')->first();

        foreach ($penerimaanStokDetail as $item) {
            if ($penerimaanStokHeader->penerimaanstok_id == $korv->id) {
                $dari = $this->vulkanStokMinus($item->stok_id, $item->vulkanisirke);
            }
        }
    }
}
