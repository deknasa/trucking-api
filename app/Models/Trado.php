<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class Trado extends MyModel
{
    use HasFactory;

    protected $table = 'trado';

    public function absensiSupir()
    {
        return $this->belongsToMany(AbsensiSupirDetail::class);
    }

    public function cekvalidasihapus($id)
    {
        // cek sudah ada absensi

        $absen = DB::table('absensisupirdetail')
            ->from(
                DB::raw("absensisupirdetail as a with (readuncommitted)")
            )
            ->select(
                'a.trado_id'
            )
            ->where('a.trado_id', '=', $id)
            ->first();
        if (isset($absen)) {
             $data = [
                'kondisi' => true,
                'keterangan' => 'Absensi Supir',
            ];

            goto selesai;
        }

        $penerimaanStok = DB::table('penerimaanstokheader')
            ->from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
            ->select(
                'a.trado_id'
            )
            ->where('a.trado_id', '=', $id)
            ->first();
        if (isset($penerimaanStok)) {
             $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan Stok',
            ];

            goto selesai;
        }
        $pengeluaranStok = DB::table('pengeluaranstokheader')
            ->from(
                DB::raw("pengeluaranstokheader as a with (readuncommitted)")
            )
            ->select(
                'a.trado_id'
            )
            ->where('a.trado_id', '=', $id)
            ->first();
        if (isset($pengeluaranStok)) {
             $data = [
                'kondisi' => true,
                'keterangan' => 'Pengeluaran Stok',
            ];

            goto selesai;
        }
        
        $serviceOut = DB::table('serviceoutheader')
            ->from(
                DB::raw("serviceoutheader as a with (readuncommitted)")
            )
            ->select(
                'a.trado_id'
            )
            ->where('a.trado_id', '=', $id)
            ->first();
        if (isset($serviceOut)) {
             $data = [
                'kondisi' => true,
                'keterangan' => 'Service Out',
            ];

            goto selesai;
        }
        
        $suratPengantar = DB::table('suratpengantar')
            ->from(
                DB::raw("suratpengantar as a with (readuncommitted)")
            )
            ->select(
                'a.trado_id'
            )
            ->where('a.trado_id', '=', $id)
            ->first();
        if (isset($suratPengantar)) {
             $data = [
                'kondisi' => true,
                'keterangan' => 'Surat Pengantar',
            ];

            goto selesai;
        }
        $serviceIn = DB::table('serviceinheader')
            ->from(
                DB::raw("serviceinheader as a with (readuncommitted)")
            )
            ->select(
                'a.trado_id'
            )
            ->where('a.trado_id', '=', $id)
            ->first();
        if (isset($serviceIn)) {
             $data = [
                'kondisi' => true,
                'keterangan' => 'Service In',
            ];

            goto selesai;
        }
        $ritasi = DB::table('ritasi')
            ->from(
                DB::raw("ritasi as a with (readuncommitted)")
            )
            ->select(
                'a.trado_id'
            )
            ->where('a.trado_id', '=', $id)
            ->first();
        if (isset($ritasi)) {
             $data = [
                'kondisi' => true,
                'keterangan' => 'Ritasi',
            ];

            goto selesai;
        }


        $data = false;
        selesai:

        return $data;
    }
    public function get()
    {
        $this->setRequestParameters();

        $aktif = request()->aktif ?? '';

        $query = Trado::from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'trado.id',
                'trado.keterangan',
                'trado.kmawal',
                'trado.kmakhirgantioli',
                'trado.tglasuransimati',
                'trado.merek',
                'trado.norangka',
                'trado.nomesin',
                'trado.nama',
                'trado.nostnk',
                'trado.alamatstnk',
                'trado.modifiedby',
                'trado.created_at',
                'trado.tglserviceopname',
                'trado.keteranganprogressstandarisasi',
                'trado.tglpajakstnk',
                'trado.tglgantiakiterakhir',
                'trado.tipe',
                'trado.jenis',
                'trado.isisilinder',
                'trado.warna',
                'trado.jenisbahanbakar',
                'trado.jumlahsumbu',
                'trado.jumlahroda',
                'trado.model',
                'trado.nobpkb',
                'trado.jumlahbanserap',
                'trado.photostnk',
                'trado.photobpkb',
                'trado.phototrado',
                'parameter_statusaktif.memo as statusaktif',
                'parameter_statusstandarisasi.memo as statusstandarisasi',
                'parameter_statusjenisplat.memo as statusjenisplat',
                'parameter_statusmutasi.memo as statusmutasi',
                'parameter_statusvalidasikendaraan.memo as statusvalidasikendaraan',
                'parameter_statusmobilstoring.memo as statusmobilstoring',
                'parameter_statusappeditban.memo as statusappeditban',
                'parameter_statuslewatvalidasi.memo as statuslewatvalidasi',
                'mandor.namamandor as mandor_id',
                'supir.namasupir as supir_id',
                'trado.updated_at',
            )
            ->leftJoin(DB::raw("parameter as parameter_statusaktif with (readuncommitted)"), 'trado.statusaktif', 'parameter_statusaktif.id')
            ->leftJoin(DB::raw("parameter as parameter_statusjenisplat with (readuncommitted)"), 'trado.statusjenisplat', 'parameter_statusjenisplat.id')
            ->leftJoin(DB::raw("parameter as parameter_statusstandarisasi with (readuncommitted)"), 'trado.statusstandarisasi', 'parameter_statusstandarisasi.id')
            ->leftJoin(DB::raw("parameter as parameter_statusmutasi with (readuncommitted)"), 'trado.statusmutasi', 'parameter_statusmutasi.id')
            ->leftJoin(DB::raw("parameter as parameter_statusvalidasikendaraan with (readuncommitted)"), 'trado.statusvalidasikendaraan', 'parameter_statusvalidasikendaraan.id')
            ->leftJoin(DB::raw("parameter as parameter_statusmobilstoring with (readuncommitted)"), 'trado.statusmobilstoring', 'parameter_statusmobilstoring.id')
            ->leftJoin(DB::raw("parameter as parameter_statusappeditban with (readuncommitted)"), 'trado.statusappeditban', 'parameter_statusappeditban.id')
            ->leftJoin(DB::raw("parameter as parameter_statuslewatvalidasi with (readuncommitted)"), 'trado.statuslewatvalidasi', 'parameter_statuslewatvalidasi.id')
            ->leftJoin(DB::raw("mandor with (readuncommitted)"), 'trado.mandor_id', 'mandor.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'trado.supir_id', 'supir.id');


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

            $query->where('trado.statusaktif', '=', $statusaktif->id);
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
            $table->unsignedBigInteger('statusstandarisasi')->default(0);
            $table->unsignedBigInteger('statusjenisplat')->default(0);
            $table->unsignedBigInteger('statusmutasi')->default(0);
            $table->unsignedBigInteger('statusvalidasikendaraan')->default(0);
            $table->unsignedBigInteger('statusmobilstoring')->default(0);
            $table->unsignedBigInteger('statusappeditban')->default(0);
            $table->unsignedBigInteger('statuslewatvalidasi')->default(0);
        });

        // AKTIF
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('DEFAULT', '=', 'YA')
            ->first();

        $datadetail = json_decode($status->get(), true);

        $iddefaultstatusaktif = $status->id ?? 0;

        // STANDARISASI
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS STANDARISASI')
            ->where('subgrp', '=', 'STATUS STANDARISASI')
            ->where("default", '=', 'YA')
            ->first();

        $iddefaultstatuStandarisasi = $status->id ?? 0;

        // 	JENIS PLAT
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'JENIS PLAT')
            ->where('subgrp', '=', 'JENIS PLAT')
            ->where("default", '=', 'YA')
            ->first();

        $iddefaultstatusJenisPlat = $status->id ?? 0;

        //STATUS MUTASI
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS MUTASI')
            ->where('subgrp', '=', 'STATUS MUTASI')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusMutasi = $status->id ?? 0;

        //STATUS VALIDASI KENDARAAN
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS VALIDASI KENDARAAN')
            ->where('subgrp', '=', 'STATUS VALIDASI KENDARAAN')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusValKen = $status->id ?? 0;

        //STATUS MOBIL STORING
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS MOBIL STORING')
            ->where('subgrp', '=', 'STATUS MOBIL STORING')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusMobStoring = $status->id ?? 0;

        //STATUS APPROVAL EDIT BAN
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS APPROVAL EDIT BAN')
            ->where('subgrp', '=', 'STATUS APPROVAL EDIT BAN')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusAppedit = $status->id ?? 0;

        //STATUS LEWAT VALIDASI
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS LEWAT VALIDASI')
            ->where('subgrp', '=', 'STATUS LEWAT VALIDASI')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusLewatVal = $status->id ?? 0;

        DB::table($tempdefault)->insert(
            [
                "statusaktif" => $iddefaultstatusaktif,
                "statusstandarisasi" => $iddefaultstatuStandarisasi,
                "statusjenisplat" => $iddefaultstatusJenisPlat,
                "statusmutasi" => $iddefaultstatusMutasi,
                "statusvalidasikendaraan" => $iddefaultstatusValKen,
                "statusmobilstoring" => $iddefaultstatusMobStoring,
                "statusappeditban" => $iddefaultstatusAppedit,
                "statuslewatvalidasi" => $iddefaultstatusLewatVal
            ]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusstandarisasi',
                'statusjenisplat',
                'statusmutasi',
                'statusvalidasikendaraan',
                'statusmobilstoring',
                'statusappeditban',
                'statuslewatvalidasi'
            );

        $data = $query->first();

        return $data;
    }

    public function findAll($id)
    {
        $data = DB::table('trado')->select(
            'trado.*',
            'mandor.namamandor as mandor',
            'supir.namasupir as supir'
        )
            ->leftJoin(DB::raw("mandor with (readuncommitted)"), 'trado.mandor_id', 'mandor.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'trado.supir_id', 'supir.id')
            ->where('trado.id', $id)
            ->first();

        return $data;
    }

    public function selectColumns($query)
    { //sesuaikan dengan createtemp

        return $query->select(
            DB::raw(
                "$this->table.id,           
                $this->table.keterangan,            
                'parameter_statusaktif.text as statusaktif',
                $this->table.kmawal,
                $this->table.kmakhirgantioli,
                $this->table.tglakhirgantioli,
                $this->table.tglstnkmati,
                $this->table.tglasuransimati,
                $this->table.tahun,
                $this->table.akhirproduksi,
                $this->table.merek,
                $this->table.norangka,
                $this->table.nomesin,
                $this->table.nama,
                $this->table.nostnk,
                $this->table.alamatstnk,
                $this->table.tglstandarisasi,
                $this->table.tglserviceopname,
                'parameter_statusstandarisasi.text as statusstandarisasi',
                $this->table.keteranganprogressstandarisasi,
                $this->table.statusjenisplat,
                $this->table.tglspeksimati,
                $this->table.tglpajakstnk,
                $this->table.tglgantiakiterakhir,
                'parameter_statusmutasi.text as statusmutasi',
                'parameter_statusvalidasikendaraan.text as statusvalidasikendaraan',
                $this->table.tipe,
                $this->table.jenis,
                $this->table.isisilinder,
                $this->table.warna,
                $this->table.jenisbahanbakar,
                $this->table.jumlahsumbu,
                $this->table.jumlahroda,
                $this->table.model,
                $this->table.nobpkb,
                $this->table.statusmobilstoring,
                'mandor.namamandor as mandor_id',
                $this->table.jumlahbanserap,
                $this->table.statusappeditban,
                $this->table.statuslewatvalidasi,

                $this->table.photostnk,
                $this->table.photobpkb,
                $this->table.phototrado,
                
               $this->table.modifiedby,
               $this->table.created_at,
               $this->table.updated_at"
            )

        )

            ->leftJoin('parameter as parameter_statusaktif', 'trado.statusaktif', 'parameter_statusaktif.id')
            ->leftJoin('parameter as parameter_statusstandarisasi', 'trado.statusstandarisasi', 'parameter_statusstandarisasi.id')
            ->leftJoin('parameter as parameter_statusmutasi', 'trado.statusmutasi', 'parameter_statusmutasi.id')
            ->leftJoin('parameter as parameter_statusvalidasikendaraan', 'trado.statusvalidasikendaraan', 'parameter_statusvalidasikendaraan.id')
            ->leftJoin('mandor', 'trado.mandor_id', 'mandor.id');
    }


    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->longText('keterangan')->default('');
            $table->string('statusaktif')->default('');
            $table->double('kmawal', 15, 2)->default(0);
            $table->double('kmakhirgantioli', 15, 2)->default(0);
            $table->date('tglakhirgantioli')->default('1900/1/1');
            $table->date('tglstnkmati')->default('1900/1/1');
            $table->date('tglasuransimati')->default('1900/1/1');
            $table->string('tahun', 40)->default('');
            $table->string('akhirproduksi', 40)->default('');
            $table->string('merek', 40)->default('');
            $table->string('norangka', 40)->default('');
            $table->string('nomesin', 40)->default('');
            $table->string('nama', 40)->default('');
            $table->string('nostnk', 30)->default('');
            $table->string('alamatstnk', 30)->default('');
            $table->date('tglstandarisasi')->default('1900/1/1');
            $table->date('tglserviceopname')->default('1900/1/1');
            $table->string('statusstandarisasi')->default('');
            $table->string('keteranganprogressstandarisasi', 100)->default('');
            $table->integer('statusjenisplat')->length(11)->default(0);
            $table->date('tglspeksimati')->default('1900/1/1');
            $table->date('tglpajakstnk')->default('1900/1/1');
            $table->date('tglgantiakiterakhir')->default('1900/1/1');
            $table->string('statusmutasi')->default('');
            $table->string('statusvalidasikendaraan')->default('');
            $table->string('tipe', 30)->default('');
            $table->string('jenis', 30)->default('');
            $table->integer('isisilinder')->length(11)->default(0);
            $table->string('warna', 30)->default('');
            $table->string('jenisbahanbakar', 30)->default('');
            $table->integer('jumlahsumbu')->length(11)->default(0);
            $table->integer('jumlahroda')->length(11)->default(0);
            $table->string('model', 50)->default('');
            $table->string('nobpkb', 50)->default('');
            $table->integer('statusmobilstoring')->length(11)->default(0);
            $table->string('mandor_id')->default('');
            $table->integer('jumlahbanserap')->length(11)->default(0);
            $table->integer('statusappeditban')->length(11)->default(0);
            $table->integer('statuslewatvalidasi')->length(11)->default(0);

            $table->string('photostnk', 1500)->default('');
            $table->string('photobpkb', 1500)->default('');
            $table->string('phototrado', 1500)->default('');

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
        DB::table($temp)->insertUsing(['id', 'keterangan', 'statusaktif', 'kmawal', 'kmakhirgantioli', 'tglakhirgantioli',  'tglstnkmati', 'tglasuransimati', 'tahun', 'akhirproduksi', 'merek', 'norangka', 'nomesin', 'nama', 'nostnk', 'alamatstnk', 'tglstandarisasi', 'tglserviceopname', 'statusstandarisasi', 'keteranganprogressstandarisasi', 'statusjenisplat', 'tglspeksimati', 'tglpajakstnk', 'tglgantiakiterakhir', 'statusmutasi', 'statusvalidasikendaraan', 'tipe', 'jenis', 'isisilinder', 'warna', 'jenisbahanbakar', 'jumlahsumbu', 'jumlahroda', 'model', 'nobpkb', 'statusmobilstoring', 'mandor_id', 'jumlahbanserap', 'statusappeditban', 'statuslewatvalidasi', 'photostnk', 'photobpkb', 'phototrado', 'modifiedby', 'created_at', 'updated_at'], $models);


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
                            $query = $query->where('parameter_statusaktif.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusstandarisasi') {
                            $query = $query->where('parameter_statusstandarisasi.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusjenisplat') {
                            $query = $query->where('parameter_statusjenisplat.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusmutasi') {
                            $query = $query->where('parameter_statusmutasi.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusmobilstoring') {
                            $query = $query->where('parameter_statusmobilstoring.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusappeditban') {
                            $query = $query->where('parameter_statusappeditban.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statuslewatvalidasi') {
                            $query = $query->where('parameter_statuslewatvalidasi.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusvalidasikendaraan') {
                            $query = $query->where('parameter_statusvalidasikendaraan.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'mandor_id') {
                            $query = $query->where('mandor.namamandor', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter_statusaktif.text', '=', $filters['data']);
                        } elseif ($filters['field'] == 'id') {
                            $query = $query->orWhereRaw("(trado.id like '%$filters[data]%'");
                        } elseif ($filters['field'] == 'updated_at') {
                            $query = $query->orWhereRaw("format(trado.updated_at,'dd-MM-yyyy HH:mm:ss') like '%$filters[data]%')");
                        } else if ($filters['field'] == 'statusstandarisasi') {
                            $query = $query->orWhere('parameter_statusstandarisasi.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusjenisplat') {
                            $query = $query->orWhere('parameter_statusjenisplat.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusmutasi') {
                            $query = $query->orWhere('parameter_statusmutasi.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusmobilstoring') {
                            $query = $query->orWhere('parameter_statusmobilstoring.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusappeditban') {
                            $query = $query->orWhere('parameter_statusappeditban.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statuslewatvalidasi') {
                            $query = $query->orWhere('parameter_statuslewatvalidasi.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusvalidasikendaraan') {
                            $query = $query->orWhere('parameter_statusvalidasikendaraan.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'mandor_id') {
                            $query = $query->orWhere('mandor.namamandor', 'LIKE', "%$filters[data]%");
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
