<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InvoiceChargeGandenganHeader extends MyModel
{
    use HasFactory;

    protected $table = 'invoicechargegandenganheader';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];


    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table);
        $query = $this->selectColumns($query)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )

            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'invoicechargegandenganheader.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("parameter as cetak with (readuncommitted)"), 'invoicechargegandenganheader.statuscetak', 'cetak.id')

            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'invoicechargegandenganheader.agen_id', 'agen.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }


    public function cekvalidasiaksi($nobukti)
    {

        $pelunasanPiutang = DB::table('pelunasanpiutangdetail')
            ->from(
                DB::raw("pelunasanpiutangdetail as a with (readuncommitted)")
            )
            ->select(
                'a.invoice_nobukti'
            )
            ->where('a.invoice_nobukti', '=', $nobukti)
            ->first();
        if (isset($pelunasanPiutang)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pelunasan Piutang',
                'kodeerror' => 'SATL'
            ];
            goto selesai;
        }

        $hutangBayar = DB::table('invoicechargegandenganheader')
            ->from(
                DB::raw("invoicechargegandenganheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'a.piutang_nobukti', 'b.nobukti')
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($hutangBayar)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Approval Jurnal',
                'kodeerror' => 'SAP'
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

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                "$this->table.id",
                "$this->table.nobukti",
                "$this->table.tglbukti",
                "$this->table.tglproses",
                "$this->table.tgljatuhtempo",
                "$this->table.agen_id",
                "$this->table.nominal",
                "$this->table.piutang_nobukti",
                "$this->table.statusapproval as statusapproval_id",
                "$this->table.userapproval",
                "$this->table.statusformat as statusformat_id",
                "$this->table.statuscetak as statuscetak_id",
                "$this->table.userbukacetak",
                "$this->table.jumlahcetak",
                "$this->table.modifiedby",
                "agen.namaagen as  agen",
                "parameter.memo as statusapproval",
                "cetak.memo as statuscetak",
                "$this->table.created_at",
                "$this->table.updated_at",
                DB::raw('(case when (year(invoicechargegandenganheader.tglapproval) <= 2000) then null else invoicechargegandenganheader.tglapproval end ) as tglapproval'),
                DB::raw('(case when (year(invoicechargegandenganheader.tglbukacetak) <= 2000) then null else invoicechargegandenganheader.tglbukacetak end ) as tglbukacetak'),



            );
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {

            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->nullable();
            $table->date('tglproses')->nullable();
            $table->date('tgljatuhtempo')->nullable();
            $table->unsignedBigInteger('agen_id')->nullable();
            $table->double('nominal')->nullable();
            $table->integer('statuscetak')->length(11)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->dateTime('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->length(11)->nullable();
            $table->integer('statusapproval')->length(11)->nullable();
            $table->string('userapproval', 50)->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $query = DB::table($modelTable);
        $query = $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                "$this->table.id",
                "$this->table.nobukti",
                "$this->table.tglbukti",
                "$this->table.tglproses",
                "$this->table.tgljatuhtempo",
                "$this->table.agen_id",
                "$this->table.nominal",
                "$this->table.statusapproval",
                "$this->table.userapproval",
                "$this->table.statusformat",
                "$this->table.userbukacetak",
                "$this->table.tglbukacetak",
                "$this->table.jumlahcetak",
                "$this->table.modifiedby"
            );
        if ((date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tgldariheader))) || (date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tglsampaiheader)))) {
            request()->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            request()->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
        }
        $query = $this->sort($query);
        $models = $this->filter($query);
        $models =  $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);

        DB::table($temp)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'tglproses',
            'tgljatuhtempo',
            'agen_id',
            'nominal',
            'statusapproval',
            'userapproval',
            'statusformat',
            'userbukacetak',
            'tglbukacetak',
            'jumlahcetak',
            'modifiedby',
        ], $models);

        return $temp;
    }

    public function find($id)
    {
        $this->setRequestParameters();

        $query = DB::table($this->table);
        $query = $this->selectColumns($query)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'invoicechargegandenganheader.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statusformat with (readuncommitted)"), 'invoicechargegandenganheader.statusformat', 'statusformat.id')
            ->leftJoin(DB::raw("parameter as cetak with (readuncommitted)"), 'invoicechargegandenganheader.statuscetak', 'cetak.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'invoicechargegandenganheader.agen_id', 'agen.id');

        $data = $query->where("$this->table.id", $id)->first();
        return $data;
    }


    public function getInvoiceGandengan($id)
    {
        $query = DB::table('invoicechargegandengandetail')->from(DB::raw("invoicechargegandengandetail with (readuncommitted)"));

        $query->select(
            'invoicechargegandengandetail.id',
            'header.nobukti as nobukti_header',
            'header.tglbukti',
            'header.nominal as nominal_header',
            'invoicechargegandengandetail.jobtrucking',
            'invoicechargegandengandetail.trado_id',
            'invoicechargegandengandetail.gandengan_id',
            'invoicechargegandengandetail.tgltrip',
            'invoicechargegandengandetail.tglakhir as tglkembali',
            'invoicechargegandengandetail.jumlahhari',
            'invoicechargegandengandetail.nominal as nominal_detail',
            'invoicechargegandengandetail.jenisorder as jenisorder',
            'invoicechargegandengandetail.namagudang as namagudang',
            'trado.kodetrado as nopolisi',
            'gandengan.kodegandengan as gandengan',
            'invoicechargegandengandetail.keterangan',
        )
            ->leftJoin(DB::raw("invoicechargegandenganheader as header with (readuncommitted)"), 'header.id', 'invoicechargegandengandetail.invoicechargegandengan_id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'trado.id', 'invoicechargegandengandetail.trado_id')
            ->leftJoin(DB::raw("gandengan with (readuncommitted)"), 'gandengan.id', 'invoicechargegandengandetail.gandengan_id');


        $query->where('invoicechargegandengandetail.invoicechargegandengan_id', '=', $id);
        return $query->get();
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'agen') {
            return $query->orderBy('agen.namaagen', $this->params['sortOrder']);
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
                        if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('parameter.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statuscetak') {
                            $query = $query->where('cetak.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'agen') {
                            $query = $query->orWhere('agen.namaagen', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'nominal') {
                            $query = $query->whereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglproses' || $filters['field'] == 'tglbukacetak' || $filters['field'] == 'tglapproval') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }
                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statusapproval') {
                                $query = $query->orWhere('parameter.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'statuscetak') {
                                $query = $query->orWhere('cetak.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'agen') {
                                $query = $query->orWhere('agen.namaagen', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->orWhereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglproses' || $filters['field'] == 'tglbukacetak' || $filters['field'] == 'tglapproval') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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
        if (request()->approve && request()->periode) {
            $query->where('invoicechargegandenganheader.statusapproval', '<>', request()->approve)
                ->whereYear('invoicechargegandenganheader.tglbukti', '=', request()->year)
                ->whereMonth('invoicechargegandenganheader.tglbukti', '=', request()->month);
            return $query;
        }
        if (request()->cetak && request()->periode) {
            $query->where('invoicechargegandenganheader.statuscetak', '<>', request()->cetak)
                ->whereYear('invoicechargegandenganheader.tglbukti', '=', request()->year)
                ->whereMonth('invoicechargegandenganheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function getExport($id)
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($this->table)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            "$this->table.id",
            "$this->table.nobukti",
            "$this->table.tglbukti",
            "$this->table.tglproses",
            "$this->table.agen_id",
            "$this->table.nominal",
            "$this->table.jumlahcetak",
            "agen.namaagen as  agen",
            "parameter.memo as statusapproval",
            'statuscetak.memo as statuscetak',
            'statuscetak.id as  statuscetak_id',
            DB::raw("'Laporan Charge Gandengan' as judulLaporan"),
            DB::raw("'" . $getJudul->text . "' as judul"),
            DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
        )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'invoicechargegandenganheader.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'invoicechargegandenganheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'invoicechargegandenganheader.agen_id', 'agen.id');

        $data = $query->first();
        return $data;
    }

    public function processStore(array $data): InvoiceChargeGandenganHeader
    {

        $group = 'INVOICE CHARGE GANDENGAN';
        $subGroup = 'INVOICE CHARGE GANDENGAN';

        $format = DB::table('parameter')
            ->where('grp', $group)
            ->where('subgrp', $subGroup)
            ->first();

        $invoiceChargeGandenganHeader = new InvoiceChargeGandenganHeader();

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

        $statusCetak = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $invoiceChargeGandenganHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $invoiceChargeGandenganHeader->agen_id = $data['agen_id'];
        $invoiceChargeGandenganHeader->tglproses = date('Y-m-d', strtotime($data['tglproses']));
        $invoiceChargeGandenganHeader->tgljatuhtempo = date('Y-m-d', strtotime($data['tgljatuhtempo']));
        $invoiceChargeGandenganHeader->statusapproval = $statusApproval->id;
        $invoiceChargeGandenganHeader->statuscetak = $statusCetak->id;
        $invoiceChargeGandenganHeader->nominal = array_sum($data['nominal_detail']);
        $invoiceChargeGandenganHeader->modifiedby = auth('api')->user()->name;
        $invoiceChargeGandenganHeader->info = html_entity_decode(request()->info);
        $invoiceChargeGandenganHeader->statusformat = $format->id;
        $invoiceChargeGandenganHeader->nobukti = (new RunningNumberService)->get($group, $subGroup, $invoiceChargeGandenganHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$invoiceChargeGandenganHeader->save()) {
            throw new \Exception("Error storing invoice charge gandengan header.");
        }

        $invoiceChargeGandenganDetails = [];

        for ($i = 0; $i < count($data['nominal_detail']); $i++) {

            $invoiceChargeGandenganDetail = (new InvoiceChargeGandenganDetail())->processStore($invoiceChargeGandenganHeader, [
                "jobtrucking_detail" => $data['jobtrucking_detail'][$i],
                "trado_id" => $data['nopolisi_detail'][$i],
                "gandengan_id" => $data['gandengan_detail'][$i],
                "tgltrip_detail" => date('Y-m-d', strtotime($data['tgltrip_detail'][$i])),
                "tglkembali_detail" => date('Y-m-d', strtotime($data['tglkembali_detail'][$i])),
                "jumlahhari_detail" => $data['jumlahhari_detail'][$i],
                "nominal_detail" => $data['nominal_detail'][$i],
                "keterangan_detail" => $data['keterangan_detail'][$i],
                "jenisorder_detail" => $data['jenisorder_detail'][$i],
                "namagudang_detail" => $data['namagudang_detail'][$i],
            ]);

            $keteranganDetail[] = 'INVOICE CHARGE GANDENGAN PADA ORDERAN TRUCKING ' . $data['jobtrucking_detail'][$i];
            $nominalDetail[] =  $data['nominal_detail'][$i];
            $invoiceNobukti[] =  $invoiceChargeGandenganHeader->nobukti;
            $invoiceChargeGandenganDetails[] = $invoiceChargeGandenganDetail->toArray();
        }
        $invoiceRequest = [
            'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
            'tgljatuhtempo' => date('Y-m-d', strtotime($data['tgljatuhtempo'])),
            'postingdari' => 'ENTRY INVOICE CHARGE GANDENGAN',
            'invoice' => $invoiceChargeGandenganHeader->nobukti,
            'agen_id' => $data['agen_id'],
            'jenis' => 'extra',
            'invoice_nobukti' => $invoiceNobukti,
            'nominal_detail' => $nominalDetail,
            'keterangan_detail' => $keteranganDetail,
        ];
        $piutangHeader = (new PiutangHeader())->processStore($invoiceRequest);
        $invoiceChargeGandenganHeader->piutang_nobukti = $piutangHeader->nobukti;
        $invoiceChargeGandenganHeader->save();


        $invoiceChargeGandenganHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($invoiceChargeGandenganHeader->getTable()),
            'postingdari' => 'ENTRY INVOICE CHARGE GANDENGAN HEADER',
            'idtrans' => $invoiceChargeGandenganHeader->id,
            'nobuktitrans' => $invoiceChargeGandenganHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $invoiceChargeGandenganHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($invoiceChargeGandenganDetail->getTable()),
            'postingdari' =>  'ENTRY INVOICE CHARGE GANDENGAN DETAIL',
            'idtrans' =>  $invoiceChargeGandenganHeaderLogTrail->id,
            'nobuktitrans' => $invoiceChargeGandenganHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $invoiceChargeGandenganDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);

        return $invoiceChargeGandenganHeader;
    }

    public function processUpdate(InvoiceChargeGandenganHeader $invoiceChargeGandenganHeader, array $data): InvoiceChargeGandenganHeader
    {
        $nobuktiOld = $invoiceChargeGandenganHeader->nobukti;
        $getTgl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'EDIT TANGGAL BUKTI')->where('subgrp', 'INVOICE CHARGE')->first();
        if (trim($getTgl->text) == 'YA') {
            $group = 'INVOICE CHARGE GANDENGAN';
            $subGroup = 'INVOICE CHARGE GANDENGAN';
            $querycek = DB::table('invoicechargegandenganheader')->from(
                DB::raw("invoicechargegandenganheader a with (readuncommitted)")
            )
                ->select(
                    'a.nobukti'
                )
                ->where('a.id', $invoiceChargeGandenganHeader->id)
                ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
                ->first();

            if (isset($querycek)) {
                $nobukti = $querycek->nobukti;
            } else {
                $nobukti = (new RunningNumberService)->get($group, $subGroup, $invoiceChargeGandenganHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
            }
            $invoiceChargeGandenganHeader->nobukti = $nobukti;
            $invoiceChargeGandenganHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        }

        $invoiceChargeGandenganHeader->agen_id = $data['agen_id'];
        $invoiceChargeGandenganHeader->tglproses = date('Y-m-d', strtotime($data['tglproses']));
        $invoiceChargeGandenganHeader->modifiedby = auth('api')->user()->name;
        $invoiceChargeGandenganHeader->info = html_entity_decode(request()->info);
        $invoiceChargeGandenganHeader->nominal = array_sum($data['nominal_detail']);

        if (!$invoiceChargeGandenganHeader->save()) {
            throw new \Exception("Error updating invoice charge header.");
        }


        InvoiceChargeGandenganDetail::where('invoicechargegandengan_id', $invoiceChargeGandenganHeader->id)->delete();

        $invoiceChargeGandenganDetails = [];
        for ($i = 0; $i < count($data['nominal_detail']); $i++) {
            // $trado = Trado::where('kodetrado', $data['nopolisi_detail'][$i])->first();

            $invoiceChargeGandenganDetail = (new InvoiceChargeGandenganDetail())->processStore($invoiceChargeGandenganHeader, [
                // "jobtrucking_detail" => $data['jobtrucking_detail'][$i],
                // "tgltrip_detail" => $data['tgltrip_detail'][$i],
                // "jumlahhari_detail" => $data['jumlahhari_detail'][$i],
                // "trado_id" => $trado->id,
                // "nominal_detail" => $data['nominal_detail'][$i],
                // "keterangan_detail" => $data['keterangan_detail'][$i],

                "jobtrucking_detail" => $data['jobtrucking_detail'][$i],
                "trado_id" => $data['nopolisi_detail'][$i],
                "gandengan_id" => $data['gandengan_detail'][$i],
                "tgltrip_detail" => date('Y-m-d', strtotime($data['tgltrip_detail'][$i])),
                "tglkembali_detail" => date('Y-m-d', strtotime($data['tglkembali_detail'][$i])),
                "jumlahhari_detail" => $data['jumlahhari_detail'][$i],
                "nominal_detail" => $data['nominal_detail'][$i],
                "keterangan_detail" => $data['keterangan_detail'][$i],
                "jenisorder_detail" => $data['jenisorder_detail'][$i],
                "namagudang_detail" => $data['namagudang_detail'][$i],
            ]);
            $keteranganDetail[] = 'INVOICE CHARGE GANDENGAN PADA ORDERAN TRUCKING ' . $data['jobtrucking_detail'][$i];
            $nominalDetail[] =  $data['nominal_detail'][$i];
            $invoiceNobukti[] =  $invoiceChargeGandenganHeader->nobukti;
            $invoiceChargeGandenganDetails[] = $invoiceChargeGandenganDetail->toArray();
        }

        $invoiceRequest = [
            'postingdari' => 'EDIT INVOICE CHARGE GANDENGAN',
            'invoice' => $invoiceChargeGandenganHeader->nobukti,
            'tgljatuhtempo' => date('Y-m-d', strtotime($data['tgljatuhtempo'])),
            'tglbukti' => $invoiceChargeGandenganHeader->tglbukti,
            'agen_id' => $data['agen_id'],
            'invoice_nobukti' => $invoiceNobukti,
            'nominal_detail' => $nominalDetail,
            'keterangan_detail' => $keteranganDetail,
        ];
        $getPiutang = PiutangHeader::from(DB::raw("piutangheader with (readuncommitted)"))->where('invoice_nobukti', $invoiceChargeGandenganHeader->nobukti)->first();
        $newPiutang = new PiutangHeader();
        $newPiutang = $newPiutang->findUpdate($getPiutang->id);
        $piutangHeader = (new PiutangHeader())->processUpdate($newPiutang, $invoiceRequest);
        $invoiceChargeGandenganHeader->piutang_nobukti = $piutangHeader->nobukti;
        $invoiceChargeGandenganHeader->save();

        $invoiceChargeGandenganHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($invoiceChargeGandenganHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? 'EDIT INVOICE CHARGE GANDENGAN HEADER',
            'idtrans' => $invoiceChargeGandenganHeader->id,
            'nobuktitrans' => $invoiceChargeGandenganHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $invoiceChargeGandenganHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($invoiceChargeGandenganDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? 'EDIT INVOICE CHARGE GANDENGAN DETAIL',
            'idtrans' =>  $invoiceChargeGandenganHeaderLogTrail->id,
            'nobuktitrans' => $invoiceChargeGandenganHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $invoiceChargeGandenganDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);


        return $invoiceChargeGandenganHeader;
    }

    public function processDestroy($id, $postingDari = ''): InvoiceChargeGandenganHeader
    {
        $invoiceChargeGandenganDetails = InvoiceChargeGandenganDetail::lockForUpdate()->where('invoicechargegandengan_id', $id)->get();

        $invoiceChargeGandenganHeader = new InvoiceChargeGandenganHeader();
        $invoiceChargeGandenganHeader = $invoiceChargeGandenganHeader->lockAndDestroy($id);

        $invoiceChargeGandenganHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => $invoiceChargeGandenganHeader->getTable(),
            'postingdari' => $postingDari,
            'idtrans' => $invoiceChargeGandenganHeader->id,
            'nobuktitrans' => $invoiceChargeGandenganHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $invoiceChargeGandenganHeader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'INVOICECHARGEGANDENGANDETAIL',
            'postingdari' => $postingDari,
            'idtrans' => $invoiceChargeGandenganHeaderLogTrail['id'],
            'nobuktitrans' => $invoiceChargeGandenganHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $invoiceChargeGandenganDetails->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $invoiceChargeGandenganHeader;
    }
}
