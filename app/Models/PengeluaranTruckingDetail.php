<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class PengeluaranTruckingDetail extends MyModel
{
    use HasFactory;

    protected $table = 'pengeluarantruckingdetail';

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

        $getPengeluaranId = DB::table("pengeluarantruckingheader")->from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
            ->where('id', request()->pengeluarantruckingheader_id)->first();
        $pengeluaranId = $getPengeluaranId->pengeluarantrucking_id;
        $tableStok = 'stok';
        $kolomStok = 'stok_id';

        //klaim



        if (isset(request()->forReport) && request()->forReport) {
            // dd($query->tosql());
            if ($pengeluaranId == 9) {
                $query->select(
                    $this->table . '.suratpengantar_nobukti',
                    'trado.kodetrado as trado_id',
                    $this->table . '.nominal',
                    $this->table . '.nominaltagih',
                    $this->table . '.keterangan',
                    'jenisorder.keterangan as jenisorderan',
                    'parameter.text as statustitipanemkl'
                );
            } else {
                // dd('test');


                if ($pengeluaranId == 7) {
                    $query->select(
                        'supir.namasupir as supir_id',
                        'karyawan.namakaryawan as karyawan_id',
                        db::raw("trim(isnull(kelompok.kodekelompok,''))+' - '+trim(stok.namastok) as stok_id"),
                        $this->table . '.pengeluaranstok_nobukti',
                        $this->table . '.qty',
                        $this->table . '.harga',
                        $this->table . '.penerimaantruckingheader_nobukti',
                        $this->table . '.nominal',
                        $this->table . '.orderantrucking_nobukti',
                        $this->table . '.suratpengantar_nobukti',
                        $this->table . '.keterangan',
                        $this->table . '.invoice_nobukti',
                    );
                } else {
                    $query->select(
                        'supir.namasupir as supir_id',
                        'karyawan.namakaryawan as karyawan_id',
                        'stok.namastok as stok_id',
                        $this->table . '.pengeluaranstok_nobukti',
                        $this->table . '.qty',
                        $this->table . '.harga',
                        $this->table . '.penerimaantruckingheader_nobukti',
                        $this->table . '.nominal',
                        $this->table . '.orderantrucking_nobukti',
                        $this->table . '.suratpengantar_nobukti',
                        $this->table . '.keterangan',
                        $this->table . '.invoice_nobukti',
                        db::raw("(case when (row_number() Over( Order By " . $this->table . ".id )) %2 =0 then '' else (row_number() Over( Order By " . $this->table . ".id )) end) as urutganjil "),
                        db::raw("(case when (row_number() Over( Order By " . $this->table . ".id )) %2 =0 then (row_number() Over( Order By " . $this->table . ".id )) else  '' end) as urutgenap "),
                    );
                }
            }
            if ($pengeluaranId == 10 || $pengeluaranId == 11 || $pengeluaranId == 12 || $pengeluaranId == 13 || $pengeluaranId == 14 || $pengeluaranId == 15) {
                $query->where('nominal', '!=', 0);
            }

            if ($pengeluaranId == 9) {
                $query->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingheader.id', $this->table . '.pengeluarantruckingheader_id')
                    ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'pengeluarantruckingheader.jenisorder_id', 'jenisorder.id')
                    ->leftJoin(DB::raw("trado with (readuncommitted)"), $this->table . '.trado_id', 'trado.id')
                    ->leftJoin(DB::raw("parameter with (readuncommitted)"), $this->table . '.statustitipanemkl', 'parameter.id');
            } else {
                // dd('test1');
                if ($pengeluaranId == 7) {
                    $query->leftJoin(DB::raw("supir with (readuncommitted)"), $this->table . '.supir_id', 'supir.id')
                    ->leftJoin(DB::raw("$tableStok as stok with (readuncommitted)"), $this->table . '.' . $kolomStok, 'stok.id')
                    ->leftJoin(DB::raw("karyawan with (readuncommitted)"), $this->table . '.karyawan_id', 'karyawan.id')
                    ->leftJoin(DB::raw("kelompok with (readuncommitted)"), 'stok.kelompok_id', 'kelompok.id');

                } else {
                    $query->leftJoin(DB::raw("supir with (readuncommitted)"), $this->table . '.supir_id', 'supir.id')
                    ->leftJoin(DB::raw("$tableStok as stok with (readuncommitted)"), $this->table . '.' . $kolomStok, 'stok.id')
                    ->leftJoin(DB::raw("karyawan with (readuncommitted)"), $this->table . '.karyawan_id', 'karyawan.id');

                }
            }
            $query->where($this->table . '.pengeluarantruckingheader_id', '=', request()->pengeluarantruckingheader_id);
            // dd($query->get());
        } else {
            if ($pengeluaranId == 7) {
                $stokKlaim = $query->select($this->table . '.stoktnl_id')
                    ->where($this->table . '.pengeluarantruckingheader_id', '=',  request()->pengeluarantruckingheader_id)->first();
                if ($getPengeluaranId->statuscabang == 516) {
                    $tableStok = (new Stok())->showTNLForKlaim($stokKlaim->stoktnl_id);
                    $kolomStok = 'stoktnl_id';
                }
            }

            $query->select(
                $this->table . '.nobukti',
                $this->table . '.nominal',
                $this->table . '.keterangan',
                $this->table . '.invoice_nobukti',
                $this->table . '.pengeluaranstok_nobukti',
                $this->table . '.stok_id',
                'stok.namastok as stok',
                $this->table . '.qty',
                $this->table . '.harga',
                $this->table . '.total',
                $this->table . '.nominaltambahan',
                $this->table . '.keterangantambahan',
                // 'pengeluaranstokheader.id as pengeluaranstokheader_id',
                $this->table . '.orderantrucking_nobukti',
                DB::raw("(case when pengeluarantruckingdetail.nominaltagih IS NULL then 0 else pengeluarantruckingdetail.nominaltagih end) as nominaltagih"),
                $this->table . '.suratpengantar_nobukti',
                $this->table . '.pengeluaranstok_nobukti',
                $this->table . '.penerimaanstok_nobukti',
                DB::raw("container.keterangan as container"),
                'supir.namasupir as supir_id',
                'karyawan.namakaryawan as karyawan_id',
                'statustitipanemkl.text as statustitipanemkl',
                $this->table . '.penerimaantruckingheader_nobukti',
                db::raw("cast((format(penerimaantruckingheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpenerimaantruckingheader"),
                db::raw("cast(cast(format((cast((format(penerimaantruckingheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpenerimaantruckingheader"),
                db::raw("cast((format(ot.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderorderantrucking"),
                db::raw("cast(cast(format((cast((format(ot.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderorderantrucking"),
                db::raw("cast((format(pengeluaranstokheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpengeluaranstokheader"),
                db::raw("cast(cast(format((cast((format(pengeluaranstokheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpengeluaranstokheader"),
                db::raw("cast((format(invoice.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderinvoiceheader"),
                db::raw("cast(cast(format((cast((format(invoice.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderinvoiceheader"),
                db::raw("cast((format(invoiceextra.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderinvoiceextraheader"),
                db::raw("cast(cast(format((cast((format(invoiceextra.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderinvoiceextraheader"),
                db::raw("cast((format(suratpengantar.tglbukti,'yyyy/MM')+'/1') as date) as tgldarisuratpengantar"),
                db::raw("cast(cast(format((cast((format(suratpengantar.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaisuratpengantar"),
                db::raw("cast((format(penerimaanstokheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpenerimaanstokheader"),
                db::raw("cast(cast(format((cast((format(penerimaanstokheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpenerimaanstokheader"),

            )
                ->leftJoin(DB::raw("invoiceheader as invoice with (readuncommitted)"), 'pengeluarantruckingdetail.invoice_nobukti', '=', 'invoice.nobukti')
                ->leftJoin(DB::raw("invoiceextraheader as invoiceextra with (readuncommitted)"), 'pengeluarantruckingdetail.invoice_nobukti', '=', 'invoiceextra.nobukti')
                ->leftJoin(DB::raw("penerimaantruckingheader with (readuncommitted)"), 'pengeluarantruckingdetail.penerimaantruckingheader_nobukti', '=', 'penerimaantruckingheader.nobukti')
                ->leftJoin(DB::raw("pengeluaranstokheader with (readuncommitted)"), 'pengeluarantruckingdetail.pengeluaranstok_nobukti', '=', 'pengeluaranstokheader.nobukti')
                ->leftJoin(DB::raw("karyawan with (readuncommitted)"), $this->table . '.karyawan_id', 'karyawan.id')
                ->leftJoin(DB::raw("supir with (readuncommitted)"), $this->table . '.supir_id', 'supir.id')
                ->leftJoin(DB::raw("orderantrucking as ot with (readuncommitted)"), 'pengeluarantruckingdetail.orderantrucking_nobukti', 'ot.nobukti')
                ->leftJoin(DB::raw("$tableStok as stok with (readuncommitted)"), "pengeluarantruckingdetail.$kolomStok", 'stok.id')
                ->leftJoin(DB::raw("parameter as statustitipanemkl with (readuncommitted)"), 'pengeluarantruckingdetail.statustitipanemkl', 'statustitipanemkl.id')
                ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), 'pengeluarantruckingdetail.suratpengantar_nobukti', '=', 'suratpengantar.nobukti')
                ->leftJoin(DB::raw("penerimaanstokheader with (readuncommitted)"), 'pengeluarantruckingdetail.pengeluaranstok_nobukti', '=', 'penerimaanstokheader.nobukti')
                ->leftJoin(DB::raw("container with (readuncommitted)"), 'ot.container_id', 'container.id');


            $query->where($this->table . '.pengeluarantruckingheader_id', '=', request()->pengeluarantruckingheader_id);

            $this->sort($query);
            $this->filter($query);

            $this->totalNominal = $query->sum($this->table . '.nominal');
            $this->totalNominalTagih = $query->sum($this->table . '.nominaltagih');
            $this->filter($query);
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
            $this->paginate($query);
        }

        return $query->get();
    }

    public function getAll($id, $kodepengeluaran)
    {
        if ($kodepengeluaran == 'BBT') {
            $query = DB::table('pengeluarantruckingdetail')->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
                ->select(
                    'pengeluarantruckingdetail.pengeluarantruckingheader_id',
                    'pengeluarantruckingdetail.nominal',
                    'pengeluarantruckingdetail.keterangan',
                    'pengeluarantruckingdetail.suratpengantar_nobukti',
                    'trado.kodetrado as trado_id',
                    'container.kodecontainer as container_id',
                    'pelanggan.kodepelanggan as pelanggan_id',
                    'pengeluarantruckingdetail.nominaltagih',
                    'pengeluarantruckingdetail.statustitipanemkl',


                )
                ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), 'suratpengantar.nobukti', 'pengeluarantruckingdetail.suratpengantar_nobukti')
                ->leftJoin(DB::raw("container with (readuncommitted)"), 'suratpengantar.container_id', 'container.id')
                ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'suratpengantar.pelanggan_id', 'pelanggan.id')
                ->leftJoin(DB::raw("trado with (readuncommitted)"), 'pengeluarantruckingdetail.trado_id', 'trado.id')
                ->where('pengeluarantruckingdetail.pengeluarantruckingheader_id', '=', $id);
        } else if ($kodepengeluaran == 'KLAIM') {
            $cek  = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
                ->select('pengeluarantrucking_id', 'statuscabang', 'tglbukti')
                ->where('id', $id)->first();
            if ($cek->statuscabang == 516) {
                $dari = date('01-m-Y', strtotime($cek->tglbukti));
                $sampai = date('t-m-Y', strtotime($cek->tglbukti));
                $tableStok = (new Stok())->getTNLForKlaim();
                $tablePengeluaranStok = (new PengeluaranStokHeader())->getTNLForKlaim($dari, $sampai);
                $tablePenerimaanStok = (new PenerimaanStokHeader())->getTNLForKlaim($dari, $sampai);

                $query = DB::table('pengeluarantruckingdetail')->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
                    ->select(
                        'pengeluarantruckingdetail.pengeluarantruckingheader_id',
                        'pengeluarantruckingdetail.nominal',
                        'pengeluarantruckingdetail.keterangan',
                        'pengeluarantruckingdetail.penerimaantruckingheader_nobukti',
                        'pengeluarantruckingdetail.pengeluaranstoktnl_nobukti as pengeluaranstok_nobukti',
                        'pengeluarantruckingdetail.penerimaanstoktnl_nobukti as penerimaanstok_nobukti',
                        'pengeluarantruckingdetail.stoktnl_id as stok_id',
                        'stok.namastok as stok',
                        'pengeluarantruckingdetail.qty',
                        'pengeluarantruckingdetail.harga',
                        'pengeluarantruckingdetail.total',
                        DB::raw("(case when pengeluaranstokheader.id IS NULL then 0 else pengeluaranstokheader.id end) as pengeluaranstokheader_id"),
                        DB::raw("(case when penerimaanstokheader.id IS NULL then 0 else penerimaanstokheader.id end) as penerimaanstokheader_id"),
                        DB::raw("pengeluarantruckingdetail.nominal as nominal_detail"),
                        DB::raw("pengeluarantruckingdetail.nominaltambahan"),
                        DB::raw("
                    (SELECT MAX(qty)
                    FROM pengeluaranstokdetail
                    WHERE stok_id = [pengeluarantruckingdetail].[stoktnl_id]
                    ) AS maxqty
                "),
                        'pengeluarantruckingdetail.nominaltagih',
                        'pengeluarantruckingdetail.statustitipanemkl',
                        'pengeluarantruckingdetail.keterangantambahan',


                    )
                    ->leftJoin(DB::raw("$tableStok as stok with (readuncommitted)"), 'pengeluarantruckingdetail.stoktnl_id', 'stok.id')
                    ->leftJoin(DB::raw("$tablePengeluaranStok as pengeluaranstokheader with (readuncommitted)"), 'pengeluaranstokheader.nobukti', 'pengeluarantruckingdetail.pengeluaranstoktnl_nobukti')
                    ->leftJoin(DB::raw("$tablePenerimaanStok as penerimaanstokheader with (readuncommitted)"), 'penerimaanstokheader.nobukti', 'pengeluarantruckingdetail.penerimaanstoktnl_nobukti')
                    ->where('pengeluarantruckingdetail.pengeluarantruckingheader_id', '=', $id);
            } else {
                $query = DB::table('pengeluarantruckingdetail')->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
                    ->select(
                        'pengeluarantruckingdetail.pengeluarantruckingheader_id',
                        'pengeluarantruckingdetail.nominal',
                        'pengeluarantruckingdetail.keterangan',
                        'pengeluarantruckingdetail.penerimaantruckingheader_nobukti',
                        'pengeluarantruckingdetail.pengeluaranstok_nobukti',
                        'pengeluarantruckingdetail.penerimaanstok_nobukti',
                        'pengeluarantruckingdetail.stok_id',
                        'stok.namastok as stok',
                        'pengeluarantruckingdetail.qty',
                        'pengeluarantruckingdetail.harga',
                        'pengeluarantruckingdetail.total',
                        DB::raw("(case when pengeluaranstokheader.id IS NULL then 0 else pengeluaranstokheader.id end) as pengeluaranstokheader_id"),
                        DB::raw("(case when penerimaanstokheader.id IS NULL then 0 else penerimaanstokheader.id end) as penerimaanstokheader_id"),
                        DB::raw("pengeluarantruckingdetail.nominal as nominal_detail"),
                        DB::raw("pengeluarantruckingdetail.nominaltambahan"),
                        DB::raw("
                    (SELECT MAX(qty)
                    FROM pengeluaranstokdetail
                    WHERE stok_id = [pengeluarantruckingdetail].[stok_id]
                    ) AS maxqty
                "),


                        'pengeluarantruckingdetail.nominaltagih',
                        'pengeluarantruckingdetail.statustitipanemkl',
                        'pengeluarantruckingdetail.keterangantambahan',


                    )
                    ->leftJoin(DB::raw("stok with (readuncommitted)"), 'pengeluarantruckingdetail.stok_id', 'stok.id')
                    ->leftJoin(DB::raw("pengeluaranstokheader with (readuncommitted)"), 'pengeluaranstokheader.nobukti', 'pengeluarantruckingdetail.pengeluaranstok_nobukti')
                    ->leftJoin(DB::raw("penerimaanstokheader with (readuncommitted)"), 'penerimaanstokheader.nobukti', 'pengeluarantruckingdetail.penerimaanstok_nobukti')
                    ->where('pengeluarantruckingdetail.pengeluarantruckingheader_id', '=', $id);
            }
        } else {


            $query = DB::table('pengeluarantruckingdetail')->from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
                ->select(
                    'pengeluarantruckingdetail.pengeluarantruckingheader_id',
                    'pengeluarantruckingdetail.nominal',
                    'pengeluarantruckingdetail.keterangan',
                    'pengeluarantruckingdetail.penerimaantruckingheader_nobukti',
                    'pengeluarantruckingdetail.pengeluaranstok_nobukti',
                    'pengeluarantruckingdetail.penerimaanstok_nobukti',
                    'pengeluarantruckingdetail.stok_id',
                    'stok.namastok as stok',
                    'pengeluarantruckingdetail.qty',
                    'pengeluarantruckingdetail.harga',
                    'pengeluarantruckingdetail.total',
                    'pengeluaranstokheader.id as pengeluaranstokheader_id',
                    DB::raw("pengeluarantruckingdetail.id as id_detail"),
                    DB::raw("pengeluarantruckingdetail.invoice_nobukti as noinvoice_detail"),
                    DB::raw("pengeluarantruckingdetail.orderantrucking_nobukti as nojobtrucking_detail"),
                    DB::raw("container.keterangan as container_detail"),
                    DB::raw("pengeluarantruckingdetail.nominal as nominal_detail"),
                    DB::raw("pengeluarantruckingdetail.nominaltambahan"),
                    DB::raw("
                    (SELECT MAX(qty)
                    FROM pengeluaranstokdetail
                    WHERE stok_id = [pengeluarantruckingdetail].[stok_id]
                    ) AS maxqty
                "),

                    'supir.namasupir as supir',
                    'supir.id as supir_id',
                    'karyawan.namakaryawan as karyawan',
                    'karyawan.id as karyawan_id',
                    'pengeluarantruckingdetail.suratpengantar_nobukti',
                    'trado.kodetrado as trado_id',
                    'pengeluarantruckingdetail.nominaltagih',
                    'pengeluarantruckingdetail.statustitipanemkl',
                    'pengeluarantruckingdetail.keterangantambahan',


                )
                ->leftJoin(DB::raw("orderantrucking as ot with (readuncommitted)"), 'pengeluarantruckingdetail.orderantrucking_nobukti', 'ot.nobukti')
                ->leftJoin(DB::raw("container with (readuncommitted)"), 'ot.container_id', 'container.id')
                ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pengeluarantruckingdetail.supir_id', 'supir.id')
                ->leftJoin(DB::raw("karyawan with (readuncommitted)"), 'pengeluarantruckingdetail.karyawan_id', 'karyawan.id')
                ->leftJoin(DB::raw("stok with (readuncommitted)"), 'pengeluarantruckingdetail.stok_id', 'stok.id')
                ->leftJoin(DB::raw("trado with (readuncommitted)"), 'pengeluarantruckingdetail.trado_id', 'trado.id')
                ->leftJoin(DB::raw("pengeluaranstokheader with (readuncommitted)"), 'pengeluaranstokheader.nobukti', 'pengeluarantruckingdetail.pengeluaranstok_nobukti')
                ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), 'suratpengantar.nobukti', 'pengeluarantruckingdetail.suratpengantar_nobukti')
                // ->leftJoin('suratpengantar', function ($join) {
                //     $join->on('pengeluarantruckingdetail.suratpengantar_nobukti', '=', 'suratpengantar.nobukti')
                //         ->whereRaw('pengeluarantruckingdetail.suratpengantar_nobukti = suratpengantar.nobukti');
                // })
                // ->leftJoin('saldosuratpengantar', 'pengeluaran.suratpengantar_nobukti', '=', 'saldosuratpengantar.nobukti')
                ->where('pengeluarantruckingdetail.pengeluarantruckingheader_id', '=', $id);
        }
        $data = $query->get();

        return $data;
    }
    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'supir_id') {
            return $query->orderBy('supir.namasupir', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'karyawan_id') {
            return $query->orderBy('karyawan.namakaryawan', $this->params['sortOrder']);
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
                            if ($filters['field'] == 'supir_id') {
                                $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'karyawan_id') {
                                $query = $query->where('karyawan.namakaryawan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'stok') {
                                $query = $query->where('stok.namastok', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'statustitipanemkl') {
                                $query = $query->where('statustitipanemkl.text', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->whereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'supir_id') {
                                $query = $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'karyawan_id') {
                                $query = $query->orWhere('karyawan.namakaryawan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'stok') {
                                $query = $query->orWhere('stok.namastok', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'statustitipanemkl') {
                                $query = $query->orWhere('statustitipanemkl.text', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->orWhereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
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
    public function processStore(PengeluaranTruckingHeader $pengeluaranTruckingHeader, array $data): PengeluaranTruckingDetail
    {
        $suratpengantar_nobukti = null;
        $trado_id = null;
        $container_id = null;
        $pelanggan_id = null;
        $nominaltagih = null;
        $jenisorder = null;

        if ($pengeluaranTruckingHeader->pengeluarantrucking_id == 9) {
            if ($data['suratpengantar_nobukti']) {
                $suratpengantar = SuratPengantar::where('nobukti', $data['suratpengantar_nobukti'])->first();
                if (!$suratpengantar) {
                    $suratpengantar = DB::table('saldosuratpengantar')->from(
                        DB::raw("saldosuratpengantar suratpengantar with (readuncommitted)")
                    )->where('nobukti', $data['suratpengantar_nobukti'])->first();
                }
                $suratpengantar_nobukti = $suratpengantar->nobukti;
                $trado_id = $suratpengantar->trado_id;
                $container_id = $suratpengantar->container_id;
                $pelanggan_id = $suratpengantar->pelanggan_id;
                $nominaltagih = $data['nominaltagih'];
                $jenisorder = $suratpengantar->jenisorder_id;
            }
        }
        if ($pengeluaranTruckingHeader->pengeluarantrucking_id == 7) {
            $nominaltagih = $data['nominaltagih'];
        }
        $pengeluaranTruckingDetail = new PengeluaranTruckingDetail();
        $pengeluaranTruckingDetail->pengeluarantruckingheader_id = $data['pengeluarantruckingheader_id'];
        $pengeluaranTruckingDetail->nobukti = $data['nobukti'];
        $pengeluaranTruckingDetail->supir_id = $data['supir_id'];
        $pengeluaranTruckingDetail->karyawan_id = $data['karyawan_id'];
        $pengeluaranTruckingDetail->penerimaantruckingheader_nobukti = $data['penerimaantruckingheader_nobukti'] ?? "";
        $pengeluaranTruckingDetail->stok_id = $data['stok_id'] ?? 0;
        $pengeluaranTruckingDetail->pengeluaranstok_nobukti = $data['pengeluaranstok_nobukti'] ?? "";
        $pengeluaranTruckingDetail->penerimaanstok_nobukti = $data['penerimaanstok_nobukti'] ?? "";
        $pengeluaranTruckingDetail->stoktnl_id = $data['stoktnl_id'] ?? 0;
        $pengeluaranTruckingDetail->pengeluaranstoktnl_nobukti = $data['pengeluaranstoktnl_nobukti'] ?? "";
        $pengeluaranTruckingDetail->penerimaanstoktnl_nobukti = $data['penerimaanstoktnl_nobukti'] ?? "";
        $pengeluaranTruckingDetail->qty = $data['qty'] ?? 0;
        $pengeluaranTruckingDetail->harga = $data['harga'] ?? 0;
        $pengeluaranTruckingDetail->total = $data['total'] ?? 0;
        $pengeluaranTruckingDetail->nominaltambahan = $data['nominaltambahan'] ?? 0;
        $pengeluaranTruckingDetail->keterangantambahan = $data['keterangantambahan'] ?? "";
        $pengeluaranTruckingDetail->trado_id = $data['trado_id'] ?? 0;
        $pengeluaranTruckingDetail->keterangan = $data['keterangan'];
        $pengeluaranTruckingDetail->invoice_nobukti = $data['invoice_nobukti'];
        $pengeluaranTruckingDetail->orderantrucking_nobukti = $data['orderantrucking_nobukti'];
        $pengeluaranTruckingDetail->nominal = $data['nominal'];
        $pengeluaranTruckingDetail->statustitipanemkl = $data['statustitipanemkl'];
        $pengeluaranTruckingDetail->suratpengantar_nobukti = $suratpengantar_nobukti;
        $pengeluaranTruckingDetail->trado_id = $trado_id;
        // $pengeluaranTruckingDetail->container_id = $container_id;
        // $pengeluaranTruckingDetail->pelanggan_id = $pelanggan_id;
        $pengeluaranTruckingDetail->nominaltagih = $nominaltagih;
        // $pengeluaranTruckingDetail->jenisorder = $jenisorder;




        // 'suratpengantar_nobukti' => $data['suratpengantar_nobukti'] ?? null,
        // 'trado_id' => $data['trado_id'] ?? null,
        // 'container_id' => $data['container_id'] ?? null,
        // 'pelanggan_id' => $data['pelanggan_id'] ?? null,
        // 'nominaltagih' => $data['nominaltagih'] ?? null,
        // 'jenisorder' => $data['jenisorder'] ?? null,


        $pengeluaranTruckingDetail->modifiedby = auth('api')->user()->name;
        $pengeluaranTruckingDetail->info = html_entity_decode(request()->info);

        if (!$pengeluaranTruckingDetail->save()) {
            throw new \Exception("Error storing pengeluaran Trucking Detail.");
        }
        return $pengeluaranTruckingDetail;
    }

    public function cekTitipanEMKL($statusTitipan, $suratPengantar, $id)
    {

        $querysp = db::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
            ->select(
                'a.id'
            )
            ->where('nobukti', $suratPengantar)
            ->first();
        if (isset($querysp)) {
            $quesp = DB::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
                ->select(
                    'a.id',
                    db::raw("(case when isnull(a.tglbatasbiayatitipanemkl,'1900/1/1')>=getdate() then 1 else 0 end) as statusbuka")
                )
                ->where('a.nobukti', $suratPengantar)
                ->whereRaw("(isnull(statusapprovalbiayatitipanemkl,0)=3)")
                ->first();
            if (isset($quesp)) {
                if ($quesp->statusbuka == 1) {
                    $cekTitipan = null;
                } else {
                    $cekTitipan = DB::table("pengeluarantruckingdetail")->from(DB::raw("pengeluarantruckingdetail as a with (readuncommitted)"))
                        ->select(
                            'a.id',
                            'a.suratpengantar_nobukti',
                            'a.statustitipanemkl',
                            db::raw("(case when isnull(b.tglbatasbiayatitipanemkl,'1900/1/1')<=getdate() then 1 else 0 end) as statusbuka")
                        )
                        ->leftJoin(DB::raw("suratpengantar as b with (readuncommitted)"), 'a.suratpengantar_nobukti', 'b.nobukti')
                        ->whereRaw("(isnull(statusapprovalbiayatitipanemkl,0)<>4)")
                        ->where('a.suratpengantar_nobukti', $suratPengantar)
                        ->where('a.statustitipanemkl', $statusTitipan);
                }
            } else {
                $cekTitipan = DB::table("pengeluarantruckingdetail")->from(DB::raw("pengeluarantruckingdetail as a with (readuncommitted)"))
                    ->select(
                        'a.id',
                        'a.suratpengantar_nobukti',
                        'a.statustitipanemkl',
                        db::raw("(case when isnull(b.tglbatasbiayatitipanemkl,'1900/1/1')<=getdate() then 1 else 0 end) as statusbuka")
                    )
                    ->leftJoin(DB::raw("suratpengantar as b with (readuncommitted)"), 'a.suratpengantar_nobukti', 'b.nobukti')
                    ->whereRaw("(isnull(statusapprovalbiayatitipanemkl,0)<>3)")
                    ->where('a.suratpengantar_nobukti', $suratPengantar)
                    ->where('a.statustitipanemkl', $statusTitipan);
            }
        } else {
            $quesp = DB::table("saldosuratpengantar")->from(db::raw("saldosuratpengantar a with (readuncommitted)"))
                ->select(
                    'a.id',
                    db::raw("(case when isnull(a.tglbatasbiayatitipanemkl,'1900/1/1')>=getdate() then 1 else 0 end) as statusbuka"),
                    'a.tglbatasbiayatitipanemkl'
                )
                ->where('a.nobukti', $suratPengantar)
                ->whereRaw("(isnull(statusapprovalbiayatitipanemkl,0)=3)")
                ->first();
            if (isset($quesp)) {
                // dd($quesp->statusbuka);
                if ($quesp->statusbuka == 1) {
                    $cekTitipan = null;
                    $id = null;
                } else {
                    $cekTitipan = DB::table("pengeluarantruckingdetail")->from(DB::raw("pengeluarantruckingdetail as a with (readuncommitted)"))
                        ->select(
                            'a.id',
                            'a.suratpengantar_nobukti',
                            'a.statustitipanemkl',
                            db::raw("(case when isnull(b.tglbatasbiayatitipanemkl,'1900/1/1')<=getdate() then 1 else 0 end) as statusbuka")
                        )
                        ->leftJoin(DB::raw("saldosuratpengantar as b with (readuncommitted)"), 'a.suratpengantar_nobukti', 'b.nobukti')
                        ->whereRaw("(isnull(statusapprovalbiayatitipanemkl,0)<>4)")
                        ->where('a.suratpengantar_nobukti', $suratPengantar)
                        ->where('a.statustitipanemkl', $statusTitipan);
                }
            } else {
                $cekTitipan = DB::table("pengeluarantruckingdetail")->from(DB::raw("pengeluarantruckingdetail as a with (readuncommitted)"))
                    ->select(
                        'a.id',
                        'a.suratpengantar_nobukti',
                        'a.statustitipanemkl',
                        db::raw("(case when isnull(b.tglbatasbiayatitipanemkl,'1900/1/1')<=getdate() then 1 else 0 end) as statusbuka")
                    )
                    ->leftJoin(DB::raw("saldosuratpengantar as b with (readuncommitted)"), 'a.suratpengantar_nobukti', 'b.nobukti')
                    ->whereRaw("(isnull(statusapprovalbiayatitipanemkl,0)<>3)")
                    ->where('a.suratpengantar_nobukti', $suratPengantar)
                    ->where('a.statustitipanemkl', $statusTitipan);
            }
        }

        if ($id != null && ($cekTitipan != null)) {
            $cekTitipan->where('a.pengeluarantruckingheader_id', '!=', $id);
        }

        if ($cekTitipan != null) {
            $cekTitipan = $cekTitipan->first();
        }

        // dd($cekTitipan);
        if ($cekTitipan != null) {
            return false;
        }
        return true;
    }
}
