<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use App\Models\Parameter;

class MandorAbsensiSupir extends MyModel
{
    use HasFactory;

    protected $table = 'trado';


    public function tableTemp($date = 'now', $deleted_id)
    {
        $trado = new Trado();
        $trado->RefreshTradoNonAktif();
        $supir = new Supir();
        $supir->RefreshSupirNonAktif();

        $mandorId = false;
        $isMandor = auth()->user()->isMandor();
        $isAdmin = auth()->user()->isAdmin();

        $userid = auth('api')->user()->id;
        $date = date('Y-m-d', strtotime($date));

        // update trado jadi non aktif jika 



        // 

        $tempabsensisupirheader = '##tempabsensisupirheader' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempabsensisupirheader, function ($table) {
            $table->integer('id');
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('kasgantung_nobukti', 50)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->integer('statuscetak')->Length(11)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->integer('statusapprovaleditabsensi')->Length(11)->nullable();
            $table->string('userapprovaleditabsensi', 50)->nullable();
            $table->date('tglapprovaleditabsensi')->nullable();
            $table->dateTime('tglbataseditabsensi')->nullable();
            $table->integer('statusapprovalpengajuantripinap')->Length(11)->nullable();
            $table->string('userapprovalpengajuantripinap', 50)->nullable();
            $table->date('tglapprovalpengajuantripinap')->nullable();
            $table->dateTime('tglbataspengajuantripinap')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->string('editing_by', 50)->nullable();
            $table->dateTime('editing_at')->nullable();
        });

        $queryabsensisupirheader = db::table("absensisupirheader")->from(db::raw("absensisupirheader a with (readuncommitted)"))
            ->select(
                'a.id',
                'a.nobukti',
                'a.tglbukti',
                'a.keterangan',
                'a.kasgantung_nobukti',
                'a.nominal',
                'a.statusformat',
                'a.statuscetak',
                'a.userbukacetak',
                'a.tglbukacetak',
                'a.jumlahcetak',
                'a.statusapprovaleditabsensi',
                'a.userapprovaleditabsensi',
                'a.tglapprovaleditabsensi',
                'a.tglbataseditabsensi',
                'a.statusapprovalpengajuantripinap',
                'a.userapprovalpengajuantripinap',
                'a.tglapprovalpengajuantripinap',
                'a.tglbataspengajuantripinap',
                'a.info',
                'a.modifiedby',
                'a.editing_by',
                'a.editing_at',
            )
            ->where('a.tglbukti', $date)
            ->orderby('a.id', 'asc');

        DB::table($tempabsensisupirheader)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'keterangan',
            'kasgantung_nobukti',
            'nominal',
            'statusformat',
            'statuscetak',
            'userbukacetak',
            'tglbukacetak',
            'jumlahcetak',
            'statusapprovaleditabsensi',
            'userapprovaleditabsensi',
            'tglapprovaleditabsensi',
            'tglbataseditabsensi',
            'statusapprovalpengajuantripinap',
            'userapprovalpengajuantripinap',
            'tglapprovalpengajuantripinap',
            'tglbataspengajuantripinap',
            'info',
            'modifiedby',
            'editing_by',
            'editing_at',
        ],  $queryabsensisupirheader);

        $queryheader = db::table($tempabsensisupirheader)->from(db::raw($tempabsensisupirheader . " a"))
            ->select(
                'a.nobukti',
                'a.id'
            )
            ->first();

        if (isset($queryheader)) {
            $nobukti = $queryheader->nobukti ?? $userid;
            $absensi_id = $queryheader->id ?? 0;
        } else {
            $nobukti = $userid;
            $absensi_id = 0;
        }

        $tempabsensisupirdetail = '##tempabsensisupirdetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempabsensisupirdetail, function ($table) {
            $table->integer('id');
            $table->unsignedBigInteger('absensi_id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('uangjalan', 15, 2)->nullable();
            $table->unsignedBigInteger('absen_id')->nullable();
            $table->unsignedBigInteger('supirold_id')->nullable();
            $table->time('jam')->nullable();
            $table->integer('statusapprovaleditabsensi')->Length(11)->nullable();
            $table->string('userapprovaleditabsensi', 50)->nullable();
            $table->date('tglapprovaleditabsensi')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();
        });
        // dd($deleted_id);
        if ($deleted_id == 0) {
            $queryabsensisupirdetail = db::table("absensisupirdetail")->from(db::raw("absensisupirdetail a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.absensi_id',
                    'a.nobukti',
                    'a.trado_id',
                    'a.supir_id',
                    'a.keterangan',
                    'a.uangjalan',
                    'a.absen_id',
                    'a.supirold_id',
                    'a.jam',
                    'a.statusapprovaleditabsensi',
                    'a.userapprovaleditabsensi',
                    'a.tglapprovaleditabsensi',
                    'a.info',
                    'a.modifiedby',
                )
                ->join(db::raw("absensisupirheader b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
                ->where('b.tglbukti', $date)
                ->orderby('a.id', 'asc');

            DB::table($tempabsensisupirdetail)->insertUsing([
                'id',
                'absensi_id',
                'nobukti',
                'trado_id',
                'supir_id',
                'keterangan',
                'uangjalan',
                'absen_id',
                'supirold_id',
                'jam',
                'statusapprovaleditabsensi',
                'userapprovaleditabsensi',
                'tglapprovaleditabsensi',
                'info',
                'modifiedby',
            ],  $queryabsensisupirdetail);
        } else {
            $user = auth('api')->user()->name;
            $class = 'TemporaryAbsensiSupir';

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
            $deleteid = db::table($temtabel)->from(db::raw($temtabel . " a with (readuncommitted)"))
                ->select(
                    'a.trado_id',
                    'a.supir_id',
                    'a.supirold_id',
                )
                ->where('a.id', $deleted_id)
                ->first();

                // dd(db::table($temtabel)->get());

            if (isset($deleteid)) {
                $AbsensiSupirDetail = db::table('absensisupirdetail')->from(db::raw("absensisupirdetail a with (readuncommitted)"))
                    ->select(
                        'a.id',
                    )
                    ->join(db::raw("absensisupirheader b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
                    ->where('a.trado_id', $deleteid->trado_id)
                    ->where('a.supir_id', $deleteid->supir_id)
                    ->where('a.supirold_id', $deleteid->supirold_id)
                    ->where('b.tglbukti', $date)
                    ->first();

                $idabsensidetail = $AbsensiSupirDetail->id ?? 0;
            } else {
                $idabsensidetail = 0;
            }
            if ($idabsensidetail != 0) {
                // dd($idabsensidetail);

                $parameter = new Parameter();
                $idstatussupirserap = $parameter->cekId('SUPIR SERAP', 'SUPIR SERAP', 'YA') ?? 0;

                $querycek = db::table('absensisupirdetail')->from(db::raw('absensisupirdetail a with (readuncommitted)'))
                    ->select(
                        'a.id'
                    )
                    ->where('a.id', $idabsensidetail)
                    ->where('a.statussupirserap', $idstatussupirserap)
                    ->first();
                if (isset($querycek)) {

                    DB::table('absensisupirdetail')
                        ->where('id', $idabsensidetail)
                        ->update([
                            'keterangan' => DB::raw("''"),
                            'absen_id' => DB::raw('0'),
                        ]);
                    DB::table($temtabel)
                        ->where('id', $deleted_id)
                        ->update([
                            'keterangan' => DB::raw("''"),
                            'absen_id' => DB::raw('0'),
                        ]);
                } else {
                    $AbsensiSupirDetail = (new MandorAbsensiSupir())->processDestroy($idabsensidetail);
                }
            }


            $tempabsensisupirdetail2 = '##tempabsensisupirdetail2' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempabsensisupirdetail2, function ($table) {
                $table->integer('id');
                $table->unsignedBigInteger('absensi_id')->nullable();
                $table->string('nobukti', 50)->nullable();
                $table->unsignedBigInteger('trado_id')->nullable();
                $table->unsignedBigInteger('supir_id')->nullable();
                $table->longText('keterangan')->nullable();
                $table->double('uangjalan', 15, 2)->nullable();
                $table->unsignedBigInteger('absen_id')->nullable();
                $table->unsignedBigInteger('supirold_id')->nullable();
                $table->time('jam')->nullable();
                $table->integer('statusapprovaleditabsensi')->Length(11)->nullable();
                $table->string('userapprovaleditabsensi', 50)->nullable();
                $table->date('tglapprovaleditabsensi')->nullable();
                $table->longText('info')->nullable();
                $table->string('modifiedby', 50)->nullable();
            });

            $queryabsensisupirdetail2 = db::table("absensisupirdetail")->from(db::raw("absensisupirdetail a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.absensi_id',
                    'a.nobukti',
                    'a.trado_id',
                    'a.supir_id',
                    'a.keterangan',
                    'a.uangjalan',
                    'a.absen_id',
                    'a.supirold_id',
                    'a.jam',
                    'a.statusapprovaleditabsensi',
                    'a.userapprovaleditabsensi',
                    'a.tglapprovaleditabsensi',
                    'a.info',
                    'a.modifiedby',
                )
                ->join(db::raw("absensisupirheader b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
                ->where('b.tglbukti', $date)
                ->orderby('a.id', 'asc');

            DB::table($tempabsensisupirdetail2)->insertUsing([
                'id',
                'absensi_id',
                'nobukti',
                'trado_id',
                'supir_id',
                'keterangan',
                'uangjalan',
                'absen_id',
                'supirold_id',
                'jam',
                'statusapprovaleditabsensi',
                'userapprovaleditabsensi',
                'tglapprovaleditabsensi',
                'info',
                'modifiedby',
            ],  $queryabsensisupirdetail2);



            // dd(db::table($tempabsensisupirdetail2)->get());

            $parameter = new Parameter();
            $idstatussupirserap = $parameter->cekId('SUPIR SERAP', 'SUPIR SERAP', 'YA') ?? 0;

            $querycek = db::table('absensisupirdetail')->from(db::raw('absensisupirdetail a with (readuncommitted)'))
                ->select(
                    'a.id'
                )
                ->where('a.id', $idabsensidetail)
                ->where('a.statussupirserap', $idstatussupirserap)
                ->first();
            if (isset($querycek)) {
                $queryabsensisupirdetail = db::table($temtabel)->from(db::raw($temtabel . " a with (readuncommitted)"))
                    ->select(
                        db::raw("isnull(b.id,0) as id"),
                        db::raw($absensi_id . " as absensi_id"),
                        db::raw("'" . $nobukti . "' as nobukti"),
                        'a.trado_id',
                        'a.supir_id',
                        'a.keterangan',
                        db::raw("isnull(b.uangjalan,0) as uangjalan"),
                        'a.absen_id',
                        'a.supirold_id',
                        db::raw("isnull(b.jam,'00:00') as jam"),
                        db::raw("isnull(b.statusapprovaleditabsensi,0) as statusapprovaleditabsensi"),
                        db::raw("isnull(b.userapprovaleditabsensi,'') as userapprovaleditabsensi"),
                        db::raw("isnull(b.tglapprovaleditabsensi,'1900/1/1') as tglapprovaleditabsensi"),
                        db::raw("isnull(b.info,'') as info"),
                        db::raw("isnull(b.modifiedby,'') as modifiedby"),
                    )
                    ->leftJoin(DB::raw($tempabsensisupirdetail2 . " as b "), function ($join) {
                        $join->on('a.trado_id', '=', 'b.trado_id');
                        $join->on('a.supir_id', '=', 'b.supir_id');
                        $join->on('a.supirold_id', '=', 'b.supirold_id');
                    })
                    ->orderby('a.id', 'asc');
            } else {
                $queryabsensisupirdetail = db::table($temtabel)->from(db::raw($temtabel . " a with (readuncommitted)"))
                    ->select(
                        db::raw("isnull(b.id,0) as id"),
                        db::raw($absensi_id . " as absensi_id"),
                        db::raw("'" . $nobukti . "' as nobukti"),
                        'a.trado_id',
                        'a.supir_id',
                        'a.keterangan',
                        db::raw("isnull(b.uangjalan,0) as uangjalan"),
                        'a.absen_id',
                        'a.supirold_id',
                        db::raw("isnull(b.jam,'00:00') as jam"),
                        db::raw("isnull(b.statusapprovaleditabsensi,0) as statusapprovaleditabsensi"),
                        db::raw("isnull(b.userapprovaleditabsensi,'') as userapprovaleditabsensi"),
                        db::raw("isnull(b.tglapprovaleditabsensi,'1900/1/1') as tglapprovaleditabsensi"),
                        db::raw("isnull(b.info,'') as info"),
                        db::raw("isnull(b.modifiedby,'') as modifiedby"),
                    )
                    ->leftJoin(DB::raw($tempabsensisupirdetail2 . " as b "), function ($join) {
                        $join->on('a.trado_id', '=', 'b.trado_id');
                        $join->on('a.supir_id', '=', 'b.supir_id');
                        $join->on('a.supirold_id', '=', 'b.supirold_id');
                    })
                    ->whereRaw("a.id<>a.deleted_id")
                    ->orderby('a.id', 'asc');
            }




            DB::table($tempabsensisupirdetail)->insertUsing([
                'id',
                'absensi_id',
                'nobukti',
                'trado_id',
                'supir_id',
                'keterangan',
                'uangjalan',
                'absen_id',
                'supirold_id',
                'jam',
                'statusapprovaleditabsensi',
                'userapprovaleditabsensi',
                'tglapprovaleditabsensi',
                'info',
                'modifiedby',
            ],  $queryabsensisupirdetail);

            // dump(DB::table($temtabel)->get());
            // dump(DB::table($tempabsensisupirdetail2)->get());
            // dd(DB::table($tempabsensisupirdetail)->get());
        }


        $bukaAbsensi = (new AbsensiSupirHeader())->cekBukaTanggalValidation($date);
        $isTanggalAllowed = (new AbsensiSupirHeader())->isBukaTanggalValidation($date);


        $statusaktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'AKTIF')->first();
        $tempTrado = '##tempTrado' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempTrado, function ($table) {
            $table->integer('id');
            $table->string('kodetrado', 30)->nullable();
            $table->longText('keterangan')->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->integer('statusabsensisupir')->length(11)->nullable();
            $table->string('nama', 40)->nullable();
            $table->unsignedBigInteger('mandor_id')->nullable();
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->dateTime('tglberlakumiliksupir')->nullable();
            $table->string('modifiedby', 30)->nullable();
        });
        $queryTrado = db::table("trado")->from(db::raw("trado a with (readuncommitted)"))
            ->select(
                'a.id',
                'a.kodetrado',
                'a.keterangan',
                'a.statusaktif',
                'a.statusabsensisupir',
                'a.nama',
                'a.mandor_id',
                'a.supir_id',
                'a.tglberlakumiliksupir',
                'a.modifiedby'
            )
            ->whereRaw("isnull(a.tglberlakumilikmandor,'1900/1/1')<='" . $date . "'")
            ->where('a.statusaktif', $statusaktif->id);



        DB::table($tempTrado)->insertUsing([
            'id',
            'kodetrado',
            'keterangan',
            'statusaktif',
            'statusabsensisupir',
            'nama',
            'mandor_id',
            'supir_id',
            'tglberlakumiliksupir',
            'modifiedby',
        ],  $queryTrado);
        // db::raw("isnull(b.id,0) as id"),
        $queryNonAktifTrado = db::table("trado")->from(db::raw("trado a with (readuncommitted)"))
            ->select(
                'a.id',
                'a.kodetrado',
                'a.keterangan',
                DB::raw($statusaktif->id . ' as statusaktif'),
                'a.statusabsensisupir',
                'a.nama',
                'a.mandor_id',
                'a.supir_id',
                'a.tglberlakumiliksupir',
                'a.modifiedby',
            )
            ->join(db::raw("approvaltradogambar b with (readuncommitted)"), 'a.kodetrado', 'b.kodetrado')
            ->join(db::raw("approvaltradoketerangan c with (readuncommitted)"), 'a.kodetrado', 'c.kodetrado')
            ->whereRaw("isnull(a.tglberlakumilikmandor,'1900/1/1')<='" . $date . "'")
            ->where('b.tglbatas','>=', $date)
            ->where('a.statusaktif','<>', $statusaktif->id);
            // dd( $queryNonAktifTrado->get());

        DB::table($tempTrado)->insertUsing([
            'id',
            'kodetrado',
            'keterangan',
            'statusaktif',
            'statusabsensisupir',
            'nama',
            'mandor_id',
            'supir_id',
            'tglberlakumiliksupir',
            'modifiedby',
        ],  $queryNonAktifTrado);

        $tempsupir = '##tempsupir' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsupir, function ($table) {
            $table->integer('id');
            $table->string('namasupir', 100)->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            // $table->unsignedBigInteger('supirold_id')->nullable();
            $table->longText('keterangan')->nullable();
            $table->unsignedBigInteger('mandor_id')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 30)->nullable();
        });
        $statusaktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'AKTIF')->first();
        $statusnonaktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'NON AKTIF')->first();

        $parameter = new Parameter();
        $miliktrado = $parameter->cekText('SUPIR MILIK TRADO', 'SUPIR MILIK TRADO') ?? 'TIDAK';

        $querysupir = db::table("supir")->from(db::raw("supir a with (readuncommitted)"))
            ->select(
                'a.id',
                'a.namasupir',
                'a.statusaktif',
                // 'a.supirold_id',
                'a.keterangan',
                'a.mandor_id',
                'a.info',
                'a.modifiedby'
            )
            // ->leftJoin(DB::raw("trado as b with (readuncommitted)"), 'b.supir_id', 'a.id')
            // ->whereRaw("isnull(b.tglberlakumiliksupir,'1900/1/1')<='" . $date . "'")
            ->where('a.statusaktif', $statusaktif->id);
        // if ($miliktrado=='YA') {
        //     $querysupir->where('b.statusaktif', $statusaktif->id); // dijakarta tidak aktifkan
        // }

        DB::table($tempsupir)->insertUsing([
            'id',
            'namasupir',
            'statusaktif',
            // 'supirold_id',
            'keterangan',
            'mandor_id',
            'info',
            'modifiedby'
        ],  $querysupir);

        $querysupir = db::table("supir")->from(db::raw("supir a with (readuncommitted)"))
        ->select(
            'a.id',
            'a.namasupir',
            DB::raw($statusaktif->id . ' as statusaktif'),
            // 'a.supirold_id',
            'a.keterangan',
            'a.mandor_id',
            'a.info',
            'a.modifiedby'
        )
        ->join(db::raw("approvalsupirgambar b with (readuncommitted)"), 'a.noktp', 'b.noktp')
        ->join(db::raw("approvalsupirketerangan c with (readuncommitted)"), 'a.noktp', 'c.noktp')
        ->where('b.tglbatas','>=', $date)
        ->where('a.statusaktif','<>', $statusaktif->id);
        // dd($querysupir->get());
        DB::table($tempsupir)->insertUsing([
            'id',
            'namasupir',
            'statusaktif',
            // 'supirold_id',
            'keterangan',
            'mandor_id',
            'info',
            'modifiedby'
        ],  $querysupir);

        $tempsupirnonaktif = '##tempsupirnonaktif' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsupirnonaktif, function ($table) {
            $table->integer('id');
            $table->string('namasupir', 100)->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            // $table->unsignedBigInteger('supirold_id')->nullable();
            $table->longText('keterangan')->nullable();
            $table->unsignedBigInteger('mandor_id')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 30)->nullable();
        });

        $querysupirnonaktif = db::table("supir")->from(db::raw("supir a with (readuncommitted)"))
            ->select(
                'a.id',
                'a.namasupir',
                'a.statusaktif',
                // 'a.supirold_id',
                'a.keterangan',
                'a.mandor_id',
                'a.info',
                'a.modifiedby'
            )
            // ->leftJoin(DB::raw("trado as b with (readuncommitted)"), 'b.supir_id', 'a.id')
            // ->whereRaw("isnull(b.tglberlakumiliksupir,'1900/1/1')<='" . $date . "'")
            ->where('a.statusaktif', $statusnonaktif->id);
        // if ($miliktrado=='YA') {
        //     $querysupirnonaktif->where('b.statusaktif', $statusaktif->id); // dijakarta tidak aktifkan
        // }

        // ->where('b.statusaktif', $statusaktif->id); // dijakarta tidak aktifkan

        DB::table($tempsupirnonaktif)->insertUsing([
            'id',
            'namasupir',
            'statusaktif',
            // 'supirold_id',
            'keterangan',
            'mandor_id',
            'info',
            'modifiedby'
        ],  $querysupirnonaktif);


        $querymandor = db::table("mandordetail")->from(db::raw("mandordetail a with (readuncommitted)"))
            ->select('a.mandor_id')
            ->where('a.user_id', $userid);
        $bukaAbsensiid = $bukaAbsensi->id ?? 0;
        $querybukaabsen = db::table("bukaabsensi")->from(db::raw("bukaabsensi a with (readuncommitted)"))
            ->select('a.mandor_user_id')
            ->where('a.id', $bukaAbsensiid);
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
            $table->datetime('tglbatas')->nullable();
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
        $absensisupirdetail = DB::table($tempabsensisupirdetail)->from(db::raw($tempabsensisupirdetail . " as absensisupirdetail"))
            ->select(
                'trado.id as trado_id',
                'trado.kodetrado',
                'supir.namasupir',
                'absensisupirdetail.keterangan',
                'absentrado.keterangan as absentrado',
                'absentrado.id as absen_id',
                'absensisupirdetail.jam',
                db::raw("'" . $date . "' as tglbukti"),
                // 'absensisupirheader.tglbukti',
                'supir.id as supir_id',
                db::raw("(case when isnull(d.id,0)=0 then d1.namasupir  else d.namasupir end) as namasupir_old"),
                db::raw("(case when isnull(d.id,0)=0 then d1.id  else d.id end) as supir_id_old"),
                // 'd.id as supir_id_old',

            )
            // ->where('absensisupirheader.tglbukti', date('Y-m-d', strtotime($date)))
            ->where('absensisupirdetail.supir_id', '!=', 0)
            // ->leftJoin(DB::raw($tempabsensisupirheader ." as absensisupirheader with (readuncommitted)"), 'absensisupirdetail.absensi_id', 'absensisupirheader.id')
            ->join(DB::raw("$tempTrado as trado with (readuncommitted)"), 'absensisupirdetail.trado_id', 'trado.id')
            ->leftJoin(DB::raw("absentrado with (readuncommitted)"), 'absensisupirdetail.absen_id', 'absentrado.id')
            ->leftJoin(DB::raw("$tempsupir as supir with (readuncommitted)"), 'absensisupirdetail.supir_id', 'supir.id')
            ->leftJoin(DB::raw("$tempsupir as d with (readuncommitted)"), 'absensisupirdetail.supirold_id', 'd.id')
            ->leftJoin(DB::raw("supir as d1 with (readuncommitted)"), 'absensisupirdetail.supirold_id', 'd1.id');

        if (!$isAdmin) {
            if ($isMandor) {
                $absensisupirdetail->Join(DB::raw($tempmandordetail . " as mandordetail"), 'trado.mandor_id', 'mandordetail.mandor_id');
            }
        }
        DB::table($tempMandor)->insertUsing(['trado_id', 'kodetrado', 'namasupir', 'keterangan', 'absentrado', 'absen_id', 'jam', 'tglbukti', 'supir_id', 'namasupir_old', 'supir_id_old'], $absensisupirdetail);
        //trado yang sudah absen dan punya tidak punya supir
        $absensisupirdetail = DB::table($tempabsensisupirdetail)->from(db::raw($tempabsensisupirdetail . " as absensisupirdetail"))
            ->select(
                'trado.id as trado_id',
                'trado.kodetrado',
                'supir.namasupir',
                'absensisupirdetail.keterangan',
                'absentrado.keterangan as absentrado',
                'absentrado.id as absen_id',
                'absensisupirdetail.jam',
                db::raw("'" . $date . "' as tglbukti"),
                // 'absensisupirheader.tglbukti',
                'supir.id as supir_id',
                // 'd.namasupir as namasupir_old',
                db::raw("(case when isnull(d1.id,0)=0 then d.namasupir else d1.namasupir end) as namasupir_old"),
                db::raw("(case when isnull(d1.id,0)=0 then d.id else d1.id end) as supir_id_old"),

            )
            // ->where('absensisupirheader.tglbukti', date('Y-m-d', strtotime($date)))
            ->where('absensisupirdetail.supir_id', '=', 0)
            // ->leftJoin(DB::raw($tempabsensisupirheader ." as absensisupirheader with (readuncommitted)"), 'absensisupirdetail.absensi_id', 'absensisupirheader.id')
            ->join(DB::raw("$tempTrado as trado with (readuncommitted)"), 'absensisupirdetail.trado_id', 'trado.id')
            ->leftJoin(DB::raw("absentrado with (readuncommitted)"), 'absensisupirdetail.absen_id', 'absentrado.id')
            ->leftJoin(DB::raw("$tempsupir as supir with (readuncommitted)"), 'absensisupirdetail.supir_id', 'supir.id')
            ->leftJoin(DB::raw("$tempsupir as d with (readuncommitted)"), 'absensisupirdetail.supirold_id', 'd.id')
            ->leftJoin(DB::raw("$tempsupirnonaktif as d1 with (readuncommitted)"), 'absensisupirdetail.supirold_id', 'd1.id');
        if (!$isAdmin) {
            if ($isMandor) {
                $absensisupirdetail->Join(DB::raw($tempmandordetail . " as mandordetail"), 'trado.mandor_id', 'mandordetail.mandor_id');

                //  $absensisupirdetail->where('trado.mandor_id',$isMandor->mandor_id);
            }
        }

        //supir Trado yang belum diisi
        DB::table($tempMandor)->insertUsing(['trado_id', 'kodetrado', 'namasupir', 'keterangan', 'absentrado', 'absen_id', 'jam', 'tglbukti', 'supir_id', 'namasupir_old', 'supir_id_old'], $absensisupirdetail);
        DB::table($tempAbsensi)->insertUsing(['trado_id', 'kodetrado', 'namasupir', 'keterangan', 'absentrado', 'absen_id', 'jam', 'tglbukti', 'supir_id', 'namasupir_old', 'supir_id_old'], $absensisupirdetail);

        // dump(DB::table($tempMandor)->get());
        // dump(DB::table($tempabsensisupirheader)->get());
        // dd(DB::table($tempabsensisupirdetail)->get());
        $update = DB::table($tempMandor);
        $update->update(["memo" => '{"MEMO":"AKTIF","SINGKATAN":"A","WARNA":"#009933","WARNATULISAN":"#FFF"}']);

        // dump(db::table($tempMandor)->where('trado_id',18)->get());
        // 
        // dd(db::table($tempTrado)->where('kodetrado','1234567890')->get());
        $trados = DB::table("$tempTrado as a")

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
            ->leftJoin("$tempsupir as c", 'a.supir_id', 'c.id')
            ->leftJoin(DB::raw($tempAbsensi . " as b "), function ($join) {
                $join->on('a.id', '=', 'b.trado_id');
                $join->on(db::raw("isnull(a.supir_id,0)"), '=', db::raw("isnull(b.supir_id,0)"));
            })
            ->where('a.statusaktif', $statusaktif->id)
            ->where('a.statusabsensisupir', $statusabsensisupir->id)
            ->whereRaw("isnull(b.id,0)=0");
        // dd($trados->where('trado_id',78)->get());



        if (!$isAdmin) {
            if ($isMandor) {
                $trados->Join(DB::raw($tempmandordetail . " as mandordetail"), 'a.mandor_id', 'mandordetail.mandor_id');
                // $trados->where('a.mandor_id',$isMandor->mandor_id);
                // }else{
                //     $trado->where('a.id',0);
            }
        }
        // dd($trados->where('a.id',18)->get());
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
        // dd(DB::table($tempMandor)->get());

        //supir serap yang belum diisi
        $tgl = date('Y-m-d', strtotime($date));
        $query_jam = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', 'BATAS JAM EDIT ABSENSI')->where('subgrp', 'BATAS JAM EDIT ABSENSI')->first();
        $jam = substr($query_jam->text, 0, 2);
        $menit = substr($query_jam->text, 3, 2);
        $query_jam = strtotime($tgl . ' ' . $jam . ':' . $menit . ':00');
        $tglbataseditabsensi = date('Y-m-d H:i:s', $query_jam);
        $tglbatas = $bukaAbsensi->tglbatas ?? $tglbataseditabsensi;
        $update->update([
            "tglbukti" => date('Y-m-d', strtotime($date)),
            "tglbatas" => $tglbatas

        ]);


        $queryhasil = DB::table($tempMandor)->from(DB::raw("$tempMandor as a"))
            ->select(
                // DB::raw("row_number() Over(Order By a.trado_id) as id"),
                'a.id as idtemp',
                'a.trado_id',
                'a.kodetrado',
                'a.namasupir',
                'a.keterangan',
                'a.absentrado',
                'a.absen_id',
                'a.jam',
                DB::raw("(case when year(isnull(a.tglbukti,'1900/1/1'))=1900 then null else format(a.tglbukti,'dd-MM-yyyy')  end)as tglbukti"),
                'a.supir_id',
                'a.namasupir_old',
                'a.supir_id_old',
                db::raw("count(sp.nobukti) as jlhtrip"),
                'a.memo',
                DB::raw("(case when year(isnull(a.tglbatas,'1900/1/1 '))=1900 then null else format(a.tglbatas,'dd-MM-yyyy HH:mm:ss')  end)as tglbatas"),
            )
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
                'a.memo',
                'a.tglbatas'
            )
            ->leftJoin('suratpengantar as sp', function ($join) {
                $join->on('sp.tglbukti', '=', 'a.tglbukti');
                $join->on('sp.trado_id', '=', 'a.trado_id');
                $join->on('sp.supir_id', '=', 'a.supir_id');
            })
            ->orderby('a.kodetrado','asc');

            // dd($queryhasil->get());

            $tempHasil = '##tempHasil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempHasil, function ($table) {
                $table->tinyIncrements('id');
                $table->integer('idtemp')->nullable();
                $table->integer('trado_id')->nullable();
                $table->string('kodetrado')->nullable();
                $table->string('namasupir')->nullable();
                $table->string('keterangan')->nullable();
                $table->string('absentrado')->nullable();
                $table->integer('absen_id')->nullable();
                $table->time('jam')->nullable();
                $table->string('tglbukti',50)->nullable();
                $table->integer('supir_id')->nullable();
                $table->string('namasupir_old')->nullable();
                $table->integer('supir_id_old')->nullable();
                $table->integer('jlhtrip')->nullable();
                $table->text('memo')->nullable();
                $table->string('tglbatas',50)->nullable();

            });            

            DB::table($tempHasil)->insertUsing([
                'idtemp',
                'trado_id',
                'kodetrado',
                'namasupir',
                'keterangan',
                'absentrado',
                'absen_id',
                'jam',
                'tglbukti',
                'supir_id',
                'namasupir_old',
                'supir_id_old',
                'jlhtrip',
                'memo',
                'tglbatas',
            ],  $queryhasil);

            $query = DB::table($tempHasil)->from(DB::raw("$tempHasil as a"))
            ->select(
                // DB::raw("row_number() Over(Order By a.trado_id) as id"),
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
                'a.jlhtrip',
                'a.memo',
                'a.tglbatas',
            );


        $user = auth('api')->user()->name;
        $class = 'TemporaryAbsensiSupir';

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

        return $query;
    }

    public function get()
    {
        $this->setRequestParameters();
        $tglbukaabsensi = request()->tglbukaabsensi ?? 'now';
        $deleted_id = request()->deleted_id ?? 0;
        // dd(request()->deleted_id);
        $query = $this->tableTemp($tglbukaabsensi, $deleted_id);

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $this->filter($query);
        $this->sort($query);
        $this->paginate($query);
        $data = $query->get();
        // dd($this->totalPages);
        return $data;
    }

    public function isTradoMilikSupir()
    {
        $query = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'ABSENSI SUPIR')
            ->where('subgrp', 'TRADO MILIK SUPIR')
            ->first();
        if ($query->text == 'YA') {
            return true;
        }
        return false;
    }

    public function getAll($id)
    {
        return $id;
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->integer('trado_id')->nullable();
            $table->string('kodetrado')->nullable();
            $table->string('namasupir')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('absentrado')->nullable();
            $table->integer('absen_id')->nullable();
            $table->time('jam')->nullable();
            $table->date('tglbukti')->nullable();
            $table->integer('supir_id')->nullable();
            $table->increments('position');
        });
        $this->setRequestParameters();

        $query = $this->tableTemp();
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing([
            'id',
            'trado_id',
            'kodetrado',
            'namasupir',
            'keterangan',
            'absentrado',
            'absen_id',
            'jam',
            'tglbukti',
            'supir_id',
        ], $models);

        return  $temp;
    }


    public function cekvalidasihapus($trado_id, $supir_id, $tglbukti)
    {
        $suratpengantar = DB::table('suratpengantar')
            ->from(
                DB::raw("suratpengantar as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->where('a.trado_id', '=', $trado_id)
            ->where('a.supir_id', '=', $supir_id)
            ->where('a.tglbukti', '=', $tglbukti)
            ->first();
        if (isset($suratpengantar)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Surat Pengantar',
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

    public function getabsentrado($id)
    {

        $queryabsen = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select(
                'text',
            )
            ->where('grp', 'ABSEN TIDAK ADA SUPIR')
            ->where('subgrp', 'ABSEN TIDAK ADA SUPIR')
            ->where('text', $id)
            ->first();
        if ($queryabsen) {
            $supir = ["supir" => 1];
        } else {
            $supir = ["supir" => 0];
        }
        $queryuang = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select(
                'text',
            )
            ->where('grp', 'ABSENSI TANPA UANG JALAN')
            ->where('text', $id)
            ->first();
        if ($queryuang) {
            $uang = ["uang" => 1];
        } else {
            $uang = ["uang" => 0];
        }
        // dd($queryabsen,
        // $queryuang,$id);
        return array_merge($supir, $uang);
    }


    public function isAbsen($id, $tanggal, $supir_id)
    {

        $absensisupirdetail = DB::table('absensisupirdetail')
            ->select(
                'absensisupirdetail.id as id',
                'trado.id as trado_id',
                'trado.kodetrado as trado',
                'supir.id as supir_id',
                'supir.namasupir as supir',
                'absentrado.id as absen_id',
                'absentrado.keterangan as absen',
                'absensisupirdetail.keterangan',
                'absensisupirdetail.jam',
                'absensisupirheader.tglbukti'
            )
            ->where('absensisupirdetail.trado_id', $id)
            ->where('absensisupirdetail.supir_id', $supir_id)
            ->where('absensisupirheader.tglbukti', date('Y-m-d', strtotime($tanggal)))
            ->leftJoin(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirdetail.absensi_id', 'absensisupirheader.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'absensisupirdetail.trado_id', 'trado.id')
            ->leftJoin(DB::raw("absentrado with (readuncommitted)"), 'absensisupirdetail.absen_id', 'absentrado.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'absensisupirdetail.supir_id', 'supir.id');
        return $absensisupirdetail->first();
    }

    public function isDateAllowedMandor($date)
    {
        $bukaAbsensi = BukaAbsensi::where('tglabsensi', '=', $date)->first();
        $tglbatas = $bukaAbsensi->tglbatas ?? 0;
        $limit = strtotime($tglbatas);
        $now = strtotime('now');
        if ($now < $limit) return true;
        return false;
    }

    public function getTrado($id, $supir_id)
    {
        $tradoMilikSupir = DB::table('parameter')->where('grp', 'ABSENSI SUPIR')->where('subgrp', 'TRADO MILIK SUPIR')->first();
        $cekSupirTrado = DB::table("trado")->from(DB::raw("trado with (readuncommitted)"))->where('id', $id)->where('supir_id', $supir_id)->first();

        if ($cekSupirTrado == '') {
            $tgl = request()->tanggal ?? 'now';
            $absensisupirdetail = DB::table('trado')
                ->select(
                    DB::raw('null as id'),
                    'trado.id as trado_id',
                    'trado.kodetrado as trado',
                    DB::raw('null as absen_id'),
                    DB::raw('null as keterangan'),
                    DB::raw('null as jam'),
                    DB::raw('null as tglbukti'),
                    DB::raw('supirserap.supirserap_id as supir_id'),
                    'supir.namasupir as supir'
                )->where('trado.id', $id)
                ->leftJoin(DB::raw("supirserap with (readuncommitted)"), 'supirserap.trado_id', 'trado.id')
                ->leftJoin(DB::raw("supir with (readuncommitted)"), 'supirserap.supirserap_id', 'supir.id')
                ->where('supirserap.tglabsensi', date('Y-m-d', strtotime($tgl)))
                ->where('supirserap.trado_id', $id)
                ->where('supirserap.supirserap_id', $supir_id);
        } else {

            $absensisupirdetail = DB::table('trado')
                ->select(
                    DB::raw('null as id'),
                    'trado.id as trado_id',
                    'trado.kodetrado as trado',
                    DB::raw('null as absen_id'),
                    DB::raw('null as keterangan'),
                    DB::raw('null as jam'),
                    DB::raw('null as tglbukti')
                )->where('trado.id', $id);

            if ($tradoMilikSupir->text == 'YA') {
                $absensisupirdetail->addSelect(DB::raw('trado.supir_id'), 'supir.namasupir as supir')
                    ->leftJoin('supir', 'trado.supir_id', 'supir.id');
            } else {
                $absensisupirdetail->addSelect(DB::raw('null as supir_id'));
            }
        }
        return $absensisupirdetail->first();
    }


    public function sort($query)
    {
        // switch ($this->params['sortIndex']) {
        //     case "trado_id":
        //         return $query->orderBy('a.id', $this->params['sortOrder']);
        //         break;
        //     case "kodetrado":
        //         return $query->orderBy('a.kodetrado', $this->params['sortOrder']);
        //         break;
        //     case "supir_id":
        //         return $query->orderBy('b.supir_id', $this->params['sortOrder']);
        //         break;
        //     case "namasupir":
        //         return $query->orderBy('c.namasupir', $this->params['sortOrder']);
        //         break;
        //     case "keterangan":
        //         return $query->orderBy('b.keterangan', $this->params['sortOrder']);
        //         break;
        //     case "absentrado":
        //         return $query->orderBy('d.keterangan', $this->params['sortOrder']);
        //         break;
        //     case "absen_id":
        //         return $query->orderBy('b.absen_id', $this->params['sortOrder']);
        //         break;
        //     case "jam":
        //         return $query->orderBy('b.jam', $this->params['sortOrder']);
        //         break;
        //     case "tglbukti":
        //         return $query->orderBy('b.tglbukti', $this->params['sortOrder']);
        //         break;
        //     default:
        //         return $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
        //         break;
        // }
        return $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }
    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
        // return $query->skip(request()->page * request()->limit)->take(request()->limit);

    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        switch ($filters['field']) {
                            case "tglbukti":
                                // $query = $query->whereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                                $query = $query->whereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                break;

                            default:
                                $query = $query->whereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

                                break;
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {

                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            switch ($filters['field']) {
                                case "tglbukti":
                                    // $query = $query->whereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                                    $query = $query->orWhereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                    break;

                                default:
                                    $query = $query->orWhereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

                                    break;
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

    public function processStore(array $data)
    {

        $deleted_id = $data['deleted_id'] ?? 0;
        if ($deleted_id != 0) {


            $user = auth('api')->user()->name;
            $class = 'TemporaryAbsensiSupir';

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


            // if ($data['id'] != $data['deleted_id']) {
            DB::table($temtabel)->insert(
                [
                    'tglbukti' => date("Y-m-d", strtotime($data['tglbukti'])),
                    'id' => $data['id'],
                    'trado_id' => $data['trado_id'],
                    'supir_id' => $data['supir_id'],
                    'keterangan' => $data['keterangan'],
                    'absen_id' => $data['absen_id'],
                    'supirold_id' => $data['supirold_id'],
                    'deleted_id' => $data['deleted_id'],
                ]
            );
            // }
            $absensiSupirDetail = db::table($temtabel)->from(db::raw($temtabel . " a "))->where('tglbukti', date('Y-m-d', strtotime($data['tglbukti'])))->first();
            goto selesai;
        }

       


        // 

        $AbsensiSupirHeader = AbsensiSupirHeader::where('tglbukti', date('Y-m-d', strtotime($data['tglbukti'])))->first();
        $tidakadasupir = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', 'TIDAK ADA SUPIR')->where('subgrp', 'TIDAK ADA SUPIR')->first();
        if ($tidakadasupir->text == $data['absen_id']) {
            $data['supir_id'] = "";
        }
        $data['jam'] = date('H:i', strtotime('now'));
        // $data['jam'] = date('H:i',strtotime('now'));


        $tglbataseditabsensi = null;
        $tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $isDateAllowedMandor = $this->isDateAllowedMandor($tglbukti);
        $bukaabsensi = DB::table('bukaabsensi')
            ->select('tglbatas')
            ->from(DB::raw("bukaabsensi with (readuncommitted)"))
            ->where('tglabsensi', $tglbukti)
            ->first();
        if ($isDateAllowedMandor && isset($bukaabsensi->tglbatas)) {
            $tglbataseditabsensi = $bukaabsensi->tglbatas;
        }
        if (AbsensiSupirHeader::todayValidation(date('Y-m-d', strtotime($tglbukti)))) {
            $query_jam = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', 'BATAS JAM EDIT ABSENSI')->where('subgrp', 'BATAS JAM EDIT ABSENSI')->first();
            $jam = substr($query_jam->text, 0, 2);
            $menit = substr($query_jam->text, 3, 2);
            $query_jam = strtotime($tglbukti . ' ' . $jam . ':' . $menit . ':00');
            $tglbataseditabsensi = date('Y-m-d H:i:s', $query_jam);
        }
        # code...

        if (!$AbsensiSupirHeader) {
            $absensiSupirRequest = [
                "tglbukti" => $data['tglbukti'],
                "kasgantung_nobukti" => $data['kasgantung_nobukti'],
                "tglbataseditabsensi" => $tglbataseditabsensi,
                "uangjalan" => [0],
                'supirold_id' => [$data['supirold_id']],
                "trado_id" => [$data['trado_id']],
                "supir_id" => [$data['supir_id']],
                "keterangan_detail" => [$data['keterangan']],
                "absen_id" => [$data['absen_id']],
                "jam" => [$data['jam']],
            ];
            $AbsensiSupirHeader = (new AbsensiSupirHeader())->processStore($absensiSupirRequest);
        }
        $jam = $data['jam'];
        // $AbsensiSupirDetail = (new AbsensiSupirDetail())->processStore($absensiSupirRequest);
        if ($this->isTradoMilikSupir()) {
            $absensiSupirDetail = AbsensiSupirDetail::where('absensi_id', $AbsensiSupirHeader->id)->where('trado_id', $data['trado_id'])->where('supirold_id', $data['supirold_id'])->lockForUpdate()->first();
        } else {
            $absensiSupirDetail = AbsensiSupirDetail::where('absensi_id', $AbsensiSupirHeader->id)->where('trado_id', $data['trado_id'])->lockForUpdate()->first();
        }
        DB::table('absensisupirdetail', 'a')
            ->Join(db::raw("trado b with (readuncommitted)"), 'a.trado_id', '=', 'b.id')
            ->whereRaw("isnull(b.tglberlakumilikmandor,'1900/1/1') > '" . $tglbukti . "' ")
            ->delete();

        if ($absensiSupirDetail) {
            $jam = $absensiSupirDetail->jam;
            return $this->processUpdate($absensiSupirDetail,  [
                'absensi_id' => $AbsensiSupirHeader->id,
                'nobukti' => $AbsensiSupirHeader->nobukti,
                'trado_id' => $data['trado_id'],
                'supir_id' => $data['supir_id'],
                'supirold_id' => $data['supirold_id'],
                'keterangan' => $data['keterangan'],
                'absen_id' => $data['absen_id'] ?? '',
                'jam' => $jam,
                'modifiedby' => $AbsensiSupirHeader->modifiedby,
            ]);
            // $absensiSupirDetail->delete();
        }


        $absensiSupirDetail = AbsensiSupirDetail::processStore($AbsensiSupirHeader, [
            'absensi_id' => $AbsensiSupirHeader->id,
            'nobukti' => $AbsensiSupirHeader->nobukti,
            'trado_id' => $data['trado_id'],
            'supir_id' => $data['supir_id'],
            'supirold_id' => $data['supirold_id'],
            'keterangan' => $data['keterangan'],
            'absen_id' => $data['absen_id'] ?? '',
            'jam' => $jam,
            'modifiedby' => $AbsensiSupirHeader->modifiedby,
        ]);

        $AbsensiSupirHeaderLogtrail = (new LogTrail())->processStore([
            'namatabel' => $this->table,
            'postingdari' => strtoupper('ENTRY ABSENSI SUPIR Header'),
            'idtrans' => $AbsensiSupirHeader->id,
            'nobuktitrans' => $AbsensiSupirHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $AbsensiSupirHeader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);
        (new LogTrail())->processStore([
            'namatabel' => $this->table,
            'postingdari' => strtoupper('ENTRY ABSENSI SUPIR Detail'),
            'idtrans' => $AbsensiSupirHeaderLogtrail->id,
            'nobuktitrans' => $AbsensiSupirHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $absensiSupirDetail->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);


        selesai:
        return $absensiSupirDetail;
    }
    public function processUpdate(AbsensiSupirDetail $AbsensiSupirDetail, array $data)
    {
        $AbsensiSupirHeader = AbsensiSupirHeader::where('id', $AbsensiSupirDetail->absensi_id)->first();
        $AbsensiSupirData = AbsensiSupirDetail::where('id', $AbsensiSupirDetail->id)->lockForUpdate()->first();
        // $AbsensiSupirDetail->delete();
        // $tidakadasupir = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', 'TIDAK ADA SUPIR')->where('subgrp', 'TIDAK ADA SUPIR')->first();
        // if ($tidakadasupir->text == $data['absen_id']) {
        //     $data['supir_id'] = "";
        // }
        // dd($AbsensiSupirDetail);
        $absensiSupirDetail = AbsensiSupirDetail::processUpdate($AbsensiSupirData, [
            'absensi_id' => $AbsensiSupirHeader->id,
            'nobukti' => $AbsensiSupirHeader->nobukti,
            'trado_id' => $data['trado_id'],
            'supir_id' => $data['supir_id'],
            'supirold_id' => $data['supirold_id'],
            'keterangan' => $data['keterangan'],
            'absen_id' => $data['absen_id'] ?? '',
            'jam' => $data['jam'],
            'modifiedby' => $AbsensiSupirHeader->modifiedby,
        ]);

        $AbsensiSupirHeaderLogtrail = (new LogTrail())->processStore([
            'namatabel' => $this->table,
            'postingdari' => strtoupper('EDIT ABSENSI SUPIR Header'),
            'idtrans' => $AbsensiSupirHeader->id,
            'nobuktitrans' => $AbsensiSupirHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $AbsensiSupirHeader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);
        (new LogTrail())->processStore([
            'namatabel' => $this->table,
            'postingdari' => strtoupper('EDIT ABSENSI SUPIR Detail'),
            'idtrans' => $AbsensiSupirHeaderLogtrail->id,
            'nobuktitrans' => $AbsensiSupirHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $absensiSupirDetail->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $absensiSupirDetail;
    }


    public function processDestroy($id)
    {
        // $AbsensiSupirHeader = AbsensiSupirHeader::where('id', $AbsensiSupirDetail->absensi_id)->first();
        $AbsensiSupirDetail = AbsensiSupirDetail::where('id', $id)->lockForUpdate()->first();
        $AbsensiSupirDetail->delete();
        return $AbsensiSupirDetail;
    }
}
