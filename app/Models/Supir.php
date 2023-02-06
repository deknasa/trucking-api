<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class Supir extends MyModel
{
    use HasFactory;

    protected $table = 'supir';

    public function cekvalidasihapus($id)
    {
        // cek sudah ada absensi
      

        $absen = DB::table('absensisupirdetail')
            ->from(
                DB::raw("absensisupirdetail as a with (readuncommitted)")
            )
            ->select(
                'a.supir_id'
            )
            ->where('a.supir_id', '=', $id)
            ->first();
        if (isset($absen)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Absensi Supir',
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

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function zona()
    {
        return $this->belongsTo(Zona::class, 'zona_id');
    }

    public function get()
    {
        $this->setRequestParameters();

        $aktif = request()->aktif ?? '';

        $query = Supir::from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'supir.id',
                'supir.namasupir',
                'supir.tgllahir',
                'supir.alamat',
                'supir.kota',
                'supir.telp',
                'parameter.memo as statusaktif',
                'supir.nominaldepositsa',
                'supir.depositke',
                'supir.nominalpinjamansaldoawal',
                'supirlama.namasupir as supirold_id',
                'supir.nosim',
                'supir.tglterbitsim',
                'supir.tglexpsim',
                'supir.keterangan',
                'supir.noktp',
                'supir.nokk',
                'statusadaupdategambar.memo as statusadaupdategambar',
                'statusluarkota.memo as statusluarkota',
                'statuszonatertentu.memo as statuszonatertentu',
                'zona.keterangan as zona_id',
                'supir.photosupir',
                'supir.photoktp',
                'supir.photosim',
                'supir.photokk',
                'supir.photoskck',
                'supir.photodomisili',
                'supir.keteranganresign',
                'statusblacklist.memo as statusblacklist',
                DB::raw('(case when (year(supir.tglberhentisupir) <= 2000) then null else supir.tglberhentisupir end ) as tglberhentisupir'),

                'supir.modifiedby',
                'supir.created_at',
                'supir.updated_at'
            )
            ->leftJoin(DB::raw("zona with (readuncommitted)"), 'supir.zona_id', 'zona.id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'supir.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statusadaupdategambar with (readuncommitted)"), 'supir.statusadaupdategambar', '=', 'statusadaupdategambar.id')
            ->leftJoin(DB::raw("parameter as statusluarkota with (readuncommitted)"), 'supir.statusluarkota', '=', 'statusluarkota.id')
            ->leftJoin(DB::raw("parameter as statuszonatertentu with (readuncommitted)"), 'supir.statuszonatertentu', '=', 'statuszonatertentu.id')
            ->leftJoin(DB::raw("parameter as statusblacklist with (readuncommitted)"), 'supir.statusblacklist', '=', 'statusblacklist.id')
            ->leftJoin(DB::raw("supir as supirlama with (readuncommitted)"), 'supir.supirold_id', '=', 'supirlama.id');



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

            $query->where('supir.statusaktif', '=', $statusaktif->id);
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
            $table->unsignedBigInteger('statusadaupdategambar')->default(0);
            $table->unsignedBigInteger('statusluarkota')->default(0);
            $table->unsignedBigInteger('statuszonatertentu')->default(0);
            $table->unsignedBigInteger('statusblacklist')->default(0);
        });

        // AKTIF
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

        // STATUS ADA UPDATE GAMBAR
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS ADA UPDATE GAMBAR')
            ->where('subgrp', '=', 'STATUS ADA UPDATE GAMBAR')
            ->where('DEFAULT', '=', 'YA')
            ->first();

        $iddefaultstatusUpdGambar = $status->id ?? 0;

        // STATUS LUAR KOTA
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS LUAR KOTA')
            ->where('subgrp', '=', 'STATUS LUAR KOTA')
            ->where('DEFAULT', '=', 'YA')
            ->first();


        $iddefaultstatusLuarKota = $status->id ?? 0;

        // ZONA TERTENTU
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'ZONA TERTENTU')
            ->where('subgrp', '=', 'ZONA TERTENTU')
            ->where('DEFAULT', '=', 'YA')
            ->first();


        $iddefaultstatusZonaTertentu = $status->id ?? 0;

        // BLACKLIST SUPIR
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'BLACKLIST SUPIR')
            ->where('subgrp', '=', 'BLACKLIST SUPIR')
            ->where('DEFAULT', '=', 'YA')
            ->first();



        $iddefaultstatusBlacklist = $status->id ?? 0;



        DB::table($tempdefault)->insert(
            [
                "statusaktif" => $iddefaultstatusaktif,
                "statusadaupdategambar" => $iddefaultstatusUpdGambar,
                "statusluarkota" => $iddefaultstatusLuarKota,
                "statuszonatertentu" => $iddefaultstatusZonaTertentu,
                "statusblacklist" => $iddefaultstatusBlacklist,
            ]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusadaupdategambar',
                'statusluarkota',
                'statuszonatertentu',
                'statusblacklist'
            );

        $data = $query->first();

        return $data;
    }

    public function findAll($id)
    {
        $data = Supir::from(DB::raw("supir with (readuncommitted)"))
            ->select(
                'supir.id',
                'supir.namasupir',
                'supir.alamat',
                'supir.kota',
                'supir.telp',
                'supir.statusaktif',
                'supir.nominaldepositsa',
                'supir.depositke',
                'supir.tglmasuk',
                'supir.nominalpinjamansaldoawal',
                'supir.supirold_id',
                'supirlama.namasupir as supirold',
                'supir.tglexpsim',
                'supir.nosim',
                'supir.keterangan',
                'supir.noktp',
                'supir.nokk',
                'supir.statusadaupdategambar',
                'supir.statusluarkota',
                'supir.statuszonatertentu',
                'supir.zona_id',
                'zona.keterangan as zona',
                'supir.angsuranpinjaman',
                'supir.plafondeposito',
                'supir.photosupir',
                'supir.photoktp',
                'supir.photosim',
                'supir.photokk',
                'supir.photoskck',
                'supir.photodomisili',
                'supir.keteranganresign',
                'supir.statusblacklist',
                'supir.tglberhentisupir',
                'supir.tgllahir',
                'supir.tglterbitsim',

                'supir.modifiedby',
                'supir.created_at',
                'supir.updated_at'
            )

            ->leftJoin(DB::raw("zona with (readuncommitted)"), 'supir.zona_id', 'zona.id')
            ->leftJoin(DB::raw("supir as supirlama with (readuncommitted)"), 'supir.supirold_id', '=', 'supirlama.id')

            ->where('supir.id', $id)->first();

        return $data;
    }

    public function selectColumns($query)
    { //sesuaikan dengan createtemp

        return $query->select(
            DB::raw(
                "$this->table.id,
                $this->table.namasupir,
                $this->table.alamat,
                $this->table.kota,
                $this->table.telp,
                $this->table.statusaktif,
                supir.nominaldepositsa,
                $this->table.depositke,
                $this->table.tglmasuk,
                $this->table.nominalpinjamansaldoawal,
                supir.namasupir as supirold_id,
                $this->table.tglexpsim,
                $this->table.nosim,
                $this->table.keterangan,
                $this->table.noktp,
                $this->table.nokk,
                $this->table.statusadaupdategambar,
                $this->table.statusluarkota,
                $this->table.statuszonatertentu,
                $this->table.zona_id,
                $this->table.angsuranpinjaman,
                $this->table.plafondeposito,
                $this->table.photosupir,
                $this->table.photoktp, 
                $this->table.photosim, 
                $this->table.photokk, 
                $this->table.photoskck, 
                $this->table.photodomisili, 
                $this->table.keteranganresign,
                $this->table.statusblacklist,
                $this->table.tglberhentisupir,
                $this->table.tgllahir,
                $this->table.tglterbitsim,

            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )

        )
            ->leftJoin('zona', 'supir.zona_id', 'zona.id')
            ->leftJoin('parameter as statusadaupdategambar', 'supir.statusadaupdategambar', '=', 'statusadaupdategambar.id')
            ->leftJoin('parameter as statusluarkota', 'supir.statusluarkota', '=', 'statusluarkota.id')
            ->leftJoin('parameter as statuszonatertentu', 'supir.statuszonatertentu', '=', 'statuszonatertentu.id')
            ->leftJoin('parameter as statusblacklist', 'supir.statusblacklist', '=', 'statusblacklist.id')
            ->leftJoin('supir as supirlama', 'supir.supirold_id', '=', 'supirlama.id');
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('namasupir', 100)->default('');
            $table->string('alamat', 100)->default('');
            $table->string('kota', 100)->default('');
            $table->string('telp', 30)->default('');
            $table->integer('statusaktif')->length(11)->default('0');
            $table->double('nominaldepositsa', 15, 2)->default(0);
            $table->double('depositke', 15, 2)->default(0);
            $table->date('tglmasuk')->default('1900/1/1');
            $table->double('nominalpinjamansaldoawal', 15, 2)->default(0);
            $table->string('supirold_id')->default(0);
            $table->date('tglexpsim')->default('1900/1/1');
            $table->string('nosim', 30)->default('');
            $table->longText('keterangan')->default('');
            $table->string('noktp', 30)->default('');
            $table->string('nokk', 30)->default('');
            $table->string('statusadaupdategambar', 300)->default('')->nullable();
            $table->string('statusluarkota', 300)->default('')->nullable();
            $table->string('statuszonatertentu', 300)->default('')->nullable();
            $table->unsignedBigInteger('zona_id')->default(0);
            $table->double('angsuranpinjaman', 15, 2)->default(0);
            $table->double('plafondeposito', 15, 2)->default(0);
            $table->string('photosupir', 4000)->default('');
            $table->string('photoktp', 4000)->default('');
            $table->string('photosim', 4000)->default('');
            $table->string('photokk', 4000)->default('');
            $table->string('photoskck', 4000)->default('');
            $table->string('photodomisili', 4000)->default('');
            $table->longText('keteranganresign')->default('');
            $table->string('statusblacklist')->default(0);
            $table->date('tglberhentisupir')->default('1900/1/1');
            $table->date('tgllahir')->default('1900/1/1');
            $table->date('tglterbitsim')->default('1900/1/1');

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
        DB::table($temp)->insertUsing([
            'id',
            'namasupir',
            'alamat',
            'kota',
            'telp',
            'statusaktif',
            'nominaldepositsa',
            'depositke',
            'tglmasuk',
            'nominalpinjamansaldoawal',
            'supirold_id',
            'tglexpsim',
            'nosim',
            'keterangan',
            'noktp',
            'nokk',
            'statusadaupdategambar',
            'statusluarkota',
            'statuszonatertentu',
            'zona_id',
            'angsuranpinjaman',
            'plafondeposito',
            'photosupir',
            'photoktp',
            'photosim',
            'photokk',
            'photoskck',
            'photodomisili',
            'keteranganresign',
            'statusblacklist',
            'tglberhentisupir',
            'tgllahir',
            'tglterbitsim',
            'modifiedby',
            'created_at',
            'updated_at'
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
                        } else if ($filters['field'] == 'statusadaupdategambar') {
                            $query = $query->where('statusadaupdategambar.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusluarkota') {
                            $query = $query->where('statusluarkota.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statuszonatertentu') {
                            $query = $query->where('statuszonatertentu.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusblacklist') {
                            $query = $query->where('statusblacklist.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'zona_id') {
                            $query = $query->where('zona.zona', 'LIKE', "%$filters[data]%");
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
                            $query = $query->orWhereRaw("(supir.id like '%$filters[data]%'");
                        } elseif ($filters['field'] == 'updated_at') {
                            $query = $query->orWhereRaw("format(supir.updated_at,'dd-MM-yyyy HH:mm:ss') like '%$filters[data]%')");
                        } else if ($filters['field'] == 'statusadaupdategambar') {
                            $query = $query->orWhere('statusadaupdategambar.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusluarkota') {
                            $query = $query->orWhere('statusluarkota.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statuszonatertentu') {
                            $query = $query->orWhere('statuszonatertentu.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusblacklist') {
                            $query = $query->orWhere('statusblacklist.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'zona_id') {
                            $query = $query->orWhere('zona.zona', 'LIKE', "%$filters[data]%");
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
