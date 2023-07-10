<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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

        if (isset(request()->id)) {
            $query->where("$this->table.id", request()->id);
        }

        if (isset(request()->penerimaanstokheader_id)) {
            $query->where("$this->table.penerimaanstokheader_id", request()->penerimaanstokheader_id);
        }
        if (isset(request()->forReport) && request()->forReport) {
            $query->select(
                "$this->table.penerimaanstokheader_id",
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
            $totalRows =  $query->count();
            $penerimaanStokDetail = $query->get();
        }else{
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
                "$this->table.nominaldiscount",
                "$this->table.total",
                "$this->table.keterangan",
                "$this->table.vulkanisirke",
                "$this->table.modifiedby",
                DB::raw("'Laporan Purchase Order (PO)' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :".auth('api')->user()->name."' as usercetak")
            )
            ->leftJoin("penerimaanstokheader", "$this->table.penerimaanstokheader_id", "penerimaanstokheader.id")
            ->leftJoin("stok", "$this->table.stok_id", "stok.id");
            $this->totalNominal = $query->sum('total');
            $this->filter($query);
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->sort($query);
            $this->paginate($query);
        }
        return $query->get();
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
            "PenerimaanStokDetail.nominaldiscount",
            "PenerimaanStokDetail.total",
            "PenerimaanStokDetail.keterangan",
            "PenerimaanStokDetail.vulkanisirke",
            "PenerimaanStokDetail.modifiedby",
        )
        ->leftJoin("stok","penerimaanstokdetail.stok_id","stok.id");

        $data = $query->where("penerimaanstokheader_id",$id)->get();

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
                                $query = $query->whereRaw("format(".$this->table . "." . $filters['field'].", '#,#0.00') LIKE '%$filters[data]%'");
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
                                $query = $query->orWhereRaw("format(".$this->table . "." . $filters['field'].", '#,#0.00') LIKE '%$filters[data]%'");
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
        $stok= Stok::where('id', $data['stok_id'])->first();
        $stokreuse = Parameter::where('grp', 'STATUS REUSE')->where('subgrp', 'STATUS REUSE')->where('text', 'REUSE')->first();
        
        $reuse=false;
        if ($stok->statusreuse==$stokreuse->id) {
            $reuse=true;
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
        // dd($datahitungstok->statushitungstok_id);
        if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {
            if (($penerimaanStokHeader->penerimaanstok_id == $spb->text)||($penerimaanStokHeader->penerimaanstok_id == $kor->text) ||($penerimaanStokHeader->penerimaanstok_id == $pst->text)||($penerimaanStokHeader->penerimaanstok_id == $pspk->text)) {
                $persediaan = $this->persediaan($penerimaanStokHeader->gudang_id,$penerimaanStokHeader->trado_id,$penerimaanStokHeader->gandengan_id);
                $this->persediaanKe($data['stok_id'],$persediaan['column'].'_id',$persediaan['value'],$data['qty']);
            }

            if (($penerimaanStokHeader->penerimaanstok_id == $spbs->text)||($penerimaanStokHeader->penerimaanstok_id == $do->text)) {
                if (!$reuse) {
                    throw ValidationException::withMessages("bukan stok reuse");                

                }
                $persediaanDari = $this->persediaan($penerimaanStokHeader->gudangdari_id,$penerimaanStokHeader->tradodari_id,$penerimaanStokHeader->gandengandari_id);
                $dari = $this->persediaanDari($data['stok_id'],$persediaanDari['column'].'_id',$persediaanDari['value'],$data['qty']);
                if (!$dari) {
                    throw ValidationException::withMessages("qty tidak cukup");
                }
                $persediaanKe = $this->persediaan($penerimaanStokHeader->gudangke_id,$penerimaanStokHeader->tradoke_id,$penerimaanStokHeader->gandenganke_id);
                $ke = $this->persediaanKe($data['stok_id'],$persediaanKe['column'].'_id',$persediaanKe['value'],$data['qty']);
            }
            
            if ($penerimaanStokHeader->penerimaanstok_id == $pg->text) {
                
                    $persediaanDari = $this->persediaan($penerimaanStokHeader->gudangdari_id,$penerimaanStokHeader->tradodari_id,$penerimaanStokHeader->gandengandari_id);
                    $dari = $this->persediaanDari($data['stok_id'],$persediaanDari['column'].'_id',$persediaanDari['value'],$data['qty']);
                    
                    if (!$dari) {
                        throw ValidationException::withMessages("qty tidak cukup");
                    }
                    $persediaanKe = $this->persediaan($penerimaanStokHeader->gudangke_id,$penerimaanStokHeader->tradoke_id,$penerimaanStokHeader->gandenganke_id);
                    $ke = $this->persediaanKe($data['stok_id'],$persediaanKe['column'].'_id',$persediaanKe['value'],$data['qty']);
                    
                
            }
            
        }
        
        $total = $data['qty'] * $data['harga'];
        $nominaldiscount = $total * ($data['persentasediscount'] / 100);
        $total -= $nominaldiscount;
                
        $penerimaanStokDetail = new PenerimaanStokDetail();
        $penerimaanStokDetail->penerimaanstokheader_id = $data['penerimaanstokheader_id'];
        $penerimaanStokDetail->nobukti = $data['nobukti'];
        $penerimaanStokDetail->stok_id = $data['stok_id'];
        $penerimaanStokDetail->qty = $data['qty'];
        $penerimaanStokDetail->harga = $data['harga'];
        $penerimaanStokDetail->nominaldiscount = $nominaldiscount;
        $penerimaanStokDetail->total = $total;
        $penerimaanStokDetail->penerimaanstok_nobukti = $data['detail_penerimaanstoknobukti'];
        $penerimaanStokDetail->persentasediscount = $data['persentasediscount'] ?? 0;
        $penerimaanStokDetail->vulkanisirke = $data['vulkanisirke'] ?? '';
        $penerimaanStokDetail->keterangan = $data['detail_keterangan'];

        $penerimaanStokDetail->modifiedby = auth('api')->user()->name;
        
        if (!$penerimaanStokDetail->save()) {
            throw new \Exception("Error storing Penerimaan Stok Detail.");
        }

        return $penerimaanStokDetail;
    }

    public function persediaan($gudang,$trado,$gandengan)
    {
        $kolom = null;
        $nama = null;
        $value = 0;
        if(!empty($gudang)) {
            $kolom = "gudang";
            $nama = "GUDANG";
            $value = $gudang;
        } elseif(!empty($trado)) {
            $kolom = "trado";
            $nama = "TRADO";
            $value = $trado;
        } elseif(!empty($gandengan)) {
            $kolom = "gandengan";
            $nama = "GANDENGAN";
            $value = $gandengan;
        }
        return [
            "column"=>$kolom,
            "value"=>$value,
            "nama"=>$nama,
        ];
    }

    public function persediaanDari($stokId,$persediaan,$persediaanId,$qty)
    {
        $stokpersediaangudang = $this->checkTempat($stokId,$persediaan,$persediaanId); //stok persediaan 
        if (!$stokpersediaangudang) {
            return false;
        }
        $stokpersediaan = StokPersediaan::lockForUpdate()->find($stokpersediaangudang->id);
        if ($qty > $stokpersediaan->qty){ //check qty
            return false;
        }
        $result = $stokpersediaan->qty - $qty;
        $stokpersediaan->update(['qty'=> $result]);
        return $stokpersediaan;
    }
    public function persediaanKe($stokId,$persediaan,$persediaanId,$qty)
    {
        $stokpersediaangudang = $this->checkTempat($stokId,$persediaan,$persediaanId); //stok persediaan 
        if (!$stokpersediaangudang) {
            $stokpersediaangudang= StokPersediaan::create(["stok_id"=> $stokId, $persediaan => $persediaanId]);
        }
        $stokpersediaangudang->qty += $qty;
        $stokpersediaangudang->save();
        return $stokpersediaangudang;
    }
    
    public function persediaanDariReturn($stokId,$persediaan,$persediaanId,$qty)
    {
        $stokpersediaangudang = $this->checkTempat($stokId,$persediaan,$persediaanId); //stok persediaan 
        if (!$stokpersediaangudang) {
            return false;
        }
        $stokpersediaan = StokPersediaan::lockForUpdate()->find($stokpersediaangudang->id);
        $result = $stokpersediaan->qty + $qty;
        $stokpersediaan->update(['qty'=> $result]);
        return $stokpersediaan;
    }
    public function persediaanKeReturn($stokId,$persediaan,$persediaanId,$qty)
    {
        $stokpersediaangudang = $this->checkTempat($stokId,$persediaan,$persediaanId); //stok persediaan 
        if (!$stokpersediaangudang) {
            return false;
        }
        $stokpersediaan = StokPersediaan::lockForUpdate()->find($stokpersediaangudang->id);
        if ($qty > $stokpersediaan->qty){ //check qty
            return false;
        }
        $stokpersediaan->qty -= $qty;
        $stokpersediaan->save();
        return $stokpersediaan;
    }

    public function checkTempat($stokId,$persediaan,$persediaanId)
    {
        $result = StokPersediaan::lockForUpdate()->where("stok_id", $stokId)->where("$persediaan", $persediaanId)->first();
        return (!$result) ? false :$result;
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
            if (($penerimaanStokHeader->penerimaanstok_id == $spb->text)||($penerimaanStokHeader->penerimaanstok_id == $kor->text) ||($penerimaanStokHeader->penerimaanstok_id == $pst->text)||($penerimaanStokHeader->penerimaanstok_id == $pspk->text)) {
                $persediaan = $this->persediaan($gudang_id,$trado_id,$gandengan_id);
                $this->persediaanKeReturn($item['stok_id'],$persediaan['column'].'_id',$persediaan['value'],$item['qty']);
            }

            if (($penerimaanStokHeader->penerimaanstok_id == $spbs->text)||($penerimaanStokHeader->penerimaanstok_id == $do->text)||($penerimaanStokHeader->penerimaanstok_id == $pg->text)) {
                $persediaanDari = $this->persediaan($gudangdari_id,$tradodari_id,$gandengandari_id);
                $dari = $this->persediaanDariReturn($item['stok_id'],$persediaanDari['column'].'_id',$persediaanDari['value'],$item['qty']);
                if (!$dari) {
                    throw ValidationException::withMessages("qty tidak cukup dari");
                }
                $persediaanKe = $this->persediaan($gudangke_id,$tradoke_id,$gandenganke_id);
                $ke = $this->persediaanKeReturn($item['stok_id'],$persediaanKe['column'].'_id',$persediaanKe['value'],$item['qty']);
                if (!$ke) {
                    throw ValidationException::withMessages("qty tidak cukup ke");
                }
            }
        }

    }


}
