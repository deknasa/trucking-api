<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KartuStok extends MyModel
{
    use HasFactory;

    protected $table = 'pengeluaranstokdetailfifo';

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
        $query = DB::table($this->table)->select(
            'pengeluaranstokdetailfifo.id',
            'stok.namastok as namabarang',
            'stok.namaterpusat as kodebarang',
            'kategori.keterangan as kategori_id',
            'pengeluaranstokdetailfifo.modifiedby'
        )
            ->leftJoin('stok', 'pengeluaranstokdetailfifo.stok_id', 'stok.id')
            ->leftJoin('kategori', 'stok.kategori_id', 'kategori.id');

        if (request()->filter && request()->datafilter && request()->stokdari_id && request()->stoksampai_id) {


            $parameter = Parameter::where('id', request()->filter)->first();
            if ($parameter->text == 'GUDANG') {
                $gudang_id = request()->datafilter;
                $query->where('pengeluaranstokdetailfifo.gudang_id', $gudang_id);
            }
            if ($parameter->text == 'TRADO') {
                $trado_id = request()->datafilter;
                $query->where('pengeluaranstokdetailfifo.trado_id', $trado_id);
            }
            if ($parameter->text == 'GANDENGAN') {
                $gandengan_id = request()->datafilter;
                $query->where('pengeluaranstokdetailfifo.gandengan_id', $gandengan_id);
            }


            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
            $this->filter($query);
            $this->paginate($query);

            $data = $query->get();
        } else {
            $data = [];
        }

        return $data;
    }

    public function getReport($stokdari_id, $stoksampai_id, $dari, $sampai, $filter, $datafilter)
    {
        // data coba coba
        $query = DB::table('pengeluaranstokdetailfifo')->select(
            'pengeluaranstokdetailfifo.id',
            'stok.namastok as namabarang',
            'stok.namaterpusat as kodebarang',
            'kategori.keterangan as kategori_id',
            'pengeluaranstokdetailfifo.qty as qtykeluar',
            'pengeluaranstokdetailfifo.penerimaanstok_qty as qtymasuk',
            'pengeluaranstokdetailfifo.modifiedby'
        )
            ->leftJoin('stok', 'pengeluaranstokdetailfifo.stok_id', 'stok.id')
            ->leftJoin('kategori', 'stok.kategori_id', 'kategori.id');

        $parameter = Parameter::where('id', $filter)->first();
        if ($parameter->text == 'GUDANG') {
            $gudang_id = $datafilter;
            $query->where('pengeluaranstokdetailfifo.gudang_id', $gudang_id);
        }
        if ($parameter->text == 'TRADO') {
            $trado_id = $datafilter;
            $query->where('pengeluaranstokdetailfifo.trado_id', $trado_id);
        }
        if ($parameter->text == 'GANDENGAN') {
            $gandengan_id = $datafilter;
            $query->where('pengeluaranstokdetailfifo.gandengan_id', $gandengan_id);
        }
        $data = $query->get();
        return $data;
    }

    public function filter($query, $relationFields = [])
    {

        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'namabarang') {
                            $query = $query->where('stok.namastok', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'kodebarang') {
                            $query = $query->where('stok.namaterpusat', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'kategori_id') {
                            $query = $query->where('kategori.keterangan', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {

                            if ($filters['field'] == 'namabarang') {
                                $query = $query->orWhere('stok.namastok', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'kodebarang') {
                                $query = $query->orWhere('stok.namaterpusat', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'kategori') {
                                $query = $query->orWhere('kategori.keterangan', 'LIKE', "%$filters[data]%");
                            } else {
                                $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
