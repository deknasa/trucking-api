<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Kota extends MyModel
{
    use HasFactory;

    protected $table = 'kota';

    // protected $casts = [
    //     'created_at' => 'date:d-m-Y H:i:s',
    //     'updated_at' => 'date:d-m-Y H:i:s'
    // ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function cekvalidasihapus($id)
    {
        $tarif = DB::table('tarif')
            ->from(
                DB::raw("tarif as a with (readuncommitted)")
            )
            ->select(
                'a.kota_id'
            )
            ->where('a.kota_id', '=', $id)
            ->first();
        if (isset($tarif)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Tarif',
            ];
            goto selesai;
        }

        $suratpengantar = DB::table('suratpengantar')
            ->from(
                DB::raw("suratpengantar as a with (readuncommitted)")
            )
            ->select(
                'a.dari_id',
                'a.sampai_id'
            )
            ->where('a.dari_id', '=', $id)
            ->where('a.sampai_id', '=', $id)
            ->first();
        if (isset($suratpengantar)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Surat Pengantar',
            ];
            goto selesai;
        }

        $upahSupir = DB::table('upahsupir')
            ->from(
                DB::raw("upahsupir as a with (readuncommitted)")
            )
            ->select(
                'a.kotadari_id',
                'a.kotasampai_id',
            )
            ->where('a.kotadari_id', '=', $id)
            ->where('a.kotasampai_id', '=', $id)
            ->first();
        if (isset($upahSupir)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Upah Supir',
            ];
            goto selesai;
        }

        $upahRitasi = DB::table('upahritasi')
            ->from(
                DB::raw("upahritasi as a with (readuncommitted)")
            )
            ->select(
                'a.kotadari_id',
                'a.kotasampai_id'
            )
            ->where('a.kotadari_id', '=', $id)
            ->where('a.kotasampai_id', '=', $id)
            ->first();
        if (isset($upahRitasi)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Upah Ritasi',
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

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();
        $kotaPelabuhan = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', 'PELABUHAN CABANG')->where('subgrp', 'PELABUHAN CABANG')->first();
        $kotaKandang = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', 'KANDANG')->where('subgrp', 'KANDANG')->first();


        $aktif = request()->aktif ?? '';
        $kotaDariId = request()->kotadari_id ?? '';
        $kotaSampaiId = request()->kotasampai_id ?? '';
        $pilihKotaId = request()->pilihkota_id ?? '';
        $dataRitasiId = request()->dataritasi_id ?? '';
        $ritasiDariKe = request()->ritasidarike ?? '';
        $upahSupirDariKe = request()->upahSupirDariKe ?? '';
        $upahSupirKotaDari = request()->upahSupirKotaDari ?? '';
        $kotaZona = request()->kotaZona ?? '';

        $idkandang = (new Parameter())->cekText('KANDANG', 'KANDANG') ?? 0;

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'kota.id',
                'kota.kodekota',
                'kota.keterangan',
                'zona.zona as zona_id',
                'parameter.memo as statusaktif',
                'kota.modifiedby',
                'kota.created_at',
                'kota.updated_at',
                DB::raw("'Laporan Kota' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'kota.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("zona with (readuncommitted)"), 'kota.zona_id', '=', 'zona.id');

        $this->filter($query);
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('kota.statusaktif', '=', $statusaktif->id);
        }
        if ($kotaDariId > 0 && $kotaSampaiId > 0) {
            $query->whereRaw("kota.id in ($kotaDariId,$kotaSampaiId)");
        }
        if ($kotaZona > 0) {
            $query->whereRaw("kota.zona_id = $kotaZona");
        }
        if ($pilihKotaId > 0) {
            $query->whereRaw("kota.id != $pilihKotaId");
        }
        // if ($dataRitasiId != '') {
        //     $ritasiPulang = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS RITASI')->where('text', 'PULANG RANGKA')->first();
        //     $ritasiTurun = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS RITASI')->where('text', 'TURUN RANGKA')->first();
        //     if ($dataRitasiId == $ritasiPulang->id) {
        //         if ($ritasiDariKe == 'dari') {
        //             $query->where("kodekota", 'BELAWAN RANGKA')->first();
        //         } else {
        //             $query->where("kodekota", 'KIM (KANDANG)')->first();
        //         }
        //     } else if ($dataRitasiId == $ritasiTurun->id) {
        //         if ($ritasiDariKe == 'dari') {
        //             $query->where("kodekota", 'KIM (KANDANG)')->first();
        //         } else {
        //             $query->where("kodekota", 'BELAWAN RANGKA')->first();
        //         }
        //     }
        // }
        if($ritasiDariKe != ''){
            $query->whereRaw("kota.id != $idkandang");
        }
        if ($upahSupirDariKe == 'dari') {
            $query->whereRaw("kota.id != $kotaKandang->text");
        }
        if ($upahSupirDariKe == 'ke') {
            if ($upahSupirKotaDari == $kotaPelabuhan->text) {
                $query->whereRaw("kota.id != $kotaKandang->text");
            }
        }

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->paginate($query);

        $data = $query->get();
        return $data;
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->nullable();
            $table->string('statusaktifnama', 300)->nullable();
        });

        $statusaktif = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'memo',
                'id',
                'text'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('default', '=', 'YA')
            ->first();

        DB::table($tempdefault)->insert(["statusaktif" => $statusaktif->id ?? 0, "statusaktifnama" => $statusaktif->text ?? ""]);
        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusaktifnama'
            );

        $data = $query->first();
        // dd($data);
        return $data;
    }
    public function findAll($id)
    {

        $query = Kota::from(DB::raw("kota with (readuncommitted)"))
            ->select(DB::raw('kota.*, zona.zona as zona, parameter.text as statusaktifnama'))
            ->leftJoin(DB::raw("zona with (readuncommitted)"), 'kota.zona_id', 'zona.id')->whereRaw("kota.id = $id")
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'kota.statusaktif', '=', 'parameter.id');

        $data = $query->first();
        return $data;
    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            DB::raw(
                "$this->table.id,
            $this->table.kodekota,
            $this->table.keterangan,
            'zona.zona',
            'parameter.text as statusaktif',
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )
        )

            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'kota.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("zona with (readuncommitted)"), 'kota.zona_id', '=', 'zona.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kodekota', 1000)->nullable();
            $table->string('keterangan', 1000)->nullable();
            $table->string('zona', 1000)->nullable();
            $table->string('statusaktif', 1000)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'kodekota', 'keterangan', 'zona', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $models);


        return  $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'zona_id') {
            return $query->orderBy('zona.zona', $this->params['sortOrder']);
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
                            if ($filters['field'] == 'statusaktif') {
                                $query = $query->where('parameter.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'zona_id') {
                                $query = $query->where('zona.zona', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->where('kota.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw('kota' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'statusaktif') {
                                    $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'zona_id') {
                                    $query = $query->orWhere('zona.zona', 'LIKE', "%$filters[data]%");
                                } else {
                                    // $query = $query->orWhere('kota.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw('kota' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function processStore(array $data, Kota $kota): Kota
    {
        // $kota = new Kota();
        $kota->kodekota = $data['kodekota'];
        $kota->keterangan = $data['keterangan'] ?? '';
        $kota->zona_id = $data['zona_id'];
        $kota->statusaktif = $data['statusaktif'];
        $kota->tas_id = $data['tas_id'] ?? '';
        $kota->modifiedby = auth('api')->user()->user;
        $kota->info = html_entity_decode(request()->info);

        if (!$kota->save()) {
            throw new \Exception('Error storing kota.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($kota->getTable()),
            'postingdari' => 'ENTRY KOTA',
            'idtrans' => $kota->id,
            'nobuktitrans' => $kota->id,
            'aksi' => 'ENTRY',
            'datajson' => $kota->toArray(),
            'modifiedby' => $kota->modifiedby
        ]);

        return $kota;
    }

    public function processUpdate(Kota $kota, array $data): Kota
    {
        // $kota = Kota::find($data['id']);
        $kota->kodekota = $data['kodekota'];
        $kota->keterangan = $data['keterangan'] ?? '';
        $kota->zona_id = $data['zona_id'];
        $kota->statusaktif = $data['statusaktif'];
        $kota->modifiedby = auth('api')->user()->user;
        $kota->info = html_entity_decode(request()->info);

        if (!$kota->save()) {
            throw new \Exception('Error updating kota.');
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($kota->getTable()),
            'postingdari' => 'EDIT KOTA',
            'idtrans' => $kota->id,
            'nobuktitrans' => $kota->id,
            'aksi' => 'EDIT',
            'datajson' => $kota->toArray(),
            'modifiedby' => $kota->modifiedby
        ]);

        return $kota;
    }

    public function processDestroy(Kota $kota): Kota
    {
        // $kota = new Kota();
        $kota = $kota->lockAndDestroy($kota->id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($kota->getTable()),
            'postingdari' => 'DELETE KOTA',
            'idtrans' => $kota->id,
            'nobuktitrans' => $kota->id,
            'aksi' => 'DELETE',
            'datajson' => $kota->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        return $kota;
    }

    public function processApprovalnonaktif(array $data)
    {
        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $kota = Kota::find($data['Id'][$i]);

            $kota->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            if ($kota->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($kota->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF KOTA',
                    'idtrans' => $kota->id,
                    'nobuktitrans' => $kota->id,
                    'aksi' => $aksi,
                    'datajson' => $kota->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $kota;
    }

    public function processApprovalaktif(array $data)
    {
        $statusaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $kota = Kota::find($data['Id'][$i]);

            $kota->statusaktif = $statusaktif->id;
            $aksi = $statusaktif->text;

            if ($kota->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($kota->getTable()),
                    'postingdari' => 'APPROVAL AKTIF KOTA',
                    'idtrans' => $kota->id,
                    'nobuktitrans' => $kota->id,
                    'aksi' => $aksi,
                    'datajson' => $kota->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $kota;
    }

    public function cekdataText($id)
    {
        $query = DB::table('kota')->from(db::raw("kota a with (readuncommitted)"))
            ->select(
                'a.kodekota as keterangan'
            )
            ->where('id', $id)
            ->first();

        $keterangan = $query->keterangan ?? '';

        return $keterangan;
    }
}
