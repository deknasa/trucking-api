<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Helpers\App;
use Illuminate\Support\Facades\Storage;


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

        $gajiSupir = DB::table('gajisupirheader')
            ->from(
                DB::raw("gajisupirheader as a with (readuncommitted)")
            )
            ->select(
                'a.supir_id'
            )
            ->where('a.supir_id', '=', $id)
            ->first();
        if (isset($gajiSupir)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Gaji Supir',
            ];

            goto selesai;
        }

        $trado = DB::table('trado')
            ->from(
                DB::raw("trado as a with (readuncommitted)")
            )
            ->select(
                'a.supir_id'
            )
            ->where('a.supir_id', '=', $id)
            ->first();
        if (isset($trado)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Trado',
            ];

            goto selesai;
        }

        $suratPengantar = DB::table('suratpengantar')
            ->from(
                DB::raw("suratpengantar as a with (readuncommitted)")
            )
            ->select(
                'a.supir_id'
            )
            ->where('a.supir_id', '=', $id)
            ->first();
        if (isset($suratPengantar)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Surat Pengantar',
            ];

            goto selesai;
        }

        $penerimaanTrucking = DB::table('penerimaantruckingdetail')
            ->from(
                DB::raw("penerimaantruckingdetail as a with (readuncommitted)")
            )
            ->select(
                'a.supir_id'
            )
            ->where('a.supir_id', '=', $id)
            ->first();
        if (isset($penerimaanTrucking)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan Trucking',
            ];

            goto selesai;
        }


        $pengeluaranTrucking = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail as a with (readuncommitted)")
            )
            ->select(
                'a.supir_id'
            )
            ->where('a.supir_id', '=', $id)
            ->first();
        if (isset($pengeluaranTrucking)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pengeluaran Trucking',
            ];

            goto selesai;
        }


        $ritasi = DB::table('ritasi')
            ->from(
                DB::raw("ritasi as a with (readuncommitted)")
            )
            ->select(
                'a.supir_id'
            )
            ->where('a.supir_id', '=', $id)
            ->first();
        if (isset($ritasi)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Ritasi',
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
        $absen = request()->absen ?? '';
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $aktif = request()->aktif ?? '';

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'supir.id',
                'supir.namasupir',
                'supir.namaalias',
                DB::raw("(case when year(isnull(supir.tgllahir,'1900/1/1'))=1900 then null else supir.tgllahir end) as tgllahir"),
                'supir.alamat',
                'supir.kota',
                'supir.telp',
                'supir.pemutihansupir_nobukti',
                'parameter.memo as statusaktif',
                'supir.nominaldepositsa',
                'supir.depositke',
                'supir.nominalpinjamansaldoawal',
                'supirlama.namasupir as supirold_id',
                'supir.nosim',
                DB::raw('(case when (year(supir.tglterbitsim) <= 2000) then null else supir.tglterbitsim end ) as tglterbitsim'),
                DB::raw('(case when (year(supir.tglexpsim) <= 2000) then null else supir.tglexpsim end ) as tglexpsim'),
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
                'supir.keteranganberhentisupir',
                'statusblacklist.memo as statusblacklist',
                DB::raw('(case when (year(supir.tglberhentisupir) <= 2000) then null else supir.tglberhentisupir end ) as tglberhentisupir'),

                'supir.modifiedby',
                'supir.created_at',
                'supir.updated_at',
                DB::raw("'Laporan Supir' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tanggal Cetak : '+format(getdate(),'dd-MM-yyyy HH:mm:ss')+' User :".auth('api')->user()->name."' as tglcetak") 
            )
            ->leftJoin(DB::raw("zona with (readuncommitted)"), 'supir.zona_id', 'zona.id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'supir.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statusadaupdategambar with (readuncommitted)"), 'supir.statusadaupdategambar', '=', 'statusadaupdategambar.id')
            ->leftJoin(DB::raw("parameter as statusluarkota with (readuncommitted)"), 'supir.statusluarkota', '=', 'statusluarkota.id')
            ->leftJoin(DB::raw("parameter as statuszonatertentu with (readuncommitted)"), 'supir.statuszonatertentu', '=', 'statuszonatertentu.id')
            ->leftJoin(DB::raw("parameter as statusblacklist with (readuncommitted)"), 'supir.statusblacklist', '=', 'statusblacklist.id')
            ->leftJoin(DB::raw("supir as supirlama with (readuncommitted)"), 'supir.supirold_id', '=', 'supirlama.id');




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
        if ($absen == true) {
            $tglbukti = date('Y-m-d', strtotime('now'));
            $absensiSupirHeader = AbsensiSupirHeader::where('tglbukti', $tglbukti)->first();
            $query->whereRaw("supir.id in (select supir_id from absensisupirdetail where absensi_id=$absensiSupirHeader->id)");
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
            $table->unsignedBigInteger('statusadaupdategambar')->nullable();
            $table->unsignedBigInteger('statusluarkota')->nullable();
            $table->unsignedBigInteger('statuszonatertentu')->nullable();
            $table->unsignedBigInteger('statusblacklist')->nullable();
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

    public function cekPemutihan($ktp)
    {
        $pemutihan = PemutihanSupir::from(DB::raw("pemutihansupirheader with (readuncommitted)"))
            ->select(DB::raw("supir.noktp"))
            ->join(DB::raw("supir with (readuncommitted)"), 'pemutihansupirheader.supir_id', 'supir.id')
            ->where('supir.noktp', $ktp)
            ->first();

        if ($pemutihan != null) {
            $status = true;
        } else {
            $status = false;
        }

        return $status;
    }
    public function findAll($id)
    {
        $data = Supir::from(DB::raw("supir with (readuncommitted)"))
            ->select(
                'supir.id',
                'supir.namasupir',
                'supir.namaalias',
                'supir.alamat',
                'supir.kota',
                'supir.telp',
                'supir.statusaktif',
                'supir.pemutihansupir_nobukti',
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
                'supir.photovaksin',
                'supir.pdfsuratperjanjian',
                'supir.photodomisili',
                'supir.keteranganresign',
                'supir.keteranganberhentisupir',
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
                $this->table.namaalias,
                $this->table.tgllahir,
                $this->table.alamat,
                $this->table.kota,
                $this->table.telp,
                parameter.memo as statusaktif,
                $this->table.nominaldepositsa,
                $this->table.depositke,
                $this->table.nominalpinjamansaldoawal,
                supir.namasupir as supirold_id,
                $this->table.nosim,
                $this->table.tglexpsim,
                $this->table.tglterbitsim,
                $this->table.keterangan,
                $this->table.noktp,
                $this->table.nokk,
                statusluarkota.memo as statusluarkota,
                $this->table.angsuranpinjaman,
                $this->table.plafondeposito,
                $this->table.photosupir,
                $this->table.photoktp, 
                $this->table.photosim, 
                $this->table.photokk, 
                $this->table.photoskck, 
                $this->table.photodomisili, 
                $this->table.tglberhentisupir,
                statusblacklist.memo as statusblacklist,
                $this->table.pemutihansupir_nobukti,

            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )

        )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'supir.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statusluarkota with (readuncommitted)"), 'supir.statusluarkota', '=', 'statusluarkota.id')
            ->leftJoin(DB::raw("parameter as statusblacklist with (readuncommitted)"), 'supir.statusblacklist', '=', 'statusblacklist.id')
            ->leftJoin('supir as supirlama', 'supir.supirold_id', '=', 'supirlama.id');
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('namasupir', 100)->nullable();
            $table->string('namaalias', 100)->nullable();
            $table->date('tgllahir')->nullable();
            $table->string('alamat', 100)->nullable();
            $table->string('kota', 100)->nullable();
            $table->string('telp', 30)->nullable();
            $table->longText('statusaktif')->nullable();
            $table->double('nominaldepositsa', 15, 2)->nullable();
            $table->double('depositke', 15, 2)->nullable();
            $table->double('nominalpinjamansaldoawal', 15, 2)->nullable();
            $table->string('supirold_id')->nullable();
            $table->string('nosim', 30)->nullable();
            $table->date('tglexpsim')->nullable();
            $table->date('tglterbitsim')->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('noktp', 30)->nullable();
            $table->string('nokk', 30)->nullable();
            $table->longText('statusluarkota')->nullable()->nullable();
            $table->double('angsuranpinjaman', 15, 2)->nullable();
            $table->double('plafondeposito', 15, 2)->nullable();
            $table->string('photosupir', 4000)->nullable();
            $table->string('photoktp', 4000)->nullable();
            $table->string('photosim', 4000)->nullable();
            $table->string('photokk', 4000)->nullable();
            $table->string('photoskck', 4000)->nullable();
            $table->string('photodomisili', 4000)->nullable();
            $table->date('tglberhentisupir')->nullable();
            $table->longText('statusblacklist',)->nullable();
            $table->string('pemutihansupir_nobukti')->nullable();

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
        DB::table($temp)->insertUsing([
            'id',
            'namasupir',
            'namaalias',
            'tgllahir',
            'alamat',
            'kota',
            'telp',
            'statusaktif',
            'nominaldepositsa',
            'depositke',
            'nominalpinjamansaldoawal',
            'supirold_id',
            'nosim',
            'tglexpsim',
            'tglterbitsim',
            'keterangan',
            'noktp',
            'nokk',
            'statusluarkota',
            'angsuranpinjaman',
            'plafondeposito',
            'photosupir',
            'photoktp',
            'photosim',
            'photokk',
            'photoskck',
            'photodomisili',
            'tglberhentisupir',
            'statusblacklist',
            'pemutihansupir_nobukti',
            'modifiedby',
            'created_at',
            'updated_at'
        ], $models);


        return  $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'supirold_id') {
            return $query->orderBy('supirlama.namasupir', $this->params['sortOrder']);
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
                        } else if ($filters['field'] == 'supirold_id') {
                            $query = $query->where('supirlama.namasupir', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tgllahir' || $filters['field'] == 'tglterbitsim' || $filters['field'] == 'tglexpsim' || $filters['field'] == 'tglberhentisupir') {
                            $query = $query->whereRaw("format((case when year(isnull($this->table." . $filters['field'] . ",'1900/1/1'))<2000 then null else supir." . $filters['field'] . " end), 'dd-MM-yyyy') LIKE '%$filters[data]%'");
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
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
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
                            } else if ($filters['field'] == 'supirold_id') {
                                $query = $query->orWhere('supirlama.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tgllahir' || $filters['field'] == 'tglterbitsim' || $filters['field'] == 'tglexpsim' || $filters['field'] == 'tglberhentisupir') {
                                $query = $query->orWhereRaw("format((case when year(isnull($this->table." . $filters['field'] . ",'1900/1/1'))<2000 then null else supir." . $filters['field'] . " end), 'dd-MM-yyyy') LIKE '%$filters[data]%'");
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

    private function deleteFiles(Supir $supir)
    {
        $sizeTypes = ['', 'medium_', 'small_'];

        $relatedPhotoSupir = [];
        $relatedPhotoKtp = [];
        $relatedPhotoSim = [];
        $relatedPhotoKk = [];
        $relatedPhotoSkck = [];
        $relatedPhotoDomisili = [];
        $relatedPhotoVaksin = [];
        $relatedPdfSuratPerjanjian = [];

        $photoSupir = json_decode($supir->photosupir, true);
        $photoKtp = json_decode($supir->photoktp, true);
        $photoSim = json_decode($supir->photosim, true);
        $photoKk = json_decode($supir->photokk, true);
        $photoSkck = json_decode($supir->photoskck, true);
        $photoDomisili = json_decode($supir->photodomisili, true);
        $photoVaksin = json_decode($supir->photoVaksin, true);
        $pdfSuratPerjanjian = json_decode($supir->pdfsuratperjanjian, true);

        if ($photoSupir != '') {
            foreach ($photoSupir as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoSupir[] = "supir/profil/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoSupir);
        }

        if ($photoKtp != '') {
            foreach ($photoKtp as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoKtp[] = "supir/ktp/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoKtp);
        }

        if ($photoSim != '') {
            foreach ($photoSim as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoSim[] = "supir/sim/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoSim);
        }

        if ($photoKk != '') {
            foreach ($photoKk as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoKk[] = "supir/kk/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoKk);
        }

        if ($photoSkck != '') {
            foreach ($photoSkck as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoSkck[] = "supir/skck/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoSkck);
        }

        if ($photoDomisili != '') {
            foreach ($photoDomisili as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoDomisili[] = "supir/domisili/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoDomisili);
        }
        if ($photoVaksin != '') {
            foreach ($photoVaksin as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoVaksin[] = "supir/vaksin/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoVaksin);
        }
        if ($pdfSuratPerjanjian != '') {
            foreach ($pdfSuratPerjanjian as $path) {
                $relatedPdfSuratPerjanjian[] = "supir/suratperjanjian/$path";
            }
            Storage::delete($relatedPdfSuratPerjanjian);
        }
    }

    private function storePdfFiles(array $files, string $destinationFolder): string
    {
        $storedFiles = [];

        foreach ($files as $file) {
            $originalFileName = "SURAT-" . $file->hashName();
            $storedFile = Storage::putFileAs('supir/' . $destinationFolder, $file, $originalFileName);
            $storedFiles[] = $originalFileName;
        }

        return json_encode($storedFiles);
    }

    private function storeFiles(array $files, string $destinationFolder): string
    {
        $storedFiles = [];
        if ($destinationFolder == 'supir') {
            $destinationFolder = 'profil';
        }
        foreach ($files as $file) {
            $originalFileName = "$destinationFolder-" . $file->hashName();
            $storedFile = Storage::putFileAs('supir/' . $destinationFolder, $file, $originalFileName);
            $resizedFiles = App::imageResize(storage_path("app/supir/$destinationFolder/"), storage_path("app/$storedFile"), $originalFileName);

            $storedFiles[] = $originalFileName;
        }

        return json_encode($storedFiles);
    }

    public function processStore(array $data): Supir
    {
        try {
            $statusAdaUpdateGambar = DB::table('parameter')->where('grp', 'STATUS ADA UPDATE GAMBAR')->where('default', 'YA')->first();
            $statusLuarKota = DB::table('parameter')->where('grp', 'STATUS LUAR KOTA')->where('default', 'YA')->first();
            $statusZonaTertentu = DB::table('parameter')->where('grp', 'ZONA TERTENTU')->where('default', 'YA')->first();
            $statusBlackList = DB::table('parameter')->where('grp', 'BLACKLIST SUPIR')->where('default', 'YA')->first();

            $supir = new Supir();
            $status = $supir->cekPemutihan($data['noktp']);

            if ($status == true) {
                $data['validate']([
                    'pemutihansupir_nobukti' => 'required'
                ], [
                    'pemutihansupir_nobukti.required' => 'nobukti pemutihan supir ' . app(ErrorController::class)->geterror('WI')->keterangan,
                ]);
            }

            
            $depositke = str_replace(',', '', $data['depositke'] ?? '');
            $supir->namasupir = $data['namasupir'];
            $supir->alamat = $data['alamat'];
            $supir->namaalias = $data['namaalias'];
            $supir->kota = $data['kota'];
            $supir->telp = $data['telp'];
            $supir->statusaktif = $data['statusaktif'];
            $supir->nominaldepositsa = array_key_exists('nominaldepositsa', $data) ? str_replace(',', '', $data['nominaldepositsa']) : 0;
            $supir->depositke = str_replace('.', '', $depositke) ?? 0;
            $supir->tglmasuk = date('Y-m-d', strtotime($data['tglmasuk']));
            $supir->nominalpinjamansaldoawal = str_replace(',', '', $data['nominalpinjamansaldoawal']) ?? 0;
            $supir->pemutihansupir_nobukti = $data['pemutihansupir_nobukti'] ?? '';
            $supir->supirold_id = $data['supirold_id'] ?? 0;
            $supir->tglexpsim = date('Y-m-d', strtotime($data['tglexpsim']));
            $supir->nosim = $data['nosim'];
            $supir->keterangan = $data['keterangan'] ?? '';
            $supir->noktp = $data['noktp'];
            $supir->nokk = $data['nokk'];
            $supir->angsuranpinjaman = str_replace(',', '', $data['angsuranpinjaman']) ?? 0;
            $supir->plafondeposito = str_replace(',', '', $data['plafondeposito']) ?? 0;
            $supir->tgllahir = date('Y-m-d', strtotime($data['tgllahir']));
            $supir->tglterbitsim = date('Y-m-d', strtotime($data['tglterbitsim']));
            $supir->modifiedby = auth('api')->user()->user;

            $supir->statusadaupdategambar = $statusAdaUpdateGambar->id;
            $supir->statusluarkota = $statusLuarKota->id;
            $supir->statuszonatertentu = $statusZonaTertentu->id;
            $supir->statusblacklist = $statusBlackList->id;

            $supir->photosupir = $data['photosupir'];
            $supir->photoktp = $data['photoktp'];
            $supir->photosim = $data['photosim'];
            $supir->photokk = $data['photokk'];
            $supir->photoskck = $data['photoskck'];
            $supir->photodomisili = $data['photodomisili'];
            $supir->photovaksin = $data['photovaksin'];
            $supir->pdfsuratperjanjian = $data['pdfsuratperjanjian'];

            if (!$supir->save()) {
                throw new \Exception("Error storing supir.");
            }

            (new LogTrail())->processStore([
                'namatabel' => strtoupper($supir->getTable()),
                'postingdari' => 'ENTRY SUPIR',
                'idtrans' => $supir->id,
                'nobuktitrans' => $supir->id,
                'aksi' => 'ENTRY',
                'datajson' => $supir->toArray(),
                'modifiedby' => $supir->modifiedby
            ]);

            return $supir;
        } catch (\Throwable $th) {
            $this->deleteFiles($supir);
            throw $th;
        }
    }

    public function processUpdate(Supir $supir, array $data): Supir
    {
        try {
            $supirNew = new Supir();
            $status = $supirNew->cekPemutihan($data['noktp']);

            if ($status == true) {
                $data['validate']([
                    'pemutihansupir_nobukti' => 'required'
                ], [
                    'pemutihansupir_nobukti.required' => 'nobukti pemutihan supir ' . app(ErrorController::class)->geterror('WI')->keterangan,
                ]);
            }

            $depositke = str_replace(',', '', $data['depositke'] ?? '');
            $supir->namasupir = $data['namasupir'];
            $supir->namaalias = $data['namaalias'];
            $supir->alamat = $data['alamat'];
            $supir->kota = $data['kota'];
            $supir->telp = $data['telp'];
            $supir->statusaktif = $data['statusaktif'];
            $supir->pemutihansupir_nobukti = $data['pemutihansupir_nobukti'] ?? '';
            $supir->nominaldepositsa = array_key_exists('nominaldepositsa', $data) ? str_replace(',', '', $data['nominaldepositsa']) : 0;
            $supir->depositke = str_replace('.00', '', $depositke) ?? 0;
            $supir->tglmasuk = date('Y-m-d', strtotime($data['tglmasuk']));
            $supir->nominalpinjamansaldoawal = str_replace(',', '', $data['nominalpinjamansaldoawal']) ?? 0;
            $supir->supirold_id = $data['supirold_id'] ?? 0;
            $supir->tglexpsim = date('Y-m-d', strtotime($data['tglexpsim']));
            $supir->nosim = $data['nosim'];
            $supir->keterangan = $data['keterangan'] ?? '';
            $supir->noktp = $data['noktp'];
            $supir->nokk = $data['nokk'];
            $supir->angsuranpinjaman = str_replace(',', '', $data['angsuranpinjaman']) ?? 0;
            $supir->plafondeposito = str_replace(',', '', $data['plafondeposito']) ?? 0;
            $supir->tgllahir = date('Y-m-d', strtotime($data['tgllahir']));
            $supir->tglterbitsim = date('Y-m-d', strtotime($data['tglterbitsim']));
            $supir->modifiedby = auth('api')->user()->name;

            $this->deleteFiles($supir);

            $supir->photosupir = $data['photosupir'];
            $supir->photoktp = $data['photoktp'];
            $supir->photosim = $data['photosim'];
            $supir->photokk = $data['photokk'];
            $supir->photoskck = $data['photoskck'];
            $supir->photodomisili = $data['photodomisili'];
            $supir->photovaksin = $data['photovaksin'];
            $supir->pdfsuratperjanjian = $data['pdfsuratperjanjian'];

            if (!$supir->save()) {
                throw new \Exception("Error storing supir.");
            }

            (new LogTrail())->processStore([
                'namatabel' => strtoupper($supir->getTable()),
                'postingdari' => 'EDIT SUPIR',
                'idtrans' => $supir->id,
                'nobuktitrans' => $supir->id,
                'aksi' => 'EDIT',
                'datajson' => $supir->toArray(),
                'modifiedby' => $supir->modifiedby
            ]);
            return $supir;
        } catch (\Throwable $th) {
            $this->deleteFiles($supir);
            throw $th;
        }
    }

    public function processDestroy($id): Supir
    {
        $supir = new Supir();
        $supir = $supir->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($supir->getTable()),
            'postingdari' => 'DELETE SUPIR',
            'idtrans' => $supir->id,
            'nobuktitrans' => $supir->id,
            'aksi' => 'DELETE',
            'datajson' => $supir->toArray(),
            'modifiedby' => auth('api')->user()->user

        ]);
        $this->deleteFiles($supir);

        return $supir;
    }
}
