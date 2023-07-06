<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NotaDebetHeader extends MyModel
{
    use HasFactory;

    protected $table = 'notadebetheader';

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
        $query = $this->selectColumns($query)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )

            ->leftJoin(DB::raw("pelunasanpiutangheader as pelunasanpiutang with (readuncommitted)"), 'notadebetheader.pelunasanpiutang_nobukti', 'pelunasanpiutang.nobukti')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'notadebetheader.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'notadebetheader.statuscetak', 'statuscetak.id');
        if (request()->tgldari && request()->tglsampai) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(notadebetheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(notadebetheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("notadebetheader.statuscetak", $statusCetak);
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
        $query = $this->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
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
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                "$this->table.id",
                "$this->table.nobukti",
                "$this->table.pelunasanpiutang_nobukti",
                "$this->table.tglbukti",
                "$this->table.postingdari",
                "$this->table.statusapproval",
                "$this->table.tgllunas",
                "$this->table.userapproval",
                DB::raw('(case when (year(notadebetheader.tglapproval) <= 2000) then null else notadebetheader.tglapproval end ) as tglapproval'),
                "$this->table.userbukacetak",
                DB::raw('(case when (year(notadebetheader.tglbukacetak) <= 2000) then null else notadebetheader.tglbukacetak end ) as tglbukacetak'),
                "$this->table.statusformat",
                "$this->table.statuscetak",
                "$this->table.created_at",
                "$this->table.updated_at",
                "statuscetak.memo as statuscetak_memo",
                "$this->table.modifiedby",
                "parameter.memo as  statusapproval_memo",
                'pelunasanpiutang.penerimaan_nobukti'

            );
    }


    public function getNotaDebet($id)
    {
        $this->setRequestParameters();

        $query = DB::table('pelunasanpiutangdetail')->from(
            DB::raw("pelunasanpiutangdetail with (readuncommitted)")
        )
            ->select(DB::raw('
        pelunasanpiutangdetail.id as detail_id,
        pelunasanpiutangdetail.nobukti,
        pelunasanpiutangdetail.tglcair,
        pelunasanpiutangdetail.nominal as nominalbayar,
        pelunasanpiutangdetail.nominal as nominal,
        pelunasanpiutangdetail.piutang_nobukti,
        pelunasanpiutangdetail.invoice_nobukti,
        notadebetheader.keterangan,
        pelunasanpiutangdetail.coalebihbayar,
        COALESCE (pelunasanpiutangdetail.nominallebihbayar, 0) as lebihbayar '))

            ->leftJoin(DB::raw("piutangheader with (readuncommitted)"), 'piutangheader.nobukti', 'pelunasanpiutangdetail.piutang_nobukti')
            ->leftJoin(DB::raw("notadebetheader with (readuncommitted)"), 'notadebetheader.pelunasanpiutang_nobukti', 'pelunasanpiutangdetail.nobukti')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'pelunasanpiutangdetail.pelanggan_id', 'pelanggan.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'pelunasanpiutangdetail.agen_id', 'agen.id')
            ->whereRaw(" EXISTS (
            SELECT notadebetheader.pelunasanpiutang_nobukti
            FROM notadebetdetail with (readuncommitted) 
			left join notadebetheader  with (readuncommitted) on notadebetdetail.notadebet_id = notadebetheader.id
            WHERE notadebetheader.pelunasanpiutang_nobukti = pelunasanpiutangdetail.nobukti   
          )")
            ->where('pelunasanpiutangdetail.nominallebihbayar', '>', 0)
            ->where('notadebetheader.id', $id);

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
            $query->where('notadebetheader.statuscetak', '<>', request()->cetak)
                ->whereYear('notadebetheader.tglbukti', '=', request()->year)
                ->whereMonth('notadebetheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }
    public function findAll($id)
    {
        $this->setRequestParameters();

        $query = NotaDebetHeader::from(DB::raw("notadebetheader with (readuncommitted)"));
        $query = $this->selectColumns($query)
            ->leftJoin('parameter', 'notadebetheader.statusapproval', 'parameter.id')
            ->leftJoin('parameter as statuscetak', 'notadebetheader.statuscetak', 'statuscetak.id')
            ->leftJoin('pelunasanpiutangheader as pelunasanpiutang', 'notadebetheader.pelunasanpiutang_nobukti', 'pelunasanpiutang.nobukti');

        $data = $query->where("$this->table.id", $id)->first();
        return $data;
    }
    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
    public function processStore(array $data): NotaDebetHeader
    {
        $group = 'NOTA DEBET BUKTI';
        $subGroup = 'NOTA DEBET BUKTI';
        $format = DB::table('parameter')->where('grp', $group)->where('subgrp', $subGroup)->first();

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $notaDebetHeader = new NotaDebetHeader();

        $notaDebetHeader->pelunasanpiutang_nobukti = $data['pelunasanpiutang_nobukti'];
        $notaDebetHeader->agen_id = $data['agen_id'];
        $notaDebetHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $notaDebetHeader->statusapproval = $statusApproval->id;
        $notaDebetHeader->tgllunas = date('Y-m-d', strtotime($data['tgllunas']));
        $notaDebetHeader->statusformat = $format->id;
        $notaDebetHeader->statuscetak = $statusCetak->id;
        $notaDebetHeader->postingdari = $data['postingdari'];
        $notaDebetHeader->modifiedby = auth('api')->user()->name; 
        $notaDebetHeader->nobukti = (new RunningNumberService)->get($group, $subGroup, $notaDebetHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$notaDebetHeader->save()) {
            throw new \Exception("Error storing nota debet header.");
        }

        $notaDebetHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($notaDebetHeader->getTable()),
            'postingdari' => $data['postingdari'],
            'idtrans' => $notaDebetHeader->id,
            'nobuktitrans' => $notaDebetHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $notaDebetHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        $notaDebetDetails = [];
        $coakredit_detail = [];
        $coadebet_detail = [];
        $nominal_detail = [];
        $keterangan_detail = [];

        for ($i = 0; $i < count($data['nominallebihbayar']); $i++) {
            $notaDebetDetail = (new NotaDebetDetail())->processStore($notaDebetHeader, [
                "invoice_nobukti" => $data['invoice_nobukti'][$i],
                "nominal" => $data['nominalpiutang'][$i],
                "nominalbayar" => $data['nominal'][$i],
                "lebihbayar" => $data['nominallebihbayar'][$i],
                "keterangandetail" => '-',
                "coalebihbayar" => $data['coakredit'][$i]
            ]);
            $notaDebetDetails[] = $notaDebetDetail->toArray();
            $coakredit_detail[] = $data['coakredit'][$i];
            $coadebet_detail[] = $data['coadebet'][$i];
            $nominal_detail[] = $data['nominallebihbayar'][$i]; //AMBIL LEBIH BAYAR ATAU GIMANA?
            $keterangan_detail[] = '-';
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($notaDebetDetail->getTable()),
            'postingdari' => $data['postingdari'],
            'idtrans' => $notaDebetHeaderLogTrail->id,
            'nobuktitrans' => $notaDebetHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $notaDebetDetails,
            'modifiedby' => auth('api')->user()->user
        ]);

        /*STORE JURNAL*/
        $jurnalRequest = [
            'tanpaprosesnobukti' => 1,
            'nobukti' => $notaDebetHeader->nobukti,
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
        $jurnalUmumHeader = (new JurnalUmumHeader())->processStore($jurnalRequest);
        return $notaDebetHeader;
    }

    public function processUpdate(NotaDebetHeader $notaDebetHeader,array $data): NotaDebetHeader
    {
        $notaDebetHeader->agen_id = $data['agen_id'] ?? '';
        $notaDebetHeader->modifiedby = auth('api')->user()->name;

        if (!$notaDebetHeader->save()) {
            throw new \Exception("Error Update nota debet header.");
        }

        $notaDebetHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($notaDebetHeader->getTable()),
            'postingdari' => $data['postingdari'],
            'idtrans' => $notaDebetHeader->id,
            'nobuktitrans' => $notaDebetHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $notaDebetHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        $notaDebetDetail = NotaDebetDetail::where('notadebet_id', $notaDebetHeader->id)->lockForUpdate()->delete();
         
        $notaDebetDetails =[];
        $coakredit_detail =[];
        $coadebet_detail =[];
        $nominal_detail =[];
        $keterangan_detail =[];
        for ($i = 0; $i < count($data['nominallebihbayar']); $i++) {
            $notaDebetDetail = (new NotaDebetDetail())->processStore($notaDebetHeader, [
                "invoice_nobukti" => $data['invoice_nobukti'][$i],
                "nominal" => $data['nominalpiutang'][$i],
                "nominalbayar" => $data['nominal'][$i],
                "lebihbayar" => $data['nominallebihbayar'][$i],
                "keterangandetail" => '-',
                "coalebihbayar" => $data['coakredit'][$i]
            ]);
            $notaDebetDetails[] = $notaDebetDetail->toArray();
            $coakredit_detail[] = $data['coakredit'][$i];
            $coadebet_detail[] = $data['coadebet'][$i];
            $nominal_detail[] = $data['nominallebihbayar'][$i]; //AMBIL LEBIH BAYAR ATAU GIMANA?
            $keterangan_detail[] = '-';

        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($notaDebetDetail->getTable()),
            'postingdari' => $data['postingdari'],
            'idtrans' => $notaDebetHeaderLogTrail->id,
            'nobuktitrans' => $notaDebetHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $notaDebetDetails,
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
        $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $notaDebetHeader->nobukti)->first();
        $newJurnal = new JurnalUmumHeader();
        $newJurnal = $newJurnal->find($getJurnal->id);
        (new JurnalUmumHeader())->processUpdate($newJurnal, $jurnalRequest);

        return $notaDebetHeader;
    }

    public function processDestroy($id, $postingDari = ''): NotaDebetHeader
    {
        $notaDebetDetails = NotaDebetDetail::lockForUpdate()->where('notadebet_id', $id)->get();

        $notaDebetHeader = new NotaDebetHeader();
        $notaDebetHeader = $notaDebetHeader->lockAndDestroy($id);

        $notaDebetHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => $notaDebetHeader->getTable(),
            'postingdari' => $postingDari,
            'idtrans' => $notaDebetHeader->id,
            'nobuktitrans' => $notaDebetHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $notaDebetHeader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'NOTADEBETDETAIL',
            'postingdari' => $postingDari,
            'idtrans' => $notaDebetHeaderLogTrail['id'],
            'nobuktitrans' => $notaDebetHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $notaDebetDetails->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);
        $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $notaDebetHeader->nobukti)->first();
        (new JurnalUmumHeader())->processDestroy($getJurnal->id, $postingDari);
        
        return $notaDebetHeader;
    }

    public function getExport($id)
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
        ->select('text')
        ->where('grp', 'JUDULAN LAPORAN')
        ->where('subgrp', 'JUDULAN LAPORAN')
        ->first();

        $query = DB::table($this->table)->from(DB::raw("notadebetheader with (readuncommitted)"))
            ->select(
                "$this->table.id",
                "$this->table.nobukti",
                "$this->table.pelunasanpiutang_nobukti",
                "$this->table.tglbukti",
                "$this->table.postingdari",
                "$this->table.tgllunas",
                "$this->table.jumlahcetak",
                'pelunasanpiutang.penerimaan_nobukti',
                'statuscetak.memo as statuscetak',
                'statuscetak.id as  statuscetak_id',
                DB::raw("'Laporan Nota Debet' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :".auth('api')->user()->name."' as usercetak")
            )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("pelunasanpiutangheader as pelunasanpiutang with (readuncommitted)"), 'notadebetheader.pelunasanpiutang_nobukti', 'pelunasanpiutang.nobukti')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'notadebetheader.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'notadebetheader.statuscetak', 'statuscetak.id');

        $data = $query->first();
        return $data;
    }
}
