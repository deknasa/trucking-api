<?php

namespace App\Models;

use App\Services\RunningNumberService;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class PenerimaanStokHeader extends MyModel
{
    use HasFactory;

    protected $table = 'penerimaanstokheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
    public function get()
    {
        $this->setRequestParameters();
        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';
        // dd(request());

        $user_id = auth('api')->user()->id ?? 0;

        $temprole = '##temprole' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprole, function ($table) {
            $table->bigInteger('aco_id')->nullable();
        });

        $queryaco = db::table("useracl")->from(db::raw("useracl a with (readuncommitted)"))
            ->select('a.aco_id')
            ->join(db::raw("penerimaanstok b with (readuncommitted)"), 'a.aco_id', 'b.aco_id')
            ->where('a.user_id', $user_id);

        DB::table($temprole)->insertUsing(['aco_id'], $queryaco);


        $queryrole = db::table("acl")->from(db::raw("acl a with (readuncommitted)"))
            ->select('a.aco_id')
            ->join(db::raw("userrole b with (readuncommitted)"), 'a.role_id', 'b.role_id')
            ->join(db::raw("penerimaanstok c with (readuncommitted)"), 'a.aco_id', 'c.aco_id')
            ->leftjoin(db::raw($temprole . " d "), 'a.aco_id', 'd.aco_id')
            ->where('b.user_id', $user_id)
            ->whereRaw("isnull(d.aco_id,0)=0");

        DB::table($temprole)->insertUsing(['aco_id'], $queryrole);


        $spb = Parameter::where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();
        $po = Parameter::where('grp', 'PO STOK')->where('subgrp', 'PO STOK')->first();
        $spbs = Parameter::where('grp', 'REUSE STOK')->where('subgrp', 'REUSE STOK')->first();
        $spbp = DB::table('penerimaanstok')->where('kodepenerimaan', 'SPBP')->first();
        $do = Parameter::where('grp', 'DO STOK')->where('subgrp', 'DO STOK')->first();
        $pg = Parameter::where('grp', 'PG STOK')->where('subgrp', 'PG STOK')->first();
        $rtb = Parameter::where('grp', 'RETUR STOK')->where('subgrp', 'RETUR STOK')->first();
        $spk = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();

        $query = DB::table($this->table);
        $query = $this->selectColumns($query)

            ->leftJoin('gudang as gudangs', 'penerimaanstokheader.gudang_id', 'gudangs.id')
            ->leftJoin('gudang as dari', 'penerimaanstokheader.gudangdari_id', 'dari.id')
            ->leftJoin('gudang as ke', 'penerimaanstokheader.gudangke_id', 'ke.id')
            ->leftJoin('parameter as statuscetak', 'penerimaanstokheader.statuscetak', 'statuscetak.id')
            ->leftJoin('parameter as statusedit', 'penerimaanstokheader.statusapprovaledit', 'statusedit.id')
            ->leftJoin('penerimaanstok', 'penerimaanstokheader.penerimaanstok_id', 'penerimaanstok.id')
            ->leftJoin('akunpusat', 'penerimaanstokheader.coa', 'akunpusat.coa')
            ->leftJoin('trado', 'penerimaanstokheader.trado_id', 'trado.id')
            ->leftJoin('trado as tradodari ', 'penerimaanstokheader.tradodari_id', 'tradodari.id')
            ->leftJoin('trado as tradoke ', 'penerimaanstokheader.tradoke_id', 'tradoke.id')
            ->leftJoin('gandengan as gandengandari ', 'penerimaanstokheader.gandengandari_id', 'gandengandari.id')
            ->leftJoin('gandengan as gandenganke ', 'penerimaanstokheader.gandenganke_id', 'gandenganke.id')
            ->leftJoin('gandengan as gandengan ', 'penerimaanstokheader.gandenganke_id', 'gandengan.id')
            ->leftJoin('hutangheader', 'penerimaanstokheader.hutang_nobukti', 'hutangheader.nobukti')
            ->leftJoin('pengeluaranstokheader as pengeluaranstok', 'penerimaanstokheader.pengeluaranstok_nobukti', 'pengeluaranstok.nobukti')
            ->leftJoin('penerimaanstokheader as nobuktipenerimaanstok', 'nobuktipenerimaanstok.nobukti', 'penerimaanstokheader.penerimaanstok_nobukti')
            ->leftJoin('penerimaanstokheader as nobuktispb', 'penerimaanstokheader.nobukti', 'nobuktispb.penerimaanstok_nobukti')
            ->leftJoin('supplier', 'penerimaanstokheader.supplier_id', 'supplier.id')
            ->join(db::raw($temprole . " d "), 'penerimaanstok.aco_id', 'd.aco_id');
        if (request()->penerimaanstok_id == $spb->text) {


            // $query->leftJoin('penerimaanstokheader as po', 'penerimaanstokheader.penerimaanstok_nobukti', '=', 'po.nobukti')
            $query->where('penerimaanstokheader.penerimaanstok_id', '=', $po->text)
                ->whereNotIn('penerimaanstokheader.nobukti', function ($query) {
                    $query->select(DB::raw('DISTINCT penerimaanstokheader.penerimaanstok_nobukti'))
                        ->from('penerimaanstokheader')
                        ->whereNotNull('penerimaanstokheader.penerimaanstok_nobukti')
                        ->where('penerimaanstokheader.penerimaanstok_nobukti', '!=', '');
                });
            // return $query->get();
        }

        if (request()->penerimaanstok_id == $spbs->text) {
            $query->leftJoin('penerimaanstokdetail as detail', 'penerimaanstokheader.id', '=', 'detail.penerimaanstokheader_id');
            $query->where('penerimaanstokheader.penerimaanstok_id', '=', $do->text)
                ->where('detail.stok_id', '=', request()->stok_id)
                ->whereNotIn('penerimaanstokheader.nobukti', function ($query) {
                    $query->select(DB::raw('DISTINCT penerimaanstokheader.penerimaanstok_nobukti'))
                        ->from('penerimaanstokheader')
                        ->whereNotNull('penerimaanstokheader.penerimaanstok_nobukti')
                        ->where('penerimaanstokheader.penerimaanstok_nobukti', '!=', '');
                });
            // return $query->get();
        }
        if (request()->penerimaanstok_id == $spbp->id) {
            $query->where('penerimaanstokheader.penerimaanstok_id', '=', $spb->text)
                ->whereNotIn('penerimaanstokheader.nobukti', function ($query) {
                    $query->select(DB::raw('DISTINCT penerimaanstokheader.penerimaanstok_nobukti'))
                        ->from('penerimaanstokheader')
                        ->whereNotNull('penerimaanstokheader.penerimaanstok_nobukti')
                        ->where('penerimaanstokheader.penerimaanstok_nobukti', '!=', '');
                });
        }

        if (request()->penerimaanstok_id == $pg->text) {
            $query->where('penerimaanstokheader.penerimaanstok_id', '=', $pg->text)
                ->whereNotIn('penerimaanstokheader.nobukti', function ($query) {
                    $query->select(DB::raw('DISTINCT penerimaanstokheader.penerimaanstok_nobukti'))
                        ->from('penerimaanstokheader')
                        ->whereNotNull('penerimaanstokheader.penerimaanstok_nobukti')
                        ->where('penerimaanstokheader.penerimaanstok_nobukti', '!=', '');
                });
        }
        if (request()->supplier_id) {
            // $query->leftJoin('penerimaanstokheader as pobeli','penerimaanstokheader.penerimaanstok_nobukti','pobeli.nobukti');
            $query->where('penerimaanstokheader.supplier_id', '=', request()->supplier_id);
            // $query->whereRaw("isnull(pobeli.nobukti,'')=''");
            // dd($query->get());
        }
        if (request()->pengeluaranstok_id == $rtb->text) {
            //jika retur cari penerimaan hanya
            $query->where('penerimaanstokheader.penerimaanstok_id', '=', $spb->text);
        }
        if (request()->pengeluaranstok_id == $spk->text) {
            //jika spk, dan stok_id adalah barang reuse harus ada pg dari tujuan ke gudang sementara untuk diperbaiki
            $query->where('penerimaanstokheader.penerimaanstok_id', '=', $pg->text);
        }
        if (request()->tgldari) {
            $query->whereBetween('penerimaanstokheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if (request()->penerimaanheader_id) {
            $query->where('penerimaanstokheader.penerimaanstok_id', request()->penerimaanheader_id);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(penerimaanstokheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(penerimaanstokheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("penerimaanstokheader.statuscetak", $statusCetak);
        }

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }


    public function getTNLForKlaim($dari, $sampai)
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

            ->get($server . "penerimaanstokheader?limit=0&tgldari=" . $dari . "&tglsampai=" . $sampai);

        $data = $getTrado->json()['data'];

        $class = 'PenerimaanStokHeaderController';
        $user = auth('api')->user()->name;
        $temtabel = 'temppg' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;

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
            $table->string('nobukti', 30)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('penerimaanstok', 50)->nullable();
            $table->string('penerimaanstok_nobukti', 50)->nullable();
            $table->date('tgldariheadernobuktipenerimaanstok')->nullable();
            $table->date('tglsampaiheadernobuktipenerimaanstok')->nullable();
            $table->string('pengeluaranstok_nobukti', 50)->nullable();
            $table->string('gudang', 50)->nullable();
            $table->string('trado', 50)->nullable();
            $table->string('gandengan', 50)->nullable();
            $table->string('tradodari', 50)->nullable();
            $table->string('tradoke', 50)->nullable();
            $table->string('gandengandari', 50)->nullable();
            $table->string('gandenganke', 50)->nullable();
            $table->string('supplier', 255)->nullable();
            $table->string('nobon', 50)->nullable();
            $table->string('hutang_nobukti', 50)->nullable();
            $table->string('gudangdari', 50)->nullable();
            $table->string('gudangke', 50)->nullable();
            $table->string('coa', 50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('gudang_id')->length(11)->nullable();
            $table->integer('gudangdari_id')->length(11)->nullable();
            $table->integer('gudangke_id')->length(11)->nullable();
            $table->integer('penerimaanstok_id')->length(11)->nullable();
            $table->integer('trado_id')->length(11)->nullable();
            $table->integer('tradoke_id')->length(11)->nullable();
            $table->integer('tradodari_id')->length(11)->nullable();
            $table->integer('gandenganke_id')->length(11)->nullable();
            $table->integer('gandengandari_id')->length(11)->nullable();
            $table->integer('gandengan_id')->length(11)->nullable();
            $table->integer('supplier_id')->length(11)->nullable();
        });

        foreach ($data as $row) {

            unset($row['statusformat']);
            unset($row['jumlahcetak']);
            unset($row['statuscetak']);
            unset($row['statusedit']);
            unset($row['parrenttglbukti']);
            unset($row['statuscetak_id']);
            unset($row['statusedit_id']);
            unset($row['tgldariheaderhutangheader']);
            unset($row['tglsampaiheaderhutangheader']);
            unset($row['tgldariheaderpengeluaranstok']);
            unset($row['tglsampaiheaderpengeluaranstok']);
            unset($row['judul']);
            DB::table($temtabel)->insert($row);
        }
        
        return $temtabel;
    }

    public function selectColumns($query)
    {
        $po = Parameter::where('grp', 'PO STOK')->where('subgrp', 'PO STOK')->first();
        $penerimaanstok_nobukti = $this->table . ".penerimaanstok_nobukti";
        $tgldaripenerimaanstok_nobukti = db::raw("cast((format(nobuktipenerimaanstok.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheadernobuktipenerimaanstok");
        $tglsampaipenerimaanstok_nobukti = db::raw("cast(cast(format((cast((format(nobuktipenerimaanstok.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheadernobuktipenerimaanstok");
        if (request()->penerimaanheader_id == $po->text) {

            $penerimaanstok_nobukti = "nobuktispb.nobukti as penerimaanstok_nobukti";
            $tgldaripenerimaanstok_nobukti = db::raw("cast((format(nobuktispb.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheadernobuktipenerimaanstok");
            $tglsampaipenerimaanstok_nobukti = db::raw("cast(cast(format((cast((format(nobuktispb.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheadernobuktipenerimaanstok");
        }

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        return $query->select(
            "$this->table.id",
            "$this->table.nobukti",
            "$this->table.tglbukti",
            "penerimaanstok.kodepenerimaan as penerimaanstok",
            $penerimaanstok_nobukti,
            $tgldaripenerimaanstok_nobukti,
            $tglsampaipenerimaanstok_nobukti,
            "$this->table.pengeluaranstok_nobukti",
            "gudangs.gudang as gudang",
            "trado.kodetrado as trado",
            "gandengan.kodegandengan as gandengan",
            "tradodari.kodetrado as tradodari",
            "tradoke.kodetrado as tradoke",
            "gandengandari.keterangan as gandengandari",
            "gandenganke.keterangan as gandenganke",
            "supplier.namasupplier as supplier",
            "$this->table.nobon",
            "$this->table.hutang_nobukti",
            "dari.gudang as gudangdari",
            "ke.gudang as gudangke",
            "$this->table.statusformat",
            "akunpusat.keterangancoa as coa",
            "$this->table.keterangan",
            "$this->table.modifiedby",
            "$this->table.created_at",
            "$this->table.updated_at",
            "penerimaanstokheader.gudang_id",
            "penerimaanstokheader.gudangdari_id",
            "penerimaanstokheader.gudangke_id",
            "penerimaanstokheader.penerimaanstok_id",
            "penerimaanstokheader.trado_id",
            "penerimaanstokheader.tradoke_id",
            "penerimaanstokheader.tradodari_id",
            "penerimaanstokheader.gandenganke_id",
            "penerimaanstokheader.gandengandari_id",
            "penerimaanstokheader.gandengan_id",
            "penerimaanstokheader.supplier_id",
            "penerimaanstokheader.jumlahcetak",
            "statuscetak.memo as  statuscetak",
            "statusedit.memo as  statusedit",
            "nobuktipenerimaanstok.tglbukti as parrenttglbukti",
            "statuscetak.id as  statuscetak_id",
            "statusedit.id as  statusedit_id",
            db::raw("cast((format(hutangheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderhutangheader"),
            db::raw("cast(cast(format((cast((format(hutangheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderhutangheader"),
            db::raw("cast((format(pengeluaranstok.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpengeluaranstok"),
            db::raw("cast(cast(format((cast((format(pengeluaranstok.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpengeluaranstok"),

            DB::raw("'" . $getJudul->text . "' as judul")
        );
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->nullable();
            $table->unsignedBigInteger('penerimaanstok_id')->nullable();
            $table->string('penerimaanstok_nobukti', 50)->nullable();
            $table->string('pengeluaranstok_nobukti', 50)->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('nobon', 50)->nullable();
            $table->string('hutang_nobukti', 50)->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->unsignedBigInteger('gudang_id')->nullable();
            $table->unsignedBigInteger('gudangdari_id')->nullable();
            $table->unsignedBigInteger('gudangke_id')->nullable();
            $table->string('coa', 50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->increments('position');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $query = DB::table($modelTable);
        $query = $this->select(
            "$modelTable.id",
            "$modelTable.nobukti",
            "$modelTable.tglbukti",
            "$modelTable.penerimaanstok_id",
            "$modelTable.penerimaanstok_nobukti",
            "$modelTable.pengeluaranstok_nobukti",
            "$modelTable.supplier_id",
            "$modelTable.nobon",
            "$modelTable.hutang_nobukti",
            "$modelTable.trado_id",
            "$modelTable.gudang_id",
            "$modelTable.gudangdari_id",
            "$modelTable.gudangke_id",
            "$modelTable.coa",
            "$modelTable.keterangan",
            "$modelTable.statusformat",
            "$modelTable.modifiedby"
        )
            ->leftJoin('gudang as gudangs', 'penerimaanstokheader.gudang_id', 'gudangs.id')
            ->leftJoin('gudang as dari', 'penerimaanstokheader.gudangdari_id', 'dari.id')
            ->leftJoin('gudang as ke', 'penerimaanstokheader.gudangke_id', 'ke.id')
            ->leftJoin('parameter as statuscetak', 'penerimaanstokheader.statuscetak', 'statuscetak.id')
            ->leftJoin('parameter as statusedit', 'penerimaanstokheader.statusapprovaledit', 'statusedit.id')
            ->leftJoin('penerimaanstok', 'penerimaanstokheader.penerimaanstok_id', 'penerimaanstok.id')
            ->leftJoin('trado', 'penerimaanstokheader.trado_id', 'trado.id')
            ->leftJoin('trado as tradodari ', 'penerimaanstokheader.tradodari_id', 'tradodari.id')
            ->leftJoin('trado as tradoke ', 'penerimaanstokheader.tradoke_id', 'tradoke.id')
            ->leftJoin('gandengan as gandengandari ', 'penerimaanstokheader.gandengandari_id', 'gandengandari.id')
            ->leftJoin('gandengan as gandenganke ', 'penerimaanstokheader.gandenganke_id', 'gandenganke.id')
            ->leftJoin('gandengan as gandengan ', 'penerimaanstokheader.gandenganke_id', 'gandengan.id')
            ->leftJoin('supplier', 'penerimaanstokheader.supplier_id', 'supplier.id');
        $query = $this->sort($query);
        $models = $this->filter($query);
        if (request()->tgldariheader) {
            $models->whereBetween('tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        }
        if (request()->penerimaanheader_id) {
            $models->where('penerimaanstok_id', request()->penerimaanstok_id);
        }
        DB::table($temp)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'penerimaanstok_id',
            'penerimaanstok_nobukti',
            'pengeluaranstok_nobukti',
            'supplier_id',
            'nobon',
            'hutang_nobukti',
            'trado_id',
            'gudang_id',
            'gudangdari_id',
            'gudangke_id',
            'coa',
            'keterangan',
            'statusformat',
            'modifiedby',
        ], $models);

        return  $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'grp') {
            return $query
                ->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder'])
                ->orderBy($this->table . '.subgrp', $this->params['sortOrder'])
                ->orderBy($this->table . '.id', $this->params['sortOrder']);
        }

        if ($this->params['sortIndex'] == 'subgrp') {
            return $query
                ->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder'])
                ->orderBy($this->table . '.grp', $this->params['sortOrder'])
                ->orderBy($this->table . '.id', $this->params['sortOrder']);
        }

        if ($this->params['sortIndex'] == 'penerimaanstok') {
            return $query->orderBy('penerimaanstok.kodepenerimaan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'gudang') {
            return $query->orderBy('gudangs.gudang', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'trado') {
            return $query->orderBy('trado.kodetrado', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'supplier') {
            return $query->orderBy('supplier.namasupplier', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'gudangdari') {
            return $query->orderBy('dari.gudang', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'gudangke') {
            return $query->orderBy('ke.gudang', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'coa') {
            return $query->orderBy('akunpusat.keterangancoa', $this->params['sortOrder']);
        }
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'penerimaanstok') {
                            $query = $query->where('penerimaanstok.kodepenerimaan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'gudang') {
                            $query = $query->where('gudangs.gudang', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'trado') {
                            $query = $query->where('trado.kodetrado', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'gandengan') {
                            $query = $query->where('gandengan.kodegandengan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'supplier') {
                            $query = $query->where('supplier.namasupplier', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'gudangdari') {
                            $query = $query->where('dari.gudang', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'gudangke') {
                            $query = $query->where('ke.gudang', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'coa') {
                            $query = $query->where('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'tglbukti') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'penerimaanstok') {
                                $query = $query->orWhere('penerimaanstok.kodepenerimaan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'gudang') {
                                $query = $query->orWhere('gudangs.gudang', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'trado') {
                                $query = $query->orWhere('trado.kodetrado', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'gandengan') {
                                $query = $query->orwhere('gandengan.kodegandengan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'supplier') {
                                $query = $query->orWhere('supplier.namasupplier', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'gudangdari') {
                                $query = $query->orWhere('dari.gudang', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'gudangke') {
                                $query = $query->orWhere('ke.gudang', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coa') {
                                $query = $query->orWhere('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tglbukti') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });
                    //function query
                    // foreach ($this->params['filters']['rules'] as $index => $filters) {
                    //     if ($filters['field'] == 'penerimaanstok_id_not_null') {
                    //         $query = $query->where($this->table . '.penerimaanstok_id', '=', "$filters[data]")->whereRaw(" $this->table.nobukti NOT IN 
                    //             (SELECT DISTINCT $this->table.penerimaanstok_nobukti
                    //             FROM penerimaanstokheader
                    //             WHERE $this->table.penerimaanstok_nobukti IS NOT NULL)
                    //             ");
                    //     }
                    // }

                    break;
                default:

                    break;
            }

            $this->totalRows = $query->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }
        if (request()->cetak && request()->periode) {
            $query->where('penerimaanstokheader.statuscetak', '<>', request()->cetak)
                ->whereYear('penerimaanstokheader.tglbukti', '=', request()->year)
                ->whereMonth('penerimaanstokheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function find($id)
    {
        $this->setRequestParameters();

        $query = DB::table($this->table);
        $query = $this->selectColumns($query)
            ->leftJoin('gudang as gudangs', 'penerimaanstokheader.gudang_id', 'gudangs.id')
            ->leftJoin('gudang as dari', 'penerimaanstokheader.gudangdari_id', 'dari.id')
            ->leftJoin('gudang as ke', 'penerimaanstokheader.gudangke_id', 'ke.id')
            ->leftJoin('parameter as statuscetak', 'penerimaanstokheader.statuscetak', 'statuscetak.id')
            ->leftJoin('parameter as statusedit', 'penerimaanstokheader.statusapprovaledit', 'statusedit.id')
            ->leftJoin('trado as tradodari ', 'penerimaanstokheader.tradodari_id', 'tradodari.id')
            ->leftJoin('trado as tradoke ', 'penerimaanstokheader.tradoke_id', 'tradoke.id')
            ->leftJoin('akunpusat', 'penerimaanstokheader.coa', 'akunpusat.coa')
            ->leftJoin('gandengan as gandengandari ', 'penerimaanstokheader.gandengandari_id', 'gandengandari.id')
            ->leftJoin('gandengan as gandenganke ', 'penerimaanstokheader.gandenganke_id', 'gandenganke.id')
            ->leftJoin('gandengan as gandengan ', 'penerimaanstokheader.gandenganke_id', 'gandengan.id')
            ->leftJoin('penerimaanstok', 'penerimaanstokheader.penerimaanstok_id', 'penerimaanstok.id')
            ->leftJoin('trado', 'penerimaanstokheader.trado_id', 'trado.id')
            ->leftJoin('hutangheader', 'penerimaanstokheader.hutang_nobukti', 'hutangheader.nobukti')
            ->leftJoin('pengeluaranstokheader as pengeluaranstok', 'penerimaanstokheader.pengeluaranstok_nobukti', 'pengeluaranstok.nobukti')
            ->leftJoin('penerimaanstokheader as nobuktipenerimaanstok', 'nobuktipenerimaanstok.nobukti', 'penerimaanstokheader.penerimaanstok_nobukti')
            ->leftJoin('supplier', 'penerimaanstokheader.supplier_id', 'supplier.id');
        $data = $query->where("$this->table.id", $id)->first();


        return $data;
    }

    public function getDetailPengeluaran($id)
    {
        $penerimaan = PenerimaanStokHeader::findOrFail($id);
        $penerimaanstokdetail = DB::table('penerimaanstokdetail')
            ->select(
                "penerimaanstokdetail.penerimaanstokheader_id",
                "penerimaanstokdetail.nobukti",
                "stok.namastok as stok",
                "pengeluaranstokdetail.qty as maximum",
                "penerimaanstokdetail.stok_id",
                "penerimaanstokdetail.qty",
                "penerimaanstokdetail.harga",
                "penerimaanstokdetail.persentasediscount",
                "penerimaanstokdetail.nominaldiscount",
                "penerimaanstokdetail.total",
                "penerimaanstokdetail.keterangan",
                "penerimaanstokdetail.vulkanisirke",
                "penerimaanstokdetail.modifiedby",
            )
            ->leftJoin('penerimaanstokheader', 'penerimaanstokdetail.penerimaanstokheader_id', 'penerimaanstokheader.id')
            ->leftJoin('pengeluaranstokdetail', 'penerimaanstokheader.pengeluaranstok_nobukti', 'pengeluaranstokdetail.nobukti')
            ->leftJoin("stok", "penerimaanstokdetail.stok_id", "stok.id")
            ->where('penerimaanstokdetail.penerimaanstokheader_id', $penerimaan->id)
            ->whereRaw('penerimaanstokdetail.stok_id = pengeluaranstokdetail.stok_id')
            ->get();

        $data = $penerimaanstokdetail;
        return $data;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function processStore(array $data): PenerimaanStokHeader
    {
        $idpenerimaan = $data['penerimaanstok_id'];
        $fetchFormat =  PenerimaanStok::where('id', $idpenerimaan)->first();
        $statusformat = $fetchFormat->format;
        $fetchGrp = Parameter::where('id', $statusformat)->first();
        $group = $fetchGrp->grp;
        $subGroup = $fetchGrp->subgrp;
        $statusformat = $fetchFormat->format;
        $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
        $jamBatas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', 'JAMBATASAPPROVAL')->where('subgrp', 'JAMBATASAPPROVAL')->first();
        $tglbatasedit = date('Y-m-d H:i:s', strtotime(date('Y-m-d') . ' ' . $jamBatas->text));
        $gudangkantor =  Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first()->text;
        $gudangsementara =  Parameter::where('grp', 'GUDANG SEMENTARA')->where('subgrp', 'GUDANG SEMENTARA')->first()->text;


        $spb = Parameter::where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();
        $pg = Parameter::where('grp', 'PG STOK')->where('subgrp', 'PG STOK')->first();
        $do = Parameter::where('grp', 'DO STOK')->where('subgrp', 'DO STOK')->first();
        $spbs = Parameter::where('grp', 'REUSE STOK')->where('subgrp', 'REUSE STOK')->first();
        $pst = Parameter::where('grp', 'PST STOK')->where('subgrp', 'PST STOK')->first();
        $pspk = Parameter::where('grp', 'PSPK STOK')->where('subgrp', 'PSPK STOK')->first();
        $korv = DB::table('penerimaanstok')->where('kodepenerimaan', 'KORV')->first();
        $spbp = DB::table('penerimaanstok')->where('kodepenerimaan', 'SPBP')->first();

        $datahitungstok = PenerimaanStok::select('statushitungstok as statushitungstok_id')->where('format', '=', $statusformat)->first();
        $statushitungstok = Parameter::where('grp', 'STATUS HITUNG STOK')->where('text', 'HITUNG STOK')->first();

        if ($data['penerimaanstok_id'] == $spb->text) {
            $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
            $data['gudang_id'] = $gudangkantor->text;
        }
        if ($data['penerimaanstok_id'] == $pst->text) {
            $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
            $data['gudang_id'] = $gudangkantor->text;
        }
        if ($data['penerimaanstok_id'] == $pspk->text) {
            $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
            $data['gudang_id'] = $gudangkantor->text;
        }
        if ($data['penerimaanstok_id'] == $korv->id) {
            $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
            $data['gudang_id'] = $gudangkantor->text;
        }
        $gudangdari_id = $data['gudangdari_id'];
        $gudangke_id = $data['gudangke_id'];
        $tradodari_id = $data['tradodari_id'];
        $tradoke_id = $data['tradoke_id'];
        $gandengandari_id = $data['gandengandari_id'];
        $gandenganke_id = $data['gandenganke_id'];

        if ($data['penerimaanstok_id'] !== $pg->text) {
            $statuspindahgudang = Parameter::where('grp', 'STATUS PINDAH GUDANG')->where('text', 'BUKAN PINDAH GUDANG')->first();
            if ($data['penerimaanstok_id'] === $do->text) {
                $statuspindahgudang = Parameter::where('grp', 'STATUS PINDAH GUDANG')->where('text', 'GUDANG KE GUDANG')->first();
                $gudangdari_id = Gudang::where('gudang', 'GUDANG SEMENTARA')->first()->id;
                $gudangke_id = Gudang::where('gudang', 'GUDANG PIHAK III')->first()->id;
            }
            if ($data['penerimaanstok_id'] === $spbs->text) {
                $statuspindahgudang = Parameter::where('grp', 'STATUS PINDAH GUDANG')->where('text', 'GUDANG KE GUDANG')->first();
                $gudangdari_id = Gudang::where('gudang', 'GUDANG PIHAK III')->first()->id;
                $gudangke_id = Gudang::where('gudang', 'GUDANG KANTOR')->first()->id;
            }
            // if ($data['penerimaanstok_id'] === $pst->text) {
            //     $pengeluaranStokHeader = PengeluaranStokHeader::where('nobukti',$data['pengeluaranstok_nobukti'])->first();
            //     $dari = PenerimaanStokDetail::persediaan($pengeluaranStokHeader->gudang_id,$pengeluaranStokHeader->trado_id,$pengeluaranStokHeader->gandengan_id);
            //     $statuspindahgudang = Parameter::where('grp', 'STATUS PINDAH GUDANG')->where("text", $dari['nama']." ke GUDANG")->first();
            //     dd($pengeluaranStokHeader);
            // }

        } else {
            $dari = PenerimaanStokDetail::persediaan($data['gudangdari_id'], $data['tradodari_id'], $data['gandengandari_id']);
            $ke = PenerimaanStokDetail::persediaan($data['gudangke_id'], $data['tradoke_id'], $data['gandenganke_id']);
            $statuspindahgudang = Parameter::where('grp', 'STATUS PINDAH GUDANG')->where("text", $dari['nama'] . " ke " . $ke['nama'])->first();
        }

        $penerimaanStokHeader = new PenerimaanStokHeader();
        $penerimaanStokHeader->tglbukti                 = date('Y-m-d', strtotime($data['tglbukti']));
        $penerimaanStokHeader->penerimaanstok_nobukti   = ($data['penerimaanstok_nobukti'] == null) ? "" : $data['penerimaanstok_nobukti'];
        $penerimaanStokHeader->pengeluaranstok_nobukti  = ($data['pengeluaranstok_nobukti'] == null) ? "" : $data['pengeluaranstok_nobukti'];
        $penerimaanStokHeader->nobon                    = ($data['nobon'] == null) ? "" : $data['nobon'];
        $penerimaanStokHeader->keterangan               = $data['keterangan'];
        $penerimaanStokHeader->coa                      = ($data['coa'] == null) ? "" : $data['coa'];
        $penerimaanStokHeader->statusformat             = $statusformat;
        $penerimaanStokHeader->penerimaanstok_id        = $data['penerimaanstok_id'];
        $penerimaanStokHeader->gudang_id                = $data['gudang_id'];
        $penerimaanStokHeader->trado_id                 = $data['trado_id'];
        $penerimaanStokHeader->gandengan_id             = $data['gandengan_id'];
        $penerimaanStokHeader->supplier_id              = $data['supplier_id'];
        $penerimaanStokHeader->gudangdari_id            = $gudangdari_id;
        $penerimaanStokHeader->gudangke_id              = $gudangke_id;
        $penerimaanStokHeader->tradodari_id             = $tradodari_id;
        $penerimaanStokHeader->tradoke_id               = $tradoke_id;
        $penerimaanStokHeader->gandengandari_id         = $gandengandari_id;
        $penerimaanStokHeader->gandenganke_id           = $gandenganke_id;
        $penerimaanStokHeader->statuspindahgudang       = ($statuspindahgudang == null) ? "" : $statuspindahgudang->id;
        $penerimaanStokHeader->modifiedby               = auth('api')->user()->name;
        $penerimaanStokHeader->info = html_entity_decode(request()->info);
        $penerimaanStokHeader->statuscetak              = $statusCetak->id;
        $penerimaanStokHeader->tglbatasedit             = $tglbatasedit;
        $data['sortname']                               = $data['sortname'] ?? 'id';
        $data['sortorder']                              = $data['sortorder'] ?? 'asc';

        $penerimaanStokHeader->nobukti                  = (new RunningNumberService)->get($group, $subGroup, $penerimaanStokHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$penerimaanStokHeader->save()) {
            throw new \Exception("Error storing penerimaan Stok Header.");
        }

        $masukgudang_id = 0;
        $masuktrado_id = 0;
        $masukgandengan_id = 0;
        $keluargudang_id = 0;
        $keluartrado_id = 0;
        $keluargandengan_id = 0;

        $penerimaanstok_id = $data['penerimaanstok_id'] ?? 0;

        $urutfifo = db::table("penerimaanstok")->from(db::raw("penerimaanstok as a with (readuncommitted)"))
            ->select('a.urutfifo')->where('a.id', $penerimaanstok_id)->first()->urutfifo ?? 0;


        // dd($gudangke_id);
        $masukgudang_id = $data['gudang_id'] ?? 0;
        $masuktrado_id = $data['trado_id'] ?? 0;
        $masukgandengan_id = $data['gandengan_id'] ?? 0;
        if ($gudangke_id != 0) {
            $masukgudang_id = $gudangke_id ?? 0;
        }
        if ($gudangdari_id != 0) {
            $keluargudang_id = $gudangdari_id ?? 0;
        }


        if ($tradoke_id != 0) {
            $masuktrado_id = $gudangke_id ?? 0;
        }

        if ($tradodari_id != 0) {
            $keluartrado_id = $tradodari_id ?? 0;
        }


        if ($gandenganke_id != 0) {
            $masukgandengan_id = $gandenganke_id ?? 0;
        }

        if ($gandengandari_id != 0) {
            $keluargandengan_id = $gandengandari_id ?? 0;
        }

        // if ($penerimaanstok_id == 1) {
        //     $masukgudang_id = $data['gudangke_id'] ?? 0;
        // }
        // if ($penerimaanstok_id == 6) {
        //     $masukgudang_id = $data['gudangke_id'] ?? 0;
        // }


        /*STORE DETAIL*/
        $penerimaanStokDetails = [];
        $totalharga = 0;
        $detaildata = [];
        $tgljatuhtempo = [];
        $keterangan_detail = [];
        for ($i = 0; $i < count($data['detail_harga']); $i++) {
            $ksqty = $data['detail_qty'][$i] ?? 0;
            $ksnilai = $data['totalItem'][$i] ?? 0;
            $penerimaanStokDetail = (new PenerimaanStokDetail())->processStore($penerimaanStokHeader, [
                "penerimaanstokheader_id" => $penerimaanStokHeader->id,
                "nobukti" => $penerimaanStokHeader->nobukti,
                "stok_id" => $data['detail_stok_id'][$i],
                "qty" => $data['detail_qty'][$i],
                "harga" => $data['detail_harga'][$i],
                "totalItem" => $data['totalItem'][$i],
                "totalsebelum" => $data['totalsebelum'][$i] ?? 0,
                "persentasediscount" => $data['detail_persentasediscount'][$i],
                "vulkanisirke" => $data['detail_vulkanisirke'][$i],
                "detail_keterangan" => $data['detail_keterangan'][$i],
                "detail_penerimaanstoknobukti" => $data['detail_penerimaanstoknobukti'][$i],
            ]);
            // dd($penerimaanstok_id);
            if ($penerimaanstok_id != 2 && $penerimaanstok_id != 10  && $penerimaanstok_id != 11) {
                if ($masukgudang_id != 0 || $masuktrado_id != 0  || $masukgandengan_id != 0) {
                    // dd('test');
                    $kartuStok = (new KartuStok())->processStore([
                        "gudang_id" => $masukgudang_id,
                        "trado_id" => $masuktrado_id,
                        "gandengan_id" => $masukgandengan_id,
                        "stok_id" => $data['detail_stok_id'][$i],
                        "nobukti" => $penerimaanStokHeader->nobukti,
                        "tglbukti" => date('Y-m-d', strtotime($data['tglbukti'])),
                        "qtymasuk" => $ksqty ?? 0,
                        "nilaimasuk" => $ksnilai ?? 0,
                        "qtykeluar" => 0,
                        "nilaikeluar" => 0,
                        "urutfifo" => $urutfifo,
                    ]);
                }

                if ($keluargudang_id != 0 || $keluartrado_id != 0  || $keluargandengan_id != 0) {
                    $kartuStok = (new KartuStok())->processStore([
                        "gudang_id" => $keluargudang_id,
                        "trado_id" => $keluartrado_id,
                        "gandengan_id" => $keluargandengan_id,
                        "stok_id" => $data['detail_stok_id'][$i],
                        "nobukti" => $penerimaanStokHeader->nobukti,
                        "tglbukti" => date('Y-m-d', strtotime($data['tglbukti'])),
                        "qtymasuk" => 0,
                        "nilaimasuk" => 0,
                        "qtykeluar" => $ksqty ?? 0,
                        "nilaikeluar" => $ksnilai ?? 0,
                        "urutfifo" => $urutfifo,
                    ]);
                }
            }

            // dd('test');


            if ($data['penerimaanstok_id'] == $spbp->id) {
                $penambahanNilai = (new PenerimaanStokPenambahanNilai())->processStore($penerimaanStokHeader, [
                    "penerimaanstokheader_id" => $penerimaanStokHeader->id,
                    "nobukti" => $penerimaanStokHeader->nobukti,
                    "stok_id" => $data['detail_stok_id'][$i],
                    "qty" => $data['detail_qty'][$i],
                    "harga" => $data['detail_harga'][$i],
                    "penerimaanstok_nobukti" => $penerimaanStokHeader->penerimaanstok_nobukti,
                ]);
            }

            if (($gudangdari_id == $gudangkantor) && ($gudangke_id == $gudangsementara)) {

                $datadetailfifo = [
                    "penerimaanstokheader_id" => $penerimaanStokHeader->id,
                    "penerimaanstok_id" => $data['penerimaanstok_id'],
                    "nobukti" => $penerimaanStokHeader->nobukti,
                    "stok_id" => $data['detail_stok_id'][$i],
                    "gudang_id" => $gudangdari_id,
                    "tglbukti" => $data['tglbukti'],
                    "qty" => $data['detail_qty'][$i],
                    "modifiedby" => auth('api')->user()->name,
                    "keterangan" => $data['keterangan'] ?? '',
                    "detail_keterangan" => $data['detail_keterangan'][$i] ?? '',
                    "statusformat" => $statusformat,
                ];

                (new PenerimaanStokDetailFifo())->processStore($penerimaanStokHeader, $datadetailfifo);
            }



            if ($data['penerimaanstok_id'] == $spb->text || $data['penerimaanstok_id'] == $spbs->text) {
                // $totalsat = ($data['detail_qty'][$i] * $data['detail_harga'][$i]);
                $totalsat = $data['totalItem'][$i];
                $totalharga += $totalsat;
                $detaildata[] = $totalsat;
                $tgljatuhtempo[] =  $data['tglbukti'];
                $keterangan_detail[] =  $data['detail_keterangan'][$i];
            }
            $penerimaanStokDetails[] = $penerimaanStokDetail->toArray();
        }



        /*STORE HUTANG IF SPB*/
        if ($data['penerimaanstok_id'] == $spb->text || $data['penerimaanstok_id'] == $spbs->text) {


            $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL HUTANG PEMBELIAN STOK')->where('subgrp', 'DEBET')->first();
            $memoDebet = json_decode($getCoaDebet->memo, true);

            $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL HUTANG PEMBELIAN STOK')->where('subgrp', 'KREDIT')->first();
            $memoKredit = json_decode($getCoaKredit->memo, true);

            $hutangRequest = [
                'proseslain' => 'PEMBELIAN STOK',
                'postingdari' => 'PENERIMAAN STOK PEMBELIAN',
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'coa' => $memoDebet['JURNAL'],
                'supplier_id' => ($data['supplier_id'] == null) ? "" : $data['supplier_id'],
                'modifiedby' => auth('api')->user()->name,
                'total' => $totalharga,
                'coadebet' => $memoDebet['JURNAL'],
                'coakredit' => $memoKredit['JURNAL'],
                'tgljatuhtempo' => $tgljatuhtempo,
                'total_detail' => $detaildata,
                'keterangan_detail' => $keterangan_detail,
            ];

            $hutangHeader = (new HutangHeader())->processStore($hutangRequest);
            $penerimaanStokHeader->hutang_nobukti = $hutangHeader->nobukti;
            $penerimaanStokHeader->save();
        } else if (($data['penerimaanstok_id'] == $pst->text) || ($data['penerimaanstok_id'] == $pspk->text)) {

            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

            $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'DEBET')->first();
            $memokredit = json_decode($getCoaKredit->memo, true);
            $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'KREDIT')->first();
            $memo = json_decode($getCoaDebet->memo, true);

            for ($i = 0; $i < count($data['detail_harga']); $i++) {
                // $totalsat = ($data['detail_qty'][$i] * $data['detail_harga'][$i]);
                $totalsat = $data['totalItem'][$i];
                $coakredit_detail[] = $memokredit['JURNAL'];
                $coadebet_detail[] = $memo['JURNAL'];
                $nominal_detail[] = $totalsat;
                $keterangan_detail[] = $data['detail_keterangan'][$i];
            }

            /*STORE JURNAL*/
            $jurnalRequest = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $penerimaanStokHeader->nobukti,
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'postingdari' => "ENTRY PENERIMAAN STOK",
                'statusapproval' => $statusApproval->id,
                'userapproval' => "",
                'tglapproval' => "",
                'modifiedby' => auth('api')->user()->name,
                'statusformat' => "0",
                'coakredit_detail' => $coakredit_detail,
                'coadebet_detail' => $coadebet_detail,
                'nominal_detail' => ceil($nominal_detail),
                'keterangan_detail' => $keterangan_detail
            ];
            $jurnalUmumHeader = (new JurnalUmumHeader())->processStore($jurnalRequest);
        }

        $penerimaanStokHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($penerimaanStokHeader->getTable()),
            'postingdari' => strtoupper('ENTRY penerimaan Stok Header'),
            'idtrans' => $penerimaanStokHeader->id,
            'nobuktitrans' => $penerimaanStokHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $penerimaanStokHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($penerimaanStokDetail->getTable()),
            'postingdari' => strtoupper('ENTRY penerimaan Stok Detail'),
            'idtrans' =>  $penerimaanStokHeaderLogTrail->id,
            'nobuktitrans' => $penerimaanStokHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $penerimaanStokDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);

        return $penerimaanStokHeader;
    }

    public function processUpdate(PenerimaanStokHeader $penerimaanStokHeader, array $data): PenerimaanStokHeader
    {
        /*STORE HEADER*/

        $idpenerimaan = $data['penerimaanstok_id'];
        $fetchFormat =  PenerimaanStok::where('id', $idpenerimaan)->first();
        $statusformat = $fetchFormat->format;

        $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $spb = Parameter::where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();
        $pg = Parameter::where('grp', 'PG STOK')->where('subgrp', 'PG STOK')->first();
        $do = Parameter::where('grp', 'DO STOK')->where('subgrp', 'DO STOK')->first();
        $spbs = Parameter::where('grp', 'REUSE STOK')->where('subgrp', 'REUSE STOK')->first();
        $kor = Parameter::where('grp', 'KOR STOK')->where('subgrp', 'KOR STOK')->first();
        $pst = Parameter::where('grp', 'PST STOK')->where('subgrp', 'PST STOK')->first();
        $pspk = Parameter::where('grp', 'PSPK STOK')->where('subgrp', 'PSPK STOK')->first();
        $korv = DB::table('penerimaanstok')->where('kodepenerimaan', 'KORV')->first();
        $spbp = DB::table('penerimaanstok')->where('kodepenerimaan', 'SPBP')->first();

        $datahitungstok = PenerimaanStok::select('statushitungstok as statushitungstok_id')->where('format', '=', $statusformat)->first();
        $statushitungstok = Parameter::where('grp', 'STATUS HITUNG STOK')->where('text', 'HITUNG STOK')->first();

        if ($data['penerimaanstok_id'] == $spb->text) {
            $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
            $data['gudang_id'] = $gudangkantor->text;
        }
        if ($data['penerimaanstok_id'] == $pst->text) {
            $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
            $data['gudang_id'] = $gudangkantor->text;
        }
        if ($data['penerimaanstok_id'] == $pspk->text) {
            $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
            $data['gudang_id'] = $gudangkantor->text;
        }

        $gudangdari_id = $data['gudangdari_id'];
        $gudangke_id = $data['gudangke_id'];
        $tradodari_id = $data['tradodari_id'];
        $tradoke_id = $data['tradoke_id'];
        $gandengandari_id = $data['gandengandari_id'];
        $gandenganke_id = $data['gandenganke_id'];




        if ($data['penerimaanstok_id'] !== $pg->text) {
            $statuspindahgudang = Parameter::where('grp', 'STATUS PINDAH GUDANG')->where('text', 'BUKAN PINDAH GUDANG')->first();
            if ($data['penerimaanstok_id'] === $do->text) {
                $statuspindahgudang = Parameter::where('grp', 'STATUS PINDAH GUDANG')->where('text', 'GUDANG KE GUDANG')->first();
                $gudangdari_id = Gudang::where('gudang', 'GUDANG SEMENTARA')->first()->id;
                $gudangke_id = Gudang::where('gudang', 'GUDANG PIHAK III')->first()->id;
            }
            if ($data['penerimaanstok_id'] === $spbs->text) {
                $statuspindahgudang = Parameter::where('grp', 'STATUS PINDAH GUDANG')->where('text', 'GUDANG KE GUDANG')->first();
                $gudangdari_id = Gudang::where('gudang', 'GUDANG PIHAK III')->first()->id;
                $gudangke_id = Gudang::where('gudang', 'GUDANG KANTOR')->first()->id;
            }
        } else {

            $dari = PenerimaanStokDetail::persediaan($data['gudangdari_id'], $data['tradodari_id'], $data['gandengandari_id']);

            $ke = PenerimaanStokDetail::persediaan($data['gudangke_id'], $data['tradoke_id'], $data['gandenganke_id']);
            $statuspindahgudang = Parameter::where('grp', 'STATUS PINDAH GUDANG')->where("text", $dari['nama'] . " ke " . $ke['nama'])->first();
        }

        $penerimaanStokHeader->penerimaanstok_nobukti   = ($data['penerimaanstok_nobukti'] == null) ? "" : $data['penerimaanstok_nobukti'];
        $penerimaanStokHeader->pengeluaranstok_nobukti  = ($data['pengeluaranstok_nobukti'] == null) ? "" : $data['pengeluaranstok_nobukti'];
        $penerimaanStokHeader->nobon                    = ($data['nobon'] == null) ? "" : $data['nobon'];
        $penerimaanStokHeader->keterangan               = ($data['keterangan'] == null) ? "" : $data['keterangan'];
        $penerimaanStokHeader->coa                      = ($data['coa'] == null) ? "" : $data['coa'];
        $penerimaanStokHeader->statusformat             = $statusformat;
        $penerimaanStokHeader->penerimaanstok_id        = ($data['penerimaanstok_id'] == null) ? "" : $data['penerimaanstok_id'];
        $penerimaanStokHeader->gudang_id                = $data['gudang_id'];
        $penerimaanStokHeader->trado_id                 = $data['trado_id'];
        $penerimaanStokHeader->supplier_id              = $data['supplier_id'];
        $penerimaanStokHeader->gandengan_id             = $data['gandengan_id'];
        $penerimaanStokHeader->gudangdari_id            = $gudangdari_id;
        $penerimaanStokHeader->gudangke_id              = $gudangke_id;
        $penerimaanStokHeader->tradodari_id             = $tradodari_id;
        $penerimaanStokHeader->tradoke_id               = $tradoke_id;
        $penerimaanStokHeader->gandengandari_id         = $gandengandari_id;
        $penerimaanStokHeader->gandenganke_id           = $gandenganke_id;
        $penerimaanStokHeader->statuspindahgudang       = ($statuspindahgudang == null) ? "" : $statuspindahgudang->id;
        $penerimaanStokHeader->modifiedby               = auth('api')->user()->name;
        $penerimaanStokHeader->info = html_entity_decode(request()->info);
        $penerimaanStokHeader->statuscetak              = $statusCetak->id;
        $data['sortname']                               = $data['sortname'] ?? 'id';
        $data['sortorder']                              = $data['sortorder'] ?? 'asc';

        if (!$penerimaanStokHeader->save()) {
            throw new \Exception("Error updating Penerimaan Stok header.");
        }

        // dd('test');
        $penerimaanStokHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($penerimaanStokHeader->getTable()),
            'postingdari' => strtoupper('edit penerimaan Stok Header'),
            'idtrans' => $penerimaanStokHeader->id,
            'nobuktitrans' => $penerimaanStokHeader->nobukti,
            'aksi' => strtoupper('edit'),
            'datajson' => $penerimaanStokHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        /*RETURN STOK PENERIMAAN*/
        if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {
            $datadetail = PenerimaanStokDetail::select('stok_id', 'qty')->where('penerimaanstokheader_id', '=', $penerimaanStokHeader->id)->get();
            (new PenerimaanStokDetail())->returnStokPenerimaan($penerimaanStokHeader->id);
        }
        // dd('test');
        if ($data['penerimaanstok_id'] == $korv->id) {
            (new PenerimaanStokDetail())->returnVulkanisir($penerimaanStokHeader->id);
        }


        /*DELETE EXISTING DETAIL*/
        $penerimaanStokDetail = PenerimaanStokDetail::where('penerimaanstokheader_id', $penerimaanStokHeader->id)->lockForUpdate()->delete();
        $kartuStok = kartuStok::where('nobukti', $penerimaanStokHeader->nobukti)->lockForUpdate()->delete();
        $PenambahanNilai = PenerimaanStokPenambahanNilai::where('penerimaanstokheader_id', $penerimaanStokHeader->id)->lockForUpdate()->delete();

        /*STORE DETAIL*/
        $penerimaanStokDetails = [];
        $totalharga = 0;
        $detaildata = [];
        $tgljatuhtempo = [];
        $keterangan_detail = [];

        // (new KartuStok())->processDestroy($penerimaanStokHeader->nobukti);

        $masukgudang_id = 0;
        $masuktrado_id = 0;
        $masukgandengan_id = 0;
        $keluargudang_id = 0;
        $keluartrado_id = 0;
        $keluargandengan_id = 0;

        $kartuStok = kartuStok::where('nobukti', $penerimaanStokHeader->nobukti)->lockForUpdate()->delete();

        $penerimaanstok_id = $data['penerimaanstok_id'] ?? 0;

        $urutfifo = db::table("penerimaanstok")->from(db::raw("penerimaanstok as a with (readuncommitted)"))
            ->select('a.urutfifo')->where('a.id', $penerimaanstok_id)->first()->urutfifo ?? 0;



        $masukgudang_id = $data['gudang_id'] ?? 0;
        $masuktrado_id = $data['trado_id'] ?? 0;
        $masukgandengan_id = $data['gandengan_id'] ?? 0;

        if ($gudangke_id != 0) {
            $masukgudang_id = $gudangke_id ?? 0;
        }
        if ($gudangdari_id != 0) {
            $keluargudang_id = $gudangdari_id ?? 0;
        }


        if ($tradoke_id != 0) {
            $masuktrado_id = $gudangke_id ?? 0;
        }

        if ($tradodari_id != 0) {
            $keluartrado_id = $tradodari_id ?? 0;
        }


        if ($gandenganke_id != 0) {
            $masukgandengan_id = $gandenganke_id ?? 0;
        }

        if ($gandengandari_id != 0) {
            $keluargandengan_id = $gandengandari_id ?? 0;
        }



        for ($i = 0; $i < count($data['detail_harga']); $i++) {

            $ksqty = $data['detail_qty'][$i] ?? 0;
            $ksnilai = $data['totalItem'][$i] ?? 0;

            $penerimaanStokDetail = (new PenerimaanStokDetail())->processStore($penerimaanStokHeader, [
                "penerimaanstokheader_id" => $penerimaanStokHeader->id,
                "nobukti" => $penerimaanStokHeader->nobukti,
                "stok_id" => $data['detail_stok_id'][$i],
                "qty" => $data['detail_qty'][$i],
                "harga" => $data['detail_harga'][$i],
                "totalItem" => $data['totalItem'][$i],
                "totalsebelum" => $data['totalsebelum'][$i] ?? 0,
                "persentasediscount" => $data['detail_persentasediscount'][$i],
                "vulkanisirke" => $data['detail_vulkanisirke'][$i],
                "detail_keterangan" => $data['detail_keterangan'][$i],
                "detail_penerimaanstoknobukti" => $data['detail_penerimaanstoknobukti'][$i],
            ]);

            if ($penerimaanstok_id != 2 && $penerimaanstok_id != 10  && $penerimaanstok_id != 11) {
                if ($masukgudang_id != 0 || $masuktrado_id != 0  || $masukgandengan_id != 0) {
                    // dd($data['detail_qty'][$i]);
                    $kartuStok = (new KartuStok())->processStore([
                        "gudang_id" => $masukgudang_id,
                        "trado_id" => $masuktrado_id,
                        "gandengan_id" => $masukgandengan_id,
                        "stok_id" => $data['detail_stok_id'][$i],
                        "nobukti" => $penerimaanStokHeader->nobukti,
                        "tglbukti" => date('Y-m-d', strtotime($data['tglbukti'])),
                        "qtymasuk" => $ksqty ?? 0,
                        "nilaimasuk" => $ksnilai ?? 0,
                        "qtykeluar" => 0,
                        "nilaikeluar" => 0,
                        "urutfifo" => $urutfifo,
                    ]);
                }

                if ($keluargudang_id != 0 || $keluartrado_id != 0  || $keluargandengan_id != 0) {
                    $kartuStok = (new KartuStok())->processStore([
                        "gudang_id" => $keluargudang_id,
                        "trado_id" => $keluartrado_id,
                        "gandengan_id" => $keluargandengan_id,
                        "stok_id" => $data['detail_stok_id'][$i],
                        "nobukti" => $penerimaanStokHeader->nobukti,
                        "tglbukti" => date('Y-m-d', strtotime($data['tglbukti'])),
                        "qtymasuk" => 0,
                        "nilaimasuk" => 0,
                        "qtykeluar" => $ksqty ?? 0,
                        "nilaikeluar" => $ksnilai ?? 0,
                        "urutfifo" => $urutfifo,
                    ]);
                }
            }



            if ($data['penerimaanstok_id'] == $spbp->id) {
                $penambahanNilai = (new PenerimaanStokPenambahanNilai())->processStore($penerimaanStokHeader, [
                    "penerimaanstokheader_id" => $penerimaanStokHeader->id,
                    "nobukti" => $penerimaanStokHeader->nobukti,
                    "stok_id" => $data['detail_stok_id'][$i],
                    "qty" => $data['detail_qty'][$i],
                    "harga" => $data['detail_harga'][$i],
                    "penerimaanstok_nobukti" => $penerimaanStokHeader->penerimaanstok_nobukti,
                ]);
            }

            if ($data['penerimaanstok_id'] == $spb->text || $data['penerimaanstok_id'] == $spbs->text) {
                // $totalsat = ($data['detail_qty'][$i] * $data['detail_harga'][$i]);
                $totalsat = $data['totalItem'][$i];
                $totalharga += $totalsat;
                $detaildata[] = $totalsat;
                $tgljatuhtempo[] =  $data['tglbukti'];
                $keterangan_detail[] =  $data['detail_keterangan'][$i];
            }
            $penerimaanStokDetails[] = $penerimaanStokDetail->toArray();
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($penerimaanStokDetail->getTable()),
            'postingdari' => strtoupper('update penerimaan Stok Detail'),
            'idtrans' =>  $penerimaanStokHeaderLogTrail->id,
            'nobuktitrans' => $penerimaanStokHeader->nobukti,
            'aksi' => strtoupper('edit'),
            'datajson' => $penerimaanStokDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);

        /*UPDATE HUTANG IF SPB*/
        if ($data['penerimaanstok_id'] == $spb->text || $data['penerimaanstok_id'] == $spbs->text) {

            $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL HUTANG PEMBELIAN STOK')->where('subgrp', 'DEBET')->first();
            $memoDebet = json_decode($getCoaDebet->memo, true);

            $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL HUTANG PEMBELIAN STOK')->where('subgrp', 'KREDIT')->first();
            $memoKredit = json_decode($getCoaKredit->memo, true);


            $hutangRequest = [
                'proseslain' => 'PEMBELIAN STOK',
                'postingdari' => 'PENERIMAAN STOK PEMBELIAN',
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'coa' => $memoDebet['JURNAL'],
                'supplier_id' => ($data['supplier_id'] == null) ? "" : $data['supplier_id'],
                'modifiedby' => auth('api')->user()->name,
                'total' => $totalharga,
                'coadebet' => $memoDebet['JURNAL'],
                'coakredit' => $memoKredit['JURNAL'],
                'tgljatuhtempo' => $tgljatuhtempo,
                'total_detail' => $detaildata,
                'keterangan_detail' => $keterangan_detail,
            ];
            $hutangHeader = HutangHeader::where('nobukti', $penerimaanStokHeader->hutang_nobukti)->first();
            (new HutangHeader())->processUpdate($hutangHeader, $hutangRequest);
        } else if (($data['penerimaanstok_id'] == $pst->text) || $data['penerimaanstok_id'] == $pspk->text) {
            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

            $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'DEBET')->first();
            $memokredit = json_decode($getCoaKredit->memo, true);
            $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PEMAKAIAN STOK')->where('subgrp', 'KREDIT')->first();
            $memo = json_decode($getCoaDebet->memo, true);

            for ($i = 0; $i < count($data['detail_harga']); $i++) {
                // $totalsat = ($data['detail_qty'][$i] * $data['detail_harga'][$i]);
                $totalsat = $data['totalItem'][$i];
                $coakredit_detail[] = $memokredit['JURNAL'];
                $coadebet_detail[] = $memo['JURNAL'];
                $nominal_detail[] = $totalsat;
                $keterangan_detail[] = $data['detail_keterangan'][$i];
            }

            /*STORE JURNAL*/
            $jurnalRequest = [
                'tanpaprosesnobukti' => 1,
                'postingdari' => "EDIT PENERIMAAN STOK",
                'coakredit_detail' => $coakredit_detail,
                'coadebet_detail' => $coadebet_detail,
                'nominal_detail' => ceil($nominal_detail),
                'keterangan_detail' => $keterangan_detail
            ];
            $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $penerimaanStokHeader->nobukti)->first();
            $newJurnal = new JurnalUmumHeader();
            $newJurnal = $newJurnal->find($getJurnal->id);
            $jurnalumumHeader = (new JurnalUmumHeader())->processUpdate($newJurnal, $jurnalRequest);
        }

        return $penerimaanStokHeader;
    }

    public function processDestroy($id): PenerimaanStokHeader
    {

        $penerimaanStokHeader = PenerimaanStokHeader::findOrFail($id);
        $dataHeader =  $penerimaanStokHeader->toArray();
        $datahitungstok = PenerimaanStok::select('statushitungstok as statushitungstok_id')->where('format', '=', $penerimaanStokHeader->statusformat)->first();
        $penerimaanStokDetail = PenerimaanStokDetail::where('penerimaanstokheader_id', '=', $penerimaanStokHeader->id)->get();
        $dataDetail = $penerimaanStokDetail->toArray();
        $statushitungstok = Parameter::where('grp', 'STATUS HITUNG STOK')->where('text', 'HITUNG STOK')->first();
        /*RETURN STOK PENERIMAAN*/
        if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {
            $datadetail = PenerimaanStokDetail::select('stok_id', 'qty')->where('penerimaanstokheader_id', '=', $penerimaanStokHeader->id)->get();
            (new PenerimaanStokDetail())->returnStokPenerimaan($penerimaanStokHeader->id);
        }
        $korv = DB::table('penerimaanstok')->where('kodepenerimaan', 'KORV')->first();
        if ($penerimaanStokHeader->penerimaanstok_id == $korv->id) {
            (new PenerimaanStokDetail())->returnVulkanisir($penerimaanStokHeader->id);
        }
        /*DELETE EXISTING DETAIL*/
        $penerimaanStokDetail = PenerimaanStokDetail::where('penerimaanstokheader_id', $penerimaanStokHeader->id)->lockForUpdate()->delete();
        $PenambahanNilai = PenerimaanStokPenambahanNilai::where('penerimaanstokheader_id', $penerimaanStokHeader->id)->lockForUpdate()->delete();

        if (isset($penerimaanStokHeader->hutang_nobukti) && ($penerimaanStokHeader->hutang_nobukti !== "")) {
            // dd(isset($penerimaanStokHeader->hutang_nobukti) && ($penerimaanStokHeader->hutang_nobukti !== ""));
            $hutangHeader = HutangHeader::where('nobukti', $penerimaanStokHeader->hutang_nobukti)->first();
            (new HutangHeader())->processDestroy($hutangHeader->id);
        }
        /*DELETE EXISTING JURNAL*/
        $JurnalUmumHeader = JurnalUmumHeader::where('nobukti', $penerimaanStokHeader->nobukti)->lockForUpdate()->first();
        if ($JurnalUmumHeader) {
            (new JurnalUmumHeader())->processDestroy($JurnalUmumHeader->id, strtoupper('DELETE penerimaan Stok Header'));
        }

        //delete kartu stok
        // (new KartuStok())->processDestroy($penerimaanStokHeader->nobukti);
        $kartuStok = kartuStok::where('nobukti', $penerimaanStokHeader->nobukti)->lockForUpdate()->delete();


        $penerimaanStokHeader = $penerimaanStokHeader->lockAndDestroy($id);

        $penerimaanStokHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => $penerimaanStokHeader->getTable(),
            'postingdari' => strtoupper('DELETE penerimaan Stok Header'),
            'idtrans' => $penerimaanStokHeader->id,
            'nobuktitrans' => $penerimaanStokHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $dataHeader,
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => "penerimaanstokdetail",
            'postingdari' => strtoupper('DELETE penerimaan Stok detail'),
            'idtrans' => $penerimaanStokHeaderLogTrail['id'],
            'nobuktitrans' => $penerimaanStokHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $dataDetail,
            'modifiedby' => auth('api')->user()->name
        ]);

        return $penerimaanStokHeader;
    }

    public function isPOUsed($id)
    {
        $query = DB::table($this->table)->from($this->table)
            ->where('penerimaanstokheader.id', $id)
            ->leftJoin('penerimaanstokheader as nobuktispb', 'penerimaanstokheader.nobukti', 'nobuktispb.penerimaanstok_nobukti');
        $data = $query->first();
        if ($data->id) {
            # code...
            return true;
        }
        return false;
    }
    public function isOutUsed($id)
    {
        $query = DB::table($this->table)->from($this->table)
            ->where('penerimaanstokheader.id', $id)
            ->join('pengeluaranstokdetailfifo', 'penerimaanstokheader.nobukti', 'pengeluaranstokdetailfifo.penerimaanstokheader_nobukti');
        $data = $query->first();

        if ($data) {
            # code...
            return [
                true,
                $data->nobukti
            ];
        }
        return false;
    }
    public function isEhtUsed($id)
    {
        $query = DB::table($this->table)->from($this->table)
            ->where('penerimaanstokheader.id', $id)
            ->leftJoin('hutangheader', 'penerimaanstokheader.hutang_nobukti', 'hutangheader.nobukti');
        $data = $query->get();
        $statusApproval = DB::table('parameter')->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();

        foreach ($data as $penerimaanstok) {
            if ($statusApproval->id == $penerimaanstok->statusapproval) {
                $test[] = $penerimaanstok->statusapproval;
                // dd($test);
                return true;
            }
        }


        return false;
    }
    public function isApproved($id)
    {
        $query = DB::table($this->table)->from($this->table)->where('penerimaanstokheader.id', $id);
        $data = $query->first();
        $status = $data->statusapproval;
        $statusApproval = DB::table('parameter')->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();

        if ($status == $statusApproval->id) {
            return true;
        }

        return false;
    }
    public function isEHTApprovedJurnal($id)
    {
        $query = DB::table($this->table)->from($this->table)->where('penerimaanstokheader.id', $id);
        $data = $query->first();
        $approvalJurnal = DB::table('penerimaanstokheader')
            ->from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'a.hutang_nobukti', 'b.nobukti')
            ->where('a.nobukti', '=', $data->nobukti)
            ->first();

        if (isset($approvalJurnal)) {
            return true;
        }

        return false;
    }

    public function printValidation($id)
    {
        $query = DB::table($this->table)->from($this->table)->where('penerimaanstokheader.id', $id);
        $data = $query->first();
        $status = $data->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($status == $statusCetak->id) {
            return true;
        }

        return false;
    }
    public function isBukaTanggalValidation($date, $penerimaanstok_id)
    {
        $date = date('Y-m-d', strtotime($date));
        $bukaPenerimaanStok = BukaPenerimaanStok::where('tglbukti', '=', $date)->where('penerimaanstok_id', '=', $penerimaanstok_id)->first();
        $tglbatas = $bukaPenerimaanStok->tglbatas ?? 0;
        $limit = strtotime($tglbatas);
        $now = strtotime('now');
        // dd( date('Y-m-d H:i:s',$now), date('Y-m-d H:i:s',$limit));
        if ($now < $limit) return true;
        return false;
    }
    public function todayValidation($tglbukti)
    {
        $tglbuktistr = strtotime($tglbukti);
        $jam = 23;
        $menit = 59;
        $limit = strtotime($tglbukti . ' +' . $jam . ' hours +' . $menit . ' minutes');
        $now = strtotime('now');
        if ($now < $limit) return true;
        return false;
    }
    public function isEditAble($id)
    {
        $tidakBolehEdit = DB::table('penerimaanstokheader')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

        $query = DB::table('penerimaanstokheader')->from(DB::raw("penerimaanstokheader with (readuncommitted)"))
            ->select('statusapprovaledit as statusedit', 'tglbatasedit')
            ->where('id', $id)
            ->first();

        if ($query->statusedit != $tidakBolehEdit->id) {
            $limit = strtotime($query->tglbatasedit);
            $now = strtotime('now');
            if ($now < $limit) return true;
        }
        return false;
    }


    // public function checkTempat($stokId,$persediaan,$persediaanId)
    // {
    //     $result = StokPersediaan::lockForUpdate()->where("stok_id", $stokId)->where("$persediaan", $persediaanId)->first();
    //     return (!$result) ? false :$result;
    // }
}
