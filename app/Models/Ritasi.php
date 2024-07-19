<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class Ritasi extends MyModel
{
    use HasFactory;

    protected $table = 'ritasi';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'tglbukti' => 'date:d-m-Y',
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)
            ->select(
                'ritasi.id',
                'ritasi.nobukti',
                'ritasi.tglbukti',
                'parameter.text as statusritasi',
                'ritasi.suratpengantar_nobukti',
                'supir.namasupir as supir_id',
                'trado.kodetrado as trado_id',
                'ritasi.jarak',
                'ritasi.upah',
                'ritasi.extra',
                'ritasi.gaji',
                'dari.keterangan as dari_id',
                'sampai.keterangan as sampai_id',
                'ritasi.modifiedby',
                'ritasi.created_at',
                'ritasi.updated_at',
                db::raw("cast((format(suratpengantar.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheadersuratpengantar"),
                db::raw("cast(cast(format((cast((format(suratpengantar.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheadersuratpengantar"),
            )
            ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), 'ritasi.suratpengantar_nobukti', 'suratpengantar.nobukti')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'ritasi.statusritasi', '=', 'parameter.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'ritasi.supir_id', '=', 'supir.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'ritasi.trado_id', '=', 'trado.id')
            ->leftJoin(DB::raw("kota as dari with (readuncommitted)"), 'ritasi.dari_id', '=', 'dari.id')
            ->leftJoin(DB::raw("kota as sampai with (readuncommitted)"), 'ritasi.sampai_id', '=', 'sampai.id');

        if (request()->tgldari) {
            $query->whereBetween('ritasi.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);


        $data = $query->get();

        // dd($data);
        return $data;
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusritasi')->nullable();
        });

        $statusritasi = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS RITASI')
            ->where('subgrp', '=', 'STATUS RITASI')
            ->where('default', '=', 'YA')
            ->first();
        DB::table($tempdefault)->insert(["statusritasi" => $statusritasi->id]);

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusritasi'
            );

        $data = $query->first();
        // dd($data);
        return $data;
    }

    public function find($id)
    {
        $query = DB::table('ritasi')->select(
            'ritasi.id',
            'ritasi.nobukti',
            'ritasi.tglbukti',
            'ritasi.dataritasi_id as statusritasi_id',
            'parameter.text as statusritasi',
            'ritasi.suratpengantar_nobukti',
            'ritasi.dari_id',
            'dari.kodekota as dari',
            'ritasi.sampai_id',
            'sampai.kodekota as sampai',
            'ritasi.trado_id',
            'trado.kodetrado as trado',
            'ritasi.supir_id',
            'supir.namasupir as supir'
        )
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'ritasi.supir_id', '=', 'supir.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'ritasi.trado_id', '=', 'trado.id')
            ->leftJoin(DB::raw("kota as dari with (readuncommitted)"), 'ritasi.dari_id', '=', 'dari.id')
            ->leftJoin(DB::raw("kota as sampai with (readuncommitted)"), 'ritasi.sampai_id', '=', 'sampai.id')
            ->leftJoin(DB::raw("dataritasi with (readuncommitted)"), 'ritasi.dataritasi_id', 'dataritasi.id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'parameter.id', 'dataritasi.statusritasi')
            ->where('ritasi.id', $id);

        $data = $query->first();
        return $data;
    }
    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
                "$this->table.id,
            $this->table.nobukti,
            $this->table.tglbukti,
            'parameter.text as statusritasi',
            'suratpengantar.nobukti as suratpengantar_nobukti',
            'supir.namasupir as supir_id',
            'trado.kodetrado as trado_id',
            $this->table.jarak,
            $this->table.gaji,
            'dari.keterangan as dari_id',
            'sampai.keterangan as sampai_id',
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )
        )
            ->leftJoin('parameter', 'ritasi.statusritasi', '=', 'parameter.id')
            ->leftJoin('suratpengantar', 'ritasi.suratpengantar_nobukti', '=', 'suratpengantar.nobukti')
            ->leftJoin('supir', 'ritasi.supir_id', '=', 'supir.id')
            ->leftJoin('trado', 'ritasi.trado_id', '=', 'trado.id')
            ->leftJoin('kota as dari', 'ritasi.dari_id', '=', 'dari.id')
            ->leftJoin('kota as sampai', 'ritasi.sampai_id', '=', 'sampai.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('statusritasi', 1000)->nullable();
            $table->string('suratpengantar_nobukti', 1000)->nullable();
            $table->string('supir_id', 1000)->nullable();
            $table->string('trado_id', 1000)->nullable();
            $table->string('jarak', 1000)->nullable();
            $table->string('gaji', 1000)->nullable();
            $table->string('dari_id', 1000)->nullable();
            $table->string('sampai_id', 1000)->nullable();
            $table->string('modifiedby', 50)->nullable();
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
        if (request()->tgldari) {
            $query->whereBetween('tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        $this->sort($query);
        $models = $this->filter($query);
        $models = $query
            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);

        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'statusritasi', 'suratpengantar_nobukti', 'supir_id', 'trado_id', 'jarak', 'gaji', 'dari_id', 'sampai_id', 'modifiedby', 'created_at', 'updated_at'], $models);


        return  $temp;
    }
    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'dari_id') {
            return $query->orderBy('dari.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'sampai_id') {
            return $query->orderBy('sampai.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'supir_id') {
            return $query->orderBy('supir.namasupir', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'trado_id') {
            return $query->orderBy('trado.kodetrado', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'statusritasi') {
            return $query->orderBy('parameter.text', $this->params['sortOrder']);
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
                        if ($filters['field'] == 'statusritasi') {
                            $query = $query->where('parameter.text', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'supir_id') {
                            $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'trado_id') {
                            $query = $query->where('trado.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'dari_id') {
                            $query = $query->where('dari.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'sampai_id') {
                            $query = $query->where('sampai.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'jarak' || $filters['field'] == 'gaji' || $filters['field'] == 'upah' || $filters['field'] == 'extra') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tglbukti') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' .$filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statusritasi') {
                                $query = $query->orWhere('parameter.text', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'supir_id') {
                                $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'trado_id') {
                                $query = $query->where('trado.keterangan', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'dari_id') {
                                $query = $query->where('dari.keterangan', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'sampai_id') {
                                $query = $query->where('sampai.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'jarak' || $filters['field'] == 'gaji' || $filters['field'] == 'upah' || $filters['field'] == 'extra') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->orWhere($this->table . '.' .$filters['field'], 'LIKE', "%$filters[data]%");
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
    public function cekvalidasiaksi($nobukti)
    {

        $gajiSupir = DB::table('gajisupirdetail')
            ->from(
                DB::raw("gajisupirdetail as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.ritasi_nobukti'
            )
            ->where('a.ritasi_nobukti', '=', $nobukti)
            ->first();
        if (isset($gajiSupir)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'GAJI SUPIR ' . $gajiSupir->nobukti,
                'kodeerror' => 'SATL'
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

    public function cekUpahRitasi($dari, $sampai)
    {
        $query = DB::table("upahritasi")->from(DB::raw("upahritasi with (readuncommitted)"))
            ->select(DB::raw("upahritasi.nominalsupir"))
            ->whereRaw("((upahritasi.kotadari_id=" . $dari . " and upahritasi.kotasampai_id=" . $sampai . ") or (upahritasi.kotasampai_id=" . $dari . " and upahritasi.kotadari_id=" . $sampai . "))")
            // ->whereRaw('upahritasi.kotasampai_id', $sampai)
            ->whereRaw("upahritasi.nominalsupir <> 0")
            ->where("upahritasi.statusaktif", '1')
            ->first();
        return $query;
    }

    public function processStore(array $data): Ritasi
    {
        $urutke = 0;

        $querytrip = DB::table('ritasi')->from(
            db::raw("ritasi a with (readuncommitted)")
        )
            ->select(
                'a.suratpengantar_urutke'
            )
            ->where('a.suratpengantar_nobukti', '=', $data['suratpengantar_nobukti'])
            ->whereRaw("isnull(a.suratpengantar_nobukti,'')<>''")
            ->orderby('a.suratpengantar_urutke', 'desc')
            ->first();

        if (isset($querytrip)) {
            $urutke = $querytrip->suratpengantar_urutke + 1;
        } else {
            $urutke = 1;
        }

        // dd($urutke);
        $group = 'RITASI';
        $subGroup = 'RITASI';

        $format = DB::table('parameter')
            ->where('grp', $group)
            ->where('subgrp', $subGroup)
            ->first();
        $upahRitasi = DB::table('upahritasi')
            ->whereRaw("(upahritasi.kotadari_id=" . $data['dari_id'] . " and upahritasi.kotasampai_id=" . $data['sampai_id'] . ") or (upahritasi.kotasampai_id=" . $data['dari_id'] . " and upahritasi.kotadari_id=" . $data['sampai_id'] . ")")->first();
        $extra = DB::table("dataritasi")->from(DB::raw("dataritasi with (readuncommitted)"))->where('id', $data['statusritasi_id'])->first();
        $extraNominal = $extra->nominal ?? 0;
        $ritasi = new Ritasi();
        $ritasi->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $ritasi->statusritasi = $extra->statusritasi;
        $ritasi->dataritasi_id = $data['statusritasi_id'];
        $ritasi->suratpengantar_nobukti = $data['suratpengantar_nobukti'];
        $ritasi->suratpengantar_urutke = $urutke;
        $ritasi->supir_id = $data['supir_id'];
        $ritasi->trado_id = $data['trado_id'];
        $ritasi->dari_id = $data['dari_id'];
        $ritasi->sampai_id = $data['sampai_id'];
        $ritasi->jarak = $upahRitasi->jarak ?? 0;
        $ritasi->upah = $upahRitasi->nominalsupir ?? 0;
        $ritasi->extra = $extra->nominal ?? 0;
        $ritasi->gaji = $upahRitasi->nominalsupir + $extraNominal;
        $ritasi->statusformat = $format->id;
        $ritasi->modifiedby = auth('api')->user()->name;
        $ritasi->info = html_entity_decode(request()->info);
        $ritasi->nobukti = (new RunningNumberService)->get($group, $subGroup, $ritasi->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$ritasi->save()) {
            throw new \Exception("Error storing ritasi.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($ritasi->getTable()),
            'postingdari' => 'ENTRY RITASI',
            'idtrans' => $ritasi->id,
            'nobuktitrans' => $ritasi->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $ritasi->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);


        return $ritasi;
    }

    public function processUpdate(Ritasi $ritasi, array $data): Ritasi
    {



        $upahRitasi = DB::table('upahritasi')
            ->whereRaw("(upahritasi.kotadari_id=" . $data['dari_id'] . " and upahritasi.kotasampai_id=" . $data['sampai_id'] . ") or (upahritasi.kotasampai_id=" . $data['dari_id'] . " and upahritasi.kotadari_id=" . $data['sampai_id'] . ")")->first();
        $extra = DB::table("dataritasi")->from(DB::raw("dataritasi with (readuncommitted)"))->where('id', $data['statusritasi_id'])->first();
        $extraNominal = $extra->nominal ?? 0;
        $ritasi->statusritasi = $extra->statusritasi;
        $ritasi->dataritasi_id = $data['statusritasi_id'];
        $ritasi->suratpengantar_nobukti = $data['suratpengantar_nobukti'];
        $ritasi->supir_id = $data['supir_id'];
        $ritasi->trado_id = $data['trado_id'];
        $ritasi->jarak = $upahRitasi->jarak ?? 0;
        $ritasi->upah = $upahRitasi->nominalsupir ?? 0;
        $ritasi->extra = $extra->nominal ?? 0;
        $ritasi->gaji = $upahRitasi->nominalsupir + $extraNominal;
        $ritasi->dari_id = $data['dari_id'];
        $ritasi->sampai_id = $data['sampai_id'];
        $ritasi->modifiedby = auth('api')->user()->name;
        $ritasi->info = html_entity_decode(request()->info);

        $notrip = $data['suratpengantar_nobukti'] ?? '';

        $queryritasi = DB::table("ritasi")->from(
            db::raw("ritasi a with (readuncommitted)")
        )
            ->select(
                'a.nobukti'
            )
            ->where('a.suratpengantar_nobukti', $notrip)
            ->orderBy('a.nobukti', 'asc')
            ->get();
        $urutke = 0;
        $datadetail = json_decode($queryritasi, true);
        foreach ($datadetail as $item) {
            $urutke = $urutke + 1;
            $ritasiUpdate  = Ritasi::lockForUpdate()->where("nobukti", $item['nobukti'])
                ->firstorFail();
            $ritasiUpdate->suratpengantar_urutke = $urutke;
            $ritasiUpdate->save();
        }

        if (!$ritasi->save()) {
            throw new \Exception("Error updating ritasi.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($ritasi->getTable()),
            'postingdari' => 'EDIT RITASI',
            'idtrans' => $ritasi->id,
            'nobuktitrans' => $ritasi->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $ritasi->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        return $ritasi;
    }

    public function processDestroy($id): Ritasi
    {
        $ritasi = new Ritasi();
        $ritasi = $ritasi->lockAndDestroy($id);

        $notripquery = db::table("ritasi")->from(
            db::raw("ritasi a with (readuncommitted)")
        )
            ->select(
                'a.suratpengantar_nobukti'
            )
            ->where('a.id', $id)
            ->first();

        if (isset($notripquery)) {
            $notrip = $notripquery->suratpengantar_nobukti;
        } else {
            $notrip = '';
        }

        if ($notrip != '') {

            $queryritasi = DB::table("ritasi")->from(
                db::raw("ritasi a with (readuncommitted)")
            )
                ->select(
                    'a.nobukti'
                )
                ->where('a.suratpengantar_nobukti', $notrip)
                ->orderBy('a.nobukti', 'asc')
                ->get();
            $urutke = 0;
            $datadetail = json_decode($queryritasi, true);
            foreach ($datadetail as $item) {
                $urutke = $urutke + 1;
                $ritasiUpdate  = Ritasi::lockForUpdate()->where("nobukti", $item['nobukti'])
                    ->firstorFail();
                $ritasiUpdate->suratpengantar_urutke = $urutke;
                $ritasiUpdate->save();
            }
        }


        (new LogTrail())->processStore([
            'namatabel' => $ritasi->getTable(),
            'postingdari' => 'DELETE RITASI',
            'idtrans' => $ritasi->id,
            'nobuktitrans' => $ritasi->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $ritasi->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $ritasi;
    }

    public function getExport($dari, $sampai)
    {
        $this->setRequestParameters();
        $getParameter = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))
            ->select(
                'text as judul',
                DB::raw("'Laporan Ritasi' as judulLaporan")
            )->where('grp', 'JUDULAN LAPORAN')->where('subgrp', 'JUDULAN LAPORAN')->first();

        $query = DB::table($this->table)
            ->select(
                'ritasi.id',
                'ritasi.nobukti',
                'ritasi.tglbukti',
                'parameter.text as statusritasi',
                'suratpengantar.nobukti as suratpengantar_nobukti',
                'supir.namasupir as supir_id',
                'trado.kodetrado as trado_id',
                'ritasi.jarak',
                'ritasi.gaji',
                'dari.keterangan as dari_id',
                'sampai.keterangan as sampai_id',
                DB::raw("'" . $dari . "' as tgldari"),
                DB::raw("'" . $sampai . "' as tglsampai"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime($dari)), date('Y-m-d', strtotime($sampai))])
            ->leftJoin('parameter', 'ritasi.statusritasi', '=', 'parameter.id')
            ->leftJoin('suratpengantar', 'ritasi.suratpengantar_nobukti', '=', 'suratpengantar.nobukti')
            ->leftJoin('supir', 'ritasi.supir_id', '=', 'supir.id')
            ->leftJoin('trado', 'ritasi.trado_id', '=', 'trado.id')
            ->leftJoin('kota as dari', 'ritasi.dari_id', '=', 'dari.id')
            ->leftJoin('kota as sampai', 'ritasi.sampai_id', '=', 'sampai.id');

        $data = $query->get();
        $allData = [
            'data' => $data,
            'parameter' => $getParameter
        ];
        return $allData;
    }

    public function ExistTradoSupirRitasi($nobukti)
    {
        $query = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->select('trado_id', 'supir_id')
            ->where('nobukti', $nobukti)
            ->first();
        return $query;
    }
}
