<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\RestrictDeletion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AlatBayar extends MyModel
{
    use HasFactory, RestrictDeletion;

    protected $table = 'alatbayar';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function cekvalidasihapus($id)
    {
        $pengeluaranHeader = DB::table('pengeluaranheader')
            ->from(
                DB::raw("pengeluaranheader as a with (readuncommitted)")
            )
            ->select(
                'a.alatbayar_id'
            )
            ->where('a.alatbayar_id', '=', $id)
            ->first();
        if (isset($pengeluaranHeader)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pengeluaran',
            ];
            goto selesai;
        }

        $pelunasanPiutang = DB::table('pelunasanpiutangheader')
            ->from(
                DB::raw("pelunasanpiutangheader as a with (readuncommitted)")
            )
            ->select(
                'a.alatbayar_id'
            )
            ->where('a.alatbayar_id', '=', $id)
            ->first();
        if (isset($pelunasanPiutang)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pelunasan Piutang',
            ];
            goto selesai;
        }
        $hutangBayar = DB::table('hutangbayarheader')
            ->from(
                DB::raw("hutangbayarheader as a with (readuncommitted)")
            )
            ->select(
                'a.alatbayar_id'
            )
            ->where('a.alatbayar_id', '=', $id)
            ->first();
        if (isset($hutangBayar)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Hutang Bayar',
            ];
            goto selesai;
        }
        $pencairanGiroPengeluaran = DB::table('pencairangiropengeluarandetail')
            ->from(
                DB::raw("pencairangiropengeluarandetail as a with (readuncommitted)")
            )
            ->select(
                'a.alatbayar_id'
            )
            ->where('a.alatbayar_id', '=', $id)
            ->first();
        if (isset($pencairanGiroPengeluaran)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pencairan Giro Pengeluaran',
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
        $aktif = request()->aktif ?? '';
        // dd(request()->all());
        $bank_id = request()->bank_id ?? 0;

        $bank = Bank::from(
            db::Raw("bank with (readuncommitted)")
        )
            ->select(
                'tipe'
            )
            ->where('id', '=', $bank_id)
            ->first();

        $tipe = $bank->tipe ?? '';

        $statusdefault = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS DEFAULT')
            ->where('subgrp', '=', 'STATUS DEFAULT')
            ->where('text', '=', 'DEFAULT')
            ->first();

        $default = request()->statusdefault ?? 0;
        $query = DB::table($this->table)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                'alatbayar.id',
                'alatbayar.kodealatbayar',
                'alatbayar.namaalatbayar',
                'alatbayar.keterangan',
                'parameter_statuslangsungcair.memo as statuslangsungcair',
                'parameter_statusdefault.memo as statusdefault',
                'parameter.memo as statusaktif',
                'alatbayar.bank_id',
                'bank.namabank as bank',
                'alatbayar.modifiedby',
                'alatbayar.created_at',
                'alatbayar.updated_at'
            )
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'alatbayar.bank_id', 'bank.id')
            ->leftJoin(DB::raw("parameter as parameter_statuslangsungcair with (readuncommitted)"), 'alatbayar.statuslangsungcair', 'parameter_statuslangsungcair.id')
            ->leftJoin(DB::raw("parameter as parameter_statusdefault with (readuncommitted)"), 'alatbayar.statusdefault', 'parameter_statusdefault.id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'alatbayar.statusaktif', 'parameter.id');


        $this->filter($query);
        if ($default == $statusdefault->id) {
            $query->where('alatbayar.statusdefault', '=', $statusdefault->id);
        }
        if ($tipe != "") {
            $query->where('bank.tipe', '=', $tipe);
        }

        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('alatbayar.statusaktif', '=', $statusaktif->id);
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
            $table->unsignedBigInteger('statusdefault')->nullable();
            $table->unsignedBigInteger('statuslangsungcair')->nullable();
            $table->unsignedBigInteger('statusaktif')->nullable();
        });

        // STATUS DEFAULT
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS DEFAULT')
            ->where('subgrp', '=', 'STATUS DEFAULT')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusdefault = $status->id ?? 0;

        //  STATUS LANGSUNG CAIR
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS LANGSUNG CAIR')
            ->where('subgrp', '=', 'STATUS LANGSUNG CAIR')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatuslangsung = $status->id ?? 0;


        $statusaktif = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('DEFAULT', '=', 'YA')
            ->first();

        $iddefaultstatusaktif = $statusaktif->id ?? 0;

        DB::table($tempdefault)->insert(
            [
                "statusdefault" => $iddefaultstatusdefault, "statuslangsungcair" => $iddefaultstatuslangsung,
                "statusaktif" => $iddefaultstatusaktif
            ]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusdefault',
                'statuslangsungcair',
                'statusaktif',
            );

        $data = $query->first();

        return $data;
    }

    public function find($id)
    {
        $query = DB::table('alatbayar')
            ->from(
                DB::raw("alatbayar with (readuncommitted)")
            )->select(
                'alatbayar.id',
                'alatbayar.kodealatbayar',
                'alatbayar.namaalatbayar',
                'alatbayar.keterangan',
                'alatbayar.statuslangsungcair',
                'alatbayar.statusdefault',
                'alatbayar.statusaktif',
                'alatbayar.bank_id',
                'bank.namabank as bank',
                'alatbayar.coa',
                'akunpusat.keterangancoa'
            )
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'alatbayar.bank_id', 'bank.id')
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'alatbayar.coa', 'akunpusat.coa')
            ->where('alatbayar.id', $id);

        $data = $query->first();

        return $data;
    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw("
                $this->table.id,
                $this->table.kodealatbayar,
                $this->table.namaalatbayar,
                $this->table.keterangan,
                'parameter_statuslangsungcair.text as statuslangsungcair',
                'parameter_statusdefault.text as statusdefault',
                'parameter_statusaktif.text as statusaktif',
                'bank.namabank as bank_id',
                $this->table.modifiedby,
                $this->table.created_at,
                $this->table.updated_at
                
            ")
            )
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'alatbayar.bank_id', 'bank.id')
            ->leftJoin(DB::raw("parameter as parameter_statuslangsungcair with (readuncommitted)"), 'alatbayar.statuslangsungcair', 'parameter_statuslangsungcair.id')
            ->leftJoin(DB::raw("parameter as parameter_statusdefault with (readuncommitted)"), 'alatbayar.statusdefault', 'parameter_statusdefault.id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'alatbayar.statusaktif', 'parameter.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kodealatbayar', 1000)->nullable();
            $table->string('namaalatbayar', 1000)->nullable();
            $table->string('keterangan', 1000)->nullable();
            $table->string('statuslangsungcair')->nullable();
            $table->string('statusdefault')->nullable();
            $table->string('statusaktif')->nullable();
            $table->string('bank_id')->nullable();
            $table->string('modifiedby')->default();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'kodealatbayar', 'namaalatbayar', 'keterangan', 'statuslangsungcair', 'statusdefault', 'statusaktif', 'bank_id', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'bank') {
            return $query->orderBy('bank.namabank', $this->params['sortOrder']);
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
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statuslangsungcair') {
                            $query = $query->where('parameter_statuslangsungcair.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statusdefault') {
                            $query = $query->where('parameter_statusdefault.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'bank') {
                            $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
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
                                $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statuslangsungcair') {
                                $query = $query->orWhere('parameter_statuslangsungcair.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusdefault') {
                                $query = $query->orWhere('parameter_statusdefault.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'bank') {
                                $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
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

    public function validateBankWithAlatbayar($bank_id)
    {
        $bank = Bank::from(
            db::Raw("bank with (readuncommitted)")
        )
            ->select(
                'tipe'
            )
            ->where('id', '=', $bank_id)
            ->first();

        $query = DB::table($this->table)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                'alatbayar.id',
                'alatbayar.kodealatbayar',
                'alatbayar.bank_id',
                'bank.namabank as bank',
            )
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'alatbayar.bank_id', 'bank.id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'alatbayar.statusaktif', 'parameter.id')
            ->where('bank.tipe', $bank->tipe)
            ->get();


        return $query;
    }
}
