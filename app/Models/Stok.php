<?php

namespace App\Models;

use App\Helpers\App;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class Stok extends MyModel
{
    use HasFactory;

    protected $table = 'stok';

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

        $pengeluaranStok = DB::table('pengeluaranstokdetail')
            ->from(
                DB::raw("pengeluaranstokdetail as a with (readuncommitted)")
            )
            ->select(
                'a.stok_id'
            )
            ->where('a.stok_id', '=', $id)
            ->first();
        if (isset($pengeluaranStok)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pengeluaran Stok',
            ];


            goto selesai;
        }

        $penerimaanStok = DB::table('penerimaanstokdetail')
            ->from(
                DB::raw("penerimaanstokdetail as a with (readuncommitted)")
            )
            ->select(
                'a.stok_id'
            )
            ->where('a.stok_id', '=', $id)
            ->first();
        if (isset($penerimaanStok)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan Stok',
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

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $aktif = request()->aktif ?? '';
        $statusreuse = request()->statusreuse ?? '';
        $kelompok = request()->kelompok_id ?? '';
        $penerimaanstok_id = request()->penerimaanstok_id ?? '';
        $penerimaanstokheader_nobukti = request()->penerimaanstokheader_nobukti ?? '';
        $pg = Parameter::where('grp', 'PG STOK')->where('subgrp', 'PG STOK')->first();
        $pg = Parameter::where('grp', 'PG STOK')->where('subgrp', 'PG STOK')->first();
        $korv = DB::table('penerimaanstok')->where('kodepenerimaan', 'KORV')->first();

        $tempumuraki = '##tempumuraki' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempumuraki, function ($table) {
            $table->Integer('stok_id')->nullable();
            $table->integer('jumlahhari')->nullable();
            $table->date('tglawal')->nullable();
        });

        DB::table($tempumuraki)->insertUsing([
            'stok_id',
            'jumlahhari',
            'tglawal',
        ], (new SaldoUmurAki())->getallstok());

        $tempumuraki2 = '##tempumuraki2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempumuraki2, function ($table) {
            $table->Integer('stok_id')->nullable();
            $table->integer('jumlahhari')->nullable();
            $table->date('tglawal')->nullable();
        });

        $queryaki = db::table($tempumuraki)->from(db::raw($tempumuraki . " a "))
            ->select(
                'a.stok_id',
                db::raw("max(a.jumlahhari) as jumlahhari"),
                db::raw("max(a.tglawal) as tglawal"),
            )
            ->groupby('a.stok_id');

        DB::table($tempumuraki2)->insertUsing([
            'stok_id',
            'jumlahhari',
            'tglawal',
        ],  $queryaki);

        //update total vulkanisir

        $querytgl = date('Y/m/d');

        $reuse = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select('a.id')
            ->where('grp', 'STATUS REUSE')
            ->where('subgrp', 'STATUS REUSE')
            ->where('text', 'REUSE')
            ->first()->id ?? 0;


        $tempvulkan = '##tempvulkan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempvulkan, function ($table) {
            $table->integer('stok_id')->nullable();
            $table->integer('vulkan')->nullable();
        });

        $tempvulkanplus = '##tempvulkanplus' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempvulkanplus, function ($table) {
            $table->integer('stok_id')->nullable();
            $table->integer('vulkan')->nullable();
        });


        $tempvulkanminus = '##tempvulkanminus' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempvulkanminus, function ($table) {
            $table->integer('stok_id')->nullable();
            $table->integer('vulkan')->nullable();
        });


        $queryvulkanplus = db::table("stok")->from(db::raw("stok a with (readuncommitted)"))
            ->select(
                db::raw("a.id as stok_id"),
                db::raw("sum(b.vulkanisirke) as vulkan"),
            )
            ->join(db::raw("penerimaanstokdetail b with (readuncommitted)"), 'a.id', 'b.stok_id')
            ->join(db::raw("penerimaanstokheader c with (readuncommitted)"), 'b.nobukti', 'c.nobukti')
            ->where('a.statusreuse', $reuse)
            ->whereraw("c.tglbukti<='" . $querytgl . "'")
            ->groupby('a.id');

        DB::table($tempvulkanplus)->insertUsing([
            'stok_id',
            'vulkan',
        ],  $queryvulkanplus);

        $queryvulkanminus = db::table("stok")->from(db::raw("stok a with (readuncommitted)"))
            ->select(
                db::raw("a.id as stok_id"),
                db::raw("sum(b.vulkanisirke) as vulkan"),
            )
            ->join(db::raw("pengeluaranstokdetail b with (readuncommitted)"), 'a.id', 'b.stok_id')
            ->join(db::raw("pengeluaranstokheader c with (readuncommitted)"), 'b.nobukti', 'c.nobukti')
            ->where('a.statusreuse', $reuse)
            ->whereraw("c.tglbukti<='" . $querytgl . "'")
            ->groupby('a.id');

        DB::table($tempvulkanminus)->insertUsing([
            'stok_id',
            'vulkan',
        ],  $queryvulkanminus);


        $queryvulkan = db::table("stok")->from(db::raw("stok a with (readuncommitted)"))
            ->select(
                db::raw("a.id  as stok_id"),
                db::raw("((isnull(a.vulkanisirawal,0)+isnull(b.vulkan,0))-isnull(c.vulkan,0)) as vulkan"),
            )
            ->leftjoin(db::raw($tempvulkanplus . " b "), 'a.id', 'b.stok_id')
            ->leftjoin(db::raw($tempvulkanminus . " c "), 'a.id', 'c.stok_id')
            ->where('a.statusreuse', $reuse);

        DB::table($tempvulkan)->insertUsing([
            'stok_id',
            'vulkan',
        ],  $queryvulkan);





        // end update vulkanisir

        $query = DB::table($this->table)->select(
            'stok.id',
            'stok.namastok',
            'parameter.memo as statusaktif',
            'service.memo as statusservicerutin',
            'service.text as servicerutin_text',
            'stok.qtymin',
            'stok.qtymax',
            'stok.keterangan',
            'stok.gambar',
            'stok.namaterpusat',
            'statusban.text as statusban',
            'stok.statusban as statusban_id',
            'statusreuse.memo as statusreuse',
            'stok.modifiedby',
            'stok.totalvulkanisir',
            'stok.vulkanisirawal',
            'jenistrado.keterangan as jenistrado',
            'kelompok.kodekelompok as kelompok',
            'subkelompok.kodesubkelompok as subkelompok',
            'kategori.kodekategori as kategori',
            'merk.keterangan as merk',
            'stok.created_at',
            'stok.updated_at',
            DB::raw("'Laporan Stok' as judulLaporan"),
            DB::raw("'" . $getJudul->text . "' as judul"),
            DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
            DB::raw("isnull(c1.jumlahhari,0) as umuraki"),
            DB::raw("isnull(d1.vulkan,0) as vulkan"),


        )
            ->leftJoin('jenistrado', 'stok.jenistrado_id', 'jenistrado.id')
            ->leftJoin('kelompok', 'stok.kelompok_id', 'kelompok.id')
            ->leftJoin('subkelompok', 'stok.subkelompok_id', 'subkelompok.id')
            ->leftJoin('kategori', 'stok.kategori_id', 'kategori.id')
            ->leftJoin('parameter', 'stok.statusaktif', 'parameter.id')
            ->leftJoin(DB::raw("parameter as service with (readuncommitted)"), 'stok.statusservicerutin', 'service.id')
            ->leftJoin(DB::raw("parameter as statusban with (readuncommitted)"), 'stok.statusban', 'statusban.id')
            ->leftJoin(DB::raw("parameter as statusreuse with (readuncommitted)"), 'stok.statusreuse', 'statusreuse.id')
            ->leftJoin('merk', 'stok.merk_id', 'merk.id')
            ->leftJoin(db::raw($tempvulkan . " d1"), "stok.id", "d1.stok_id")
            ->leftJoin(db::raw($tempumuraki2 . " c1"), "stok.id", "c1.stok_id");



        $this->filter($query);
        // dd($query->toSql());
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('stok.statusaktif', '=', $statusaktif->id);
        }
        if (($statusreuse == 'REUSE') || ($pg->text == $penerimaanstok_id)) {

            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS REUSE')
                ->where('text', '=', 'REUSE')
                ->first();



            $query->where('stok.statusreuse', '=', $statusaktif->id);
        }

        if ($kelompok != '') {
            $query->where('stok.kelompok_id', '=', $kelompok);
        }
        if ($penerimaanstokheader_nobukti) {
            $spb = Parameter::where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();
            if ($spb->text == $penerimaanstok_id) {
                $query->leftJoin('penerimaanstokdetail', 'stok.id', 'penerimaanstokdetail.stok_id')
                    ->where('penerimaanstokdetail.nobukti', $penerimaanstokheader_nobukti);
            }
        }

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->paginate($query);
        // dd($query->toSql());
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

        $getStok = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json',
        ])

            ->get($server . "stok?limit=0&aktif=AKTIF");

        $data = $getStok->json()['data'];

        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'StokTruckingController';

        $temtabel = 'tempstoktnl' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;

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
            $table->string('namastok', 300)->nullable();
            $table->string('statusaktif', 300)->nullable();
            $table->string('statusservicerutin', 300)->nullable();
            $table->string('servicerutin_text', 300)->nullable();
            $table->double('qtymin', 15, 2)->nullable();
            $table->double('qtymax', 15, 2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->longText('gambar')->nullable();
            $table->longText('namaterpusat')->nullable();
            $table->string('statusban', 300)->nullable();
            $table->integer('statusban_id')->nullable();
            $table->string('statusreuse', 300)->nullable();
            $table->string('modifiedby', 300)->nullable();
            $table->double('totalvulkanisir', 15, 2)->nullable();
            $table->double('vulkanisirawal', 15, 2)->nullable();
            $table->string('jenistrado', 300)->nullable();
            $table->string('kelompok', 300)->nullable();
            $table->string('subkelompok', 300)->nullable();
            $table->string('kategori', 300)->nullable();
            $table->string('merk', 300)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('umuraki')->nullable();
            $table->integer('vulkan')->nullable();
        });

        foreach ($data as $row) {
            unset($row['judulLaporan']);
            unset($row['judul']);
            unset($row['tglcetak']);
            unset($row['usercetak']);
            unset($row['statusreuse']);
            DB::table($temtabel)->insert($row);
        }

        return $temtabel;
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->nullable();
            $table->unsignedBigInteger('statusreuse')->nullable();
            $table->unsignedBigInteger('statusban')->nullable();
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

        $statusreuse = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS REUSE')
            ->where('subgrp', '=', 'STATUS REUSE')
            ->where('default', '=', 'YA')
            ->first();

        $statusban = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS BAN')
            ->where('subgrp', '=', 'STATUS BAN')
            ->where('default', '=', 'YA')
            ->first();

        DB::table($tempdefault)->insert([
            "statusaktif" => $statusaktif->id,
            "statusreuse" => $statusreuse->id,
            "statusban" => $statusban->id
        ]);

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusreuse',
                'statusban'
            );

        $data = $query->first();
        // dd($data);
        return $data;
    }
    public function findAll($id)
    {
        $data = DB::table('stok')->select(
            'stok.id',
            'stok.namastok',
            'stok.statusaktif',
            'stok.qtymin',
            'stok.qtymax',
            'stok.keterangan',
            'stok.gambar',
            'stok.namaterpusat',
            'stok.modifiedby',
            'stok.jenistrado_id',
            'stok.kelompok_id',
            'stok.totalvulkanisir',
            'stok.statusreuse',
            'stok.subkelompok_id',
            'stok.satuan_id',
            'stok.kategori_id',
            'stok.merk_id',
            'stok.statusban',
            'jenistrado.keterangan as jenistrado',
            'kelompok.kodekelompok as kelompok',
            'subkelompok.kodesubkelompok as subkelompok',
            'satuan.satuan as satuan',
            'kategori.kodekategori as kategori',
            'merk.keterangan as merk',
        )
            ->leftJoin('jenistrado', 'stok.jenistrado_id', 'jenistrado.id')
            ->leftJoin('kelompok', 'stok.kelompok_id', 'kelompok.id')
            ->leftJoin('subkelompok', 'stok.subkelompok_id', 'subkelompok.id')
            ->leftJoin('satuan', 'stok.satuan_id', 'satuan.id')
            ->leftJoin('kategori', 'stok.kategori_id', 'kategori.id')
            ->leftJoin('merk', 'stok.merk_id', 'merk.id')
            ->where('stok.id', $id)
            ->first();

        return $data;
    }

    public function getGambarName($id)
    {
        $query = DB::table("stok")->from(DB::raw("stok with (readuncommitted)"))
            ->select('gambar')
            ->where('id', $id)
            ->first();

        return $query;
    }

    public function createTemp(string $modelTable)
    {
        $tempumuraki = '##tempumuraki' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempumuraki, function ($table) {
            $table->Integer('stok_id')->nullable();
            $table->integer('jumlahhari')->nullable();
            $table->date('tglawal')->nullable();
        });

        DB::table($tempumuraki)->insertUsing([
            'stok_id',
            'jumlahhari',
            'tglawal',
        ], (new SaldoUmurAki())->getallstok());

        $tempumuraki2 = '##tempumuraki2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempumuraki2, function ($table) {
            $table->Integer('stok_id')->nullable();
            $table->integer('jumlahhari')->nullable();
            $table->date('tglawal')->nullable();
        });

        $queryaki = db::table($tempumuraki)->from(db::raw($tempumuraki . " a "))
            ->select(
                'a.stok_id',
                db::raw("max(a.jumlahhari) as jumlahhari"),
                db::raw("max(a.tglawal) as tglawal"),
            )
            ->groupby('a.stok_id');

        DB::table($tempumuraki2)->insertUsing([
            'stok_id',
            'jumlahhari',
            'tglawal',
        ],  $queryaki);

        //update total vulkanisir

        $querytgl = date('Y/m/d');

        $reuse = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select('a.id')
            ->where('grp', 'STATUS REUSE')
            ->where('subgrp', 'STATUS REUSE')
            ->where('text', 'REUSE')
            ->first()->id ?? 0;


        $tempvulkan = '##tempvulkan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempvulkan, function ($table) {
            $table->integer('stok_id')->nullable();
            $table->integer('vulkan')->nullable();
        });

        $tempvulkanplus = '##tempvulkanplus' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempvulkanplus, function ($table) {
            $table->integer('stok_id')->nullable();
            $table->integer('vulkan')->nullable();
        });


        $tempvulkanminus = '##tempvulkanminus' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempvulkanminus, function ($table) {
            $table->integer('stok_id')->nullable();
            $table->integer('vulkan')->nullable();
        });


        $queryvulkanplus = db::table("stok")->from(db::raw("stok a with (readuncommitted)"))
            ->select(
                db::raw("a.id as stok_id"),
                db::raw("sum(b.vulkanisirke) as vulkan"),
            )
            ->join(db::raw("penerimaanstokdetail b with (readuncommitted)"), 'a.id', 'b.stok_id')
            ->join(db::raw("penerimaanstokheader c with (readuncommitted)"), 'b.nobukti', 'c.nobukti')
            ->where('a.statusreuse', $reuse)
            ->whereraw("c.tglbukti<='" . $querytgl . "'")
            ->groupby('a.id');

        DB::table($tempvulkanplus)->insertUsing([
            'stok_id',
            'vulkan',
        ],  $queryvulkanplus);

        $queryvulkanminus = db::table("stok")->from(db::raw("stok a with (readuncommitted)"))
            ->select(
                db::raw("a.id as stok_id"),
                db::raw("sum(b.vulkanisirke) as vulkan"),
            )
            ->join(db::raw("pengeluaranstokdetail b with (readuncommitted)"), 'a.id', 'b.stok_id')
            ->join(db::raw("pengeluaranstokheader c with (readuncommitted)"), 'b.nobukti', 'c.nobukti')
            ->where('a.statusreuse', $reuse)
            ->whereraw("c.tglbukti<='" . $querytgl . "'")
            ->groupby('a.id');

        DB::table($tempvulkanminus)->insertUsing([
            'stok_id',
            'vulkan',
        ],  $queryvulkanminus);


        $queryvulkan = db::table("stok")->from(db::raw("stok a with (readuncommitted)"))
            ->select(
                db::raw("a.id  as stok_id"),
                db::raw("((isnull(a.vulkanisirawal,0)+isnull(b.vulkan,0))-isnull(c.vulkan,0)) as vulkan"),
            )
            ->leftjoin(db::raw($tempvulkanplus . " b "), 'a.id', 'b.stok_id')
            ->leftjoin(db::raw($tempvulkanminus . " c "), 'a.id', 'c.stok_id')
            ->where('a.statusreuse', $reuse);

        DB::table($tempvulkan)->insertUsing([
            'stok_id',
            'vulkan',
        ],  $queryvulkan);

        //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->unsignedBigInteger('jenistrado_id')->nullable();
            $table->unsignedBigInteger('kelompok_id')->nullable();
            $table->unsignedBigInteger('subkelompok_id')->nullable();
            $table->unsignedBigInteger('kategori_id')->nullable();
            $table->unsignedBigInteger('merk_id')->nullable();
            $table->string('namastok', 200)->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->double('qtymin', 15, 2)->nullable();
            $table->double('qtymax', 15, 2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->longText('gambar')->nullable();
            $table->longText('namaterpusat')->nullable();
            $table->string('umuraki', 15, 2)->nullable();
            $table->string('vulkan', 15, 2)->nullable();

            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->select(
            'stok.id',
            'stok.jenistrado_id',
            'stok.kelompok_id',
            'stok.subkelompok_id',
            'stok.kategori_id',
            'stok.merk_id',
            'stok.namastok',
            'stok.statusaktif',
            'stok.qtymin',
            'stok.qtymax',
            'stok.keterangan',
            'stok.gambar',
            'stok.namaterpusat',
            DB::raw("isnull(c1.jumlahhari,0) as umuraki"),
            DB::raw("isnull(d1.vulkan,0) as vulkan"),
            'stok.modifiedby',
            'stok.created_at',
            'stok.updated_at'
        )
            ->leftJoin('jenistrado', 'stok.jenistrado_id', 'jenistrado.id')
            ->leftJoin('kelompok', 'stok.kelompok_id', 'kelompok.id')
            ->leftJoin('subkelompok', 'stok.subkelompok_id', 'subkelompok.id')
            ->leftJoin('kategori', 'stok.kategori_id', 'kategori.id')
            ->leftJoin('parameter', 'stok.statusaktif', 'parameter.id')
            ->leftJoin(DB::raw("parameter as service with (readuncommitted)"), 'stok.statusservicerutin', 'service.id')
            ->leftJoin(DB::raw("parameter as statusban with (readuncommitted)"), 'stok.statusban', 'statusban.id')
            ->leftJoin(DB::raw("parameter as statusreuse with (readuncommitted)"), 'stok.statusreuse', 'statusreuse.id')
            ->leftJoin('merk', 'stok.merk_id', 'merk.id')
            ->leftJoin(db::raw($tempvulkan . " d1"), "stok.id", "d1.stok_id")
            ->leftJoin(db::raw($tempumuraki2 . " c1"), "stok.id", "c1.stok_id");

        $query = $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing([
            'id',
            'jenistrado_id',
            'kelompok_id',
            'subkelompok_id',
            'kategori_id',
            'merk_id',
            'namastok',
            'statusaktif',
            'qtymin',
            'qtymax',
            'keterangan',
            'gambar',
            'namaterpusat',
            'umuraki',
            'vulkan',
            'modifiedby',
            'created_at',
            'updated_at'
        ], $models);

        return  $temp;
    }


    public function selectColumns($query)
    {

        return $query->select(
            DB::raw(
                "$this->table.id,
                $this->table.namastok,
                parameter.memo as statusaktif,
                $this->table.qtymin,
                $this->table.qtymax,
                $this->table.keterangan,
                $this->table.gambar,
                $this->table.namaterpusat,
                $this->table.modifiedby,
                jenistrado.keterangan as jenistrado,
                kelompok.kodekelompok as kelompok,
                subkelompok.kodesubkelompok as subkelompok,
                kategori.kodekategori as kategori,
                merk.keterangan as merk,
                $this->table.created_at,
                $this->table.updated_at"
            )
        )
            ->leftJoin('jenistrado', 'stok.jenistrado_id', 'jenistrado.id')
            ->leftJoin('kelompok', 'stok.kelompok_id', 'kelompok.id')
            ->leftJoin('subkelompok', 'stok.subkelompok_id', 'subkelompok.id')
            ->leftJoin('kategori', 'stok.kategori_id', 'kategori.id')
            ->leftJoin('parameter', 'stok.statusaktif', 'parameter.id')
            ->leftJoin('merk', 'stok.merk_id', 'merk.id');
    }


    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'jenistrado') {
            return $query->orderBy('jenistrado.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'kelompok') {
            return $query->orderBy('kelompok.kodekelompok', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'subkelompok') {
            return $query->orderBy('subkelompok.kodesubkelompok', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'kategori') {
            return $query->orderBy('kategori.kodekategori', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'merk') {
            return $query->orderBy('merk.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'umuraki') {
            return $query->orderBy('c1.jumlahhari', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'vulkan') {
            return $query->orderBy('d1.vulkan', $this->params['sortOrder']);
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
                        if ($filters['field'] == 'statusservicerutin') {
                            $query = $query->where('service.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'jenistrado') {
                            $query = $query->whereRaw('jenistrado.keterangan LIKE' . "'%$filters[data]%'");
                        } else if ($filters['field'] == 'kelompok') {
                            $query = $query->whereRaw('kelompok.kodekelompok LIKE' . "'%$filters[data]%'");
                        } else if ($filters['field'] == 'subkelompok') {
                            $query = $query->whereRaw('subkelompok.kodesubkelompok LIKE' . "'%$filters[data]%'");
                        } else if ($filters['field'] == 'kategori') {
                            $query = $query->whereRaw('kategori.kodekategori LIKE' . "'%$filters[data]%'");
                        } else if ($filters['field'] == 'merk') {
                            $query = $query->whereRaw('merk.keterangan LIKE' . "'%$filters[data]%'");
                        } else if ($filters['field'] == 'umuraki') {
                            $query = $query->whereRaw('c1.jumlahhari LIKE' . "'%$filters[data]%'");
                        } else if ($filters['field'] == 'vulkan') {
                            $query = $query->whereRaw('d1.vulkan LIKE' . "'%$filters[data]%'");
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
                            if ($filters['field'] == 'statusservicerutin') {
                                $query = $query->orWhere('service.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'jenistrado') {
                                $query = $query->orWhereRaw('jenistrado.keterangan LIKE ' . "'%$filters[data]%'");
                            } else if ($filters['field'] == 'kelompok') {
                                $query = $query->orWhereRaw('kelompok.kodekelompok LIKE ' . "'%$filters[data]%'");
                            } else if ($filters['field'] == 'subkelompok') {
                                $query = $query->orWhereRaw('subkelompok.kodesubkelompok LIKE ' . "'%$filters[data]%'");
                            } else if ($filters['field'] == 'kategori') {
                                $query = $query->orWhereRaw('kategori.kodekategori LIKE ' . "'%$filters[data]%'");
                            } else if ($filters['field'] == 'merk') {
                                $query = $query->orWhereRaw('merk.keterangan LIKE ' . "'%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'umuraki') {
                                $query = $query->orwhereRaw('c1.jumlahhari LIKE' . "'%$filters[data]%'");
                            } else if ($filters['field'] == 'vulkan') {
                                $query = $query->orwhereRaw('d1.vulkan LIKE' . "'%$filters[data]%'");
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

    public function processStore(array $data): Stok
    {
        $stok = new stok();
        $stok->keterangan = $data['keterangan'];
        $stok->namastok = $data['namastok'];
        $stok->namaterpusat = $data['namaterpusat'];
        $stok->statusaktif = $data['statusaktif'];
        $stok->kelompok_id = $data['kelompok_id'];
        $stok->subkelompok_id = $data['subkelompok_id'];
        $stok->kategori_id = $data['kategori_id'];
        $stok->merk_id = $data['merk_id'] ?? 0;
        $stok->jenistrado_id = $data['jenistrado_id'] ?? 0;
        $stok->keterangan = $data['keterangan'] ?? '';
        $stok->qtymin = $data['qtymin'] ?? 0;
        $stok->qtymax = $data['qtymax'] ?? 0;
        $stok->statusreuse = $data['statusreuse'];
        $stok->statusban = $data['statusban'];
        $stok->satuan_id = $data['satuan_id'];
        $stok->statusservicerutin = $data['statusservicerutin'];
        $stok->vulkanisirawal = $data['vulkanisirawal'];
        $stok->hargabelimin = $data['hargabelimin'];
        $stok->hargabelimax = $data['hargabelimax'];
        $stok->modifiedby = auth('api')->user()->name;
        $stok->info = html_entity_decode(request()->info);
        if ($data['gambar']) {
            $stok->gambar = $this->storeFiles($data['gambar'], 'stok');
        } else {
            $stok->gambar = '';
        }

        if (!$stok->save()) {
            throw new \Exception("Error storing stok.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($stok->getTable()),
            'postingdari' => 'ENTRY STOK',
            'idtrans' => $stok->id,
            'nobuktitrans' => $stok->id,
            'aksi' => 'ENTRY',
            'datajson' => $stok->toArray(),
            'modifiedby' => $stok->modifiedby
        ]);


        return $stok;
    }
    public function processUpdate(Stok $stok, array $data): Stok
    {

        $stok->keterangan = $data['keterangan'];
        $stok->namastok = $data['namastok'];
        $stok->namaterpusat = $data['namaterpusat'];
        $stok->namaterpusat = $data['namaterpusat'];
        $stok->statusaktif = $data['statusaktif'];
        $stok->kelompok_id = $data['kelompok_id'];
        $stok->subkelompok_id = $data['subkelompok_id'];
        $stok->kategori_id = $data['kategori_id'];
        $stok->merk_id =  $data['merk_id'] ?? 0;
        $stok->jenistrado_id = $data['jenistrado_id'] ?? 0;
        $stok->keterangan = $data['keterangan'] ?? '';
        $stok->qtymin = $data['qtymin'] ?? 0;
        $stok->qtymax = $data['qtymax'] ?? 0;
        $stok->statusban = $data['statusban'];
        $stok->satuan_id = $data['satuan_id'];
        $stok->statusservicerutin = $data['statusservicerutin'];
        $stok->hargabelimin = $data['hargabelimin'];
        $stok->hargabelimax = $data['hargabelimax'];
        $stok->modifiedby = auth('api')->user()->name;
        $stok->info = html_entity_decode(request()->info);

        $statusPakai = $this->cekvalidasihapus($stok->id);
        if (!$statusPakai['kondisi']) {
            $stok->statusreuse = $data['statusreuse'];
            $stok->vulkanisirawal = $data['vulkanisirawal'];
        }

        $this->deleteFiles($stok);
        if ($data['gambar']) {
            $stok->gambar = $this->storeFiles($data['gambar'], 'stok');
        } else {
            $stok->gambar = '';
        }
        if (!$stok->save()) {
            throw new \Exception("Error updating stok.");
        }


        (new LogTrail())->processStore([
            'namatabel' => strtoupper($stok->getTable()),
            'postingdari' => 'EDIT STOK',
            'idtrans' => $stok->id,
            'nobuktitrans' => $stok->id,
            'aksi' => 'ENTRY',
            'datajson' => $stok->toArray(),
            'modifiedby' => $stok->modifiedby
        ]);

        return $stok;
    }

    public function processDestroy($id): Stok
    {
        $stok = new Stok;
        $stok = $stok->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($stok->getTable()),
            'postingdari' => 'DELETE STOK',
            'idtrans' => $stok->id,
            'nobuktitrans' => $stok->id,
            'aksi' => 'DELETE',
            'datajson' => $stok->toArray(),
            'modifiedby' => $stok->modifiedby
        ]);

        return $stok;
    }

    public function processApprovalklaim(Stok $stok): Stok
    {
        $statusApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        if ($stok->statusapprovaltanpaklaim == $statusApproval->id) {
            $stok->statusapprovaltanpaklaim = $statusNonApproval->id;
        } else {
            $stok->statusapprovaltanpaklaim = $statusApproval->id;
        }

        $stok->tglapprovaltanpaklaim = date('Y-m-d', time());
        $stok->userapprovaltanpaklaim = auth('api')->user()->name;

        if ($stok->save()) {
            (new LogTrail())->processStore([
                'namatabel' => strtoupper($stok->getTable()),
                'postingdari' => 'UN/APPROVE STOK TANPA KALIM',
                'idtrans' => $stok->id,
                'nobuktitrans' => $stok->id,
                'aksi' => 'UN/APPROVE',
                'datajson' => $stok->toArray(),
                'modifiedby' => $stok->modifiedby
            ]);

            DB::commit();
        }
        return $stok;
    }

    public function getvulkanisir($id)
    {

        $queryvulkanawal =  Stok::from(db::raw("stok a with (readuncommitted)"))
            ->select(db::raw("isnull(a.vulkanisirawal,0) as vulawal"))
            ->where('a.id', $id)->first();

        $queryvulkan = Stok::from(db::raw("stok a with (readuncommitted)"))
            ->select(
                'a.statusban',
                db::raw("sum(isnull(b.vulkanisirke,0)) as vulkanplus"),
                db::raw("sum(isnull(c.vulkanisirke,0)) as vulkanminus")
            )
            ->leftjoin(db::raw("penerimaanstokdetail b with (readuncommitted)"), 'a.id', 'b.stok_id')
            ->leftjoin(db::raw("pengeluaranstokdetail c with (readuncommitted)"), 'a.id', 'c.stok_id')
            ->where('a.id', $id)
            ->groupby('a.id', 'a.statusban')
            ->first();

        $totalplus = $queryvulkan->vulkanplus ?? 0;
        $totalminus = $queryvulkan->vulkanminus ?? 0;
        $vulawal = $queryvulkanawal->vulawal ?? 0;
        $total = ($totalplus + $vulawal) - $totalminus;
        if (isset($queryvulkan)) {
            $totalvulkan = $total ?? 0;
        } else {
            $totalvulkan = 0;
        }

        return ['totalvulkan' => $totalvulkan, 'statusban' => $queryvulkan->statusban];
    }


    private function storeFiles(array $files, string $destinationFolder): string
    {
        $storedFiles = [];

        foreach ($files as $file) {
            $originalFileName = $file->hashName();
            $storedFile = Storage::putFileAs($destinationFolder, $file, $originalFileName);
            $resizedFiles = App::imageResize(storage_path("app/$destinationFolder/"), storage_path("app/$storedFile"), $originalFileName);

            $storedFiles[] = $originalFileName;
        }

        return json_encode($storedFiles);
    }


    private function deleteFiles(Stok $stok)
    {
        $sizeTypes = ['', 'medium_', 'small_'];

        $relatedPhotoStok = [];
        $photoStok = json_decode($stok->gambar, true);
        if ($photoStok) {
            foreach ($photoStok as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoStok[] = "stok/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoStok);
        }
    }

    public function processKonsolidasi($data)
    {
        if ($data['stok_id' . config('app.kode_cabang')] != '') {
            $query = DB::table('stok')->where('id', $data['stok_id' . config('app.kode_cabang')])->update([
                'namaterpusat' => strtoupper($data['namaterpusat']),
            ]);
        }
        if ($data['stok_id' . config('app.kode_cabang') . 'del'] != '') {
            $query = DB::table('stok')->where('id', $data['stok_id' . config('app.kode_cabang').'del'])->update([
                'namaterpusat' => '',
            ]);
        }
        $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->select('cabang.kodecabang')
            ->join(db::raw("parameter with (readuncommitted)"), 'cabang.id', 'parameter.text')
            ->where('parameter.grp', 'ID CABANG')
            ->first();

        unset($data['cekKoneksi'][$getCabang->kodecabang]);

        $this->saveToCabang($data);
    }

    public function saveToCabang($data)
    {
        $cekKoneksi = $data['cekKoneksi'];
        if (array_key_exists('TNL', $cekKoneksi)) {

            if ($data['stok_idjkttnl'] != '') {
                $accessTokenJktTnlStok = session('access_token_jkttnl_stok');
                $data['konsolidasi'] = true;
                if (!$accessTokenJktTnlStok) {
                    $postRequest = [
                        'user' => config('app.user_api'),
                        'password' => config('app.pass_api'),
                        'ipclient' => '',
                        'ipserver' => '',
                        'latitude' => '',
                        'longitude' => '',
                        'browser' => '',
                        'os' => '',
                    ];
                    $token = $this->getToken(config('app.url_token_jkttnl'), $postRequest);

                    $token = json_decode($token, TRUE);
                    if ($token != '') {

                        if (array_key_exists('access_token', $token)) {
                            $accessToken = $token['access_token'];
                            session(['access_token_jkttnl_stok' => $token['access_token']]);

                            $send = $this->postData(config('app.url_post_konsol_jkttnl'), 'POST', $accessToken, $data);
                            $send = json_decode($send, TRUE);
                            if (array_key_exists('status', $send)) {
                                goto selesai;
                            } else {
                                throw new \Exception($send['message']);
                            }
                        } else {
                            throw new \Exception("server Jakarta TNL tidak bisa diakses");
                        }
                    } else {
                        throw new \Exception("server Jakarta TNL tidak bisa diakses");
                    }
                } else {
                    $send = $this->postData(config('app.url_post_konsol_jkttnl'), 'POST', $accessTokenJktTnlStok, $data);

                    $send = json_decode($send, TRUE);
                    if (array_key_exists('status', $send)) {
                        goto selesai;
                    } else {
                        throw new \Exception($send['message']);
                    }
                }
            }
            if ($data['stok_idjkttnldel'] != '') {
                $accessTokenJktTnlStok = session('access_token_jkttnl_stok');
                $data['konsolidasi'] = true;
                if (!$accessTokenJktTnlStok) {
                    $postRequest = [
                        'user' => config('app.user_api'),
                        'password' => config('app.pass_api'),
                        'ipclient' => '',
                        'ipserver' => '',
                        'latitude' => '',
                        'longitude' => '',
                        'browser' => '',
                        'os' => '',
                    ];
                    $token = $this->getToken(config('app.url_token_jkttnl'), $postRequest);

                    $token = json_decode($token, TRUE);
                    if ($token != '') {

                        if (array_key_exists('access_token', $token)) {
                            $accessToken = $token['access_token'];
                            session(['access_token_jkttnl_stok' => $token['access_token']]);

                            $send = $this->postData(config('app.url_post_konsol_jkttnl'), 'POST', $accessToken, $data);
                            $send = json_decode($send, TRUE);
                            if (array_key_exists('status', $send)) {
                                goto selesai;
                            } else {
                                throw new \Exception($send['message']);
                            }
                        } else {
                            throw new \Exception("server Jakarta TNL tidak bisa diakses");
                        }
                    } else {
                        throw new \Exception("server Jakarta TNL tidak bisa diakses");
                    }
                } else {
                    $send = $this->postData(config('app.url_post_konsol_jkttnl'), 'POST', $accessTokenJktTnlStok, $data);

                    $send = json_decode($send, TRUE);
                    if (array_key_exists('status', $send)) {
                        goto selesai;
                    } else {
                        throw new \Exception($send['message']);
                    }
                }
            }
        }


        selesai:
        return true;
    }


    public function http_request(string $url, string $method = 'GET', array $headers = null, array $body = null): string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if (!empty($body)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, ['Content-Type: application/json']));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }
    public function getToken($server, $postRequest)
    {
        $token = $this->http_request(
            $server,
            'POST',
            [
                'Accept: application/json'
            ],
            $postRequest
        );

        return $token;
    }
    public function postData($server, $method, $accessToken, $data)
    {
        $send = $this->http_request(
            $server,
            $method,
            [
                'Authorization: Bearer ' . $accessToken,
                'Accept: application/json',
                'Content-Type: application/json'
            ],
            $data
        );
        return $send;
    }
}
