<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Bank extends MyModel
{
    use HasFactory;

    protected $table = 'bank';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    public function cekvalidasihapus($id)
    {
        $pengeluaranHeader = DB::table('pengeluaranheader')
            ->from(
                DB::raw("pengeluaranheader as a with (readuncommitted)")
            )
            ->select(
                'a.bank_id'
            )
            ->where('a.bank_id', '=', $id)
            ->first();
        if (isset($pengeluaranHeader)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pengeluaran',
            ];
            goto selesai;
        }

        $penerimaanHeader = DB::table('penerimaanheader')
            ->from(
                DB::raw("penerimaanheader as a with (readuncommitted)")
            )
            ->select(
                'a.bank_id'
            )
            ->where('a.bank_id', '=', $id)
            ->first();
        if (isset($penerimaanHeader)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan',
            ];
            goto selesai;
        }
        $kasgantungHeader = DB::table('kasgantungheader')
            ->from(
                DB::raw("kasgantungheader as a with (readuncommitted)")
            )
            ->select(
                'a.bank_id'
            )
            ->where('a.bank_id', '=', $id)
            ->first();
        if (isset($kasgantungHeader)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Kas Gantung',
            ];
            goto selesai;
        }

        $penerimaanGiroDetail = DB::table('penerimaangirodetail')
            ->from(
                DB::raw("penerimaangirodetail as a with (readuncommitted)")
            )
            ->select(
                'a.bank_id'
            )
            ->where('a.bank_id', '=', $id)
            ->first();
        if (isset($penerimaanGiroDetail)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan Giro',
            ];
            goto selesai;
        }

        $rekapPenerimaanHeader = DB::table('rekappenerimaanheader')
            ->from(
                DB::raw("rekappenerimaanheader as a with (readuncommitted)")
            )
            ->select(
                'a.bank_id'
            )
            ->where('a.bank_id', '=', $id)
            ->first();
        if (isset($rekapPenerimaanHeader)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Rekap Penerimaan',
            ];
            goto selesai;
        }

        $rekapPengeluaranHeader = DB::table('rekappengeluaranheader')
            ->from(
                DB::raw("rekappengeluaranheader as a with (readuncommitted)")
            )
            ->select(
                'a.bank_id'
            )
            ->where('a.bank_id', '=', $id)
            ->first();
        if (isset($rekapPengeluaranHeader)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Rekap Pengeluaran',
            ];
            goto selesai;
        }
        $penerimaanTrucking = DB::table('penerimaantruckingheader')
            ->from(
                DB::raw("penerimaantruckingheader as a with (readuncommitted)")
            )
            ->select(
                'a.bank_id'
            )
            ->where('a.bank_id', '=', $id)
            ->first();
        if (isset($penerimaanTrucking)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan Trucking',
            ];
            goto selesai;
        }
        $pengeluaranTrucking = DB::table('pengeluarantruckingheader')
            ->from(
                DB::raw("pengeluarantruckingheader as a with (readuncommitted)")
            )
            ->select(
                'a.bank_id'
            )
            ->where('a.bank_id', '=', $id)
            ->first();
        if (isset($pengeluaranTrucking)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pengeluaran Trucking',
            ];
            goto selesai;
        }
        $pelunasanPiutang = DB::table('pelunasanpiutangheader')
            ->from(
                DB::raw("pelunasanpiutangheader as a with (readuncommitted)")
            )
            ->select(
                'a.bank_id'
            )
            ->where('a.bank_id', '=', $id)
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
                'a.bank_id'
            )
            ->where('a.bank_id', '=', $id)
            ->first();
        if (isset($hutangBayar)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Hutang Bayar',
            ];
            goto selesai;
        }

        $pengeluaranStok = DB::table('pengeluaranstokheader')
            ->from(
                DB::raw("pengeluaranstokheader as a with (readuncommitted)")
            )
            ->select(
                'a.bank_id'
            )
            ->where('a.bank_id', '=', $id)
            ->first();
        if (isset($pengeluaranStok)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pengeluaran Stok',
            ];
            goto selesai;
        }

        $alatBayar = DB::table('alatbayar')
            ->from(
                DB::raw("alatbayar as a with (readuncommitted)")
            )
            ->select(
                'a.bank_id'
            )
            ->where('a.bank_id', '=', $id)
            ->first();
        if (isset($alatBayar)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Alat Bayar',
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
        $tipe = request()->tipe ?? '';
        $bankId = request()->bankId ?? 0;

        $query = DB::table($this->table)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                'bank.id',
                'bank.kodebank',
                'bank.namabank',
                'akunpusat.keterangancoa as coa',
                'bank.tipe',
                'parameter.memo as statusaktif',
                'formatpenerimaan.memo as formatpenerimaan',
                'formatpengeluaran.memo as formatpengeluaran',
                'bank.modifiedby',
                'bank.created_at',
                'bank.updated_at'
            )
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'bank.coa', '=', 'akunpusat.coa')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'bank.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("parameter as formatpenerimaan with (readuncommitted)"), 'bank.formatpenerimaan', '=', 'formatpenerimaan.id')
            ->leftJoin(DB::raw("parameter as formatpengeluaran with (readuncommitted)"), 'bank.formatpengeluaran', '=', 'formatpengeluaran.id');



        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('bank.statusaktif', '=', $statusaktif->id);
        }
        if($tipe == 'KAS'){
            $query->where('bank.tipe', '=', 'KAS');
        }
        if($tipe == 'BANK'){
            $query->where('bank.tipe', '=', 'BANK');
        }
        if($bankId != 0){
            $query->where('bank.id', '=', $bankId);
        }

        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->default(0);
        });

        $statusaktif = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('default', '=', 'YA')
            ->first();
        DB::table($tempdefault)->insert(["statusaktif" => $statusaktif->id]);

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif'
            );

        $data = $query->first();
        // dd($data);
        return $data;
    }
    public function findAll($id)
    {
        $query =  DB::table('bank')->from(
            DB::raw("bank with (readuncommitted)")
        )
            ->select(
                'bank.id',
                'bank.kodebank',
                'bank.namabank',
                'bank.coa',
                'bank.tipe',
                'bank.statusaktif',
                'bank.formatpenerimaan',
                'bank.formatpengeluaran',

            )
            ->where('bank.id', $id);

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
            $this->table.kodebank,
            $this->table.namabank,
            $this->table.coa,
            $this->table.tipe,
            parameter.text as statusaktif,
            formatpenerimaan.text as formatpenerimaan,
            formatpengeluaran.text as formatpengeluaran,
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
                )
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'bank.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("parameter as formatpenerimaan with (readuncommitted)"), 'bank.formatpenerimaan', '=', 'formatpenerimaan.id')
            ->leftJoin(DB::raw("parameter as formatpengeluaran with (readuncommitted)"), 'bank.formatpengeluaran', '=', 'formatpengeluaran.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('kodebank', 1000)->default('');
            $table->string('namabank', 1000)->default('');
            $table->string('coa', 1000)->default('');
            $table->string('tipe', 1000)->default('');
            $table->string('statusaktif', 1000)->default('');
            $table->string('formatpenerimaan', 1000)->default('');
            $table->string('formatpengeluaran', 1000)->default('');
            $table->string('modifiedby', 50)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'kodebank', 'namabank', 'coa', 'tipe', 'statusaktif', 'formatpenerimaan', 'formatpengeluaran', 'modifiedby', 'created_at', 'updated_at'], $models);


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
                        } else if ($filters['field'] == 'formatpenerimaan') {
                            $query = $query->where('formatpenerimaan.text', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'formatpengeluaran') {
                            $query = $query->where('formatpengeluaran.text', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', '=', $filters['data']);
                        } elseif ($filters['field'] == 'id') {
                            $query = $query->orWhereRaw("(bank.id like '%$filters[data]%'");
                        } elseif ($filters['field'] == 'updated_at') {
                            $query = $query->orWhereRaw("format(bank.updated_at,'dd-MM-yyyy HH:mm:ss') like '%$filters[data]%')");
                        } else if ($filters['field'] == 'formatpenerimaan') {
                            $query = $query->orWhere('formatpenerimaan.text', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'formatpengeluaran') {
                            $query = $query->orWhere('formatpengeluaran.text', 'LIKE', "%$filters[data]%");
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
