<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));
        if (isset(request()->id)) {
            $query->where("$this->table.id", request()->id);
        }

        if (isset(request()->pengeluaranstokheader_id)) {
            $query->where("$this->table.pengeluaranstokheader_id", request()->pengeluaranstokheader_id);
        }
        if (isset(request()->forReport) && request()->forReport) {
            $query->select(
                "$this->table.pengeluaranstokheader_id",
                "$this->table.nobukti",
                "stok.namastok as stok",
                "$this->table.stok_id",
                "$this->table.qty",
                "$this->table.harga",
                "$this->table.persentasediscount",
                "$this->table.nominaldiscount",
                "$this->table.total",
                "$this->table.keterangan",
                "$this->table.vulkanisirke",
                "$this->table.modifiedby",
            );
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
                "stok.namastok as stok",
                "$this->table.qty",
                "$this->table.harga",
                "$this->table.persentasediscount",
                "$this->table.nominaldiscount",
                "$this->table.total",
                "$this->table.keterangan",
                "$this->table.vulkanisirke",
                "parameter.text as statusoli",
                "$this->table.modifiedby",
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
                ->leftJoin("pengeluaranstokheader", "$this->table.pengeluaranstokheader_id", "pengeluaranstokheader.id")
                ->leftJoin("stok", "$this->table.stok_id", "stok.id")
                ->leftJoin("parameter", "$this->table.statusoli", "parameter.id");

            $this->totalNominal = $query->sum('total');
            $this->filter($query);
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->sort($query);
            $this->paginate($query);
            // dd($query->toSql());
        }
        return $query->get();
    }

    public function getAll($id)
    {
        $query = DB::table('PengeluaranStokDetail');
        $query = $query->select(
            'PengeluaranStokDetail.Pengeluaranstokheader_id',
            'PengeluaranStokDetail.nobukti',
            'stok.namastok as stok',
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
            'PengeluaranStokDetail.modifiedby',
        )
            ->leftJoin('stok', 'PengeluaranStokDetail.stok_id', 'stok.id')
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
        $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
        $gudangsementara = Parameter::where('grp', 'GUDANG SEMENTARA')->where('subgrp', 'GUDANG SEMENTARA')->first();
        $gudangpihak3 = Parameter::where('grp', 'GUDANG PIHAK3')->where('subgrp', 'GUDANG PIHAK3')->first();

        if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {
            if ($pengeluaranStokHeader->pengeluaranstok_id == $kor->text) {
                $persediaan = $this->persediaan($pengeluaranStokHeader->gudang_id, $pengeluaranStokHeader->trado_id, $pengeluaranStokHeader->gandengan_id);
                $dari = $this->persediaanDari($data['stok_id'], $persediaan['column'] . '_id', $persediaan['value'], $data['qty']);
            } else if ($pengeluaranStokHeader->pengeluaranstok_id == $pja->text) {
                $dari = $this->persediaanDari($data['stok_id'], 'gudang_id', $gudangpihak3->text, $data['qty']);
            } else {
                $dari = $this->persediaanDari($data['stok_id'], 'gudang_id', $gudangkantor->text, $data['qty']);
            }
            if (!$dari) {
                throw ValidationException::withMessages(['qty' => 'qty tidak cukup']);
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
        }





        $pengeluaranStokDetail = new PengeluaranStokDetail();
        $pengeluaranStokDetail->pengeluaranstokheader_id = $data['pengeluaranstokheader_id'];
        $pengeluaranStokDetail->nobukti = $data['nobukti'];
        $pengeluaranStokDetail->stok_id = $data['stok_id'];
        $pengeluaranStokDetail->qty = $data['qty'];
        $pengeluaranStokDetail->harga = $data['harga'];
        $pengeluaranStokDetail->nominaldiscount = $nominaldiscount;
        $pengeluaranStokDetail->total = $total;
        $pengeluaranStokDetail->persentasediscount = $data['persentasediscount'];
        $pengeluaranStokDetail->vulkanisirke = $data['vulkanisirke'];
        $pengeluaranStokDetail->statusoli = $data['statusoli'];
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
        $total = $stok->totalvulkanisir - $vulkan;
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
                $dari = $this->persediaanDariReturn($detail->stok_id, 'gudang_id', $gudangpihak3->text, $detail->qty);
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
        foreach ($pengeluaranStokDetailFifo as $fifo) {
            $penerimaanStok = PenerimaanStokDetail::where('nobukti', $fifo->penerimaanstokheader_nobukti)->where('stok_id', $fifo->stok_id)->first();
            $penerimaanStok->qtykeluar -= $fifo->qty;
            $penerimaanStok->save();
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
