<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\RestrictDeletion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\Controller;


class Agen extends MyModel
{
    use HasFactory, RestrictDeletion;

    protected $table = 'agen';

    protected $casts = [
        'tglapproval' => 'date:d-m-Y',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function cekvalidasihapus($id)
    {

        $suratPengantar = DB::table('suratpengantar')
            ->from(
                DB::raw("suratpengantar as a with (readuncommitted)")
            )
            ->select(
                'a.agen_id'
            )
            ->where('a.agen_id', '=', $id)
            ->first();
        if (isset($suratPengantar)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Surat Pengantar',
            ];
            goto selesai;
        }
        $orderanTrucking = DB::table('orderantrucking')
            ->from(
                DB::raw("orderantrucking as a with (readuncommitted)")
            )
            ->select(
                'a.agen_id'
            )
            ->where('a.agen_id', '=', $id)
            ->first();
        if (isset($orderanTrucking)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Orderan Trucking',
            ];
            goto selesai;
        }
        $piutang = DB::table('piutangheader')
            ->from(
                DB::raw("piutangheader as a with (readuncommitted)")
            )
            ->select(
                'a.agen_id'
            )
            ->where('a.agen_id', '=', $id)
            ->first();
        if (isset($piutang)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'piutang',
            ];
            goto selesai;
        }
        $pelunasanPiutang = DB::table('pelunasanpiutangheader')
            ->from(
                DB::raw("pelunasanpiutangheader as a with (readuncommitted)")
            )
            ->select(
                'a.agen_id'
            )
            ->where('a.agen_id', '=', $id)
            ->first();
        if (isset($pelunasanPiutang)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pelunasan Piutang',
            ];
            goto selesai;
        }

        $invoice = DB::table('invoiceheader')
            ->from(
                DB::raw("invoiceheader as a with (readuncommitted)")
            )
            ->select(
                'a.agen_id'
            )
            ->where('a.agen_id', '=', $id)
            ->first();
        if (isset($invoice)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Invoice',
            ];
            goto selesai;
        }

        $invoiceExtra = DB::table('invoiceextraheader')
            ->from(
                DB::raw("invoiceextraheader as a with (readuncommitted)")
            )
            ->select(
                'a.agen_id'
            )
            ->where('a.agen_id', '=', $id)
            ->first();
        if (isset($invoiceExtra)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Invoice Extra',
            ];
            goto selesai;
        }

        $penerimaanGiro = DB::table('penerimaangiroheader')
            ->from(
                DB::raw("penerimaangiroheader as a with (readuncommitted)")
            )
            ->select(
                'a.agen_id'
            )
            ->where('a.agen_id', '=', $id)
            ->first();
        if (isset($penerimaanGiro)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan Giro',
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

    public function isDeletable()
    {
        $statusApproval = Parameter::from(
            DB::raw("Parameter with (readuncommitted)")
        )->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();

        return $this->statusapproval != $statusApproval->id;
    }

    public function get()
    {
        $this->setRequestParameters();
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $aktif = request()->aktif ?? '';
        $invoice = request()->invoice ?? '';

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'agen.id',
                'agen.kodeagen',
                'agen.namaagen',
                'agen.keterangan',
                'parameter.memo as statusaktif',
                'agen.namaperusahaan',
                'agen.alamat',
                'agen.notelp',
                'agen.contactperson',
                'agen.top',
                'statusapproval.memo as statusapproval',
                'agen.userapproval',
                DB::raw('(case when (year(agen.tglapproval) <= 2000) then null else agen.tglapproval end ) as tglapproval'),
                'statustas.memo as statustas',
                'agen.jenisemkl',
                'agen.created_at',
                'agen.modifiedby',
                'agen.updated_at',
                DB::raw("'Laporan Customer' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'agen.statusaktif', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'agen.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("parameter as statustas with (readuncommitted)"), 'agen.statustas', 'statustas.id');

        // $controller = new Controller;
        // dump($controller->get_client_ip());
        // dd($controller->get_server_ip());



        $this->filter($query);

        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('agen.statusaktif', '=', $statusaktif->id)
                ->where('agen.statusapproval', '=', 3);
        }

        if ($invoice == 'UTAMA') {
            $query->whereRaw("(isnull(agen.coa,'')<>'' or isnull(agen.coapendapatan,'')<>'' ) ");
        }

        // dd($query->toSql());
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->paginate($query);

        $data = $query->get();

            // dd($$query->toSql());
        ;
        return $data;
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->nullable();
            $table->unsignedBigInteger('statustas')->nullable();
            $table->unsignedBigInteger('jenisemkl')->nullable();
            $table->string('keteranganjenisemkl', 255)->nullable();
        });

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusaktif = $status->id ?? 0;

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS TAS')
            ->where('subgrp', '=', 'STATUS TAS')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatustas = $status->id ?? 0;


        $jenisemkl = DB::table('jenisemkl')->from(
            DB::raw('jenisemkl with (readuncommitted)')
        )
            ->select(
                'id as jenisemkl',
                'kodejenisemkl as keteranganjenisemkl',

            )
            ->where('kodejenisemkl', '=', 'TAS')
            ->first();
        DB::table($tempdefault)->insert(
            [
                "statusaktif" => $iddefaultstatusaktif, "statustas" => $iddefaultstatustas,
                "jenisemkl" => $jenisemkl->jenisemkl, "keteranganjenisemkl" => $jenisemkl->keteranganjenisemkl
            ]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statustas',
                'jenisemkl',
                'keteranganjenisemkl',
            );

        $data = $query->first();

        return $data;
    }

    public function findAll($id)
    {
        $query = DB::table("agen")->from(DB::raw("agen with (readuncommitted)"))
            ->select(
                'agen.id',
                'agen.kodeagen',
                'agen.namaagen',
                'agen.keterangan',
                'agen.statusaktif',
                'agen.namaperusahaan',
                "agen.alamat",
                "agen.notelp",
                "agen.nohp",
                "agen.contactperson",
                "agen.top",
                "agen.statustas",
                "agen.coa",
                DB::raw("(trim(coa.coa)+' - '+trim(coa.keterangancoa)) as keterangancoa"),
                DB::raw("(trim(coapendapatan.coa)+' - '+trim(coapendapatan.keterangancoa)) as keterangancoapendapatan"),
                "agen.coapendapatan",
            )
            ->leftJoin(DB::raw("akunpusat as coa with (readuncommitted)"), 'agen.coa', 'coa.coa')
            ->leftJoin(DB::raw("akunpusat as coapendapatan with (readuncommitted)"), 'agen.coapendapatan', 'coapendapatan.coa')
            ->where('agen.id', $id);

        return $query->first();
    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            "$this->table.id",
            "$this->table.kodeagen",
            "$this->table.namaagen",
            "$this->table.keterangan",
            "parameter.text as statusaktif",
            "$this->table.namaperusahaan",
            "$this->table.alamat",
            "$this->table.notelp",
            "$this->table.nohp",
            "$this->table.contactperson",
            "$this->table.top",
            "statusapproval.text as statusapproval",
            "$this->table.userapproval",
            "$this->table.tglapproval",
            "statustas.text as statustas",
            "jenisemkl.keterangan as jenisemkl",
            "$this->table.modifiedby",
            "$this->table.created_at",
            "$this->table.updated_at",
        )
            ->leftJoin(DB::raw("parameter as parameter with (readuncommitted)"), "agen.statusaktif", "parameter.id")
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), "agen.statusapproval", "statusapproval.id")
            ->leftJoin(DB::raw("parameter as statustas with (readuncommitted)"), "agen.statustas", "statustas.id")
            ->leftJoin(DB::raw("jenisemkl with (readuncommitted)"), "agen.jenisemkl", "jenisemkl.id");
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp'  . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kodeagen', 1000)->nullable();
            $table->string('namaagen', 1000)->nullable();
            $table->string('keterangan', 1000)->nullable();
            $table->string('statusaktif', 1000)->nullable();
            $table->string('namaperusahaan', 1000)->nullable();
            $table->string('alamat', 1000)->nullable();
            $table->string('notelp', 1000)->nullable();
            $table->string('nohp', 1000)->nullable();
            $table->string('contactperson', 1000)->nullable();
            $table->string('top', 1000)->nullable();
            $table->string('statusapproval', 1000)->nullable();
            $table->string('userapproval', 1000)->nullable();
            $table->string('tglapproval', 1000)->nullable();
            $table->string('statustas', 1000)->nullable();
            $table->string('jenisemkl', 1000)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->increments('position');
        });

        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $query = $this->sort($query);
        $models = $this->filter($query);

        DB::table($temp)->insertUsing([
            'id',
            'kodeagen',
            'namaagen',
            'keterangan',
            'statusaktif',
            'namaperusahaan',
            'alamat',
            'notelp',
            'nohp',
            'contactperson',
            'top',
            'statusapproval',
            'userapproval',
            'tglapproval',
            'statustas',
            'jenisemkl',
            'modifiedby',
            'created_at',
            'updated_at',
        ], $models);

        return  $temp;
    }

    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('statusapproval.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statustas') {
                            $query = $query->where('statustas.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'check') {
                            $query = $query->whereRaw('1 = 1');
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statusaktif') {
                                $query = $query->orWhere('parameter.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'statusapproval') {
                                $query = $query->orWhere('statusapproval.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'statustas') {
                                $query = $query->orWhere('statustas.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'check') {
                                $query = $query->whereRaw('1 = 1');
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

    public function processStore(array $data): Agen
    {
        $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        $agen = new Agen();
        $agen->kodeagen = $data['kodeagen'];
        $agen->namaagen = $data['namaagen'];
        $agen->keterangan = $data['keterangan'] ?? '';
        $agen->statusaktif = $data['statusaktif'];
        $agen->namaperusahaan = $data['namaperusahaan'];
        $agen->alamat = $data['alamat'];
        $agen->coa = $data['coa'];
        $agen->coapendapatan = $data['coapendapatan'];
        $agen->notelp = $data['notelp'];
        $agen->contactperson = $data['contactperson'];
        $agen->top = $data['top'];
        $agen->statusapproval = $statusNonApproval->id;
        $agen->statustas = $data['statustas'];
        // $agen->jenisemkl = $request->jenisemkl;
        $agen->tglapproval = '';
        $agen->modifiedby = auth('api')->user()->name;
        $agen->info = html_entity_decode(request()->info);
        // $request->sortname = $request->sortname ?? 'id';
        // $request->sortorder = $request->sortorder ?? 'asc';

        if (!$agen->save()) {
            throw new \Exception("Error storing agen.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($agen->getTable()),
            'postingdari' => 'ENTRY AGEN',
            'idtrans' => $agen->id,
            'nobuktitrans' => $agen->id,
            'aksi' => 'ENTRY',
            'datajson' => $agen->toArray(),
            'modifiedby' => $agen->modifiedby
        ]);

        return $agen;
    }

    public function processUpdate(Agen $agen, array $data): Agen
    {
        $agen->kodeagen = $data['kodeagen'];
        $agen->namaagen = $data['namaagen'];
        $agen->keterangan = $data['keterangan'] ?? '';
        $agen->statusaktif = $data['statusaktif'];
        $agen->namaperusahaan = $data['namaperusahaan'];
        $agen->alamat = $data['alamat'];
        $agen->coa = $data['coa'];
        $agen->coapendapatan = $data['coapendapatan'];
        $agen->notelp = $data['notelp'];
        $agen->contactperson = $data['contactperson'];
        $agen->top = $data['top'];
        $agen->statustas = $data['statustas'];
        // $agen->jenisemkl = $request->jenisemkl;
        $agen->modifiedby = auth('api')->user()->name;
        $agen->info = html_entity_decode(request()->info);
        // $request->sortname = $request->sortname ?? 'id';
        // $request->sortorder = $request->sortorder ?? 'asc';

        if (!$agen->save()) {
            throw new \Exception("Error update agen.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($agen->getTable()),
            'postingdari' => 'EDIT AGEN',
            'idtrans' => $agen->id,
            'nobuktitrans' => $agen->id,
            'aksi' => 'EDIT',
            'datajson' => $agen->toArray(),
            'modifiedby' => $agen->modifiedby
        ]);

        return $agen;
    }

    public function processDestroy($id): Agen
    {
        $agen = new Agen();
        $agen = $agen->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($agen->getTable()),
            'postingdari' => 'DELETE AGEN',
            'idtrans' => $agen->id,
            'nobuktitrans' => $agen->id,
            'aksi' => 'DELETE',
            'datajson' => $agen->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $agen;
    }

    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $Agen = Agen::find($data['Id'][$i]);

            $Agen->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            // dd($Agen);
            if ($Agen->save()) {

                (new LogTrail())->processStore([

                    'namatabel' => strtoupper($Agen->getTable()),
                    'postingdari' => 'APPROVAL Agen',
                    'idtrans' => $Agen->id,
                    'nobuktitrans' => $Agen->id,
                    'aksi' => $aksi,
                    'datajson' => $Agen->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }


        return $Agen;
    }


    public function processApproval(array $data)
    {

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $Agen = Agen::find($data['Id'][$i]);

            if ($Agen->statusapproval == $statusApproval->id) {
                $Agen->statusapproval = $statusNonApproval->id;
                $aksi = $statusNonApproval->text;
            } else {
                $Agen->statusapproval = $statusApproval->id;
                $aksi = $statusApproval->text;
            }

            $Agen->tglapproval = date('Y-m-d', time());
            $Agen->userapproval = auth('api')->user()->name;
            if ($Agen->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($Agen->getTable()),
                    'postingdari' => 'APPROVAL Agen',
                    'idtrans' => $Agen->id,
                    'nobuktitrans' => $Agen->id,
                    'aksi' => $aksi,
                    'datajson' => $Agen->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }


        return $Agen;
    }
}
