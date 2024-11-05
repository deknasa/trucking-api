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
                $table->unsignedBigInteger('statusjeniskendaraan')->nullable();
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
            $header = $query;
            $statustrip = DB::table("parameter")->from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->select(
                    'memo',
                    'id'
                )
                ->where('grp', '=', 'TIDAK ADA TRIP')
                ->where('subgrp', '=', 'TIDAK ADA TRIP')
                ->where('text', '=', 'TIDAK ADA TRIP')
                ->first();

            $statustriplengkap = DB::table("parameter")->from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->select(
                    'memo',
                    'id'
                )
                ->where('grp', '=', 'TIDAK ADA TRIP')
                ->where('subgrp', '=', 'TIDAK ADA TRIP')
                ->where('text', '=', 'LENGKAP')
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
                'a.statusjeniskendaraan',
                'c.tglbukti as tglabsensi',
                'b.nobukti',
                'trado.nominalplusborongan'
            )
                ->join(DB::raw("suratpengantar as b with(readuncommitted)"), function ($join) use ($param1) {
                    $join->on('a.supir_id', '=', 'b.supir_id');
                    $join->on('a.trado_id', '=', 'b.trado_id');
                    $join->on('a.statusjeniskendaraan', '=', 'b.statusjeniskendaraan');
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
                'statusjeniskendaraan',
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
                    'a.statusjeniskendaraan',
                    'a.nominalplusborongan',
                    DB::raw("count(a.nobukti) as jumlah")
                )
                ->groupBy('a.trado_id', 'a.supir_id', 'a.statusjeniskendaraan', 'a.nominalplusborongan');


            $tempspgroup = '##tempspgroup' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempspgroup, function ($table) {
                $table->unsignedBigInteger('trado_id')->nullable();
                $table->unsignedBigInteger('supir_id')->nullable();
                $table->unsignedBigInteger('statusjeniskendaraan')->nullable();
                $table->bigInteger('nominalplusborongan')->nullable();
                $table->double('jumlah', 15, 2)->nullable();
            });

            DB::table($tempspgroup)->insertUsing([
                'trado_id',
                'supir_id',
                'statusjeniskendaraan',
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
                $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));
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
                        $join->on("$this->table.statusjeniskendaraan", "=", "c.statusjeniskendaraan");
                    })
                    ->where('absensisupirdetail.absensi_id',request()->absensi_id);
                    // ->where('trado.statusabsensisupir', $statusabsensi);
            } else {
                
                $ricsupirtemp = '##ricsupirtemp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($ricsupirtemp, function ($table) {
                    $table->string('nobukti')->nullable();
                    $table->integer('supir_id')->nullable();
                    $table->date('tgltrip')->nullable();
                });
                $ricSupirQuery = DB::table('gajisupirheader')
                    ->leftJoin('gajisupirdetail', 'gajisupirheader.id', '=', 'gajisupirdetail.gajisupir_id')
                    ->leftJoin('suratpengantar', 'gajisupirdetail.suratpengantar_nobukti', '=', 'suratpengantar.nobukti')
                    ->select(DB::raw("min(gajisupirheader.nobukti) as nobukti"),'gajisupirheader.supir_id', 'suratpengantar.tglbukti')
                    ->where('suratpengantar.tglbukti', $header->tglbukti)
                    ->groupBy('gajisupirheader.supir_id', 'suratpengantar.tglbukti');
                DB::table($ricsupirtemp)->insertUsing(["nobukti","supir_id", "tgltrip"], $ricSupirQuery);               
                

                $query->select(
                    "trado.kodetrado as trado",
                    "supir.namasupir as supir",
                    "absentrado.keterangan as status",
                    "absentrado.keterangan as statusKeterangan",
                    "absentrado.memo as memo",
                    DB::raw("(case when c.nominalplusborongan IS NULL then 0 else c.nominalplusborongan end) as nominalplusborongan"),
                    "$this->table.keterangan as keterangan_detail",
                    DB::raw("LEFT($this->table.jam, 5) as jam"),
                    "$this->table.id",
                    "$this->table.trado_id",
                    "$this->table.supir_id",
                    DB::raw("isnull($this->table.uangjalan,0) as uangjalan"),
                    "$this->table.absensi_id",
                    'supirric.nobukti as nobukti_ric',
                    "jeniskendaraan.text as statusjeniskendaraan",
                    "trado.statusgerobak",
                    "e.nobukti as pengembaliankasgantung_nobukti",
                    "g.nobukti as penerimaan_nobukti",
                    "g.bank_id as bank_penerimaan",
                    db::raw("cast((format(e.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpkgt"),
                    db::raw("cast(cast(format((cast((format(e.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpkgt"),
                    db::raw("cast((format(g.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpenerimaan"),
                    db::raw("cast(cast(format((cast((format(g.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpenerimaan"),
                    DB::raw("left(jam, 5)"),
                    DB::raw("isnull(c.jumlah,0) as jumlahtrip"),
                    DB::raw("(CASE WHEN isnull($this->table.statustambahantrado,0)=0 THEN '' ELSE (CASE WHEN $this->table.statustambahantrado=655 THEN tradotambahan.text ELSE '' end) end) as statustambahantrado"),
                    DB::raw("(CASE WHEN isnull($this->table.statussupirserap,0)=0 THEN '' ELSE (CASE WHEN $this->table.statussupirserap=593 THEN serap.text ELSE '' end) end) as statussupirserap "),
                    DB::Raw("(case when isnull(c.jumlah,0)=0  and isnull(absentrado.kodeabsen,'')='' then '$statustrip->memo' else '$statustriplengkap->memo' end) as statustrip"),
                    DB::Raw("(case when isnull(c.jumlah,0)=0  and isnull(absentrado.kodeabsen,'')='' then '$statustrip->id' else '$statustriplengkap->id' end) as statustripid")

                )
                    ->leftjoin(DB::raw("trado with (readuncommitted)"), "trado.id", "$this->table.trado_id")
                    ->leftjoin(DB::raw("parameter as jeniskendaraan with (readuncommitted)"), "jeniskendaraan.id", "$this->table.statusjeniskendaraan")
                    ->leftjoin(DB::raw("supir with (readuncommitted)"), "supir.id", "$this->table.supir_id")
                    ->leftjoin(DB::raw("absentrado with (readuncommitted)"), "absentrado.id", "$this->table.absen_id")
                    ->leftJoin("parameter as serap", "$this->table.statussupirserap", 'serap.id')
                    ->leftJoin(DB::raw("$ricsupirtemp as supirric with (readuncommitted)"), "$this->table.supir_id", 'supirric.supir_id')
                    ->leftJoin(DB::raw("parameter as tradotambahan with (readuncommitted)"), "$this->table.statustambahantrado", 'tradotambahan.id')
                    ->leftjoin(DB::raw($tempspgroup . " as c"), function ($join) {
                        $join->on("$this->table.supir_id", "=", "c.supir_id");
                        $join->on("$this->table.trado_id", "=", "c.trado_id");
                        $join->on("$this->table.statusjeniskendaraan", "=", "c.statusjeniskendaraan");
                    })
                    ->leftJoin('gajisupiruangjalan as b', function ($join) {
                        $join->on($this->table . '.nobukti', '=', 'b.absensisupir_nobukti')
                            ->on($this->table . '.supir_id', '=', 'b.supir_id');
                    })
                    ->leftJoin('prosesgajisupirdetail as f', function ($join) {
                        $join->on('b.gajisupir_nobukti', '=', 'f.gajisupir_nobukti')
                            ->on($this->table . '.supir_id', '=', 'f.supir_id')
                            ->on($this->table . '.trado_id', '=', 'f.trado_id');
                    })
                    ->leftJoin('prosesgajisupirheader as d', 'f.prosesgajisupir_id', '=', 'd.id')
                    ->leftJoin('pengembaliankasgantungheader as e', 'd.pengembaliankasgantung_nobukti', '=', 'e.nobukti')
                    ->leftJoin('penerimaanheader as g', 'e.penerimaan_nobukti', '=', 'g.nobukti')
                    ->where('trado.statusabsensisupir', $statusabsensi);

                if (request()->from == 'tidaklengkap') {
                    $query->leftjoin(DB::raw($tempsp . " as tempsp"), function ($join) {
                        $join->on("$this->table.supir_id", "=", "c.supir_id");
                        $join->on("$this->table.trado_id", "=", "c.trado_id");
                    })
                        ->whereRaw("isnull($this->table.absen_id,0)=0")
                        ->whereRaw("isnull(tempsp.nobukti,'')=''");
                }

                if (request()->from == 'viewHistory') {
                    $isMandor = auth()->user()->isMandor();
                    $isAdmin = auth()->user()->isAdmin();

                    if (!$isAdmin) {
                        if ($isMandor) {
                            $query->Join(DB::raw($tempmandordetail . " as mandordetail"), "$this->table.mandor_id", 'mandordetail.mandor_id');
                        }
                    }
                }

            
                if ($getAbsen) {                    
                    $isTangki = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'ABSENSI TANGKI')->first()->text ?? 'TIDAK';
                    $statusGandengan = (new Parameter())->cekId('STATUS JENIS KENDARAAN', 'STATUS JENIS KENDARAAN', 'GANDENGAN');
                    
                    $statusJenisKendaraan = ($isTangki == 'YA') ? request()->statusjeniskendaraan : $statusGandengan;
                    $isMandor = auth()->user()->isMandor();
                    $isAdmin = auth()->user()->isAdmin();

                    if (!$isAdmin) {
                        if ($isMandor) {
                            $query->where("$this->table.statusjeniskendaraan", $statusJenisKendaraan);
                            $query->Join(DB::raw($tempmandordetail . " as mandordetail"), "$this->table.mandor_id", 'mandordetail.mandor_id');

                            // dd($query->get());
                        }
                    } else {
                        $query->where("$this->table.statusjeniskendaraan", $statusJenisKendaraan);
                    }

                    if (request()->from == 'pengajuantripinap') {
                        $aksi = request()->aksi;
                        $tgltrip = date('Y-m-d', strtotime(request()->tgltrip));
                        $query->addSelect(DB::raw("(trim(trado.kodetrado)+' - '+trim(supir.namasupir)) as tradosupir"))
                            ->where("$this->table.supir_id", '!=', 0);
                        if ($aksi == 'add') {
                            $query->whereRaw("absensisupirdetail.trado_id not in (select trado_id from pengajuantripinap where tglabsensi='$tgltrip')");
                        } else {
                            $id = request()->pengajuantrip_id ?? 0;
                            $query->whereRaw("absensisupirdetail.trado_id not in (select trado_id from pengajuantripinap where tglabsensi='$tgltrip' and id <> $id)");
                        }
                    } else if (request()->from == 'tripinap') {
                        $aksi = request()->aksi;
                        $tgltrip = date('Y-m-d', strtotime(request()->tgltrip));

                        $getBatasInput = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'BATAS TRIP INAP')->where('subgrp', 'BATAS TRIP INAP')->first()->text;

                        $batas = date('Y-m-d', strtotime("-$getBatasInput days")) . ' 00:00:00';
                        $now = date('Y-m-d H:i:s');
                        $awal = date('Y-m-d') . ' 00:00:00';
                        $akhir = date('Y-m-d') . ' 23:59:59';
                        $query->addSelect(DB::raw("(trim(trado.kodetrado)+' - '+trim(supir.namasupir)) as tradosupir"))
                            ->where("$this->table.supir_id", '!=', 0);
                        // ->whereRaw("CONVERT(VARCHAR(10), created_at, 23) = '$now'")

                        $getPengajuan3Hari = DB::table("pengajuantripinap")->from(DB::raw("pengajuantripinap with (readuncommitted)"))
                            ->select('id', 'trado_id')
                            ->where('statusapproval', 3)
                            ->where('tglabsensi', $tgltrip)
                            ->whereBetween('created_at', [$batas, $now]);
                        if ($aksi == 'add') {
                            $getPengajuan3Hari->whereRaw("trado_id not in (select trado_id from tripinap where tglabsensi='$tgltrip')");
                        } else {
                            $id = request()->tripinap_id ?? 0;
                            $getPengajuan3Hari->whereRaw("trado_id not in (select trado_id from tripinap where tglabsensi='$tgltrip' and id <> $id)");
                        }
                        $tempPengajuan = '##tempPengajuan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                        Schema::create($tempPengajuan, function ($table) {
                            $table->bigInteger('id')->nullable();
                            $table->bigInteger('trado_id')->nullable();
                        });

                        DB::table($tempPengajuan)->insertUsing([
                            'id',
                            'trado_id',
                        ], $getPengajuan3Hari);

                        $getPengajuan3Hari = DB::table("pengajuantripinap")->from(DB::raw("pengajuantripinap with (readuncommitted)"))
                            ->select('id', 'trado_id')
                            ->where('statusapproval', 3)
                            ->where('tglabsensi', $tgltrip)
                            ->where('statusapprovallewatbataspengajuan', 3)
                            ->whereNotBetween('created_at', [$batas, $now])
                            ->whereRaw("tglbataslewatbataspengajuan >= '$awal'")
                            ->whereRaw("tglbataslewatbataspengajuan <= '$akhir'");
                        if ($aksi == 'add') {
                            $getPengajuan3Hari->whereRaw("trado_id not in (select trado_id from tripinap where tglabsensi='$tgltrip')");
                        } else {
                            $id = request()->tripinap_id ?? 0;
                            $getPengajuan3Hari->whereRaw("trado_id not in (select trado_id from tripinap where tglabsensi='$tgltrip' and id <> $id)");
                        }
                        DB::table($tempPengajuan)->insertUsing([
                            'id',
                            'trado_id',
                        ], $getPengajuan3Hari);


                        if ($aksi == 'add') {
                            $query->whereRaw("absensisupirdetail.trado_id in (select trado_id from $tempPengajuan)");
                        } else {
                            $id = request()->pengajuantrip_id ?? 0;
                            $query->whereRaw("absensisupirdetail.trado_id in (select trado_id from $tempPengajuan where id <> $id)");
                        }
                    } else {

                        $query->addSelect(DB::raw("(trim(trado.kodetrado)+' - '+trim(supir.namasupir)) as tradosupir"))
                            ->where("$this->table.supir_id", '!=', 0)
                            ->whereRaw("(absentrado.kodeabsen is null OR absentrado.kodeabsen='I' OR absentrado.kodeabsen='G')");
                        // dd($query->get());
                    }
                }
                if ($isProsesUangjalan == true) {
                    // dd($query->get());
                    $aksi = request()->aksi;
                    $uangJalanId = request()->uangJalanId ?? 0;
                    $absensiId = request()->absensi_id ?? 0;
                    $query->where('absensisupirdetail.uangjalan', '!=', 0);
                    $getProsesUangjalan = DB::table("prosesuangjalansupirheader")->from(DB::raw("prosesuangjalansupirheader as uangjalan with (readuncommitted)"))
                        ->join(DB::raw("absensisupirheader"), 'uangjalan.absensisupir_nobukti', 'absensisupirheader.nobukti')
                        ->select('uangjalan.id', 'uangjalan.supir_id', 'uangjalan.trado_id', 'absensisupirheader.nobukti', 'absensisupirheader.id as absensiId')
                        ->where('absensisupirheader.id', $absensiId);

                    $tempProsesUangjalan = '##tempProsesUangjalan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                    Schema::create($tempProsesUangjalan, function ($table) {
                        $table->bigInteger('id')->nullable();
                        $table->bigInteger('supir_id')->nullable();
                        $table->bigInteger('trado_id')->nullable();
                        $table->string('nobukti', 1000)->nullable();
                        $table->bigInteger('absensiId')->nullable();
                    });

                    DB::table($tempProsesUangjalan)->insertUsing([
                        'id',
                        'supir_id',
                        'trado_id',
                        'nobukti',
                        'absensiId',
                    ], $getProsesUangjalan);

                    $query->addSelect(DB::raw("(trim(trado.kodetrado)+' - '+trim(supir.namasupir)) as tradosupir"));
                    if ($aksi == 'add') {
                        $query->whereRaw("absensisupirdetail.supir_id not in (select supir_id from $tempProsesUangjalan)");
                    } else {

                        $query->whereRaw("absensisupirdetail.supir_id not in (select supir_id from $tempProsesUangjalan where id <> $uangJalanId)");
                    }
                }
           
                $this->totalRows = $query->count();
                $this->totalNominal = $query->sum('uangjalan');
          
                $this->jlhtrip = $query->sum('c.jumlah');
                $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
                $this->filter($query);
                $this->sort($query);
                // dd($query->tosql());
                $this->paginate($query);
                // dd($query->get());
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

        $statustrip = DB::table("parameter")->from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'TIDAK ADA TRIP')
            ->where('subgrp', '=', 'TIDAK ADA TRIP')
            ->where('text', '=', 'TIDAK ADA TRIP')
            ->first();

        $tempsp = '##tempsp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsp, function ($table) {
            $table->unsignedBigInteger('absensi_id')->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->unsignedBigInteger('statusjeniskendaraan')->nullable();
            $table->date('tglabsensi')->nullable();
            $table->string('nobukti', 100)->nullable();
            $table->bigInteger('nominalplusborongan')->nullable();
        });
        
        $param1 = date('Y-m-d', strtotime($date));
        $statustriplengkap = DB::table("parameter")->from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'TIDAK ADA TRIP')
            ->where('subgrp', '=', 'TIDAK ADA TRIP')
            ->where('text', '=', 'LENGKAP')
            ->first();

            $querysp = DB::table('absensisupirdetail')->from(
                DB::raw("absensisupirdetail as a with (readuncommitted)")
            )->select(
                'a.absensi_id',
                'a.trado_id',
                'a.supir_id',
                'a.statusjeniskendaraan',
                'c.tglbukti as tglabsensi',
                'b.nobukti',
                'trado.nominalplusborongan'
            )
                ->join(DB::raw("suratpengantar as b with(readuncommitted)"), function ($join) use ($param1) {
                    $join->on('a.supir_id', '=', 'b.supir_id');
                    $join->on('a.trado_id', '=', 'b.trado_id');
                    $join->on('a.statusjeniskendaraan', '=', 'b.statusjeniskendaraan');
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
                'statusjeniskendaraan',
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
                    'a.statusjeniskendaraan',
                    'a.nominalplusborongan',
                    DB::raw("count(a.nobukti) as jumlah")
                )
                ->groupBy('a.trado_id', 'a.supir_id', 'a.statusjeniskendaraan', 'a.nominalplusborongan');


            $tempspgroup = '##tempspgroup' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempspgroup, function ($table) {
                $table->unsignedBigInteger('trado_id')->nullable();
                $table->unsignedBigInteger('supir_id')->nullable();
                $table->unsignedBigInteger('statusjeniskendaraan')->nullable();
                $table->bigInteger('nominalplusborongan')->nullable();
                $table->double('jumlah', 15, 2)->nullable();
            });

            DB::table($tempspgroup)->insertUsing([
                'trado_id',
                'supir_id',
                'statusjeniskendaraan',
                'nominalplusborongan',
                'jumlah',
            ], $queryspgroup);


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
                ->leftJoin(DB::raw($tempmandordetaillogin . ' as b'), 'a.mandor_id', '=', 'b.mandor_id')
                ->leftJoin(DB::raw($tempmandorbukaabsen . ' as c'), 'a.user_id', '=', 'c.mandor_user_id')
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
            $table->string('nobukti')->nullable();
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
            $table->double('uangjalan')->nullable();
            $table->integer('statusjeniskendaraan')->Length(11)->nullable();
            $table->string('statusjeniskendaraannama')->nullable();
            $table->integer('statussupirserap')->Length(11)->nullable();
            $table->integer('statustambahantrado')->Length(11)->nullable();
            $table->integer('statustrip')->Length(11)->nullable();
        });
        $tempAbsensi = '##tempAbsensi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempAbsensi, function ($table) {
            $table->tinyIncrements('id');
            $table->string('nobukti')->nullable();
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
            $table->double('uangjalan')->nullable();
            $table->integer('statusjeniskendaraan')->Length(11)->nullable();
            $table->string('statusjeniskendaraannama')->nullable();
            $table->integer('statussupirserap')->Length(11)->nullable();
            $table->integer('statustambahantrado')->Length(11)->nullable();
            $table->integer('statustrip')->Length(11)->nullable();
        });

        //trado yang sudah absen dan punya supir
        $absensisupirdetail = DB::table('absensisupirdetail')
            ->select(
                'absensisupirdetail.nobukti',
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
                db::raw("isnull(absensisupirdetail.statusjeniskendaraan,0) as statusjeniskendaraan"),
                'statusjeniskendaraan.text as statusjeniskendaraannama',
                db::raw("isnull(absensisupirdetail.statussupirserap,0) as statussupirserap"),
                db::raw("isnull(absensisupirdetail.statustambahantrado,0) as statustambahantrado"),
                DB::Raw("(case when isnull(c.jumlah,0)=0  and isnull(absentrado.kodeabsen,'')='' then '$statustrip->id' else '$statustriplengkap->id' end) as statustrip")


            )
            ->where('absensisupirheader.tglbukti', date('Y-m-d', strtotime($date)))
            ->where('absensisupirdetail.supir_id', '!=', 0)
            ->leftJoin(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirdetail.absensi_id', 'absensisupirheader.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'absensisupirdetail.trado_id', 'trado.id')
            ->leftJoin(DB::raw("absentrado with (readuncommitted)"), 'absensisupirdetail.absen_id', 'absentrado.id')
            ->leftJoin(DB::raw("parameter as statusjeniskendaraan with (readuncommitted)"), 'absensisupirdetail.statusjeniskendaraan', 'statusjeniskendaraan.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'absensisupirdetail.supir_id', 'supir.id')
            ->leftJoin(DB::raw("supir as d with (readuncommitted)"), 'absensisupirdetail.supirold_id', 'd.id')
            ->leftjoin(DB::raw($tempspgroup . " as c"), function ($join) {
                $join->on("$this->table.supir_id", "=", "c.supir_id");
                $join->on("$this->table.trado_id", "=", "c.trado_id");
                $join->on("$this->table.statusjeniskendaraan", "=", "c.statusjeniskendaraan");
            });

        if (!$isAdmin) {
            if ($isMandor) {
                $absensisupirdetail->Join(DB::raw($tempmandordetail . " as mandordetail"), 'trado.mandor_id', 'mandordetail.mandor_id');
            }
        }
        DB::table($tempMandor)->insertUsing(['nobukti', 'trado_id', 'kodetrado', 'namasupir', 'keterangan', 'absentrado', 'absen_id', 'jam', 'tglbukti', 'supir_id', 'namasupir_old', 'supir_id_old', 'uangjalan', 'statusjeniskendaraan', 'statusjeniskendaraannama', 'statussupirserap', 'statustambahantrado', 'statustrip'], $absensisupirdetail);


        $parameter = new Parameter();
        $statuslibur = $parameter->cekText('ABSENSI SUPIR SERAP', 'L') ?? '0';
        $ketstatuslibur = db::table("absentrado")->from(db::raw("absentrado a with (readuncommitted)"))
            ->select(
                'a.keterangan'
            )
            ->where('a.id', $statuslibur)
            ->first()->keterangan ?? '';


        //trado yang sudah absen dan punya tidak punya supir
        $absensisupirdetail = DB::table('absensisupirdetail')
            ->select(
                'absensisupirdetail.nobukti',
                'trado.id as trado_id',
                'trado.kodetrado',
                'supir.namasupir',
                'absensisupirdetail.keterangan',
                // 'absentrado.keterangan as absentrado',
                // 'absentrado.id as absen_id',
                DB::raw("(case when isnull(trado.mandor_id,0)=0 and isnull(absensisupirdetail.absen_id,0)=0 then '" . $ketstatuslibur . "' else absentrado.keterangan end) as absentrado"),
                DB::raw("(case when isnull(trado.mandor_id,0)=0 and isnull(absensisupirdetail.absen_id,0)=0 then " . $statuslibur . " else absentrado.id end) as absen_id"),

                'absensisupirdetail.jam',
                'absensisupirheader.tglbukti',
                'supir.id as supir_id',
                'd.namasupir as namasupir_old',
                'd.id as supir_id_old',
                'absensisupirdetail.uangjalan',
                db::raw("isnull(absensisupirdetail.statusjeniskendaraan,0) as statusjeniskendaraan"),
                'statusjeniskendaraan.text as statusjeniskendaraannama',
                db::raw("isnull(absensisupirdetail.statussupirserap,0) as statussupirserap"),
                db::raw("isnull(absensisupirdetail.statustambahantrado,0) as statustambahantrado")

            )
            ->where('absensisupirheader.tglbukti', date('Y-m-d', strtotime($date)))
            ->where('absensisupirdetail.supir_id', '=', 0)
            ->leftJoin(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirdetail.absensi_id', 'absensisupirheader.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'absensisupirdetail.trado_id', 'trado.id')
            ->leftJoin(DB::raw("absentrado with (readuncommitted)"), 'absensisupirdetail.absen_id', 'absentrado.id')
            ->leftJoin(DB::raw("parameter as statusjeniskendaraan with (readuncommitted)"), 'absensisupirdetail.statusjeniskendaraan', 'statusjeniskendaraan.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'absensisupirdetail.supir_id', 'supir.id')
            ->leftJoin(DB::raw("supir as d with (readuncommitted)"), 'absensisupirdetail.supirold_id', 'd.id');
        if (!$isAdmin) {
            if ($isMandor) {
                $absensisupirdetail->Join(DB::raw($tempmandordetail . " as mandordetail"), 'trado.mandor_id', 'mandordetail.mandor_id');

                //  $absensisupirdetail->where('trado.mandor_id',$isMandor->mandor_id);
            }
        }

        //supir Trado yang belum diisi
        DB::table($tempMandor)->insertUsing(['nobukti', 'trado_id', 'kodetrado', 'namasupir', 'keterangan', 'absentrado', 'absen_id', 'jam', 'tglbukti', 'supir_id', 'namasupir_old', 'supir_id_old', 'uangjalan', 'statusjeniskendaraan', 'statusjeniskendaraannama', 'statussupirserap', 'statustambahantrado'], $absensisupirdetail);
        DB::table($tempAbsensi)->insertUsing(['nobukti', 'trado_id', 'kodetrado', 'namasupir', 'keterangan', 'absentrado', 'absen_id', 'jam', 'tglbukti', 'supir_id', 'namasupir_old', 'supir_id_old', 'uangjalan', 'statusjeniskendaraan', 'statusjeniskendaraannama', 'statussupirserap', 'statustambahantrado'], $absensisupirdetail);


        $update = DB::table($tempMandor);
        $update->update(["memo" => '{"MEMO":"AKTIF","SINGKATAN":"A","WARNA":"#009933","WARNATULISAN":"#FFF"}']);


        $trados = DB::table('trado as a')

            ->select(
                // DB::raw('isnull(b.id,null) as id'),
                'a.id as trado_id',
                'a.kodetrado as kodetrado',
                'c.namasupir as namasupir',
                DB::raw('null as keterangan'),
                DB::raw("(case when isnull(a.mandor_id,0)=0 then '" . $ketstatuslibur . "' else null end) as absentrado"),
                DB::raw("(case when isnull(a.mandor_id,0)=0 then " . $statuslibur . " else null end) as absen_id"),
                DB::raw("null as jam"),
                DB::raw("null as tglbukti"),
                DB::raw("(case when (select text from parameter where grp='ABSENSI SUPIR' and subgrp='TRADO MILIK SUPIR')= 'YA' then a.supir_id else null end) as supir_id"),
                'c.namasupir as namasupir_old',
                DB::raw("(case when (select text from parameter where grp='ABSENSI SUPIR' and subgrp='TRADO MILIK SUPIR')= 'YA' then a.supir_id else null end) as supir_id_old"),

            )
            ->leftJoin('supir as c', 'a.supir_id', 'c.id')
            ->leftJoin(DB::raw($tempAbsensi . " as b "), function ($join) {
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
        } else {
            $trados->whereRaw("a.id not in (select trado_id from $tempMandor)");
        }

        DB::table($tempMandor)->insertUsing(['trado_id', 'kodetrado', 'namasupir', 'keterangan', 'absentrado', 'absen_id', 'jam', 'tglbukti', 'supir_id', 'namasupir_old', 'supir_id_old'], $trados);

        //supir serap yang belum diisi
        $tgl = date('Y-m-d', strtotime($date));

        $query = DB::table($tempMandor)->from(DB::raw("$tempMandor as a"))
            ->select(
                // DB::raw("row_number() Over(Order By a.trado_id) as id"),
                'a.id',
                'a.nobukti',
                'a.trado_id',
                'a.kodetrado as trado',
                DB::raw("isnull(a.namasupir,'') as supir"),
                DB::raw("isnull(a.keterangan,'') as keterangan"),
                DB::raw("isnull(a.absentrado,'') as absen"),
                DB::raw("isnull(a.absen_id,0) as absen_id"),
                'a.jam',
                DB::raw("cast((case when year(isnull(a.tglbukti,'1900/1/1'))=1900 then null else format(a.tglbukti,'yyyy/MM/dd')  end)  as datetime )as tglbukti"),
                'a.supir_id',
                'a.namasupir_old',
                'a.supir_id_old',
                db::raw("count(sp.nobukti) as jlhtrip"),
                'a.uangjalan',
                'a.statusjeniskendaraan',
                'a.statusjeniskendaraannama',
                'a.memo',
                db::raw("isnull(a.statussupirserap,0) as statussupirserap"),
                db::raw("isnull(a.statustambahantrado,0) as statustambahantrado")
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
                'a.nobukti',
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
                'a.uangjalan',
                'a.statusjeniskendaraan',
                'a.statusjeniskendaraannama',
                'a.memo',
                'a.statussupirserap',
                'a.statustambahantrado'
            )
            ->leftJoin('suratpengantar as sp', function ($join) {
                $join->on('sp.tglbukti', '=', 'a.tglbukti');
                $join->on('sp.trado_id', '=', 'a.trado_id');
                $join->on('sp.supir_id', '=', 'a.supir_id');
                $join->on('sp.statusjeniskendaraan', '=', 'a.statusjeniskendaraan');
            });



        $tempdata = '##tempdata' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdata, function ($table) {
            $table->integer('id')->nullable();
            $table->string('nobukti')->nullable();
            $table->integer('trado_id')->nullable();
            $table->string('trado')->nullable();
            $table->string('supir')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('absen')->nullable();
            $table->integer('absen_id')->nullable();
            $table->time('jam')->nullable();
            $table->date('tglbukti')->nullable();
            $table->integer('supir_id')->nullable();
            $table->string('namasupir_old')->nullable();
            $table->integer('supir_id_old')->nullable();
            $table->integer('jlhtrip')->nullable();
            $table->integer('statusjeniskendaraan')->nullable();
            $table->string('statusjeniskendaraannama')->nullable();
            $table->double('uangjalan')->nullable();
            $table->longText('memo')->nullable();
            $table->integer('statussupirserap')->Length(11)->nullable();
            $table->integer('statustambahantrado')->Length(11)->nullable();
        });

        DB::table($tempdata)->insertUsing([
            'id', 'nobukti', 'trado_id', 'trado', 'supir', 'keterangan', 'absen', 'absen_id', 'jam',
            'tglbukti', 'supir_id', 'namasupir_old', 'supir_id_old', 'jlhtrip', 'uangjalan',
            'statusjeniskendaraan',
            'statusjeniskendaraannama',
            'memo', 'statussupirserap', 'statustambahantrado'

        ], $query);

        // temporary 

        $tempdatamandor = '##tempdatamandor' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdatamandor, function ($table) {
            $table->integer('mandor_id')->nullable();
            $table->datetime('tglbatas')->nullable();
        });

        $querymandor = db::table('mandordetail')->from(db::raw("mandordetail a with (readuncommitted)"))
            ->select(
                'a.mandor_id',
                db::raw("max(b.tglbatas) as tglbatas")
            )
            ->join(db::raw("bukaabsensi b with (readuncommitted)"), 'a.user_id', 'b.mandor_user_id')
            ->where('b.tglabsensi', date('Y-m-d', strtotime($date)))
            ->whereRaw("b.tglbatas>=getdate()")
            ->groupBy('a.mandor_id');

        DB::table($tempdatamandor)->insertUsing(['mandor_id', 'tglbatas'], $querymandor);

        $tempdatatrado = '##tempdatatrado' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdatatrado, function ($table) {
            $table->integer('trado_id')->nullable();
            $table->datetime('tglbatas')->nullable();
        });

        $querytrado = db::table('absensisupirdetail')->from(db::raw("absensisupirdetail a with (readuncommitted)"))
            ->select(
                'a.trado_id',
                'd.tglbatas',
            )
            ->join(db::raw("absensisupirheader b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(db::raw("trado c with (readuncommitted)"), 'a.trado_id', 'c.id')
            ->join(db::raw($tempdatamandor . " d with (readuncommitted)"), 'c.mandor_id', 'd.mandor_id')
            ->where('b.tglbukti', date('Y-m-d', strtotime($date)));

        DB::table($tempdatatrado)->insertUsing(['trado_id', 'tglbatas'], $querytrado);



        $tempdatahasil = '##tempdatahasil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdatahasil, function ($table) {
            $table->integer('trado_id')->nullable();
            $table->integer('supir_id')->nullable();
            $table->datetime('tglbatas')->nullable();
        });


        $queryhasil = db::table('absensisupirdetail')->from(db::raw("absensisupirdetail a with (readuncommitted)"))
            ->select(
                'a.trado_id',
                db::raw('max(a.supir_id) as supir_id'),
                db::raw('max(c.tglbatas) as tglbatas'),
            )
            ->join(db::raw("absensisupirheader b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->join(db::raw($tempdatatrado . " c with (readuncommitted)"), 'a.trado_id', 'c.trado_id')
            ->groupBy('a.trado_id')
            ->where('b.tglbukti', date('Y-m-d', strtotime($date)));

        DB::table($tempdatahasil)->insertUsing(['trado_id', 'supir_id', 'tglbatas'], $queryhasil);

        $batasJamEdit = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', '=', 'BATAS JAM EDIT ABSENSI')
            ->where('subgrp', '=', 'BATAS JAM EDIT ABSENSI')
            ->first();

        $tempidabsen = '##tempidabsen' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempidabsen, function ($table) {
            $table->integer('text')->nullable();
        });

        $queryabsen = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.text'
            )
            ->where('a.grp', 'ABSENSI TANPA UANG JALAN')
            ->orderby('a.text', 'asc');

        DB::table($tempidabsen)->insertUsing(['text'], $queryabsen);

        $tidakadasupirabsensi = '##tidakadasupirabsensi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tidakadasupirabsensi, function ($table) {
            $table->integer('text')->nullable();
        });

        $queryaTidakadaSupir = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.text'
            )
            ->where('a.grp', 'ABSEN TIDAK ADA SUPIR')
            ->orderby('a.text', 'asc');

        DB::table($tidakadasupirabsensi)->insertUsing(['text'], $queryaTidakadaSupir);
        $ricsupirtemp = '##ricsupirtemp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($ricsupirtemp, function ($table) {
            $table->integer('supir_id')->nullable();
            $table->date('tgltrip')->nullable();
        });

        $ricSupirQuery = DB::table('gajisupirheader')
            ->leftJoin('gajisupirdetail', 'gajisupirheader.id', '=', 'gajisupirdetail.gajisupir_id')
            ->leftJoin('suratpengantar', 'gajisupirdetail.suratpengantar_nobukti', '=', 'suratpengantar.nobukti')
            ->select('gajisupirheader.supir_id', 'suratpengantar.tglbukti')
            ->where('suratpengantar.tglbukti', $date)
            ->groupBy('gajisupirheader.supir_id', 'suratpengantar.tglbukti');
        DB::table($ricsupirtemp)->insertUsing(["supir_id", "tgltrip"], $ricSupirQuery);


        $tempric = '##tempric' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempric, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->integer('supir_id')->nullable();
            $table->longtext('gajisupir_nobukti')->nullable();
        });

        $queryric = db::table("gajisupiruangjalan")->from(db::raw("gajisupiruangjalan a with (readuncommitted)"))
            ->select(
                'c.nobukti',
                'a.supir_id',
                db::raw("isnull(STRING_AGG(cast(a.gajisupir_nobukti  as nvarchar(max)), ', '),'') as gajisupir_nobukti")

            )
            ->join(DB::raw("absensisupirdetail as b with (readuncommitted)"), function ($join) {
                $join->on("a.supir_id", "=", "b.supir_id");
                $join->on("a.absensisupir_nobukti", "=", "b.nobukti");
            })
            ->join(db::raw("absensisupirheader c with (readuncommitted)"), 'a.absensisupir_nobukti', 'c.nobukti')
            ->where('c.tglbukti', date('Y-m-d', strtotime($date)))
            ->groupby('c.nobukti')
            ->groupby('a.supir_id');

        DB::table($tempric)->insertUsing(['nobukti', 'supir_id', 'gajisupir_nobukti'], $queryric);
        $tglbatas = (new AbsensiSupirHeader)->getTomorrowDate($date);
        // dd(db::table($tempdatahasil)->get());
        $query = db::table($tempdata)->from(db::raw($tempdata . " a"))
            ->select(
                'a.id',
                'a.nobukti',
                'a.trado_id',
                'a.trado',
                'a.supir',
                'a.keterangan',
                // 'a.absen',
                // 'a.absen_id',
                DB::raw("(case when isnull(c.mandor_id,0)=0 and isnull(a.absen_id,0)=0 then '" . $ketstatuslibur . "' else a.absen end) as absen"),
                DB::raw("(case when isnull(c.mandor_id,0)=0 and isnull(a.absen_id,0)=0  then " . $statuslibur . " else a.absen_id end) as absen_id"),

                'a.jam',
                'a.tglbukti',
                'a.supir_id',
                'a.namasupir_old',
                'a.supir_id_old',
                'a.jlhtrip',
                'a.memo',
                'a.statusjeniskendaraan',
                'a.statusjeniskendaraannama',
                'supirric.tgltrip as tgltrip',
                DB::RAW("isnull(a.uangjalan,0) as uangjalan"),
                db::raw("format(cast(isnull(b.tglbatas,
                    (case when year(isnull(a.tglbukti,'1900/1/1'))=1900  then  '" .  date('Y-m-d', strtotime($tglbatas)) . " " . $batasJamEdit->text . "'  else   ' " . date('Y-m-d', strtotime($tglbatas)) . " " . $batasJamEdit->text . "' end)
                    ) as datetime),'dd-MM-yyyy HH:mm:ss') as tglbatas"),
                db::raw("(case when cast(format(cast(isnull(b.tglbatas,
                    (case when year(isnull(a.tglbukti,'1900/1/1'))=1900  then  '" .  date('Y-m-d', strtotime($tglbatas)) . " " . $batasJamEdit->text . "' else   ' " . date('Y-m-d', strtotime($tglbatas)) . " " . $batasJamEdit->text . "' end)
                    ) as datetime),'yyyy/MM/dd HH:mm:ss') as datetime)>=getdate() then 1 else 0 end) 
                    as berlaku"),
                db::raw("(case when cast(format(cast(isnull(b.tglbatas,
                    (case when year(isnull(a.tglbukti,'1900/1/1'))=1900  then  '" .  date('Y-m-d', strtotime($tglbatas)) . " " . $batasJamEdit->text . "' else   ' " . date('Y-m-d', strtotime($tglbatas)) . " " . $batasJamEdit->text . "' end)
                    ) as datetime),'yyyy/MM/dd HH:mm:ss') as datetime)>=getdate() then 1 else 0 end) 
                    as berlaku"),
                db::raw("(CASE WHEN a.absen_id IN (SELECT text FROM " . $tempidabsen . ") or isnull(d.supir_id,0)<>0 THEN 'readonly' ELSE '' END) AS uangjalan_readonly"),
                db::raw("(CASE WHEN a.absen_id IN (SELECT text FROM " . $tidakadasupirabsensi . ") or isnull(d.supir_id,0)<>0 THEN 'readonly' ELSE '' END) AS tidakadasupir"),
                // DB::raw("(CASE WHEN isnull(e.nobukti,0) = 0 THEN '' ELSE 'readonly' END) as pujnobukti_readonly"),
                DB::raw("(CASE WHEN e.nobukti  IS NULL THEN '' ELSE 'readonly' END) as pujnobukti_readonly"),

                DB::raw("(CASE WHEN isnull(a.statussupirserap,0)=0 THEN '' ELSE
                (CASE WHEN a.statussupirserap=593 THEN parameter.text ELSE '' end) end) as statussupirserap"),
                DB::raw("(CASE WHEN isnull(a.statustambahantrado,0)=0 THEN '' ELSE
                (CASE WHEN a.statustambahantrado=655 THEN tambahtrado.text ELSE '' end) end) as statustambahantrado
            "),

                // db::raw("format(cast(b.tglbatas as datetime),'dd-MM-yyyy HH:mm:ss') as tglbatas1"),
                // db::raw("format(cast(format(a.tglbukti,'yyyy/MM/dd')+' 12:00:00 as datetime),'dd-MM-yyyy HH:mm:ss') as tglbatas2"),
                // db::raw("format(cast('".  date('Y-m-d', strtotime($date)) ." 12:00:00' as datetime) ,'dd-MM-yyyy HH:mm:ss') as tglbatas3"),
            )
            ->leftjoin(DB::raw($tempdatahasil . " as b "), function ($join) {
                // $join->on('a.supir_id', '=', 'b.supir_id');
                $join->on('a.trado_id', '=', 'b.trado_id');
            })
            ->leftjoin(DB::raw("prosesuangjalansupirheader as e"), function ($join) {
                $join->on('a.nobukti', '=', 'e.absensisupir_nobukti');
                $join->on('a.supir_id', '=', 'e.supir_id');
                $join->on('a.trado_id', '=', 'e.trado_id');
            })
            ->leftJoin("parameter", 'a.statussupirserap', 'parameter.id')
            ->leftJoin("parameter as tambahtrado", 'a.statustambahantrado', 'tambahtrado.id')
            ->leftJoin(db::raw($tempric . " as d"), 'a.supir_id', 'd.supir_id')
            ->join(db::raw("trado c with (readuncommitted)"), 'a.trado_id', 'c.id')
            ->leftJoin(DB::raw("$ricsupirtemp as supirric with (readuncommitted)"), 'a.supir_id', 'supirric.supir_id')
            ->orderBy('a.trado', 'asc')
            ->orderBy('a.statussupirserap', 'desc')
            ->orderBy('a.statustambahantrado', 'desc')
            ->orderBy('a.supir', 'asc');

        // dd($query->tosql());
        // 


        return $query;
    }
    // public function tableTemp2($date = 'now')
    // {
    //     $mandorId = false;
    //     $isMandor = auth()->user()->isMandor();
    //     $isAdmin = auth()->user()->isAdmin();

    //     $statusaktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'AKTIF')->first();
    //     $statusabsensisupir = DB::table('parameter')->where('grp', 'STATUS ABSENSI SUPIR')->where('subgrp', 'STATUS ABSENSI SUPIR')->where('text', 'ABSENSI SUPIR')->first();
    //     $tradoMilikSupir = DB::table('parameter')->where('grp', 'ABSENSI SUPIR')->where('subgrp', 'TRADO MILIK SUPIR')->first();

    //     // $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
    //     // Schema::create($temp, function ($table) {
    //     //     $table->bigInteger('id')->nullable();
    //     //     $table->integer('trado_id')->nullable();
    //     //     $table->integer('supir_id')->nullable();
    //     //     $table->integer('absen_id')->nullable();
    //     //     $table->string('keterangan')->nullable();
    //     //     $table->time('jam')->nullable();
    //     //     $table->date('tglbukti')->default();
    //     // });



    //     $tempMandor = '##tempmandor' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
    //     Schema::create($tempMandor, function ($table) {
    //         $table->tinyIncrements('id');
    //         $table->integer('trado_id')->nullable();
    //         $table->string('kodetrado')->nullable();
    //         $table->string('namasupir')->nullable();
    //         $table->string('keterangan')->nullable();
    //         $table->string('absentrado')->nullable();
    //         $table->integer('absen_id')->nullable();
    //         $table->time('jam')->nullable();
    //         $table->date('tglbukti')->nullable();
    //         $table->integer('supir_id')->nullable();
    //         $table->string('namasupir_old')->nullable();
    //         $table->integer('supir_id_old')->nullable();
    //         $table->double('uangjalan', 15, 2)->nullable();
    //         $table->text('memo')->nullable();
    //     });

    //     //trado yang sudah absen dan punya supir
    //     $absensisupirdetail = DB::table('absensisupirdetail')
    //         ->select(
    //             'trado.id as trado_id',
    //             'trado.kodetrado',
    //             'supir.namasupir',
    //             'absensisupirdetail.keterangan',
    //             'absentrado.keterangan as absentrado',
    //             'absentrado.id as absen_id',
    //             'absensisupirdetail.jam',
    //             'absensisupirheader.tglbukti',
    //             'supir.id as supir_id',
    //             'd.namasupir as namasupir_old',
    //             'd.id as supir_id_old',
    //             'absensisupirdetail.uangjalan',


    //         )
    //         ->where('absensisupirheader.tglbukti', date('Y-m-d', strtotime($date)))
    //         ->where('absensisupirdetail.supir_id', '!=', 0)
    //         ->leftJoin(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirdetail.absensi_id', 'absensisupirheader.id')
    //         ->leftJoin(DB::raw("trado with (readuncommitted)"), 'absensisupirdetail.trado_id', 'trado.id')
    //         ->leftJoin(DB::raw("absentrado with (readuncommitted)"), 'absensisupirdetail.absen_id', 'absentrado.id')
    //         ->leftJoin(DB::raw("supir with (readuncommitted)"), 'absensisupirdetail.supir_id', 'supir.id')
    //         ->leftJoin(DB::raw("supir as d with (readuncommitted)"), 'absensisupirdetail.supirold_id', 'd.id');


    //     DB::table($tempMandor)->insertUsing(['trado_id', 'kodetrado', 'namasupir', 'keterangan', 'absentrado', 'absen_id', 'jam', 'tglbukti', 'supir_id', 'namasupir_old', 'supir_id_old', 'uangjalan'], $absensisupirdetail);

    //     //trado yang sudah absen dan punya tidak punya supir
    //     $absensisupirdetail = DB::table('absensisupirdetail')
    //         ->select(
    //             'trado.id as trado_id',
    //             'trado.kodetrado',
    //             'supir.namasupir',
    //             'absensisupirdetail.keterangan',
    //             'absentrado.keterangan as absentrado',
    //             'absentrado.id as absen_id',
    //             'absensisupirdetail.jam',
    //             'absensisupirheader.tglbukti',
    //             'supir.id as supir_id',
    //             'd.namasupir as namasupir_old',
    //             'd.id as supir_id_old',
    //             'absensisupirdetail.uangjalan',


    //         )
    //         ->where('absensisupirheader.tglbukti', date('Y-m-d', strtotime($date)))
    //         ->where('absensisupirdetail.supir_id', '=', 0)
    //         ->leftJoin(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirdetail.absensi_id', 'absensisupirheader.id')
    //         ->leftJoin(DB::raw("trado with (readuncommitted)"), 'absensisupirdetail.trado_id', 'trado.id')
    //         ->leftJoin(DB::raw("absentrado with (readuncommitted)"), 'absensisupirdetail.absen_id', 'absentrado.id')
    //         ->leftJoin(DB::raw("supir with (readuncommitted)"), 'absensisupirdetail.supir_id', 'supir.id')
    //         ->leftJoin(DB::raw("supir as d with (readuncommitted)"), 'absensisupirdetail.supirold_id', 'd.id');


    //     //supir Trado yang belum diisi
    //     DB::table($tempMandor)->insertUsing(['trado_id', 'kodetrado', 'namasupir', 'keterangan', 'absentrado', 'absen_id', 'jam', 'tglbukti', 'supir_id', 'namasupir_old', 'supir_id_old', 'uangjalan'], $absensisupirdetail);

    //     $update = DB::table($tempMandor);
    //     $update->update(["memo" => '{"MEMO":"AKTIF","SINGKATAN":"A","WARNA":"#009933","WARNATULISAN":"#FFF"}']);

    //     $trados = DB::table('trado as a')

    //         ->select(
    //             // DB::raw('isnull(b.id,null) as id'),
    //             'a.id as trado_id',
    //             'a.kodetrado as kodetrado',
    //             'c.namasupir as namasupir',
    //             DB::raw('null as keterangan'),
    //             DB::raw('null as absentrado'),
    //             DB::raw('null as absen_id'),
    //             DB::raw("null as jam"),
    //             DB::raw("null as tglbukti"),
    //             DB::raw("(case when (select text from parameter where grp='ABSENSI SUPIR' and subgrp='TRADO MILIK SUPIR')= 'YA' then a.supir_id else null end) as supir_id"),
    //             'c.namasupir as namasupir_old',
    //             DB::raw("(case when (select text from parameter where grp='ABSENSI SUPIR' and subgrp='TRADO MILIK SUPIR')= 'YA' then a.supir_id else null end) as supir_id_old"),

    //         )
    //         ->leftJoin('supir as c', 'a.supir_id', 'c.id')
    //         ->where('a.statusaktif', $statusaktif->id)
    //         ->where('a.statusabsensisupir', $statusabsensisupir->id);

    //     // ->whereRaw("a.id not in (select trado_id from $tempMandor)");


    //     if ($tradoMilikSupir->text == 'YA') {
    //         $trados->whereRaw("NOT EXISTS (
    //             SELECT 1
    //             FROM $tempMandor temp
    //             WHERE (temp.trado_id = a.id and temp.supir_id_old = a.supir_id)
    //         )")
    //             ->where('a.supir_id', '!=', 0);
    //     } else {
    //         $trados->whereRaw("a.id not in (select trado_id from $tempMandor)");
    //     }
    //     // dd(2,$trados->get());
    //     // dd(DB::table($tempMandor)->get());

    //     DB::table($tempMandor)->insertUsing(['trado_id', 'kodetrado', 'namasupir', 'keterangan', 'absentrado', 'absen_id', 'jam', 'tglbukti', 'supir_id', 'namasupir_old', 'supir_id_old'], $trados);


    //     //supir serap yang belum diisi
    //     $tgl = date('Y-m-d', strtotime($date));
    //     $trado = DB::table('trado as a')
    //         ->select(
    //             // DB::raw('isnull(b.id,null) as id'),
    //             'a.id as trado_id',
    //             'a.kodetrado as kodetrado',
    //             'c.namasupir as namasupir',
    //             DB::raw('null as keterangan'),
    //             DB::raw('null as absentrado'),
    //             DB::raw('null as absen_id'),
    //             DB::raw("null as jam"),
    //             DB::raw("null as tglbukti"),
    //             'c.id as supir_id',
    //             'c.namasupir as namasupir_old',
    //             'c.id as supir_id_old',
    //         )
    //         ->where('a.statusaktif', $statusaktif->id)
    //         ->where('a.statusabsensisupir', $statusabsensisupir->id)
    //         ->leftJoin('supirserap as e', 'e.trado_id', 'a.id')
    //         ->leftJoin('supir as c', 'e.supirserap_id', 'c.id')
    //         ->where('e.tglabsensi', date('Y-m-d', strtotime($date)))
    //         ->where('e.statusapproval', 3)
    //         ->whereRaw("e.supirserap_id not in (select supirold_id from absensisupirdetail join absensisupirheader on absensisupirheader.nobukti = absensisupirdetail.nobukti where absensisupirheader.tglbukti='$tgl' and absensisupirdetail.trado_id = e.trado_id)");
    //     // ->whereRaw("e.supirserap_id not in (select supir_id from absensisupirdetail join absensisupirheader on absensisupirheader.nobukti = absensisupirdetail.nobukti where absensisupirheader.tglbukti='$tgl')");

    //     if ($tradoMilikSupir->text == 'YA') {
    //         $trado->where('a.supir_id', '!=', 0);
    //     }

    //     DB::table($tempMandor)->insertUsing(['trado_id', 'kodetrado', 'namasupir', 'keterangan', 'absentrado', 'absen_id', 'jam', 'tglbukti', 'supir_id', 'namasupir_old', 'supir_id_old'], $trado);
    //     // isnull(absensisupirdetail.supir_id,0)
    //     // isnull(supir.namasupir,'')
    //     $query = DB::table($tempMandor)->from(DB::raw("$tempMandor as a"))
    //         ->select(
    //             // DB::raw("row_number() Over(Order By a.trado_id) as id"),
    //             'a.id',
    //             'a.trado_id',
    //             'a.kodetrado as trado',
    //             DB::raw("isnull(a.supir_id,0) as supir_id"),
    //             DB::raw("isnull(a.namasupir,'') as supir"),
    //             DB::raw("isnull(a.keterangan,'') as keterangan"),
    //             DB::raw("isnull(a.absen_id,0) as absen_id"),
    //             DB::raw("isnull(a.absentrado,'') as absen"),
    //             DB::raw("isnull(a.uangjalan,0) as uangjalan"),
    //             'a.namasupir_old',
    //             'a.supir_id_old',
    //             db::raw("count(sp.nobukti) as jlhtrip"),
    //         )
    //         ->groupBy(
    //             "a.id",
    //             "a.trado_id",
    //             "a.kodetrado",
    //             "a.supir_id",
    //             "a.namasupir",
    //             "a.keterangan",
    //             "a.absen_id",
    //             "a.absentrado",
    //             "a.uangjalan",
    //             "a.namasupir_old",
    //             "a.supir_id_old",
    //         )
    //         ->leftJoin('suratpengantar as sp', function ($join) {
    //             $join->on('sp.tglbukti', '=', 'a.tglbukti');
    //             $join->on('sp.trado_id', '=', 'a.trado_id');
    //             $join->on('sp.supir_id', '=', 'a.supir_id');
    //         });

    //     return $query->orderBy('kodetrado', 'asc');
    // }

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
            ->select(DB::raw("supir_id, trado_id, statusjeniskendaraan"))
            ->whereRaw("gajisupir_nobukti = '$nobukti'")
            ->first();

        $temp = '##tempAbsen' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        // LEPAS ROW NUMBER DULU, BARU INPUT CREATE TEMP, BARU KASIH ROW NUMBER
        Schema::create($temp, function ($table) {
            $table->integer('absensi_id')->nullable();
            $table->string('nobukti');
            $table->date('tglbukti')->nullable();
            $table->float('uangjalan')->nullable();
            $table->string('trado')->nullable();
        });

        $isTangki = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'ABSENSI TANGKI')->first()->text ?? 'TIDAK';
        if ($isTangki == 'YA') {
            $queryTemp = DB::table('absensisupirdetail')->from(DB::raw("absensisupirdetail with (readuncommitted)"))
                ->select(
                    'absensisupirdetail.absensi_id',
                    'absensisupirdetail.nobukti',
                    'absensisupirheader.tglbukti',
                    'absensisupirdetail.uangjalan',
                    'trado.kodetrado as trado'
                )
                ->join(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirheader.nobukti', 'absensisupirdetail.nobukti')
                ->join(DB::raw("trado with (readuncommitted)"), 'trado.id', 'absensisupirdetail.trado_id')
                ->whereRaw("absensisupirdetail.nobukti in (select absensisupir_nobukti from gajisupiruangjalan where gajisupir_nobukti='$nobukti')")
                ->where('absensisupirdetail.statusjeniskendaraan', $fetch->statusjeniskendaraan)
                ->where('absensisupirdetail.supir_id', $fetch->supir_id);
            // ->where('absensisupirdetail.trado_id', $fetch->trado_id);

            DB::table($temp)->insertUsing(['absensi_id', 'nobukti', 'tglbukti', 'uangjalan', 'trado'], $queryTemp);
        } else {

            $queryTemp = DB::table('absensisupirdetail')->from(DB::raw("absensisupirdetail with (readuncommitted)"))
                ->select(
                    'absensisupirdetail.absensi_id',
                    'absensisupirdetail.nobukti',
                    'absensisupirheader.tglbukti',
                    'absensisupirdetail.uangjalan',
                    'trado.kodetrado as trado'
                )
                ->join(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirheader.nobukti', 'absensisupirdetail.nobukti')
                ->join(DB::raw("trado with (readuncommitted)"), 'trado.id', 'absensisupirdetail.trado_id')
                ->whereRaw("absensisupirdetail.nobukti in (select absensisupir_nobukti from gajisupiruangjalan where gajisupir_nobukti='$nobukti')")
                ->where('absensisupirdetail.supir_id', $fetch->supir_id);
            // ->where('absensisupirdetail.trado_id', $fetch->trado_id);

            DB::table($temp)->insertUsing(['absensi_id', 'nobukti', 'tglbukti', 'uangjalan', 'trado'], $queryTemp);
        }
        $queryTemp = DB::table('saldoabsensisupirdetail')->from(DB::raw("saldoabsensisupirdetail with (readuncommitted)"))
            ->select(
                'saldoabsensisupirdetail.absensi_id',
                'saldoabsensisupirdetail.nobukti',
                'saldoabsensisupirheader.tglbukti',
                'saldoabsensisupirdetail.uangjalan',
                'trado.kodetrado as trado'
            )
            ->join(DB::raw("saldoabsensisupirheader with (readuncommitted)"), 'saldoabsensisupirheader.nobukti', 'saldoabsensisupirdetail.nobukti')
            ->join(DB::raw("trado with (readuncommitted)"), 'trado.id', 'saldoabsensisupirdetail.trado_id')
            ->whereRaw("saldoabsensisupirdetail.nobukti in (select absensisupir_nobukti from gajisupiruangjalan where gajisupir_nobukti='$nobukti')")
            ->where('saldoabsensisupirdetail.supir_id', $fetch->supir_id);
        // ->where('absensisupirdetail.trado_id', $fetch->trado_id);

        DB::table($temp)->insertUsing(['absensi_id', 'nobukti', 'tglbukti', 'uangjalan', 'trado'], $queryTemp);
        $query = DB::table($temp)->from(DB::raw("$temp as a with (readuncommitted)"))
            ->select(
                DB::raw("row_number() Over(Order By a.nobukti) as id"),
                'a.nobukti',
                'a.tglbukti',
                'a.uangjalan',
                'a.trado',
            );
        if ($query->first() != null) {
            $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);

            if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
                switch ($this->params['filters']['groupOp']) {
                    case "AND":
                        $query->where(function ($query) {
                            foreach ($this->params['filters']['rules'] as $index => $filters) {
                                if ($filters['field'] == 'uangjalan') {
                                    $query = $query->whereRaw("format(a.uangjalan, '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglbukti') {
                                    $query->whereRaw("format(a.tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else {
                                    $query = $query->where('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                }
                            }
                        });

                        break;
                    case "OR":
                        $query->where(function ($query) {
                            foreach ($this->params['filters']['rules'] as $index => $filters) {
                                if ($filters['field'] == 'uangjalan') {
                                    $query = $query->orWhereRaw("format(a.uangjalan, '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglbukti') {
                                    $query->orWhereRaw("format(a.tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else {
                                    $query = $query->orWhere('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                }
                            }
                        });
                        break;
                    default:

                        break;
                }
            }
            $this->paginate($query);

            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
            $this->totalUangJalan = $query->sum('a.uangjalan');
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
                                // } else if ($filters['field'] == 'statustrip') {
                                //     $query = $query->whereRaw("(case when isnull(c.jumlah,0)=0  and isnull(absentrado.kodeabsen,'')='' then ' $statustrip->memo ' else '' end) LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'statusKeterangan') {
                                $query = $query->where('absentrado.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'statusjeniskendaraan') {
                                $query = $query->where('jeniskendaraan.text', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'keterangan_detail') {
                                $query = $query->where("$this->table.keterangan", 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'uangjalan') {
                                $query = $query->whereRaw("format($this->table.uangjalan, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'jumlahtrip') {
                                if ($filters['data'] == 0) {
                                    $query = $query->whereRaw("c.jumlah IS NULL");
                                } else {
                                    $query = $query->whereRaw("format(c.jumlah, '#,#0.00') LIKE '%$filters[data]%'");
                                }
                                // $query = $query->havingRaw("format(c.jumlah, '#,#0.00') LIKE '%$filters[data]%'");
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
                                // } else if ($filters['field'] == 'statustrip') {
                                //     $query = $query->whereRaw("(case when isnull(c.jumlah,0)=0  and isnull(absentrado.kodeabsen,'')='' then ' $statustrip->memo ' else '' end) LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'status') {
                                $query = $query->orWhere('absentrado.kodeabsen', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'statusKeterangan') {
                                $query = $query->orWhere('absentrado.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'statusjeniskendaraan') {
                                $query = $query->orWhere('jeniskendaraan.text', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'keterangan_detail') {
                                $query = $query->orWhere("$this->table.keterangan", 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'uangjalan') {
                                $query = $query->orWhereRaw("format($this->table.uangjalan, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'jumlahtrip') {
                                if ($filters['data'] == 0) {
                                    $query = $query->orWhereRaw("c.jumlah IS NULL");
                                } else {
                                    $query = $query->orWhereRaw("format(c.jumlah, '#,#0.00') LIKE '%$filters[data]%'");
                                }
                                // $query = $query->orWhereRaw("format(c.jumlah, '#,#0.00') LIKE '%$filters[data]%'");
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
        $statustrip = DB::table("parameter")->from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->select(
                'memo',
                'id'
            )
            ->where('grp', '=', 'TIDAK ADA TRIP')
            ->where('subgrp', '=', 'TIDAK ADA TRIP')
            ->where('text', '=', 'TIDAK ADA TRIP')
            ->first();

        $statustriplengkap = DB::table("parameter")->from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->select(
                'memo',
                'id'
            )
            ->where('grp', '=', 'TIDAK ADA TRIP')
            ->where('subgrp', '=', 'TIDAK ADA TRIP')
            ->where('text', '=', 'LENGKAP')
            ->first();
            
        if ($this->params['sortIndex'] == 'trado') {
            return $query->orderBy('trado.kodetrado', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'supir') {
            return $query->orderBy('supir.namasupir', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'status') {
            return $query->orderBy('absentrado.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'keterangan_detail') {
            return $query->orderBy($this->table . '.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'jumlahtrip') {
            return $query->orderBy('c.jumlah', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'tglbukti') {
            return $query->orderBy('absensisupirheader.tglbukti', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'statustrip') {
            return $query->orderBy(db::raw("(case when isnull(c.jumlah,0)=0  and isnull(absentrado.kodeabsen,'')='' then '$statustrip->id' else '$statustriplengkap->id' end)"), $this->params['sortOrder']);
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

        $ytrado_id = $data['trado_id'] ?? 0;
        $mandor_id = db::table("trado")->from(db::raw("trado a with (readuncommitted)"))
            ->select('a.mandor_id')
            ->where('a.id', $ytrado_id)
            ->first()->mandor_id ?? 0;

        $parameter = new Parameter();
        $idstatusnonsupirserap = $parameter->cekId('SUPIR SERAP', 'SUPIR SERAP', 'TIDAK') ?? 0;
        $idstatustambahantrado = $parameter->cekId('TAMBAHAN TRADO ABSENSI', 'TAMBAHAN TRADO ABSENSI', 'TIDAK') ?? 0;

        $absensiSupirDetail = new AbsensiSupirDetail();
        $absensiSupirDetail->absensi_id = $data['absensi_id'] ?? '';
        $absensiSupirDetail->nobukti = $data['nobukti'] ?? '';
        $absensiSupirDetail->trado_id = $data['trado_id'] ?? '';
        $absensiSupirDetail->absen_id = $data['absen_id'] ?? '';
        $absensiSupirDetail->statusjeniskendaraan = $data['statusjeniskendaraan'] ?? '';
        $absensiSupirDetail->supir_id = $data['supir_id'] ?? '';
        $absensiSupirDetail->supirold_id = $data['supirold_id'] ?? '';
        $absensiSupirDetail->jam = $data['jam'] ?? '';

        $absensiSupirDetail->keterangan = $data['keterangan'] ?? '';
        $absensiSupirDetail->modifiedby = $data['modifiedby'] ?? '';
        $absensiSupirDetail->mandor_id = $mandor_id ?? 0;
        $absensiSupirDetail->statussupirserap = $data['statussupirserap'] ?? $idstatusnonsupirserap;
        $absensiSupirDetail->statustambahantrado = $data['statustambahantrado'] ?? $idstatustambahantrado;

        if (array_key_exists("uangjalan", $data)) {
            $absensiSupirDetail->uangjalan = $data['uangjalan'];
        }
        if (!$absensiSupirDetail->save()) {
            throw new \Exception("Gagal menyimpan absensi supir detail.");
        }
        return $absensiSupirDetail;
    }
    public function processUpdate(AbsensiSupirDetail $absensiSupirDetail, array $data): AbsensiSupirDetail
    {
        $ytrado_id = $data['trado_id'] ?? 0;

        // dd($data);
        $mandor_id = db::table("trado")->from(db::raw("trado a with (readuncommitted)"))
            ->select('a.mandor_id')
            ->where('a.id', $ytrado_id)
            ->first()->mandor_id ?? 0;

        // dd($mandor_id);

        $absensiSupirDetail->absensi_id = $data['absensi_id'] ?? '';
        $absensiSupirDetail->nobukti = $data['nobukti'] ?? '';
        $absensiSupirDetail->trado_id = $data['trado_id'] ?? '';
        $absensiSupirDetail->absen_id = $data['absen_id'] ?? '';
        $absensiSupirDetail->statusjeniskendaraan = $data['statusjeniskendaraan'] ?? '';
        $absensiSupirDetail->supir_id = $data['supir_id'] ?? '';
        $absensiSupirDetail->supirold_id = $data['supirold_id'] ?? '';
        $absensiSupirDetail->jam = $data['jam'] ?? '';
        // $absensiSupirDetail->uangjalan = $data['uangjalan'] ?? '';
        $absensiSupirDetail->keterangan = $data['keterangan'] ?? '';
        $absensiSupirDetail->modifiedby = $data['modifiedby'] ?? '';
        // $absensiSupirDetail->mandor_id = $mandor_id ?? 0;
        if (array_key_exists("uangjalan", $data)) {
            $absensiSupirDetail->uangjalan = $data['uangjalan'];
        }
        if (!$absensiSupirDetail->save()) {
            throw new \Exception("Gagal menyimpan absensi supir detail.");
        }
        return $absensiSupirDetail;
    }

    public function deleteFromApprovalTanpa($tglabsensi, $tradoid)
    {
        $tglabsensi = date('Y-m-d', strtotime($tglabsensi));
        $saldosuratpengantar = DB::table('saldosuratpengantar')
            ->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->where('trado_id', $tradoid)
            ->where('tglbukti', '>', $tglabsensi)
            ->get();

        $absensi = DB::table('absensisupirapprovalheader')
            ->from(DB::raw("absensisupirapprovalheader as header with (readuncommitted)"))
            ->where('header.tglbukti', '>', $tglabsensi)
            ->where('detail.trado_id', $tradoid)
            ->leftJoin(DB::raw("absensisupirapprovaldetail as detail with (readuncommitted)"), 'detail.absensisupirapproval_id', 'header.id')
            ->get();
        if ((0 <= count($absensi)) && (0 <= count($saldosuratpengantar))) {
            $AbsensiSupirDetail = AbsensiSupirDetail::where('trado_id', $tradoid)
                ->select('AbsensiSupirDetail.id')
                ->where('absensiSupirheader.tglbukti', '>', $tglabsensi)
                ->leftJoin(DB::raw("absensiSupirheader  with (readuncommitted)"), 'absensiSupirheader.id', 'AbsensiSupirDetail.absensi_id');
            $AbsensiSupirDetail->delete();
        }
    }
    public function updateFromApprovalTanpa($tglabsensi, $supir_id)
    {
        $tglabsensi = date('Y-m-d', strtotime($tglabsensi));
        $saldosuratpengantar = DB::table('saldosuratpengantar')
            ->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->where('supir_id', $supir_id)
            ->where('tglbukti', '>', $tglabsensi)
            ->get();

        $absensi = DB::table('absensisupirapprovalheader')
            ->from(DB::raw("absensisupirapprovalheader as header with (readuncommitted)"))
            ->where('header.tglbukti', '>', $tglabsensi)
            ->where('detail.supir_id', $supir_id)
            ->leftJoin(DB::raw("absensisupirapprovaldetail as detail with (readuncommitted)"), 'detail.absensisupirapproval_id', 'header.id')
            ->get();

        if ((0 <= count($absensi)) && (0 <= count($saldosuratpengantar))) {
            $AbsensiSupirDetail = AbsensiSupirDetail::where('supir_id', $supir_id)
                ->select('AbsensiSupirDetail.id')
                ->where('absensiSupirheader.tglbukti', '>', $tglabsensi)
                ->leftJoin(DB::raw("absensiSupirheader  with (readuncommitted)"), 'absensiSupirheader.id', 'AbsensiSupirDetail.absensi_id');
            $AbsensiSupirDetail->update([
                'supir_id' => 0
            ]);
        }
    }
}
