<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NotaKreditHeader extends MyModel
{
    use HasFactory;

    protected $table = 'notakreditheader';

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
        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';
        $query = DB::table($this->table);
        $query = $this->selectColumns($query)

            ->leftJoin(DB::raw("pelunasanpiutangheader as pelunasanpiutang with (readuncommitted)"), 'notakreditheader.pelunasanpiutang_nobukti', 'pelunasanpiutang.nobukti')
            ->leftJoin('parameter as statuscetak', 'notakreditheader.statuscetak', 'statuscetak.id')
            ->leftJoin('parameter', 'notakreditheader.statusapproval', 'parameter.id');
        if (request()->tgldari && request()->tglsampai) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(notakreditheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(notakreditheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("notakreditheader.statuscetak", $statusCetak);
        }

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }


    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 50)->unique();
            $table->string('pelunasanpiutang_nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('postingdari', 50)->nullable();
            $table->integer('statusapproval')->length(11)->nullable();
            $table->date('tgllunas')->nullable();
            $table->string('userapproval', 50)->nullable();
            $table->date('tglapproval')->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->increments('position');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $query = DB::table($modelTable);
        $query = $this->select(
            "id",
            "nobukti",
            "pelunasanpiutang_nobukti",
            "tglbukti",
            "postingdari",
            "statusapproval",
            "tgllunas",
            "userapproval",
            "tglapproval",
            "statusformat",
            "modifiedby",
        );
        $query = $this->sort($query);
        $models = $this->filter($query);

        DB::table($temp)->insertUsing([
            "id",
            "nobukti",
            "pelunasanpiutang_nobukti",
            "tglbukti",
            "postingdari",
            "statusapproval",
            "tgllunas",
            "userapproval",
            "tglapproval",
            "statusformat",
            "modifiedby",
        ], $models);
        return $temp;
    }

    public function selectColumns($query)
    {
        return $query->select(
            "$this->table.id",
            "$this->table.nobukti",
            "$this->table.pelunasanpiutang_nobukti",
            "$this->table.tglbukti",
            DB::raw('(case when (year(notakreditheader.tglapproval) <= 2000) then null else notakreditheader.tglapproval end ) as tglapproval'),
            "$this->table.postingdari",
            "$this->table.statusapproval",
            "$this->table.tgllunas",
            "$this->table.userapproval",
            "$this->table.userbukacetak",
            DB::raw('(case when (year(notakreditheader.tglbukacetak) <= 2000) then null else notakreditheader.tglbukacetak end ) as tglbukacetak'),
            "$this->table.statusformat",
            "$this->table.modifiedby",
            "$this->table.statuscetak",
            "$this->table.created_at",
            "$this->table.updated_at",
            "parameter.memo as  statusapproval_memo",
            "statuscetak.memo as  statuscetak_memo",
            'pelunasanpiutang.penerimaan_nobukti'
        );
    }

    public function getNotaKredit($id)
    {
        $this->setRequestParameters();

        $query = DB::table('pelunasanpiutangdetail')
            ->select(DB::raw('
        pelunasanpiutangdetail.id as detail_id,
        pelunasanpiutangdetail.nobukti,
        pelunasanpiutangdetail.tglcair,
        pelunasanpiutangdetail.nominal as nominalbayar,
        pelunasanpiutangdetail.nominal as nominal,
        pelunasanpiutangdetail.piutang_nobukti,
        pelunasanpiutangdetail.invoice_nobukti,
        notakreditheader.keterangan,
        pelunasanpiutangdetail.coapenyesuaian,
        COALESCE (pelunasanpiutangdetail.penyesuaian, 0) as penyesuaian '))

            ->leftJoin('piutangheader', 'piutangheader.nobukti', 'pelunasanpiutangdetail.piutang_nobukti')
            ->leftJoin('notakreditheader', 'notakreditheader.pelunasanpiutang_nobukti', 'pelunasanpiutangdetail.nobukti')
            ->leftJoin('pelanggan', 'pelunasanpiutangdetail.pelanggan_id', 'pelanggan.id')
            ->leftJoin('agen', 'pelunasanpiutangdetail.agen_id', 'agen.id')
            ->whereRaw(" EXISTS (
            SELECT notakreditheader.pelunasanpiutang_nobukti
            FROM notakreditdetail
			left join notakreditheader on notakreditdetail.notakredit_id = notakreditheader.id
            WHERE notakreditheader.pelunasanpiutang_nobukti = pelunasanpiutangdetail.nobukti   
          )")
            ->where('pelunasanpiutangdetail.penyesuaian', '>', 0)
            ->where('notakreditheader.id', $id);



        $data = $query->get();

        return $data;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'grp') {
            return $query
                ->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder'])
                ->orderBy($this->table . '.subgrp', $this->params['sortOrder'])
                ->orderBy($this->table . '.id', $this->params['sortOrder']);
        }

        if ($this->params['sortIndex'] == 'subgrp') {
            return $query
                ->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder'])
                ->orderBy($this->table . '.grp', $this->params['sortOrder'])
                ->orderBy($this->table . '.id', $this->params['sortOrder']);
        }

        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statusapproval_memo') {
                                $query = $query->where('parameter.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'statuscetak_memo') {
                                $query = $query->where('statuscetak.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgllunas' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak') {
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
                                if ($filters['field'] == 'statusapproval_memo') {
                                    $query = $query->orWhere('parameter.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'statuscetak_memo') {
                                    $query = $query->orWhere('statuscetak.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgllunas' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak') {
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
        if (request()->cetak && request()->periode) {
            $query->where('notakreditheader.statuscetak', '<>', request()->cetak)
                ->whereYear('notakreditheader.tglbukti', '=', request()->year)
                ->whereMonth('notakreditheader.tglbukti', '=', request()->month);
            return $query;
        }

        return $query;
    }
    public function findAll($id)
    {
        $this->setRequestParameters();
        $query = NotaKreditHeader::from(DB::raw("notakreditheader with (readuncommitted)"));
        $query = $this->selectColumns($query)
            ->leftJoin('parameter', 'notakreditheader.statusapproval', 'parameter.id')
            ->leftJoin('parameter as statuscetak', 'notakreditheader.statuscetak', 'statuscetak.id')
            ->leftJoin('pelunasanpiutangheader as pelunasanpiutang', 'notakreditheader.pelunasanpiutang_nobukti', 'pelunasanpiutang.nobukti');

        $data = $query->where("$this->table.id", $id)->first();
        return $data;
    }
    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function processStore(array $data): NotaKreditHeader
    {
        $group = 'NOTA KREDIT BUKTI';
        $subGroup = 'NOTA KREDIT BUKTI';
        $format = DB::table('parameter')->where('grp', $group)->where('subgrp', $subGroup)->first();

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $notaKreditHeader = new NotaKreditHeader();

        $notaKreditHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $notaKreditHeader->pelunasanpiutang_nobukti = $data['pelunasanpiutang_nobukti'];
        $notaKreditHeader->agen_id = $data['agen_id'];
        $notaKreditHeader->statusapproval = $statusApproval->id;
        $notaKreditHeader->tgllunas = date('Y-m-d', strtotime($data['tgllunas']));
        $notaKreditHeader->statusformat = $format->id;
        $notaKreditHeader->statuscetak = $statusCetak->id;
        $notaKreditHeader->postingdari = $data['postingdari'];
        $notaKreditHeader->modifiedby = auth('api')->user()->name;
        $notaKreditHeader->nobukti = (new RunningNumberService)->get($group, $subGroup, $notaKreditHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$notaKreditHeader->save()) {
            throw new \Exception("Error storing nota kredit header.");
        }

        $notaKreditHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($notaKreditHeader->getTable()),
            'postingdari' => $data['postingdari'],
            'idtrans' => $notaKreditHeader->id,
            'nobuktitrans' => $notaKreditHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $notaKreditHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        $notaKreditDetails = [];
        $coakredit_detail = [];
        $coadebet_detail = [];
        $nominal_detail = [];
        $keterangan_detail = [];

        for ($i = 0; $i < count($data['potongan']); $i++) {
            $notaKreditDetail = (new NotaKreditDetail())->processStore($notaKreditHeader, [
                "invoice_nobukti" => $data['invoice_nobukti'][$i],
                "nominal" => $data['nominalpiutang'][$i],
                "nominalbayar" => $data['nominal'][$i],
                "penyesuaian" => $data['potongan'][$i],
                "keterangandetail" => $data['keteranganpotongan'][$i],
                "coaadjust" => $data['coapotongan'][$i]
            ]);
            $notaKreditDetails[] = $notaKreditDetail->toArray();
            $coakredit_detail[] = $data['coapotongan'][$i];
            $coadebet_detail[] = $data['coadebet'][$i];
            $nominal_detail[] = $data['potongan'][$i]; //AMBIL LEBIH BAYAR ATAU GIMANA?
            $keterangan_detail[] = $data['keteranganpotongan'][$i];
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($notaKreditDetail->getTable()),
            'postingdari' => $data['postingdari'],
            'idtrans' => $notaKreditHeaderLogTrail->id,
            'nobuktitrans' => $notaKreditHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $notaKreditDetails,
            'modifiedby' => auth('api')->user()->user
        ]);

        /*STORE JURNAL*/
        $jurnalRequest = [
            'tanpaprosesnobukti' => 1,
            'nobukti' => $notaKreditHeader->nobukti,
            'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
            'postingdari' => $data['postingdari'],
            'statusapproval' => $statusApproval->id,
            'userapproval' => "",
            'tglapproval' => "",
            'modifiedby' => auth('api')->user()->name,
            'statusformat' => "0",
            'coakredit_detail' => $coakredit_detail,
            'coadebet_detail' => $coadebet_detail,
            'nominal_detail' => $nominal_detail,
            'keterangan_detail' => $keterangan_detail
        ];
        (new JurnalUmumHeader())->processStore($jurnalRequest);

        return $notaKreditHeader;
    }

    public function processUpdate(NotaKreditHeader $notaKreditHeader, array $data): NotaKreditHeader
    {
        $notaKreditHeader->agen_id = $data['agen_id'] ?? '';
        $notaKreditHeader->modifiedby = auth('api')->user()->name;

        if (!$notaKreditHeader->save()) {
            throw new \Exception("Error Update nota kredit header.");
        }

        $notaKreditHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($notaKreditHeader->getTable()),
            'postingdari' => $data['postingdari'],
            'idtrans' => $notaKreditHeader->id,
            'nobuktitrans' => $notaKreditHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $notaKreditHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        NotaKreditDetail::where('notakredit_id', $notaKreditHeader->id)->lockForUpdate()->delete();

        $notaKreditDetails = [];
        $coakredit_detail = [];
        $coadebet_detail = [];
        $nominal_detail = [];
        $keterangan_detail = [];
        for ($i = 0; $i < count($data['potongan']); $i++) {
            $notaKreditDetail = (new NotaKreditDetail())->processStore($notaKreditHeader, [
                "invoice_nobukti" => $data['invoice_nobukti'][$i],
                "nominal" => $data['nominalpiutang'][$i],
                "nominalbayar" => $data['nominal'][$i],
                "penyesuaian" => $data['potongan'][$i],
                "keterangandetail" => $data['keteranganpotongan'][$i],
                "coaadjust" => $data['coapotongan'][$i]
            ]);
            $notaKreditDetails[] = $notaKreditDetail->toArray();
            $coakredit_detail[] = $data['coapotongan'][$i];
            $coadebet_detail[] = $data['coadebet'][$i];
            $nominal_detail[] = $data['potongan'][$i]; //AMBIL LEBIH BAYAR ATAU GIMANA?
            $keterangan_detail[] = $data['keteranganpotongan'][$i];
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($notaKreditDetail->getTable()),
            'postingdari' => $data['postingdari'],
            'idtrans' => $notaKreditHeaderLogTrail->id,
            'nobuktitrans' => $notaKreditHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $notaKreditDetails,
            'modifiedby' => auth('api')->user()->user
        ]);

        /*STORE JURNAL*/
        $jurnalRequest = [
            'postingdari' => $data['postingdari'],
            'coakredit_detail' => $coakredit_detail,
            'coadebet_detail' => $coadebet_detail,
            'nominal_detail' => $nominal_detail,
            'keterangan_detail' => $keterangan_detail
        ];
        $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $notaKreditHeader->nobukti)->first();
        $newJurnal = new JurnalUmumHeader();
        $newJurnal = $newJurnal->find($getJurnal->id);
        (new JurnalUmumHeader())->processUpdate($newJurnal, $jurnalRequest);

        return $notaKreditHeader;
    }

    public function processDestroy($id, $postingDari = ''): NotaKreditHeader
    {
        $notaKreditDetails = NotaKreditDetail::lockForUpdate()->where('notakredit_id', $id)->get();

        $notaKreditHeader = new NotaKreditHeader();
        $notaKreditHeader = $notaKreditHeader->lockAndDestroy($id);

        $notaKreditHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => $notaKreditHeader->getTable(),
            'postingdari' => $postingDari,
            'idtrans' => $notaKreditHeader->id,
            'nobuktitrans' => $notaKreditHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $notaKreditHeader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'NOTAKREDITDETAIL',
            'postingdari' => $postingDari,
            'idtrans' => $notaKreditHeaderLogTrail['id'],
            'nobuktitrans' => $notaKreditHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $notaKreditDetails->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);
        $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $notaKreditHeader->nobukti)->first();
        (new JurnalUmumHeader())->processDestroy($getJurnal->id, $postingDari);

        return $notaKreditHeader;
    }
}
