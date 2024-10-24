<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OpnameHeader extends MyModel
{
    use HasFactory;

    protected $table = 'opnameheader';

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

        $query = DB::table($this->table)->from(
            DB::raw("opnameheader with (readuncommitted)")
        )
            ->select(
                'opnameheader.id',
                'opnameheader.nobukti',
                'opnameheader.tglbukti',
                'opnameheader.keterangan',
                'gudang.gudang',
                'parameter.memo as statuscetak',
                'approval.memo as statusapproval',
                'opnameheader.userbukacetak',
                DB::raw('(case when (year(opnameheader.tglbukacetak) <= 2000) then null else opnameheader.tglbukacetak end ) as tglbukacetak'),
                'opnameheader.modifiedby',
                'opnameheader.created_at',
                'opnameheader.updated_at',
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'opnameheader.statuscetak', 'parameter.id')
            ->leftJoin(DB::raw("parameter as approval with (readuncommitted)"), 'opnameheader.statusapproval', 'approval.id')
            ->leftJoin(DB::raw("gudang with (readuncommitted)"), 'opnameheader.gudang_id', 'gudang.id');
        if (request()->tgldari && request()->tglsampai) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(opnameheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(opnameheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("opnameheader.statuscetak", $statusCetak);
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }


    public function getInventory($kelompok_id, $statusreuse, $statusban, $filter, $jenistgltampil, $priode, $stokdari_id, $stoksampai_id, $dataFilter, $prosesneraca, $kelompok)
    {
        $jenislaporan = (new Parameter)->cekId('JENIS LAPORAN', 'JENIS LAPORAN', 'NORMAL');

        $inventory = (new LaporanSaldoInventory())->getReport($kelompok_id, $statusreuse, $statusban, $filter, $jenistgltampil, $priode, $stokdari_id, $stoksampai_id, $dataFilter, $prosesneraca, $jenislaporan);
        // dd($inventory->get());
        $tempinevtory = '##tempinevtory' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempinevtory, function ($table) {
            $table->string('header')->nullable();
            $table->string('judul')->nullable();
            $table->string('lokasi')->nullable();
            $table->string('namalokasi')->nullable();
            $table->string('kategori')->nullable();
            $table->string('tgldari')->nullable();
            $table->string('tglsampai')->nullable();
            $table->string('stokdari')->nullable();
            $table->string('stoksampai')->nullable();
            $table->string('vulkanisirke')->nullable();
            $table->unsignedBigInteger('stok_id')->nullable();
            $table->string('kodebarang')->nullable();
            $table->string('namabarang')->nullable();
            $table->string('tanggal')->nullable();
            $table->double('qty', 15, 2)->nullable();

            $table->string('satuan')->nullable();
            $table->string('nominal')->nullable();
            $table->string('disetujui')->nullable();
            $table->string('diperiksa')->nullable();
        });

        DB::table($tempinevtory)->insertUsing([
            "header",
            "judul",
            "lokasi",
            "namalokasi",
            "kategori",
            "tgldari",
            "tglsampai",
            "stokdari",
            "stoksampai",
            "vulkanisirke",
            "stok_id",
            "kodebarang",
            "namabarang",
            "tanggal",
            "qty",
            "satuan",
            "nominal",
            "disetujui",
            "diperiksa",
        ], $inventory);
        $data = DB::table($tempinevtory)
            ->select(
                'stok_id as id',
                'stok_id',
                'namabarang',
                'stok.kelompok_id as kelompok',
                'tanggal',
                db::raw("sum(qty) as qty"),
                db::raw("0 as qtyfisik"),
            )
            ->leftJoin(DB::raw("stok with (readuncommitted)"), "$tempinevtory.stok_id", 'stok.id')
            ->groupBy('stok_id', 'namabarang', 'tanggal', 'stok.kelompok_id');
        if ($kelompok) {
            $data = $data->where('stok.kelompok_id', $kelompok);
        }

        return $data->get();
    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw("
            $this->table.id,
            $this->table.nobukti,
            $this->table.tglbukti,
            $this->table.keterangan,
            'gudang.gudang',
            'parameter.memo as statuscetak',
            'approval.memo as statusapproval',
            $this->table.userbukacetak,
            $this->table.tglbukacetak,
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at
            ")
            )->leftJoin(DB::raw("parameter with (readuncommitted)"), 'opnameheader.statuscetak', 'parameter.id')
            ->leftJoin(DB::raw("parameter as approval with (readuncommitted)"), 'opnameheader.statusapproval', 'approval.id')

            ->leftJoin(DB::raw("gudang with (readuncommitted)"), 'opnameheader.gudang_id', 'gudang.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('keterangan', 1000)->nullable();
            $table->string('gudang')->nullable();
            $table->string('statuscetak')->nullable();
            $table->string('statusapproval')->nullable();
            $table->string('userbukacetak')->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->string('modifiedby')->default();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });
        // if ((date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tgldariheader))) || (date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tglsampaiheader)))) {
        //     request()->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
        //     request()->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
        // }
        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        $models = $query
            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'keterangan', 'gudang','statuscetak',
        'statusapproval', 'userbukacetak', 'tglbukacetak', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }

    public function findAll($id)
    {
        $query = DB::table("opnameheader")->from(DB::raw("opnameheader with (readuncommitted)"))
            ->select(
                'opnameheader.id',
                'opnameheader.nobukti',
                'opnameheader.tglbukti',
                'opnameheader.keterangan',
                'opnameheader.gudang_id',
                'opnameheader.statusapproval',
                'gudang.gudang',
                'opnameheader.kelompok_id',
                'kelompok.kodekelompok as kelompok'
            )
            ->leftJoin(DB::raw("gudang with (readuncommitted)"), 'opnameheader.gudang_id', 'gudang.id')
            ->leftJoin(DB::raw("kelompok with (readuncommitted)"), 'opnameheader.kelompok_id', 'kelompok.id')
            ->where('opnameheader.id', $id)
            ->first();

        return $query;
    }
    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'gudang') {
            return $query->orderBy('gudang.gudang', $this->params['sortOrder']);
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
                        if ($filters['field'] == 'statuscetak') {
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('approval.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'gudang') {
                            $query = $query->where('gudang.gudang', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'tglbukti') {
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
                            if ($filters['field'] == 'statuscetak') {
                                $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusapproval') {
                                $query = $query->orWhere('approval.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'gudang') {
                                $query = $query->orWhere('gudang.gudang', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tglbukti') {
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

        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function processStore(array $data): OpnameHeader
    {
        $group = 'OPNAME BUKTI';
        $subGroup = 'OPNAME BUKTI';

        $format = DB::table('parameter')
            ->where('grp', $group)
            ->where('subgrp', $subGroup)
            ->first();

        $opnameHeader = new OpnameHeader();

        $statusCetak = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $statusapproval = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

        $opnameHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $opnameHeader->keterangan = $data['keterangan'] ?? '';
        $opnameHeader->gudang_id = $data['gudang_id'];
        $opnameHeader->kelompok_id = $data['kelompok_id'];
        $opnameHeader->statusformat = $format->id;
        $opnameHeader->statuscetak = $statusCetak->id;
        $opnameHeader->statusapproval = $statusapproval->id;
        $opnameHeader->userbukacetak = '';
        $opnameHeader->tglbukacetak = '';
        $opnameHeader->modifiedby = auth('api')->user()->name;

        $opnameHeader->nobukti = (new RunningNumberService)->get($group, $subGroup, $opnameHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$opnameHeader->save()) {
            throw new \Exception("Error storing opname header.");
        }

        $opnameHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($opnameHeader->getTable()),
            'postingdari' => 'ENTRY OPNAME HEADER',
            'idtrans' => $opnameHeader->id,
            'nobuktitrans' => $opnameHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $opnameHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);


        $opnameDetails = [];

        for ($i = 0; $i < count($data['stok_id']); $i++) {

            $opnameDetail = (new OpnameDetail())->processStore($opnameHeader, [
                'stok_id' => $data['stok_id'][$i],
                'qty' => $data['qty'][$i],
                'tglbuktimasuk' => $data['tglbuktimasuk'][$i],
                'qtyfisik' => $data['qtyfisik'][$i]
            ]);
            $opnameDetails[] = $opnameDetail->toArray();
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($opnameDetail->getTable()),
            'postingdari' => 'ENTRY OPNAME DETAIL',
            'idtrans' =>  $opnameHeaderLogTrail->id,
            'nobuktitrans' => $opnameHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $opnameDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);

        return $opnameHeader;
    }


    public function processUpdate(OpnameHeader $opnameHeader, array $data): OpnameHeader
    {
        $group = 'OPNAME BUKTI';
        $subGroup = 'OPNAME BUKTI';

        $nobuktiOld = $opnameHeader->nobukti;
        $querycek = DB::table('opnameheader')->from(
            DB::raw("opnameheader a with (readuncommitted)")
        )
            ->select(
                'a.nobukti'
            )
            ->where('a.id', $opnameHeader->id)
            ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
            ->first();

        if (isset($querycek)) {
            $nobukti = $querycek->nobukti;
        } else {
            $nobukti = (new RunningNumberService)->get($group, $subGroup, $opnameHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
        }
        $opnameHeader->nobukti = $nobukti;
        $opnameHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $opnameHeader->keterangan = $data['keterangan'] ?? '';
        $opnameHeader->gudang_id = $data['gudang_id'];
        $opnameHeader->kelompok_id = $data['kelompok_id'];
        $opnameHeader->info = html_entity_decode(request()->info);

        if (!$opnameHeader->save()) {
            throw new \Exception("Error updating opname header.");
        }

        $opnameHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($opnameHeader->getTable()),
            'postingdari' => 'EDIT OPNAME HEADER',
            'idtrans' => $opnameHeader->id,
            'nobuktitrans' => $opnameHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $opnameHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        OpnameDetail::where('opname_id', $opnameHeader->id)->delete();

        $opnameDetails = [];

        for ($i = 0; $i < count($data['stok_id']); $i++) {

            $opnameDetail = (new OpnameDetail())->processStore($opnameHeader, [
                'stok_id' => $data['stok_id'][$i],
                'qty' => $data['qty'][$i],
                'tglbuktimasuk' => $data['tglbuktimasuk'][$i],
                'qtyfisik' => $data['qtyfisik'][$i]
            ]);
            $opnameDetails[] = $opnameDetail->toArray();
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($opnameDetail->getTable()),
            'postingdari' => 'EDIT OPNAME DETAIL',
            'idtrans' =>  $opnameHeaderLogTrail->id,
            'nobuktitrans' => $opnameHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $opnameDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);

        return $opnameHeader;
    }

    public function processDestroy($id, $postingDari = ''): OpnameHeader
    {
        $opnameDetails = OpnameDetail::lockForUpdate()->where('opname_id', $id)->get();

        $opnameHeader = new OpnameHeader();
        $opnameHeader = $opnameHeader->lockAndDestroy($id);

        $opnameHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => $opnameHeader->getTable(),
            'postingdari' => $postingDari,
            'idtrans' => $opnameHeader->id,
            'nobuktitrans' => $opnameHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $opnameHeader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'OPNAMEDETAIL',
            'postingdari' => $postingDari,
            'idtrans' => $opnameHeaderLogTrail['id'],
            'nobuktitrans' => $opnameHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $opnameDetails->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $opnameHeader;
    }

    public function processApprove(OpnameHeader $opnameHeader)
    {

        $statusApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusBelumApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        if ($opnameHeader->statusapproval == $statusApproval->id) {
            $opnameHeader->statusapproval = $statusBelumApproval->id;
        } else {
            $opnameHeader->statusapproval = $statusApproval->id;
        }

        $opnameHeader->tglapproval = date('Y-m-d', time());
        $opnameHeader->userapproval = auth('api')->user()->name;
        if (!$opnameHeader->save()) {
            throw new \Exception('Error Approval.');
        }
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($opnameHeader->getTable()),
            'postingdari' => "opnameheader",
            'idtrans' => $opnameHeader->id,
            'nobuktitrans' => $opnameHeader->nobukti,
            'aksi' => 'Un/Approve',
            'datajson' => $opnameHeader->toArray(),
            'modifiedby' => auth('api')->user()->name,
        ]);
        return $opnameHeader;
    }


    public function getExport($id)
    {
        $this->setRequestParameters();

        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($this->table)->from(
            DB::raw("opnameheader with (readuncommitted)")
        )->select(
            'opnameheader.id',
            'opnameheader.nobukti',
            'opnameheader.tglbukti',
            'opnameheader.keterangan',
            'gudang.gudang as gudang',
            'opnameheader.jumlahcetak',
            'opnameheader.kelompok_id',
            'kelompok.kodekelompok as kelompok',

            'statuscetak.memo as statuscetak',
            'statuscetak.id as  statuscetak_id',
            DB::raw("'Laporan Opname Header' as judulLaporan"),
            DB::raw("'" . $getJudul->text . "' as judul"),
            DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
        )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'opnameheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("kelompok with (readuncommitted)"), 'opnameheader.kelompok_id', 'kelompok.id')

            ->leftJoin(DB::raw("gudang with (readuncommitted)"), 'opnameheader.gudang_id', 'gudang.id');
        if (request()->tgldari && request()->tglsampai) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(opnameheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(opnameheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("opnameheader.statuscetak", $statusCetak);
        }
        $data = $query->first();
        return $data;
    }
}
