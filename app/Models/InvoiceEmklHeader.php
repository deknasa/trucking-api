<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InvoiceEmklHeader extends MyModel
{
    use HasFactory;

    protected $table = 'invoiceemklheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get()
    {
        $this->setRequestParameters();
        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';
        $query = DB::table($this->table)->from(DB::raw("invoiceemklheader with (readuncommitted)"))
            ->select(
                'invoiceemklheader.id',
                'invoiceemklheader.nobukti',
                'invoiceemklheader.tglbukti',
                'pelanggan.namapelanggan as pelanggan_id',
                'jenisorder.keterangan as jenisorder_id',
                'invoiceemklheader.nobuktiinvoicepajak',
                'invoiceemklheader.nobuktiinvoicereimbursement',
                'invoiceemklheader.nobuktiinvoicenonpajak',
                'invoiceemklheader.pengeluaranheader_nobukti',
                'invoiceemklheader.piutang_nobukti',
                'invoiceemklheader.destination',
                'invoiceemklheader.keterangan',
                'invoiceemklheader.kapal',
                'invoiceemklheader.qty',
                'statusinvoice.memo as statusinvoice',
                'statuspajak.memo as statuspajak',
                'statusppn.memo as statusppn',
                db::raw("isnull(invoiceemklheader.nominalppn,0) as nominalppn"),
                'statusapproval.memo as statusapproval',
                'invoiceemklheader.userapproval',
                DB::raw('(case when (year(invoiceemklheader.tglapproval) <= 2000) then null else invoiceemklheader.tglapproval end ) as tglapproval'),
                'statuscetak.memo as statuscetak',
                'invoiceemklheader.userbukacetak',
                DB::raw('(case when (year(invoiceemklheader.tglbukacetak) <= 2000) then null else invoiceemklheader.tglbukacetak end ) as tglbukacetak'),
                'invoiceemklheader.modifiedby',
                'invoiceemklheader.created_at',
                'invoiceemklheader.updated_at',
                db::raw("cast((format(pengeluaran.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpengeluaranheader"),
                db::raw("cast(cast(format((cast((format(pengeluaran.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpengeluaranheader"),
            )
            ->leftJoin(DB::raw("pengeluaranheader as pengeluaran with (readuncommitted)"), 'invoiceemklheader.pengeluaranheader_nobukti', '=', 'pengeluaran.nobukti')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'invoiceemklheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'invoiceemklheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("parameter as statusinvoice with (readuncommitted)"), 'invoiceemklheader.statusinvoice', 'statusinvoice.id')
            ->leftJoin(DB::raw("parameter as statuspajak with (readuncommitted)"), 'invoiceemklheader.statuspajak', 'statuspajak.id')
            ->leftJoin(DB::raw("parameter as statusppn with (readuncommitted)"), 'invoiceemklheader.statusppn', 'statusppn.id')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'invoiceemklheader.pelanggan_id', 'pelanggan.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'invoiceemklheader.jenisorder_id', 'jenisorder.id');

        if (request()->tgldari && request()->tglsampai) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(invoiceemklheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(invoiceemklheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("invoiceemklheader.statuscetak", $statusCetak);
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();
        return $data;
    }
    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'pelanggan') {
            return $query->orderBy('pelanggan.namapelanggan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'jenisorder_id') {
            return $query->orderBy('jenisorder.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'container_id') {
            return $query->orderBy('container.keterangan', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statusapproval') {
                                $query = $query->where('statusapproval.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'statuscetak') {
                                $query = $query->where('statuscetak.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'statusinvoice') {
                                $query = $query->where('statusinvoice.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'statuspajak') {
                                $query = $query->where('statuspajak.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'statusppn') {
                                $query = $query->where('statusppn.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'pelanggan_id') {
                                $query = $query->where('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'jenisorder_id') {
                                $query = $query->where('jenisorder.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'container_id') {
                                $query = $query->where('container.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominalppn') {
                                $query = $query->whereRaw("format(isnull($this->table.nominalppn, 0), '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgljatuhtempo' || $filters['field'] == 'tglterima' || $filters['field'] == 'tglbukacetak' || $filters['field'] == 'tglapproval') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'statusapproval') {
                                    $query = $query->orWhere('statusapproval.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'statuscetak') {
                                    $query = $query->orWhere('statuscetak.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'statusinvoice') {
                                    $query = $query->orWhere('statusinvoice.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'statuspajak') {
                                    $query = $query->orWhere('statuspajak.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'statusppn') {
                                    $query = $query->orWhere('statusppn.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'pelanggan_id') {
                                    $query = $query->orWhere('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'jenisorder_id') {
                                    $query = $query->orWhere('jenisorder.keterangan', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'container_id') {
                                    $query = $query->orWhere('container.keterangan', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'nominalppn') {
                                    $query = $query->orWhereRaw("format(isnull($this->table.nominalppn, 0), '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgljatuhtempo' || $filters['field'] == 'tglterima' || $filters['field'] == 'tglbukacetak' || $filters['field'] == 'tglapproval') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else {
                                    // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                                }
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

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "$this->table.id,
                 $this->table.nobukti,
                 $this->table.tglbukti,
                'pelanggan.namapelanggan as pelanggan_id',
                'jenisorder.keterangan as jenisorder_id',

                $this->table.nobuktiinvoicepajak,
                $this->table.nobuktiinvoicereimbursement,
                $this->table.nobuktiinvoicenonpajak,
                $this->table.pengeluaranheader_nobukti,
                $this->table.piutang_nobukti,
                $this->table.destination,
                $this->table.keterangan,
                $this->table.kapal,
                $this->table.qty,
                'statusinvoice.text as statusinvoice',
                'statuspajak.text as statuspajak',
                'statusppn.text as statusppn',
                isnull(invoiceemklheader.nominalppn,0) as nominalppn,
                'statusapproval.text as statusapproval',
                $this->table.userapproval,
                $this->table.tglapproval,
                'statuscetak.text as statuscetak',
                $this->table.userbukacetak,
                $this->table.tglbukacetak,
                $this->table.modifiedby,
                $this->table.created_at,
                $this->table.updated_at
                "
                )
            )
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'invoiceemklheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'invoiceemklheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("parameter as statusinvoice with (readuncommitted)"), 'invoiceemklheader.statusinvoice', 'statusinvoice.id')
            ->leftJoin(DB::raw("parameter as statuspajak with (readuncommitted)"), 'invoiceemklheader.statuspajak', 'statuspajak.id')
            ->leftJoin(DB::raw("parameter as statusppn with (readuncommitted)"), 'invoiceemklheader.statusppn', 'statusppn.id')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'invoiceemklheader.pelanggan_id', 'pelanggan.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'invoiceemklheader.jenisorder_id', 'jenisorder.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('pelanggan_id')->nullable();
            $table->string('jenisorder_id')->nullable();
            $table->string('nobuktiinvoicepajak')->nullable();
            $table->string('nobuktiinvoicereimbursement')->nullable();
            $table->string('nobuktiinvoicenonpajak')->nullable();
            $table->string('pengeluaranheader_nobukti')->nullable();
            $table->string('piutang_nobukti')->nullable();
            $table->string('destination')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('kapal')->nullable();
            $table->string('qty')->nullable();
            $table->string('statusinvoice')->nullable();
            $table->string('statuspajak')->nullable();
            $table->string('statusppn')->nullable();
            $table->bigInteger('nominalppn')->nullable();
            $table->string('statusapproval')->nullable();
            $table->string('userapproval')->default();
            $table->date('tglapproval')->nullable();
            $table->string('statuscetak', 1000)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->string('modifiedby')->default();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        if ((date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tgldariheader))) || (date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tglsampaiheader)))) {
            request()->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            request()->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
        }

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        $models =  $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'pelanggan_id', 'jenisorder_id', 'nobuktiinvoicepajak', 'nobuktiinvoicereimbursement', 'nobuktiinvoicenonpajak', 'pengeluaranheader_nobukti', 'piutang_nobukti', 'destination', 'keterangan', 'kapal', 'qty', 'statusinvoice', 'statuspajak', 'statusppn', 'nominalppn', 'statusapproval', 'userapproval', 'tglapproval', 'statuscetak', 'userbukacetak', 'tglbukacetak', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function findAll($id)
    {
        $query = DB::table("invoiceemklheader")->from(DB::raw("invoiceemklheader with (readuncommitted)"))
            ->select(
                'invoiceemklheader.*',
                'pelanggan.namapelanggan as pelanggan',
                'jenisorder.keterangan as jenisorder',
                'tujuan.keterangan as tujuan'
            )
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'invoiceemklheader.pelanggan_id', 'pelanggan.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'invoiceemklheader.jenisorder_id', 'jenisorder.id')
            ->leftJoin(DB::raw("tujuan with (readuncommitted)"), 'invoiceemklheader.tujuan_id', 'tujuan.id')
            ->where('invoiceemklheader.id', $id);
        $data = $query->first();
        return $data;
    }


    public function getSpSearch($request, $id, $edit)
    {

        $statusjeniskendaraan = request()->statusjeniskendaraan;
        $parambukti = request()->nobukti ?? '';
        $invoiceUtamaId = DB::table('parameter')->from(DB::raw("parameter as a with (readuncommitted)"))
            ->select('a.id')
            ->where('a.grp', '=', 'STATUS INVOICE EMKL')
            ->where('a.text', '=', 'UTAMA')
            ->first();


        $statusinvoice = $request->statusinvoice ?? 0;

        $temphasil = '##temphasil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temphasil, function ($table) {
            $table->string('nojobemkl', 1000)->nullable();
            $table->date('tgljobemkl')->nullable();
            $table->string('nocont', 1000)->nullable();
            $table->string('noseal', 1000)->nullable();
            $table->string('namapelanggan', 1000)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->longtext('keteranganbiaya')->nullable();
        });
        if ($request->jenisorder_id == 1) {

            $queryhasil = DB::table('jobemkl')->from(
                db::raw("jobemkl a with (readuncommitted)")
            )
                ->select(db::raw("a.nobukti as nojobemkl, a.tglbukti as tgljobemkl, a.nocont, a.noseal, b.namapelanggan,a.nominal,'' as ketaranganbiaya"))
                ->Join(db::raw("pelanggan as b with (readuncommitted)"), 'a.shipper_id', 'b.id')
                ->whereRaw("a.tglbukti>='" . date('Y-m-d', strtotime($request->tgldari)) . "' and  a.tglbukti<='" . date('Y-m-d', strtotime($request->tglsampai)) . "'")
                ->where('a.jenisorder_id', $request->jenisorder_id)
                ->where('a.shipper_id', $request->pelanggan_id);
        } else {
            if ($statusinvoice == $invoiceUtamaId->id) {
                $queryhasil = DB::table('jobemkl')->from(
                    db::raw("jobemkl a with (readuncommitted)")
                )
                    ->select(
                        db::raw("a.nobukti as nojobemkl, a.tglbukti as tgljobemkl, a.nocont, a.noseal, b.namapelanggan,a.nominal"),
                        db::raw("
                                (SELECT b1.kodebiayaemkl as biaya_emkl,
                                    format(a1.nominal,'#0.00') as nominal_biaya,
                                    a1.keterangan as keterangan_biaya
                                from jobemklrincianbiaya a1 
                                    inner join biayaemkl b1 on a1.biayaemkl_id=b1.id
                                    where a1.nobukti=a.nobukti 
                                FOR JSON PATH) as keteranganbiaya
    
                           "),

                    )
                    ->leftJoin(db::raw("pelanggan as b with (readuncommitted)"), 'a.shipper_id', 'b.id')
                    ->leftJoin(db::raw("invoiceemkldetail as d with (readuncommitted)"), 'a.nobukti', 'd.jobemkl_nobukti')
                    // ->leftJoin(db::raw("invoiceemkldetail d with (readuncommitted)"), function ($join)  use ($parambukti) {
                    //     $join->on('jobemkl.nobukti', '=', 'd.jobemkl_nobukti');
                    //     $join->on('d.nobukti', '!=', DB::raw($parambukti));
                    // })                    
                    ->whereRaw("a.tglbukti>='" . date('Y-m-d', strtotime($request->tgldari)) . "' and  a.tglbukti<='" . date('Y-m-d', strtotime($request->tglsampai)) . "'")
                    ->where('a.tujuan_id', $request->tujuan_id)
                    ->where('a.jenisorder_id', $request->jenisorder_id);
                // ->whereraw("isnull(d.nobukti,'')=''");
            } else {
                $queryhasil = DB::table('jobemkl')->from(
                    db::raw("jobemkl a with (readuncommitted)")
                )
                    ->select(
                        db::raw("a.nobukti as nojobemkl, a.tglbukti as tgljobemkl, a.nocont, a.noseal, b.namapelanggan"),
                        db::raw("0 as nominal"),
                        db::raw("'' as keteranganbiaya"),

                    )
                    ->leftJoin(db::raw("pelanggan as b with (readuncommitted)"), 'a.shipper_id', 'b.id')
                    ->whereRaw("a.tglbukti>='" . date('Y-m-d', strtotime($request->tgldari)) . "' and  a.tglbukti<='" . date('Y-m-d', strtotime($request->tglsampai)) . "'")
                    ->where('a.tujuan_id', $request->tujuan_id)
                    ->where('a.jenisorder_id', $request->jenisorder_id);
            }
        }


        DB::table($temphasil)->insertUsing([
            'nojobemkl',
            'tgljobemkl',
            'nocont',
            'noseal',
            'namapelanggan',
            'nominal',
            'keteranganbiaya',
        ], $queryhasil);

        $tempdatahasil = '##tempdatahasil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdatahasil, function ($table) {
            $table->Integer('idinvoice')->nullable();
            $table->longText('nojobemkl')->nullable();
            $table->date('tgljobemkl')->nullable();
            $table->LongText('nocont')->nullable();
            $table->LongText('noseal')->nullable();
            $table->LongText('namapelanggan')->nullable();
            $table->Double('nominal', 15, 2)->nullable();
            $table->LongText('keterangan_detail')->nullable();
            $table->LongText('keterangan_biaya')->nullable();
        });


        $query2 = DB::table('invoiceemkldetail')->from(
            DB::raw("invoiceemkldetail as a")
        )
            ->select(
                'a.invoiceemkl_id as idinvoice',
                DB::raw("a.jobemkl_nobukti as nojobemkl"),
                DB::raw("b.tglbukti as tgljobemkl"),
                DB::raw("b.nocont"),
                DB::raw("b.noseal"),
                DB::raw("pelanggan.namapelanggan"),
                DB::raw("isnull(a.nominal,0) as nominal"),
                'a.keterangan as keterangan_detail'

            )
            ->leftjoin(DB::raw($temphasil . " a1"), 'a.jobemkl_nobukti', 'a1.nojobemkl')
            ->join(DB::raw("jobemkl as b with (readuncommitted)"), 'a1.nojobemkl', 'b.nobukti')
            ->leftJoin(db::raw("pelanggan with (readuncommitted)"), 'b.shipper_id', 'pelanggan.id')
            ->where('a.invoiceemkl_id', $id)

            ->orderBy("b.tglbukti");


        DB::table($tempdatahasil)->insertUsing([
            'idinvoice',
            'nojobemkl',
            'tgljobemkl',
            'nocont',
            'noseal',
            'namapelanggan',
            'nominal',
            'keterangan_detail'
        ], $query2);

        // dd($query2->get(), db::table($temphasil)->get());


        $query2 = DB::table($temphasil)->from(
            DB::raw($temphasil . " as a")
        )
            ->select(
                DB::raw("null as idinvoice"),
                'a.nojobemkl',
                'a.tgljobemkl',
                'a.nocont',
                'a.noseal',
                'a.namapelanggan',
                DB::raw("a.nominal as nominal"),
                DB::raw("'' as keterangan_detail"),
                DB::raw("a.keteranganbiaya as keterangan_biaya")
            );


        if ($statusinvoice == $invoiceUtamaId->id || $edit == true) {
            $query2->leftJoin(DB::raw("invoiceemkldetail f with (readuncommitted)"), 'a.nojobemkl', 'f.jobemkl_nobukti')
                ->whereRaw("isnull(f.jobemkl_nobukti,'')=''");
        }

        $query2->orderBy("a.tgljobemkl");

        DB::table($tempdatahasil)->insertUsing([
            'idinvoice',
            'nojobemkl',
            'tgljobemkl',
            'nocont',
            'noseal',
            'namapelanggan',
            'nominal',
            'keterangan_detail',
            'keterangan_biaya'
        ], $query2);


        if ($statusinvoice == $invoiceUtamaId->id && $request->jenisorder_id == 2) {
            $tempawal = '##tempawal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempawal, function ($table) {
                $table->longText('jobemkl')->nullable();
            });

            $queryawal =  DB::table('jobemkl')->from(
                db::raw("jobemkl a with (readuncommitted)")
            )
                ->select('a.nobukti as jobemkl')
                ->join(db::raw("invoiceemkldetail as b with (readuncommitted)"), 'a.nobukti', 'b.jobemkl_nobukti')
                ->join(db::raw("invoiceemklheader as c with (readuncommitted)"), 'b.nobukti', 'c.nobukti')
                ->whereRaw("a.tglbukti>='" . date('Y-m-d', strtotime($request->tgldari)) . "' and  a.tglbukti<='" . date('Y-m-d', strtotime($request->tglsampai)) . "'")
                ->where('c.statusinvoice', '!=', 722);
            DB::table($tempawal)->insertUsing([
                'jobemkl',
            ], $queryawal);

            $tempgetutama = '##tempgetutama' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempgetutama, function ($table) {
                $table->longText('jobemkl')->nullable();
            });
            $querygetutama =  DB::table($tempawal)->from(
                db::raw("$tempawal a with (readuncommitted)")
            )
                ->select('a.jobemkl')
                ->join(db::raw("invoiceemkldetail as b with (readuncommitted)"), 'a.jobemkl', 'b.jobemkl_nobukti')
                ->join(db::raw("invoiceemklheader as c with (readuncommitted)"), 'b.nobukti', 'c.nobukti')
                ->where('c.statusinvoice', 722);
            DB::table($tempgetutama)->insertUsing([
                'jobemkl',
            ], $querygetutama);

            $tempfinal = '##tempfinal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempfinal, function ($table) {
                $table->longText('jobemkl')->nullable();
            });
            $queryfinal =  DB::table($tempawal)->from(
                db::raw("$tempawal a with (readuncommitted)")
            )
                ->select('a.jobemkl')
                ->leftJoin(db::raw("$tempgetutama as b with (readuncommitted)"), 'a.jobemkl', 'b.jobemkl')
                ->whereRaw("isnull(b.jobemkl,'')=''");
            DB::table($tempfinal)->insertUsing([
                'jobemkl',
            ], $queryfinal);

            $queryhasil = DB::table('jobemkl')->from(
                db::raw("jobemkl a with (readuncommitted)")
            )
                ->select(
                    db::raw("null as idinvoice, a.nobukti as nojobemkl, a.tglbukti as tgljobemkl, a.nocont, a.noseal, b.namapelanggan,a.nominal,'' as keterangan_detail"),
                    db::raw("
                            (SELECT b1.kodebiayaemkl as biaya_emkl,
                                format(a1.nominal,'#0.00') as nominal_biaya,
                                a1.keterangan as keterangan_biaya
                            from jobemklrincianbiaya a1 
                                inner join biayaemkl b1 on a1.biayaemkl_id=b1.id
                                where a1.nobukti=a.nobukti 
                            FOR JSON PATH) as keterangan_biaya

                       "),

                )
                ->leftJoin(db::raw("pelanggan as b with (readuncommitted)"), 'a.shipper_id', 'b.id')
                ->join(db::raw("$tempfinal as d with (readuncommitted)"), 'a.nobukti', 'd.jobemkl')
                ->whereRaw("a.tglbukti>='" . date('Y-m-d', strtotime($request->tgldari)) . "' and  a.tglbukti<='" . date('Y-m-d', strtotime($request->tglsampai)) . "'")
                ->where('a.tujuan_id', $request->tujuan_id)
                ->where('a.jenisorder_id', $request->jenisorder_id)
                ->orderBy("a.tglbukti");


            DB::table($tempdatahasil)->insertUsing([
                'idinvoice',
                'nojobemkl',
                'tgljobemkl',
                'nocont',
                'noseal',
                'namapelanggan',
                'nominal',
                'keterangan_detail',
                'keterangan_biaya'
            ], $queryhasil);
        }
        // dd(db::table($tempdatahasil)->get());
        if ($edit == true) {

            $tempdatainvoice = '##tempdatainvoice' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempdatainvoice, function ($table) {
                $table->longText('jobemkl_nobukti')->nullable();
                $table->Integer('id')->nullable();
            });

            $queryinvoice = db::table("invoiceemklheader")->from(db::raw("invoiceemklheader a with (readuncommitted)"))
                ->select(
                    'b.jobemkl_nobukti',
                    'b.id'
                )
                ->join(db::raw("invoiceemkldetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
                ->where('a.id', $id);

            DB::table($tempdatainvoice)->insertUsing([
                'jobemkl_nobukti',
                'id',
            ], $queryinvoice);

            $query = DB::table($tempdatahasil)->from(
                DB::raw($tempdatahasil . " as a")
            )
                ->select(
                    DB::raw("row_number() Over(Order By tgljobemkl) as id"),
                    'a.idinvoice',
                    'a.nojobemkl',
                    'a.tgljobemkl',
                    'a.nocont',
                    'a.noseal',
                    'a.namapelanggan',
                    'a.nominal',
                    'a.keterangan_detail',
                    db::raw("
                    (SELECT b1.kodebiayaemkl as biaya_emkl,
                           format(a1.nominal,'#0.00') as nominal_biaya,
                           a1.keterangan as keterangan_biaya
                       from invoiceemkldetailrincianbiaya a1 
                           inner join biayaemkl b1 on a1.biayaemkl_id=b1.id
                           where a1.invoiceemkldetail_id=b.id 
                       FOR JSON PATH) as keterangan_biaya
                   "),
                )
                ->join(db::raw($tempdatainvoice . " b "), 'a.nojobemkl', 'b.jobemkl_nobukti')
                ->orderBy("a.tgljobemkl");
        } else {
            $query = DB::table($tempdatahasil)->from(
                DB::raw($tempdatahasil . " as a")
            )
                ->select(
                    DB::raw("row_number() Over(Order By tgljobemkl) as id"),
                    'a.idinvoice',
                    'a.nojobemkl',
                    'a.tgljobemkl',
                    'a.nocont',
                    'a.noseal',
                    'a.namapelanggan',
                    'a.nominal',
                    'a.keterangan_detail',
                    'a.keterangan_biaya',
                )
                // ->where('a.nocont', '!=', '')
                ->orderBy("a.tgljobemkl");
        }


        $data = $query->get();

        // dd($data);
        return $data;
    }

    public function cekvalidasiaksi($nobukti)
    {

        $error = new Error();
        $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';


        $pelunasanPiutang = DB::table('pelunasanpiutangdetail')
            ->from(
                DB::raw("pelunasanpiutangdetail as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.invoice_nobukti'
            )
            ->where('a.invoice_nobukti', '=', $nobukti)
            ->first();
        if (isset($pelunasanPiutang)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti pelunasan piutang <b>' . $pelunasanPiutang->nobukti . '</b> <br> ' . $keterangantambahanerror,

                // 'keterangan' => 'Pelunasan Piutang ' . $pelunasanPiutang->nobukti,
                'kodeerror' => 'SATL2'
            ];
            goto selesai;
        }
        $getPengeluaran = db::table("invoiceemklheader")->from(db::raw("invoiceemklheader with (readuncommitted)"))
            ->where('nobukti', $nobukti)
            ->first();

        $pelunasanPiutang = DB::table('pengeluaranheader')
            ->from(
                DB::raw("pengeluaranheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
            )
            ->where('a.nobukti', '=', $getPengeluaran->pengeluaranheader_nobukti)
            ->first();
        if (isset($pelunasanPiutang)) {

            $keteranganerror = $error->cekKeteranganError('TDT') ?? '';
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti PENGELUARAN <b>' . $pelunasanPiutang->nobukti . '</b> <br> ' . $keterangantambahanerror,

                // 'keterangan' => 'Pelunasan Piutang ' . $pelunasanPiutang->nobukti,
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }

        $data = [
            'kondisi' => false,
            'keterangan' => '',
        ];
        selesai:
        return $data;
    }

    public function processStore(array $data): InvoiceEmklHeader
    {
        $prosesReimburse = $data['prosesreimburse'] ?? 0;
        $jenisbiaya = $data['statusjenisbiaya'] ?? 0;

        $group = 'INVOICE BUKTI';
        $subGroup = 'INVOICE BUKTI';
        $format = DB::table('parameter')
            ->where('grp', $group)
            ->where('subgrp', $subGroup)
            ->first();

        $groupinvoicebongkaran = 'INVOICE BONGKARAN';
        $subGroupinvoicebongkaran = 'INVOICE BONGKARAN';
        $formatinvoicetambahan = DB::table('parameter')
            ->where('grp', $groupinvoicebongkaran)
            ->where('subgrp', $subGroupinvoicebongkaran)
            ->first();

        $groupinvoicepajak = 'INVOICE PAJAK';
        $subGroupinvoicepajak = 'INVOICE PAJAK';
        $formatinvoicepajak = DB::table('parameter')
            ->where('grp', $groupinvoicepajak)
            ->where('subgrp', $subGroupinvoicepajak)
            ->first();

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
        $statusPPN = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS PPN')->where('default', 'YA')->first();
        $statusNonPajak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS PAJAK')->where('text', 'NON PAJAK')->first();
        $statusPajak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS PAJAK')->where('text', 'PAJAK')->first();
        $statusInvoice = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS INVOICE EMKL')->where('text', 'UTAMA')->first();
        $idMuatan = DB::table("jenisorder")->from(db::raw("jenisorder with (readuncommitted)"))
            ->where('kodejenisorder', 'MUAT')->first();
        $statusReimbursement = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS REIMBURSE')->where('text', 'TIDAK')->first();

        $jenisorder_id = $data['jenisorder_id'] ?? 0;
        $tujuan_id = $data['tujuan_id'] ?? 0;
        $cabang_id = $data['cabang_id'] ?? 0;
        $parameter = new Parameter();
        $cabang_id = $parameter->cekText('ID CABANG', 'ID CABANG') ?? 0;


        $statuspajakdata = $data['statuspajak'] ?? '';
        $invoiceHeader = new InvoiceEmklHeader();
        // $no=(new RunningNumberService)->get($groupinvoicepajak, $subGroupinvoicepajak, $invoiceHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])), 0, 0, 0, 0, 'nobuktiinvoicepajak', 'statusformatinvoicepajak');
        // dd($no);
        $invoiceHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $invoiceHeader->pelanggan_id = $data['pelanggan_id'];
        $invoiceHeader->jenisorder_id = $data['jenisorder_id'];
        $invoiceHeader->tujuan_id = $tujuan_id;
        $invoiceHeader->statusinvoice = $data['statusinvoice'];
        $invoiceHeader->statuspajak = $data['statuspajak'];
        $invoiceHeader->statusppn = $data['statusppn'] ?? $statusPPN->id;
        // $invoiceHeader->nobuktiinvoicepajak = $data['nobuktiinvoicepajak'] ?? '';
        $invoiceHeader->keterangan = $data['keterangan'] ?? '';
        // $invoiceHeader->destination = $data['destination'] ?? '';
        // $invoiceHeader->kapal = $data['kapal'] ?? '';
        $invoiceHeader->statusapproval = $statusApproval->id;
        $invoiceHeader->userapproval = '';
        $invoiceHeader->tglapproval = '';
        $invoiceHeader->statuscetak = $statusCetak->id;
        $invoiceHeader->tgldari = ($data['tgldari'] != '') ? date('Y-m-d', strtotime($data['tgldari'])) : null;
        $invoiceHeader->tglsampai = ($data['tglsampai'] != '') ? date('Y-m-d', strtotime($data['tglsampai'])) : null;
        $invoiceHeader->modifiedby = auth('api')->user()->name;
        $invoiceHeader->info = html_entity_decode(request()->info);
        $invoiceHeader->statusformat = $format->id;
        $invoiceHeader->statusformatreimbursement = $data['statusreimbursement'] ?? $statusReimbursement->id;
        $invoiceHeader->nobukti = (new RunningNumberService)->get($group, $subGroup, $invoiceHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
        if ($jenisorder_id == 2) {
            $invoiceHeader->statusformatinvoicetambahan = $formatinvoicetambahan->id;
            $invoiceHeader->nobuktiinvoicetambahan = (new RunningNumberService)->get($groupinvoicebongkaran, $subGroupinvoicebongkaran, $invoiceHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])), $tujuan_id, $cabang_id, 0, 0, 'nobuktiinvoicetambahan', 'statusformatinvoicetambahan');
        }
        if ($jenisorder_id == 1) {
            // dd($statusPajak->id,$statuspajakdata);
            if ($statusPajak->id == $statuspajakdata) {
                // dd('a');
                $invoiceHeader->statusformatinvoicepajak = $formatinvoicepajak->id;
                $invoiceHeader->nobuktiinvoicepajak = (new RunningNumberService)->get($groupinvoicepajak, $subGroupinvoicepajak, $invoiceHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])), 0, 0, 0, 0, 'nobuktiinvoicepajak', 'statusformatinvoicepajak');
                // dd($invoiceHeader->nobuktiinvoicepajak);
            }
        }

        if ($invoiceHeader->tujuan_id != 0) {
            $pelangggan = db::table("tujuan")->from(db::raw("tujuan with (readuncommitted)"))->where('id', $invoiceHeader->tujuan_id)->first()->pelanggan_id ?? 0;
            $data['pelanggan_id'] = $pelangggan;

            $invoiceHeader->pelanggan_id = $pelangggan;
        }
        // dd($invoiceHeader->nobuktiinvoicepajak);
        // dd('test1');
        // (string $group, string $subGroup, string $table, string $tgl, int  $tujuan = 0, int  $cabang = 0, int  $jenisbiaya = 0, int  $marketing = 0): string

        if ($prosesReimburse != 0) {

            $group = 'INVOICE REIMBURSEMENT';
            $subGroup = 'INVOICE REIMBURSEMENT';
            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subGroup)
                ->first();
            $invoiceHeader->statusformatreimbursement = $format->id;
            $cabang_id = $data['cabang_id'] ?? 0;

            $parameter = new Parameter();

            $cabang_id = $parameter->cekText('ID CABANG', 'ID CABANG') ?? '1900-01-01';

            $invoiceHeader->nobuktiinvoicereimbursement = (new RunningNumberService)->get($group, $subGroup, $invoiceHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])), $tujuan_id, $cabang_id, $jenisbiaya, 0, 'nobuktiinvoicereimbursement', 'statusformatinvoicereimbursement');
            // dd($invoiceHeader->nobuktiinvoicereimbursement);
            $invoiceHeader->pengeluaranheader_nobukti = $data['pengeluaranheader_nobukti'];
        }
        if ($data['statuspajak'] == $statusNonPajak->id) {
            $invoiceHeader->nobuktiinvoicenonpajak = $invoiceHeader->nobukti ?? '';
        }
        if (!$invoiceHeader->save()) {
            throw new \Exception("Error storing invoice emkl header.");
        }

        $invoiceDetails = [];

        $keteranganDetail = [];
        $nominalDetail = [];
        $invoiceNobukti = [];

        if ($data['jenisorder_id'] == $idMuatan->id) {
            $paramcoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE MUATAN UTAMA')
                ->where('subgrp', 'DEBET')
                ->where('text', 'DEBET')
                ->first();
            $memocoa = json_decode($paramcoa->memo, true);
            $coadebet = $memocoa['JURNAL'];

            $param = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE MUATAN UTAMA')
                ->where('subgrp', 'KREDIT')
                ->where('text', 'KREDIT')
                ->first();
            $memo = json_decode($param->memo, true);
            $coakredit = $memo['JURNAL'];

            $paramppn = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE MUATAN UTAMA')
                ->where('subgrp', 'KREDIT PPN')
                ->where('text', 'KREDIT PPN')
                ->first();
            $memo = json_decode($paramppn->memo, true);
            $coakreditppn = $memo['JURNAL'];
        } else {
            $paramcoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE BONGKARAN UTAMA')
                ->where('subgrp', 'DEBET')
                ->where('text', 'DEBET')
                ->first();
            $memocoa = json_decode($paramcoa->memo, true);
            $coadebet = $memocoa['JURNAL'];

            $param = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE BONGKARAN UTAMA')
                ->where('subgrp', 'KREDIT')
                ->where('text', 'KREDIT')
                ->first();
            $memo = json_decode($param->memo, true);
            $coakredit = $memo['JURNAL'];
        }

        if ($data['statusinvoice'] == $statusInvoice->id && date('m', strtotime($data['tglbukti'])) == date('m', strtotime($data['tgldari']))) {
            $coadebetdetail = $coadebet;
            $coakreditdetail = $coakredit;
        }
        if ($data['statusinvoice'] != $statusInvoice->id) {
            // JURNAL TAMBAHAN BULAN SAMA
            if (date('m', strtotime($data['tglbukti'])) == date('m', strtotime($data['tgldari']))) {

                if ($data['jenisorder_id'] == $idMuatan->id) {
                    $paramcoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE MUATAN UTAMA')
                        ->where('subgrp', 'DEBET')
                        ->where('text', 'DEBET')
                        ->first();
                    $memocoa = json_decode($paramcoa->memo, true);
                    $coadebetdetail = $memocoa['JURNAL'];

                    $param = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE MUATAN UTAMA')
                        ->where('subgrp', 'KREDIT')
                        ->where('text', 'KREDIT')
                        ->first();
                    $memo = json_decode($param->memo, true);
                    $coakreditdetail = $memo['JURNAL'];
                } else {
                    $paramcoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE BONGKARAN UTAMA')
                        ->where('subgrp', 'DEBET')
                        ->where('text', 'DEBET')
                        ->first();
                    $memocoa = json_decode($paramcoa->memo, true);
                    $coadebetdetail = $memocoa['JURNAL'];

                    $param = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE BONGKARAN UTAMA')
                        ->where('subgrp', 'KREDIT')
                        ->where('text', 'KREDIT')
                        ->first();
                    $memo = json_decode($param->memo, true);
                    $coakreditdetail = $memo['JURNAL'];
                }
            } else {

                // JURNAL TAMBAHAN BULAN BEDA
                $paramcoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PIUTANG INVOICE EMKL TAMBAHAN')
                    ->where('subgrp', 'DEBET')
                    ->where('text', 'DEBET')
                    ->first();
                $memocoa = json_decode($paramcoa->memo, true);
                $coadebetdetail = $memocoa['JURNAL'];

                $param = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PIUTANG INVOICE EMKL TAMBAHAN')
                    ->where('subgrp', 'KREDIT')
                    ->where('text', 'KREDIT')
                    ->first();
                $memo = json_decode($param->memo, true);
                $coakreditdetail = $memo['JURNAL'];
            }
        }

        if ($data['statusinvoice'] == $statusInvoice->id && date('m', strtotime($data['tglbukti'])) != date('m', strtotime($data['tgldari']))) {
            $paramcoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PIUTANG INVOICE EMKL KELEBIHAN')
                ->where('subgrp', 'DEBET')
                ->where('text', 'DEBET')
                ->first();
            $memocoa = json_decode($paramcoa->memo, true);
            $coadebetdetailkelebihan = $memocoa['JURNAL'];

            $param = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PIUTANG INVOICE EMKL KELEBIHAN')
                ->where('subgrp', 'KREDIT')
                ->where('text', 'KREDIT')
                ->first();
            $memo = json_decode($param->memo, true);
            $coakreditdetailkelebihan = $memo['JURNAL'];

            $paramcoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PIUTANG INVOICE EMKL KEKURANGAN')
                ->where('subgrp', 'DEBET')
                ->where('text', 'DEBET')
                ->first();
            $memocoa = json_decode($paramcoa->memo, true);
            $coadebetdetailkekurangan = $memocoa['JURNAL'];

            $param = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PIUTANG INVOICE EMKL KEKURANGAN')
                ->where('subgrp', 'KREDIT')
                ->where('text', 'KREDIT')
                ->first();
            $memo = json_decode($param->memo, true);
            $coakreditdetailkekurangan = $memo['JURNAL'];
        }

        if ($prosesReimburse != 0) {
            $idthc = (new Parameter())->cekId('BIAYA REIMBURSE EMKL', 'BIAYA REIMBURSE EMKL', 'THC');
            $idstorage = (new Parameter())->cekId('BIAYA REIMBURSE EMKL', 'BIAYA REIMBURSE EMKL', 'STO');
            $iddemurage = (new Parameter())->cekId('BIAYA REIMBURSE EMKL', 'BIAYA REIMBURSE EMKL', 'STO-DEM');
            if ($data['biaya'] == $idthc) {
                $paramcoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE REIMBURSE OPT')
                    ->where('subgrp', 'DEBET')
                    ->where('text', 'DEBET')
                    ->first();
                $memocoa = json_decode($paramcoa->memo, true);
                $coadebetdetail = $memocoa['JURNAL'];

                $param = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE REIMBURSE OPT')
                    ->where('subgrp', 'KREDIT')
                    ->where('text', 'KREDIT')
                    ->first();
                $memo = json_decode($param->memo, true);
                $coakreditdetail = $memo['JURNAL'];
            }
            if ($data['biaya'] == $idstorage) {
                $paramcoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE REIMBURSE STORAGE')
                    ->where('subgrp', 'DEBET')
                    ->where('text', 'DEBET')
                    ->first();
                $memocoa = json_decode($paramcoa->memo, true);
                $coadebetdetail = $memocoa['JURNAL'];

                $param = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE REIMBURSE STORAGE')
                    ->where('subgrp', 'KREDIT')
                    ->where('text', 'KREDIT')
                    ->first();
                $memo = json_decode($param->memo, true);
                $coakreditdetail = $memo['JURNAL'];
            }
            if ($data['biaya'] == $iddemurage) {
                $paramcoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE REIMBURSE DEMURAGE')
                    ->where('subgrp', 'DEBET')
                    ->where('text', 'DEBET')
                    ->first();
                $memocoa = json_decode($paramcoa->memo, true);
                $coadebetdetail = $memocoa['JURNAL'];

                $param = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE REIMBURSE DEMURAGE')
                    ->where('subgrp', 'KREDIT')
                    ->where('text', 'KREDIT')
                    ->first();
                $memo = json_decode($param->memo, true);
                $coakreditdetail = $memo['JURNAL'];
            }
        }
        $total = 0;
        $nojobs = '';
        $nominaljurnal = [];
        $coadebetjurnal = [];
        $coakreditjurnal = [];
        $keteranganjurnal = [];
        for ($i = 0; $i < count($data['nominal']); $i++) {



            $status = [];
            $nominalfifo = [];
            $coadebetfifo = [];
            $biayaemkl_idfifo = [];
            $selisih = 0;
            $jobemkl = '';
            if ($prosesReimburse == 0) {
                $jobemkl = DB::table("jobemkl")->from(DB::raw("jobemkl with (readuncommitted)"))
                    ->where('nobukti', $data['nojobemkl'][$i])->first();
            }
            if ($prosesReimburse == 0) {
                if ($data['statusinvoice'] == $statusInvoice->id && date('m', strtotime($data['tglbukti'])) != date('m', strtotime($data['tgldari']))) {
                    if ($data['jenisorder_id'] == $idMuatan->id) {
                        $nominalawal = db::table("jurnalumumdetail")->from(db::raw("jurnalumumdetail with (readuncommitted)"))
                            ->where('coa', $coakredit)->where('nobukti', $data['nojobemkl'][$i])->first()->nominal ?? 0;
                        $nominalawal = $nominalawal * -1;
                        // } else {
                        //     $nominalawal = db::table("jurnalumumdetail")->from(db::raw("jurnalumumdetail with (readuncommitted)"))
                        //         ->where('coa', $coadebet)->where('nobukti', $data['nojobemkl'][$i])->first()->nominal ?? 0;
                        // }

                        if ($statusPPN->text == 'PPN 1.1%') {
                            $nom = $data['nominal'][$i];
                            $nilaippn = db::select("select (case when (($nom * 0.011) - floor(($nom * 0.011))) >= 0.51 then round(($nom * 0.011),0) else round(($nom * 0.011),0,1) end) as ppn");
                            $nominalppn = $nilaippn[0]->ppn;
                        } else {
                            $nom = $data['nominal'][$i];
                            $nilaippn = db::select("select (case when (($nom * 0.11) - floor(($nom * 0.11))) >= 0.51 then round(($nom * 0.11),0) else round(($nom * 0.11),0,1) end) as ppn");
                            $nominalppn = $nilaippn[0]->ppn;
                        }

                        if ($data['nominal'][$i] > $nominalawal) {
                            $coadebetdetail = $coadebetdetailkelebihan;
                            $coakreditdetail = $coakreditdetailkelebihan;

                            $nominaljurnal[] = $data['nominal'][$i] - $nominalawal;
                            $selisih = $data['nominal'][$i] - $nominalawal;
                            $coadebetjurnal[] = $coadebetdetail;
                            $coakreditjurnal[] = $coakreditdetail;
                            $keteranganjurnal[] =  $invoiceHeader->nobukti . ' ' . $jobemkl->nobukti . ' ' . $data['keterangan_detail'][$i];
                            $status[] = 'normal';
                            $nominalfifo[] = $nominalawal + $nominalppn;
                            $coadebetfifo[] = $coadebet;
                            $status[] = 'selisih';
                            $nominalfifo[] = $selisih;
                            $coadebetfifo[] = $coadebetdetail;
                        }
                        if ($data['nominal'][$i] < $nominalawal) {
                            $coadebetdetail = $coadebetdetailkekurangan;
                            $coakreditdetail = $coakreditdetailkekurangan;

                            $nominaljurnal[] = $nominalawal - $data['nominal'][$i];
                            $selisih = $nominalawal - $data['nominal'][$i];
                            $coadebetjurnal[] = $coadebetdetail;
                            $coakreditjurnal[] = $coakreditdetail;
                            $keteranganjurnal[] =  $invoiceHeader->nobukti . ' ' . $jobemkl->nobukti . ' ' . $data['keterangan_detail'][$i];
                            $status[] = 'normal';
                            $nominalfifo[] = $nominalawal - $selisih + $nominalppn;
                            $coadebetfifo[] = $coadebet;
                            $status[] = 'selisih';
                            $nominalfifo[] = -$selisih;
                            $coadebetfifo[] = $coadebetdetail;
                        }
                        if ($data['nominal'][$i] == $nominalawal) {                        
                            $coadebetdetail = $coadebet;
                            $coakreditdetail = $coakredit;
                            if ($statusPPN->text == 'PPN 1.1%') {
                                $nom = $data['nominal'][$i];
                                $nilaippn = db::select("select (case when (($nom * 0.011) - floor(($nom * 0.011))) >= 0.51 then round(($nom * 0.011),0) else round(($nom * 0.011),0,1) end) as ppn");
                                $nominalppn = $nilaippn[0]->ppn;
                            } else {
                                $nom = $data['nominal'][$i];
                                $nilaippn = db::select("select (case when (($nom * 0.11) - floor(($nom * 0.11))) >= 0.51 then round(($nom * 0.11),0) else round(($nom * 0.11),0,1) end) as ppn");
                                $nominalppn = $nilaippn[0]->ppn;
                            }
                            $status[] = 'normal';
                            $nominalfifo[] = $data['nominal'][$i] + $nominalppn;
                            $coadebetfifo[] = $coadebet;
                        }
                    } else {
                        $coadebetdetail = $coadebet;
                        $coakreditdetail = $coakredit;
                    }
                }
                if ($data['statusinvoice'] == $statusInvoice->id && date('m', strtotime($data['tglbukti'])) == date('m', strtotime($data['tgldari']))) {
                    $status[] = 'normal';
                    if ($data['jenisorder_id'] != 2) {
                        $nominalfifo[] = $data['nominal'][$i];
                    }
                    $coadebetfifo[] = $coadebet;
                }
            }

            // 

            // 
            $nojobemkl = ($prosesReimburse == 0) ? $jobemkl->nobukti : '';
            if ($data['statusinvoice'] != $statusInvoice->id) {
                $nominaljurnal[] = $data['nominal'][$i];
                $coadebetjurnal[] = $coadebetdetail;
                $coakreditjurnal[] = $coakreditdetail;

                if ($prosesReimburse == 0) {
                    $keteranganjurnal[] =  $invoiceHeader->nobukti . ' ' . $nojobemkl . ' ' . $data['keterangan_detail'][$i];
                } else {
                    $keteranganjurnal[] =  $invoiceHeader->nobuktiinvoicereimbursement . ' ' . $data['keterangan_detail'][$i];
                }
                $status[] = 'normal';
                if ($data['jenisorder_id'] != 2) {
                    $nominalfifo[] = $data['nominal'][$i];
                }

                $coadebetfifo[] = $coadebet;
            }
            // dd($data['nominal'][$i]);

            $nominalbiaya = $data['nominal'][$i];
            // if ($data['jenisorder_id'] == $idMuatan->id) {
            //     if ($statusPPN->text == 'PPN 1.1%') {

            //         $nilaippn = db::select("select round(($nominalbiaya * 0.011), 0, 1) as ppn");
            //         $nominalppn = $nilaippn[0]->ppn;
            //         $nominalbiaya = $nominalbiaya + $nominalppn;
            //     } else {
            //         $nilaippn = db::select("select round(($nominalbiaya * 0.11), 0, 1) as ppn");
            //         $nominalppn = $nilaippn[0]->ppn;
            //         $nominalbiaya = $nominalbiaya + $nominalppn;
            //     }
            // }
            $invoiceDetail = (new InvoiceEmklDetail())->processStore($invoiceHeader, [
                'nominal' => $nominalbiaya,
                'jobemkl_nobukti' => ($prosesReimburse == 0) ? $jobemkl->nobukti : '',
                'container_id' => ($prosesReimburse == 0) ? $jobemkl->container_id : '',
                'coadebet' => $coadebetdetail,
                'coakredit' => $coakreditdetail,
                'selisih' => $selisih,
                'keterangan' => $invoiceHeader->nobukti . ' ' . $nojobemkl . ' ' . $data['keterangan_detail'][$i],
            ]);

            $invoiceDetails[] = $invoiceDetail->toArray();
            $keterangan_biaya = $data['keterangan_biaya'][$i] ?? '';
            if ($keterangan_biaya != '') {


                $invoiceEmklDetailRincianBiaya = (new InvoiceEmklDetailRincianBiaya())->processStore([
                    'invoiceemkl_id' => $invoiceHeader->id,
                    'invoiceemkldetail_id' => $invoiceDetail->id,
                    'jobemkl_nobukti' => $invoiceDetail->jobemkl_nobukti,
                    'keteranganbiaya' =>  $keterangan_biaya,
                    'nobukti' =>   $invoiceHeader->nobukti,
                    'tglbukti' =>  $data['tglbukti'],
                    'modifiedby' => auth('api')->user()->name,
                ]);
            }
            if ($data['jenisorder_id'] == 2 && $prosesReimburse == 0) {
                $querydetailjob = db::table("invoiceemkldetailrincianbiaya")->from(db::raw("invoiceemkldetailrincianbiaya a with (readuncommitted)"))
                    ->select(
                        db::raw("sum(a.nominal) as nominal")
                    )
                    ->join(db::raw("invoiceemkldetail b with (readuncommitted)"), 'a.invoiceemkldetail_id', 'b.id')
                    ->join(db::raw("invoiceemklheader c with (readuncommitted)"), 'b.invoiceemkl_id', 'c.id')
                    ->where('c.nobukti', $invoiceHeader->nobukti)
                    ->where('b.id', $invoiceDetail->id)
                    ->first();
                $nominalfifo[] = $querydetailjob->nominal ?? 0;
                $status[] = 'normal';
                $coadebetfifo[] = $coadebet;
                $nominaldetail = $querydetailjob->nominal ?? 0;

                db::update("update invoiceemkldetail set nominal=" . $nominaldetail . " where id=" . $invoiceDetail->id . " and nobukti='" . $invoiceHeader->nobukti . "'");

                if ($data['statusinvoice'] == $statusInvoice->id && date('m', strtotime($data['tglbukti'])) != date('m', strtotime($data['tgldari']))) {
                    $getSelisihBiaya = DB::table("jobemklrincianbiaya")->from(db::raw("jobemklrincianbiaya as a with (readuncommitted)"))
                        ->select(db::raw("a.nobukti,a.biayaemkl_id, isnull(a.nominal, 0) as nominalawal, isnull(b.nominal, 0) as nominalrincian, (case when isnull(b.nominal, 0) > isnull(a.nominal, 0) then (isnull(b.nominal,0) - isnull(a.nominal,0)) else (isnull(a.nominal, 0) - isnull(b.nominal, 0)) end) as selisih"))
                        ->join(DB::raw("invoiceemkldetailrincianbiaya as b with (readuncommitted)"), 'a.nobukti', 'b.jobemkl_nobukti')
                        ->where('a.nobukti', $invoiceDetail->jobemkl_nobukti)
                        ->whereRaw("a.biayaemkl_id=b.biayaemkl_id")
                        // ->whereRaw("(isnull(a.nominal, 0) - isnull(b.nominal, 0)) != 0")
                        ->get();
                    $selisihnilaijob = 0;
                    if (count($getSelisihBiaya) > 0) {
                        $nominalfifo = [];
                        $status = [];
                        $coadebetfifo = [];

                        for ($j = 0; $j < count($getSelisihBiaya); $j++) {
                            if ($getSelisihBiaya[$j]->nominalrincian > $getSelisihBiaya[$j]->nominalawal) {

                                $coadebetdetail = $coadebetdetailkelebihan;
                                $coakreditdetail = $coakreditdetailkelebihan;
                                $selisih = $getSelisihBiaya[$j]->nominalrincian - $getSelisihBiaya[$j]->nominalawal;

                                $nominaljurnal[] = $selisih;
                                $coadebetjurnal[] = $coadebetdetail;
                                $coakreditjurnal[] = $coakreditdetail;
                                $keteranganjurnal[] = $invoiceHeader->nobukti . ' ' . $nojobemkl . ' ' . $data['keterangan_detail'][$i];
                                $nominalfifo[] = $getSelisihBiaya[$j]->nominalawal;
                                $status[] = 'normal';
                                $coadebetfifo[] = $coadebet;
                                $biayaemkl_idfifo[] =  $getSelisihBiaya[$j]->biayaemkl_id;
                                $status[] = 'selisih';
                                $nominalfifo[] = $selisih;
                                $coadebetfifo[] = $coadebetdetailkelebihan;
                                $biayaemkl_idfifo[] =  $getSelisihBiaya[$j]->biayaemkl_id;
                                $selisihnilaijob += $selisih;
                            }
                            if ($getSelisihBiaya[$j]->nominalrincian < $getSelisihBiaya[$j]->nominalawal) {

                                $coadebetdetail = $coadebetdetailkekurangan;
                                $coakreditdetail = $coakreditdetailkekurangan;
                                $selisih = $getSelisihBiaya[$j]->nominalawal - $getSelisihBiaya[$j]->nominalrincian;
                                $nominaljurnal[] = $selisih;
                                $coadebetjurnal[] = $coadebetdetail;
                                $coakreditjurnal[] = $coakreditdetail;
                                $keteranganjurnal[] = $invoiceHeader->nobukti . ' ' . $nojobemkl . ' ' . $data['keterangan_detail'][$i];
                                $nominalfifo[] = $getSelisihBiaya[$j]->nominalawal;
                                $status[] = 'normal';
                                $coadebetfifo[] = $coadebet;
                                $biayaemkl_idfifo[] =  $getSelisihBiaya[$j]->biayaemkl_id;
                                $status[] = 'selisih';
                                $nominalfifo[] = -$selisih;
                                $coadebetfifo[] = $coadebetdetailkekurangan;
                                $biayaemkl_idfifo[] =  $getSelisihBiaya[$j]->biayaemkl_id;
                                $selisihnilaijob -= $selisih;
                            }
                            if ($getSelisihBiaya[$j]->nominalrincian == $getSelisihBiaya[$j]->nominalawal) {
                                $nominalfifo[] = $getSelisihBiaya[$j]->nominalawal;
                                $status[] = 'normal';
                                $coadebetfifo[] = $coadebet;
                                $biayaemkl_idfifo[] =  $getSelisihBiaya[$j]->biayaemkl_id;
                            }
                        }
                    }

                    db::update("update invoiceemkldetail set selisih=" . $selisihnilaijob . " where id=" . $invoiceDetail->id . " and nobukti='" . $invoiceHeader->nobukti . "'");
                }
            }

            $nojob_emkl = ($jobemkl != '') ? $jobemkl->nobukti : '';
            for ($a = 0; $a < count($nominalfifo); $a++) {

                $invoiceDetail = (new InvoiceEmklFifo())->processStore([
                    'nobukti' => $invoiceHeader->nobukti,
                    'jobemkl_nobukti' => $nojob_emkl,
                    'status' => $status[$a] ?? '',
                    'nominal' => $nominalfifo[$a] ?? 0,
                    'coadebet' => $coadebetfifo[$a] ?? '',
                    'biayaemkl_id' => $biayaemkl_idfifo[$a] ?? '',
                    'nominalpelunasan' => 0
                ]);
            }

            $total = $total + $data['nominal'][$i];
            if ($prosesReimburse == 0) {
                $nojobs .= ' ' . $jobemkl->nobukti . ',';
            }
            // STORE 

            // STORE JURNAL

            $coakredit_detail = [];
            $coakreditextra_detail = [];
            $coadebet_detail = [];
            $nominal_detail = [];
            $keterangan_detail = [];
            $nominal_ppn = [];
            $nominal_total = [];
            if ($data['statusinvoice'] == $statusInvoice->id  && date('m', strtotime($data['tglbukti'])) == date('m', strtotime($data['tgldari']))) {
                if ($data['jenisorder_id'] == $idMuatan->id) {
                    $coadebet_detail[] = $coadebet;
                    $coakredit_detail[] = $coakreditppn;
                    $coakreditextra_detail[] = $coakredit;
                    $nominal_detail[] = $data['nominal'][$i];
                    $keterangan_detail[] =   $invoiceHeader->nobukti . ' ' . $jobemkl->nobukti . ' ' . $data['keterangan_detail'][$i];
                    if ($statusPPN->text == 'PPN 1.1%') {
                        $nominalppn = round($data['nominal'][$i] * 0.011);
                    } else {
                        $nominalppn = round($data['nominal'][$i] * 0.11);
                    }
                    $nominal_ppn[] = $nominalppn;
                    $nominal_total[] = $data['nominal'][$i] + $nominalppn;
                    $jurnalRequest = [
                        'tanpaprosesnobukti' => 1,
                        'multikredit' => 1,
                        'nobukti' => $jobemkl->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                        'postingdari' => 'ENTRY INVOICE EMKL',
                        'statusformat' => "0",
                        'coakredit_detail' => $coakredit_detail,
                        'coadebet_detail' => $coadebet_detail,
                        'coakreditextra_detail' => $coakreditextra_detail,
                        'nominal_detail' => $nominal_detail,
                        'nominal_ppn' => $nominal_ppn,
                        'nominal_total' => $nominal_total,
                        'keterangan_detail' => $keterangan_detail
                    ];
                    $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $jobemkl->nobukti)->first();
                    if ($getJurnal != '') {

                        $newJurnal = new JurnalUmumHeader();
                        $newJurnal = $newJurnal->find($getJurnal->id);
                        (new JurnalUmumHeader())->processUpdate($newJurnal, $jurnalRequest);
                    } else {
                        (new JurnalUmumHeader())->processStore($jurnalRequest);
                    }
                } else {
                    $coadebet_detail[] = $coadebet;
                    $coakredit_detail[] = $coakredit;
                    $nominal_detail[] = $data['nominal'][$i];
                    $keterangan_detail[] =   $invoiceHeader->nobukti . ' ' . $jobemkl->nobukti . ' ' . $data['keterangan_detail'][$i];
                    $jurnalRequest = [
                        'tanpaprosesnobukti' => 1,
                        'nobukti' => $jobemkl->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                        'postingdari' => 'ENTRY INVOICE EMKL',
                        'statusformat' => "0",
                        'coakredit_detail' => $coakredit_detail,
                        'coadebet_detail' => $coadebet_detail,
                        'nominal_detail' => $nominal_detail,
                        'keterangan_detail' => $keterangan_detail
                    ];

                    $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $jobemkl->nobukti)->first();
                    if ($getJurnal != '') {

                        $newJurnal = new JurnalUmumHeader();
                        $newJurnal = $newJurnal->find($getJurnal->id);
                        (new JurnalUmumHeader())->processUpdate($newJurnal, $jurnalRequest);
                    } else {
                        (new JurnalUmumHeader())->processStore($jurnalRequest);
                    }
                }
            }
        }

        $tempkapal = '##tempkapal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempkapal, function ($table) {
            $table->longtext('kapal')->nullable();
        });

        $tempdestination = '##tempdestination' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdestination, function ($table) {
            $table->longtext('destination')->nullable();
        });

        $querykapal = db::table("invoiceemkldetail")->from(db::raw("invoiceemkldetail a with (readuncommitted)"))
            ->select(
                'b.kapal'
            )
            ->join(db::raw("jobemkl b"), 'a.jobemkl_nobukti', 'b.nobukti')
            ->join(db::raw("invoiceemklheader c"), 'a.nobukti', 'c.nobukti')
            ->where('c.nobukti', $invoiceHeader->nobukti)
            ->groupby('b.kapal');


        DB::table($tempkapal)->insertUsing([
            'kapal',
        ], $querykapal);

        $querydestination = db::table("invoiceemkldetail")->from(db::raw("invoiceemkldetail a with (readuncommitted)"))
            ->select(
                'b.destination'
            )
            ->join(db::raw("jobemkl b"), 'a.jobemkl_nobukti', 'b.nobukti')
            ->join(db::raw("invoiceemklheader c"), 'a.nobukti', 'c.nobukti')
            ->where('c.nobukti', $invoiceHeader->nobukti)
            ->groupby('b.destination');


        DB::table($tempdestination)->insertUsing([
            'destination',
        ], $querydestination);

        $kapal = db::table($tempkapal)->from(db::raw($tempkapal . " a "))
            ->select(
                db::raw("string_agg(cast(a.kapal as nvarchar(max)),', ') as kapal"),
            )
            ->first()->kapal ?? '';

        $destination = db::table($tempdestination)->from(db::raw($tempdestination . " a "))
            ->select(
                db::raw("string_agg(cast(a.destination as nvarchar(max)),', ') as destination"),
            )
            ->first()->destination ?? '';

        // dd($kapal,$destination, $invoiceHeader->nobukti);
        $invoiceHeader->destination = $destination ?? '';
        $invoiceHeader->kapal = $kapal ?? '';
        // db::update("update invoiceemklheader set kapal='" . $kapal . "',destination='".$destination."' where nobukti='" . $invoiceHeader->nobukti . "'");


        if ($data['statusinvoice'] == $statusInvoice->id) {
            $keteranganDetail[] = "TAGIHAN INVOICE EMKL " . $invoiceHeader->nobukti . " " . $nojobs;
        } else {
            if ($prosesReimburse != 0) {
                $keteranganDetail[] = "TAGIHAN INVOICE REIMBURSEMENT " . $invoiceHeader->nobuktiinvoicereimbursement;
            } else {
                $keteranganDetail[] = "TAGIHAN INVOICE EMKL TAMBAHAN " . $invoiceHeader->nobukti . " " . $nojobs;
            }
        }
        $nominalDetail[] = $total;
        $invoiceNobukti[] =  $invoiceHeader->nobukti;
        $nominalppn = 0;
        if ($prosesReimburse == 0) {
            if ($data['jenisorder_id'] == $idMuatan->id) {
                $nominalDetail = [];
                if ($statusPPN->text == 'PPN 1.1%') {

                    $nilaippn = db::select("select (case when (($total * 0.011) - floor(($total * 0.011))) >= 0.51 then round(($total * 0.011),0) else round(($total * 0.011),0,1) end) as ppn");
                    $nominalppn = $nilaippn[0]->ppn;
                    $nominalDetail[] = $total + $nominalppn;
                } else {
                    $nilaippn = db::select("select (case when (($total * 0.11) - floor(($total * 0.11))) >= 0.51 then round(($total * 0.11),0) else round(($total * 0.11),0,1) end) as ppn");
                    $nominalppn = $nilaippn[0]->ppn;
                    $nominalDetail[] = $total + $nominalppn;
                }
            }
        }
        
        if ($data['statusinvoice'] != $statusInvoice->id) {
            if($data['jenisorder_id'] == 1 && count($coadebetjurnal) > 0){
                $nominaljurnal[] = $nominalppn;
                $coadebetjurnal[] = $coadebetjurnal[0];
                $coakreditjurnal[] = $coakreditjurnal[0];
                $keteranganjurnal[] = 'PPN ATAS INVOICE PAJAK '. $invoiceHeader->nobuktiinvoicepajak;
            }
        }
        
        $invoiceRequest = [
            'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
            'tgljatuhtempo' => date('Y-m-d', strtotime($data['tglbukti'])),
            'postingdari' => 'ENTRY INVOICE EMKL',
            'invoice' => $invoiceHeader->nobukti,
            'coadebet' => $coadebet,
            'coakredit' => $coakredit,
            'pelanggan_id' => $data['pelanggan_id'],
            'invoice_nobukti' => $invoiceNobukti,
            'nominal_detail' => $nominalDetail,
            'keterangan_detail' => $keteranganDetail,
            'nominaljurnal' => $nominaljurnal,
            'coadebetjurnal' => $coadebetjurnal,
            'coakreditjurnal' => $coakreditjurnal,
            'keteranganjurnal' => $keteranganjurnal
        ];
        if ($data['statusinvoice'] == $statusInvoice->id && date('m', strtotime($data['tglbukti'])) == date('m', strtotime($data['tgldari']))) {
            $invoiceRequest['jenis'] = 'emklutama';
        }
        if ($data['statusinvoice'] == $statusInvoice->id && date('m', strtotime($data['tglbukti'])) != date('m', strtotime($data['tgldari']))) {
            $invoiceRequest['jenis'] = 'emklutamabedabulan';
        }
        if ($data['statusinvoice'] != $statusInvoice->id) {
            $invoiceRequest['jenis'] = 'emkltambahan';
        }

        $piutangHeader = (new PiutangHeader())->processStore($invoiceRequest);
        $invoiceHeader->piutang_nobukti = $piutangHeader->nobukti;


        $invoiceHeader->nominalppn = $nominalppn;

        if ($prosesReimburse == 0) {
            $tempqty = '##tempqty' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempqty, function ($table) {
                $table->string('nobukti', 50)->nullable();
                $table->string('kodecontainer', 50)->nullable();
                $table->string('qty', 50)->nullable();
            });
            $querygetcont = DB::table("invoiceemkldetail")->from(DB::raw("invoiceemkldetail with (readuncommitted)"))
                ->select(DB::raw("invoiceemkldetail.nobukti, container.kodecontainer, CAST(COUNT(invoiceemkldetail.container_id) AS VARCHAR) AS qty"))

                ->join(db::raw("container with (readuncommitted)"), 'invoiceemkldetail.container_id', 'container.id')
                ->where('invoiceemkldetail.nobukti', $invoiceHeader->nobukti)
                ->groupBy('invoiceemkldetail.nobukti', 'container.kodecontainer');

            DB::table($tempqty)->insertUsing([
                'nobukti',
                'kodecontainer',
                'qty'
            ], $querygetcont);

            $querygetqty = db::table($tempqty)->from(db::raw("$tempqty with (readuncommitted)"))
                ->select(DB::raw("nobukti, STRING_AGG(qty + 'x' + kodecontainer, ', ') AS qty"))
                ->groupBy('nobukti')
                ->first();

            $invoiceHeader->qty = $querygetqty->qty;
        }

        $invoiceHeader->save();

        $invoiceHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($invoiceHeader->getTable()),
            'postingdari' => 'ENTRY INVOICE EMKL HEADER',
            'idtrans' => $invoiceHeader->id,
            'nobuktitrans' => $invoiceHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $invoiceHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($invoiceDetail->getTable()),
            'postingdari' => 'ENTRY INVOICE EMKL DETAIL',
            'idtrans' =>  $invoiceHeaderLogTrail->id,
            'nobuktitrans' => $invoiceHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $invoiceDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);
        return $invoiceHeader;
    }

    public function processUpdate(InvoiceEmklHeader $invoiceHeader, array $data): InvoiceEmklHeader
    {
        // DD('TEST');
        $prosesReimburse = $data['prosesreimburse'] ?? 0;

        $statusInvoice = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS INVOICE EMKL')->where('text', 'UTAMA')->first();
        $statusPPN = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS PPN')->where('default', 'YA')->first();
        $idMuatan = DB::table("jenisorder")->from(db::raw("jenisorder with (readuncommitted)"))
            ->where('kodejenisorder', 'MUAT')->first();

        $invoiceHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $invoiceHeader->pelanggan_id = $data['pelanggan_id'];
        $invoiceHeader->jenisorder_id = $data['jenisorder_id'];
        // $invoiceHeader->nobuktiinvoicepajak = $data['nobuktiinvoicepajak'] ?? '';
        // $invoiceHeader->keterangan = $data['keterangan'] ?? '';
        // $invoiceHeader->destination = $data['destination'] ?? '';
        $invoiceHeader->kapal = $data['kapal'] ?? '';
        $invoiceHeader->tujuan_id = $data['tujuan_id'];
        $invoiceHeader->tgldari = ($data['tgldari'] != '') ? date('Y-m-d', strtotime($data['tgldari'])) : null;
        $invoiceHeader->tglsampai = ($data['tglsampai'] != '') ? date('Y-m-d', strtotime($data['tglsampai'])) : null;
        $invoiceHeader->modifiedby = auth('api')->user()->name;
        $invoiceHeader->info = html_entity_decode(request()->info);
        $invoiceHeader->editing_by = '';
        $invoiceHeader->editing_at = null;
        if ($invoiceHeader->tujuan_id != 0) {
            $pelangggan = db::table("tujuan")->from(db::raw("tujuan with (readuncommitted)"))->where('id', $invoiceHeader->tujuan_id)->first()->pelanggan_id ?? 0;
            $data['pelanggan_id'] = $pelangggan;
            $invoiceHeader->pelanggan_id = $pelangggan;
        }

        if (!$invoiceHeader->save()) {
            throw new \Exception("Error updating invoice emkl header.");
        }

        InvoiceEmklDetail::where('invoiceemkl_id', $invoiceHeader->id)->delete();
        InvoiceEmklFifo::where('nobukti', $invoiceHeader->nobukti)->delete();

        $keteranganDetail = [];
        $nominalDetail = [];
        $invoiceNobukti = [];

        $invoiceDetails = [];

        if ($data['jenisorder_id'] == $idMuatan->id) {
            $paramcoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE MUATAN UTAMA')
                ->where('subgrp', 'DEBET')
                ->where('text', 'DEBET')
                ->first();
            $memocoa = json_decode($paramcoa->memo, true);
            $coadebet = $memocoa['JURNAL'];

            $param = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE MUATAN UTAMA')
                ->where('subgrp', 'KREDIT')
                ->where('text', 'KREDIT')
                ->first();
            $memo = json_decode($param->memo, true);
            $coakredit = $memo['JURNAL'];

            $paramppn = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE MUATAN UTAMA')
                ->where('subgrp', 'KREDIT PPN')
                ->where('text', 'KREDIT PPN')
                ->first();
            $memo = json_decode($paramppn->memo, true);
            $coakreditppn = $memo['JURNAL'];
        } else {
            $paramcoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE BONGKARAN UTAMA')
                ->where('subgrp', 'DEBET')
                ->where('text', 'DEBET')
                ->first();
            $memocoa = json_decode($paramcoa->memo, true);
            $coadebet = $memocoa['JURNAL'];

            $param = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE BONGKARAN UTAMA')
                ->where('subgrp', 'KREDIT')
                ->where('text', 'KREDIT')
                ->first();
            $memo = json_decode($param->memo, true);
            $coakredit = $memo['JURNAL'];
        }
        if ($data['statusinvoice'] == $statusInvoice->id && date('m', strtotime($data['tglbukti'])) == date('m', strtotime($data['tgldari']))) {
            $coadebetdetail = $coadebet;
            $coakreditdetail = $coakredit;
        }
        if ($data['statusinvoice'] != $statusInvoice->id) {
            // JURNAL TAMBAHAN BULAN SAMA
            if (date('m', strtotime($data['tglbukti'])) == date('m', strtotime($data['tgldari']))) {

                if ($data['jenisorder_id'] == $idMuatan->id) {
                    $paramcoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE MUATAN UTAMA')
                        ->where('subgrp', 'DEBET')
                        ->where('text', 'DEBET')
                        ->first();
                    $memocoa = json_decode($paramcoa->memo, true);
                    $coadebetdetail = $memocoa['JURNAL'];

                    $param = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE MUATAN UTAMA')
                        ->where('subgrp', 'KREDIT')
                        ->where('text', 'KREDIT')
                        ->first();
                    $memo = json_decode($param->memo, true);
                    $coakreditdetail = $memo['JURNAL'];
                } else {
                    $paramcoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE BONGKARAN UTAMA')
                        ->where('subgrp', 'DEBET')
                        ->where('text', 'DEBET')
                        ->first();
                    $memocoa = json_decode($paramcoa->memo, true);
                    $coadebetdetail = $memocoa['JURNAL'];

                    $param = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE BONGKARAN UTAMA')
                        ->where('subgrp', 'KREDIT')
                        ->where('text', 'KREDIT')
                        ->first();
                    $memo = json_decode($param->memo, true);
                    $coakreditdetail = $memo['JURNAL'];
                }
            } else {

                // JURNAL TAMBAHAN BULAN BEDA
                $paramcoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PIUTANG INVOICE EMKL TAMBAHAN')
                    ->where('subgrp', 'DEBET')
                    ->where('text', 'DEBET')
                    ->first();
                $memocoa = json_decode($paramcoa->memo, true);
                $coadebetdetail = $memocoa['JURNAL'];

                $param = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PIUTANG INVOICE EMKL TAMBAHAN')
                    ->where('subgrp', 'KREDIT')
                    ->where('text', 'KREDIT')
                    ->first();
                $memo = json_decode($param->memo, true);
                $coakreditdetail = $memo['JURNAL'];
            }
        }

        if ($data['statusinvoice'] == $statusInvoice->id && date('m', strtotime($data['tglbukti'])) != date('m', strtotime($data['tgldari']))) {
            $paramcoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PIUTANG INVOICE EMKL KELEBIHAN')
                ->where('subgrp', 'DEBET')
                ->where('text', 'DEBET')
                ->first();
            $memocoa = json_decode($paramcoa->memo, true);
            $coadebetdetailkelebihan = $memocoa['JURNAL'];

            $param = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PIUTANG INVOICE EMKL KELEBIHAN')
                ->where('subgrp', 'KREDIT')
                ->where('text', 'KREDIT')
                ->first();
            $memo = json_decode($param->memo, true);
            $coakreditdetailkelebihan = $memo['JURNAL'];

            $paramcoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PIUTANG INVOICE EMKL KEKURANGAN')
                ->where('subgrp', 'DEBET')
                ->where('text', 'DEBET')
                ->first();
            $memocoa = json_decode($paramcoa->memo, true);
            $coadebetdetailkekurangan = $memocoa['JURNAL'];

            $param = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PIUTANG INVOICE EMKL KEKURANGAN')
                ->where('subgrp', 'KREDIT')
                ->where('text', 'KREDIT')
                ->first();
            $memo = json_decode($param->memo, true);
            $coakreditdetailkekurangan = $memo['JURNAL'];
        }

        if ($prosesReimburse != 0) {
            $idthc = (new Parameter())->cekId('BIAYA REIMBURSE EMKL', 'BIAYA REIMBURSE EMKL', 'THC');
            $idstorage = (new Parameter())->cekId('BIAYA REIMBURSE EMKL', 'BIAYA REIMBURSE EMKL', 'STO');
            $iddemurage = (new Parameter())->cekId('BIAYA REIMBURSE EMKL', 'BIAYA REIMBURSE EMKL', 'STO-DEM');
            if ($data['biaya'] == $idthc) {
                $paramcoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE REIMBURSE OPT')
                    ->where('subgrp', 'DEBET')
                    ->where('text', 'DEBET')
                    ->first();
                $memocoa = json_decode($paramcoa->memo, true);
                $coadebetdetail = $memocoa['JURNAL'];

                $param = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE REIMBURSE OPT')
                    ->where('subgrp', 'KREDIT')
                    ->where('text', 'KREDIT')
                    ->first();
                $memo = json_decode($param->memo, true);
                $coakreditdetail = $memo['JURNAL'];
            }
            if ($data['biaya'] == $idstorage) {
                $paramcoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE REIMBURSE STORAGE')
                    ->where('subgrp', 'DEBET')
                    ->where('text', 'DEBET')
                    ->first();
                $memocoa = json_decode($paramcoa->memo, true);
                $coadebetdetail = $memocoa['JURNAL'];

                $param = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE REIMBURSE STORAGE')
                    ->where('subgrp', 'KREDIT')
                    ->where('text', 'KREDIT')
                    ->first();
                $memo = json_decode($param->memo, true);
                $coakreditdetail = $memo['JURNAL'];
            }
            if ($data['biaya'] == $iddemurage) {
                $paramcoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE REIMBURSE DEMURAGE')
                    ->where('subgrp', 'DEBET')
                    ->where('text', 'DEBET')
                    ->first();
                $memocoa = json_decode($paramcoa->memo, true);
                $coadebetdetail = $memocoa['JURNAL'];

                $param = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL INVOICE REIMBURSE DEMURAGE')
                    ->where('subgrp', 'KREDIT')
                    ->where('text', 'KREDIT')
                    ->first();
                $memo = json_decode($param->memo, true);
                $coakreditdetail = $memo['JURNAL'];
            }
        }

        $total = 0;
        $nojobs = '';
        $nominaljurnal = [];
        $coadebetjurnal = [];
        $coakreditjurnal = [];
        $keteranganjurnal = [];
        for ($i = 0; $i < count($data['nominal']); $i++) {
            $status = [];
            $nominalfifo = [];
            $coadebetfifo = [];
            $biayaemkl_idfifo = [];
            $selisih = 0;
            $jobemkl = '';
            if ($prosesReimburse == 0) {
                $jobemkl = DB::table("jobemkl")->from(DB::raw("jobemkl with (readuncommitted)"))
                    ->where('nobukti', $data['nojobemkl'][$i])->first();
            }
            if ($prosesReimburse == 0) {
                if ($data['statusinvoice'] == $statusInvoice->id && date('m', strtotime($data['tglbukti'])) != date('m', strtotime($data['tgldari']))) {
                    if ($data['jenisorder_id'] == $idMuatan->id) {
                        $nominalawal = db::table("jurnalumumdetail")->from(db::raw("jurnalumumdetail with (readuncommitted)"))
                            ->where('coa', $coakredit)->where('nobukti', $data['nojobemkl'][$i])->first()->nominal ?? 0;
                        $nominalawal = $nominalawal * -1;
                        // } else {
                        //     $nominalawal = db::table("jurnalumumdetail")->from(db::raw("jurnalumumdetail with (readuncommitted)"))
                        //         ->where('coa', $coadebet)->where('nobukti', $data['nojobemkl'][$i])->first()->nominal ?? 0;
                        // }
                        if ($statusPPN->text == 'PPN 1.1%') {
                            $nom = $data['nominal'][$i];
                            $nilaippn = db::select("select (case when (($nom * 0.011) - floor(($nom * 0.011))) >= 0.51 then round(($nom * 0.011),0) else round(($nom * 0.011),0,1) end) as ppn");
                            $nominalppn = $nilaippn[0]->ppn;
                        } else {
                            $nom = $data['nominal'][$i];
                            $nilaippn = db::select("select (case when (($nom * 0.11) - floor(($nom * 0.11))) >= 0.51 then round(($nom * 0.11),0) else round(($nom * 0.11),0,1) end) as ppn");
                            $nominalppn = $nilaippn[0]->ppn;
                        }

                        if ($data['nominal'][$i] > $nominalawal) {
                            $coadebetdetail = $coadebetdetailkelebihan;
                            $coakreditdetail = $coakreditdetailkelebihan;

                            $nominaljurnal[] = $data['nominal'][$i] - $nominalawal;
                            $selisih = $data['nominal'][$i] - $nominalawal;
                            $coadebetjurnal[] = $coadebetdetail;
                            $coakreditjurnal[] = $coakreditdetail;
                            $keteranganjurnal[] = $data['keterangan_detail'][$i] ?? $invoiceHeader->nobukti . ' ' . $jobemkl->nobukti;
                            $status[] = 'normal';
                            $nominalfifo[] = $nominalawal + $nominalppn;
                            $coadebetfifo[] = $coadebet;
                            $status[] = 'selisih';
                            $nominalfifo[] = $selisih;
                            $coadebetfifo[] = $coadebetdetail;
                        }
                        if ($data['nominal'][$i] < $nominalawal) {
                            $coadebetdetail = $coadebetdetailkekurangan;
                            $coakreditdetail = $coakreditdetailkekurangan;

                            $nominaljurnal[] = $nominalawal - $data['nominal'][$i];
                            $selisih = $nominalawal - $data['nominal'][$i];
                            $coadebetjurnal[] = $coadebetdetail;
                            $coakreditjurnal[] = $coakreditdetail;
                            $keteranganjurnal[] = $data['keterangan_detail'][$i] ?? $invoiceHeader->nobukti . ' ' . $jobemkl->nobukti;
                            $status[] = 'normal';
                            $nominalfifo[] = $nominalawal - $selisih + $nominalppn;
                            $coadebetfifo[] = $coadebet;
                            $status[] = 'selisih';
                            $nominalfifo[] = -$selisih;
                            $coadebetfifo[] = $coadebetdetail;
                        }
                        if ($data['nominal'][$i] == $nominalawal) {                            
                            $coadebetdetail = $coadebet;
                            $coakreditdetail = $coakredit;
                            if ($statusPPN->text == 'PPN 1.1%') {
                                $nom = $data['nominal'][$i];
                                $nilaippn = db::select("select (case when (($nom * 0.011) - floor(($nom * 0.011))) >= 0.51 then round(($nom * 0.011),0) else round(($nom * 0.011),0,1) end) as ppn");
                                $nominalppn = $nilaippn[0]->ppn;
                            } else {
                                $nom = $data['nominal'][$i];
                                $nilaippn = db::select("select (case when (($nom * 0.11) - floor(($nom * 0.11))) >= 0.51 then round(($nom * 0.11),0) else round(($nom * 0.11),0,1) end) as ppn");
                                $nominalppn = $nilaippn[0]->ppn;
                            }
                            $status[] = 'normal';
                            $nominalfifo[] = $data['nominal'][$i] + $nominalppn;
                            $coadebetfifo[] = $coadebet;
                        }
                    } else {
                        $coadebetdetail = $coadebet;
                        $coakreditdetail = $coakredit;
                    }
                }

                if ($data['statusinvoice'] == $statusInvoice->id && date('m', strtotime($data['tglbukti'])) == date('m', strtotime($data['tgldari']))) {
                    $status[] = 'normal';
                    if ($data['jenisorder_id'] != 2) {
                        $nominalfifo[] = $data['nominal'][$i];
                    }
                    $coadebetfifo[] = $coadebet;
                }
            }

            $nojobemkl = ($prosesReimburse == 0) ? $jobemkl->nobukti : '';
            if ($data['statusinvoice'] != $statusInvoice->id) {
                $nominaljurnal[] = $data['nominal'][$i];
                $coadebetjurnal[] = $coadebetdetail;
                $coakreditjurnal[] = $coakreditdetail;
                if ($prosesReimburse == 0) {
                    $keteranganjurnal[] =  $invoiceHeader->nobukti . ' ' . $nojobemkl . ' ' . $data['keterangan_detail'][$i];
                } else {
                    $keteranganjurnal[] =  $invoiceHeader->nobuktiinvoicereimbursement . ' ' . $data['keterangan_detail'][$i];
                }
                $status[] = 'normal';
                if ($data['jenisorder_id'] != 2) {
                    $nominalfifo[] = $data['nominal'][$i];
                }

                $coadebetfifo[] = $coadebet;
            }

            $nominalbiaya = $data['nominal'][$i];
            // if ($data['jenisorder_id'] == $idMuatan->id) {
            //     if ($statusPPN->text == 'PPN 1.1%') {

            //         $nilaippn = db::select("select round(($nominalbiaya * 0.011), 0, 1) as ppn");
            //         $nominalppn = $nilaippn[0]->ppn;
            //         $nominalbiaya = $nominalbiaya + $nominalppn;
            //     } else {
            //         $nilaippn = db::select("select round(($nominalbiaya * 0.11), 0, 1) as ppn");
            //         $nominalppn = $nilaippn[0]->ppn;
            //         $nominalbiaya = $nominalbiaya + $nominalppn;
            //     }
            // }
            $invoiceDetail = (new InvoiceEmklDetail())->processStore($invoiceHeader, [
                'nominal' => $nominalbiaya,
                'jobemkl_nobukti' => ($prosesReimburse == 0) ? $jobemkl->nobukti : '',
                'container_id' => ($prosesReimburse == 0) ? $jobemkl->container_id : '',
                'coadebet' => $coadebetdetail,
                'coakredit' => $coakreditdetail,
                'selisih' => $selisih,
                'keterangan' => $data['keterangan_detail'][$i] ?? $invoiceHeader->nobukti . ' ' . $jobemkl->nobukti,
            ]);
            $keterangan_biaya = $data['keterangan_biaya'][$i] ?? '';
            if ($keterangan_biaya != '') {


                $invoiceEmklDetailRincianBiaya = (new InvoiceEmklDetailRincianBiaya())->processStore([
                    'invoiceemkl_id' => $invoiceHeader->id,
                    'invoiceemkldetail_id' => $invoiceDetail->id,
                    'jobemkl_nobukti' => $invoiceDetail->jobemkl_nobukti,
                    'keteranganbiaya' =>  $keterangan_biaya,
                    'nobukti' =>   $invoiceHeader->nobukti,
                    'tglbukti' =>  $data['tglbukti'],
                    'modifiedby' => auth('api')->user()->name,
                ]);
            }
            if ($data['jenisorder_id'] == 2 && $prosesReimburse == 0) {
                $querydetailjob = db::table("invoiceemkldetailrincianbiaya")->from(db::raw("invoiceemkldetailrincianbiaya a with (readuncommitted)"))
                    ->select(
                        db::raw("sum(a.nominal) as nominal")
                    )
                    ->join(db::raw("invoiceemkldetail b with (readuncommitted)"), 'a.invoiceemkldetail_id', 'b.id')
                    ->join(db::raw("invoiceemklheader c with (readuncommitted)"), 'b.invoiceemkl_id', 'c.id')
                    ->where('c.nobukti', $invoiceHeader->nobukti)
                    ->where('b.id', $invoiceDetail->id)
                    ->first();
                $nominalfifo[] = $querydetailjob->nominal ?? 0;
                $status[] = 'normal';
                $coadebetfifo[] = $coadebet;
                $nominaldetail = $querydetailjob->nominal ?? 0;
                db::update("update invoiceemkldetail set nominal=" . $nominaldetail . " where id=" . $invoiceDetail->id . " and nobukti='" . $invoiceHeader->nobukti . "'");

                if ($data['statusinvoice'] == $statusInvoice->id && date('m', strtotime($data['tglbukti'])) != date('m', strtotime($data['tgldari']))) {
                    $getSelisihBiaya = DB::table("jobemklrincianbiaya")->from(db::raw("jobemklrincianbiaya as a with (readuncommitted)"))
                        ->select(db::raw("a.nobukti,a.biayaemkl_id, isnull(a.nominal, 0) as nominalawal, isnull(b.nominal, 0) as nominalrincian, (case when isnull(b.nominal, 0) > isnull(a.nominal, 0) then (isnull(b.nominal,0) - isnull(a.nominal,0)) else (isnull(a.nominal, 0) - isnull(b.nominal, 0)) end) as selisih"))
                        ->join(DB::raw("invoiceemkldetailrincianbiaya as b with (readuncommitted)"), 'a.nobukti', 'b.jobemkl_nobukti')
                        ->where('a.nobukti', $invoiceDetail->jobemkl_nobukti)
                        ->whereRaw("a.biayaemkl_id=b.biayaemkl_id")
                        // ->whereRaw("(isnull(a.nominal, 0) - isnull(b.nominal, 0)) != 0")
                        ->get();
                    $selisihnilaijob = 0;
                    if (count($getSelisihBiaya) > 0) {
                        $nominalfifo = [];
                        $status = [];
                        $coadebetfifo = [];
                        // $getnilaiawal = db::table("jurnalumumdetail")->from(db::raw("jurnalumumdetail with (readuncommitted)"))
                        //     ->select(DB::raw("nominal"))
                        //     ->where('coa', $coadebet)->where('nobukti', $data['nojobemkl'][$i])->get();
                        // for ($j = 0; $j < count($getnilaiawal); $j++) {
                        //     $nominalfifo[] = $getnilaiawal[$j]->nominal;
                        //     $status[] = 'normal';
                        //     $coadebetfifo[] = $coadebet;
                        // }

                        for ($j = 0; $j < count($getSelisihBiaya); $j++) {
                            if ($getSelisihBiaya[$j]->nominalrincian > $getSelisihBiaya[$j]->nominalawal) {

                                $coadebetdetail = $coadebetdetailkelebihan;
                                $coakreditdetail = $coakreditdetailkelebihan;
                                $selisih = $getSelisihBiaya[$j]->nominalrincian - $getSelisihBiaya[$j]->nominalawal;
                                $nominaljurnal[] = $selisih;
                                $coadebetjurnal[] = $coadebetdetail;
                                $coakreditjurnal[] = $coakreditdetail;
                                $keteranganjurnal[] = $data['keterangan_detail'][$i] ?? $invoiceHeader->nobukti . ' ' . $jobemkl->nobukti;
                                $nominalfifo[] = $getSelisihBiaya[$j]->nominalawal;
                                $status[] = 'normal';
                                $coadebetfifo[] = $coadebet;
                                $biayaemkl_idfifo[] =  $getSelisihBiaya[$j]->biayaemkl_id;
                                $status[] = 'selisih';
                                $nominalfifo[] = $selisih;
                                $coadebetfifo[] = $coadebetdetailkelebihan;
                                $biayaemkl_idfifo[] =  $getSelisihBiaya[$j]->biayaemkl_id;
                                $selisihnilaijob += $selisih;
                            }
                            if ($getSelisihBiaya[$j]->nominalrincian < $getSelisihBiaya[$j]->nominalawal) {

                                $coadebetdetail = $coadebetdetailkekurangan;
                                $coakreditdetail = $coakreditdetailkekurangan;
                                $selisih = $getSelisihBiaya[$j]->nominalawal - $getSelisihBiaya[$j]->nominalrincian;
                                $nominaljurnal[] = $selisih;
                                $coadebetjurnal[] = $coadebetdetail;
                                $coakreditjurnal[] = $coakreditdetail;
                                $keteranganjurnal[] = $data['keterangan_detail'][$i] ?? $invoiceHeader->nobukti . ' ' . $jobemkl->nobukti;
                                $nominalfifo[] = $getSelisihBiaya[$j]->nominalawal;
                                $status[] = 'normal';
                                $coadebetfifo[] = $coadebet;
                                $biayaemkl_idfifo[] =  $getSelisihBiaya[$j]->biayaemkl_id;
                                $status[] = 'selisih';
                                $nominalfifo[] = -$selisih;
                                $coadebetfifo[] = $coadebetdetailkekurangan;
                                $biayaemkl_idfifo[] =  $getSelisihBiaya[$j]->biayaemkl_id;
                                $selisihnilaijob -= $selisih;
                            }
                            if ($getSelisihBiaya[$j]->nominalrincian == $getSelisihBiaya[$j]->nominalawal) {
                                $nominalfifo[] = $getSelisihBiaya[$j]->nominalawal;
                                $status[] = 'normal';
                                $coadebetfifo[] = $coadebet;
                                $biayaemkl_idfifo[] =  $getSelisihBiaya[$j]->biayaemkl_id;
                            }
                        }
                    }

                    db::update("update invoiceemkldetail set selisih=" . $selisihnilaijob . " where id=" . $invoiceDetail->id . " and nobukti='" . $invoiceHeader->nobukti . "'");
                }
            }

            $nojob_emkl = ($jobemkl != '') ? $jobemkl->nobukti : '';
            for ($a = 0; $a < count($nominalfifo); $a++) {

                $invoiceDetail = (new InvoiceEmklFifo())->processStore([
                    'nobukti' => $invoiceHeader->nobukti,
                    'jobemkl_nobukti' => $nojob_emkl,
                    'status' => $status[$a] ?? '',
                    'nominal' => $nominalfifo[$a],
                    'coadebet' => $coadebetfifo[$a] ?? '',
                    'biayaemkl_id' => $biayaemkl_idfifo[$a] ?? '',
                    'nominalpelunasan' => 0
                ]);
            }
            $total = $total + $data['nominal'][$i];
            if ($prosesReimburse == 0) {
                $nojobs .= ' ' . $jobemkl->nobukti . ',';
            }
            $invoiceDetails[] = $invoiceDetail->toArray();
            // STORE 
            $coakredit_detail = [];
            $coakreditextra_detail = [];
            $coadebet_detail = [];
            $nominal_detail = [];
            $keterangan_detail = [];
            $nominal_ppn = [];
            $nominal_total = [];
            if ($data['statusinvoice'] == $statusInvoice->id  && date('m', strtotime($data['tglbukti'])) == date('m', strtotime($data['tgldari']))) {
                if ($data['jenisorder_id'] == $idMuatan->id) {
                    $coadebet_detail[] = $coadebet;
                    $coakredit_detail[] = $coakreditppn;
                    $coakreditextra_detail[] = $coakredit;
                    $nominal_detail[] = $data['nominal'][$i];
                    $keterangan_detail[] =  $data['keterangan_detail'][$i] ?? $invoiceHeader->nobukti . ' ' . $jobemkl->nobukti;
                    if ($statusPPN->text == 'PPN 1.1%') {
                        $nominalppn = round($data['nominal'][$i] * 0.011);
                    } else {
                        $nominalppn = round($data['nominal'][$i] * 0.11);
                    }
                    $nominal_ppn[] = $nominalppn;
                    $nominal_total[] = $data['nominal'][$i] + $nominalppn;
                    $jurnalRequest = [
                        'tanpaprosesnobukti' => 1,
                        'multikredit' => 1,
                        'nobukti' => $jobemkl->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                        'postingdari' => 'ENTRY INVOICE EMKL',
                        'statusformat' => "0",
                        'coakredit_detail' => $coakredit_detail,
                        'coadebet_detail' => $coadebet_detail,
                        'coakreditextra_detail' => $coakreditextra_detail,
                        'nominal_detail' => $nominal_detail,
                        'nominal_ppn' => $nominal_ppn,
                        'nominal_total' => $nominal_total,
                        'keterangan_detail' => $keterangan_detail
                    ];


                    $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $jobemkl->nobukti)->first();
                    if ($getJurnal != '') {

                        $newJurnal = new JurnalUmumHeader();
                        $newJurnal = $newJurnal->find($getJurnal->id);
                        (new JurnalUmumHeader())->processUpdate($newJurnal, $jurnalRequest);
                    } else {
                        (new JurnalUmumHeader())->processStore($jurnalRequest);
                    }
                } else {
                    $coadebet_detail[] = $coadebet;
                    $coakredit_detail[] = $coakredit;
                    $nominal_detail[] = $data['nominal'][$i];
                    $keterangan_detail[] =   $invoiceHeader->nobukti . ' ' . $jobemkl->nobukti . ' ' . $data['keterangan_detail'][$i];
                    $jurnalRequest = [
                        'tanpaprosesnobukti' => 1,
                        'nobukti' => $jobemkl->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                        'postingdari' => 'ENTRY INVOICE EMKL',
                        'statusformat' => "0",
                        'coakredit_detail' => $coakredit_detail,
                        'coadebet_detail' => $coadebet_detail,
                        'nominal_detail' => $nominal_detail,
                        'keterangan_detail' => $keterangan_detail
                    ];

                    $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $jobemkl->nobukti)->first();
                    if ($getJurnal != '') {

                        $newJurnal = new JurnalUmumHeader();
                        $newJurnal = $newJurnal->find($getJurnal->id);
                        (new JurnalUmumHeader())->processUpdate($newJurnal, $jurnalRequest);
                    } else {
                        (new JurnalUmumHeader())->processStore($jurnalRequest);
                    }
                }
            }
        }
        $tempkapal = '##tempkapal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempkapal, function ($table) {
            $table->longtext('kapal')->nullable();
        });

        $tempdestination = '##tempdestination' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdestination, function ($table) {
            $table->longtext('destination')->nullable();
        });

        $querykapal = db::table("invoiceemkldetail")->from(db::raw("invoiceemkldetail a with (readuncommitted)"))
            ->select(
                'b.kapal'
            )
            ->join(db::raw("jobemkl b"), 'a.jobemkl_nobukti', 'b.nobukti')
            ->join(db::raw("invoiceemklheader c"), 'a.nobukti', 'c.nobukti')
            ->where('c.nobukti', $invoiceHeader->nobukti)
            ->groupby('b.kapal');


        DB::table($tempkapal)->insertUsing([
            'kapal',
        ], $querykapal);

        $querydestination = db::table("invoiceemkldetail")->from(db::raw("invoiceemkldetail a with (readuncommitted)"))
            ->select(
                'b.destination'
            )
            ->join(db::raw("jobemkl b"), 'a.jobemkl_nobukti', 'b.nobukti')
            ->join(db::raw("invoiceemklheader c"), 'a.nobukti', 'c.nobukti')
            ->where('c.nobukti', $invoiceHeader->nobukti)
            ->groupby('b.destination');


        DB::table($tempdestination)->insertUsing([
            'destination',
        ], $querydestination);

        $kapal = db::table($tempkapal)->from(db::raw($tempkapal . " a "))
            ->select(
                db::raw("string_agg(cast(a.kapal as nvarchar(max)),', ') as kapal"),
            )
            ->first()->kapal ?? '';

        $destination = db::table($tempdestination)->from(db::raw($tempdestination . " a "))
            ->select(
                db::raw("string_agg(cast(a.destination as nvarchar(max)),', ') as destination"),
            )
            ->first()->destination ?? '';

        // dd($kapal,$destination, $invoiceHeader->nobukti);
        $invoiceHeader->destination = $destination ?? '';
        $invoiceHeader->kapal = $kapal ?? '';
        

        if ($invoiceHeader->statusinvoice == $statusInvoice->id) {
            $keteranganDetail[] =  "TAGIHAN INVOICE EMKL " . $invoiceHeader->nobukti . " " . $nojobs;
        } else {
            if ($prosesReimburse != 0) {
                $keteranganDetail[] = "TAGIHAN INVOICE REIMBURSEMENT " . $invoiceHeader->nobuktiinvoicereimbursement;
            } else {
                $keteranganDetail[] = "TAGIHAN INVOICE EMKL TAMBAHAN " . $invoiceHeader->nobukti . " " . $nojobs;
            }
        }
        $nominalDetail[] = $total;
        $invoiceNobukti[] =  $invoiceHeader->nobukti;
        $nominalppn = 0;

        if ($prosesReimburse == 0) {
            if ($invoiceHeader->jenisorder_id == $idMuatan->id) {
                $nominalDetail = [];
                if ($statusPPN->text == 'PPN 1.1%') {
                    $nilaippn = db::select("select (case when (($total * 0.011) - floor(($total * 0.011))) >= 0.51 then round(($total * 0.011),0) else round(($total * 0.011),0,1) end) as ppn");
                    $nominalppn = $nilaippn[0]->ppn;
                    $nominalDetail[] = $total + $nominalppn;
                } else {
                    $nilaippn = db::select("select (case when (($total * 0.11) - floor(($total * 0.11))) >= 0.51 then round(($total * 0.11),0) else round(($total * 0.11),0,1) end) as ppn");
                    $nominalppn = $nilaippn[0]->ppn;
                    $nominalDetail[] = $total + $nominalppn;
                }
            }
        }
        $invoiceHeader->nominalppn = $nominalppn;
        $invoiceHeader->save();

        if ($data['statusinvoice'] != $statusInvoice->id) {
            if($data['jenisorder_id'] == 1 && count($coadebetjurnal) > 0){
                $nominaljurnal[] = $nominalppn;
                $coadebetjurnal[] = $coadebetjurnal[0];
                $coakreditjurnal[] = $coakreditjurnal[0];
                $keteranganjurnal[] = 'PPN ATAS INVOICE PAJAK '. $invoiceHeader->nobuktiinvoicepajak;
            }
        }
        $invoiceRequest = [
            'tgljatuhtempo' => date('Y-m-d', strtotime($data['tglbukti'])),
            'postingdari' => 'EDIT INVOICE EMKL',
            'tglbukti' => $invoiceHeader->tglbukti,
            'invoice' => $invoiceHeader->nobukti,
            'pelanggan_id' => $invoiceHeader->pelanggan_id,
            'invoice_nobukti' => $invoiceNobukti,
            'nominal_detail' => $nominalDetail,
            'keterangan_detail' => $keteranganDetail,
            'nominaljurnal' => $nominaljurnal,
            'coadebetjurnal' => $coadebetjurnal,
            'coakreditjurnal' => $coakreditjurnal,
            'keteranganjurnal' => $keteranganjurnal
        ];


        if ($data['statusinvoice'] == $statusInvoice->id && date('m', strtotime($data['tglbukti'])) == date('m', strtotime($data['tgldari']))) {
            $invoiceRequest['jenis'] = 'emklutama';
        }
        if ($data['statusinvoice'] == $statusInvoice->id && date('m', strtotime($data['tglbukti'])) != date('m', strtotime($data['tgldari']))) {
            $invoiceRequest['jenis'] = 'emklutamabedabulan';
        }
        if ($data['statusinvoice'] != $statusInvoice->id) {
            $invoiceRequest['jenis'] = 'emkltambahan';
        }


        $getPiutang = PiutangHeader::from(DB::raw("piutangheader with (readuncommitted)"))->where('invoice_nobukti', $invoiceHeader->nobukti)->first();
        $newPiutang = new PiutangHeader();
        $newPiutang = $newPiutang->findUpdate($getPiutang->id);
        $piutangHeader = (new PiutangHeader())->processUpdate($newPiutang, $invoiceRequest);


        if ($prosesReimburse == 0) {
            $tempqty = '##tempqty' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempqty, function ($table) {
                $table->string('nobukti', 50)->nullable();
                $table->string('kodecontainer', 50)->nullable();
                $table->string('qty', 50)->nullable();
            });
            $querygetcont = DB::table("invoiceemkldetail")->from(DB::raw("invoiceemkldetail with (readuncommitted)"))
                ->select(DB::raw("invoiceemkldetail.nobukti, container.kodecontainer, CAST(COUNT(invoiceemkldetail.container_id) AS VARCHAR) AS qty"))

                ->join(db::raw("container with (readuncommitted)"), 'invoiceemkldetail.container_id', 'container.id')
                ->where('invoiceemkldetail.nobukti', $invoiceHeader->nobukti)
                ->groupBy('invoiceemkldetail.nobukti', 'container.kodecontainer');

            DB::table($tempqty)->insertUsing([
                'nobukti',
                'kodecontainer',
                'qty'
            ], $querygetcont);

            $querygetqty = db::table($tempqty)->from(db::raw("$tempqty with (readuncommitted)"))
                ->select(DB::raw("nobukti, STRING_AGG(qty + 'x' + kodecontainer, ', ') AS qty"))
                ->groupBy('nobukti')
                ->first();

            $invoiceHeader->qty = $querygetqty->qty;
        }

        $invoiceHeader->piutang_nobukti = $piutangHeader->nobukti;
        $invoiceHeader->save();

        $invoiceHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($invoiceHeader->getTable()),
            'postingdari' => 'EDIT INVOICE EMKL HEADER',
            'idtrans' => $invoiceHeader->id,
            'nobuktitrans' => $invoiceHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $invoiceHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($invoiceDetail->getTable()),
            'postingdari' => 'EDIT INVOICE EMKL DETAIL',
            'idtrans' =>  $invoiceHeaderLogTrail->id,
            'nobuktitrans' => $invoiceHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $invoiceDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);

        return $invoiceHeader;
    }

    public function processDestroy($id, $postingDari = ''): InvoiceEmklHeader
    {
        $invoiceDetails = InvoiceEmklDetail::lockForUpdate()->where('invoiceemkl_id', $id)->get();

        $invoiceHeader = new InvoiceEmklHeader();
        $invoiceHeader = $invoiceHeader->lockAndDestroy($id);

        $invoiceHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => $invoiceHeader->getTable(),
            'postingdari' => $postingDari,
            'idtrans' => $invoiceHeader->id,
            'nobuktitrans' => $invoiceHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $invoiceHeader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'INVOICEEMKLDETAIL',
            'postingdari' => $postingDari,
            'idtrans' => $invoiceHeaderLogTrail['id'],
            'nobuktitrans' => $invoiceHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $invoiceDetails->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        DB::table('invoiceemklfifo')->where('nobukti', $invoiceHeader->nobukti)->delete();
        $getPiutang = PiutangHeader::from(DB::raw("piutangheader with (readuncommitted)"))->where('invoice_nobukti', $invoiceHeader->nobukti)->first();
        (new PiutangHeader())->processDestroy($getPiutang->id, $postingDari);
        return $invoiceHeader;
    }
    public function getExport($id)
    {
        $this->setRequestParameters();
        $getBank = (new Parameter())->cekText('FOOTER INVOICE EMKL', 'BANK');
        $getAn = (new Parameter())->cekText('FOOTER INVOICE EMKL', 'PERUSAHAAN');
        $getLokasi = (new Parameter())->cekText('FOOTER INVOICE EMKL', 'LOKASI');
        $getAdmin1 = (new Parameter())->cekText('FOOTER INVOICE EMKL', 'ADMIN1');
        $getAdmin2 = (new Parameter())->cekText('FOOTER INVOICE EMKL', 'ADMIN2');
        $getkota = (new Parameter())->cekText('FOOTER INVOICE EMKL', 'KOTA');

        $query = DB::table($this->table)->from(DB::raw("invoiceemklheader with (readuncommitted)"))
            ->select(
                'invoiceemklheader.id',
                'invoiceemklheader.nobukti',
                'invoiceemklheader.nobuktiinvoicetambahan',
                'invoiceemklheader.nobuktiinvoicereimbursement',
                'invoiceemklheader.nobuktiinvoicepajak',
                DB::raw("'" . $getkota . ",'+
                format(invoiceemklheader.tglbukti,'dd ')+
                (case when month(invoiceemklheader.tglbukti)=1 then 'JANUARI'
                      when month(invoiceemklheader.tglbukti)=2 then 'FEBRUARI'
                      when month(invoiceemklheader.tglbukti)=3 then 'MARET'
                      when month(invoiceemklheader.tglbukti)=4 then 'APRIL'
                      when month(invoiceemklheader.tglbukti)=5 then 'MEI'
                      when month(invoiceemklheader.tglbukti)=6 then 'JUNI'
                      when month(invoiceemklheader.tglbukti)=7 then 'JULI'
                      when month(invoiceemklheader.tglbukti)=8 then 'AGUSTUS'
                      when month(invoiceemklheader.tglbukti)=9 then 'SEPTEMBER'
                      when month(invoiceemklheader.tglbukti)=10 then 'OKTOBER'
                      when month(invoiceemklheader.tglbukti)=11 then 'NOVEMBER'
                      when month(invoiceemklheader.tglbukti)=12 then 'DESEMBER' ELSE '' END)

                +format(invoiceemklheader.tglbukti,' yyyy') 
                 as tglbukti"),
                'invoiceemklheader.qty',
                'invoiceemklheader.nobuktiinvoicepajak',
                'invoiceemklheader.nobuktiinvoicenonpajak',
                // 'invoiceemklheader.nobuktiinvoicereimbursement',
                db::raw("isnull(invoiceemklheader.nobuktiinvoicereimbursement, '') as nobuktiinvoicereimbursement"),
                'invoiceemklheader.destination',
                'invoiceemklheader.kapal',
                'invoiceemklheader.nominalppn',
                db::raw("isnull(nominalppn,0) as nominalppn"),
                'pelanggan.namapelanggan as pelanggan',
                'tujuan.keterangan as tujuan',
                'jenisorder.keterangan as jenisorder',
                'statuspajak.text as statuspajak',
                'statusformatreimbursement.text as statusformatreimbursement',
                'statusinvoice.text as statusinvoice',
                'statuscetak.memo as statuscetak',
                'statuscetak.id as  statuscetak_id',
                DB::raw("(case when isnull(invoiceemklheader.nobuktiinvoicereimbursement,'') <> '' then 'INVOICE REIMBURSEMENT' else 
                    (case when invoiceemklheader.jenisorder_id=1 then 'KWITANSI / INVOICE' else 'INVOICE TAGIHAN' end) end) as judulLaporan"),
                DB::raw("'" . $getBank . "' as footerbank"),
                DB::raw("'" . $getAn . "' as footerperusahaan"),
                DB::raw("'" . $getLokasi . "' as footerlokasi"),
                DB::raw("'" . $getAdmin1 . "' as admin1"),
                DB::raw("'" . $getAdmin2 . "' as admin2"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("parameter as statuspajak with (readuncommitted)"), 'invoiceemklheader.statuspajak', 'statuspajak.id')
            ->leftJoin(DB::raw("parameter as statusformatreimbursement with (readuncommitted)"), 'invoiceemklheader.statusformatreimbursement', 'statusformatreimbursement.id')
            ->leftJoin(DB::raw("parameter as statusppn with (readuncommitted)"), 'invoiceemklheader.statusppn', 'statusppn.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'invoiceemklheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("parameter as statusinvoice with (readuncommitted)"), 'invoiceemklheader.statusinvoice', 'statusinvoice.id')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'invoiceemklheader.pelanggan_id', 'pelanggan.id')
            ->leftJoin(DB::raw("tujuan with (readuncommitted)"), 'invoiceemklheader.tujuan_id', 'tujuan.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'invoiceemklheader.jenisorder_id', 'jenisorder.id');

        $data = $query->first();
        return $data;
    }
}
