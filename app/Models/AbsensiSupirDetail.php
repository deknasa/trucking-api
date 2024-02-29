<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class AbsensiSupirDetail extends MyModel
{
    use HasFactory;

    protected $table = 'absensisupirdetail';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'jam' => 'date:H:i:s',
    ];

    public function get()
    {
        $this->setRequestParameters();
        if (request()->absensi_id != '') {

            $getAbsen = request()->getabsen ?? false;
            $isProsesUangjalan = request()->isProsesUangjalan ?? false;
            $tempsp = '##tempsp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempsp, function ($table) {
                $table->unsignedBigInteger('absensi_id')->nullable();
                $table->unsignedBigInteger('trado_id')->nullable();
                $table->unsignedBigInteger('supir_id')->nullable();
                $table->date('tglabsensi')->nullable();
                $table->string('nobukti', 100)->nullable();
                $table->bigInteger('nominalplusborongan')->nullable();
            });

            $query = DB::table('absensisupirheader')->from(
                DB::raw("absensisupirheader as a with(readuncommitted)")
            )
                ->select(
                    DB::raw("format(a.tglbukti,'yyyy/MM/dd') as tglbukti")
                )
                ->where('a.id', '=', request()->absensi_id)
                ->first();

            $statustrip = DB::table("parameter")->from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->select(
                    'memo'
                )
                ->where('grp', '=', 'TIDAK ADA TRIP')
                ->where('subgrp', '=', 'TIDAK ADA TRIP')
                ->where('text', '=', 'TIDAK ADA TRIP')
                ->first();

            $statusabsensi = db::table("parameter")->from(db::raw("parameter"))->select('id')
                ->where('grp', 'STATUS ABSENSI SUPIR')
                ->where('subgrp', 'STATUS ABSENSI SUPIR')
                ->where('text', 'ABSENSI SUPIR')
                ->first()->id ?? 0;


            $param1 = $query->tglbukti;
            $querysp = DB::table('absensisupirdetail')->from(
                DB::raw("absensisupirdetail as a with (readuncommitted)")
            )->select(
                'a.absensi_id',
                'a.trado_id',
                'a.supir_id',
                'c.tglbukti as tglabsensi',
                'b.nobukti',
                'trado.nominalplusborongan'
            )
                ->join(DB::raw("suratpengantar as b with(readuncommitted)"), function ($join) use ($param1) {
                    $join->on('a.supir_id', '=', 'b.supir_id');
                    $join->on('a.trado_id', '=', 'b.trado_id');
                    $join->on('b.tglbukti', '=', DB::raw("'" . $param1 . "'"));
                })
                ->join(DB::raw("absensisupirheader as c with (readuncommitted)"), 'a.absensi_id', 'c.id')
                ->join(DB::raw("trado with (readuncommitted)"), 'a.trado_id', 'trado.id')
                ->where('c.id', '=', request()->absensi_id);
            // return $querysp->get();

            // dd($querysp->toSql(),request()->absensi_id);
            DB::table($tempsp)->insertUsing([
                'absensi_id',
                'trado_id',
                'supir_id',
                'tglabsensi',
                'nobukti',
                'nominalplusborongan',
            ], $querysp);


            $queryspgroup = DB::table($tempsp)
                ->from(
                    DB::raw($tempsp . " as a")
                )
                ->select(
                    'a.trado_id',
                    'a.supir_id',
                    'a.nominalplusborongan',
                    DB::raw("count(a.nobukti) as jumlah")
                )
                ->groupBy('a.trado_id', 'a.supir_id', 'a.nominalplusborongan');


            $tempspgroup = '##tempspgroup' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempspgroup, function ($table) {
                $table->unsignedBigInteger('trado_id')->nullable();
                $table->unsignedBigInteger('supir_id')->nullable();
                $table->bigInteger('nominalplusborongan')->nullable();
                $table->double('jumlah', 15, 2)->nullable();
            });

            DB::table($tempspgroup)->insertUsing([
                'trado_id',
                'supir_id',
                'nominalplusborongan',
                'jumlah',
            ], $queryspgroup);

            $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));
            $userid = auth('api')->user()->id;
            // dd($userid);

            $querymandor = db::table("mandordetail")->from(db::raw("mandordetail a with (readuncommitted)"))
                ->select('a.mandor_id')
                ->where('a.user_id', $userid);
            $querybukaabsen = db::table("suratpengantarapprovalinputtrip")->from(db::raw("suratpengantarapprovalinputtrip a with (readuncommitted)"))
                ->select('a.user_id')
                ->where('a.tglbukti', date('Y-m-d', strtotime(request()->tglbukti)));
            if ($querybukaabsen->count()) {
                $tempmandordetaillogin = '##mandordetaillogin' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempmandordetaillogin, function ($table) {
                    $table->id();
                    $table->unsignedBigInteger('mandor_id')->nullable();
                });
                DB::table($tempmandordetaillogin)->insertUsing([
                    'mandor_id',
                ],  $querymandor);

                $tempmandorbukaabsen = '##mandorbukaabsen' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempmandorbukaabsen, function ($table) {
                    $table->id();
                    $table->unsignedBigInteger('user_id')->nullable();
                });

                DB::table($tempmandorbukaabsen)->insertUsing([
                    'user_id',
                ],  $querybukaabsen);

                $querymandor = DB::table('mandordetail as a')
                    ->leftJoin(DB::raw($tempmandordetaillogin . ' as b'), 'a.mandor_id', '=', 'b.mandor_id')
                    ->leftJoin(DB::raw($tempmandorbukaabsen . ' as c'), 'a.user_id', '=', 'c.user_id')
                    ->whereRaw('COALESCE(b.mandor_id, 0) <> 0')
                    ->whereRaw('COALESCE(c.user_id, 0) <> 0')
                    ->select('a.mandor_id');
                // ->pluck('a.mandor_id');

            }
            $tempmandordetail = '##tempmandordetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempmandordetail, function ($table) {
                $table->id();
                $table->unsignedBigInteger('mandor_id')->nullable();
            });

            DB::table($tempmandordetail)->insertUsing([
                'mandor_id',
            ],  $querymandor);
            // dd( DB::table($tempmandordetail)->get());

            $params = [
                "id" => request()->id,
                "absensi_id" => request()->absensi_id,
                "withHeader" => request()->withHeader ?? false,
                "whereIn" => request()->whereIn ?? [],
                "forReport" => request()->forReport ?? false,
                // "notIndex" => iseet(request()->notIndex) ?  false : true,
            ];

            // return  request()->id;

            if (isset($params["id"]) && !isset(request()->notIndex)) {
                $query->where("$this->table.id", $params["id"]);
            }

            if (isset($params["absensi_id"])) {
                $query->where("$this->table.absensi_id", $params["absensi_id"]);
            }

            if ($params["withHeader"]) {
                $query->join("absensisupirheader", "absensisupirheader.id", "$this->table.absensi_id");
            }

            if (count($params["whereIn"]) > 0) {
                $query->whereIn("absensi_id", $params["whereIn"]);
            }
            if (isset(request()->forReport) && request()->forReport) {
                $query->select(
                    "header.id as id_header",
                    "header.nobukti as nobukti_header",
                    "header.tglbukti as tgl_header",
                    "header.kasgantung_nobukti as kasgantung_nobukti_header",
                    "header.nominal as nominal_header",
                    "trado.kodetrado as trado",
                    "supir.namasupir as supir",
                    "absentrado.keterangan as status",
                    "$this->table.keterangan as keterangan_detail",
                    DB::raw("LEFT($this->table.jam, 5) as jam"),
                    "$this->table.uangjalan",
                    "$this->table.absensi_id",
                    DB::raw("isnull(c.jumlah,0) as jumlahtrip")
                )
                    ->leftjoin(DB::raw("absensisupirheader as header with (readuncommitted)"), "header.id", "$this->table.absensi_id")
                    ->leftjoin(DB::raw("trado with (readuncommitted)"), "trado.id", "$this->table.trado_id")
                    ->leftjoin(DB::raw("supir with (readuncommitted)"), "supir.id", "$this->table.supir_id")
                    ->leftjoin(DB::raw("absentrado with (readuncommitted)"), "absentrado.id", "$this->table.absen_id")
                    ->leftjoin(DB::raw($tempspgroup . " as c"), function ($join) {
                        $join->on("$this->table.supir_id", "=", "c.supir_id");
                        $join->on("$this->table.trado_id", "=", "c.trado_id");
                    })
                    ->where('trado.statusabsensisupir', $statusabsensi);
            } else {
                $query->select(
                    "trado.kodetrado as trado",
                    "supir.namasupir as supir",
                    "absentrado.kodeabsen as status",
                    "absentrado.keterangan as statusKeterangan",
                    "absentrado.memo as memo",
                    DB::raw("(case when c.nominalplusborongan IS NULL then 0 else c.nominalplusborongan end) as nominalplusborongan"),
                    "$this->table.keterangan as keterangan_detail",
                    DB::raw("LEFT($this->table.jam, 5) as jam"),
                    "$this->table.id",
                    "$this->table.trado_id",
                    "$this->table.supir_id",
                    "$this->table.uangjalan",
                    "$this->table.absensi_id",
                    DB::raw("left(jam, 5)"),
                    DB::raw("isnull(c.jumlah,0) as jumlahtrip"),
                    DB::Raw("(case when isnull(c.jumlah,0)=0  and isnull(absentrado.kodeabsen,'')='' then ' $statustrip->memo ' else '' end) as statustrip")

                )
                    ->leftjoin(DB::raw("trado with (readuncommitted)"), "trado.id", "$this->table.trado_id")
                    ->leftjoin(DB::raw("supir with (readuncommitted)"), "supir.id", "$this->table.supir_id")
                    ->leftjoin(DB::raw("absentrado with (readuncommitted)"), "absentrado.id", "$this->table.absen_id")
                    ->leftjoin(DB::raw($tempspgroup . " as c"), function ($join) {
                        $join->on("$this->table.supir_id", "=", "c.supir_id");
                        $join->on("$this->table.trado_id", "=", "c.trado_id");
                    })
                    ->where('trado.statusabsensisupir', $statusabsensi);
                if ($getAbsen) {

                    $isMandor = auth()->user()->isMandor();
                    $isAdmin = auth()->user()->isAdmin();

                    if (!$isAdmin) {
                        if ($isMandor) {
                            // $query->where('trado.mandor_id', $isMandor->mandor_id);
                            $query->Join(DB::raw($tempmandordetail . " as mandordetail"), 'trado.mandor_id', 'mandordetail.mandor_id');
                        }
                    }
                    $query->addSelect(DB::raw("(trim(trado.kodetrado)+' - '+trim(supir.namasupir)) as tradosupir"))
                    ->where("$this->table.supir_id", '!=', 0)
                    ->whereRaw("absentrado.kodeabsen is null");
                }
                if ($isProsesUangjalan == true) {
                    $query->where('absensisupirdetail.uangjalan', '!=', 0);
                }
                $this->totalRows = $query->count();
                $this->totalNominal = $query->sum('uangjalan');
                $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
                $this->filter($query);
                $this->sort($query);
                $this->paginate($query);

                $absensiSupirDetail = $query->get();
            }

            return  $query->get();
        }
    }




    public function getAll2($id)
    {

        $statusabsensi = db::table("parameter")->from(db::raw("parameter"))->select('id')
            ->where('grp', 'STATUS ABSENSI SUPIR')
            ->where('subgrp', 'STATUS ABSENSI SUPIR')
            ->where('text', 'ABSENSI SUPIR')
            ->first()->id ?? 0;


        $statusaktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'AKTIF')->first();
        $query = DB::table('trado')->from(DB::raw("trado with (readuncommitted)"))
            ->select(
                'trado.id as trado_id',
                'trado.kodetrado as trado',
                DB::raw("isnull(absensisupirdetail.supir_id,0) as supir_id"),
                DB::raw("isnull(supir.namasupir,'') as supir"),
                DB::raw("isnull(absensisupirdetail.keterangan,'') as keterangan"),
                DB::raw("isnull(absensisupirdetail.absen_id,0) as absen_id"),
                DB::raw("isnull(absentrado.keterangan,'') as absen"),
                DB::raw("isnull(absensisupirdetail.jam,'') as jam"),
                DB::raw("isnull(absensisupirdetail.uangjalan,0) as uangjalan"),
            )
            ->where('trado.statusaktif', $statusaktif->id)
            ->leftJoin('absensisupirdetail', function ($join)  use ($id) {
                $join->on('absensisupirdetail.trado_id', '=', 'trado.id')
                    ->where('absensisupirdetail.absensi_id', '=', $id);
            })
            ->leftjoin(DB::raw("supir with (readuncommitted)"), 'absensisupirdetail.supir_id', 'supir.id')
            ->leftJoin(DB::raw("absentrado with (readuncommitted)"), 'absensisupirdetail.absen_id', 'absentrado.id')
            ->where('trado.statusabsensisupir', $statusabsensi)
            ->orderBy('trado.kodetrado', 'asc');


        $data = $query->get();

        return $data;
    }

    public function getAll($id)
    {
        $absensiSupirDetail = AbsensiSupirHeader::find($id);
        $tglabsensi = $absensiSupirDetail->tglbukti;
        $query = $this->tableTemp($tglabsensi);


        $data = $query->get();
        return $data;
    }

    public function tableTemp($date = 'now')
    {
        $mandorId = false;
        $isMandor = auth()->user()->isMandor();
        $isAdmin = auth()->user()->isAdmin();

        // dd($isMandor);
        $userid = auth('api')->user()->id;
        // dd($userid);

        $querymandor = db::table("mandordetail")->from(db::raw("mandordetail a with (readuncommitted)"))
            ->select('a.mandor_id')
            ->where('a.user_id', $userid);
        $querybukaabsen = db::table("bukaabsensi")->from(db::raw("bukaabsensi a with (readuncommitted)"))
            ->select('a.mandor_user_id')
            ->where('a.tglabsensi', date('Y-m-d', strtotime($date)));
        if ($querybukaabsen->count()) {
            $tempmandordetaillogin = '##mandordetaillogin' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempmandordetaillogin, function ($table) {
                $table->id();
                $table->unsignedBigInteger('mandor_id')->nullable();
            });
            DB::table($tempmandordetaillogin)->insertUsing([
                'mandor_id',
            ],  $querymandor);
            
            $tempmandorbukaabsen = '##mandorbukaabsen' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempmandorbukaabsen, function ($table) {
                $table->id();
                $table->unsignedBigInteger('mandor_user_id')->nullable();
            });
            
            DB::table($tempmandorbukaabsen)->insertUsing([
                'mandor_user_id',
            ],  $querybukaabsen);

            $querymandor = DB::table('mandordetail as a')
            ->leftJoin(DB::raw($tempmandordetaillogin.' as b'), 'a.mandor_id', '=', 'b.mandor_id')
            ->leftJoin(DB::raw($tempmandorbukaabsen.' as c'), 'a.user_id', '=', 'c.mandor_user_id')
            ->whereRaw('COALESCE(b.mandor_id, 0) <> 0')
            ->whereRaw('COALESCE(c.mandor_user_id, 0) <> 0')
            ->select('a.mandor_id')
            ->groupBy('a.mandor_id');
            
        }

        $tempmandordetail = '##tempmandordetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempmandordetail, function ($table) {
            $table->id();
            $table->unsignedBigInteger('mandor_id')->nullable();
        });

        DB::table($tempmandordetail)->insertUsing([
            'mandor_id',
        ],  $querymandor);


        // dd(db::table($tempmandordetail)->get());

        $statusaktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'AKTIF')->first();
        $statusabsensisupir = DB::table('parameter')->where('grp', 'STATUS ABSENSI SUPIR')->where('subgrp', 'STATUS ABSENSI SUPIR')->where('text', 'ABSENSI SUPIR')->first();
        $tradoMilikSupir = DB::table('parameter')->where('grp', 'ABSENSI SUPIR')->where('subgrp', 'TRADO MILIK SUPIR')->first();

        $tempMandor = '##tempmandor' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempMandor, function ($table) {
            $table->tinyIncrements('id');
            $table->integer('trado_id')->nullable();
            $table->string('kodetrado')->nullable();
            $table->string('namasupir')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('absentrado')->nullable();
            $table->integer('absen_id')->nullable();
            $table->time('jam')->nullable();
            $table->date('tglbukti')->nullable();
            $table->integer('supir_id')->nullable();
            $table->string('namasupir_old')->nullable();
            $table->integer('supir_id_old')->nullable();
            $table->text('memo')->nullable();

        });
        $tempAbsensi = '##tempAbsensi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempAbsensi, function ($table) {
            $table->tinyIncrements('id');
            $table->integer('trado_id')->nullable();
            $table->string('kodetrado')->nullable();
            $table->string('namasupir')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('absentrado')->nullable();
            $table->integer('absen_id')->nullable();
            $table->time('jam')->nullable();
            $table->date('tglbukti')->nullable();
            $table->integer('supir_id')->nullable();
            $table->string('namasupir_old')->nullable();
            $table->integer('supir_id_old')->nullable();
            $table->text('memo')->nullable();

        });

        //trado yang sudah absen dan punya supir
        $absensisupirdetail = DB::table('absensisupirdetail')
            ->select(
                'trado.id as trado_id',
                'trado.kodetrado',
                'supir.namasupir',
                'absensisupirdetail.keterangan',
                'absentrado.keterangan as absentrado',
                'absentrado.id as absen_id',
                'absensisupirdetail.jam',
                'absensisupirheader.tglbukti',
                'supir.id as supir_id',
                'd.namasupir as namasupir_old',
                'd.id as supir_id_old',

            )
            ->where('absensisupirheader.tglbukti', date('Y-m-d', strtotime($date)))
            ->where('absensisupirdetail.supir_id', '!=', 0)
            ->leftJoin(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirdetail.absensi_id', 'absensisupirheader.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'absensisupirdetail.trado_id', 'trado.id')
            ->leftJoin(DB::raw("absentrado with (readuncommitted)"), 'absensisupirdetail.absen_id', 'absentrado.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'absensisupirdetail.supir_id', 'supir.id')
            ->leftJoin(DB::raw("supir as d with (readuncommitted)"), 'absensisupirdetail.supirold_id', 'd.id');

        if (!$isAdmin) {
            if ($isMandor) {                
                $absensisupirdetail->Join(DB::raw($tempmandordetail . " as mandordetail"), 'trado.mandor_id', 'mandordetail.mandor_id');
            }
        }
        DB::table($tempMandor)->insertUsing(['trado_id', 'kodetrado', 'namasupir', 'keterangan', 'absentrado', 'absen_id', 'jam', 'tglbukti', 'supir_id','namasupir_old','supir_id_old'], $absensisupirdetail);

        //trado yang sudah absen dan punya tidak punya supir
         $absensisupirdetail = DB::table('absensisupirdetail')
         ->select(
             'trado.id as trado_id',
             'trado.kodetrado',
             'supir.namasupir',
             'absensisupirdetail.keterangan',
             'absentrado.keterangan as absentrado',
             'absentrado.id as absen_id',
             'absensisupirdetail.jam',
             'absensisupirheader.tglbukti',
             'supir.id as supir_id',
             'd.namasupir as namasupir_old',
             'd.id as supir_id_old',

         )
         ->where('absensisupirheader.tglbukti', date('Y-m-d', strtotime($date)))
         ->where('absensisupirdetail.supir_id', '=', 0)
         ->leftJoin(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirdetail.absensi_id', 'absensisupirheader.id')
         ->leftJoin(DB::raw("trado with (readuncommitted)"), 'absensisupirdetail.trado_id', 'trado.id')
         ->leftJoin(DB::raw("absentrado with (readuncommitted)"), 'absensisupirdetail.absen_id', 'absentrado.id')
         ->leftJoin(DB::raw("supir with (readuncommitted)"), 'absensisupirdetail.supir_id', 'supir.id')
         ->leftJoin(DB::raw("supir as d with (readuncommitted)"), 'absensisupirdetail.supirold_id', 'd.id');
     if (!$isAdmin) {
         if ($isMandor) {
            $absensisupirdetail->Join(DB::raw($tempmandordetail . " as mandordetail"), 'trado.mandor_id', 'mandordetail.mandor_id');

            //  $absensisupirdetail->where('trado.mandor_id',$isMandor->mandor_id);
         }
     }

     //supir Trado yang belum diisi
     DB::table($tempMandor)->insertUsing(['trado_id', 'kodetrado', 'namasupir', 'keterangan', 'absentrado', 'absen_id', 'jam', 'tglbukti', 'supir_id','namasupir_old','supir_id_old'], $absensisupirdetail);
     DB::table($tempAbsensi)->insertUsing(['trado_id', 'kodetrado', 'namasupir', 'keterangan', 'absentrado', 'absen_id', 'jam', 'tglbukti', 'supir_id','namasupir_old','supir_id_old'], $absensisupirdetail);

     $update = DB::table($tempMandor);
     $update->update(["memo"=>'{"MEMO":"AKTIF","SINGKATAN":"A","WARNA":"#009933","WARNATULISAN":"#FFF"}']);

        $trados = DB::table('trado as a')

            ->select(
                // DB::raw('isnull(b.id,null) as id'),
                'a.id as trado_id',
                'a.kodetrado as kodetrado',
                'c.namasupir as namasupir',
                DB::raw('null as keterangan'),
                DB::raw('null as absentrado'),
                DB::raw('null as absen_id'),
                DB::raw("null as jam"),
                DB::raw("null as tglbukti"),
                DB::raw("(case when (select text from parameter where grp='ABSENSI SUPIR' and subgrp='TRADO MILIK SUPIR')= 'YA' then a.supir_id else null end) as supir_id"),
                'c.namasupir as namasupir_old',
                DB::raw("(case when (select text from parameter where grp='ABSENSI SUPIR' and subgrp='TRADO MILIK SUPIR')= 'YA' then a.supir_id else null end) as supir_id_old"),

            )
            ->leftJoin('supir as c', 'a.supir_id', 'c.id')
            ->leftJoin(DB::raw($tempAbsensi ." as b "), function ($join) {
                $join->on('a.id', '=', 'b.trado_id');
                $join->on(db::raw("isnull(a.supir_id,0)"), '=', db::raw("isnull(b.supir_id,0)"));
            })
            ->where('a.statusaktif', $statusaktif->id)
            ->where('a.statusabsensisupir', $statusabsensisupir->id)
            ->whereRaw("isnull(b.id,0)=0");
            

        if (!$isAdmin) {
            if ($isMandor) {
                $trados->Join(DB::raw($tempmandordetail . " as mandordetail"), 'a.mandor_id', 'mandordetail.mandor_id');
                // $trados->where('a.mandor_id',$isMandor->mandor_id);
            // }else{
            //     $trado->where('a.id',0);
            }
        }

        if ($tradoMilikSupir->text == 'YA') {
            $trados->whereRaw("NOT EXISTS (
                SELECT 1
                FROM $tempMandor temp
                WHERE (temp.trado_id = a.id and temp.supir_id_old = a.supir_id)
            )");
            // ->where('a.supir_id', '!=', 0);
        }else{
            $trados->whereRaw("a.id not in (select trado_id from $tempMandor)");
        }
        
        DB::table($tempMandor)->insertUsing(['trado_id', 'kodetrado', 'namasupir', 'keterangan', 'absentrado', 'absen_id', 'jam', 'tglbukti', 'supir_id','namasupir_old','supir_id_old'], $trados);

        //supir serap yang belum diisi
        $tgl = date('Y-m-d', strtotime($date));
        
        $query = DB::table($tempMandor)->from(DB::raw("$tempMandor as a"))
            ->select(
                // DB::raw("row_number() Over(Order By a.trado_id) as id"),
                'a.id',
                'a.trado_id',
                'a.kodetrado as trado',
                DB::raw("isnull(a.namasupir,'') as supir"),
                DB::raw("isnull(a.keterangan,'') as keterangan"),
                DB::raw("isnull(a.absentrado,'') as absen"),
                DB::raw("isnull(a.absen_id,0) as absen_id"),
                'a.jam',
                           DB::raw("(case when year(isnull(a.tglbukti,'1900/1/1'))=1900 then null else format(a.tglbukti,'dd-MM-yyyy')  end)as tglbukti"),
                'a.supir_id',
                'a.namasupir_old',
                'a.supir_id_old',
                db::raw("count(sp.nobukti) as jlhtrip"),
                'a.memo'
            )
            // ->select(
            //     // DB::raw("row_number() Over(Order By a.trado_id) as id"),
            //     'a.id',
            //     'a.trado_id',
            //     'a.kodetrado as trado',
            //     DB::raw("isnull(a.supir_id,0) as supir_id"),
            //     DB::raw("isnull(a.namasupir,'') as supir"),
            //     DB::raw("isnull(a.keterangan,'') as keterangan"),
            //     DB::raw("isnull(a.absen_id,0) as absen_id"),
            //     DB::raw("isnull(a.absentrado,'') as absen"),
            //     DB::raw("isnull(a.uangjalan,0) as uangjalan"),
            //     'a.namasupir_old',
            //     'a.supir_id_old',
            //     db::raw("count(sp.nobukti) as jlhtrip"),
            // )
            ->groupBy(
                'a.id',
                'a.trado_id',
                'a.kodetrado',
                'a.namasupir',
                'a.keterangan',
                'a.absentrado',
                'a.absen_id',
                'a.jam',
                'a.tglbukti',
                'a.supir_id',
                'a.namasupir_old',
                'a.supir_id_old',
                'a.memo'
            )
            ->leftJoin('suratpengantar as sp' , function ($join) {
                $join->on('sp.tglbukti', '=', 'a.tglbukti');
                $join->on('sp.trado_id', '=', 'a.trado_id');
                $join->on('sp.supir_id', '=', 'a.supir_id');
            });
        return $query;
    }
    public function tableTemp2($date = 'now')
    {
        $mandorId = false;
        $isMandor = auth()->user()->isMandor();
        $isAdmin = auth()->user()->isAdmin();

        $statusaktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'AKTIF')->first();
        $statusabsensisupir = DB::table('parameter')->where('grp', 'STATUS ABSENSI SUPIR')->where('subgrp', 'STATUS ABSENSI SUPIR')->where('text', 'ABSENSI SUPIR')->first();
        $tradoMilikSupir = DB::table('parameter')->where('grp', 'ABSENSI SUPIR')->where('subgrp', 'TRADO MILIK SUPIR')->first();

        // $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // Schema::create($temp, function ($table) {
        //     $table->bigInteger('id')->nullable();
        //     $table->integer('trado_id')->nullable();
        //     $table->integer('supir_id')->nullable();
        //     $table->integer('absen_id')->nullable();
        //     $table->string('keterangan')->nullable();
        //     $table->time('jam')->nullable();
        //     $table->date('tglbukti')->default();
        // });



        $tempMandor = '##tempmandor' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempMandor, function ($table) {
            $table->tinyIncrements('id');
            $table->integer('trado_id')->nullable();
            $table->string('kodetrado')->nullable();
            $table->string('namasupir')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('absentrado')->nullable();
            $table->integer('absen_id')->nullable();
            $table->time('jam')->nullable();
            $table->date('tglbukti')->nullable();
            $table->integer('supir_id')->nullable();
            $table->string('namasupir_old')->nullable();
            $table->integer('supir_id_old')->nullable();
            $table->double('uangjalan', 15, 2)->nullable();
            $table->text('memo')->nullable();
        });

        //trado yang sudah absen dan punya supir
        $absensisupirdetail = DB::table('absensisupirdetail')
            ->select(
                'trado.id as trado_id',
                'trado.kodetrado',
                'supir.namasupir',
                'absensisupirdetail.keterangan',
                'absentrado.keterangan as absentrado',
                'absentrado.id as absen_id',
                'absensisupirdetail.jam',
                'absensisupirheader.tglbukti',
                'supir.id as supir_id',
                'd.namasupir as namasupir_old',
                'd.id as supir_id_old',
                'absensisupirdetail.uangjalan',


            )
            ->where('absensisupirheader.tglbukti', date('Y-m-d', strtotime($date)))
            ->where('absensisupirdetail.supir_id', '!=', 0)
            ->leftJoin(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirdetail.absensi_id', 'absensisupirheader.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'absensisupirdetail.trado_id', 'trado.id')
            ->leftJoin(DB::raw("absentrado with (readuncommitted)"), 'absensisupirdetail.absen_id', 'absentrado.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'absensisupirdetail.supir_id', 'supir.id')
            ->leftJoin(DB::raw("supir as d with (readuncommitted)"), 'absensisupirdetail.supirold_id', 'd.id');


        DB::table($tempMandor)->insertUsing(['trado_id', 'kodetrado', 'namasupir', 'keterangan', 'absentrado', 'absen_id', 'jam', 'tglbukti', 'supir_id', 'namasupir_old', 'supir_id_old', 'uangjalan'], $absensisupirdetail);

        //trado yang sudah absen dan punya tidak punya supir
        $absensisupirdetail = DB::table('absensisupirdetail')
            ->select(
                'trado.id as trado_id',
                'trado.kodetrado',
                'supir.namasupir',
                'absensisupirdetail.keterangan',
                'absentrado.keterangan as absentrado',
                'absentrado.id as absen_id',
                'absensisupirdetail.jam',
                'absensisupirheader.tglbukti',
                'supir.id as supir_id',
                'd.namasupir as namasupir_old',
                'd.id as supir_id_old',
                'absensisupirdetail.uangjalan',


            )
            ->where('absensisupirheader.tglbukti', date('Y-m-d', strtotime($date)))
            ->where('absensisupirdetail.supir_id', '=', 0)
            ->leftJoin(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirdetail.absensi_id', 'absensisupirheader.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'absensisupirdetail.trado_id', 'trado.id')
            ->leftJoin(DB::raw("absentrado with (readuncommitted)"), 'absensisupirdetail.absen_id', 'absentrado.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'absensisupirdetail.supir_id', 'supir.id')
            ->leftJoin(DB::raw("supir as d with (readuncommitted)"), 'absensisupirdetail.supirold_id', 'd.id');


        //supir Trado yang belum diisi
        DB::table($tempMandor)->insertUsing(['trado_id', 'kodetrado', 'namasupir', 'keterangan', 'absentrado', 'absen_id', 'jam', 'tglbukti', 'supir_id', 'namasupir_old', 'supir_id_old', 'uangjalan'], $absensisupirdetail);

        $update = DB::table($tempMandor);
        $update->update(["memo" => '{"MEMO":"AKTIF","SINGKATAN":"A","WARNA":"#009933","WARNATULISAN":"#FFF"}']);

        $trados = DB::table('trado as a')

            ->select(
                // DB::raw('isnull(b.id,null) as id'),
                'a.id as trado_id',
                'a.kodetrado as kodetrado',
                'c.namasupir as namasupir',
                DB::raw('null as keterangan'),
                DB::raw('null as absentrado'),
                DB::raw('null as absen_id'),
                DB::raw("null as jam"),
                DB::raw("null as tglbukti"),
                DB::raw("(case when (select text from parameter where grp='ABSENSI SUPIR' and subgrp='TRADO MILIK SUPIR')= 'YA' then a.supir_id else null end) as supir_id"),
                'c.namasupir as namasupir_old',
                DB::raw("(case when (select text from parameter where grp='ABSENSI SUPIR' and subgrp='TRADO MILIK SUPIR')= 'YA' then a.supir_id else null end) as supir_id_old"),

            )
            ->leftJoin('supir as c', 'a.supir_id', 'c.id')
            ->where('a.statusaktif', $statusaktif->id)
            ->where('a.statusabsensisupir', $statusabsensisupir->id);

        // ->whereRaw("a.id not in (select trado_id from $tempMandor)");


        if ($tradoMilikSupir->text == 'YA') {
            $trados->whereRaw("NOT EXISTS (
                SELECT 1
                FROM $tempMandor temp
                WHERE (temp.trado_id = a.id and temp.supir_id_old = a.supir_id)
            )")
                ->where('a.supir_id', '!=', 0);
        } else {
            $trados->whereRaw("a.id not in (select trado_id from $tempMandor)");
        }
        // dd(2,$trados->get());
        // dd(DB::table($tempMandor)->get());

        DB::table($tempMandor)->insertUsing(['trado_id', 'kodetrado', 'namasupir', 'keterangan', 'absentrado', 'absen_id', 'jam', 'tglbukti', 'supir_id', 'namasupir_old', 'supir_id_old'], $trados);


        //supir serap yang belum diisi
        $tgl = date('Y-m-d', strtotime($date));
        $trado = DB::table('trado as a')
            ->select(
                // DB::raw('isnull(b.id,null) as id'),
                'a.id as trado_id',
                'a.kodetrado as kodetrado',
                'c.namasupir as namasupir',
                DB::raw('null as keterangan'),
                DB::raw('null as absentrado'),
                DB::raw('null as absen_id'),
                DB::raw("null as jam"),
                DB::raw("null as tglbukti"),
                'c.id as supir_id',
                'c.namasupir as namasupir_old',
                'c.id as supir_id_old',
            )
            ->where('a.statusaktif', $statusaktif->id)
            ->where('a.statusabsensisupir', $statusabsensisupir->id)
            ->leftJoin('supirserap as e', 'e.trado_id', 'a.id')
            ->leftJoin('supir as c', 'e.supirserap_id', 'c.id')
            ->where('e.tglabsensi', date('Y-m-d', strtotime($date)))
            ->where('e.statusapproval', 3)
            ->whereRaw("e.supirserap_id not in (select supirold_id from absensisupirdetail join absensisupirheader on absensisupirheader.nobukti = absensisupirdetail.nobukti where absensisupirheader.tglbukti='$tgl' and absensisupirdetail.trado_id = e.trado_id)");
        // ->whereRaw("e.supirserap_id not in (select supir_id from absensisupirdetail join absensisupirheader on absensisupirheader.nobukti = absensisupirdetail.nobukti where absensisupirheader.tglbukti='$tgl')");

        if ($tradoMilikSupir->text == 'YA') {
            $trado->where('a.supir_id', '!=', 0);
        }

        DB::table($tempMandor)->insertUsing(['trado_id', 'kodetrado', 'namasupir', 'keterangan', 'absentrado', 'absen_id', 'jam', 'tglbukti', 'supir_id', 'namasupir_old', 'supir_id_old'], $trado);
        // isnull(absensisupirdetail.supir_id,0)
        // isnull(supir.namasupir,'')
        $query = DB::table($tempMandor)->from(DB::raw("$tempMandor as a"))
            ->select(
                // DB::raw("row_number() Over(Order By a.trado_id) as id"),
                'a.id',
                'a.trado_id',
                'a.kodetrado as trado',
                DB::raw("isnull(a.supir_id,0) as supir_id"),
                DB::raw("isnull(a.namasupir,'') as supir"),
                DB::raw("isnull(a.keterangan,'') as keterangan"),
                DB::raw("isnull(a.absen_id,0) as absen_id"),
                DB::raw("isnull(a.absentrado,'') as absen"),
                DB::raw("isnull(a.uangjalan,0) as uangjalan"),
                'a.namasupir_old',
                'a.supir_id_old',
                db::raw("count(sp.nobukti) as jlhtrip"),
            )
            ->groupBy(
                "a.id",
                "a.trado_id",
                "a.kodetrado",
                "a.supir_id",
                "a.namasupir",
                "a.keterangan",
                "a.absen_id",
                "a.absentrado",
                "a.uangjalan",
                "a.namasupir_old",
                "a.supir_id_old",
            )
            ->leftJoin('suratpengantar as sp' , function ($join) {
                $join->on('sp.tglbukti', '=', 'a.tglbukti');
                $join->on('sp.trado_id', '=', 'a.trado_id');
                $join->on('sp.supir_id', '=', 'a.supir_id');
            });

        return $query->orderBy('kodetrado', 'asc');
    }

    public function absensiSupirHeader()
    {
        return $this->belongsToMany(AbsensiSupirHeader::class);
    }

    public function trado()
    {
        return $this->belongsTo(Trado::class, 'trado_id');
    }

    public function supir()
    {
        return $this->belongsTo(Supir::class, 'supir_id');
    }

    public function absenTrado()
    {
        return $this->belongsTo(AbsenTrado::class, 'absen_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('d-m-Y H:i:s');
    }

    public function find($id)
    {
        $query = DB::table('absensisupirdetail')->from(DB::raw("absensisupirdetail with (readuncommitted)"))
            ->select(
                DB::raw("isnull(absensisupirdetail.trado_id,0) as trado_id"),
                DB::raw("isnull(trado.kodetrado,'') as trado"),
                DB::raw("isnull(absensisupirdetail.supir_id,0) as supir_id"),
                DB::raw("isnull(supir.namasupir,'') as supir"),
                DB::raw("isnull(absensisupirdetail.keterangan,'') as keterangan"),
                DB::raw("isnull(absensisupirdetail.absen_id,0) as absen_id"),
                DB::raw("isnull(absentrado.keterangan,'') as absen"),
                DB::raw("isnull(absensisupirdetail.jam,'') as jam"),
                DB::raw("isnull(absensisupirdetail.uangjalan,0) as uangjalan"),
            )
            ->join(DB::raw("trado with (readuncommitted)"), 'absensisupirdetail.trado_id', 'trado.id')
            ->join(DB::raw("supir with (readuncommitted)"), 'absensisupirdetail.supir_id', 'supir.id')
            ->leftJoin(DB::raw("absentrado with (readuncommitted)"), 'absensisupirdetail.absen_id', 'absentrado.id')
            ->where('absensisupirdetail.absensi_id', $id);

        $detail = $query->get();
        return $detail;
    }

    public function getAbsensiUangJalan($nobukti)
    {
        $this->setRequestParameters();
        $fetch =  DB::table('gajisupiruangjalan')->from(DB::raw("gajisupiruangjalan with (readuncommitted)"))
            ->select(DB::raw("supir_id, trado_id"))
            ->whereRaw("gajisupir_nobukti = '$nobukti'")
            ->first();

        $query = DB::table('absensisupirdetail')->from(DB::raw("absensisupirdetail with (readuncommitted)"))
            ->select(
                'absensisupirdetail.absensi_id',
                'absensisupirdetail.nobukti',
                'absensisupirheader.tglbukti',
                'absensisupirdetail.uangjalan'
            )
            ->join(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirheader.nobukti', 'absensisupirdetail.nobukti')
            ->whereRaw("absensisupirdetail.nobukti in (select absensisupir_nobukti from gajisupiruangjalan where gajisupir_nobukti='$nobukti')")
            ->where('absensisupirdetail.supir_id', $fetch->supir_id)
            ->where('absensisupirdetail.trado_id', $fetch->trado_id);

        if ($query->first() != null) {
            $this->sort($query);
            $this->filter($query);
            $this->paginate($query);

            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
            $this->totalUangJalan = $query->sum('absensisupirdetail.uangjalan');
            return $query->get();
        } else {
            $this->totalUangJalan = 0;
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'trado') {
                                $query = $query->where('trado.kodetrado', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'supir') {
                                $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tradosupir') {
                                $query = $query->whereRaw("(trim(trado.kodetrado)+' - '+trim(supir.namasupir)) LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'status') {
                                $query = $query->where('absentrado.kodeabsen', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'statusKeterangan') {
                                $query = $query->where('absentrado.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'keterangan_detail') {
                                $query = $query->where("$this->table.keterangan", 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'uangjalan') {
                                $query = $query->whereRaw("format($this->table.uangjalan, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'jumlahtrip') {
                                $query = $query->whereRaw("format(c.jumlah, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti') {
                                $query->whereRaw("format(absensisupirheader.tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'trado') {
                                $query = $query->orWhere('trado.kodetrado', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'supir') {
                                $query = $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tradosupir') {
                                $query = $query->orWhereRaw("(trim(trado.kodetrado)+' - '+trim(supir.namasupir)) LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'status') {
                                $query = $query->orWhere('absentrado.kodeabsen', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'statusKeterangan') {
                                $query = $query->orWhere('absentrado.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'keterangan_detail') {
                                $query = $query->orWhere("$this->table.keterangan", 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'uangjalan') {
                                $query = $query->orWhereRaw("format($this->table.uangjalan, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'jumlahtrip') {
                                $query = $query->orWhereRaw("format(c.jumlah, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti') {
                                $query->orWhereRaw("format(absensisupirheader.tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'trado') {
            return $query->orderBy('trado.kodetrado', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'supir') {
            return $query->orderBy('supir.namasupir', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'status') {
            return $query->orderBy('absenstrado.kodeabsen', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'keterangan_detail') {
            return $query->orderBy($this->table . '.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'jumlahtrip') {
            return $query->orderBy('c.jumlah', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'tglbukti') {
            return $query->orderBy('absensisupirheader.tglbukti', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function processStore(AbsensiSupirHeader $absensiSupirHeader, array $data): AbsensiSupirDetail
    {
        $absensiSupirDetail = new AbsensiSupirDetail();
        $absensiSupirDetail->absensi_id = $data['absensi_id'] ?? '';
        $absensiSupirDetail->nobukti = $data['nobukti'] ?? '';
        $absensiSupirDetail->trado_id = $data['trado_id'] ?? '';
        $absensiSupirDetail->absen_id = $data['absen_id'] ?? '';
        $absensiSupirDetail->supir_id = $data['supir_id'] ?? '';
        $absensiSupirDetail->supirold_id = $data['supirold_id'] ?? '';
        $absensiSupirDetail->jam = $data['jam'] ?? '';
        $absensiSupirDetail->uangjalan = $data['uangjalan'] ?? '';
        $absensiSupirDetail->keterangan = $data['keterangan'] ?? '';
        $absensiSupirDetail->modifiedby = $data['modifiedby'] ?? '';

        if (!$absensiSupirDetail->save()) {
            throw new \Exception("Gagal menyimpan absensi supir detail.");
        }
        return $absensiSupirDetail;
    }
    public function processUpdate(AbsensiSupirDetail $absensiSupirDetail, array $data): AbsensiSupirDetail
    {
        $absensiSupirDetail->absensi_id = $data['absensi_id'] ?? '';
        $absensiSupirDetail->nobukti = $data['nobukti'] ?? '';
        $absensiSupirDetail->trado_id = $data['trado_id'] ?? '';
        $absensiSupirDetail->absen_id = $data['absen_id'] ?? '';
        $absensiSupirDetail->supir_id = $data['supir_id'] ?? '';
        $absensiSupirDetail->supirold_id = $data['supirold_id'] ?? '';
        $absensiSupirDetail->jam = $data['jam'] ?? '';
        $absensiSupirDetail->uangjalan = $data['uangjalan'] ?? '';
        $absensiSupirDetail->keterangan = $data['keterangan'] ?? '';
        $absensiSupirDetail->modifiedby = $data['modifiedby'] ?? '';

        if (!$absensiSupirDetail->save()) {
            throw new \Exception("Gagal menyimpan absensi supir detail.");
        }
        return $absensiSupirDetail;
    }
}
