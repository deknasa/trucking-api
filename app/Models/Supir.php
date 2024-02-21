<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Helpers\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;


class Supir extends MyModel
{
    use HasFactory;

    protected $table = 'supir';

    public function cekvalidasihapus($id)
    {
        // cek sudah ada absensi
        $cekTglResign = DB::table("supir")->from(DB::raw("supir with (readuncommitted)"))
            ->select(
                'namasupir',
                DB::raw("(case when year(isnull(supir.tglberhentisupir,'1900/1/1'))=1900 then null else supir.tglberhentisupir end) as tglberhentisupir")
            )
            ->where('id', $id)
            ->first();
        if (request()->aksi == 'EDIT') {

            // dd($cekTglResign->tglberhentisupir);
            if ($cekTglResign->tglberhentisupir != null) {
                $data = [
                    'kondisi' => true,
                    'keterangan' => $cekTglResign->namasupir,
                ];


                goto selesai;
            }
        } else {

            if ($cekTglResign->tglberhentisupir != null) {
                $data = [
                    'kondisi' => true,
                    'keterangan' => $cekTglResign->namasupir,
                ];


                goto selesai;
            }

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
        $supir_id = request()->supir_id ?? '';
        $isProsesUangjalan = request()->isProsesUangjalan ?? '';
        $absensi_id = request()->absensi_id ?? '';
        $tgltrip = request()->tgltrip ?? '';

        $isMandor = auth()->user()->isMandor();
        $isAdmin = auth()->user()->isAdmin();
        $formatCabang = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', 'MANDOR SUPIR')->where('subgrp', 'MANDOR SUPIR')->first();
        
        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'supir.id',
                'supir.namasupir',
                'supir.namaalias',
                DB::raw("(case when year(isnull(supir.tgllahir,'1900/1/1'))<1900 then null else supir.tgllahir end) as tgllahir"),
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
                'statuspostingtnl.memo as statuspostingtnl',
                DB::raw('(case when (year(supir.tglberhentisupir) <= 2000) then null else supir.tglberhentisupir end ) as tglberhentisupir'),
                db::raw("cast((format(pemutihansupir.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpemutihansupir"),
                db::raw("cast(cast(format((cast((format(pemutihansupir.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpemutihansupir"),
                'supir.modifiedby',
                'supir.created_at',
                'supir.updated_at',
                DB::raw("isnull(b.namamandor,'') as mandor_id"),
                DB::raw("'Laporan Supir' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("zona with (readuncommitted)"), 'supir.zona_id', 'zona.id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'supir.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statusadaupdategambar with (readuncommitted)"), 'supir.statusadaupdategambar', '=', 'statusadaupdategambar.id')
            ->leftJoin(DB::raw("parameter as statusluarkota with (readuncommitted)"), 'supir.statusluarkota', '=', 'statusluarkota.id')
            ->leftJoin(DB::raw("parameter as statuszonatertentu with (readuncommitted)"), 'supir.statuszonatertentu', '=', 'statuszonatertentu.id')
            ->leftJoin(DB::raw("parameter as statusblacklist with (readuncommitted)"), 'supir.statusblacklist', '=', 'statusblacklist.id')
            ->leftJoin(DB::raw("parameter as statuspostingtnl with (readuncommitted)"), 'supir.statuspostingtnl', '=', 'statuspostingtnl.id')
            ->leftJoin(DB::raw("pemutihansupirheader as pemutihansupir with (readuncommitted)"), 'supir.pemutihansupir_nobukti', '=', 'pemutihansupir.nobukti')
            ->leftJoin(DB::raw("supir as supirlama with (readuncommitted)"), 'supir.supirold_id', '=', 'supirlama.id')
            ->leftJoin(DB::raw("mandor as b with (readuncommitted)"), 'supir.mandor_id', '=', 'b.id');


            // if (!$isAdmin && ($formatCabang->text == 'FORMAT 2')) {
            //     if ($isMandor) {
            //         $query->where('supir.mandor_id', $isMandor->mandor_id);
            //     }
            // }

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
            $trado_id = request()->trado_id ?? '';
            $tglbukti = date('Y-m-d', strtotime($tgltrip));
            $tradoMilikSupir = DB::table('parameter')->where('grp', 'ABSENSI SUPIR')->where('subgrp', 'TRADO MILIK SUPIR')->first();
            $absensiSupirHeader = AbsensiSupirHeader::where('tglbukti', $tglbukti)->first();
            if ($tradoMilikSupir->text == 'YA') {
                $query->whereRaw("supir.id in (select supir_id from absensisupirdetail where absensi_id=$absensiSupirHeader->id and trado_id=$trado_id)");
            } else {
                $query->whereRaw("supir.id in (select supir_id from absensisupirdetail where absensi_id=$absensiSupirHeader->id)");
            }
        }
        if($isProsesUangjalan == true)
        {
            $query->addSelect(DB::raw("absensisupirdetail.uangjalan"))
            ->join(DB::raw("absensisupirdetail with (readuncommitted)"), 'absensisupirdetail.supir_id','supir.id')
            ->where('absensisupirdetail.absensi_id', $absensi_id)
            ->where('absensisupirdetail.uangjalan', '!=', 0);
        }

        if ($supir_id != '') {
            $query->where('supir.id', $supir_id);
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->paginate($query);

        // dd($query->toSql());
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
            $table->unsignedBigInteger('statuspostingtnl')->nullable();
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
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS POSTING TNL')
            ->where('subgrp', '=', 'STATUS POSTING TNL')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatuspostingtnl = $status->id ?? 0;


        DB::table($tempdefault)->insert(
            [
                "statusaktif" => $iddefaultstatusaktif,
                "statusadaupdategambar" => $iddefaultstatusUpdGambar,
                "statusluarkota" => $iddefaultstatusLuarKota,
                "statuszonatertentu" => $iddefaultstatusZonaTertentu,
                "statusblacklist" => $iddefaultstatusBlacklist,
                "statuspostingtnl" => $iddefaultstatuspostingtnl
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
                'statusblacklist',
                'statuspostingtnl',
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
                'supir.statuspostingtnl',
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
                statuspostingtnl.memo as statuspostingtnl,
                isnull(b.namamandor,'') as mandor_id,
                $this->table.modifiedby,
                $this->table.created_at,
                $this->table.updated_at"
            )

        )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'supir.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statusluarkota with (readuncommitted)"), 'supir.statusluarkota', '=', 'statusluarkota.id')
            ->leftJoin(DB::raw("parameter as statusblacklist with (readuncommitted)"), 'supir.statusblacklist', '=', 'statusblacklist.id')
            ->leftJoin(DB::raw("parameter as statuspostingtnl with (readuncommitted)"), 'supir.statuspostingtnl', '=', 'statuspostingtnl.id')
            ->leftJoin('supir as supirlama', 'supir.supirold_id', '=', 'supirlama.id')
            ->leftJoin(DB::raw("mandor as b with (readuncommitted)"), 'supir.mandor_id', '=', 'b.id');
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
            $table->longText('statuspostingtnl')->nullable();
            $table->string('mandor_id', 100)->nullable();

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
            'statuspostingtnl',
            'mandor_id',
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
        } else if ($this->params['sortIndex'] == 'mandor_id') {
            return $query->orderBy('b.namamandor', $this->params['sortOrder']);
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
                        } else if ($filters['field'] == 'statuspostingtnl') {
                            $query = $query->where('statuspostingtnl.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'zona_id') {
                            $query = $query->where('zona.zona', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'supirold_id') {
                            $query = $query->where('supirlama.namasupir', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'mandor_id') {
                            $query = $query->where('b.namamandor', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tgllahir' || $filters['field'] == 'tglterbitsim' || $filters['field'] == 'tglexpsim' || $filters['field'] == 'tglberhentisupir') {
                            $query = $query->whereRaw("format((case when year(isnull($this->table." . $filters['field'] . ",'1900/1/1'))<2000 then null else supir." . $filters['field'] . " end), 'dd-MM-yyyy') LIKE '%$filters[data]%'");
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
                            } else if ($filters['field'] == 'statuspostingtnl') {
                                $query = $query->orWhere('statuspostingtnl.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'zona_id') {
                                $query = $query->orWhere('zona.zona', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'mandor_id') {
                                $query = $query->orwhere('b.namamandor', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'supirold_id') {
                                $query = $query->orWhere('supirlama.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tgllahir' || $filters['field'] == 'tglterbitsim' || $filters['field'] == 'tglexpsim' || $filters['field'] == 'tglberhentisupir') {
                                $query = $query->orWhereRaw("format((case when year(isnull($this->table." . $filters['field'] . ",'1900/1/1'))<2000 then null else supir." . $filters['field'] . " end), 'dd-MM-yyyy') LIKE '%$filters[data]%'");
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

    private function storePdfFilesBase64(array $files, string $destinationFolder): string
    {
        $storedFiles = [];

        foreach ($files as $file) {
            $originalFileName = "SURAT-" . hash('sha256', $file) . '.pdf';
            $pdfData = base64_decode($file);
            $storedFile = Storage::put('supir/' . $destinationFolder . '/' . $originalFileName, $pdfData);
            $storedFiles[] = $originalFileName;
        }

        return json_encode($storedFiles);
    }

    private function storeFilesBase64(array $files, string $destinationFolder): string
    {
        $storedFiles = [];
        if ($destinationFolder == 'supir') {
            $destinationFolder = 'profil';
        }
        foreach ($files as $file) {
            $originalFileName = "$destinationFolder-" . hash('sha256', $file) . '.jpg';
            $imageData = base64_decode($file);
            $storedFile = Storage::put('supir/' . $destinationFolder . '/' . $originalFileName, $imageData);
            $resizedFiles = App::imageResize(storage_path("app/supir/$destinationFolder/"), storage_path("app/supir/$destinationFolder/$originalFileName"), $originalFileName);

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
            $isMandor = auth()->user()->isMandor();
            if ($isMandor) {
                $data['mandor_id'] = $isMandor->mandor_id;
            }
            $supir = new Supir();


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
            $supir->mandor_id = $data['mandor_id']??'';
            $supir->angsuranpinjaman = str_replace(',', '', $data['angsuranpinjaman']) ?? 0;
            $supir->plafondeposito = str_replace(',', '', $data['plafondeposito']) ?? 0;
            $supir->tgllahir = date('Y-m-d', strtotime($data['tgllahir']));
            $supir->tglterbitsim = date('Y-m-d', strtotime($data['tglterbitsim']));
            $supir->statuspostingtnl = $data['statuspostingtnl'];
            $supir->modifiedby = auth('api')->user()->user;
            $supir->info = html_entity_decode(request()->info);

            $supir->statusadaupdategambar = $statusAdaUpdateGambar->id;
            $supir->statusluarkota = $statusLuarKota->id;
            $supir->statuszonatertentu = $statusZonaTertentu->id;
            $supir->statusblacklist = $statusBlackList->id;
            if ($data['from'] != '') {
                $supir->photosupir = $this->storeFilesBase64($data['photosupir'], 'supir');
                $supir->photoktp = $this->storeFilesBase64($data['photoktp'], 'ktp');
                $supir->photosim = $this->storeFilesBase64($data['photosim'], 'sim');
                $supir->photokk = $this->storeFilesBase64($data['photokk'], 'kk');
                $supir->photoskck = $this->storeFilesBase64($data['photoskck'], 'skck');
                $supir->photodomisili = $this->storeFilesBase64($data['photodomisili'], 'domisili');
                $supir->photovaksin = $this->storeFilesBase64($data['photovaksin'], 'vaksin');
                $supir->pdfsuratperjanjian = $this->storePdfFilesBase64($data['pdfsuratperjanjian'], 'suratperjanjian');
            } else {
                $supir->photosupir = (count($data['photosupir']) > 0) ? $this->storeFiles($data['photosupir'], 'supir') : '';
                $supir->photoktp = (count($data['photoktp']) > 0) ? $this->storeFiles($data['photoktp'], 'ktp') : '';
                $supir->photosim = (count($data['photosim']) > 0) ? $this->storeFiles($data['photosim'], 'sim') : '';
                $supir->photokk = (count($data['photokk']) > 0) ? $this->storeFiles($data['photokk'], 'kk') : '';
                $supir->photoskck = (count($data['photoskck']) > 0) ? $this->storeFiles($data['photoskck'], 'skck') : '';
                $supir->photodomisili = (count($data['photodomisili']) > 0) ? $this->storeFiles($data['photodomisili'], 'domisili') : '';
                $supir->photovaksin = (count($data['photovaksin']) > 0) ? $this->storeFiles($data['photovaksin'], 'vaksin') : '';
                $supir->pdfsuratperjanjian = (count($data['pdfsuratperjanjian']) > 0) ? $this->storePdfFiles($data['pdfsuratperjanjian'], 'suratperjanjian') : '';
            }
            if (!$supir->save()) {
                throw new \Exception("Error storing supir.");
            }

            $approvalSupirGambar = ApprovalSupirGambar::where('noktp', $supir->noktp)->first();
            if ($approvalSupirGambar) {
                $nonApp = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->whereRaw("grp like '%STATUS APPROVAL%'")->whereRaw("text like '%NON APPROVAL%'")->first();
                $approvalSupirGambar->statusapproval = $nonApp->id;
                $approvalSupirGambar->save();
            }

            $approvalSupirKeterangan = ApprovalSupirKeterangan::where('noktp', $supir->noktp)->first();
            if ($approvalSupirKeterangan) {
                $nonApp = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->whereRaw("grp like '%STATUS APPROVAL%'")->whereRaw("text like '%NON APPROVAL%'")->first();
                $approvalSupirKeterangan->statusapproval = $nonApp->id;
                $approvalSupirKeterangan->save();
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

            if ($data['from'] == '') {
                if ($data['pemutihansupir_nobukti'] != '') {
                    $supir_id[] = $supir->id;
                    $getPosting = DB::table("pemutihansupirdetail")->from(DB::raw("pemutihansupirdetail with (readuncommitted)"))
                        ->select(DB::raw("sum(nominal) as nominal"))
                        ->where('nobukti', $data['pemutihansupir_nobukti'])
                        ->where('statusposting', 83)
                        ->first();
                    if ($getPosting != '') {

                        $nominalposting[] = $getPosting->nominal;
                        $keterangan[] = 'PINJAMAN DARI PEMUTIHAN ' . $data['pemutihansupir_nobukti'] . ' (POSTING)';

                        $pengeluaranRequest = [
                            'tglbukti' => date('Y-m-d'),
                            'pengeluarantrucking_id' => 1,
                            'statusposting' => 84,
                            'supir_id' => $supir_id,
                            'pemutihansupir_nobukti' => $data['pemutihansupir_nobukti'],
                            'nominal' => $nominalposting,
                            'keterangan' => $keterangan
                        ];
                        (new PengeluaranTruckingHeader())->processStore($pengeluaranRequest);
                    }
                    $getNonPosting = DB::table("pemutihansupirdetail")->from(DB::raw("pemutihansupirdetail with (readuncommitted)"))
                        ->select(DB::raw("sum(nominal) as nominal"))
                        ->where('nobukti', $data['pemutihansupir_nobukti'])
                        ->where('statusposting', 84)
                        ->first();
                    if ($getNonPosting != '') {

                        $nominalnonposting[] = $getNonPosting->nominal;
                        $keteranganNon[] = 'PINJAMAN DARI PEMUTIHAN ' . $data['pemutihansupir_nobukti'] . ' (NON POSTING)';

                        $pengeluaranRequestNon = [
                            'tglbukti' => date('Y-m-d'),
                            'pengeluarantrucking_id' => 1,
                            'statusposting' => 84,
                            'supir_id' => $supir_id,
                            'pemutihansupir_nobukti' => $data['pemutihansupir_nobukti'],
                            'nominal' => $nominalnonposting,
                            'keterangan' => $keteranganNon
                        ];
                        (new PengeluaranTruckingHeader())->processStore($pengeluaranRequestNon);
                    }
                }
            }
            return $supir;
        } catch (\Throwable $th) {
            $this->deleteFiles($supir);
            throw $th;
        }
    }

    public function processUpdate(Supir $supir, array $data): Supir
    {
        try {
            $isMandor = auth()->user()->isMandor();
            if ($isMandor) {
                $data['mandor_id'] = $isMandor->mandor_id;
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
            $supir->mandor_id = $data['mandor_id']?? '';
            $supir->angsuranpinjaman = str_replace(',', '', $data['angsuranpinjaman']) ?? 0;
            $supir->plafondeposito = str_replace(',', '', $data['plafondeposito']) ?? 0;
            $supir->tgllahir = date('Y-m-d', strtotime($data['tgllahir']));
            $supir->tglterbitsim = date('Y-m-d', strtotime($data['tglterbitsim']));
            $supir->modifiedby = auth('api')->user()->name;
            $supir->info = html_entity_decode(request()->info);

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

            $approvalSupirGambar = ApprovalSupirGambar::where('noktp', $supir->noktp)->first();
            if ($approvalSupirGambar) {
                $nonApp = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->whereRaw("grp like '%STATUS APPROVAL%'")->whereRaw("text like '%NON APPROVAL%'")->first();
                $approvalSupirGambar->statusapproval = $nonApp->id;
                $approvalSupirGambar->save();
            }

            $approvalSupirKeterangan = ApprovalSupirKeterangan::where('noktp', $supir->noktp)->first();
            if ($approvalSupirKeterangan) {
                $nonApp = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->whereRaw("grp like '%STATUS APPROVAL%'")->whereRaw("text like '%NON APPROVAL%'")->first();
                $approvalSupirKeterangan->statusapproval = $nonApp->id;
                $approvalSupirKeterangan->save();
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

    public function postingTnl($data, $gambar)
    {
        $photoSupir = json_decode($gambar['supir']);
        $photoKtp = json_decode($gambar['ktp']);
        $photoSim = json_decode($gambar['sim']);
        $photoKk = json_decode($gambar['kk']);
        $photoSkck = json_decode($gambar['skck']);
        $photoDomisili = json_decode($gambar['domisili']);
        $photoVaksin = json_decode($gambar['vaksin']);
        $photoPDF = json_decode($gambar['pdfsuratperjanjian']);

        $server = config('app.server_jkt');
        $getToken = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])
            ->post($server . 'truckingtnl-api/public/api/token', [
                'user' => 'ADMIN',
                'password' => getenv('PASSWORD_TNL'),
                'ipclient' => '',
                'ipserver' => '',
                'latitude' => '',
                'longitude' => '',
                'browser' => '',
                'os' => '',
            ]);
        if ($getToken->getStatusCode() == '404') {
            throw new \Exception("Akun Tidak Terdaftar di Trucking TNL");
        } else if ($getToken->getStatusCode() == '200') {

            $access_token = json_decode($getToken, TRUE)['access_token'];

            foreach ($photoSupir as $imagePath) {
                $supirBase64[] = base64_encode(file_get_contents(storage_path("app/supir/profil/" . $imagePath)));
            }
            foreach ($photoKtp as $imagePath) {
                $ktpBase64[] = base64_encode(file_get_contents(storage_path("app/supir/ktp/" . $imagePath)));
            }
            foreach ($photoSim as $imagePath) {
                $simBase64[] = base64_encode(file_get_contents(storage_path("app/supir/sim/" . $imagePath)));
            }
            foreach ($photoKk as $imagePath) {
                $kkBase64[] = base64_encode(file_get_contents(storage_path("app/supir/kk/" . $imagePath)));
            }
            foreach ($photoSkck as $imagePath) {
                $skckBase64[] = base64_encode(file_get_contents(storage_path("app/supir/skck/" . $imagePath)));
            }
            foreach ($photoDomisili as $imagePath) {
                $domisiliBase64[] = base64_encode(file_get_contents(storage_path("app/supir/domisili/" . $imagePath)));
            }
            foreach ($photoVaksin as $imagePath) {
                $vaksinBase64[] = base64_encode(file_get_contents(storage_path("app/supir/vaksin/" . $imagePath)));
            }
            foreach ($photoPDF as $imagePath) {
                $pdfBase64[] = base64_encode(file_get_contents(storage_path("app/supir/suratperjanjian/" . $imagePath)));
            }
            $data['photosupir'] = $supirBase64;
            $data['photoktp'] = $ktpBase64;
            $data['photosim'] = $simBase64;
            $data['photokk'] = $kkBase64;
            $data['photoskck'] = $skckBase64;
            $data['photodomisili'] = $domisiliBase64;
            $data['photovaksin'] = $vaksinBase64;
            $data['pdfsuratperjanjian'] = $pdfBase64;
            $data['from'] = 'jkt';
            $copySupir = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
            ])

                ->post($server . "truckingtnl-api/public/api/supir", $data);

            $tesResp = $copySupir->toPsrResponse();
            $response = [
                'statuscode' => $tesResp->getStatusCode(),
                'data' => $copySupir->json(),
            ];
            $dataResp = $copySupir->json();
            if ($tesResp->getStatusCode() != 201) {
                if ($tesResp->getStatusCode() == 422) {
                    throw new \Exception($dataResp['errors']['namasupir'][0] . ' di TNL');
                } else {
                    throw new \Exception($dataResp['message']);
                }
            }
            return $response;
        } else {
            throw new \Exception("server tidak bisa diakses");
        }
    }

    public function processStatusNonAktifKeterangan($noktp)
    {

        $supir = Supir::from(DB::raw("supir with (readuncommitted)"))->where('noktp', $noktp)->first();
        if (!$supir) {
            return false;
        }
        $statusNonAktif = Parameter::from(DB::Raw("parameter with (readuncommitted)"))->select('id')->where('grp', '=', 'STATUS AKTIF')->where('subgrp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        $statusAktif = Parameter::from(DB::Raw("parameter with (readuncommitted)"))->select('id')->where('grp', '=', 'STATUS AKTIF')->where('subgrp', '=', 'STATUS AKTIF')->where('text', '=', 'AKTIF')->first();

        $required = [
            "namasupir" => $supir->namasupir,
            "alamat" => $supir->alamat,
            "namaalias" => $supir->namaalias,
            "kota" => $supir->kota,
            "telp" => $supir->telp,
            "nosim" => $supir->nosim,
            "noktp" => $supir->noktp,
            "nokk" => $supir->nokk,
            "tgllahir" => $supir->tgllahir,
        ];
        $key = array_keys($required, null);
        if (count($key)) {
            $supir->statusaktif = $statusNonAktif->id;
            $supir->save();
        }
        return $key;
    }
    public function processStatusNonAktifGambar($noktp)
    {
        $supir = Supir::from(DB::raw("supir with (readuncommitted)"))->where('noktp', $noktp)->first();
        if (!$supir) {
            return false;
        }
        $statusNonAktif = Parameter::from(DB::Raw("parameter with (readuncommitted)"))->select('id')->where('grp', '=', 'STATUS AKTIF')->where('subgrp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        $statusAktif = Parameter::from(DB::Raw("parameter with (readuncommitted)"))->select('id')->where('grp', '=', 'STATUS AKTIF')->where('subgrp', '=', 'STATUS AKTIF')->where('text', '=', 'AKTIF')->first();

        $required = [
            "photosupir" => $supir->photosupir,
            "photoktp" => $supir->photoktp,
            "photosim" => $supir->photosim,
            "photokk" => $supir->photokk,
            "photoskck" => $supir->photoskck,
            "photodomisili" => $supir->photodomisili,
            "photovaksin" => $supir->photovaksin,
            "pdfsuratperjanjian" => $supir->pdfsuratperjanjian,
        ];

        $key = array_keys($required, null);
        if (count($key)) {
            $supir->statusaktif = $statusNonAktif->id;
            $supir->save();
        }
        return $key;
    }

    public function getSupirResignModel($noktp)
    {
        $getPemutihan = DB::table("pemutihansupirheader")->from(DB::raw("pemutihansupirheader with (readuncommitted)"))
            ->select('pemutihansupirheader.nobukti')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pemutihansupirheader.supir_id', 'supir.id')
            ->where('supir.noktp', $noktp)
            ->orderBy('pemutihansupirheader.id', 'desc')
            ->first();
        $nobuktiPemutihan = '';
        if ($getPemutihan != '') {
            $nobuktiPemutihan = $getPemutihan->nobukti;
        }

        $query = Supir::from(DB::raw("supir with (readuncommitted)"))
            ->select(
                'supir.id',
                'supir.namasupir',
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
                DB::raw("'$nobuktiPemutihan' as pemutihansupir_nobukti")
            )
            ->where('supir.noktp', $noktp)
            ->leftJoin(DB::raw("supir as supirlama with (readuncommitted)"), 'supir.supirold_id', '=', 'supirlama.id')
            ->first();

        return $query;
    }

    public function validationSupirResign($noktp, $id = 0)
    {
        $query = DB::table("supir")->from(DB::raw("supir with (readuncommitted)"))
            ->where("noktp", $noktp)
            ->whereRaw("isnull(tglberhentisupir,'1900-01-01') = '1900-01-01'");
        if ($id != 0) {
            $query->whereRaw("id != $id");
        }
        $data = $query->first();
        if ($data != null) {
            return false;
        } else {
            return true;
        }
    }

    public function getHistoryMandor($id)
    {
        $query = DB::table("supir")->from(DB::raw("supir with (readuncommitted)"))
            ->select(
                'supir.id',
                'supir.namasupir as supir',
                'supir.mandor_id',
                'mandor.namamandor as mandor',
                DB::raw('ISNULL(supir.tglberlakumilikmandor, getdate()) as tglberlaku'),
            )
            ->leftJoin(DB::raw("mandor with (readuncommitted)"), 'supir.mandor_id', 'mandor.id')
            ->where('supir.id', $id)
            ->first();

        return $query;
    }

    public function processHistorySupirMilikMandor($data)
    {
        $supir = Supir::findOrFail($data['id']);
        $supir->mandor_id = $data['mandorbaru_id'];
        $supir->tglberlakumilikmandor = date('Y-m-d', strtotime($data['tglberlaku']));

        if (!$supir->save()) {
            throw new \Exception("Error updating supir milik mandor.");
        }
        $dataLogtrail = [
            'id' => $supir->id,
            'namasupir' => $supir->namasupir,
            'mandorbaru_id' => $supir->mandor_id,
            'mandorlama_id' => $data['mandor_id'],
            'tglberlakumilikmandor' => $supir->tglberlakumilikmandor,

        ];
        
        (new HistorySupirMilikMandor())->processStore($dataLogtrail);
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($supir->getTable()),
            'postingdari' => 'HISTORY SUPIR MILIK MANDOR',
            'idtrans' => $supir->id,
            'nobuktitrans' => $supir->id,
            'aksi' => 'HISTORY SUPIR MILIK MANDOR',
            'datajson' => $dataLogtrail,
            'modifiedby' => auth('api')->user()->name
        ]);
        
        DB::table('suratpengantar')
            ->where('supir_id', $supir->id)
            ->where('tglbukti', '>=', $supir->tglberlakumilikmandor)
            ->update([
                'mandorsupir_id' => $supir->mandor_id,
                'modifiedby' => auth('api')->user()->name
            ]);
        return $supir;
    }

    public function getListHistoryMandor($id)
    {
        $query = DB::table('logtrail')->from(DB::raw("logtrail with (readuncommitted)"))
            ->select('datajson')
            ->where('namatabel', 'SUPIR')
            ->where('idtrans', $id)
            ->where('aksi', 'HISTORY SUPIR MILIK MANDOR')
            ->get();
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->id();
            $table->integer('supir_id')->nullable();
            $table->integer('mandorbaru_id')->nullable();
            $table->integer('mandorlama_id')->nullable();
            $table->date('tanggalberlakugrid')->nullable();
        });
        foreach ($query as $row) {
            $data = json_decode($row->datajson, true);
            DB::table($temp)->insert(
                [
                    'supir_id' => $data['id'],
                    'mandorbaru_id' => $data['mandorbaru_id'],
                    'mandorlama_id' => $data['mandorlama_id'],
                    'tanggalberlakugrid' => $data['tglberlakumilikmandor'],
                ]
            );
        }
        $this->setRequestParameters();
        $query = DB::table($temp)->from(DB::raw("$temp as a with (readuncommitted)"))
            ->select('a.id as idgrid', 'supir.namasupir as namasupirgrid', 'mandorbaru.namamandor as mandorbarugrid', 'mandorlama.namamandor as mandorlamagrid', 'a.tanggalberlakugrid')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'a.supir_id', 'supir.id')
            ->leftJoin(DB::raw("mandor as mandorbaru with (readuncommitted)"), 'a.mandorbaru_id', 'mandorbaru.id')
            ->leftJoin(DB::raw("mandor as mandorlama with (readuncommitted)"), 'a.mandorlama_id', 'mandorlama.id');
            

        $this->sortHistory($query);
        $this->filterHistory($query);
        $this->paginateHistory($query);
        return $query->get();
    }

    public function sortHistory($query)
    {
        if ($this->params['sortIndex'] == 'namasupirgrid') {
            return $query->orderBy('supir.namasupir', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'mandorbarugrid') {
            return $query->orderBy('mandorbaru.namamandor', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'mandorlamagrid') {
            return $query->orderBy('mandorlama.namamandor', $this->params['sortOrder']);
        } else {
            return $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }


    public function filterHistory($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'namasupirgrid') {
                            $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'mandorlamagrid') {
                            $query = $query->where('mandorlama.namamandor', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'mandorbarugrid') {
                            $query = $query->where('mandorbaru.namamandor', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'tanggalberlakugrid') {
                            $query = $query->whereRaw("format((case when year(isnull(a." . $filters['field'] . ",'1900/1/1'))<2000 then null else a." . $filters['field'] . " end), 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'mandorlamagrid') {
                                $query = $query->orwhere('mandorlama.namamandor', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'mandorbarugrid') {
                                $query = $query->orwhere('mandorbaru.namamandor', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'namasupirgrid') {
                                $query = $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tanggalberlakugrid') {
                                $query = $query->orWhereRaw("format((case when year(isnull(a." . $filters['field'] . ",'1900/1/1'))<2000 then null else a." . $filters['field'] . " end), 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function paginateHistory($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $Supir = Supir::find($data['Id'][$i]);

                $Supir->statusaktif = $statusnonaktif->id;
                $aksi = $statusnonaktif->text;

                // dd($Supir);
            if ($Supir->save()) {
                
                (new LogTrail())->processStore([
                    
                    'namatabel' => strtoupper($Supir->getTable()),
                    'postingdari' => 'APPROVAL SUPIR',
                    'idtrans' => $Supir->id,
                    'nobuktitrans' => $Supir->id,
                    'aksi' => $aksi,
                    'datajson' => $Supir->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }


        return $Supir;
    }
    public function processApprovalSupirLuarKota(array $data)
    {

        $statusLuarKota = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS LUAR KOTA')->where('text', '=', 'BOLEH LUAR KOTA')->first();
        $statusBukanLuarKota = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS LUAR KOTA')->where('text', '=', 'TIDAK BOLEH LUAR KOTA')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $supir = Supir::find($data['Id'][$i]);
            if ($supir->statusluarkota == $statusLuarKota->id) {
                $supir->statusluarkota = $statusBukanLuarKota->id;
                $aksi = $statusBukanLuarKota->text;
            } else {
                $supir->statusluarkota = $statusLuarKota->id;
                $aksi = $statusLuarKota->text;
            }

                // dd($Supir);
            if ($supir->save()) {
                
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($supir->getTable()),
                    'postingdari' => 'APPROVED SUPIR LUAR KOTA',
                    'idtrans' => $supir->id,
                    'nobuktitrans' => $supir->id,
                    'aksi' => $aksi,
                    'datajson' => $supir->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ]);
            }
        }


        return $supir;
    }
    
    public function processApprovalBlackListSupir(array $data)
    {
        $statusBlackList = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'BLACKLIST SUPIR')->where('text', '=', 'SUPIR BLACKLIST')->first();
        $statusBukanBlackList = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'BLACKLIST SUPIR')->where('text', '=', 'BUKAN SUPIR BLACKLIST')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $supir = Supir::find($data['Id'][$i]);
            if ($supir->statusblacklist == $statusBlackList->id) {
                $supir->statusblacklist = $statusBukanBlackList->id;
                $aksi = $statusBukanBlackList->text;
            } else {
                $supir->statusblacklist = $statusBlackList->id;
                $aksi = $statusBlackList->text;
            }
            
            
            // dd($Supir);
            if ($supir->save()) {
                
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($supir->getTable()),
                    'postingdari' => 'APPROVED BLACKLIST SUPIR',
                    'idtrans' => $supir->id,
                    'nobuktitrans' => $supir->id,
                    'aksi' => $aksi,
                    'datajson' => $supir->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ]);
            }
        }
        

        return $supir;
    }

    
}
