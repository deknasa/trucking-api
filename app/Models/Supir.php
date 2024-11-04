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
        $this->RefreshSupirNonAktif();
        $absen = request()->absen ?? '';
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $from = request()->from ?? '';
        $aktif = request()->aktif ?? '';
        $supir_id = request()->supir_id ?? '';
        $isProsesUangjalan = request()->isProsesUangjalan ?? '';
        $absensi_id = request()->absensi_id ?? '';
        $tgltrip = request()->tgltrip ?? '';
        $fromSupirSerap = request()->fromSupirSerap ?? '';
        $isMandor = auth()->user()->isMandor();
        $isAdmin = auth()->user()->isAdmin();
        $formatCabang = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', 'MANDOR SUPIR')->where('subgrp', 'MANDOR SUPIR')->first();

        $defaultmemononapproval = db::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.memo'
            )
            ->where('a.grp', 'STATUS APPROVAL')
            ->where('a.subgrp', 'STATUS APPROVAL')
            ->where('a.text', 'NON APPROVAL')
            ->first()->memo ?? '';

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
                DB::raw("(case when (year(isnull(supir.tglbatastidakbolehluarkota,'1900-01-01')) <= 2000) then null else supir.tglbatastidakbolehluarkota end ) as tglbatastidakbolehluarkota"),
                'supir.keterangan',
                'supir.noktp',
                'supir.nokk',
                'statusadaupdategambar.memo as statusadaupdategambar',
                'statusluarkota.memo as statusluarkota',
                'supir.keterangantidakbolehluarkota',
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
                DB::raw('(case when (year(supir.tglmasuk) <= 2000) then null else supir.tglmasuk end ) as tglmasuk'),
                DB::raw('(case when (year(supir.tglberhentisupir) <= 2000) then null else supir.tglberhentisupir end ) as tglberhentisupir'),
                db::raw("cast((format(pemutihansupir.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpemutihansupir"),
                db::raw("cast(cast(format((cast((format(pemutihansupir.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpemutihansupir"),
                'statusapproval.memo as statusapproval',
                'supir.modifiedby',
                'supir.created_at',
                'supir.updated_at',
                DB::raw("isnull(b.namamandor,'') as mandor_id"),
                DB::raw("'Laporan Supir' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
                db::raw("isnull(parameter_statusapprovalhistorymilikmandor.memo,'" . $defaultmemononapproval . "')  as statusapprovalhistorysupirmilikmandor"),
                'supir.userapprovalhistorysupirmilikmandor as userapprovalhistorysupirmilikmandor',
                'supir.tglapprovalhistorysupirmilikmandor as tglapprovalhistorysupirmilikmandor',
                'supir.tglupdatehistorysupirmilikmandor as tglupdatehistorysupirmilikmandor',
                'supir.tglberlakumilikmandor as tglberlakumilikmandor',


            )
            ->leftJoin(DB::raw("zona with (readuncommitted)"), 'supir.zona_id', 'zona.id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'supir.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statusadaupdategambar with (readuncommitted)"), 'supir.statusadaupdategambar', '=', 'statusadaupdategambar.id')
            ->leftJoin(DB::raw("parameter as statusluarkota with (readuncommitted)"), 'supir.statusluarkota', '=', 'statusluarkota.id')
            ->leftJoin(DB::raw("parameter as statuszonatertentu with (readuncommitted)"), 'supir.statuszonatertentu', '=', 'statuszonatertentu.id')
            ->leftJoin(DB::raw("parameter as statusblacklist with (readuncommitted)"), 'supir.statusblacklist', '=', 'statusblacklist.id')
            ->leftJoin(DB::raw("parameter as statuspostingtnl with (readuncommitted)"), 'supir.statuspostingtnl', '=', 'statuspostingtnl.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'supir.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("pemutihansupirheader as pemutihansupir with (readuncommitted)"), 'supir.pemutihansupir_nobukti', '=', 'pemutihansupir.nobukti')
            ->leftJoin(DB::raw("parameter as parameter_statusapprovalhistorymilikmandor with (readuncommitted)"), 'supir.statusapprovalhistorysupirmilikmandor', 'parameter_statusapprovalhistorymilikmandor.id')
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
        if ($from == 'historytradosupir') {
            $query->whereRaw("supir.id not in (select supir_id from trado where isnull(supir_id,0) != 0)");
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
        if ($fromSupirSerap == "true") {
            $tglbukti = date('Y-m-d', strtotime($tgltrip));
            $absensiSupirHeader = AbsensiSupirHeader::where('tglbukti', $tglbukti)->first();
            if (!$absensiSupirHeader) {
                return $query->where('supir.id', 0)->get();
            }
            
            $statusapproval = DB::table('parameter')->where('grp', 'STATUS APPROVAL')->where('subgrp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
            $parameter = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp', '=', 'ABSENSI SUPIR SERAP')
                ->get();
            $values = array_column($parameter->toArray(), 'text');
            $result = implode(',', $values);
            $query->whereRaw("supir.id not in (select supirold_id from absensisupirdetail where absensi_id=$absensiSupirHeader->id and absen_id IN ($result) )");
            $query->where("supir.statusapproval",$statusapproval->id);
        }
        if ($isProsesUangjalan == true) {
            $query->addSelect(DB::raw("absensisupirdetail.uangjalan"))
                ->join(DB::raw("absensisupirdetail with (readuncommitted)"), 'absensisupirdetail.supir_id', 'supir.id')
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
            $table->string('statusaktifnama', 300)->nullable();
            $table->unsignedBigInteger('statusadaupdategambar')->nullable();
            $table->unsignedBigInteger('statusluarkota')->nullable();
            $table->unsignedBigInteger('statuszonatertentu')->nullable();
            $table->unsignedBigInteger('statusblacklist')->nullable();
            $table->unsignedBigInteger('statuspostingtnl')->nullable();
            $table->string('statuspostingtnlnama', 300)->nullable();
        });

        // AKTIF
        $statusAktif = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id',
                'text'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('DEFAULT', '=', 'YA')
            ->first();

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
        
        $statusPostingTNL = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id',
                'text'
            )
            ->where('grp', '=', 'STATUS POSTING TNL')
            ->where('subgrp', '=', 'STATUS POSTING TNL')
            ->where('default', '=', 'YA')
            ->first();

        DB::table($tempdefault)->insert(
            [
                "statusaktif" => $statusAktif->id ?? 0,
                "statusaktifnama" => $statusAktif->text ?? "",
                "statusadaupdategambar" => $iddefaultstatusUpdGambar,
                "statusluarkota" => $iddefaultstatusLuarKota,
                "statuszonatertentu" => $iddefaultstatusZonaTertentu,
                "statusblacklist" => $iddefaultstatusBlacklist,
                "statuspostingtnl" => $statusPostingTNL->id ?? 0,
                "statuspostingtnlnama" => $statusPostingTNL->text ?? ""
            ]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusaktifnama',
                'statusadaupdategambar',
                'statusluarkota',
                'statuszonatertentu',
                'statusblacklist',
                'statuspostingtnl',
                'statuspostingtnlnama',
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
        $defaultmemononapproval = db::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.memo'
            )
            ->where('a.grp', 'STATUS APPROVAL')
            ->where('a.subgrp', 'STATUS APPROVAL')
            ->where('a.text', 'NON APPROVAL')
            ->first()->memo ?? '';

        $userid = auth('api')->user()->id;

        $acoid = db::table('acos')->from(db::raw("acos a with (readuncommitted)"))
            ->select(
                'a.id'
            )
            ->where('a.class', 'supir')
            ->where('a.method', 'update')
            ->first()->id ?? 0;

        $data = (new MyModel())->hakuser($userid, $acoid);
        if ($data == true) {
            $hakutama = 1;
        } else {
            $hakutama = 0;
        }

        $tempapproval = '##tempapproval' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempapproval, function ($table) {
            $table->string('namasupir', 100)->nullable();
            $table->longText('noktp', 100)->nullable();
        });

        $statusApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first()->id ?? 0;

        $queryapproval = db::table('approvalsupirketerangan')->from(db::raw("approvalsupirketerangan a with (readuncommitted)"))
            ->select(
                'a.namasupir',
                'a.noktp'
            )
            ->whereraw("cast(format(getdate(),'yyyy/MM/dd') as datetime)<=a.tglbatas")
            ->where('a.statusapproval', $statusApproval)
            ->orderby('a.namasupir', 'asc');

        DB::table($tempapproval)->insertUsing([
            'namasupir',
            'noktp',
        ],  $queryapproval);


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
                'mandor.namamandor as mandor',
                'supir.mandor_id',
                'param_statusaktif.text as statusaktifnama',
                'param_statuspostingtnl.text as statuspostingtnlnama',
                'supir.modifiedby',
                'supir.created_at',
                'supir.updated_at',
                db::raw("(case when " . $hakutama . "=1 or isnull(c.noktp,'')<>'' then '' else 'readonly' end) as noktp_readonly"),
                db::raw("(case when " . $hakutama . "=1 or isnull(c.noktp,'')<>'' then '' else 'readonly' end) as namasupir_readonly"),
                db::raw("(case when " . $hakutama . "=1 or isnull(c.noktp,'')<>'' then '' else 'readonly' end) as tgllahir_readonly"),
                db::raw("(case when " . $hakutama . "=1 or isnull(c.noktp,'')<>'' then '' else 'readonly' end) as alamat_readonly"),
                db::raw("(case when " . $hakutama . "=1 or isnull(c.noktp,'')<>'' then '' else 'readonly' end) as kota_readonly"),
                db::raw("(case when " . $hakutama . "=1 or isnull(c.noktp,'')<>'' then '' else 'readonly' end) as nokk_readonly"),
                db::raw("(case when " . $hakutama . "=1 or isnull(c.noktp,'')<>'' then '' else 'readonly' end) as tglmasuk_readonly"),
                db::raw("(case when " . $hakutama . "=1 or isnull(c.noktp,'')<>'' then '' else 'readonly' end) as statusaktif_readonly"),
                db::raw("(case when " . $hakutama . "=1 or isnull(c.noktp,'')<>'' then '' else 'readonly' end) as keterangan_readonly"),
                db::raw("(case when " . $hakutama . "=1 or isnull(c.noktp,'')<>'' then '' else 'readonly' end) as photodomisili_readonly"),
                db::raw("(case when " . $hakutama . "=1 or isnull(c.noktp,'')<>'' then '' else 'readonly' end) as photokk_readonly"),
                db::raw("(case when " . $hakutama . "=1 or isnull(c.noktp,'')<>'' then '' else 'readonly' end) as photoktp_readonly"),
                db::raw("(case when " . $hakutama . "=1 or isnull(c.noktp,'')<>'' then '' else 'readonly' end) as photosim_readonly"),
                db::raw("(case when " . $hakutama . "=1 or isnull(c.noktp,'')<>'' then '' else 'readonly' end) as photoskck_readonly"),
                db::raw("(case when " . $hakutama . "=1 or isnull(c.noktp,'')<>'' then '' else 'readonly' end) as photosupir_readonly"),
                db::raw("(case when " . $hakutama . "=1 or isnull(c.noktp,'')<>'' then '' else 'readonly' end) as photovaksin_readonly"),

                db::raw("isnull(parameter_statusapprovalhistorymilikmandor.memo,'" . $defaultmemononapproval . "')  as statusapprovalhistorysupirmilikmandor"),
                'supir.userapprovalhistorysupirmilikmandor as userapprovalhistorysupirmilikmandor',
                'supir.tglapprovalhistorysupirmilikmandor as tglapprovalhistorysupirmilikmandor',
                'supir.tglupdatehistorysupirmilikmandor as tglupdatehistorysupirmilikmandor',
                'supir.tglberlakumilikmandor as tglberlakumilikmandor',

            )
            ->leftJoin(DB::raw("parameter as param_statusaktif with (readuncommitted)"), 'supir.statusaktif', 'param_statusaktif.id')
            ->leftJoin(DB::raw("parameter as param_statuspostingtnl with (readuncommitted)"), 'supir.statuspostingtnl', 'param_statuspostingtnl.id')
            ->leftJoin(DB::raw("zona with (readuncommitted)"), 'supir.zona_id', 'zona.id')
            ->leftJoin(DB::raw("mandor with (readuncommitted)"), 'supir.mandor_id', 'mandor.id')
            ->leftJoin(DB::raw("supir as supirlama with (readuncommitted)"), 'supir.supirold_id', '=', 'supirlama.id')
            ->leftJoin(DB::raw("parameter as parameter_statusapprovalhistorymilikmandor with (readuncommitted)"), 'supir.statusapprovalhistorysupirmilikmandor', 'parameter_statusapprovalhistorymilikmandor.id')
            ->leftjoin(DB::raw($tempapproval . " as c"), function ($join) {
                $join->on('supir.namasupir', '=', 'c.namasupir');
                $join->on('supir.noktp', '=', 'c.noktp');
            })
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
               (case when (year(isnull(supir.tglbatastidakbolehluarkota,'1900-01-01')) <= 2000) then null else supir.tglbatastidakbolehluarkota end ) as tglbatastidakbolehluarkota,
                $this->table.keterangantidakbolehluarkota,
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
                parameter_statusapprovalhistorymilikmandor.memo as statusapprovalhistorysupirmiliksupir,
                $this->table.userapprovalhistorysupirmilikmandor as userapprovalhistorysupirmilikmandor,
                $this->table.tglapprovalhistorysupirmilikmandor as tglapprovalhistorysupirmilikmandor,
                $this->table.tglupdatehistorysupirmilikmandor as tglupdatehistorysupirmilikmandor,                
                $this->table.tglberlakumilikmandor as tglberlakumilikmandor,
                'statusapproval.memo as statusapproval',
                $this->table.modifiedby,
                $this->table.created_at,
                $this->table.updated_at"
            )

        )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'supir.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statusluarkota with (readuncommitted)"), 'supir.statusluarkota', '=', 'statusluarkota.id')
            ->leftJoin(DB::raw("parameter as statusblacklist with (readuncommitted)"), 'supir.statusblacklist', '=', 'statusblacklist.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'supir.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("parameter as statuspostingtnl with (readuncommitted)"), 'supir.statuspostingtnl', '=', 'statuspostingtnl.id')
            ->leftJoin(DB::raw("parameter as parameter_statusapprovalhistorymilikmandor with (readuncommitted)"), 'supir.statusapprovalhistorysupirmilikmandor', 'parameter_statusapprovalhistorymilikmandor.id')

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
            $table->date('tglbatastidakbolehluarkota')->nullable();
            $table->longText('keterangantidakbolehluarkota')->nullable();
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
            $table->longtext('statusapprovalhistorysupirmilikmandor')->nullable();
            $table->string('userapprovalhistorysupirmilikmandor', 50)->nullable();
            $table->datetime('tglapprovalhistorysupirmilikmandor')->nullable();
            $table->datetime('tglupdatehistorysupirmilikmandor')->nullable();
            $table->datetime('tglberlakumilikmandor')->nullable();
            $table->longtext('statusapproval')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        // dd($query->get());
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
            'tglbatastidakbolehluarkota',
            'keterangantidakbolehluarkota',
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
            'statusapprovalhistorysupirmilikmandor',
            'userapprovalhistorysupirmilikmandor',
            'tglapprovalhistorysupirmilikmandor',
            'tglupdatehistorysupirmilikmandor',
            'tglberlakumilikmandor',
            'statusapproval',
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
                        } else if ($filters['field'] == 'statusapprovalhistorysupirmilikmandor') {
                            $query = $query->where('parameter_statusapprovalhistorymilikmandor.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('statusapproval.text', '=', $filters['data']);
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
                        } else if ($filters['field'] == 'statusapprovalhistorysupirmilikmandor') {
                            $query = $query->where('parameter_statusapprovalhistorysupirmilikmandor.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'mandor_id') {

                            $query = $query->where('b.namamandor', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tgllahir' || $filters['field'] == 'tglterbitsim' || $filters['field'] == 'tglexpsim' || $filters['field'] == 'tglmasuk' || $filters['field'] == 'tglberhentisupir' || $filters['field'] == 'tglbatastidakbolehluarkota') {
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
                            } else if ($filters['field'] == 'statusapprovalhistorysupirmilikmandor') {
                                $query = $query->orwhere('parameter_statusapprovalhistorymilikmandor.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'statusapproval') {
                                $query = $query->orwhere('statusapproval.text', '=', $filters['data']);
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
                            } else if ($filters['field'] == 'statusapprovalhistorysupirmilikmandor') {
                                $query = $query->orwhere('parameter_statusapprovalhistorysupirmilikmandor.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'supirold_id') {
                                $query = $query->orWhere('supirlama.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tgllahir' || $filters['field'] == 'tglterbitsim' || $filters['field'] == 'tglexpsim' || $filters['field'] == 'tglmasuk' || $filters['field'] == 'tglberhentisupir' || $filters['field'] == 'tglbatastidakbolehluarkota') {
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
            $storedFile = Storage::disk('toTnl')->put( 'supir/'. $destinationFolder . '/' . $originalFileName, $pdfData);
            // $storedFile = Storage::put('supir/' . $destinationFolder . '/' . $originalFileName, $pdfData);
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

            $storedFile = Storage::disk('toTnl')->putFileAs('supir/' . $destinationFolder, $file, $originalFileName);
            $pathDestination = Storage::disk('toTnl')->getDriver()->getAdapter()->applyPathPrefix(null);
            $resizedFiles = App::imageResize($pathDestination.'supir/'.$destinationFolder.'/', $pathDestination.'supir/'.$destinationFolder.'/'.$originalFileName, $originalFileName);

            // $pathDestination.$destinationFolder.'/', $pathDestination.$destinationFolder.'/'.$originalFileName, $originalFileName
            // $storedFile = Storage::disk('toTnl')->put( 'supir/'.$destinationFolder . '/' . $originalFileName, $imageData);
            // $pathDestination = Storage::disk('toTnl')->getDriver()->getAdapter()->applyPathPrefix(null);
            // $resizedFiles = App::imageResize($pathDestination.'supir/'.$destinationFolder.'/', $pathDestination.'supir/'.$destinationFolder.'/'.$originalFileName, $originalFileName);

            // $storedFile = Storage::put('supir/' . $destinationFolder . '/' . $originalFileName, $imageData);
            // $resizedFiles = App::imageResize(storage_path("app/supir/$destinationFolder/"), storage_path("app/supir/$destinationFolder/$originalFileName"), $originalFileName);

            $storedFiles[] = $originalFileName;
        }

        return json_encode($storedFiles);
    }

    public function processStore(array $data, Supir $supir): Supir
    {
        try {
            $statusAdaUpdateGambar = DB::table('parameter')->where('grp', 'STATUS ADA UPDATE GAMBAR')->where('default', 'YA')->first();
            $statusApprovalDefault = DB::table('parameter')->where('grp', 'STATUS APPROVAL')->where('default', 'YA')->first();
            $statusLuarKota = DB::table('parameter')->where('grp', 'STATUS LUAR KOTA')->where('default', 'YA')->first();
            $statusZonaTertentu = DB::table('parameter')->where('grp', 'ZONA TERTENTU')->where('default', 'YA')->first();
            $statusBlackList = DB::table('parameter')->where('grp', 'BLACKLIST SUPIR')->where('default', 'YA')->first();
            $cabang = DB::table('parameter')->where('grp', 'CABANG')->where('subgrp', 'CABANG')->first();
            $batasBulan = DB::table('parameter')->where('grp', 'BATAS BULAN SUPIR BARU LUAR KOTA')->where('subgrp', 'BATAS BULAN SUPIR BARU LUAR KOTA')->first();
            $tglmasuk = date('Y-m-d', strtotime($data['tglmasuk']));
            $isBolehLuarKota = DB::table("parameter")->where('grp', 'VALIDASI SUPIR')->where('subgrp', 'BOLEH LUAR KOTA')->first()->text ?? 'TIDAK';
            if ($isBolehLuarKota == 'YA') {
                $tglBatasLuarKota = null;
            } else {
                $tglBatasLuarKota = (date('Y-m-d', strtotime("+$batasBulan->text months", strtotime($tglmasuk))));
            }

            if ($cabang->text != "SURABAYA") {
                $statusApprovalDefault = DB::table('parameter')->where('grp', 'STATUS APPROVAL')->where('subgrp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
            }

            $supirlama_id = request()->id ?? null;
            $isMandor = auth()->user()->isMandor();
            $userid = auth('api')->user()->id;
            if ($isMandor) {
                // $data['mandor_id'] = $isMandor->mandor_id;
                if ($isMandor) {

                    $temp1 = '##temp1' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                    Schema::create($temp1, function ($table) {
                        $table->id();
                        $table->integer('mandor_id')->nullable();
                    });

                    $query1 = db::table('mandor')->from(db::raw("mandor a with (readuncommitted)"))
                        ->select(
                            'a.id',
                        )
                        ->join(db::raw("mandordetail b with (readuncommitted)"), 'a.id', 'b.mandor_id')
                        ->where('b.user_id', $userid)
                        ->groupby('a.id');

                    DB::table($temp1)->insertUsing([
                        'mandor_id',
                    ], $query1);

                    $temp2 = '##temp2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                    Schema::create($temp2, function ($table) {
                        $table->id();
                        $table->integer('mandor_id')->nullable();
                        $table->integer('jumlah')->nullable();
                    });

                    $query2 = db::table('mandordetail')->from(db::raw("mandordetail a with (readuncommitted)"))
                        ->select(
                            'a.mandor_id',
                            db::raw("count(a.id) as jumlah")
                        )
                        ->join(db::raw($temp1 . " b "), 'a.mandor_id', 'b.mandor_id')
                        ->groupby('a.mandor_id');

                    DB::table($temp2)->insertUsing([
                        'mandor_id',
                        'jumlah',
                    ], $query2);

                    $queryidmandor = db::table($temp2)->from(db::raw($temp2 . " a"))
                        ->select(
                            'a.mandor_id'
                        )
                        ->orderby('a.jumlah', 'asc')
                        ->orderby('a.mandor_id', 'asc')
                        ->first();
                    $data['mandor_id'] = $queryidmandor->mandor_id ?? 0;
                }
            }
            // $supir = new Supir();
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
            $supir->mandor_id = $data['mandor_id'] ?? '';
            $supir->angsuranpinjaman = str_replace(',', '', $data['angsuranpinjaman']) ?? 0;
            $supir->plafondeposito = str_replace(',', '', $data['plafondeposito']) ?? 0;
            $supir->tgllahir = date('Y-m-d', strtotime($data['tgllahir']));
            $supir->tglterbitsim = date('Y-m-d', strtotime($data['tglterbitsim']));
            $supir->statusapproval = $statusApprovalDefault->id;
            $supir->statuspostingtnl = $data['statuspostingtnl'];
            $supir->tglberhentisupir = date('Y-m-d', strtotime("1900-01-01"));
            $supir->modifiedby = auth('api')->user()->user;
            $supir->supirlama_id = $supirlama_id;
            $supir->info = html_entity_decode(request()->info);
            $supir->tas_id = $data['tas_id'] ?? '';

            if ($data['mandor_id'] != 0) {
                $supir->tglberlakumilikmandor = date('Y-m-d');
            }
            $supir->statusadaupdategambar = $statusAdaUpdateGambar->id;
            $supir->statusluarkota = $statusLuarKota->id;
            $supir->tglbatastidakbolehluarkota = $tglBatasLuarKota;
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
                    if ($getPosting->nominal != '') {

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
                    if ($getNonPosting->nominal != '') {

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
            $oldTglMasuk = $supir->tglmasuk;
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
            // $supir->mandor_id = $data['mandor_id']?? '';
            $supir->angsuranpinjaman = str_replace(',', '', $data['angsuranpinjaman']) ?? 0;
            $supir->plafondeposito = str_replace(',', '', $data['plafondeposito']) ?? 0;
            $supir->tgllahir = date('Y-m-d', strtotime($data['tgllahir']));
            $supir->tglterbitsim = date('Y-m-d', strtotime($data['tglterbitsim']));
            $supir->modifiedby = auth('api')->user()->name;
            $supir->info = html_entity_decode(request()->info);

            $this->deleteFiles($supir);

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

            if ($oldTglMasuk != date('Y-m-d', strtotime($data['tglmasuk']))) {
                $isBolehLuarKota = DB::table("parameter")->where('grp', 'VALIDASI SUPIR')->where('subgrp', 'BOLEH LUAR KOTA')->first()->text ?? 'TIDAK';
                if ($isBolehLuarKota != 'YA') {


                    $batasBulan = DB::table('parameter')->where('grp', 'BATAS BULAN SUPIR BARU LUAR KOTA')->where('subgrp', 'BATAS BULAN SUPIR BARU LUAR KOTA')->first();
                    $tglmasuk = date('Y-m-d', strtotime($data['tglmasuk']));
                    $tglBatasLuarKota = (date('Y-m-d', strtotime("+$batasBulan->text months", strtotime($tglmasuk))));

                    $supir->tglbatastidakbolehluarkota = $tglBatasLuarKota;
                }
            }

            if (!$supir->save()) {
                throw new \Exception("Error storing supir.");
            }

            // $approvalSupirGambar = ApprovalSupirGambar::where('noktp', $supir->noktp)->first();
            // if ($approvalSupirGambar) {
            //     $nonApp = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->whereRaw("grp like '%STATUS APPROVAL%'")->whereRaw("text like '%NON APPROVAL%'")->first();
            //     $approvalSupirGambar->statusapproval = $nonApp->id;
            //     $approvalSupirGambar->save();
            // }

            // $approvalSupirKeterangan = ApprovalSupirKeterangan::where('noktp', $supir->noktp)->first();
            // if ($approvalSupirKeterangan) {
            //     $nonApp = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->whereRaw("grp like '%STATUS APPROVAL%'")->whereRaw("text like '%NON APPROVAL%'")->first();
            //     $approvalSupirKeterangan->statusapproval = $nonApp->id;
            //     $approvalSupirKeterangan->save();
            // }

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

    public function processDestroy(Supir $supir): Supir
    {
        // $supir = new Supir();
        $supir = $supir->lockAndDestroy($supir->id);

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
                'password' => config('app.password_tnl'),
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
            if ($photoSupir != '') {
                foreach ($photoSupir as $imagePath) {
                    $supirBase64[] = base64_encode(file_get_contents(storage_path("app/supir/profil/" . $imagePath)));
                }
                $data['photosupir'] = $supirBase64;
            }
            if ($photoKtp != '') {
                foreach ($photoKtp as $imagePath) {
                    $ktpBase64[] = base64_encode(file_get_contents(storage_path("app/supir/ktp/" . $imagePath)));
                }
                $data['photoktp'] = $ktpBase64;
            }
            if ($photoSim != '') {
                foreach ($photoSim as $imagePath) {
                    $simBase64[] = base64_encode(file_get_contents(storage_path("app/supir/sim/" . $imagePath)));
                }
                $data['photosim'] = $simBase64;
            }
            if ($photoKk != '') {
                foreach ($photoKk as $imagePath) {
                    $kkBase64[] = base64_encode(file_get_contents(storage_path("app/supir/kk/" . $imagePath)));
                }
                $data['photokk'] = $kkBase64;
            }
            if ($photoSkck != '') {
                foreach ($photoSkck as $imagePath) {
                    $skckBase64[] = base64_encode(file_get_contents(storage_path("app/supir/skck/" . $imagePath)));
                }
                $data['photoskck'] = $skckBase64;
            }
            if ($photoDomisili != '') {
                foreach ($photoDomisili as $imagePath) {
                    $domisiliBase64[] = base64_encode(file_get_contents(storage_path("app/supir/domisili/" . $imagePath)));
                }
                $data['photodomisili'] = $domisiliBase64;
            }
            if ($photoVaksin != '') {
                foreach ($photoVaksin as $imagePath) {
                    $vaksinBase64[] = base64_encode(file_get_contents(storage_path("app/supir/vaksin/" . $imagePath)));
                }
                $data['photovaksin'] = $vaksinBase64;
            }
            if ($photoPDF != '') {
                foreach ($photoPDF as $imagePath) {
                    $pdfBase64[] = base64_encode(file_get_contents(storage_path("app/supir/suratperjanjian/" . $imagePath)));
                }
                $data['pdfsuratperjanjian'] = $pdfBase64;
            }
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
            ->select('pemutihansupirheader.nobukti','pemutihansupirheader.supir_id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'pemutihansupirheader.supir_id', 'supir.id')
            ->where('supir.noktp', $noktp)
            ->orderBy('pemutihansupirheader.id', 'desc')
            ->first();
        $nobuktiPemutihan = '';
        if ($getPemutihan != '') {
            $nobuktiPemutihan = $getPemutihan->nobukti;
        }
        $parameter = new Parameter();
        $statusNonAktif = $parameter->cekId('STATUS AKTIF', 'STATUS AKTIF','NON AKTIF');

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
           $table->string('nobukti');
           $table->bigInteger('supir_id')->nullable();       
           $table->double('sisa',15,2)->nullable(); 
        });
        $fetch = DB::table("pengeluarantruckingdetail")->from(db::raw("pengeluarantruckingdetail with (readuncommitted)"))
            ->select(DB::raw("pengeluarantruckingdetail.nobukti, pengeluarantruckingdetail.supir_id, (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa"))
            ->join(db::raw("supir with (readuncommitted)"), 'pengeluarantruckingdetail.supir_id', 'supir.id')
            ->whereRaw("pengeluarantruckingdetail.nobukti like '%PJT%' and supir.noktp='$noktp'")
            ->groupBy('pengeluarantruckingdetail.nobukti','pengeluarantruckingdetail.nominal','pengeluarantruckingdetail.supir_id');
        $tes = DB::table($temp)->insertUsing(['nobukti', 'supir_id', 'sisa',], $fetch);


        $query = DB::table('supir')->from(DB::raw("supir with (readuncommitted)"))
            ->select(
                'supir.id',
                'supir.namasupir',
                'supir.alamat',
                'supir.kota',
                'supir.telp',
                // DB::raw($statusNonAktif." as statusaktif"),
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
                DB::raw(" '' as pdfsuratperjanjian"),
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
            ->leftJoin(DB::raw("supir as supirlama with (readuncommitted)"), 'supir.supirlama_id', '=', 'supirlama.id');
            if ($getPemutihan == '') {
                $query->Join(db::raw("$temp as b with (readuncommitted)"), 'supir.id', 'b.supir_id')
                ->whereRaw("isnull(b.sisa,0) != 0");
            }else{
                $query->where('supir.id', $getPemutihan->supir_id);
            }
            $query->orderBy('supir.id', 'desc');

        return $query->first();
    }

    public function validationSupirResign($noktp, $id = 0)
    {
        $query = DB::table("supir")->from(DB::raw("supir with (readuncommitted)"))
            ->where("noktp", $noktp)
            ->where("statusaktif", 1)
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
        $statusNonApp = DB::table('parameter')->where('grp', 'STATUS APPROVAL')->where('subgrp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

        $mandorbaru = $data['mandorbaru_id'] ?? 0;
        $mandorlama = $data['mandor_id'] ?? 0;

        if ($mandorbaru == 0) {
            $data['mandorbaru_id'] = $mandorlama;
        }

        $supir = Supir::findOrFail($data['id']);
        $supir->mandor_id = $data['mandorbaru_id'];
        $supir->tglberlakumilikmandor = date('Y-m-d', strtotime($data['tglberlaku']));
        $supir->tglupdatehistorysupirmilikmandor = date('Y-m-d H:i:s');
        $supir->statusapprovalhistorysupirmilikmandor = $statusNonApp->id;


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
                    'postingdari' => 'APPROVAL NON AKTIF SUPIR',
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

    public function processApprovalaktif(array $data)
    {

        $statusaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $Supir = Supir::find($data['Id'][$i]);

            $Supir->statusaktif = $statusaktif->id;
            $aksi = $statusaktif->text;

            // dd($Supir);
            if ($Supir->save()) {

                (new LogTrail())->processStore([

                    'namatabel' => strtoupper($Supir->getTable()),
                    'postingdari' => 'APPROVAL AKTIF SUPIR',
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

    public function processApprovalHistoryTradoMilikMandor(array $data)
    {
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        $jambatas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', '=', 'JAMBATASAPPROVAL')->where('subgrp', '=', 'JAMBATASAPPROVAL')->first();
        $tglbatas = date('Y-m-d') . ' ' . $jambatas->text ?? '00:00:00';
        for ($i = 0; $i < count($data['Id']); $i++) {

            $supir = Supir::find($data['Id'][$i]);
            if ($supir->statusapprovalhistorysupirmilikmandor == $statusApproval->id) {
                $supir->statusapprovalhistorysupirmilikmandor = $statusNonApproval->id;
                $supir->tglapprovalhistorysupirmilikmandor = '';
                $supir->userapprovalhistorysupirmilikmandor = '';
                $aksi = $statusNonApproval->text;
            } else {
                $supir->statusapprovalhistorysupirmilikmandor = $statusApproval->id;
                $supir->tglapprovalhistorysupirmilikmandor = date('Y-m-d H:i:s');
                $supir->userapprovalhistorysupirmilikmandor = auth('api')->user()->name;
                $aksi = $statusApproval->text;
            }

            $supir->tglapprovalhistorysupirmilikmandor = date('Y-m-d H:i:s');
            $supir->userapprovalhistorysupirmilikmandor = auth('api')->user()->name;
            $supir->info = html_entity_decode(request()->info);

            if (!$supir->save()) {
                throw new \Exception('Error Un/approval History Supir Milik Mandor.');
            }

            (new LogTrail())->processStore([
                'namatabel' => strtoupper($supir->getTable()),
                'postingdari' => "UN/APPROVAL History Supir Milik Mandor",
                'idtrans' => $supir->id,
                'nobuktitrans' => $supir->nobukti,
                'aksi' => $aksi,
                'datajson' => $supir->toArray(),
                'modifiedby' => auth('api')->user()->name,
            ]);
            $result[] = $supir;
        }

        return $result;
    }

    public function processApprovalSupirLuarKota(array $data)
    {
        $supir = Supir::find($data['id']);
        $tglbataslama = $supir->tglbatastidakbolehluarkota;
        $supir->statusluarkota = $data['statusluarkota'];
        $supir->keterangantidakbolehluarkota = $data['keterangan'];
        $supir->tglbatastidakbolehluarkota = date('Y-m-d', strtotime($data['tglbatas']));
        $supir->save();

        $dataHistory = [
            'supir_id' => $data['id'],
            'tglbataslama' => $tglbataslama,
            'tglbatasbaru' => date('Y-m-d', strtotime($data['tglbatas'])),
            'statusluarkota' => $data['statusluarkota']
        ];

        (new HistoryTglBatasLuarKota())->processStore($dataHistory);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($supir->getTable()),
            'postingdari' => 'UN/APPROVAL SUPIR LUAR KOTA',
            'idtrans' => $supir->id,
            'nobuktitrans' => $supir->id,
            'aksi' => 'UN/APPROVAL',
            'datajson' => $supir->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

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
                $supir->statusaktif = 1;
                $aksi = $statusBukanBlackList->text;
            } else {
                $supir->statusblacklist = $statusBlackList->id;
                $supir->statusaktif = 2;
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


    public function processApproval(array $data)
    {

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $supir = Supir::find($data['Id'][$i]);

            if ($supir->statusapproval == $statusApproval->id) {
                $supir->statusapproval = $statusNonApproval->id;
                $aksi = $statusNonApproval->text;
            } else {
                $supir->statusapproval = $statusApproval->id;
                $aksi = $statusApproval->text;
            }

            $supir->tglapproval = date('Y-m-d', time());
            $supir->userapproval = auth('api')->user()->name;
            if ($supir->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($supir->getTable()),
                    'postingdari' => 'APPROVAL supir',
                    'idtrans' => $supir->id,
                    'nobuktitrans' => $supir->id,
                    'aksi' => $aksi,
                    'datajson' => $supir->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        
        return $supir;
    }

    public function getApprovalLuarKota($id)
    {
        $query = DB::table("supir")->from(DB::raw("supir with (readuncommitted)"))
            ->select('id', 'id as supir_id', 'noktp', 'namasupir', 'statusluarkota', 'keterangantidakbolehluarkota as keterangan', DB::raw("(case when (year(isnull(supir.tglbatastidakbolehluarkota,'1900-01-01')) <= 2000) then null else supir.tglbatastidakbolehluarkota end ) as tglbatas"))
            ->where('id', $id)
            ->first();
        return $query;
    }

    public function RefreshSupirNonAktif()
    {

        $date = date('Y-m-d');
        $statusApp = DB::table('parameter')->where('grp', 'STATUS APPROVAL')->where('subgrp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusNonApp = DB::table('parameter')->where('grp', 'STATUS APPROVAL')->where('subgrp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

        $statusAktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'AKTIF')->first();
        $statusNonAktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'NON AKTIF')->first();

        $tempapprovalsupirketerangan = '##tempapprovalsupirketerangan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempapprovalsupirketerangan, function ($table) {
            $table->string('namasupir', 50)->nullable();
            $table->string('noktp', 50)->nullable();
        });

        $queryapprovalsupirketerangan = db::table('approvalsupirketerangan')->from(db::raw("approvalsupirketerangan a with (readuncommitted)"))
            ->select(
                'a.namasupir',
                'a.noktp'
            )
            ->whereRaw("a.tglbatas<'" . $date . "'")
            ->orderby('a.namasupir', 'asc')
            ->orderby('a.noktp', 'asc');



        DB::table($tempapprovalsupirketerangan)->insertUsing([
            'namasupir',
            'noktp',
        ],  $queryapprovalsupirketerangan);

        $queryapprovalsupirgambar = db::table('approvalsupirgambar')->from(db::raw("approvalsupirgambar a with (readuncommitted)"))
            ->select(
                'a.namasupir',
                'a.noktp'
            )
            ->whereRaw("a.tglbatas<'" . $date . "'")
            ->orderby('a.namasupir', 'asc')
            ->orderby('a.noktp', 'asc');
        // dd('test');

        DB::table($tempapprovalsupirketerangan)->insertUsing([
            'namasupir',
            'noktp',
        ],  $queryapprovalsupirgambar);

        $tempapprovalsupirrekap = '##tempapprovalsupirrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempapprovalsupirrekap, function ($table) {
            $table->string('namasupir', 50)->nullable();
            $table->string('noktp', 50)->nullable();
        });

        $queryapprovalsupirgambar = db::table($tempapprovalsupirketerangan)->from(db::raw($tempapprovalsupirketerangan . " a "))
            ->select(
                'a.namasupir',
                'a.noktp'
            )
            ->groupby('a.namasupir')
            ->groupby('a.noktp');


        DB::table($tempapprovalsupirrekap)->insertUsing([
            'namasupir',
            'noktp',
        ],  $queryapprovalsupirgambar);

        // dd(db::table($tempapprovalsupirrekap)->get());
        // dd($statusApp->id);

        $supir1 = DB::table('supir')->from(DB::raw("supir a with (readuncommitted)"))
            ->join(DB::raw($tempapprovalsupirrekap . ' b'), function ($join) {
                $join->on('a.namasupir', '=', 'b.namasupir');
                $join->on('a.noktp', '=', 'b.noktp');
            })
            ->where('a.statusaktif', $statusAktif->id)
            ->get();



        $tempsupirketerangan = '##tempsupirketerangan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsupirketerangan, function ($table) {
            $table->integer('supir_id')->nullable();
            $table->integer('statusapprovalketerangan')->nullable();
        });

        $tempsupirgambar = '##tempsupirgambar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsupirgambar, function ($table) {
            $table->integer('supir_id')->nullable();
            $table->integer('statusapprovalgambar')->nullable();
        });

        $datadetail = json_decode($supir1, true);
        foreach ($datadetail as $supir) {
            $photosupir = true;
            $photoktp = true;
            $photosim = true;
            $photokk = true;
            $pdfsuratperjanjian = true;

           
            if (!is_null(json_decode($supir['photosupir']))) {
                foreach (json_decode($supir['photosupir']) as $value) {
                    if ($value != '') {
                        if (!Storage::exists("supir/profil/$value")) {
                            $photosupir = false;
                            goto selesai1;
                        }
                    } else {
                        $photosupir = false;
                        goto selesai1;
                    }
                }
            } else {
                $photosupir = false;
            }
            
            selesai1:
            if (!is_null(json_decode($supir['photoktp']))) {
                foreach (json_decode($supir['photoktp']) as $value) {
                    if ($value != '') {
                        if (!Storage::exists("supir/ktp/$value")) {
                            $photoktp = false;
                            goto selesai2;
                        }
                    } else {
                        $photoktp = false;
                        goto selesai2;
                    }
                }
            } else {
                $photoktp = false;
            }


            selesai2:
            if (!is_null(json_decode($supir['photosim']))) {
                foreach (json_decode($supir['photosim']) as $value) {
                    if ($value != '') {
                        if (!Storage::exists("supir/sim/$value")) {
                            $photosim = false;
                            goto selesai3;
                        }
                    } else {
                        $photosim = false;
                        goto selesai3;
                    }
                }
            } else {
                $photosim = false;
            }

            selesai3:

            if (!is_null(json_decode($supir['photokk']))) {
                foreach (json_decode($supir['photokk']) as $value) {
                    if ($value != '') {
                        if (!Storage::exists("supir/kk/$value")) {
                            $photokk = false;
                            goto selesai5;
                        }
                    } else {
                        $photokk = false;
                        goto selesai5;
                    }
                }
            } else {
                $photokk = false;
            }


            selesai5:

            if (!is_null(json_decode($supir['pdfsuratperjanjian']))) {
                foreach (json_decode($supir['pdfsuratperjanjian']) as $value) {
                    if ($value != '') {
                        if (!Storage::exists("supir/SURATPERJANJIAN/$value")) {
                            $pdfsuratperjanjian = false;
                            goto selesai6;
                        }
                    } else {
                        $pdfsuratperjanjian = false;
                        goto selesai6;
                    }
                }
            } else {
                $pdfsuratperjanjian = false;
            }

            selesai6:


            $querygambar = db::table('approvalsupirgambar')->from(db::raw("approvalsupirgambar a with (readuncommitted)"))
                ->select(
                    'a.id'
                )
                ->whereRaw("a.tglbatas <='" . $date . "'")
                ->where('a.namasupir', $supir['namasupir'])
                ->where('a.noktp', $supir['noktp'])
                ->where('a.statusapproval', $statusApp->id)
                ->first();



            if ((!$photosupir) || (!$photokk) || (!$photoktp) || (!$photosim) || (!$pdfsuratperjanjian) ) {
                if (isset($querygambar)) {

                    DB::table($tempsupirgambar)->insert([
                        'supir_id' => $supir['id'],
                        'statusapprovalgambar' =>  $statusNonApp->id,
                    ]);
                }
            }
            $required = [
                "namasupir" => $supir['namasupir'],
                "alamat" => $supir['alamat'],
                "namaalias" => $supir['namaalias'],
                "kota" => $supir['kota'],
                "telp" => $supir['telp'],
                "nosim" => $supir['nosim'],
                "noktp" => $supir['noktp'],
                "nokk" => $supir['nokk'],
                "tgllahir" => $supir['tgllahir'],
            ];
            $key = array_keys($required, null);

            $jumlah = count($key);

            $queryketerangan = db::table('approvalsupirketerangan')->from(db::raw("approvalsupirketerangan a with (readuncommitted)"))
                ->select(
                    'a.id'
                )
                ->whereRaw("a.tglbatas<'" . $date . "'")
                ->where('a.namasupir', $supir['namasupir'])
                ->where('a.noktp', $supir['noktp'])
                ->where('a.statusapproval', $statusApp->id)
                ->first();

            if ($jumlah != 0) {
                if (isset($queryketerangan)) {

                    DB::table($tempsupirketerangan)->insert([
                        'supir_id' => $supir['id'],
                        'statusapprovalketerangan' =>  $statusNonApp->id,
                    ]);
                }
            }
        }

        // dd('test');

        $query1 = db::table($tempsupirgambar)->from(db::raw($tempsupirgambar . " a"))
            ->select('a.supir_id')
            ->orderby('a.supir_id', 'asc')
            ->first();

        $query2 = db::table($tempsupirketerangan)->from(db::raw($tempsupirketerangan . " a"))
            ->select('a.supir_id')
            ->orderby('a.supir_id', 'asc')
            ->first();
        // 

        if (isset($query1)) {
            DB::table('supir')
                ->from(db::raw("supir"))
                ->join(db::raw($tempsupirgambar . " b"), 'supir.id', 'b.supir_id')
                ->update([
                    'statusaktif' => $statusNonAktif->id,
                ]);
        } else {
            if (isset($query2)) {
                DB::table('supir')
                    ->from(db::raw("supir"))
                    ->join(db::raw($tempsupirketerangan . " b"), 'supir.id', 'b.supir_id')
                    ->update([
                        'statusaktif' => $statusNonAktif->id,
                    ]);
            }
        }
    }

        public function cekdataText($id)
    {
        $query = DB::table('supir')->from(db::raw("supir a with (readuncommitted)"))
            ->select(
                'a.namasupir as keterangan'
            )
            ->where('id', $id)
            ->first();

        $keterangan = $query->keterangan ?? '';

        return $keterangan;
    }
}
