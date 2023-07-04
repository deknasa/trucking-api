<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PelunasanHutangDetail extends MyModel
{
    use HasFactory;

    protected $table = 'PelunasanHutangdetail';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    public function getAll($id)
    {

        $query = DB::table('PelunasanHutangdetail')->from(DB::raw("PelunasanHutangdetail with (readuncommitted)"))
            ->select(
                'PelunasanHutangdetail.nominal',
                'PelunasanHutangdetail.hutang_nobukti',
                'PelunasanHutangdetail.cicilan',
                'PelunasanHutangdetail.potongan',
                'PelunasanHutangdetail.keterangan',

            )

            ->where('PelunasanHutang_id', '=', $id);

        $data = $query->get();

        return $data;
    }
    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));

        if (isset(request()->forReport) && request()->forReport) {
            $query->select(
                'header.nobukti',
                'header.tglbukti',
                'header.keterangan as keteranganheader',
                'header.pengeluaran_nobukti',
                'header.coa',
                'bank.namabank as bank',
                'supplier.namasupplier as supplier',
                'pelanggan.namapelanggan as pelanggan',
                $this->table . '.nominal',
                $this->table . '.keterangan',
                'header.tglcair',
                $this->table . '.potongan',
                $this->table . '.hutang_nobukti',
                'alatbayar.namaalatbayar as alatbayar_id',

            )
                ->leftJoin(DB::raw("PelunasanHutangheader as header with (readuncommitted)"), 'header.id', $this->table . '.PelunasanHutang_id')
                ->leftJoin(DB::raw("bank with (readuncommitted)"), 'header.bank_id', 'bank.id')
                ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'header.supplier_id', 'supplier.id')
                ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'header.pelanggan_id', 'pelanggan.id')
                ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'header.alatbayar_id', 'alatbayar.id');

            $query->where($this->table . '.PelunasanHutang_id', '=', request()->PelunasanHutang_id);
        } else {
            $query->select(
                $this->table . '.nobukti',
                $this->table . '.nominal',
                $this->table . '.keterangan',
                $this->table . '.potongan',
                $this->table . '.hutang_nobukti'
            );

            $this->sort($query);
            $query->where($this->table . '.PelunasanHutang_id', '=', request()->PelunasanHutang_id);
            $this->filter($query);

            $this->totalNominal = $query->sum('nominal');
            $this->totalPotongan = $query->sum('potongan');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->paginate($query);
        }

        return $query->get();
    }

    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {

                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {

                            $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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


    public function processStore(PelunasanHutangHeader $PelunasanHutangHeader, array $data): PelunasanHutangDetail
    {
        $PelunasanHutangDetail = new PelunasanHutangDetail();
        $PelunasanHutangDetail->PelunasanHutang_id = $data['PelunasanHutang_id'];
        $PelunasanHutangDetail->nobukti = $data['nobukti'];
        $PelunasanHutangDetail->nominal = $data['nominal'];
        $PelunasanHutangDetail->hutang_nobukti = $data['hutang_nobukti'];
        $PelunasanHutangDetail->cicilan = $data['cicilan'];
        $PelunasanHutangDetail->potongan = $data['potongan'];
        $PelunasanHutangDetail->keterangan = $data['keterangan'];
        $PelunasanHutangDetail->modifiedby = $PelunasanHutangHeader->modifiedby;


        if (!$PelunasanHutangDetail->save()) {
            throw new \Exception("Error storing Pengeluaran Detail.");
        }

        return $PelunasanHutangDetail;
    }
}