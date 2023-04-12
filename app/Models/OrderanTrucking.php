<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrderanTrucking extends MyModel
{
    use HasFactory;

    protected $table = 'orderantrucking';

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

    public function cekvalidasihapus($nobukti)
    {

        $suratPengantar = DB::table('suratpengantar')
            ->from(
                DB::raw("suratpengantar as a with (readuncommitted)")
            )
            ->select(
                'a.jobtrucking'
            )
            ->where('a.jobtrucking', '=', $nobukti)
            ->first();
        if (isset($suratPengantar)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Surat Pengantar',
            ];


            goto selesai;
        }


        $invoice = DB::table('invoicedetail')
            ->from(
                DB::raw("invoicedetail as a with (readuncommitted)")
            )
            ->select(
                'a.orderantrucking_nobukti'
            )
            ->where('a.orderantrucking_nobukti', '=', $nobukti)
            ->first();
        if (isset($invoice)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'invoice',
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
        $query = DB::table($this->table)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                'orderantrucking.id',
                'orderantrucking.nobukti',
                'orderantrucking.tglbukti',
                'container.keterangan as container_id',
                'agen.namaagen as agen_id',
                'jenisorder.keterangan as jenisorder_id',
                'pelanggan.namapelanggan as pelanggan_id',
                'tarif.tujuan as tarif_id',
                'orderantrucking.nominal',
                'orderantrucking.nojobemkl',
                'orderantrucking.nocont',
                'orderantrucking.noseal',
                'orderantrucking.nojobemkl2',
                'orderantrucking.nocont2',
                'orderantrucking.noseal2',
                'parameter.memo as statuslangsir',
                'param2.memo as statusperalihan',
                'orderantrucking.modifiedby',
                'orderantrucking.created_at',
                'orderantrucking.updated_at'
            )
            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
            ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'orderantrucking.tarif_id', '=', 'tarif.id')
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'orderantrucking.container_id', '=', 'container.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'orderantrucking.agen_id', '=', 'agen.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'orderantrucking.jenisorder_id', '=', 'jenisorder.id')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'orderantrucking.pelanggan_id', '=', 'pelanggan.id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'orderantrucking.statuslangsir', '=', 'parameter.id')
            ->leftJoin(DB::raw("parameter AS param2 with (readuncommitted)"), 'orderantrucking.statusperalihan', '=', 'param2.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);


        $data = $query->get();

        return $data;
    }

    public function getagentas($id)
    {
        $data = DB::table('agen')
            ->from(DB::raw("agen with (readuncommitted)"))
            ->select(
                DB::raw("(case when jenisemkl.kodejenisemkl='TAS' then 1 else 0 end)  as statustas")
            )
            ->join('jenisemkl', 'jenisemkl.id', 'agen.jenisemkl')
            ->where('agen.id', $id)
            ->first();


        return $data;
    }

    public function getcont($id)
    {
        $data = DB::table('container')
            ->from(DB::raw("container with (readuncommitted)"))
            ->select(
                DB::raw("(case when kodecontainer='2X20`' then 1 else 0 end)  as kodecontainer")
            )
            ->where('container.id', $id)
            ->first();


        return $data;
    }
    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statuslangsir')->nullable();
            $table->unsignedBigInteger('statusperalihan')->nullable();
        });

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS LANGSIR')
            ->where('subgrp', '=', 'STATUS LANGSIR')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatuslangsir = $status->id ?? 0;

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS PERALIHAN')
            ->where('subgrp', '=', 'STATUS PERALIHAN')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusperalihan = $status->id ?? 0;


        DB::table($tempdefault)->insert(
            ["statuslangsir" => $iddefaultstatuslangsir, "statusperalihan" => $iddefaultstatusperalihan]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statuslangsir',
                'statusperalihan',
            );

        $data = $query->first();

        return $data;
    }

    public function findAll($id)
    {
        $query = DB::table('orderantrucking')
            ->from(
                DB::raw("orderantrucking with (readuncommitted)")
            )
            ->select(
                'orderantrucking.id',
                'orderantrucking.nobukti',
                'orderantrucking.tglbukti',
                'orderantrucking.container_id',
                'container.keterangan as container',
                'orderantrucking.agen_id',
                'agen.namaagen as agen',
                'orderantrucking.jenisorder_id',
                'jenisorder.keterangan as jenisorder',
                'orderantrucking.pelanggan_id',
                'pelanggan.namapelanggan as pelanggan',
                'orderantrucking.tarif_id as tarifrincian_id',
                'tarif.tujuan as tarifrincian',
                'orderantrucking.nominal',
                'orderantrucking.nojobemkl',
                'orderantrucking.nocont',
                'orderantrucking.noseal',
                'orderantrucking.nojobemkl2',
                'orderantrucking.nocont2',
                'orderantrucking.noseal2',
                'orderantrucking.statuslangsir',
                'orderantrucking.statusperalihan',
                'orderantrucking.modifiedby',
                'orderantrucking.created_at',
                'orderantrucking.updated_at'
            )
            ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'orderantrucking.tarif_id', '=', 'tarif.id')
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'orderantrucking.container_id', '=', 'container.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'orderantrucking.agen_id', '=', 'agen.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'orderantrucking.jenisorder_id', '=', 'jenisorder.id')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'orderantrucking.pelanggan_id', '=', 'pelanggan.id')
            ->where('orderantrucking.id', $id);

        $data = $query->first();

        return $data;
    }
    public function getOrderanTrip($tglproses, $agen)
    {
        $data = [
            [
                "id" => 1,
                "jobtrucking" => "III/ V /00001",
                "tgltrip" => "2022-09-12",
                "jumlahhari" => "11",
                "nominal_detail" => "21000000",
                "nopolisi" => "BK 1234 AB",
                "keterangan" => "keterangan id 1",
            ], [
                "id" => 2,
                "jobtrucking" => "III/ V /00002",
                "tgltrip" => "2022-09-12",
                "jumlahhari" => "12",
                "nominal_detail" => "22000000",
                "nopolisi" => "BK 4567 AB",
                "keterangan" => "keterangan id 2",
            ], [
                "id" => 3,
                "jobtrucking" => "III/ V /00003",
                "tgltrip" => "2022-09-12",
                "jumlahhari" => "13",
                "nominal_detail" => "23000000",
                "nopolisi" => "BK 1234 AB",
                "keterangan" => "keterangan id 3",
            ]
        ];

        return $data;
    }

    public function agen()
    {
        return $this->belongsTo(Agen::class, 'agen_id');
    }

    public function container()
    {
        return $this->belongsTo(Container::class, 'container_id');
    }

    public function jenisorder()
    {
        return $this->belongsTo(JenisOrder::class, 'jenisorder_id');
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function tarif()
    {
        return $this->belongsTo(Tarif::class, 'tarif_id');
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
            'container.keterangan as container_id',
            'agen.namaagen as agen_id',
            'jenisorder.keterangan as jenisorder_id',
            'pelanggan.namapelanggan as pelanggan_id',
            'tarif.tujuan as tarif_id',
            $this->table.nominal,
            $this->table.nojobemkl,
            $this->table.nocont,
            $this->table.noseal,
            $this->table.nojobemkl2,
            $this->table.nocont2,
            $this->table.noseal2,
            'parameter.text as statuslangsir',
            'param2.text as statusperalihan',
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
                )
            )
            ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'orderantrucking.tarif_id', '=', 'tarif.id')
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'orderantrucking.container_id', '=', 'container.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'orderantrucking.agen_id', '=', 'agen.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'orderantrucking.jenisorder_id', '=', 'jenisorder.id')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'orderantrucking.pelanggan_id', '=', 'pelanggan.id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'orderantrucking.statuslangsir', '=', 'parameter.id')
            ->leftJoin(DB::raw("parameter AS param2 with (readuncommitted)"), 'orderantrucking.statusperalihan', '=', 'param2.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('container_id', 1000)->nullable();
            $table->string('agen_id', 1000)->nullable();
            $table->string('jenisorder_id', 1000)->nullable();
            $table->string('pelanggan_id', 1000)->nullable();
            $table->string('tarif_id', 1000)->nullable();
            $table->string('nominal', 1000)->nullable();
            $table->string('nojobemkl', 1000)->nullable();
            $table->string('nocont', 1000)->nullable();
            $table->string('noseal', 1000)->nullable();
            $table->string('nojobemkl2', 1000)->nullable();
            $table->string('nocont2', 1000)->nullable();
            $table->string('noseal2', 1000)->nullable();
            $table->string('statuslangsir', 1000)->nullable();
            $table->string('statusperalihan', 1000)->nullable();
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
        $models =  $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'container_id', 'agen_id', 'jenisorder_id', 'pelanggan_id', 'tarif_id', 'nominal', 'nojobemkl', 'nocont', 'noseal', 'nojobemkl2', 'nocont2', 'noseal2', 'statuslangsir', 'statusperalihan', 'modifiedby', 'created_at', 'updated_at'], $models);


        return  $temp;
    }
    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'container_id') {
            return $query->orderBy('container.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'agen_id') {
            return $query->orderBy('agen.namaagen', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'jenisorder_id') {
            return $query->orderBy('jenisorder.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'pelanggan_id') {
            return $query->orderBy('pelanggan.namapelanggan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'tarif_id') {
            return $query->orderBy('tarif.tujuan', $this->params['sortOrder']);
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
                        if ($filters['field'] == 'statuslangsir') {
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } elseif ($filters['field'] == 'statusperalihan') {
                            $query = $query->where('param2.text', '=', "$filters[data]");
                        } elseif ($filters['field'] == 'agen_id') {
                            $query = $query->where('agen.namaagen', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'pelanggan_id') {
                            $query = $query->where('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'container_id') {
                            $query = $query->where('container.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'tarif_id') {
                            $query = $query->where('tarif.tujuan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'jenisorder_id') {
                            $query = $query->where('jenisorder.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'nominal') {
                            $query = $query->whereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tglbukti') {
                            $query = $query->whereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statuslangsir') {
                            $query = $query->orWhere('parameter.text', '', "$filters[data]");
                        } elseif ($filters['field'] == 'statusperalihan') {
                            $query = $query->orWhere('param2.text', '', "$filters[data]");
                        } elseif ($filters['field'] == 'agen_id') {
                            $query = $query->orWhere('agen.namaagen', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'pelanggan_id') {
                            $query = $query->orWhere('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'container_id') {
                            $query = $query->orWhere('container.keterangan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'tarif_id') {
                            $query = $query->orWhere('tarif.tujuan', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'jenisorder_id') {
                            $query = $query->orWhere('jenisorder.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'nominal') {
                            $query = $query->orWhereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tglbukti') {
                            $query = $query->orWhereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->orWhereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

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
}
