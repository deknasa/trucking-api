<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class Tarif extends MyModel
{
    use HasFactory;

    protected $table = 'tarif';

    public $detailTasId;

    protected $casts = [
        'tglberlaku' => 'date:d-m-Y',
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
        $orderanTrucking = DB::table('orderantrucking')
            ->from(
                DB::raw("orderantrucking as a with (readuncommitted)")
            )
            ->select(
                'a.tarif_id'
            )
            ->where('a.tarif_id', '=', $id)
            ->first();
        if (isset($orderanTrucking)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Orderan Trucking',
            ];
            goto selesai;
        }
        $suratPengantar = DB::table('suratpengantar')
            ->from(
                DB::raw("suratpengantar as a with (readuncommitted)")
            )
            ->select(
                'a.tarif_id'
            )
            ->where('a.tarif_id', '=', $id)
            ->first();
        if (isset($suratPengantar)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Surat Pengantar',
            ];
            goto selesai;
        }

        $upahSupir = DB::table('upahsupir')
            ->from(
                DB::raw("upahsupir as a with (readuncommitted)")
            )
            ->select(
                'a.tarif_id'
            )
            ->where('a.tarif_id', '=', $id)
            ->first();
        if (isset($upahSupir)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Upah Supir',
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


        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'TarifController';

        $aktif = request()->aktif ?? '';
        $jenisOrder = request()->jenisOrder ?? '';
        $isParent = request()->isParent ?? false;
        // 
        if ($proses == 'reload') {
            $temtabel = 'temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

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
                $table->bigInteger('id')->nullable();
                $table->longText('parent_id')->nullable();
                $table->longText('pelabuhan_id')->nullable();
                $table->longText('upahsupir')->nullable();
                $table->longText('tujuan')->nullable();
                $table->longText('penyesuaian')->nullable();
                $table->longText('statusaktif')->nullable();
                $table->longText('statusaktiftext')->nullable();
                $table->longText('statussistemton')->nullable();
                $table->longText('kota_id')->nullable();
                $table->bigInteger('kotaId')->nullable();
                $table->longText('zona_id')->nullable();
                $table->longText('jenisorder')->nullable()->nullable();
                $table->date('tglmulaiberlaku')->nullable();
                $table->longText('statuspenyesuaianharga')->nullable();
                $table->longText('statuspostingtnl')->nullable();
                $table->longText('keterangan')->nullable();
                $table->longText('modifiedby')->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->longText('tglcetak')->nullable();
                $table->longText('usercetak')->nullable();
                $table->longText('tujuanpenyesuaian')->nullable();
                $table->bigInteger('statusaktif_id')->nullable();
                $table->bigInteger('jenisorder_id')->nullable();
            });

            $tempupah = '##tempupah' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempupah, function (Blueprint $table) {
                $table->bigInteger('id')->nullable();
                $table->longText('upahsupir')->nullable();
            });
            $queryUpah = DB::table("tarif")->from(DB::raw("tarif with (readuncommitted)"))
                ->select('tarif.id', db::raw(" STRING_AGG(cast(isnull(kotadari.kodekota,'')+(case when isnull(kotasampai.kodekota,'')='' then '' else ' - ' +isnull(kotasampai.kodekota,'') end)+ 
                    (case when isnull(upahsupir.penyesuaian,'')='' then '' else ' ( ' +isnull(upahsupir.penyesuaian,'')+ ' ) ' end) as nvarchar(max)), ', ') as upahsupir
                    "))
                ->leftJoin(DB::raw("upahsupir as upahsupir with (readuncommitted)"), 'upahsupir.tarif_id', '=', 'tarif.id')
                ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'upahsupir.kotadari_id')
                ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'upahsupir.kotasampai_id')
                ->groupBy('tarif.id');

            DB::table($tempupah)->insertUsing([
                'id',
                'upahsupir',
            ], $queryUpah);
            $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
                ->select(
                    'tarif.id',
                    'parent.tujuan as parent_id',
                    'pelabuhan.kodekota as pelabuhan_id',
                    db::raw(" upahsupir.upahsupir as upahsupir"),
                    'tarif.tujuan',
                    'tarif.penyesuaian',
                    'parameter.memo as statusaktif',
                    'parameter.text as statusaktiftext',
                    'sistemton.memo as statussistemton',
                    'kota.kodekota as kota_id',
                    'tarif.kota_id as kotaId',
                    'zona.zona as zona_id',
                    'jenisorder.keterangan as jenisorder',
                    'tarif.tglmulaiberlaku',
                    'p.memo as statuspenyesuaianharga',
                    'posting.memo as statuspostingtnl',
                    'tarif.keterangan',
                    'tarif.modifiedby',
                    'tarif.created_at',
                    'tarif.updated_at',
                    DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                    DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
                    DB::raw("(trim(tarif.tujuan)+(case when trim(tarif.penyesuaian)='' then '' else ' - ' end)+trim(tarif.penyesuaian)) as tujuanpenyesuaian"),
                    'tarif.statusaktif as statusaktif_id',
                    'tarif.jenisorder_id',

                )
                ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'tarif.statusaktif', '=', 'parameter.id')
                ->leftJoin(DB::raw("kota with (readuncommitted)"), 'tarif.kota_id', '=', 'kota.id')
                ->leftJoin(DB::raw("zona with (readuncommitted)"), 'tarif.zona_id', '=', 'zona.id')
                ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'tarif.jenisorder_id', '=', 'jenisorder.id')
                // ->leftJoin(DB::raw("$tempUpahsupir as B with (readuncommitted)"), 'tarif.upahsupir_id', '=', "B.id")
                ->leftJoin(DB::raw("tarif as parent with (readuncommitted)"), 'tarif.parent_id', '=', 'parent.id')
                ->leftJoin(DB::raw("parameter AS p with (readuncommitted)"), 'tarif.statuspenyesuaianharga', '=', 'p.id')
                ->leftJoin(DB::raw("parameter AS sistemton with (readuncommitted)"), 'tarif.statussistemton', '=', 'sistemton.id')
                ->leftJoin(DB::raw("parameter AS posting with (readuncommitted)"), 'tarif.statuspostingtnl', '=', 'posting.id')
                ->leftJoin(DB::raw("$tempupah as upahsupir with (readuncommitted)"), 'upahsupir.id', '=', 'tarif.id')
                ->leftJoin(DB::raw("kota as pelabuhan with (readuncommitted)"), 'tarif.pelabuhan_id', '=', 'pelabuhan.id');
                

            DB::table($temtabel)->insertUsing([
                'id',
                'parent_id',
                'pelabuhan_id',
                'upahsupir',
                'tujuan',
                'penyesuaian',
                'statusaktif',
                'statusaktiftext',
                'statussistemton',
                'kota_id',
                'kotaId',
                'zona_id',
                'jenisorder',
                'tglmulaiberlaku',
                'statuspenyesuaianharga',
                'statuspostingtnl',
                'keterangan',
                'modifiedby',
                'created_at',
                'updated_at',
                'tglcetak',
                'usercetak',
                'tujuanpenyesuaian',
                'statusaktif_id',
                'jenisorder_id',
            ], $query);
        } else {
            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel with (readuncommitted)")
            )
                ->select(
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            $temtabel = $querydata->namatabel;
        }

        // 

        // $tempUpahsupir = $this->tempUpahsupir();
        // $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
        //     ->select(
        //         'tarif.id',
        //         'parent.tujuan as parent_id',
        //          db::raw("isnull(kotadari.keterangan,'')+(case when isnull(kotasampai.keterangan,'')='' then '' else ' - ' +isnull(kotasampai.keterangan,'') end)+ 
        //          (case when isnull(upahsupir.penyesuaian,'')='' then '' else ' ( ' +isnull(upahsupir.penyesuaian,'')+ ' ) ' end) as upahsupir
        //          ") ,
        //         'tarif.tujuan',
        //         'tarif.penyesuaian',
        //         'parameter.memo as statusaktif',
        //         'sistemton.memo as statussistemton',
        //         'kota.kodekota as kota_id',
        //         'tarif.kota_id as kotaId',
        //         'zona.zona as zona_id',
        //         'jenisorder.keterangan as jenisorder',
        //         'tarif.tglmulaiberlaku',
        //         'p.memo as statuspenyesuaianharga',
        //         'posting.memo as statuspostingtnl',
        //         'tarif.keterangan',
        //         'tarif.modifiedby',
        //         'tarif.created_at',
        //         'tarif.updated_at',
        //         DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
        //         DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
        //         DB::raw("(trim(tarif.tujuan)+(case when trim(tarif.penyesuaian)='' then '' else ' - ' end)+trim(tarif.penyesuaian)) as tujuanpenyesuaian"),
        //     )
        //     ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'tarif.statusaktif', '=', 'parameter.id')
        //     ->leftJoin(DB::raw("kota with (readuncommitted)"), 'tarif.kota_id', '=', 'kota.id')
        //     ->leftJoin(DB::raw("zona with (readuncommitted)"), 'tarif.zona_id', '=', 'zona.id')
        //     ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'tarif.jenisorder_id', '=', 'jenisorder.id')
        //     // ->leftJoin(DB::raw("$tempUpahsupir as B with (readuncommitted)"), 'tarif.upahsupir_id', '=', "B.id")
        //     ->leftJoin(DB::raw("tarif as parent with (readuncommitted)"), 'tarif.parent_id', '=', 'parent.id')
        //     ->leftJoin(DB::raw("parameter AS p with (readuncommitted)"), 'tarif.statuspenyesuaianharga', '=', 'p.id')
        //     ->leftJoin(DB::raw("parameter AS sistemton with (readuncommitted)"), 'tarif.statussistemton', '=', 'sistemton.id')
        //     ->leftJoin(DB::raw("parameter AS posting with (readuncommitted)"), 'tarif.statuspostingtnl', '=', 'posting.id')
        //     ->leftJoin(DB::raw("upahsupir as upahsupir with (readuncommitted)"), 'upahsupir.tarif_id', '=', 'tarif.id')
        //     ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'upahsupir.kotadari_id')
        //     ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'upahsupir.kotasampai_id');

        $querydata = DB::table('listtemporarytabel')->from(
            DB::raw("listtemporarytabel with (readuncommitted)")
        )
            ->select(
                'namatabel',
            )
            ->where('class', '=', $class)
            ->where('modifiedby', '=', $user)
            ->first();

        $temtabel = $querydata->namatabel;
        $query = DB::table(DB::raw($temtabel))->from(
            DB::raw(DB::raw($temtabel) . " tarif with (readuncommitted)")
        )
            ->distinct()->select(
                'tarif.id',
                'tarif.parent_id',
                'tarif.pelabuhan_id',
                'tarif.upahsupir',
                'tarif.tujuan',
                'tarif.penyesuaian',
                'tarif.statusaktif',
                'tarif.statussistemton',
                'tarif.kota_id',
                'tarif.kotaId',
                'tarif.zona_id',
                'tarif.jenisorder',
                'tarif.tglmulaiberlaku',
                'tarif.statuspenyesuaianharga',
                'tarif.statuspostingtnl',
                'tarif.keterangan',
                'tarif.modifiedby',
                'tarif.created_at',
                'tarif.updated_at',
                'tarif.tglcetak',
                'tarif.usercetak',
                'tarif.tujuanpenyesuaian',
            );
            // dd($query->where('tarif.pelabuhan_id','PELABUHAN PARE-PARE')->get());


        // dd('test');
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('tarif.statusaktif_id', '=', $statusaktif->id);
        }
        if ($jenisOrder != '') {
            if ($jenisOrder == 'MUATAN') {
                $jenis = DB::table("jenisorder")->from(DB::raw("jenisorder with (readuncommitted)"))->where('keterangan', 'MUATAN')->first();

                $query->where('tarif.jenisorder_id', '=', $jenis->id);
            } else if ($jenisOrder == 'BONGKARAN') {
                $jenis = DB::table("jenisorder")->from(DB::raw("jenisorder with (readuncommitted)"))->where('keterangan', 'BONGKARAN')->first();

                $query->where('tarif.jenisorder_id', '=', $jenis->id);
            } else if ($jenisOrder == 'IMPORT') {
                $jenis = DB::table("jenisorder")->from(DB::raw("jenisorder with (readuncommitted)"))->where('keterangan', 'IMPORT')->first();

                $query->where('tarif.jenisorder_id', '=', $jenis->id);
            } else if ($jenisOrder == 'EKSPORT') {
                $jenis = DB::table("jenisorder")->from(DB::raw("jenisorder with (readuncommitted)"))->where('keterangan', 'EKSPORT')->first();

                $query->where('tarif.jenisorder_id', '=', $jenis->id);
            } else {
                $query->whereRaw("(tarif.jenisorder_id = 0 or tarif.jenisorder_id IS NULL)");
            }
        }

        if ($isParent == true) {
            // if ($jenisOrder == '') {
            //     $query->whereRaw("(tarif.jenisorder_id = 0 or tarif.jenisorder_id IS NULL)");
            // }
            $query->where('tarif.penyesuaian', '');
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $this->filter($query);
        $this->sort($query);

        $this->paginate($query);
        // dd($query->toSql());l
        $data = $query->get();

        return $data;
    }
    public function tempUpahsupir()
    {
        $tempUpahsupir = '##tempupah' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $fetch = UpahSupir::from(DB::raw("upahsupir with (readuncommitted)"))
            ->select('upahsupir.id as id', 'kota.keterangan as kotasampai_id')
            ->leftJoin(DB::raw("kota with (readuncommitted)"), 'upahsupir.kotasampai_id', 'kota.id');

        Schema::create($tempUpahsupir, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kotasampai_id', 200)->nullable();
        });
        DB::table($tempUpahsupir)->insertUsing(['id', 'kotasampai_id'], $fetch);

        return $tempUpahsupir;
    }

    public function selectColumns($query)
    { //sesuaikan dengan createtemp


        $tempupah = '##tempupah' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempupah, function (Blueprint $table) {
            $table->bigInteger('id')->nullable();
            $table->longText('upahsupir')->nullable();
        });
        $queryUpah = DB::table("tarif")->from(DB::raw("tarif with (readuncommitted)"))
            ->select('tarif.id', db::raw(" STRING_AGG(cast(isnull(kotadari.kodekota,'')+(case when isnull(kotasampai.kodekota,'')='' then '' else ' - ' +isnull(kotasampai.kodekota,'') end)+ 
                (case when isnull(upahsupir.penyesuaian,'')='' then '' else ' ( ' +isnull(upahsupir.penyesuaian,'')+ ' ) ' end) as nvarchar(max)), ', ') as upahsupir
                "))
            ->leftJoin(DB::raw("upahsupir as upahsupir with (readuncommitted)"), 'upahsupir.tarif_id', '=', 'tarif.id')
            ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'upahsupir.kotadari_id')
            ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'upahsupir.kotasampai_id')
            ->groupBy('tarif.id');

        DB::table($tempupah)->insertUsing([
            'id',
            'upahsupir',
        ], $queryUpah);

        $query1 = $query->select(
            db::raw($this->table . ".id"),
            'parent.tujuan as parent_id',
            'pelabuhan.kodekota as pelabuhan_id',
            db::raw($this->table . ".tujuan"),
            db::raw($this->table . ".penyesuaian"),
            'parameter.memo as statusaktif',
            'parameter.text as statusaktiftext',
            'sistemton.text as statussistemton',
            'kota.kodekota as kota_id',
            'zona.zona as zona_id',
            'jenisorder.keterangan as jenisorder',
            db::raw($this->table . ".tglmulaiberlaku"),
            'p.text as statuspenyesuaianharga',
            'posting.text as statuspostingtnl',
            db::raw($this->table . ".keterangan"),
            db::raw($this->table . ".modifiedby"),
            db::raw($this->table . ".created_at"),
            db::raw($this->table . ".updated_at"),
            //  db::raw("'' as upahsupir")
            db::raw(" upahsupir.upahsupir as upahsupir"),

        )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'tarif.statusaktif', '=', 'parameter.id')
            ->leftJoin(DB::raw("kota with (readuncommitted)"), 'tarif.kota_id', '=', 'kota.id')
            ->leftJoin(DB::raw("zona with (readuncommitted)"), 'tarif.zona_id', '=', 'zona.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'tarif.jenisorder_id', '=', 'jenisorder.id')
            // ->leftJoin(DB::raw("$tempUpahsupir as B with (readuncommitted)"), 'tarif.upahsupir_id', '=', "B.id")
            ->leftJoin(DB::raw("tarif as parent with (readuncommitted)"), 'tarif.parent_id', '=', 'parent.id')
            ->leftJoin(DB::raw("parameter AS p with (readuncommitted)"), 'tarif.statuspenyesuaianharga', '=', 'p.id')
            ->leftJoin(DB::raw("parameter AS sistemton with (readuncommitted)"), 'tarif.statussistemton', '=', 'sistemton.id')
            ->leftJoin(DB::raw("parameter AS posting with (readuncommitted)"), 'tarif.statuspostingtnl', '=', 'posting.id')
            ->leftJoin(DB::raw("$tempupah as upahsupir with (readuncommitted)"), 'upahsupir.id', '=', 'tarif.id')
            ->leftJoin(DB::raw("kota as pelabuhan with (readuncommitted)"), 'tarif.pelabuhan_id', '=', 'pelabuhan.id');


        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('parent_id', 200)->nullable();
            $table->string('pelabuhan_id', 200)->nullable();
            $table->string('tujuan', 200)->nullable();
            $table->string('penyesuaian', 200)->nullable();
            $table->string('statusaktif')->nullable();
            $table->string('statusaktiftext')->nullable();
            $table->string('statussistemton')->nullable();
            $table->string('kota_id')->nullable();
            $table->string('zona_id')->nullable();
            $table->string('jenisorder')->nullable();
            $table->date('tglmulaiberlaku')->nullable();
            $table->string('statuspenyesuaianharga')->nullable();
            $table->string('statuspostingtnl')->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->longtext('upahsupir')->nullable();
        });

        DB::table($temp)->insertUsing([
            'id', 'parent_id','pelabuhan_id', 'tujuan', 'penyesuaian',  'statusaktif', 'statusaktiftext',  'statussistemton', 'kota_id', 'zona_id', 'jenisorder', 'tglmulaiberlaku',
            'statuspenyesuaianharga', 'statuspostingtnl', 'keterangan', 'modifiedby', 'created_at', 'updated_at', 'upahsupir'
        ], $query1);

        $query2 = db::table($temp)->from(db::raw($temp . " as tarif with (readuncommitted)"))
            ->select(
                'tarif.id',
                'tarif.parent_id',
                'tarif.pelabuhan_id',
                'tarif.tujuan',
                'tarif.penyesuaian',
                'tarif.statusaktif',
                'tarif.statusaktiftext',
                'tarif.statussistemton',
                'tarif.kota_id',
                'tarif.zona_id',
                'tarif.jenisorder',
                'tarif.tglmulaiberlaku',
                'tarif.statuspenyesuaianharga',
                'tarif.statuspostingtnl',
                'tarif.keterangan',
                'tarif.modifiedby',
                'tarif.created_at',
                'tarif.updated_at',
                'tarif.upahsupir'
            );
        return $query2;
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##tempAB' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('parent_id', 200)->nullable();
            $table->string('pelabuhan_id', 200)->nullable();
            $table->string('tujuan', 200)->nullable();
            $table->string('penyesuaian', 200)->nullable();
            $table->string('statusaktif')->nullable();
            $table->string('statusaktiftext')->nullable();
            $table->string('statussistemton')->nullable();
            $table->string('kota_id')->nullable();
            $table->string('zona_id')->nullable();
            $table->string('jenisorder')->nullable();
            $table->date('tglmulaiberlaku')->nullable();
            $table->string('statuspenyesuaianharga')->nullable();
            $table->string('statuspostingtnl')->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->longtext('upahsupir')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);

        $this->sort($query);
        // dd($query->get());   
        $models = $this->filter($query);
        // $models = $this->filter($query);

        DB::table($temp)->insertUsing([
            'id', 'parent_id','pelabuhan_id', 'tujuan', 'penyesuaian',  'statusaktif', 'statusaktiftext',  'statussistemton', 'kota_id', 'zona_id', 'jenisorder', 'tglmulaiberlaku',
            'statuspenyesuaianharga', 'statuspostingtnl', 'keterangan', 'modifiedby', 'created_at', 'updated_at', 'upahsupir'
        ], $models);

        return  $temp;
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->nullable();
            $table->string('statusaktifnama', 300)->nullable();
            $table->unsignedBigInteger('statussistemton')->nullable();
            $table->string('statussistemtonnama', 300)->nullable();
            $table->unsignedBigInteger('statuspenyesuaianharga')->nullable();
            $table->unsignedBigInteger('statuspostingtnl')->nullable();
            $table->unsignedBigInteger('statuslangsir')->nullable();
            $table->string('statuslangsirnama')->nullable();
        });

        $statusaktif = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id',
                'text'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('default', '=', 'YA')
            ->first();

        $statussistemton = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id',
                'text'
            )
            ->where('grp', '=', 'SISTEM TON')
            ->where('subgrp', '=', 'SISTEM TON')
            ->where('default', '=', 'YA')
            ->first();

        $statuspenyesuaianharga = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'PENYESUAIAN HARGA')
            ->where('subgrp', '=', 'PENYESUAIAN HARGA')
            ->where('default', '=', 'YA')
            ->first();

        $statuspostingtnl = Parameter::from(
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

        $statuslangsir = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id',
                'text'
            )
            ->where('grp', '=', 'STATUS LANGSIR')
            ->where('subgrp', '=', 'STATUS LANGSIR')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusLangsir = $statuslangsir->id ?? 0;
        $namadefaultstatusLangsir = $statuslangsir->text ?? '';

        DB::table($tempdefault)->insert(
            [
                "statusaktif" => $statusaktif->id ?? 0,
                "statusaktifnama" => $statusaktif->text ?? "",
                "statussistemton" => $statussistemton->id ?? 0,
                "statussistemtonnama" => $statussistemton->text ?? "",
                "statuspenyesuaianharga" => $statuspenyesuaianharga->id ?? 0,
                "statuspostingtnl" => $statuspostingtnl->id ?? 0,
                "statuslangsir" => $iddefaultstatusLangsir,
                "statuslangsirnama" => $namadefaultstatusLangsir,
            ]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusaktifnama',
                'statussistemton',
                'statussistemtonnama',
                'statuspenyesuaianharga',
                'statuspostingtnl',
                'statuslangsir',
                'statuslangsirnama',
            );

        $data = $query->first();

        return $data;
    }

    public function findAll($id)
    {
        $tempUpahsupir = (new static)->tempUpahsupir();
        $query = Tarif::from(DB::raw("tarif with (readuncommitted)"))
            ->select(
                'tarif.id',
                DB::raw("(case when tarif.parent_id=0 then null else tarif.parent_id end) as parent_id"),
                'parent.tujuan as parent',
                DB::raw("(case when tarif.pelabuhan_id=0 then null else tarif.pelabuhan_id end) as pelabuhan_id"),
                'pelabuhan.kodekota as pelabuhan',
                // DB::raw("(case when tarif.upahsupir_id=0 then null else tarif.upahsupir_id end) as upahsupir_id"),
                // "$tempUpahsupir.kotasampai_id as upah",     

                db::raw("isnull(kotadari.keterangan,'')+(case when isnull(kotasampai.keterangan,'')='' then '' else ' - ' +isnull(kotasampai.keterangan,'') end)+ 
                (case when isnull(upahsupir.penyesuaian,'')='' then '' else ' ( ' +isnull(upahsupir.penyesuaian,'')+ ' ) ' end) as upah
                "),
                'kotadari.keterangan as dari',
                'kotasampai.keterangan as sampai',
                'upahsupir.penyesuaian as penyesuaianupah',
                'upahsupir.id as upah_id',
                DB::raw("TRIM(tarif.tujuan) as tujuan"),
                'tarif.penyesuaian',
                'tarif.statusaktif',
                'tarif.statussistemton',
                DB::raw("(case when tarif.kota_id=0 then null else tarif.kota_id end) as kota_id"),
                'kota.keterangan as kota',
                DB::raw("(case when tarif.zona_id=0 then null else tarif.zona_id end) as zona_id"),
                'zona.keterangan as zona',
                'jenisorder.id as jenisorder_id',
                'jenisorder.keterangan as jenisorder',
                'tarif.tglmulaiberlaku',
                'tarif.statuspenyesuaianharga',
                'tarif.statuslangsir',
                'statuslangsir.text as statuslangsirnama',
                DB::raw("(case when tarif.statuspostingtnl IS NULL then 0 else tarif.statuspostingtnl end) as statuspostingtnl"),
                'tarif.keterangan',
                'param_statusaktif.text as statusaktifnama',
                'param_statussistemton.text as statussistemtonnama',
            )
            ->leftJoin(DB::raw("kota with (readuncommitted)"), 'tarif.kota_id', '=', 'kota.id')
            ->leftJoin(DB::raw("zona with (readuncommitted)"), 'tarif.zona_id', '=', 'zona.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'tarif.jenisorder_id', '=', 'jenisorder.id')
            ->leftJoin(DB::raw("tarif as parent with (readuncommitted)"), 'tarif.parent_id', '=', 'parent.id')

            ->leftJoin(DB::raw("parameter as statuslangsir with (readuncommitted)"), 'tarif.statuslangsir', 'statuslangsir.id')
            ->leftJoin(DB::raw("parameter as param_statusaktif with (readuncommitted)"), 'tarif.statusaktif', '=', 'param_statusaktif.id')
            ->leftJoin(DB::raw("parameter as param_statussistemton with (readuncommitted)"), 'tarif.statussistemton', '=', 'param_statussistemton.id')
            ->leftJoin(DB::raw("upahsupir as upahsupir with (readuncommitted)"), 'upahsupir.tarif_id', '=', 'tarif.id')
            ->leftJoin(DB::raw("kota as kotadari with (readuncommitted)"), 'kotadari.id', '=', 'upahsupir.kotadari_id')
            ->leftJoin(DB::raw("kota as kotasampai with (readuncommitted)"), 'kotasampai.id', '=', 'upahsupir.kotasampai_id')
            ->leftJoin(DB::raw("kota as pelabuhan with (readuncommitted)"), 'tarif.pelabuhan_id', '=', 'pelabuhan.id')
            // ->leftJoin(DB::raw("$tempUpahsupir with (readuncommitted)"), 'tarif.upahsupir_id', '=', "$tempUpahsupir.id")

            ->where('tarif.id', $id);

        $data = $query->first();
        return $data;
    }


    public function sort($query)
    {
        // if ($this->params['sortIndex'] == 'parent_id') {
        //     return $query->orderBy('parent.tujuan', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'upahsupir_id') {
        //     return $query->orderBy('B.kotasampai_id', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'kota_id') {
        //     return $query->orderBy('kota.kodekota', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'zona_id') {
        //     return $query->orderBy('zona.zona', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'jenisorder') {
        //     return $query->orderBy('jenisorder.keterangan', $this->params['sortOrder']);
        // } else {
        return $query->orderBy('tarif.' . $this->params['sortIndex'], $this->params['sortOrder']);
        // }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('tarif.statusaktiftext', '=', "$filters[data]");
                            // } elseif ($filters['field'] == 'container_id') {
                            //     $query = $query->where('container.keterangan', 'LIKE', "%$filters[data]%");
                            // } elseif ($filters['field'] == 'parent_id') {
                            //     $query = $query->where('parent.tujuan', 'LIKE', "%$filters[data]%");
                            // } elseif ($filters['field'] == 'upahsupir_id') {
                            //     $query = $query->where('B.kotasampai_id', 'LIKE', "%$filters[data]%");
                            // } elseif ($filters['field'] == 'kota_id') {
                            //     $query = $query->where('kota.keterangan', 'LIKE', "%$filters[data]%");
                            // } elseif ($filters['field'] == 'keterangan_id') {
                            //     $query = $query->where('keterangan.keterangan', 'LIKE', "%$filters[data]%");
                            // } elseif ($filters['field'] == 'zona_id') {
                            //     $query = $query->where('zona.keterangan', 'LIKE', "%$filters[data]%");
                            // } elseif ($filters['field'] == 'tujuanpenyesuaian') {
                            //     $query = $query->whereRaw("(trim(tarif.tujuan)+(case when trim(tarif.penyesuaian)='' then '' else ' - ' end)+trim(tarif.penyesuaian)) LIKE '%$filters[data]%'");
                            // } elseif ($filters['field'] == 'jenisorder') {
                            //     $query = $query->where('jenisorder.keterangan', 'LIKE', "%$filters[data]%");
                            // } elseif ($filters['field'] == 'statuspenyesuaianharga') {
                            //     $query = $query->where('p.text', '=', "$filters[data]");
                            // } elseif ($filters['field'] == 'statuspostingtnl') {
                            //     $query = $query->where('posting.text', '=', "$filters[data]");
                            // } elseif ($filters['field'] == 'statussistemton') {
                            //     $query = $query->where('sistemton.text', '=', "$filters[data]");
                        } else
                        if ($filters['field'] == 'tglmulaiberlaku') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'check') {
                            $query = $query->whereRaw('1 = 1');
                        } else {
                            // $query = $query->where('tarif.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw('tarif' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            // if ($filters['field'] == 'statusaktif') {
                            //     $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                            // } elseif ($filters['field'] == 'container_id') {
                            //     $query = $query->orWhere('container.keterangan', 'LIKE', "%$filters[data]%");
                            // } elseif ($filters['field'] == 'parent_id') {
                            //     $query = $query->orWhere('parent.tujuan', 'LIKE', "%$filters[data]%");
                            // } elseif ($filters['field'] == 'upahsupir_id') {
                            //     $query = $query->orWhere('kotasampai_id', 'LIKE', "%$filters[data]%");
                            // } elseif ($filters['field'] == 'kota_id') {
                            //     $query = $query->orWhere('kota.keterangan', 'LIKE', "%$filters[data]%");
                            // } elseif ($filters['field'] == 'zona_id') {
                            //     $query = $query->orWhere('zona.keterangan', 'LIKE', "%$filters[data]%");
                            // } elseif ($filters['field'] == 'tujuanpenyesuaian') {
                            //     $query = $query->orWhereRaw("(trim(tarif.tujuan)+(case when trim(tarif.penyesuaian)='' then '' else ' - ' end)+trim(tarif.penyesuaian)) LIKE '%$filters[data]%'");
                            // } elseif ($filters['field'] == 'jenisorder') {
                            //     $query = $query->orWhere('jenisorder.keterangan', 'LIKE', "%$filters[data]%");
                            // } elseif ($filters['field'] == 'statuspenyesuaianharga') {
                            //     $query = $query->orWhere('p.text', '=', "$filters[data]");
                            // } elseif ($filters['field'] == 'statuspostingtnl') {
                            //     $query = $query->orWhere('posting.text', '=', "$filters[data]");
                            // } elseif ($filters['field'] == 'statussistemton') {
                            //     $query = $query->orWhere('sistemton.text', '=', "$filters[data]");
                            // } else 
                            if ($filters['field'] == 'tglmulaiberlaku') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'check') {
                                $query = $query->whereRaw('1 = 1');
                            } else {
                                // $query = $query->orWhere('tarif.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw('tarif' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function cekValidasi($id)
    {
        $rekap = DB::table('suratpengantar')
            ->from(
                DB::raw("suratpengantar as a with (readuncommitted)")
            )
            ->select(
                'a.tarif_id'
            )
            ->leftJoin(DB::raw("tarifrincian b with (readuncommitted)"), 'a.tarif_id', '=', 'b.id')
            ->where('b.tarif_id', '=', $id)
            ->first();


        if (isset($rekap)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'surat pengantar',
                'kodeerror' => 'SATL'
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

    public function processStore(array $data, Tarif $tarif, $connecTnl = null): Tarif
    {
        // $tarif = new Tarif();
        $tarif->parent_id = $data['parent_id'] ?? '';
        $tarif->pelabuhan_id = $data['pelabuhan_id'] ?? '';
        // $tarif->upahsupir_id = $data['upahsupir_id'] ?? '';
        $tarif->tujuan = $data['tujuan'];
        $tarif->penyesuaian = $data['penyesuaian'] ?? '';
        $tarif->statusaktif = $data['statusaktif'];
        $tarif->statussistemton = $data['statussistemton'];
        $tarif->kota_id = $data['kota_id'];
        $tarif->zona_id = $data['zona_id'] ?? '';
        $tarif->jenisorder_id = $data['jenisorder_id'] ?? 0;
        $tarif->tglmulaiberlaku = date('Y-m-d', strtotime($data['tglmulaiberlaku']));
        $tarif->statuspenyesuaianharga = $data['statuspenyesuaianharga'];
        $tarif->statuspostingtnl = $data['statuspostingtnl'];
        $tarif->statuslangsir = $data['statuslangsir'];
        $tarif->keterangan = $data['keterangan'];
        $tarif->modifiedby = auth('api')->user()->user;
        $tarif->info = html_entity_decode(request()->info);
        $tarif->tas_id = $data['tas_id'];

        if (!$tarif->save()) {
            throw new \Exception("Error storing tarif.");
        }
        $upahsupir_id = $data['upahsupir_id'] ?? 0;
        if ($upahsupir_id != 0) {
            $upahsupir = new Upahsupir();
            if ($connecTnl) {
                $upahsupir->setConnection('srvtnl');
            }
            $datadetailsUpahSupir = $upahsupir->processUpdateTarif([
                'tarif_id' => $tarif->id,
                'id' => $upahsupir_id,
            ]);
        }
        $logtrail = new LogTrail();
        if ($connecTnl) {
            $logtrail->setConnection('srvtnl');
        }
        $storedLogTrail = $logtrail->processStore([
            'namatabel' => strtoupper($tarif->getTable()),
            'postingdari' => 'ENTRY TARIF',
            'idtrans' => $tarif->id,
            'nobuktitrans' => $tarif->id,
            'aksi' => 'ENTRY',
            'datajson' => $tarif->toArray(),
            'modifiedby' => $tarif->modifiedby
        ]);

        $detaillog = [];
        for ($i = 0; $i < count($data['container_id']); $i++) {
            $tarifRincian = new TarifRincian();
            if ($connecTnl) {
                $tarifRincian->setConnection('srvtnl');
            }
            $datadetails = $tarifRincian->processStore([
                'tarif_id' => $tarif->id,
                'container_id' => $data['container_id'][$i],
                'nominal' => $data['nominal'][$i],
                'tas_id' => $data['detail_tas_id'][$i] ?? 0,
            ], $tarifRincian);
            $tarif->detailTasId[] = $datadetails->id;
            $detaillog[] = $datadetails->toArray();
        }
        $logtrail = new LogTrail();
        if ($connecTnl) {
            $logtrail->setConnection('srvtnl');
        }
        $logtrail->processStore([
            'namatabel' => strtoupper($datadetails->getTable()),
            'postingdari' => 'ENTRY UPAH SUPIR RINCIAN',
            'idtrans' =>  $storedLogTrail['id'],
            'nobuktitrans' => $tarif->id,
            'aksi' => 'ENTRY',
            'datajson' => $detaillog,
            'modifiedby' => auth('api')->user()->user
        ]);


        // $statusTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('text', 'POSTING TNL')->first();
        // if ($data['statuspostingtnl'] == $statusTnl->id) {
        //     $statusBukanTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('text', 'TIDAK POSTING TNL')->first();
        //     // posting ke tnl
        //     $data['statuspostingtnl'] = $statusBukanTnl->id;

        //     $postingTNL = $this->postingTnl($data);
        //     if ($postingTNL['statuscode'] != 201) {
        //         if ($postingTNL['statuscode'] == 422) {
        //             throw new \Exception($postingTNL['data']['errors']['penyesuaian'][0] . ' di TNL');
        //         } else {
        //             throw new \Exception($postingTNL['data']['message']);
        //         }
        //     }
        // }

        return $tarif;
    }

    public function processUpdate(Tarif $tarif, array $data, $connecTnl = null): Tarif
    {

        $tarif->parent_id = $data['parent_id'] ?? '';
        $tarif->pelabuhan_id = $data['pelabuhan_id'] ?? '';
        // $tarif->upahsupir_id = $data['upahsupir_id'] ?? '';
        $tarif->tujuan = $data['tujuan'];
        $tarif->penyesuaian = $data['penyesuaian'] ?? '';
        $tarif->statusaktif = $data['statusaktif'];
        $tarif->statussistemton = $data['statussistemton'];
        $tarif->kota_id = $data['kota_id'];
        $tarif->zona_id = $data['zona_id'] ?? '';
        $tarif->jenisorder_id = $data['jenisorder_id'] ?? 0;
        $tarif->tglmulaiberlaku = date('Y-m-d', strtotime($data['tglmulaiberlaku']));
        $tarif->statuspenyesuaianharga = $data['statuspenyesuaianharga'];
        $tarif->statuslangsir = $data['statuslangsir'];
        $tarif->keterangan = $data['keterangan'];
        $tarif->info = html_entity_decode(request()->info);

        if (!$tarif->save()) {
            throw new \Exception("Error updating tarif.");
        }

        $upahsupir_id = $data['upahsupir_id'] ?? 0;
        if ($upahsupir_id != 0) {
            $upahsupir = new Upahsupir();
            if ($connecTnl) {
                $upahsupir->setConnection('srvtnl');
            }
            $datadetailsUpahSupir = $upahsupir->processUpdateTarif([
                'tarif_id' => $tarif->id,
                'id' => $upahsupir_id,
            ]);
        }

        $logtrail = new LogTrail();
        if ($connecTnl) {
            $logtrail->setConnection('srvtnl');
        }
        $storedLogTrail = $logtrail->processStore([
            'namatabel' => strtoupper($tarif->getTable()),
            'postingdari' => 'EDIT TARIF',
            'idtrans' => $tarif->id,
            'nobuktitrans' => $tarif->id,
            'aksi' => 'EDIT',
            'datajson' => $tarif->toArray(),
            'modifiedby' => $tarif->modifiedby
        ]);

        $detaillog = [];
        $tarifRincian = new TarifRincian();
        if ($connecTnl) {
            $tarifRincian->setConnection('srvtnl');
        }
        $tarifRincian->where('tarif_id', $tarif->id)->delete();
        for ($i = 0; $i < count($data['container_id']); $i++) {
            $tarifRincian = new TarifRincian();
            if ($connecTnl) {
                $tarifRincian->setConnection('srvtnl');
            }
            $datadetails = $tarifRincian->processUpdate($tarif, [
                'tarif_id' => $tarif->id,
                'detail_id' => $data['detail_id'][$i],
                'container_id' => $data['container_id'][$i],
                'nominal' => $data['nominal'][$i],
                'tas_id' => $data['detail_tas_id'][$i] ?? 0,

            ], $tarifRincian);
            $tarif->detailTasId[] = $datadetails->id;
            $detaillog[] = $datadetails->toArray();
        }
        $logtrail = new LogTrail();
        if ($connecTnl) {
            $logtrail->setConnection('srvtnl');
        }
        $logtrail->processStore([
            'namatabel' => strtoupper($datadetails->getTable()),
            'postingdari' => 'ENTRY UPAH SUPIR RINCIAN',
            'idtrans' =>  $storedLogTrail['id'],
            'nobuktitrans' => $tarif->id,
            'aksi' => 'ENTRY',
            'datajson' => $detaillog,
        ]);

        return $tarif;
    }

    public function processDestroy(Tarif $tarif): Tarif
    {
        // $tarif = new Tarif();
        $tarif = $tarif->lockAndDestroy($tarif->id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($tarif->getTable()),
            'postingdari' => 'DELETE TARIF',
            'idtrans' => $tarif->id,
            'nobuktitrans' => $tarif->id,
            'aksi' => 'DELETE',
            'datajson' => $tarif->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        return $tarif;
    }

    public function postingTnl($data)
    {
        $server = config('app.server_jkt');
        // $getToken = Http::withHeaders([
        //     'Content-Type' => 'application/json',
        //     'Accept' => 'application/json'
        // ])
        //     ->post($server . 'truckingtnl-api/public/api/token', [
        //         'user' => 'ADMIN',
        //         'password' => config('app.password_tnl'),
        //         'ipclient' => '',
        //         'ipserver' => '',
        //         'latitude' => '',
        //         'longitude' => '',
        //         'browser' => '',
        //         'os' => '',
        //     ]);

        $accessTokenTnl = $data['accessTokenTnl'] ?? '';
        $access_token = $accessTokenTnl;
        if ($accessTokenTnl != '') {
            $data['from'] = 'jkt';
            $transferTarif = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $access_token
            ])->post($server . 'truckingtnl-api/public/api/tarif', $data);
            $tesResp = $transferTarif->toPsrResponse();
            $response = [
                'statuscode' => $tesResp->getStatusCode(),
                'data' => $transferTarif->json(),
            ];

            $dataResp = $transferTarif->json();
            if ($tesResp->getStatusCode() != 201) {
                if ($tesResp->getStatusCode() == 422) {
                    throw new \Exception($dataResp['errors']['penyesuaian'][0] . ' di TNL');
                } else {
                    throw new \Exception($dataResp['message']);
                }
            }
            return $response;
        } else {
            throw new \Exception("server tidak bisa diakses");
        }
    }

    public function processApprovalnonaktif(array $data)
    {
        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $Tarif = Tarif::find($data['Id'][$i]);

            $Tarif->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            // dd($Tarif);
            if ($Tarif->save()) {

                (new LogTrail())->processStore([

                    'namatabel' => strtoupper($Tarif->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF TARIF',
                    'idtrans' => $Tarif->id,
                    'nobuktitrans' => $Tarif->id,
                    'aksi' => $aksi,
                    'datajson' => $Tarif->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $Tarif;
    }
    public function processApprovalaktif(array $data)
    {

        $parameter = new Parameter();
        $statusaktif = $parameter->cekId('STATUS AKTIF', 'STATUS AKTIF', 'AKTIF') ?? 0;
        $statusnonaktif = $parameter->cekId('STATUS AKTIF', 'STATUS AKTIF', 'NON AKTIF') ?? 0;
        
        for ($i = 0; $i < count($data['Id']); $i++) {
            $Tarif = Tarif::find($data['Id'][$i]);
            $statusaktif_id=$Tarif->statusaktif ?? 0;
            if ($statusaktif_id==$statusaktif) {
                $Tarif->statusaktif = $statusnonaktif;
                $aksi = 'NON AKTIF';
            } else {
                $Tarif->statusaktif = $statusaktif;
                $aksi = 'AKTIF';
            }

            // dd($Tarif);
            if ($Tarif->save()) {

                (new LogTrail())->processStore([

                    'namatabel' => strtoupper($Tarif->getTable()),
                    'postingdari' => 'APPROVAL AKTIF TARIF',
                    'idtrans' => $Tarif->id,
                    'nobuktitrans' => $Tarif->id,
                    'aksi' => $aksi,
                    'datajson' => $Tarif->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $Tarif;
    }
}
