<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Helpers\App;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;

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
        $this->RefreshTradoNonAktif();

        $absensiId = request()->absensiId ?? '';
        $aktif = request()->aktif ?? '';
        $trado_id = request()->trado_id ?? '';
        $supirserap = request()->supirserap ?? false;
        $tglabsensi = date('Y-m-d', strtotime(request()->tglabsensi)) ?? '';
        $cabang = request()->cabang ?? 'TAS';
        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $userid = auth('api')->user()->id;
        // dd($userid);

        $querymandor = db::table("mandordetail")->from(db::raw("mandordetail a with (readuncommitted)"))
            ->select('a.mandor_id')
            ->where('a.user_id', $userid);

        $tempmandordetail = '##tempmandordetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempmandordetail, function ($table) {
            $table->id();
            $table->unsignedBigInteger('mandor_id')->nullable();
        });

        DB::table($tempmandordetail)->insertUsing([
            'mandor_id',
        ],  $querymandor);

        $temptrado = '##temptrado' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temptrado, function ($table) {
            $table->id();
            $table->integer('trado_id')->nullable();
        });


        $isMandor = auth()->user()->isMandor();
        $isAdmin = auth()->user()->isAdmin();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();


        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))

            ->select(
                'trado.id',
                'trado.keterangan',
                'trado.kodetrado',
                'trado.kmawal',
                'trado.kmakhirgantioli',
                DB::raw("(case when year(isnull(trado.tglasuransimati,'1900/1/1'))=1900 then null  else trado.tglasuransimati end) as tglasuransimati"),
                DB::raw("(case when year(isnull(trado.tglspeksimati,'1900/1/1'))=1900 then null  else trado.tglspeksimati end) as tglspeksimati"),
                DB::raw("(case when year(isnull(trado.tglstnkmati,'1900/1/1'))=1900 then null  else trado.tglstnkmati end) as tglstnkmati"),
                'trado.merek',
                'trado.norangka',
                'trado.nomesin',
                'trado.nama',
                'trado.nostnk',
                'trado.alamatstnk',
                'trado.modifiedby',
                'trado.created_at',
                DB::raw("(case when year(isnull(trado.tglserviceopname,'1900/1/1'))=1900 then null else trado.tglserviceopname end) as tglserviceopname"),
                'trado.keteranganprogressstandarisasi',
                DB::raw("(case when year(isnull(trado.tglpajakstnk,'1900/1/1'))=1900 then null else trado.tglpajakstnk end) as tglpajakstnk"),
                DB::raw("(case when year(isnull(trado.tglgantiakiterakhir,'1900/1/1'))=1900 then null else trado.tglgantiakiterakhir end) as tglgantiakiterakhir"),
                'trado.tipe',
                'trado.jenis',
                'trado.isisilinder',
                'trado.warna',
                'trado.jenisbahanbakar',
                'trado.jumlahsumbu',
                'trado.jumlahroda',
                'trado.model',
                'trado.tahun',
                DB::raw("(case when trado.nominalplusborongan IS NULL then 0 else trado.nominalplusborongan end) as nominalplusborongan"),
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
                'parameter_statusabsensisupir.memo as statusabsensisupir',
                'mandor.namamandor as mandor_id',
                'supir.id as supirid',
                'supir.namasupir as supir_id',
                'trado.updated_at',
                DB::raw("'Laporan Trado' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("parameter as parameter_statusaktif with (readuncommitted)"), 'trado.statusaktif', 'parameter_statusaktif.id')
            ->leftJoin(DB::raw("parameter as parameter_statusjenisplat with (readuncommitted)"), 'trado.statusjenisplat', 'parameter_statusjenisplat.id')
            ->leftJoin(DB::raw("parameter as parameter_statusstandarisasi with (readuncommitted)"), 'trado.statusstandarisasi', 'parameter_statusstandarisasi.id')
            ->leftJoin(DB::raw("parameter as parameter_statusmutasi with (readuncommitted)"), 'trado.statusmutasi', 'parameter_statusmutasi.id')
            ->leftJoin(DB::raw("parameter as parameter_statusvalidasikendaraan with (readuncommitted)"), 'trado.statusvalidasikendaraan', 'parameter_statusvalidasikendaraan.id')
            ->leftJoin(DB::raw("parameter as parameter_statusmobilstoring with (readuncommitted)"), 'trado.statusmobilstoring', 'parameter_statusmobilstoring.id')
            ->leftJoin(DB::raw("parameter as parameter_statusappeditban with (readuncommitted)"), 'trado.statusappeditban', 'parameter_statusappeditban.id')
            ->leftJoin(DB::raw("parameter as parameter_statuslewatvalidasi with (readuncommitted)"), 'trado.statuslewatvalidasi', 'parameter_statuslewatvalidasi.id')
            ->leftJoin(DB::raw("parameter as parameter_statusabsensisupir with (readuncommitted)"), 'trado.statusabsensisupir', 'parameter_statusabsensisupir.id')
            ->leftJoin(DB::raw("mandor with (readuncommitted)"), 'trado.mandor_id', 'mandor.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'trado.supir_id', 'supir.id');
        // ->where("trado.id" ,"=","37");

        if (!$isAdmin) {
            if ($isMandor) {
                $query->Join(DB::raw($tempmandordetail . " as mandordetail"), 'trado.mandor_id', 'mandordetail.mandor_id');

                // $query->where('trado.mandor_id', $isMandor->mandor_id);
            }
        }

        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('trado.statusaktif', '=', $statusaktif->id);
        }


        if ($supirserap) {
            $absensiQuery =  DB::table('absensisupirheader')->from(
                DB::raw("absensisupirheader a with (readuncommitted)")
            )->where('tglbukti', $tglabsensi)->first();
            if ($absensiQuery) {
                $absensisupirapprovalheader =  DB::table('absensisupirapprovalheader')->from(
                    DB::raw("absensisupirapprovalheader a with (readuncommitted)")
                )->where('absensisupir_nobukti', $absensiQuery->nobukti)->first();
                if ($absensisupirapprovalheader) {
                    return $query->where('trado.id', 0)->get();
                }
            } else {
                return $query->where('trado.id', 0)->get();
            }

            $absensiId =  $absensiQuery->id ?? '';
        }

        if ($absensiId != '') {
            $querytradoabsen = db::table("absensisupirdetail")->from(db::raw("absensisupirdetail a with (readuncommitted)"))
                ->select('a.trado_id')
                ->where('a.absensi_id', '=', $absensiId)
                ->groupBy('a.trado_id');

            // dd($querytradoabsen ->get());
            DB::table($temptrado)->insertUsing([
                'trado_id',
            ],  $querytradoabsen);


            $query->join(db::raw($temptrado) . ' as absensisupirdetail', 'trado.id', '=', 'absensisupirdetail.trado_id');
        }

        $this->filter($query);

        if ($trado_id != '') {
            $query->where('trado.id', $trado_id);
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function getTNLForKlaim()
    {
        $server = config('app.url_tnl');
        $getToken = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])
            ->post($server . 'token', [
                'user' => 'ADMIN',
                'password' => getenv('PASSWORD_TNL'),
                'ipclient' => '',
                'ipserver' => '',
                'latitude' => '',
                'longitude' => '',
                'browser' => '',
                'os' => '',
            ]);
        $access_token = json_decode($getToken, TRUE)['access_token'];

        $getTrado = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json',
        ])

            ->get($server . "trado?limit=0&aktif=AKTIF");

        $data = $getTrado->json()['data'];
        $class = 'TradoLookupController';
        $user = auth('api')->user()->name;

        $temtabel = 'temptradotnl' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;

        $querydata = DB::table('listtemporarytabel')->from(
            DB::raw("listtemporarytabel a with (readuncommitted)")
        )
            ->select(
                'id',
                'class',
                'namatabel',
            )
            ->where('class', '=', $class)
            ->where('modifiedby', '=', $user)
            ->first();

        if (isset($querydata)) {
            Schema::dropIfExists($querydata->namatabel);
            DB::table('listtemporarytabel')->where('id', $querydata->id)->delete();
        }

        DB::table('listtemporarytabel')->insert(
            [
                'class' => $class,
                'namatabel' => $temtabel,
                'modifiedby' => $user,
                'created_at' => date('Y/m/d H:i:s'),
                'updated_at' => date('Y/m/d H:i:s'),
            ]
        );

        Schema::create($temtabel, function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('kodetrado', 30)->nullable();
            $table->double('kmawal', 15, 2)->nullable();
            $table->double('kmakhirgantioli', 15, 2)->nullable();
            $table->date('tglasuransimati')->nullable();
            $table->date('tglspeksimati')->nullable();
            $table->date('tglstnkmati')->nullable();
            $table->string('merek', 40)->nullable();
            $table->string('norangka', 40)->nullable();
            $table->string('nomesin', 40)->nullable();
            $table->string('nama', 40)->nullable();
            $table->string('nostnk', 50)->nullable();
            $table->longText('alamatstnk')->nullable();
            $table->string('modifiedby', 30)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->date('tglserviceopname')->nullable();
            $table->string('keteranganprogressstandarisasi', 100)->nullable();
            $table->date('tglpajakstnk')->nullable();
            $table->date('tglgantiakiterakhir')->nullable();
            $table->string('tipe', 30)->nullable();
            $table->string('jenis', 30)->nullable();
            $table->integer('isisilinder')->length(11)->nullable();
            $table->string('warna', 30)->nullable();
            $table->string('jenisbahanbakar', 30)->nullable();
            $table->integer('jumlahsumbu')->length(11)->nullable();
            $table->integer('jumlahroda')->length(11)->nullable();
            $table->string('model', 50)->nullable();
            $table->string('tahun', 40)->nullable();
            $table->double('nominalplusborongan', 15, 2)->nullable();
            $table->string('nobpkb', 50)->nullable();
            $table->integer('jumlahbanserap')->length(11)->nullable();
            $table->string('mandor_id', 1500)->nullable();
            $table->string('supir_id', 1500)->nullable();
            $table->string('supirid', 1500)->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        foreach ($data as $row) {
            unset($row['judulLaporan']);
            unset($row['judul']);
            unset($row['tglcetak']);
            unset($row['usercetak']);
            unset($row['photostnk']);
            unset($row['photobpkb']);
            unset($row['phototrado']);
            unset($row['statusaktif']);
            unset($row['statusstandarisasi']);
            unset($row['statusjenisplat']);
            unset($row['statusmutasi']);
            unset($row['statusvalidasikendaraan']);
            unset($row['statusmobilstoring']);
            unset($row['statusappeditban']);
            unset($row['statuslewatvalidasi']);
            unset($row['statusabsensisupir']);
            DB::table($temtabel)->insert($row);
        }

        return $temtabel;
    }
    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->nullable();
            $table->unsignedBigInteger('statusgerobak')->nullable();
            $table->unsignedBigInteger('statusjenisplat')->nullable();
            $table->unsignedBigInteger('statusabsensisupir')->nullable();
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

        // GEROBAK
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS GEROBAK')
            ->where('subgrp', '=', 'STATUS GEROBAK')
            ->where("default", '=', 'YA')
            ->first();

        $iddefaultstatusGerobak = $status->id ?? 0;

        // STATUS ABSENSI SUPIR
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS ABSENSI SUPIR')
            ->where('subgrp', '=', 'STATUS ABSENSI SUPIR')
            ->where("default", '=', 'YA')
            ->first();

        $iddefaultstatusAbsensiSupir = $status->id ?? 0;

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


        DB::table($tempdefault)->insert(
            [
                "statusaktif" => $iddefaultstatusaktif,
                "statusgerobak" => $iddefaultstatusGerobak,
                "statusjenisplat" => $iddefaultstatusJenisPlat,
                "statusabsensisupir" => $iddefaultstatusAbsensiSupir,
            ]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusgerobak',
                'statusjenisplat',
                'statusabsensisupir',
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
                $this->table.kodetrado,            
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

            ->leftJoin(DB::raw("parameter as parameter_statusaktif with (readuncommitted)"), 'trado.statusaktif', 'parameter_statusaktif.id')
            ->leftJoin(DB::raw("parameter as parameter_statusjenisplat with (readuncommitted)"), 'trado.statusjenisplat', 'parameter_statusjenisplat.id')
            ->leftJoin(DB::raw("parameter as parameter_statusstandarisasi with (readuncommitted)"), 'trado.statusstandarisasi', 'parameter_statusstandarisasi.id')
            ->leftJoin(DB::raw("parameter as parameter_statusmutasi with (readuncommitted)"), 'trado.statusmutasi', 'parameter_statusmutasi.id')
            ->leftJoin(DB::raw("parameter as parameter_statusvalidasikendaraan with (readuncommitted)"), 'trado.statusvalidasikendaraan', 'parameter_statusvalidasikendaraan.id')
            ->leftJoin(DB::raw("parameter as parameter_statusmobilstoring with (readuncommitted)"), 'trado.statusmobilstoring', 'parameter_statusmobilstoring.id')
            ->leftJoin(DB::raw("parameter as parameter_statusappeditban with (readuncommitted)"), 'trado.statusappeditban', 'parameter_statusappeditban.id')
            ->leftJoin(DB::raw("parameter as parameter_statuslewatvalidasi with (readuncommitted)"), 'trado.statuslewatvalidasi', 'parameter_statuslewatvalidasi.id')
            ->leftJoin(DB::raw("parameter as parameter_statusabsensisupir with (readuncommitted)"), 'trado.statusabsensisupir', 'parameter_statusabsensisupir.id')
            ->leftJoin(DB::raw("mandor with (readuncommitted)"), 'trado.mandor_id', 'mandor.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'trado.supir_id', 'supir.id');
    }


    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->longText('keterangan')->nullable();
            $table->longText('kodetrado')->nullable();
            $table->string('statusaktif')->nullable();
            $table->double('kmawal', 15, 2)->nullable();
            $table->double('kmakhirgantioli', 15, 2)->nullable();
            $table->date('tglakhirgantioli')->nullable();
            $table->date('tglstnkmati')->nullable();
            $table->date('tglasuransimati')->nullable();
            $table->string('tahun', 40)->nullable();
            $table->string('akhirproduksi', 40)->nullable();
            $table->string('merek', 40)->nullable();
            $table->string('norangka', 40)->nullable();
            $table->string('nomesin', 40)->nullable();
            $table->string('nama', 40)->nullable();
            $table->string('nostnk', 50)->nullable();
            $table->longText('alamatstnk')->nullable();
            $table->date('tglstandarisasi')->nullable();
            $table->date('tglserviceopname')->nullable();
            $table->string('statusstandarisasi')->nullable();
            $table->string('keteranganprogressstandarisasi', 100)->nullable();
            $table->integer('statusjenisplat')->length(11)->nullable();
            $table->date('tglspeksimati')->nullable();
            $table->date('tglpajakstnk')->nullable();
            $table->date('tglgantiakiterakhir')->nullable();
            $table->string('statusmutasi')->nullable();
            $table->string('statusvalidasikendaraan')->nullable();
            $table->string('tipe', 30)->nullable();
            $table->string('jenis', 30)->nullable();
            $table->integer('isisilinder')->length(11)->nullable();
            $table->string('warna', 30)->nullable();
            $table->string('jenisbahanbakar', 30)->nullable();
            $table->integer('jumlahsumbu')->length(11)->nullable();
            $table->integer('jumlahroda')->length(11)->nullable();
            $table->string('model', 50)->nullable();
            $table->string('nobpkb', 50)->nullable();
            $table->integer('statusmobilstoring')->length(11)->nullable();
            $table->string('mandor_id')->nullable();
            $table->integer('jumlahbanserap')->length(11)->nullable();
            $table->integer('statusappeditban')->length(11)->nullable();
            $table->integer('statuslewatvalidasi')->length(11)->nullable();

            $table->string('photostnk', 1500)->nullable();
            $table->string('photobpkb', 1500)->nullable();
            $table->string('phototrado', 1500)->nullable();

            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();

        $isMandor = auth()->user()->isMandor();
        $isAdmin = auth()->user()->isAdmin();

        $userid = auth('api')->user()->id;


        $querymandor = db::table("mandordetail")->from(db::raw("mandordetail a with (readuncommitted)"))
            ->select('a.mandor_id')
            ->where('a.user_id', $userid);

        $tempmandordetail = '##tempmandordetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempmandordetail, function ($table) {
            $table->id();
            $table->unsignedBigInteger('mandor_id')->nullable();
        });

        DB::table($tempmandordetail)->insertUsing([
            'mandor_id',
        ],  $querymandor);

        $query = DB::table($modelTable);
        if (!$isAdmin) {
            if ($isMandor) {
                $query->Join(DB::raw($tempmandordetail . " as mandordetail"), 'trado.mandor_id', 'mandordetail.mandor_id');

                // $query->where('trado.mandor_id', $isMandor->mandor_id);
            }
        }
        $query = $this->selectColumns($query);
        $this->sort($query);


        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'keterangan', 'kodetrado', 'statusaktif', 'kmawal', 'kmakhirgantioli', 'tglakhirgantioli',  'tglstnkmati', 'tglasuransimati', 'tahun', 'akhirproduksi', 'merek', 'norangka', 'nomesin', 'nama', 'nostnk', 'alamatstnk', 'tglstandarisasi', 'tglserviceopname', 'statusstandarisasi', 'keteranganprogressstandarisasi', 'statusjenisplat', 'tglspeksimati', 'tglpajakstnk', 'tglgantiakiterakhir', 'statusmutasi', 'statusvalidasikendaraan', 'tipe', 'jenis', 'isisilinder', 'warna', 'jenisbahanbakar', 'jumlahsumbu', 'jumlahroda', 'model', 'nobpkb', 'statusmobilstoring', 'mandor_id', 'jumlahbanserap', 'statusappeditban', 'statuslewatvalidasi', 'photostnk', 'photobpkb', 'phototrado', 'modifiedby', 'created_at', 'updated_at'], $models);
        // dd(db::table($temp)->get());

        return  $temp;
    }


    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'mandor_id') {
            return $query->orderBy('mandor.namamandor', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'supir_id') {
            return $query->orderBy('supir.namasupir', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder'])
                ->orderBy($this->table . '.kodetrado', $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        // dd($query);
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
                        } else if ($filters['field'] == 'statusabsensisupir') {
                            $query = $query->where('parameter_statusabsensisupir.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusvalidasikendaraan') {
                            $query = $query->where('parameter_statusvalidasikendaraan.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'mandor_id') {
                            $query = $query->where('mandor.namamandor', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'supir_id') {
                            $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tglasuransimati' || $filters['field'] == 'tglserviceopname' || $filters['field'] == 'tglpajakstnk' || $filters['field'] == 'tglstnkmati' || $filters['field'] == 'tglasuransimati' || $filters['field'] == 'tglspeksimati' || $filters['field'] == 'tglgantiakiterakhir' || $filters['field'] == 'tglakhirgantioli') {
                            $query = $query->whereRaw("format((case when year(isnull($this->table." . $filters['field'] . ",'1900/1/1'))<2000 then null else trado." . $filters['field'] . " end), 'dd-MM-yyyy') LIKE '%$filters[data]%'");
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
                                $query = $query->orWhere('parameter_statusaktif.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
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
                            } else if ($filters['field'] == 'statusabsensisupir') {
                                $query = $query->orWhere('parameter_statusabsensisupir.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'statusvalidasikendaraan') {
                                $query = $query->orWhere('parameter_statusvalidasikendaraan.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'mandor_id') {
                                $query = $query->orWhere('mandor.namamandor', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'supir_id') {
                                $query = $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tglasuransimati' || $filters['field'] == 'tglserviceopname' || $filters['field'] == 'tglpajakstnk' || $filters['field'] == 'tglstnkmati' || $filters['field'] == 'tglasuransimati' || $filters['field'] == 'tglspeksimati' || $filters['field'] == 'tglgantiakiterakhir' || $filters['field'] == 'tglakhirgantioli') {
                                $query = $query->orWhereRaw("format((case when year(isnull($this->table." . $filters['field'] . ",'1900/1/1'))<2000 then null else trado." . $filters['field'] . " end), 'dd-MM-yyyy') LIKE '%$filters[data]%'");
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

    private function storeFiles(array $files, string $destinationFolder): string
    {
        $storedFiles = [];

        foreach ($files as $file) {
            $originalFileName = $file->hashName();
            $storedFile = Storage::putFileAs("trado/" . $destinationFolder, $file, $originalFileName);
            $resizedFiles = App::imageResize(storage_path("app/trado/$destinationFolder/"), storage_path("app/$storedFile"), $originalFileName);

            $storedFiles[] = $originalFileName;
        }

        return json_encode($storedFiles);
    }

    private function deleteFiles(Trado $trado)
    {
        $sizeTypes = ['', 'medium_', 'small_'];

        $relatedPhotoTrado = [];
        $relatedPhotoStnk = [];
        $relatedPhotoBpkb = [];

        $photoTrado = json_decode($trado->phototrado, true);
        $photoStnk = json_decode($trado->photostnk, true);
        $photoBpkb = json_decode($trado->photobpkb, true);

        if ($photoTrado != '') {
            foreach ($photoTrado as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoTrado[] = "trado/trado/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoTrado);
        }

        if ($photoStnk != '') {
            foreach ($photoStnk as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoStnk[] = "trado/stnk/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoStnk);
        }

        if ($photoBpkb != '') {
            foreach ($photoBpkb as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoBpkb[] = "trado/bpkb/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoBpkb);
        }
    }

    public function processStore(array $data): Trado
    {
        $trado = '';
        try {
            $statusStandarisasi = DB::table('parameter')->where('grp', 'STATUS STANDARISASI')->where('default', 'YA')->first();
            $statusMutasi = DB::table('parameter')->where('grp', 'STATUS MUTASI')->where('default', 'YA')->first();
            $statusValidasi = DB::table('parameter')->where('grp', 'STATUS VALIDASI KENDARAAN')->where('default', 'YA')->first();
            $statusMobStoring = DB::table('parameter')->where('grp', 'STATUS MOBIL STORING')->where('default', 'YA')->first();
            $statusAppeditban = DB::table('parameter')->where('grp', 'STATUS APPROVAL EDIT BAN')->where('default', 'YA')->first();
            $statusLewatValidasi = DB::table('parameter')->where('grp', 'STATUS LEWAT VALIDASI')->where('default', 'YA')->first();
            $isMandor = auth()->user()->isMandor();
            $userid = auth('api')->user()->id;
            // dd($userid);
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

            $trado = new Trado();
            $trado->keterangan = $data['keterangan'] ?? '';
            $trado->kodetrado = $data['kodetrado'];
            $trado->statusaktif = $data['statusaktif'];
            $trado->tahun = $data['tahun'];
            $trado->merek = $data['merek'];
            $trado->norangka = $data['norangka'];
            $trado->nomesin = $data['nomesin'];
            $trado->nama = $data['nama'];
            $trado->nostnk = $data['nostnk'];
            $trado->alamatstnk = $data['alamatstnk'];
            $trado->statusstandarisasi = $statusStandarisasi->id;
            $trado->statusjenisplat = $data['statusjenisplat'];
            $trado->statusmutasi = $statusMutasi->id;
            $trado->tglpajakstnk = date('Y-m-d', strtotime($data['tglpajakstnk']));
            $trado->tglstnkmati = date('Y-m-d', strtotime($data['tglstnkmati']));
            $trado->tglasuransimati = date('Y-m-d', strtotime($data['tglasuransimati']));
            $trado->tglspeksimati = date('Y-m-d', strtotime($data['tglspeksimati']));
            $trado->statusvalidasikendaraan = $statusValidasi->id;
            $trado->tipe = $data['tipe'];
            $trado->jenis = $data['jenis'];
            $trado->isisilinder = $data['isisilinder'];
            $trado->warna = $data['warna'];
            $trado->jenisbahanbakar = $data['jenisbahanbakar'];
            $trado->jumlahsumbu = $data['jumlahsumbu'];
            $trado->jumlahroda = $data['jumlahroda'];
            $trado->model = $data['model'];
            $trado->nobpkb = $data['nobpkb'];
            $trado->statusmobilstoring = $statusMobStoring->id;
            $trado->mandor_id = $data['mandor_id'] ?? 0;
            $trado->supir_id = $data['supir_id'] ?? 0;
            $trado->jumlahbanserap = $data['jumlahbanserap'];
            $trado->statusgerobak = $data['statusgerobak'];
            $trado->statusabsensisupir = $data['statusabsensisupir'];
            $trado->statusappeditban = $statusAppeditban->id;
            $trado->statuslewatvalidasi = $statusLewatValidasi->id;
            $trado->nominalplusborongan = str_replace(',', '', $data['nominalplusborongan']) ?? 0;
            $trado->modifiedby = auth('api')->user()->user;
            $trado->info = html_entity_decode(request()->info);


            if ($data['mandor_id'] != 0) {
                $trado->tglberlakumilikmandor = date('Y-m-d');
            }
            if ($data['supir_id'] != 0) {
                $trado->tglberlakumiliksupir = date('Y-m-d');
            }

            $trado->photostnk = $data['photostnk'];
            $trado->photobpkb = $data['photobpkb'];
            $trado->phototrado = $data['phototrado'];

            if (!$trado->save()) {
                throw new \Exception("Error storing trado.");
            }

            (new LogTrail())->processStore([
                'namatabel' => strtoupper($trado->getTable()),
                'postingdari' => 'ENTRY TRADO',
                'idtrans' => $trado->id,
                'nobuktitrans' => $trado->id,
                'aksi' => 'ENTRY',
                'datajson' => $trado->toArray(),
                'modifiedby' => $trado->modifiedby
            ]);


            // $approvalTradoKeterangan = ApprovalTradoKeterangan::where('kodetrado', $trado->kodetrado)->first();
            // if ($approvalTradoKeterangan) {
            //     $nonApp = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->whereRaw("grp like '%STATUS APPROVAL%'")->whereRaw("text like '%NON APPROVAL%'")->first();
            //     $approvalTradoKeterangan->statusapproval = $nonApp->id;
            //     $approvalTradoKeterangan->save();
            // }

            // $param1 = $trado->id;
            // $param2 = $trado->modifiedby;
            // $stokgudang = Stok::from(DB::raw("stok with (readuncommitted)"))
            //     ->select(DB::raw(
            //         "stok.id as stok_id,
            //         0  as gudang_id,"
            //             . $param1 . " as trado_id,
            //     0 as gandengan_id,
            //     0 as qty,'"
            //             . $param2 . "' as modifiedby"
            //     ))
            //     ->leftjoin('stokpersediaan', function ($join) use ($param1) {
            //         $join->on('stokpersediaan.stok_id', '=', 'stok.id');
            //         $join->on('stokpersediaan.trado_id', '=', DB::raw("'" . $param1 . "'"));
            //     })
            //     ->where(DB::raw("isnull(stokpersediaan.id,0)"), '=', 0);

            // $datadetail = json_decode($stokgudang->get(), true);

            // $dataexist = $stokgudang->exists();
            // $detaillogtrail = [];
            // foreach ($datadetail as $item) {


            //     $stokpersediaan = new StokPersediaan();
            //     $stokpersediaan->stok_id = $item['stok_id'];
            //     $stokpersediaan->gudang_id = $item['gudang_id'];
            //     $stokpersediaan->trado_id = $item['trado_id'];
            //     $stokpersediaan->gandengan_id = $item['gandengan_id'];
            //     $stokpersediaan->qty = $item['qty'];
            //     $stokpersediaan->modifiedby = $item['modifiedby'];
            //     if (!$stokpersediaan->save()) {
            //         throw new \Exception('Error store stok persediaan.');
            //     }
            //     $detaillogtrail[] = $stokpersediaan->toArray();
            // }

            // if ($dataexist == true) {
            //     (new LogTrail())->processStore([
            //         'namatabel' => strtoupper($stokpersediaan->getTable()),
            //         'postingdari' => 'STOK PERSEDIAAN',
            //         'idtrans' => $stokpersediaan->id,
            //         'nobuktitrans' => $stokpersediaan->id,
            //         'aksi' => 'EDIT',
            //         'datajson' => json_encode($detaillogtrail),
            //         'modifiedby' => auth('api')->user()->name
            //     ]);
            // }

            return $trado;
        } catch (\Throwable $th) {
            if ($trado != '') {
                $this->deleteFiles($trado);
            }

            throw $th;
        }
    }

    public function processUpdate(Trado $trado, array $data): Trado
    {
        try {
            $isMandor = auth()->user()->isMandor();
            if ($isMandor) {
                $data['mandor_id'] = $isMandor->mandor_id;
            }

            $trado->keterangan = $data['keterangan'] ?? '';
            $trado->kodetrado = $data['kodetrado'];
            $trado->statusaktif = $data['statusaktif'];
            $trado->tahun = $data['tahun'];
            $trado->merek = $data['merek'];
            $trado->norangka = $data['norangka'];
            $trado->nomesin = $data['nomesin'];
            $trado->nama = $data['nama'];
            $trado->nostnk = $data['nostnk'];
            $trado->alamatstnk = $data['alamatstnk'];
            $trado->statusjenisplat = $data['statusjenisplat'];
            $trado->tipe = $data['tipe'];
            $trado->jenis = $data['jenis'];
            $trado->tglpajakstnk = date('Y-m-d', strtotime($data['tglpajakstnk']));
            $trado->tglstnkmati = date('Y-m-d', strtotime($data['tglstnkmati']));
            $trado->tglasuransimati = date('Y-m-d', strtotime($data['tglasuransimati']));
            $trado->tglspeksimati = date('Y-m-d', strtotime($data['tglspeksimati']));
            $trado->isisilinder =  str_replace(',', '', $data['isisilinder']);
            $trado->warna = $data['warna'];
            $trado->jenisbahanbakar = $data['jenisbahanbakar'];
            $trado->jumlahsumbu = $data['jumlahsumbu'];
            $trado->jumlahroda = $data['jumlahroda'];
            $trado->model = $data['model'];
            $trado->nobpkb = $data['nobpkb'];
            // $trado->mandor_id = $data['mandor_id'] ?? 0;
            // $trado->supir_id = $data['supir_id'] ?? 0;
            $trado->jumlahbanserap = $data['jumlahbanserap'];
            $trado->statusgerobak = $data['statusgerobak'];
            $trado->statusabsensisupir = $data['statusabsensisupir'];
            $trado->nominalplusborongan = str_replace(',', '', $data['nominalplusborongan']) ?? 0;

            $this->deleteFiles($trado);

            $trado->photostnk = $data['photostnk'];
            $trado->photobpkb = $data['photobpkb'];
            $trado->phototrado = $data['phototrado'];
            $trado->modifiedby = auth('api')->user()->user;
            $trado->info = html_entity_decode(request()->info);

            if (!$trado->save()) {
                throw new \Exception("Error updating trado.");
            }

            (new LogTrail())->processStore([
                'namatabel' => strtoupper($trado->getTable()),
                'postingdari' => 'EDIT TRADO',
                'idtrans' => $trado->id,
                'nobuktitrans' => $trado->id,
                'aksi' => 'EDIT',
                'datajson' => $trado->toArray(),
                'modifiedby' => $trado->modifiedby
            ]);

            // $approvalTradoKeterangan = ApprovalTradoKeterangan::where('kodetrado', $trado->kodetrado)->first();
            // if ($approvalTradoKeterangan) {
            //     $nonApp = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->whereRaw("grp like '%STATUS APPROVAL%'")->whereRaw("text like '%NON APPROVAL%'")->first();
            //     $approvalTradoKeterangan->statusapproval = $nonApp->id;
            //     $approvalTradoKeterangan->save();
            // }

            // $param1 = $trado->id;
            // $param2 = $trado->modifiedby;
            // $stokgudang = Stok::from(DB::raw("stok with (readuncommitted)"))
            //     ->select(DB::raw(
            //         "stok.id as stok_id,
            //         0  as gudang_id,"
            //             . $param1 . " as trado_id,
            //     0 as gandengan_id,
            //     0 as qty,'"
            //             . $param2 . "' as modifiedby"
            //     ))
            //     ->leftjoin('stokpersediaan', function ($join) use ($param1) {
            //         $join->on('stokpersediaan.stok_id', '=', 'stok.id');
            //         $join->on('stokpersediaan.trado_id', '=', DB::raw("'" . $param1 . "'"));
            //     })
            //     ->where(DB::raw("isnull(stokpersediaan.id,0)"), '=', 0);
            // $datadetail = json_decode($stokgudang->get(), true);

            // $dataexist = $stokgudang->exists();
            // $detaillogtrail = [];
            // foreach ($datadetail as $item) {
            //     $stokpersediaan = new StokPersediaan();
            //     $stokpersediaan->stok_id = $item['stok_id'];
            //     $stokpersediaan->gudang_id = $item['gudang_id'];
            //     $stokpersediaan->trado_id = $item['trado_id'];
            //     $stokpersediaan->gandengan_id = $item['gandengan_id'];
            //     $stokpersediaan->qty = $item['qty'];
            //     $stokpersediaan->modifiedby = $item['modifiedby'];
            //     if (!$stokpersediaan->save()) {
            //         throw new \Exception('Error store stok persediaan.');
            //     }
            //     $detaillogtrail[] = $stokpersediaan->toArray();
            // }

            // if ($dataexist == true) {
            //     (new LogTrail())->processStore([
            //         'namatabel' => strtoupper($stokpersediaan->getTable()),
            //         'postingdari' => 'STOK PERSEDIAAN',
            //         'idtrans' => $trado->id,
            //         'nobuktitrans' => $trado->id,
            //         'aksi' => 'EDIT',
            //         'datajson' => json_encode($detaillogtrail),
            //         'modifiedby' => $trado->modifiedby
            //     ]);
            // }
            return $trado;
        } catch (\Throwable $th) {
            $this->deleteFiles($trado);
            throw $th;
        }
    }

    public function processDestroy($id): Trado
    {
        $trado = new Trado();
        $trado = $trado->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($trado->getTable()),
            'postingdari' => 'DELETE TRADO',
            'idtrans' => $trado->id,
            'nobuktitrans' => $trado->id,
            'aksi' => 'DELETE',
            'datajson' => $trado->toArray(),
            'modifiedby' => $trado->modifiedby
        ]);
        $this->deleteFiles($trado);

        return $trado;
    }

    public function processStatusNonAktifKeterangan($kodetrado)
    {
        $trado = Trado::from(DB::raw("trado with (readuncommitted)"))->where('kodetrado', $kodetrado)->first();

        $statusNonAktif = Parameter::from(DB::Raw("parameter with (readuncommitted)"))->select('id')->where('grp', '=', 'STATUS AKTIF')->where('subgrp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        $statusAktif = Parameter::from(DB::Raw("parameter with (readuncommitted)"))->select('id')->where('grp', '=', 'STATUS AKTIF')->where('subgrp', '=', 'STATUS AKTIF')->where('text', '=', 'AKTIF')->first();
        $required = [
            "kodetrado" => $trado->kodetrado,
            "tahun" => $trado->tahun,
            "merek" => $trado->merek,
            "norangka" => $trado->norangka,
            "nomesin" => $trado->nomesin,
            "nama" => $trado->nama,
            "nostnk" => $trado->nostnk,
            "alamatstnk" => $trado->alamatstnk,
            "tglpajakstnk" => $trado->tglpajakstnk,
            "tipe" => $trado->tipe,
            "jenis" => $trado->jenis,
            "isisilinder" => $trado->isisilinder,
            "warna" => $trado->warna,
            "jenisbahanbakar" => $trado->jenisbahanbakar,
            "jumlahsumbu" => $trado->jumlahsumbu,
            "jumlahroda" => $trado->jumlahroda,
            "model" => $trado->model,
            "nobpkb" => $trado->nobpkb,
            "jumlahbanserap" => $trado->jumlahbanserap,
        ];
        $key = array_keys($required, null);
        if (count($key)) {
            $trado->statusaktif = $statusNonAktif->id;
            $trado->save();
        }
        return $key;
    }

    public function processApprovalMesin(array $data)
    {
        // dd($data);
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        $jambatas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', '=', 'JAMBATASAPPROVAL')->where('subgrp', '=', 'JAMBATASAPPROVAL')->first();
        $tglbatas = date('Y-m-d') . ' ' . $jambatas->text ?? '00:00:00';
        // dd($tglbatas);
        for ($i = 0; $i < count($data['tradoId']); $i++) {

            $trado = Trado::find($data['tradoId'][$i]);
            if ($trado->statusapprovalreminderolimesin == $statusApproval->id) {
                $trado->statusapprovalreminderolimesin = $statusNonApproval->id;
                $trado->tglapprovalreminderolimesin = '';
                $trado->userapprovalreminderolimesin = '';
                $trado->tglbatasreminderolimesin = '';
                $aksi = $statusNonApproval->text;
            } else {
                $trado->statusapprovalreminderolimesin = $statusApproval->id;
                $trado->tglapprovalreminderolimesin = date('Y-m-d H:i:s');
                $trado->userapprovalreminderolimesin = auth('api')->user()->name;
                $trado->tglbatasreminderolimesin = $tglbatas;
                $aksi = $statusApproval->text;
            }

            $trado->tglapprovalreminderolimesin = date('Y-m-d H:i:s');
            $trado->userapprovalreminderolimesin = auth('api')->user()->name;
            $trado->info = html_entity_decode(request()->info);

            if (!$trado->save()) {
                throw new \Exception('Error Un/approval Reminder Oli Mesin.');
            }

            (new LogTrail())->processStore([
                'namatabel' => strtoupper($trado->getTable()),
                'postingdari' => "UN/APPROVAL Reminder Oli Mesin",
                'idtrans' => $trado->id,
                'nobuktitrans' => $trado->nobukti,
                'aksi' => $aksi,
                'datajson' => $trado->toArray(),
                'modifiedby' => auth('api')->user()->name,
            ]);
            $result[] = $trado;
        }

        return $result;
    }

    public function processApprovalPersneling(array $data)
    {
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        $jambatas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', '=', 'JAMBATASAPPROVAL')->where('subgrp', '=', 'JAMBATASAPPROVAL')->first();
        $tglbatas = date('Y-m-d') . ' ' . $jambatas->text ?? '00:00:00';
        for ($i = 0; $i < count($data['tradoId']); $i++) {

            $trado = Trado::find($data['tradoId'][$i]);
            if ($trado->statusapprovalreminderolipersneling == $statusApproval->id) {
                $trado->statusapprovalreminderolipersneling = $statusNonApproval->id;
                $trado->tglapprovalreminderolipersneling = '';
                $trado->userapprovalreminderolipersneling = '';
                $trado->tglbatasreminderolipersneling = '';
                $aksi = $statusNonApproval->text;
            } else {
                $trado->statusapprovalreminderolipersneling = $statusApproval->id;
                $trado->tglapprovalreminderolipersneling = date('Y-m-d H:i:s');
                $trado->userapprovalreminderolipersneling = auth('api')->user()->name;
                $trado->tglbatasreminderolipersneling = $tglbatas;
                $aksi = $statusApproval->text;
            }

            $trado->tglapprovalreminderolipersneling = date('Y-m-d H:i:s');
            $trado->userapprovalreminderolipersneling = auth('api')->user()->name;
            $trado->info = html_entity_decode(request()->info);

            if (!$trado->save()) {
                throw new \Exception('Error Un/approval Reminder Oli persneling.');
            }

            (new LogTrail())->processStore([
                'namatabel' => strtoupper($trado->getTable()),
                'postingdari' => "UN/APPROVAL Reminder Oli persneling",
                'idtrans' => $trado->id,
                'nobuktitrans' => $trado->nobukti,
                'aksi' => $aksi,
                'datajson' => $trado->toArray(),
                'modifiedby' => auth('api')->user()->name,
            ]);
            $result[] = $trado;
        }

        return $result;
    }

    public function processApprovalGardan(array $data)
    {
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        $jambatas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', '=', 'JAMBATASAPPROVAL')->where('subgrp', '=', 'JAMBATASAPPROVAL')->first();
        $tglbatas = date('Y-m-d') . ' ' . $jambatas->text ?? '00:00:00';
        for ($i = 0; $i < count($data['tradoId']); $i++) {

            $trado = Trado::find($data['tradoId'][$i]);
            if ($trado->statusapprovalreminderoligardan == $statusApproval->id) {
                $trado->statusapprovalreminderoligardan = $statusNonApproval->id;
                $trado->tglapprovalreminderoligardan = '';
                $trado->userapprovalreminderoligardan = '';
                $trado->tglbatasreminderoligardan = '';
                $aksi = $statusNonApproval->text;
            } else {
                $trado->statusapprovalreminderoligardan = $statusApproval->id;
                $trado->tglapprovalreminderoligardan = date('Y-m-d H:i:s');
                $trado->userapprovalreminderoligardan = auth('api')->user()->name;
                $trado->tglbatasreminderoligardan = $tglbatas;
                $aksi = $statusApproval->text;
            }

            $trado->tglapprovalreminderoligardan = date('Y-m-d H:i:s');
            $trado->userapprovalreminderoligardan = auth('api')->user()->name;
            $trado->info = html_entity_decode(request()->info);

            if (!$trado->save()) {
                throw new \Exception('Error Un/approval Reminder Oli gardan.');
            }

            (new LogTrail())->processStore([
                'namatabel' => strtoupper($trado->getTable()),
                'postingdari' => "UN/APPROVAL Reminder Oli gardan",
                'idtrans' => $trado->id,
                'nobuktitrans' => $trado->nobukti,
                'aksi' => $aksi,
                'datajson' => $trado->toArray(),
                'modifiedby' => auth('api')->user()->name,
            ]);
            $result[] = $trado;
        }
        return $result;
    }

    public function processApprovalSaringanHawa(array $data)
    {
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        $jambatas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', '=', 'JAMBATASAPPROVAL')->where('subgrp', '=', 'JAMBATASAPPROVAL')->first();
        $tglbatas = date('Y-m-d') . ' ' . $jambatas->text ?? '00:00:00';
        for ($i = 0; $i < count($data['tradoId']); $i++) {

            $trado = Trado::find($data['tradoId'][$i]);
            if ($trado->statusapprovalremindersaringanhawa == $statusApproval->id) {
                $trado->statusapprovalremindersaringanhawa = $statusNonApproval->id;
                $trado->tglapprovalremindersaringanhawa = '';
                $trado->userapprovalremindersaringanhawa = '';
                $trado->tglbatasremindersaringanhawa = '';
                $aksi = $statusNonApproval->text;
            } else {
                $trado->statusapprovalremindersaringanhawa = $statusApproval->id;
                $trado->tglapprovalremindersaringanhawa = date('Y-m-d H:i:s');
                $trado->userapprovalremindersaringanhawa = auth('api')->user()->name;
                $trado->tglbatasremindersaringanhawa = $tglbatas;
                $aksi = $statusApproval->text;
            }

            $trado->tglapprovalremindersaringanhawa = date('Y-m-d H:i:s');
            $trado->userapprovalremindersaringanhawa = auth('api')->user()->name;
            $trado->info = html_entity_decode(request()->info);

            if (!$trado->save()) {
                throw new \Exception('Error Un/approval Reminder Saringan Hawa.');
            }

            (new LogTrail())->processStore([
                'namatabel' => strtoupper($trado->getTable()),
                'postingdari' => "UN/APPROVAL Reminder Saringan Hawa",
                'idtrans' => $trado->id,
                'nobuktitrans' => $trado->nobukti,
                'aksi' => $aksi,
                'datajson' => $trado->toArray(),
                'modifiedby' => auth('api')->user()->name,
            ]);
            $result[] = $trado;
        }

        return $result;
    }

    public function getHistoryMandor($id)
    {
        $query = DB::table("trado")->from(DB::raw("trado with (readuncommitted)"))
            ->select(
                'trado.id',
                'trado.kodetrado as trado',
                'trado.mandor_id',
                'mandor.namamandor as mandor',
                DB::raw('ISNULL(trado.tglberlakumilikmandor, getdate()) as tglberlaku'),
            )
            ->leftJoin(DB::raw("mandor with (readuncommitted)"), 'trado.mandor_id', 'mandor.id')
            ->where('trado.id', $id)
            ->first();

        return $query;
    }

    public function processHistoryTradoMilikMandor($data)
    {
        $trado = Trado::findOrFail($data['id']);
        $trado->mandor_id = $data['mandorbaru_id'];
        $trado->tglberlakumilikmandor = date('Y-m-d', strtotime($data['tglberlaku']));

        if (!$trado->save()) {
            throw new \Exception("Error updating trado milik mandor.");
        }
        $dataLogtrail = [
            'id' => $trado->id,
            'kodetrado' => $trado->kodetrado,
            'mandorbaru_id' => $trado->mandor_id,
            'mandorlama_id' => $data['mandor_id'],
            'tglberlakumilikmandor' => $trado->tglberlakumilikmandor,

        ];
        (new HistorytradoMilikMandor())->processStore($dataLogtrail);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($trado->getTable()),
            'postingdari' => 'HISTORY TRADO MILIK MANDOR',
            'idtrans' => $trado->id,
            'nobuktitrans' => $trado->id,
            'aksi' => 'HISTORY TRADO MILIK MANDOR',
            'datajson' => $dataLogtrail,
            'modifiedby' => auth('api')->user()->name
        ]);

        DB::table('suratpengantar')
            ->where('trado_id', $trado->id)
            ->where('tglbukti', '>=', $trado->tglberlakumilikmandor)
            ->update([
                'mandortrado_id' => $trado->mandor_id,
                'modifiedby' => auth('api')->user()->name
            ]);

        return $trado;
    }


    public function getHistorySupir($id)
    {
        $query = DB::table("trado")->from(DB::raw("trado with (readuncommitted)"))
            ->select(
                'trado.id',
                'trado.kodetrado as trado',
                'trado.supir_id',
                'supir.namasupir as supir',
                DB::raw('ISNULL(trado.tglberlakumiliksupir, getdate()) as tglberlaku'),
            )
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'trado.supir_id', 'supir.id')
            ->where('trado.id', $id)
            ->first();

        return $query;
    }

    public function processHistoryTradoMilikSupir($data)
    {
        $trado = Trado::findOrFail($data['id']);
        $trado->supir_id = $data['supirbaru_id'];
        $trado->tglberlakumiliksupir = date('Y-m-d', strtotime($data['tglberlaku']));

        if (!$trado->save()) {
            throw new \Exception("Error updating trado milik supir.");
        }
        $dataLogtrail = [
            'id' => $trado->id,
            'kodetrado' => $trado->kodetrado,
            'supirbaru_id' => $trado->supir_id,
            'supirlama_id' => $data['supir_id'],
            'tglberlakumiliksupir' => $trado->tglberlakumiliksupir,

        ];

        (new HistoryTradoMilikSupir())->processStore($dataLogtrail);
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($trado->getTable()),
            'postingdari' => 'HISTORY TRADO MILIK SUPIR',
            'idtrans' => $trado->id,
            'nobuktitrans' => $trado->id,
            'aksi' => 'HISTORY TRADO MILIK SUPIR',
            'datajson' => $dataLogtrail,
            'modifiedby' => auth('api')->user()->name
        ]);
        return $trado;
    }

    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $Trado = Trado::find($data['Id'][$i]);

            $Trado->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            // dd($Trado);
            if ($Trado->save()) {

                (new LogTrail())->processStore([

                    'namatabel' => strtoupper($Trado->getTable()),
                    'postingdari' => 'APPROVAL TRADO',
                    'idtrans' => $Trado->id,
                    'nobuktitrans' => $Trado->id,
                    'aksi' => $aksi,
                    'datajson' => $Trado->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }


        return $Trado;
    }
    public function RefreshTradoNonAktif()
    {

        $date = date('Y-m-d');
        $statusApp = DB::table('parameter')->where('grp', 'STATUS APPROVAL')->where('subgrp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusNonApp = DB::table('parameter')->where('grp', 'STATUS APPROVAL')->where('subgrp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

        $statusAktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'AKTIF')->first();
        $statusNonAktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'NON AKTIF')->first();

        $tempapprovaltradoketerangan = '##tempapprovaltradoketerangan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempapprovaltradoketerangan, function ($table) {
            $table->string('kodetrado', 50)->nullable();
        });

        $queryapprovaltradoketerangan = db::table('approvaltradoketerangan')->from(db::raw("approvaltradoketerangan a with (readuncommitted)"))
            ->select(
                'a.kodetrado'
            )
            ->whereRaw("a.tglbatas<'". $date . "'")
            ->orderby('a.kodetrado', 'asc');



        DB::table($tempapprovaltradoketerangan)->insertUsing([
            'kodetrado',
        ],  $queryapprovaltradoketerangan);

        $queryapprovaltradogambar = db::table('approvaltradogambar')->from(db::raw("approvaltradogambar a with (readuncommitted)"))
            ->select(
                'a.kodetrado'
            )
            ->whereRaw("a.tglbatas<'". $date . "'")
            ->orderby('a.kodetrado', 'asc');
        // dd('test');

        DB::table($tempapprovaltradoketerangan)->insertUsing([
            'kodetrado',
        ],  $queryapprovaltradogambar);

        $tempapprovaltradorekap = '##tempapprovaltradorekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempapprovaltradorekap, function ($table) {
            $table->string('kodetrado', 50)->nullable();
        });

        $queryapprovaltradogambar = db::table($tempapprovaltradoketerangan)->from(db::raw($tempapprovaltradoketerangan . " a "))
            ->select(
                'a.kodetrado'
            )
            ->groupby('a.kodetrado');


        DB::table($tempapprovaltradorekap)->insertUsing([
            'kodetrado',
        ],  $queryapprovaltradogambar);

        // dd(db::table($tempapprovaltradorekap)->get());
        // dd($statusApp->id);

        $trado1 = DB::table('trado')->from(DB::raw("trado a with (readuncommitted)"))
            ->join(db::raw($tempapprovaltradorekap . ' b'), 'a.kodetrado', 'b.kodetrado')
            ->where('a.statusaktif', $statusAktif->id)
            ->get();

            // dd($trado1);



        $temptradoketerangan = '##temptradoketerangan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temptradoketerangan, function ($table) {
            $table->integer('trado_id')->nullable();
            $table->integer('statusapprovalketerangan')->nullable();
        });

        $temptradogambar = '##temptradogambar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temptradogambar, function ($table) {
            $table->integer('trado_id')->nullable();
            $table->integer('statusapprovalgambar')->nullable();
        });

        $datadetail = json_decode($trado1, true);
        foreach ($datadetail as $trado) {
            $photobpkb = true;
            $photostnk = true;
            $phototrado = true;

            if (!is_null(json_decode($trado['photobpkb']))) {
                foreach (json_decode($trado['photobpkb']) as $value) {
                    if ($value != '') {
                        if (!Storage::exists("trado/bpkb/$value")) {
                            $photobpkb = false;
                            goto selesai1;
                        }
                    } else {
                        $photobpkb = false;
                        goto selesai1;
                    }
                }
            } else {
                $photobpkb = false;
            }

            selesai1:
            if (!is_null(json_decode($trado['photostnk']))) {
                foreach (json_decode($trado['photostnk']) as $value) {
                    if ($value != '') {
                        if (!Storage::exists("trado/stnk/$value")) {
                            $photostnk = false;
                            goto selesai2;
                        }
                    } else {
                        $photostnk = false;
                        goto selesai2;
                    }
                }
            } else {
                $photostnk = false;
            }


            selesai2:
            if (!is_null(json_decode($trado['phototrado']))) {
                foreach (json_decode($trado['phototrado']) as $value) {
                    if ($value != '') {
                        if (!Storage::exists("trado/trado/$value")) {
                            $phototrado = false;
                            goto selesai3;
                        }
                    } else {
                        $phototrado = false;
                        goto selesai3;
                    }
                }
            } else {
                $phototrado = false;
            }

            selesai3:


            $querygambar = db::table('approvaltradogambar')->from(db::raw("approvaltradogambar a with (readuncommitted)"))
                ->select(
                    'a.id'
                )
                ->whereRaw("a.tglbatas<'" . $date . "'")
                ->where('a.kodetrado', $trado['kodetrado'])
                ->where('a.statusapproval', $statusApp->id)
                ->first();

                // dd($querygambar->tosql());
            if ($photobpkb == true || $photostnk == true  || $phototrado == true) {
                if (isset($querygambar)) {

                    DB::table($temptradogambar)->insert([
                        'trado_id' => $trado['id'],
                        'statusapprovalgambar' =>  $statusNonApp->id,
                    ]);
                }
            }
            $required = [
                "kodetrado" => $trado['kodetrado'],
                "tahun" => $trado['tahun'],
                "merek" => $trado['merek'],
                "norangka" => $trado['norangka'],
                "nomesin" => $trado['nomesin'],
                "nama" => $trado['nama'],
                "nostnk" => $trado['nostnk'],
                "alamatstnk" => $trado['alamatstnk'],
                "tglpajakstnk" => $trado['tglpajakstnk'],
                "tipe" => $trado['tipe'],
                "jenis" => $trado['jenis'],
                "isisilinder" => $trado['isisilinder'],
                "warna" => $trado['warna'],
                "jenisbahanbakar" => $trado['jenisbahanbakar'],
                "jumlahsumbu" => $trado['jumlahsumbu'],
                "jumlahroda" => $trado['jumlahroda'],
                "model" => $trado['model'],
                "nobpkb" => $trado['nobpkb'],
                "jumlahbanserap" => $trado['jumlahbanserap'],
            ];
            $key = array_keys($required, null);
            
            $jumlah = count($key);

            $queryketerangan = db::table('approvaltradoketerangan')->from(db::raw("approvaltradoketerangan a with (readuncommitted)"))
                ->select(
                    'a.id'
                )
                ->whereRaw("a.tglbatas<'" . $date . "'")
                ->where('a.kodetrado', $trado['kodetrado'])
                ->where('a.statusapproval', $statusApp->id)
                ->first();

            if ($jumlah != 0) {
                if (isset($queryketerangan)) {

                    DB::table($temptradoketerangan)->insert([
                        'trado_id' => $trado['id'],
                        'statusapprovalketerangan' =>  $statusNonApp->id,
                    ]);
                }
            }
        }

// dd('test');

        $query1 = db::table($temptradogambar)->from(db::raw($temptradogambar . " a"))
            ->select('a.trado_id')
            ->orderby('a.trado_id','asc')
            ->first();

        $query2 = db::table($temptradoketerangan)->from(db::raw($temptradoketerangan . " a"))
        ->select('a.trado_id')
            ->orderby('a.trado_id','asc')
            ->first();
            // 
            
        if (isset($query1)) {
            DB::table('trado')
                ->from(db::raw("trado"))
                ->join(db::raw($temptradogambar . " b"), 'trado.id', 'b.trado_id')
                ->update([
                    'statusaktif' => $statusNonAktif->id,
                ]);
        } else {
            if (isset($query2)) {
                DB::table('trado')
                    ->from(db::raw("trado"))
                    ->join(db::raw($temptradoketerangan . " b"), 'trado.id', 'b.trado_id')
                    ->update([
                        'statusaktif' => $statusNonAktif->id,
                    ]);
            }
        }
    }
}
