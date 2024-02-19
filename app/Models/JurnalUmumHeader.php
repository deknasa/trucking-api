<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class JurnalUmumHeader extends MyModel
{
    use HasFactory;

    protected $table = 'jurnalumumheader';
    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    // public function resolveRouteBinding($value, $field = null)
    // {
    //     return $this->where('id', '=', $value)->lockForUpdate()->firstOrFail();
    // }

    public function get()
    {
        $this->setRequestParameters();

        $lennobukti = 3;
        $lookup = request()->jurnal ?? '';
        $tempsummary = '##tempsummary' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsummary, function ($table) {
            $table->id();
            $table->string('nobukti', 1000)->nullable();
            $table->double('nominaldebet', 15, 2)->nullable();
            $table->double('nominalkredit', 15, 2)->nullable();
        });

        $querysummary = JurnalUmumHeader::from(
            DB::raw("jurnalumumheader as a with (readuncommitted)")
        )
            ->select(
                'a.nobukti as nobukti',
                DB::raw("sum((case when b.nominal<=0 then 0 else b.nominal end)) as nominaldebet"),
                DB::raw("sum((case when b.nominal>=0 then 0 else abs(b.nominal) end)) as nominalkredit"),
            )
            ->join(DB::raw("jurnalumumdetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->groupBy('a.nobukti');


        DB::table($tempsummary)->insertUsing([
            'nobukti',
            'nominaldebet',
            'nominalkredit',
        ], $querysummary);

        $query = DB::table($this->table)->from(
            DB::raw("jurnalumumheader with (readuncommitted)")
        )
            ->select(
                'jurnalumumheader.id',
                'jurnalumumheader.nobukti',
                'jurnalumumheader.tglbukti',
                'jurnalumumheader.postingdari',
                'jurnalumumheader.userapproval',
                'statuscetak.memo as statuscetak',
                DB::raw('(case when (year(jurnalumumheader.tglapproval) <= 2000) then null else jurnalumumheader.tglapproval end ) as tglapproval'),
                'jurnalumumheader.modifiedby',
                'jurnalumumheader.created_at',
                'jurnalumumheader.updated_at',
                'statusapproval.memo as statusapproval',
                'c.nominaldebet as nominaldebet',
                'c.nominalkredit as nominalkredit',
            )
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'jurnalumumheader.statuscetak', 'statuscetak.id')
            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'jurnalumumheader.statusapproval', 'statusapproval.id')
            ->leftjoin(DB::raw($tempsummary . " as c"), 'jurnalumumheader.nobukti', 'c.nobukti');

        if ($lookup != '') {
            $params = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL UMUM BUKTI')->where('subgrp', 'JURNAL UMUM BUKTI')->first();
            $query->where('jurnalumumheader.statusformat', $params->id);
        }

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);


        $data = $query->get();


        return $data;
    }
    public function jurnalumumdetail()
    {
        return $this->hasMany(JurnalUmumDetail::class, 'jurnalumum_id');
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
            $this->table.postingdari,
            'statusapproval.text as statusapproval',
            $this->table.userapproval,
            $this->table.tglapproval,
            $this->table.modifiedby,
            $this->table.updated_at"
                )
            )
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'jurnalumumheader.statusapproval', 'statusapproval.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('postingdari', 1000)->nullable();
            $table->string('statusapproval', 1000)->nullable();
            $table->string('userapproval', 1000)->nullable();
            $table->string('tglapproval', 1000)->nullable();
            $table->string('modifiedby', 50)->nullable();
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
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'postingdari', 'statusapproval', 'userapproval', 'tglapproval', 'modifiedby', 'updated_at'], $models);


        return  $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'nominaldebet') {
            return $query->orderBy('c.' . $this->params['sortIndex'], $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'nominalkredit') {

            return $query->orderBy('c.' . $this->params['sortIndex'], $this->params['sortOrder']);
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
                            if ($filters['field'] == 'statuscetak') {
                                $query = $query->where('statuscetak.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusapproval') {
                                $query = $query->where('statusapproval.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'nominaldebet') {
                                $query = $query->whereRaw("format(c.nominaldebet, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'nominalkredit') {
                                $query = $query->whereRaw("format(c.nominalkredit, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglapproval') {
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
                                if ($filters['field'] == 'statuscetak') {
                                    $query = $query->orWhere('statuscetak.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statusapproval') {
                                    $query = $query->orWhere('statusapproval.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'nominaldebet') {
                                    $query = $query->orWhereRaw("format(c.nominaldebet, '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'nominalkredit') {
                                    $query = $query->orWhereRaw("format(c.nominalkredit, '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglapproval') {
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
    public function cekvalidasiaksi($nobukti)
    {
        $pengeluaran = DB::table('pengeluaranheader')
            ->from(
                DB::raw("pengeluaranheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($pengeluaran)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pengeluaran',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }
        $penerimaan = DB::table('penerimaanheader')
            ->from(
                DB::raw("penerimaanheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($penerimaan)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }
        $hutang = DB::table('hutangheader')
            ->from(
                DB::raw("hutangheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($hutang)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Hutang',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }
        $piutang = DB::table('piutangheader')
            ->from(
                DB::raw("piutangheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($piutang)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Piutang',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }
        $penerimaanGiro = DB::table('penerimaangiroheader')
            ->from(
                DB::raw("penerimaangiroheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($penerimaanGiro)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan Giro',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }
        $prosesGajiSupir = DB::table('prosesgajisupirheader')
            ->from(
                DB::raw("prosesgajisupirheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($prosesGajiSupir)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Proses Gaji Supir',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }
        $penerimaanTrucking = DB::table('penerimaantruckingheader')
            ->from(
                DB::raw("penerimaantruckingheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($penerimaanTrucking)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan Trucking',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }

        $pencairanGiropengeluaran = DB::table('pencairangiropengeluaranheader')
            ->from(
                DB::raw("pencairangiropengeluaranheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($pencairanGiropengeluaran)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pencairan Giro Pengeluaran',
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

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function processStore(array $data): JurnalUmumHeader
    {


        $sumNominal = 0;
        for ($i = 0; $i < count($data['nominal_detail']); $i++) {
            $sumNominal += $data['nominal_detail'][$i];
        }
        if (!$sumNominal) {
            return (new JurnalUmumHeader());
            // dd($sumNominal);
        }


        $tanpaprosesnobukti = $data['tanpaprosesnobukti'] ?? 0;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        if ($tanpaprosesnobukti == 0) {

            $group = 'JURNAL UMUM BUKTI';
            $subGroup = 'JURNAL UMUM BUKTI';

            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subGroup)
                ->first();
        }

        $jurnalUmumHeader = new JurnalUmumHeader();
        $statusApproval = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

        $jurnalUmumHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $jurnalUmumHeader->postingdari = $data['postingdari'] ?? 'ENTRY JURNAL UMUM';
        $jurnalUmumHeader->statusapproval = ($tanpaprosesnobukti == 1) ? $statusNonApproval->id : $statusApproval->id;
        $jurnalUmumHeader->userapproval = ($tanpaprosesnobukti == 1) ? '' : auth('api')->user()->name;
        $jurnalUmumHeader->tglapproval = ($tanpaprosesnobukti == 1) ? '' : date('Y-m-d H:i:s');
        $jurnalUmumHeader->statuscetak = $statusCetak->id ?? 0;
        $jurnalUmumHeader->statusformat = $data['statusformat'] ?? $format->id;
        $jurnalUmumHeader->modifiedby = auth('api')->user()->name;
        $jurnalUmumHeader->info = html_entity_decode(request()->info);

        if ($tanpaprosesnobukti == 0) {
            $jurnalUmumHeader->nobukti = (new RunningNumberService)->get($group, $subGroup, $jurnalUmumHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
        } else {
            $jurnalUmumHeader->nobukti = $data['nobukti'];
        }

        if (!$jurnalUmumHeader->save()) {
            throw new \Exception("Error storing jurnal umum header.");
        }

        $jurnalUmumHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($jurnalUmumHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? 'ENTRY JURNAL UMUM HEADER',
            'idtrans' => $jurnalUmumHeader->id,
            'nobuktitrans' => $jurnalUmumHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $jurnalUmumHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        $jurnalUmumDetails = [];

        for ($i = 0; $i < count($data['nominal_detail']); $i++) {
            for ($x = 0; $x <= 1; $x++) {
                if ($x == 1) {
                    $jurnalUmumDetail = (new JurnalUmumDetail())->processStore($jurnalUmumHeader, [
                        'tglbukti' => (str_contains($jurnalUmumHeader->nobukti, 'EBS')) ? date('Y-m-d', strtotime($data['tglbukti_detail'][$i])) : $jurnalUmumHeader->tglbukti,
                        'coa' => $data['coakredit_detail'][$i],
                        'nominal' => $data['nominal_detail'][$i] * -1,
                        'keterangan' => $data['keterangan_detail'][$i],
                        'baris' => $i,
                    ]);

                    if ($tanpaprosesnobukti == 0) {
                        $coa_detail[] = $data['coakredit_detail'][$i];
                        $nominal_detail[] = '-' . $data['nominal_detail'][$i];
                        $keterangan_detail[] = $data['keterangan_detail'][$i];
                        $baris[] = $i;
                    }
                } else {
                    $jurnalUmumDetail = (new JurnalUmumDetail())->processStore($jurnalUmumHeader, [
                        'tglbukti' => (str_contains($jurnalUmumHeader->nobukti, 'EBS')) ? date('Y-m-d', strtotime($data['tglbukti_detail'][$i])) : $jurnalUmumHeader->tglbukti,
                        'coa' => $data['coadebet_detail'][$i],
                        'nominal' => $data['nominal_detail'][$i] * 1,
                        'keterangan' => $data['keterangan_detail'][$i],
                        'baris' => $i,
                    ]);

                    if ($tanpaprosesnobukti == 0) {
                        $coa_detail[] = $data['coadebet_detail'][$i];
                        $nominal_detail[] = $data['nominal_detail'][$i];
                        $keterangan_detail[] = $data['keterangan_detail'][$i];
                        $baris[] = $i;
                    }
                }
                $jurnalUmumDetails[] = $jurnalUmumDetail->toArray();
            }
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($jurnalUmumDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? 'ENTRY JURNAL UMUM DETAIL',
            'idtrans' =>  $jurnalUmumHeaderLogTrail->id,
            'nobuktitrans' => $jurnalUmumHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $jurnalUmumDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);

        if ($tanpaprosesnobukti == 0) {
            $jurnalRequest = [
                'nobukti' => $jurnalUmumHeader->nobukti,
                'tglbukti' => $jurnalUmumHeader->tglbukti,
                'postingdari' => $jurnalUmumHeader->postingdari,
                'statusapproval' => $jurnalUmumHeader->statusapproval,
                'statusformat' => $jurnalUmumHeader->statusformat,
                'coa_detail' => $coa_detail,
                'nominal_detail' => $nominal_detail,
                'keterangan_detail' => $keterangan_detail,
                'baris' => $baris,
            ];
            (new JurnalUmumPusatHeader())->processStore($jurnalRequest);
        }

        return $jurnalUmumHeader;
    }

    public function processUpdate(JurnalUmumHeader $jurnalUmumHeader, array $data): JurnalUmumHeader
    {
        $tanpaprosesnobukti = $data['tanpaprosesnobukti'] ?? 0;
        $sumNominal = 0;
        for ($i = 0; $i < count($data['nominal_detail']); $i++) {
            $sumNominal += $data['nominal_detail'][$i];
        }
        if (!$sumNominal) {
            return $jurnalUmumHeader;
        }
        $group = 'JURNAL UMUM BUKTI';
        $subGroup = 'JURNAL UMUM BUKTI';
        $getTgl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'EDIT TANGGAL BUKTI')->where('subgrp', 'JURNAL UMUM')->first();

        if (trim($getTgl->text) == 'YA') {

            $querycek = DB::table('jurnalumumheader')->from(
                DB::raw("jurnalumumheader a with (readuncommitted)")
            )
                ->select(
                    'a.nobukti'
                )
                ->where('a.id', $jurnalUmumHeader->id)
                ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
                ->first();

            if (isset($querycek)) {
                $nobukti = $querycek->nobukti;
            } else {
                if (str_contains($data['nobukti'], 'JU')) {
                    $nobukti = (new RunningNumberService)->get($group, $subGroup, $jurnalUmumHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
                } else {
                    $nobukti = $data['nobukti'];
                }
            }
            $jurnalUmumHeader->nobukti = $nobukti;
            $jurnalUmumHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        }
        $jurnalUmumHeader->postingdari = $data['postingdari'] ?? 'EDIT JURNAL UMUM';
        $jurnalUmumHeader->modifiedby = auth('api')->user()->name;
        $jurnalUmumHeader->info = html_entity_decode(request()->info);


        if (!$jurnalUmumHeader->save()) {
            throw new \Exception("Error updating jurnal umum header.");
        }

        $jurnalUmumHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($jurnalUmumHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? 'EDIT JURNAL UMUM HEADER',
            'idtrans' => $jurnalUmumHeader->id,
            'nobuktitrans' => $jurnalUmumHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $jurnalUmumHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        JurnalUmumDetail::where('jurnalumum_id', $jurnalUmumHeader->id)->delete();

        // dd($data);
        $jurnalUmumDetails = [];
        for ($i = 0; $i < count($data['nominal_detail']); $i++) {
            for ($x = 0; $x <= 1; $x++) {
                // $data['nominal_detail'][$i];
                if ($x == 1) {
                    $jurnalUmumDetail = (new JurnalUmumDetail())->processStore($jurnalUmumHeader, [
                        'tglbukti' => (str_contains($jurnalUmumHeader->nobukti, 'EBS')) ? date('Y-m-d', strtotime($data['tglbukti_detail'][$i])) : $jurnalUmumHeader->tglbukti,
                        'coa' => $data['coakredit_detail'][$i],
                        'nominal' => $data['nominal_detail'][$i] * -1,
                        'keterangan' => $data['keterangan_detail'][$i],
                        'baris' => $i,
                    ]);
                    if ($tanpaprosesnobukti == 0) {
                        $coa_detail[] = $data['coakredit_detail'][$i];
                        $nominal_detail[] = '-' . $data['nominal_detail'][$i];
                        $keterangan_detail[] = $data['keterangan_detail'][$i];
                        $baris[] = $i;
                    }
                } else {
                    $jurnalUmumDetail = (new JurnalUmumDetail())->processStore($jurnalUmumHeader, [
                        'tglbukti' => (str_contains($jurnalUmumHeader->nobukti, 'EBS')) ? date('Y-m-d', strtotime($data['tglbukti_detail'][$i])) : $jurnalUmumHeader->tglbukti,
                        'coa' => $data['coadebet_detail'][$i],
                        'nominal' => $data['nominal_detail'][$i] * 1,
                        'keterangan' => $data['keterangan_detail'][$i],
                        'baris' => $i,
                    ]);
                    if ($tanpaprosesnobukti == 0) {
                        $coa_detail[] = $data['coadebet_detail'][$i];
                        $nominal_detail[] = $data['nominal_detail'][$i];
                        $keterangan_detail[] = $data['keterangan_detail'][$i];
                        $baris[] = $i;
                    }
                }
                $jurnalUmumDetails[] = $jurnalUmumDetail->toArray();
            }
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($jurnalUmumDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? 'EDIT JURNAL UMUM DETAIL',
            'idtrans' =>  $jurnalUmumHeaderLogTrail->id,
            'nobuktitrans' => $jurnalUmumHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $jurnalUmumDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);

        if ($tanpaprosesnobukti == 0) {
            $getJurnal = JurnalUmumPusatHeader::from(DB::raw("jurnalumumpusatheader with (readuncommitted)"))->where('nobukti', $jurnalUmumHeader->nobukti)->first();

            $jurnalUmumPusatHeader = new JurnalUmumPusatHeader();
            $jurnalUmumPusatHeader = $jurnalUmumPusatHeader->lockAndDestroy($getJurnal->id);

            $jurnalRequest = [
                'nobukti' => $jurnalUmumHeader->nobukti,
                'tglbukti' => $jurnalUmumHeader->tglbukti,
                'postingdari' => $jurnalUmumHeader->postingdari,
                'statusapproval' => $jurnalUmumHeader->statusapproval,
                'statusformat' => $jurnalUmumHeader->statusformat,
                'coa_detail' => $coa_detail,
                'nominal_detail' => $nominal_detail,
                'keterangan_detail' => $keterangan_detail,
                'baris' => $baris,
            ];
            (new JurnalUmumPusatHeader())->processStore($jurnalRequest);
        }
        return $jurnalUmumHeader;
    }

    public function processDestroy($id, $postingDari = ''): JurnalUmumHeader
    {
        $jurnalUmumDetails = JurnalUmumDetail::lockForUpdate()->where('jurnalumum_id', $id)->get();

        $jurnalUmumHeader = new JurnalUmumHeader();
        $jurnalUmumHeader = $jurnalUmumHeader->lockAndDestroy($id);

        $jurnalUmumHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => $jurnalUmumHeader->getTable(),
            'postingdari' => $postingDari,
            'idtrans' => $jurnalUmumHeader->id,
            'nobuktitrans' => $jurnalUmumHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $jurnalUmumHeader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'JURNALUMUMDETAIL',
            'postingdari' => $postingDari,
            'idtrans' => $jurnalUmumHeaderLogTrail['id'],
            'nobuktitrans' => $jurnalUmumHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $jurnalUmumDetails->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        if ($jurnalUmumHeader->statusformat == 115) {
            $getJurnal = JurnalUmumPusatHeader::from(DB::raw("jurnalumumpusatheader with (readuncommitted)"))->where('nobukti', $jurnalUmumHeader->nobukti)->first();
            if ($getJurnal != '') {
                $jurnalumumHeader = (new JurnalUmumPusatHeader())->processDestroy($getJurnal->id, strtoupper('DELETE JURNAL UMUM'));
            }
        }
        return $jurnalUmumHeader;
    }

    public function getExport($id)
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $this->setRequestParameters();

        $lennobukti = 3;

        $tempsummary = '##tempsummary' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsummary, function ($table) {
            $table->id();
            $table->string('nobukti', 1000)->nullable();
            $table->double('nominaldebet', 15, 2)->nullable();
            $table->double('nominalkredit', 15, 2)->nullable();
        });

        $querysummary = JurnalUmumHeader::from(
            DB::raw("jurnalumumheader as a with (readuncommitted)")
        )
            ->select(
                'a.nobukti as nobukti',
                DB::raw("sum((case when b.nominal<=0 then 0 else b.nominal end)) as nominaldebet"),
                DB::raw("sum((case when b.nominal>=0 then 0 else abs(b.nominal) end)) as nominalkredit"),
            )
            ->join(DB::raw("jurnalumumdetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->groupBy('a.nobukti');


        DB::table($tempsummary)->insertUsing([
            'nobukti',
            'nominaldebet',
            'nominalkredit',
        ], $querysummary);

        $query = DB::table($this->table)->from(
            DB::raw("jurnalumumheader with (readuncommitted)")
        )
            ->select(
                'jurnalumumheader.id',
                'jurnalumumheader.nobukti',
                'jurnalumumheader.tglbukti',
                'jurnalumumheader.postingdari',
                'c.nominaldebet as nominaldebet',
                'c.nominalkredit as nominalkredit',
                'statuscetak.memo as statuscetak',
                "statuscetak.id as  statuscetak_id",
                'jurnalumumheader.jumlahcetak',
                DB::raw("'Laporan Jurnal Umum' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'jurnalumumheader.statuscetak', 'statuscetak.id')
            ->leftjoin(DB::raw($tempsummary . " as c"), 'jurnalumumheader.nobukti', 'c.nobukti');

        $data = $query->first();
        return $data;
    }

    public function processApproval(array $data): JurnalUmumHeader
    {
        // dd($data);

        $statusApproval = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        for ($i = 0; $i < count($data['jurnalId']); $i++) {

            $jurnalumum = JurnalUmumHeader::find($data['jurnalId'][$i]);

            $jurnalUmumPusat = JurnalUmumPusatHeader::from(DB::raw("jurnalumumpusatheader with (readuncommitted)"))->where('nobukti', $jurnalumum->nobukti)->first();
            if ($jurnalumum->statusapproval == $statusApproval->id) {
                $jurnalumum->statusapproval = $statusNonApproval->id;
                $jurnalumum->tglapproval = date('Y-m-d', strtotime("1900-01-01"));
                $jurnalumum->userapproval = '';
                $aksi = $statusNonApproval->text;

                if ($jurnalUmumPusat != null) {
                    (new JurnalUmumPusatHeader())->processDestroy($jurnalUmumPusat->id, "$aksi JURNAL UMUM");
                }
            } else {
                $jurnalumum->statusapproval = $statusApproval->id;
                $aksi = $statusApproval->text;
                $jurnalumum->tglapproval = date('Y-m-d H:i:s');
                $jurnalumum->userapproval = auth('api')->user()->name;
                $jurnalumum->info = html_entity_decode(request()->info);

                $jurnalDetail = JurnalUmumDetail::where('jurnalumum_id', $data['jurnalId'][$i])->get();
                $coa_detail = [];
                $nominal_detail = [];
                $keterangan_detail = [];
                $baris = [];

                foreach ($jurnalDetail as $index => $value) {
                    $coa_detail[] = $value->coa;
                    $nominal_detail[] = $value->nominal;
                    $keterangan_detail[] = $value->keterangan;
                    $baris[] = $value->baris;
                }

                $jurnalRequest = [
                    'nobukti' => $jurnalumum->nobukti,
                    'tglbukti' => $jurnalumum->tglbukti,
                    'postingdari' => $jurnalumum->postingdari,
                    'statusapproval' => $jurnalumum->statusapproval,
                    'statusformat' => $jurnalumum->statusformat,
                    'coa_detail' => $coa_detail,
                    'nominal_detail' => $nominal_detail,
                    'keterangan_detail' => $keterangan_detail,
                    'baris' => $baris,
                ];

                (new JurnalUmumPusatHeader())->processStore($jurnalRequest);
            }

            if (!$jurnalumum->save()) {
                throw new \Exception("Error approval jurnal umum header.");
            }
            (new LogTrail())->processStore([
                'namatabel' => strtoupper($jurnalumum->getTable()),
                'postingdari' => 'APPROVAL JURNAL UMUM',
                'idtrans' => $jurnalumum->id,
                'nobuktitrans' => $jurnalumum->nobukti,
                'aksi' => $aksi,
                'datajson' => $jurnalumum->toArray(),
                'modifiedby' => auth('api')->user()->user
            ]);

            // PROSES JURNAL UMUM PUSAT
            // $jurnalUmumPusat = JurnalUmumPusatHeader::from(DB::raw("jurnalumumpusatheader with (readuncommitted)"))->where('nobukti', $jurnalumum->nobukti)->first();
            // if ($jurnalUmumPusat != null) {
            //     (new JurnalUmumPusatHeader())->processDestroy($jurnalUmumPusat->id, "$aksi JURNAL UMUM");
            // } else {
            //     if ($jurnalumum->statusapproval == $statusNonApproval->id) {


            //     }
            // }
        }

        return $jurnalumum;
    }
}
