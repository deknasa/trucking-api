<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
                "$this->table.modifiedby",
            ) 
            ->leftJoin("pengeluaranstokheader", "$this->table.pengeluaranstokheader_id", "pengeluaranstokheader.id")
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
        $query = DB::table('PengeluaranStokDetail');
        $query = $query->select(
            'PengeluaranStokDetail.Pengeluaranstokheader_id',
            'PengeluaranStokDetail.nobukti',
            'stok.namastok as stok',
            'PengeluaranStokDetail.stok_id',
            'PengeluaranStokDetail.qty',
            'PengeluaranStokDetail.harga',
            'PengeluaranStokDetail.persentasediscount',
            'PengeluaranStokDetail.nominaldiscount',
            'PengeluaranStokDetail.total',
            'PengeluaranStokDetail.keterangan',
            'PengeluaranStokDetail.vulkanisirke',
            'PengeluaranStokDetail.modifiedby',
        )
        ->leftJoin('stok','PengeluaranStokDetail.stok_id','stok.id');

        $data = $query->where("Pengeluaranstokheader_id",$id)->get();

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

    public function processStore(PengeluaranStokHeader $pengeluaranStokHeader, array $data): PengeluaranStokDetail
    {
        $total = $data['qty'] * $data['harga'];
        $nominaldiscount = $total * ($data['persentasediscount'] / 100);
        $total -= $nominaldiscount;
        $pengeluaranStokHeader = PengeluaranStokHeader::where('id', $data['pengeluaranstokheader_id'])->first();

        $stok= Stok::where('id', $data['stok_id'])->first();
        $stokreuse = Parameter::where('grp', 'STATUS REUSE')->where('subgrp', 'STATUS REUSE')->where('text', 'REUSE')->first();
        
        $reuse=false;
        if ($stok->statusreuse==$stokreuse->id) {
            $reuse=true;
        } 

        $datahitungstok = PengeluaranStok::select('statushitungstok as statushitungstok_id')->where('format', '=', $pengeluaranStokHeader->statusformat)->first();
        $statushitungstok = Parameter::where('grp', 'STATUS HITUNG STOK')->where('text', 'HITUNG STOK')->first();
        
        $spk = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();
        $kor = Parameter::where('grp', 'KOR MINUS STOK')->where('subgrp', 'KOR MINUS STOK')->first();
        $rtr = Parameter::where('grp', 'RETUR STOK')->where('subgrp', 'RETUR STOK')->first();
        $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();

        if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {
            if ($pengeluaranStokHeader->pengeluaranstok_id == $kor->text) {
                $persediaan = $this->persediaan($pengeluaranStokHeader->gudang_id,$pengeluaranStokHeader->trado_id,$pengeluaranStokHeader->gandengan_id);
                $dari = $this->persediaanDari($data['stok_id'],$persediaan['column'].'_id',$persediaan['value'],$data['qty']);
            }else {
                $dari = $this->persediaanDari($data['stok_id'],'gudang_id', $gudangkantor->text,$data['qty']);
            }
            if (!$dari) {
                throw new \Exception("qty tidak cukup");                
            }
            // if (($pengeluaranStokHeader->pengeluaranstok_id != $spk->text)) {
            //     // if (!$reuse) {
            //     //     throw new \Exception("bukan stok reuse");                

            //     // }
            // }
            if ($pengeluaranStokHeader->pengeluaranstok_id == $spk->text) {
                if (!$reuse) {
                    throw new \Exception("bukan stok reuse");                
                }
                $persediaan = $this->persediaan($pengeluaranStokHeader->gudang_id,$pengeluaranStokHeader->trado_id,$pengeluaranStokHeader->gandengan_id);
                $ke = $this->persediaanKe($data['stok_id'],$persediaan['column'].'_id',$persediaan['value'],$data['qty']);
            }
        }

        $pengeluaranStokDetail = new PengeluaranStokDetail();
        $pengeluaranStokDetail->pengeluaranstokheader_id = $data['pengeluaranstokheader_id'];
        $pengeluaranStokDetail->nobukti = $data['nobukti'];
        $pengeluaranStokDetail->stok_id = $data['stok_id'];
        $pengeluaranStokDetail->qty = $data['qty'];
        $pengeluaranStokDetail->harga = $data['harga'] ?? 0;
        $pengeluaranStokDetail->nominaldiscount = $nominaldiscount;
        $pengeluaranStokDetail->total = $total ?? 0;
        $pengeluaranStokDetail->persentasediscount = $data['persentasediscount'] ?? 0;
        $pengeluaranStokDetail->vulkanisirke = $data['vulkanisirke'] ?? 0;
        $pengeluaranStokDetail->keterangan = $data['detail_keterangan'];

        $pengeluaranStokDetail->modifiedby = auth('api')->user()->name;


        if (!$pengeluaranStokDetail->save()) {
            throw new \Exception("Error storing pengeluaran Stok Detail.");
        }

        return $pengeluaranStokDetail;
        
    }


    public function persediaan($gudang,$trado,$gandengan)
    {
        $kolom = null;
        $value = 0;
        if(!empty($gudang)) {
            $kolom = "gudang";
            $value = $gudang;
          } elseif(!empty($trado)) {
            $kolom = "trado";
            $value = $trado;
          } elseif(!empty($gandengan)) {
            $kolom = "gandengan";
            $value = $gandengan;
          }
          return [
            "column"=>$kolom,
            "value"=>$value
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
    public function checkTempat($stokId,$persediaan,$persediaanId)
    {
        $result = StokPersediaan::lockForUpdate()->where("stok_id", $stokId)->where("$persediaan", $persediaanId)->first();
        return (!$result) ? false :$result;
    }


    public function resetQtyPenerimaan($id)
    {
        $pengeluaranStokHeader = PengeluaranStokHeader::findOrFail($id);

        // $pengeluaranStokHeader = PengeluaranStokHeader::find($id);
        $spk = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();
        $kor = Parameter::where('grp', 'KOR MINUS STOK')->where('subgrp', 'KOR MINUS STOK')->first();
        $rtr = Parameter::where('grp', 'RETUR STOK')->where('subgrp', 'RETUR STOK')->first();
        $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
        $pengeluaranStokDetail = PengeluaranStokDetail::where('pengeluaranstokheader_id', $id)->get();
        
        foreach ($pengeluaranStokDetail as $detail) {
            /*Update  di stok persediaan*/
            $dari = true;
            if ($pengeluaranStokHeader->pengeluaranstok_id != ($kor->text || $rtr->text )) {
                $persediaan = $this->persediaan($pengeluaranStokHeader->gudang_id,$pengeluaranStokHeader->trado_id,$pengeluaranStokHeader->gandengan_id);
                $dari = $this->persediaanDari($detail->stok_id,$column,$value,$detail->qty);
            }
            
            if (!$dari) {
                throw new \Exception("qty tidak cukup");
            }
            if ($pengeluaranStokHeader->pengeluaranstok_id == $kor->text) {
                $persediaan = $this->persediaan($pengeluaranStokHeader->gudang_id,$pengeluaranStokHeader->trado_id,$pengeluaranStokHeader->gandengan_id);
                $ke = $this->persediaanKe($detail->stok_id,$persediaan['column'].'_id',$persediaan['value'],$detail->qty);
            }else {
                $ke = $this->persediaanKe($detail->stok_id,'gudang_id', $gudangkantor->text,$detail->qty);
            }

            
        }

        $pengeluaranStokDetailFifo = PengeluaranStokDetailFifo::where('nobukti', $pengeluaranStokHeader->nobukti)->get();
        foreach ($pengeluaranStokDetailFifo as $fifo) {
            $penerimaanStok = PenerimaanStokDetail::where('nobukti',$fifo->penerimaanstokheader_nobukti)->where('stok_id',$fifo->stok_id)->first();
            $penerimaanStok->qtykeluar -= $fifo->qty;
            $penerimaanStok->save();
        }

    }

}
