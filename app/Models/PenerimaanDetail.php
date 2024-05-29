<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PenerimaanDetail extends MyModel
{
    use HasFactory;

    protected $table = 'penerimaandetail';

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

        if (isset(request()->forReport) && request()->forReport) {
            $query->select(
                "header.nobukti",
                "header.tglbukti",
                "header.tgllunas",
                "bank.namabank as bank",
                "$this->table.nowarkat",
                "$this->table.tgljatuhtempo",
                "$this->table.nominal",
                "$this->table.keterangan as keterangan_detail",
                "bd.namabank as bank_detail",
                "$this->table.invoice_nobukti",
                "bpd.namabank as bankpelanggan_detail",
                DB::raw("(case when year(isnull($this->table.bulanbeban,'1900/1/1'))=1900 then null else $this->table.bulanbeban end) as bulanbeban"),
                "debet.keterangancoa as coadebet",
                "kredit.keterangancoa as coakredit",
            )
                ->leftJoin(DB::raw("penerimaanheader as header with (readuncommitted)"), "header.id", "$this->table.penerimaan_id")
                ->leftJoin(DB::raw("bank with (readuncommitted)"), "bank.id", "header.bank_id")
                ->leftJoin(DB::raw("bank as bd with (readuncommitted)"), "bd.id", "=", "$this->table.bank_id")
                ->leftJoin(DB::raw("akunpusat as debet with (readuncommitted)"), "debet.coa", "$this->table.coadebet")
                ->leftJoin(DB::raw("akunpusat as kredit with (readuncommitted)"), "kredit.coa", "$this->table.coakredit")
                ->leftJoin(DB::raw("bankpelanggan as bpd with (readuncommitted)"), "bpd.id", "=", "$this->table.bankpelanggan_id");
            $query->where($this->table . ".penerimaan_id", "=", request()->penerimaan_id)
                ->orderBy('penerimaandetail.id', 'asc');

            $penerimaanDetail = $query->get();
        } else {
            $query->select(
                "$this->table.nobukti",
                "$this->table.nowarkat",
                DB::raw("(case when year(isnull($this->table.tgljatuhtempo,'1900/1/1'))< '2000' then null else $this->table.tgljatuhtempo end) as tgljatuhtempo"),
                "$this->table.nominal",
                "$this->table.keterangan",
                "bank.namabank as bank_id",
                "$this->table.invoice_nobukti",
                "bankpelanggan.namabank as bankpelanggan_id", ///

                "$this->table.pelunasanpiutang_nobukti",
                "$this->table.penerimaangiro_nobukti",
                DB::raw("(case when year(isnull($this->table.bulanbeban,'1900/1/1'))=1900 then null else $this->table.bulanbeban end) as bulanbeban"),
                "a.keterangancoa as coadebet",
                "b.keterangancoa as coakredit",
                db::raw("cast((format(invoice.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderinvoiceheader"),
                db::raw("cast(cast(format((cast((format(invoice.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderinvoiceheader"),
                db::raw("cast((format(invoiceextra.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderinvoiceextraheader"),
                db::raw("cast(cast(format((cast((format(invoiceextra.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderinvoiceextraheader"),
                db::raw("cast((format(pelunasanpiutangheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpelunasanpiutangheader"),
                db::raw("cast(cast(format((cast((format(pelunasanpiutangheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpelunasanpiutangheader"),
                db::raw("cast((format(penerimaangiroheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpenerimaangiroheader"),
                db::raw("cast(cast(format((cast((format(penerimaangiroheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpenerimaangiroheader"),

            )
                ->leftJoin(DB::raw("invoiceheader as invoice with (readuncommitted)"), 'penerimaandetail.invoice_nobukti', '=', 'invoice.nobukti')
                ->leftJoin(DB::raw("invoiceextraheader as invoiceextra with (readuncommitted)"), 'penerimaandetail.invoice_nobukti', '=', 'invoiceextra.nobukti')
                ->leftJoin(DB::raw("pelunasanpiutangheader with (readuncommitted)"), 'penerimaandetail.pelunasanpiutang_nobukti', '=', 'pelunasanpiutangheader.nobukti')
                ->leftJoin(DB::raw("penerimaangiroheader with (readuncommitted)"), 'penerimaandetail.penerimaangiro_nobukti', '=', 'penerimaangiroheader.nobukti')
                ->leftJoin(DB::raw("bank with (readuncommitted)"), "bank.id", "=", "$this->table.bank_id")
                ->leftJoin(DB::raw("akunpusat as a with (readuncommitted)"), "a.coa", "=", "$this->table.coadebet")
                ->leftJoin(DB::raw("akunpusat as b with (readuncommitted)"), "b.coa", "=", "$this->table.coakredit")
                ->leftJoin(DB::raw("bankpelanggan with (readuncommitted)"), "bankpelanggan.id", "=", "$this->table.bankpelanggan_id");
            $query->where($this->table . ".penerimaan_id", "=", request()->penerimaan_id);
            $this->totalNominal = $query->sum('penerimaandetail.nominal');
            $this->filter($query);
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->sort($query);
            $this->paginate($query);
        }
        return $query->get();
    }

    public function findAll($id)
    {
        $detail = DB::table("penerimaandetail")
            ->select(
                'penerimaandetail.coakredit',
                'akunpusat.keterangancoa as ketcoakredit',
                'penerimaandetail.tgljatuhtempo',
                'penerimaandetail.nowarkat',
                DB::raw("(case when penerimaandetail.bankpelanggan_id=0 then null else penerimaandetail.bankpelanggan_id end) as bankpelanggan_id"),
                'bankpelanggan.namabank as bankpelanggan',
                'penerimaandetail.keterangan',
                'penerimaandetail.nominal',
                'penerimaandetail.invoice_nobukti',
                'penerimaandetail.pelunasanpiutang_nobukti',
                DB::raw("(case when year(cast(penerimaandetail.bulanbeban as datetime))='1900' then '' else format(penerimaandetail.bulanbeban,'yyyy-MM-dd') end) as bulanbeban"),
            )
            ->leftJoin(DB::raw("bankpelanggan with (readuncommitted)"), 'penerimaandetail.bankpelanggan_id', 'bankpelanggan.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'penerimaandetail.coakredit', 'akunpusat.coa')
            ->where('penerimaandetail.penerimaan_id', $id)
            ->orderBy('penerimaandetail.id')
            ->get();

        //  dd($detail);

        return $detail;
    }

    public function findAllpengembalian($id)
    {

        $coaKreditPengembalian = DB::table('parameter')->where('grp', 'PENGEMBALIAN KAS/BANK')->where('subgrp', 'PENGEMBALIAN KAS/BANK')->where('text', 'DEBET')->first();
        $memoKredit = json_decode($coaKreditPengembalian->memo, true);
        $debetpengembalian = $memoKredit['JURNAL'];
        $ketcoapengembalian = db::table('akunpusat')->from(db::raw("akunpusat a with (readuncommitted)"))
            ->select(
                'a.keterangancoa'
            )
            ->where('a.coa', $debetpengembalian)
            ->first()->keterangancoa ?? '';

        $detail = DB::table("penerimaandetail")
            ->select(
                db::raw("'" . $debetpengembalian . "' as coakredit"),
                db::raw("'" . $ketcoapengembalian . "' as ketcoakredit"),
                'penerimaandetail.tgljatuhtempo',
                'penerimaandetail.nowarkat',
                DB::raw("(case when penerimaandetail.bankpelanggan_id=0 then null else penerimaandetail.bankpelanggan_id end) as bankpelanggan_id"),
                'bankpelanggan.namabank as bankpelanggan',
                'penerimaandetail.keterangan',
                'penerimaandetail.nominal',
                'penerimaandetail.invoice_nobukti',
                'penerimaandetail.pelunasanpiutang_nobukti',
                DB::raw("(case when year(cast(penerimaandetail.bulanbeban as datetime))='1900' then '' else format(penerimaandetail.bulanbeban,'yyyy-MM-dd') end) as bulanbeban"),
            )
            ->leftJoin(DB::raw("bankpelanggan with (readuncommitted)"), 'penerimaandetail.bankpelanggan_id', 'bankpelanggan.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'penerimaandetail.coakredit', 'akunpusat.coa')
            ->where('penerimaandetail.penerimaan_id', $id)
            ->get();

        //  dd($detail);

        return $detail;
    }


    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {

                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'bank_id') {
                                $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'bankpelanggan_id') {
                                $query = $query->where('bankpelanggan.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coadebet') {
                                $query = $query->where('a.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coakredit') {
                                $query = $query->where('b.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'created_at') {
                                $query = $query->whereRaw("format($this->table.created_at,'dd-MM-yyyy HH:mm:ss') like '%$filters[data]%'");
                            } else if ($filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format($this->table.updated_at,'dd-MM-yyyy HH:mm:ss') like '%$filters[data]%'");
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
                            if ($filters['field'] == 'bank_id') {
                                $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'bankpelanggan_id') {
                                $query = $query->orWhere('bankpelanggan.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coadebet') {
                                $query = $query->orWhere('a.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coakredit') {
                                $query = $query->orWhere('b.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'created_at') {
                                $query = $query->orWhereRaw("format($this->table.created_at,'dd-MM-yyyy HH:mm:ss') like '%$filters[data]%'");
                            } else if ($filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format($this->table.updated_at,'dd-MM-yyyy HH:mm:ss') like '%$filters[data]%'");
                            } else if ($filters['field'] == 'tgljatuhtempo') {
                                $query = $query->orWhereRaw("format($this->table.tgljatuhtempo,'dd-MM-yyyy') like '%$filters[data]%'");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->orWhereRaw("format($this->table.nominal,'#,#0.00') like '%$filters[data]%'");
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
        if ($this->params['sortIndex'] == 'coadebet') {
            return $query->orderBy('a.keterangancoa', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'coakredit') {
            return $query->orderBy('b.keterangancoa', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }


    public function processStore(PenerimaanHeader $penerimaanHeader, array $data): PenerimaanDetail
    {
        $penerimaanDetail = new PenerimaanDetail;
        $penerimaanDetail->penerimaan_id = $data['penerimaan_id'];
        $penerimaanDetail->nobukti = $data['nobukti'];
        $penerimaanDetail->nowarkat = $data['nowarkat'] ?? '';
        $penerimaanDetail->tgljatuhtempo = $data['tgljatuhtempo'];
        $penerimaanDetail->nominal = $data['nominal'];
        $penerimaanDetail->coadebet = $data['coadebet'];
        $penerimaanDetail->coakredit = $data['coakredit'];
        $penerimaanDetail->keterangan = $data['keterangan'];
        $penerimaanDetail->bank_id = $data['bank_id'];
        $penerimaanDetail->invoice_nobukti = $data['invoice_nobukti'] ?? '';
        $penerimaanDetail->bankpelanggan_id = $data['bankpelanggan_id'] ?? 0;
        $penerimaanDetail->pelunasanpiutang_nobukti = $data['pelunasanpiutang_nobukti'] ?? '';
        $penerimaanDetail->penerimaangiro_nobukti = $data['penerimaangiro_nobukti'] ?? '';
        $penerimaanDetail->bulanbeban = $data['bulanbeban'];
        $penerimaanDetail->modifiedby = auth('api')->user()->name;
        $penerimaanDetail->info = html_entity_decode(request()->info);

        $penerimaanDetail->save();

        if (!$penerimaanDetail->save()) {
            throw new \Exception("Error storing Penerimaan Detail.");
        }

        return $penerimaanDetail;
    }
}
