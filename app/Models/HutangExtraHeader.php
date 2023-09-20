<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HutangExtraHeader extends MyModel
{
    use HasFactory;

    protected $table = 'hutangextraheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function cekvalidasiaksi($nobukti)
    {
        $hutangBayar = DB::table('hutangbayardetail')
            ->from(
                DB::raw("hutangbayardetail as a with (readuncommitted)")
            )
            ->select(
                'a.hutang_nobukti'
            )
            ->where('a.hutang_nobukti', '=', $nobukti)
            ->first();
        if (isset($hutangBayar)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'HUTANG BAYAR',
                'kodeerror' => 'SATL'
            ];
            goto selesai;
        }


        $jurnal = DB::table('hutangextraheader')
            ->from(
                DB::raw("hutangextraheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'a.hutang_nobukti', 'b.nobukti')
            ->where('a.hutang_nobukti', '=', $nobukti)
            ->first();

        if (isset($jurnal)) {
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
    public function get()
    {
        $this->setRequestParameters();
        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';
        $query = DB::table($this->table)->from(DB::raw("hutangextraheader with (readuncommitted)"))
            ->select(
                'hutangextraheader.id',
                'hutangextraheader.nobukti',
                'hutangextraheader.tglbukti',
                'hutangextraheader.postingdari',
                'hutangextraheader.hutang_nobukti',

                'akunpusat.keterangancoa as coa',
                'supplier.namasupplier as supplier_id',
                'hutangextraheader.total',

                'parameter.memo as statuscetak',
                'statusapproval.memo as statusapproval',
                'hutangextraheader.userapproval',
                'hutangextraheader.userbukacetak',
                'hutangextraheader.jumlahcetak',
                DB::raw('(case when (year(hutangextraheader.tglbukacetak) <= 2000) then null else hutangextraheader.tglbukacetak end ) as tglbukacetak'),
                DB::raw("cast((format(hutangheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderhutangheader"),
                DB::raw("cast(cast(format((cast((format(hutangheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderhutangheader"), 
                DB::raw('(case when (year(hutangextraheader.tglapproval) <= 2000) then null else hutangextraheader.tglapproval end ) as tglapproval'),

                'hutangextraheader.modifiedby',
                'hutangextraheader.created_at',
                'hutangextraheader.updated_at'
            )
            ->leftJoin('hutangheader','hutangextraheader.hutang_nobukti','hutangheader.nobukti')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'hutangextraheader.statuscetak', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'hutangextraheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'hutangextraheader.coa', 'akunpusat.coa')
            ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'hutangextraheader.supplier_id', 'supplier.id');

        if (request()->tgldari) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(hutangextraheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(hutangextraheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("hutangextraheader.statuscetak", $statusCetak);
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);
        $data = $query->get();

        return $data;
    }

    public function findAll($id)
    {
        $query = DB::table('hutangextraheader')->from(
            DB::raw("hutangextraheader with (readuncommitted)")
        )
            ->select(
                'hutangextraheader.id',
                'hutangextraheader.nobukti',
                'hutangextraheader.tglbukti',
                'supplier.namasupplier as supplier',
                'hutangextraheader.supplier_id',
            )
            ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'hutangextraheader.supplier_id', 'supplier.id')

            ->where('hutangextraheader.id', $id);

        $data = $query->first();
        return $data;
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
                 $this->table.coa,
                 $this->table.hutang_nobukti,
                 $this->table.postingdari,
                 'supplier.namasupplier as supplier_id',
                 $this->table.total,
                 'statusapproval.text as statusapproval',
                 $this->table.userapproval,
                 $this->table.tglapproval,
                 'parameter.text as statuscetak',
                 $this->table.userbukacetak,
                 $this->table.tglbukacetak,
                 $this->table.jumlahcetak,
                 $this->table.modifiedby,
                 $this->table.created_at,
                 $this->table.updated_at"
                )

            )
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'hutangextraheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'hutangextraheader.statuscetak', 'parameter.id')
            ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'hutangextraheader.supplier_id', 'supplier.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->nullable();
            $table->string('coa', 50)->nullable();
            $table->string('hutang_nobukti', 50)->nullable();
            $table->string('postingdari', 50)->nullable();
            $table->string('supplier_id', 50)->nullable();
            $table->double('total', 15, 2)->nullable();
            $table->string('statusapproval', 1000)->nullable();
            $table->string('userapproval', 50)->nullable();
            $table->date('tglapproval')->nullable();
            $table->string('statuscetak', 1000)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
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
        $models = $query
            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);

        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'coa', 'hutang_nobukti', 'postingdari', 'supplier_id', 'total', 'statusapproval', 'userapproval', 'tglapproval','statuscetak', 'userbukacetak', 'tglbukacetak', 'jumlahcetak', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'coa') {
            return $query->orderBy('akunpusat.keterangancoa', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'supplier_id') {
            return $query->orderBy('supplier.namasupplier', $this->params['sortOrder']);
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
                                $query->where('parameter.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusapproval') {
                                $query->where('statusapproval.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'supplier_id') {
                                $query->where('supplier.namasupplier', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coa') {
                                $query->where('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'total') {
                                $query = $query->whereRaw("format(hutangextraheader.total, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
                                    $query->orWhere('parameter.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statusapproval') {
                                    $query->orWhere('statusapproval.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'supplier_id') {
                                    $query->orWhere('supplier.namasupplier', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'coa') {
                                    $query->orWhere('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'total') {
                                    $query = $query->orWhereRaw("format(hutangextraheader.total, '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else {
                                    // $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
            $query->where('hutangextraheader.statuscetak', '<>', request()->cetak)
                ->whereYear('hutangextraheader.tglbukti', '=', request()->year)
                ->whereMonth('hutangextraheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }


    public function processStore(array $data): HutangExtraHeader
    {
        // dd($data);

        /*STORE HEADER*/
        $group = 'HUTANG EXTRA BUKTI';
        $subGroup = 'HUTANG EXTRA BUKTI';

        $format = DB::table('parameter')->where('grp', $group)->where('subgrp', $subGroup)->first();

        $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
        $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL HUTANG EXTRA MANUAL')->where('subgrp', 'DEBET')->first();
        $memo = json_decode($getCoaDebet->memo, true);
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

        $hutangExtraHeader = new HutangExtraHeader();

        $hutangExtraHeader->tglbukti =  date('Y-m-d', strtotime($data['tglbukti']));
        $hutangExtraHeader->coa = $memo['JURNAL'];
        $hutangExtraHeader->supplier_id = $data['supplier_id'];
        $hutangExtraHeader->postingdari = 'ENTRY HUTANG EXTRA';
        $hutangExtraHeader->statusformat = $format->id;
        $hutangExtraHeader->statuscetak = $statusCetak->id;
        $hutangExtraHeader->statusapproval = $statusApproval->id;
        $hutangExtraHeader->total = array_sum($data['total_detail']);
        $hutangExtraHeader->modifiedby = auth('api')->user()->name;
        $hutangExtraHeader->info = html_entity_decode(request()->info);
        $hutangExtraHeader->nobukti = (new RunningNumberService)->get($group, $subGroup, $hutangExtraHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));


        if (!$hutangExtraHeader->save()) {
            throw new \Exception("Error storing Hutang extra header.");
        }

        $hutangExtraHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($hutangExtraHeader->getTable()),
            'postingdari' => strtoupper('ENTRY HUTANG EXTRA HEADER'),
            'idtrans' => $hutangExtraHeader->id,
            'nobuktitrans' => $hutangExtraHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $hutangExtraHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        /* Store detail */
        $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL HUTANG EXTRA MANUAL')->where('subgrp', 'KREDIT')->first();
        $memoKredit = json_decode($getCoaKredit->memo, true);

        $hutangDetails = [];

        for ($i = 0; $i < count($data['total_detail']); $i++) {

            $hutangDetail = (new HutangExtraDetail())->processStore($hutangExtraHeader, [
                'tgljatuhtempo' => date('Y-m-d', strtotime($data['tgljatuhtempo'][$i])),
                'total' => $data['total_detail'][$i],
                'cicilan' => '',
                'totalbayar' => '',
                'keterangan' => $data['keterangan_detail'][$i],
                'modifiedby' => $hutangExtraHeader->modifiedby,
            ]);
            $hutangDetails[] = $hutangDetail->toArray();
            $tgljatuhtempo[] = $data['tgljatuhtempo'][$i];
            $nominal_detail[] = $data['total_detail'][$i];
            $keterangan_detail[] = $data['keterangan_detail'][$i];
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($hutangDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY HUTANG EXTRA DETAIL'),
            'idtrans' =>  $hutangExtraHeaderLogTrail->id,
            'nobuktitrans' => $hutangExtraHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $hutangDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);
        $hutangRequest = [
            'proseslain' => 'HUTANG EXTRA',
            'postingdari' => 'ENTRY HUTANG EXTRA',
            'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
            'coa' => $memo['JURNAL'],
            'supplier_id' => $data['supplier_id'],
            'modifiedby' => auth('api')->user()->name,
            'total' => array_sum($data['total_detail']),
            'coadebet' => $memo['JURNAL'],
            'coakredit' => $memoKredit['JURNAL'],
            'tgljatuhtempo' => $tgljatuhtempo,
            'total_detail' => $nominal_detail,
            'keterangan_detail' => $keterangan_detail,
        ];

        $hutangHeader = (new HutangHeader())->processStore($hutangRequest);
        $hutangExtraHeader->hutang_nobukti = $hutangHeader->nobukti;
        $hutangExtraHeader->save();

        return $hutangExtraHeader;
    }


    public function processUpdate(HutangExtraHeader $hutangExtraHeader, array $data): HutangExtraHeader
    {
        $getTgl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'EDIT TANGGAL BUKTI')->where('subgrp', 'HUTANG EXTRA')->first();
        if (trim($getTgl->text) == 'YA') {
            $group = 'HUTANG EXTRA BUKTI';
            $subGroup = 'HUTANG EXTRA BUKTI';
            $querycek = DB::table('hutangextraheader')->from(
                DB::raw("hutangextraheader a with (readuncommitted)")
            )
                ->select(
                    'a.nobukti'
                )
                ->where('a.id', $hutangExtraHeader->id)
                ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
                ->first();

            if (isset($querycek)) {
                $nobukti = $querycek->nobukti;
            } else {
                $nobukti = (new RunningNumberService)->get($group, $subGroup, $hutangExtraHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
            }

            $hutangExtraHeader->nobukti = $nobukti;
            $hutangExtraHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        }

        $hutangExtraHeader->supplier_id = $data['supplier_id'];
        $hutangExtraHeader->postingdari = 'EDIT HUTANG EXTRA HEADER';
        $hutangExtraHeader->total = array_sum($data['total_detail']);
        $hutangExtraHeader->modifiedby = auth('api')->user()->name;
        $hutangExtraHeader->info = html_entity_decode(request()->info);

        $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL HUTANG EXTRA MANUAL')->where('subgrp', 'DEBET')->first();
        $memo = json_decode($getCoaDebet->memo, true);

        if (!$hutangExtraHeader->save()) {
            throw new \Exception("Error updating hutang extra header.");
        }



        HutangExtraDetail::where('hutangextra_id', $hutangExtraHeader->id)->delete();

        $hutangDetails = [];

        $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL HUTANG EXTRA MANUAL')->where('subgrp', 'KREDIT')->first();
        $memoKredit = json_decode($getCoaKredit->memo, true);

        for ($i = 0; $i < count($data['total_detail']); $i++) {

            $hutangDetail = (new HutangExtraDetail())->processStore($hutangExtraHeader, [
                'tgljatuhtempo' => date('Y-m-d', strtotime($data['tgljatuhtempo'][$i])),
                'total' => $data['total_detail'][$i],
                'cicilan' => '',
                'totalbayar' => '',
                'keterangan' => $data['keterangan_detail'][$i],
                'modifiedby' => $hutangExtraHeader->modifiedby,
            ]);
            $hutangDetails[] = $hutangDetail->toArray();
            $tgljatuhtempo[] = $data['tgljatuhtempo'][$i];
            $nominal_detail[] = $data['total_detail'][$i];
            $keterangan_detail[] = $data['keterangan_detail'][$i];
        }


        $hutangRequest = [
            'proseslain' => 'HUTANG EXTRA',
            'postingdari' => 'EDIT HUTANG EXTRA',
            'tglbukti' => $hutangExtraHeader->tglbukti,
            'coa' => $memo['JURNAL'],
            'supplier_id' => $data['supplier_id'],
            'modifiedby' => auth('api')->user()->name,
            'total' => array_sum($data['total_detail']),
            'coadebet' => $memo['JURNAL'],
            'coakredit' => $memoKredit['JURNAL'],
            'tgljatuhtempo' => $tgljatuhtempo,
            'total_detail' => $nominal_detail,
            'keterangan_detail' => $keterangan_detail,
        ];

        $getHutang = DB::table("hutangheader")->from(DB::raw("hutangheader with (readuncommitted)"))->where('nobukti', $hutangExtraHeader->hutang_nobukti)->first();
        $newHutang = new HutangHeader();
        $newHutang = $newHutang->find($getHutang->id);
        $hutang = (new HutangHeader())->processUpdate($newHutang, $hutangRequest);
        $hutangExtraHeader->hutang_nobukti = $hutang->nobukti;
        $hutangExtraHeader->save();

        $hutangExtraHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($hutangExtraHeader->getTable()),
            'postingdari' => 'EDIT HUTANG EXTRA HEADER',
            'idtrans' => $hutangExtraHeader->id,
            'nobuktitrans' => $hutangExtraHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $hutangExtraHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($hutangDetail->getTable()),
            'postingdari' => 'EDIT HUTANG EXTRA DETAIL',
            'idtrans' =>  $hutangExtraHeaderLogTrail->id,
            'nobuktitrans' => $hutangExtraHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $hutangDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);
        return $hutangExtraHeader;
    }

    public function processDestroy($id, $postingDari = ''): HutangExtraHeader
    {
        $hutangDetails = HutangExtraDetail::lockForUpdate()->where('hutangextra_id', $id)->get();

        $hutangExtraHeader = new HutangExtraHeader();
        $hutangExtraHeader = $hutangExtraHeader->lockAndDestroy($id);

        $hutangExtraHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => $hutangExtraHeader->getTable(),
            'postingdari' => $postingDari,
            'idtrans' => $hutangExtraHeader->id,
            'nobuktitrans' => $hutangExtraHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $hutangExtraHeader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'HUTANGEXTRADETAIL',
            'postingdari' => $postingDari,
            'idtrans' => $hutangExtraHeaderLogTrail['id'],
            'nobuktitrans' => $hutangExtraHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $hutangDetails->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);
        $getHutang = DB::table("hutangheader")->from(DB::raw("hutangheader with (readuncommitted)"))->where('nobukti', $hutangExtraHeader->hutang_nobukti)->first();
        (new HutangHeader())->processDestroy($getHutang->id, $postingDari);
        return $hutangExtraHeader;
    }


    public function getExport($id)
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($this->table)->from(DB::raw("hutangextraheader with (readuncommitted)"))
            ->select(
                'hutangextraheader.id',
                'hutangextraheader.nobukti',
                'hutangextraheader.tglbukti',
                'hutangextraheader.hutang_nobukti',
                'hutangextraheader.postingdari',
                'akunpusat.keterangancoa as coa',
                'supplier.namasupplier as supplier_id',
                'statuscetak.memo as statuscetak',
                'statuscetak.id as  statuscetak_id',
                'hutangextraheader.jumlahcetak',
                DB::raw("'Cetak Hutang Extra' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'hutangextraheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'hutangextraheader.coa', 'akunpusat.coa')
            ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'hutangextraheader.supplier_id', 'supplier.id');

        $data = $query->first();
        return $data;
    }

    public function processApproval(array $data)
    {
        // dd($data);

        $statusApproval = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        for ($i = 0; $i < count($data['hutangId']); $i++) {

            $hutangExtraHeader = HutangExtraHeader::find($data['hutangId'][$i]);
            if ($hutangExtraHeader->statusapproval == $statusApproval->id) {
                $hutangExtraHeader->statusapproval = $statusNonApproval->id;
                $hutangExtraHeader->tglapproval = date('Y-m-d', strtotime("1900-01-01"));
                $hutangExtraHeader->userapproval = '';
                $aksi = $statusNonApproval->text;
            } else {
                $hutangExtraHeader->statusapproval = $statusApproval->id;
                $hutangExtraHeader->tglapproval = date('Y-m-d H:i:s');
                $hutangExtraHeader->userapproval = auth('api')->user()->name;
                $aksi = $statusApproval->text;
            }

            $hutangExtraHeader->save();
            (new LogTrail())->processStore([
                'namatabel' => strtoupper($hutangExtraHeader->getTable()),
                'postingdari' => 'APPROVAL HUTANG EXTRA',
                'idtrans' => $hutangExtraHeader->id,
                'nobuktitrans' => $hutangExtraHeader->nobukti,
                'aksi' => $aksi,
                'datajson' => $hutangExtraHeader->toArray(),
                'modifiedby' => auth('api')->user()->user
            ]);
        }

        return $hutangExtraHeader;
    }
}
