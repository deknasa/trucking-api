<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PenerimaanGiroDetail extends MyModel
{
    use HasFactory;

    protected $table = 'penerimaangirodetail';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function findAll($id)
    {
        $detail = DB::table('penerimaangirodetail')->from(DB::raw("penerimaangirodetail with (readuncommitted)"))
            ->select(
                'penerimaangirodetail.nowarkat',
                'penerimaangirodetail.tgljatuhtempo',
                'penerimaangirodetail.nominal',
                'penerimaangirodetail.coadebet',
                'penerimaangirodetail.keterangan',
                'penerimaangirodetail.bank_id',
                'bank.namabank as bank',
                'penerimaangirodetail.pelanggan_id',
                'pelanggan.namapelanggan as pelanggan',
                'penerimaangirodetail.invoice_nobukti',
                'penerimaangirodetail.bankpelanggan_id',
                'bankpelanggan.namabank as bankpelanggan',
                'penerimaangirodetail.pelunasanpiutang_nobukti',
                'penerimaangirodetail.jenisbiaya',
                DB::raw("(case when year(cast(penerimaangirodetail.bulanbeban as datetime))<='2000' then '' else format(penerimaangirodetail.bulanbeban,'yyyy-MM-dd') end) as bulanbeban"),
            )
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'penerimaangirodetail.bank_id', 'bank.id')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'penerimaangirodetail.pelanggan_id', 'pelanggan.id')
            ->leftJoin(DB::raw("bankpelanggan with (readuncommitted)"), 'penerimaangirodetail.bankpelanggan_id', 'bankpelanggan.id')
            ->where('penerimaangirodetail.penerimaangiro_id', $id)
            ->orderBy('penerimaangirodetail.id')
            ->get();

        return $detail;
    }
    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));

        if (isset(request()->forReport) && request()->forReport) {
            $query->select(
                'kreditcoa.keterangancoa as namacoakredit',
                'bank.namabank as bank_id',
                $this->table . '.tgljatuhtempo',
                $this->table . '.invoice_nobukti',
                $this->table . '.keterangan',
                $this->table . '.nominal',
            )
                ->leftJoin(DB::raw("bank with (readuncommitted)"), $this->table . '.bank_id', 'bank.id')
                ->leftjoin(DB::raw("akunpusat as kreditcoa with (readuncommitted)"), $this->table .'.coakredit', 'kreditcoa.coa');

            $query->where($this->table . '.penerimaangiro_id', '=', request()->penerimaangiro_id);
        } else {
            $query->select(
                $this->table . '.nobukti',
                $this->table . '.nowarkat',
                $this->table . '.tgljatuhtempo',
                'coadebet.keterangancoa as coadebet',
                'coakredit.keterangancoa as coakredit',
                'bank.namabank as bank_id',
                'bankpelanggan.namabank as bankpelanggan_id',
                $this->table . '.invoice_nobukti',
                $this->table . '.pelunasanpiutang_nobukti',
                $this->table . '.jenisbiaya',
                DB::raw("(case when year(cast(penerimaangirodetail.bulanbeban as datetime))<='2000' then '' else format(penerimaangirodetail.bulanbeban,'yyyy-MM-dd') end) as bulanbeban"),
                $this->table . '.keterangan',
                $this->table . '.nominal'
            )
                ->leftJoin(DB::raw("akunpusat as coadebet with (readuncommitted)"), $this->table . '.coadebet', 'coadebet.coa')
                ->leftJoin(DB::raw("akunpusat as coakredit with (readuncommitted)"), $this->table . '.coakredit', 'coakredit.coa')
                ->leftJoin(DB::raw("bank with (readuncommitted)"), $this->table . '.bank_id', 'bank.id')
                ->leftJoin(DB::raw("bankpelanggan with (readuncommitted)"), $this->table . '.bankpelanggan_id', 'bankpelanggan.id');

            $this->sort($query);
            $query->where($this->table . '.penerimaangiro_id', '=', request()->penerimaangiro_id);
            $this->filter($query);

            $this->totalNominal = $query->sum('nominal');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->paginate($query);
        }

        return $query->get();
    }

    public function getDetailForPenerimaan()
    {
        $query = DB::table('penerimaangirodetail')->from(DB::raw("penerimaangirodetail with (readuncommitted)"))
            ->select(
                'penerimaangirodetail.nobukti',
                'penerimaangirodetail.nowarkat',
                'penerimaangirodetail.tgljatuhtempo',
                'penerimaangirodetail.coadebet as coakredit',
                DB::raw("(case when penerimaangirodetail.bankpelanggan_id=0 then null else penerimaangirodetail.bankpelanggan_id end) as bankpelanggan_id"),
                'coadebet.keterangancoa as ketcoakredit',
                'bankpelanggan.namabank as bankpelanggan',
                'penerimaangirodetail.keterangan',
                'penerimaangirodetail.nominal'
            )
            ->leftJoin(DB::raw("akunpusat as coadebet with (readuncommitted)"), 'penerimaangirodetail.coadebet', 'coadebet.coa')
            ->leftJoin(DB::raw("bankpelanggan with (readuncommitted)"), 'penerimaangirodetail.bankpelanggan_id', 'bankpelanggan.id')
            ->where('penerimaangirodetail.penerimaangiro_id', '=', request()->penerimaangiro_id);

        return $query->get();
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'coadebet') {
            return $query->orderBy('coadebet.keterangancoa', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'coakredit') {
            return $query->orderBy('coakredit.keterangancoa', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'bank_id') {
            return $query->orderBy('bank.namabank', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'bankpelanggan_id') {
            return $query->orderBy('bankpelanggan.namabank', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'coadebet') {
                                $query = $query->where('coadebet.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coakredit') {
                                $query = $query->where('coakredit.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'bank_id') {
                                $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'bankpelanggan_id') {
                                $query = $query->where('bankpelanggan.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tgljatuhtempo') {
                                $query = $query->whereRaw("format($this->table.tgljatuhtempo,'dd-MM-yyyy') like '%$filters[data]%'");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->whereRaw("format($this->table.nominal,'#,#0.00') like '%$filters[data]%'");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'coadebet') {
                                $query = $query->orWhere('coadebet.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coakredit') {
                                $query = $query->orWhere('coakredit.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'bank_id') {
                                $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'bankpelanggan_id') {
                                $query = $query->orWhere('bankpelanggan.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tgljatuhtempo') {
                                $query = $query->orWhereRaw("format($this->table.tgljatuhtempo,'dd-MM-yyyy') like '%$filters[data]%'");
                            } else {
                                $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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

    public function processStore(PenerimaanGiroHeader $penerimaanGiroHeader, array $data): PenerimaanGiroDetail
    {
        $penerimaanGiroDetail = new PenerimaanGiroDetail();
        $penerimaanGiroDetail->penerimaangiro_id = $penerimaanGiroHeader->id;
        $penerimaanGiroDetail->nobukti = $penerimaanGiroHeader->nobukti;
        $penerimaanGiroDetail->nowarkat = $data['nowarkat'];
        $penerimaanGiroDetail->tgljatuhtempo = $data['tgljatuhtempo'];
        $penerimaanGiroDetail->nominal = $data['nominal'];
        $penerimaanGiroDetail->coadebet = $data['coadebet'];
        $penerimaanGiroDetail->coakredit = $data['coakredit'];
        $penerimaanGiroDetail->keterangan = $data['keterangan'];
        $penerimaanGiroDetail->bank_id = $data['bank_id'];
        $penerimaanGiroDetail->invoice_nobukti = $data['invoice_nobukti'];
        $penerimaanGiroDetail->bankpelanggan_id = $data['bankpelanggan_id'];
        $penerimaanGiroDetail->jenisbiaya = $data['jenisbiaya'];
        $penerimaanGiroDetail->pelunasanpiutang_nobukti = $data['pelunasanpiutang_nobukti'];
        $penerimaanGiroDetail->bulanbeban = $data['bulanbeban'];
        $penerimaanGiroDetail->modifiedby = auth('api')->user()->name;
        $penerimaanGiroDetail->info = html_entity_decode(request()->info);

        if (!$penerimaanGiroDetail->save()) {
            throw new \Exception("Error storing Penerimaan Giro Detail.");
        }

        return $penerimaanGiroDetail;
    }
}
