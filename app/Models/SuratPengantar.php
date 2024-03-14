<?php

namespace App\Models;

use App\Services\RunningNumberService;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SuratPengantar extends MyModel
{
    use HasFactory;

    protected $table = 'suratpengantar';

    protected $casts = [
        'tglbukti' => 'date:d-m-Y',
        'tglsp' => 'date:d-m-Y',
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function suratpengantarBiaya()
    {
        return $this->hasMany(SuratPengantarBiayaTambahan::class, 'suratpengantar_id');
    }
    public function todayValidation($id)
    {
        $query = DB::table('suratpengantar')->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->select('tglbukti')
            ->where('id', $id)
            ->first();

        $tglbukti = strtotime($query->tglbukti);
        $today = strtotime('today');
        if ($tglbukti === $today) {
            if (date("H:i:s") < "12:00:00") {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function isEditAble($id)
    {
        $query = DB::table('suratpengantar')->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->select('tglbataseditsuratpengantar as tglbatasedit')
            ->where('id', $id)
            ->first();
        if (date('Y-m-d H:i:s', strtotime($query->tglbatasedit)) < date('Y-m-d H:i:s')) {
            return false;
        }
        // if ($query->tglbatasedit == $approval->id) return true;
        return true;
    }

    public function cekvalidasihapus($nobukti, $jobtrucking)
    {

        $error = new Error();
        $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';

        $gajiSupir = DB::table('gajisupirdetail')
            ->from(
                DB::raw("gajisupirdetail as a with (readuncommitted)")
            )
            ->select(
                'a.suratpengantar_nobukti',
                'a.nobukti'
            )
            ->where('a.suratpengantar_nobukti', '=', $nobukti)
            ->first();


        if (isset($gajiSupir)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>'. $nobukti . '</b><br>' .$keteranganerror.'<br> GAji Supir <b>'. $gajiSupir->nobukti .'</b> <br> '.$keterangantambahanerror,
                // 'keterangan' => 'gaji supir ' . $gajiSupir->nobukti,
                'kodeerror' => 'SATL2'
            ];


            goto selesai;
        }
        if (request()->aksi == 'DELETE') {
            $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';

            $ritasi = DB::table('ritasi')
                ->from(
                    DB::raw("ritasi as a with (readuncommitted)")
                )
                ->select(
                    'a.nobukti',
                    'a.suratpengantar_nobukti'
                )
                ->where('a.suratpengantar_nobukti', '=', $nobukti)
                ->first();


            if (isset($ritasi)) {
                $data = [
                    'kondisi' => true,
                    'keterangan' => 'No Bukti <b>'. $nobukti . '</b><br>' .$keteranganerror.'<br> Ritasi <b>'. $ritasi->nobukti .'</b> <br> '.$keterangantambahanerror,
                    'kodeerror' => 'SATL2'
                    // 'keterangan' => 'ritasi ' . $ritasi->nobukti,
                ];


                goto selesai;
            }

            $cekSP = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->select('dari_id', 'jobtrucking')->where('nobukti', $nobukti)->first();
            if ($cekSP->dari_id == 1) {

                $cekJob = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->where('jobtrucking', $cekSP->jobtrucking)->where('nobukti', '<>', $nobukti)->first();
                if ($cekJob != '') {
                    $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';

                    $data = [
                        'kondisi' => true,
                        'keterangan' => 'No Bukti <b>'. $nobukti . '</b><br>' .$keteranganerror.'<br> Trip <b>'. $cekJob->nobukti .'</b> <br> '.$keterangantambahanerror,
                        'kodeerror' => 'SATL2'                        
                        // 'keterangan' => 'trip ' . $cekJob->nobukti,
                    ];


                    goto selesai;
                }
            }
        }
        $tempinvdetail = '##tempinvdetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempinvdetail, function ($table) {
            $table->string('nobukti')->nullable();
            $table->string('suratpengantar_nobukti')->nullable();
        });

        $status = InvoiceDetail::from(
            db::Raw("invoicedetail with (readuncommitted)")
        )->select('nobukti', 'suratpengantar_nobukti')
            ->where('orderantrucking_nobukti', $jobtrucking)->first();


        if (isset($status)) {
            $sp = explode(',', $status->suratpengantar_nobukti);

            for ($i = 0; $i < count($sp); $i++) {
                DB::table($tempinvdetail)->insert(
                    [
                        "nobukti" => $status->nobukti,
                        "suratpengantar_nobukti" => $sp[$i]
                    ]
                );
            }
        }


        $query = DB::table($tempinvdetail)->from(DB::raw($tempinvdetail))
            ->select(
                'nobukti',
                'suratpengantar_nobukti',
            )->where('suratpengantar_nobukti', '=', $nobukti)
            ->first();

        if (isset($query)) {
            $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';

            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>'. $nobukti . '</b><br>' .$keteranganerror.'<br> Invoice <b>'. $cekJob->nobukti .'</b> <br> '.$keterangantambahanerror,
                'kodeerror' => 'SATL2'                  
                // 'keterangan' => 'invoice ' . $query->nobukti,
            ];
            goto selesai;
        }

        $query = DB::table('pendapatansupirdetail')->from(DB::raw("pendapatansupirdetail with (readuncommitted)"))
            ->select(
                'nobuktitrip',
                'nobukti'
            )->where('nobuktitrip', '=', $nobukti)
            ->first();

        if (isset($query)) {
            $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';

            $data = [
                'kondisi' => true,
                // 'keterangan' => 'pendapatan supir ' . $query->nobukti,
                'keterangan' => 'No Bukti <b>'. $nobukti . '</b><br>' .$keteranganerror.'<br> pendapatan supir <b>'. $cekJob->nobukti .'</b> <br> '.$keterangantambahanerror,
                'kodeerror' => 'SATL2'                  

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
        $tglabsensi = request()->tglabsensi ?? '';
        $trado_id = request()->trado_id ?? '';
        $supir_id = request()->supir_id ?? '';
        $gudangsama = request()->gudangsama ?? 0;
        $longtrip = request()->longtrip ?? 0;
        $from = request()->from ?? '';

        $getSudahbuka = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS SUDAH BUKA')->where('subgrp', 'STATUS SUDAH BUKA')->where('text', 'SUDAH BUKA')->first() ?? 0;
        $getBelumbuka = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS SUDAH BUKA')->where('subgrp', 'STATUS SUDAH BUKA')->where('text', 'BELUM BUKA')->first() ?? 0;



        $tempsuratpengantar = '##tempsuratpengantar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsuratpengantar, function ($table) {
            $table->integer('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('jobtrucking', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->unsignedBigInteger('pelanggan_id')->nullable();
            $table->longText('keterangan')->nullable();
            $table->bigInteger('nourutorder')->nullable();
            $table->unsignedBigInteger('upah_id')->nullable();
            $table->unsignedBigInteger('dari_id')->nullable();
            $table->unsignedBigInteger('sampai_id')->nullable();
            $table->longText('penyesuaian')->nullable();
            $table->unsignedBigInteger('container_id')->nullable();
            $table->string('nocont', 50)->nullable();
            $table->string('nocont2', 50)->nullable();
            $table->string('noseal', 50)->nullable();
            $table->string('noseal2', 50)->nullable();
            $table->unsignedBigInteger('statuscontainer_id')->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->unsignedBigInteger('gandengan_id')->nullable();
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->string('nojob', 50)->nullable();
            $table->string('nojob2', 50)->nullable();
            $table->integer('statuslongtrip')->length(11)->nullable();
            $table->integer('statuslangsir')->length(11)->nullable();
            $table->decimal('omset', 15, 2)->nullable();
            $table->decimal('discount', 15, 2)->nullable();
            $table->decimal('totalomset', 15, 2)->nullable();
            $table->decimal('gajisupir', 15, 2)->nullable();
            $table->decimal('gajikenek', 15, 2)->nullable();
            $table->unsignedBigInteger('agen_id')->nullable();
            $table->unsignedBigInteger('jenisorder_id')->nullable();
            $table->integer('statusperalihan')->length(11)->nullable();
            $table->unsignedBigInteger('tarif_id')->nullable();
            $table->decimal('nominalperalihan', 15, 2)->nullable();
            $table->decimal('persentaseperalihan', 15, 2)->nullable();
            $table->unsignedBigInteger('biayatambahan_id')->nullable();
            $table->string('nosp', 50)->nullable();
            $table->date('tglsp')->nullable();
            $table->integer('statusritasiomset')->length(11)->nullable();
            $table->unsignedBigInteger('cabang_id')->nullable();
            $table->decimal('komisisupir', 15, 2)->nullable();
            $table->decimal('tolsupir', 15, 2)->nullable();
            $table->decimal('jarak', 15, 2)->nullable();
            $table->string('nosptagihlain', 50)->nullable();
            $table->decimal('nilaitagihlain', 15, 2)->nullable();
            $table->string('tujuantagih', 50)->nullable();
            $table->decimal('liter', 15, 2)->nullable();
            $table->decimal('nominalstafle', 15, 2)->nullable();
            $table->integer('statusnotif')->length(11)->nullable();
            $table->integer('statusoneway')->length(11)->nullable();
            $table->integer('statusedittujuan')->length(11)->nullable();
            $table->decimal('upahbongkardepo', 15, 2)->nullable();
            $table->decimal('upahmuatdepo', 15, 2)->nullable();
            $table->decimal('hargatol', 15, 2)->nullable();
            $table->decimal('qtyton', 15, 2)->nullable();
            $table->decimal('totalton', 15, 2)->nullable();
            $table->unsignedBigInteger('mandorsupir_id')->nullable();
            $table->unsignedBigInteger('mandortrado_id')->nullable();
            $table->integer('statustrip')->length(11)->nullable();
            $table->string('notripasal', 50)->nullable();
            $table->date('tgldoor')->nullable();
            $table->integer('statusdisc')->length(11)->nullable();
            $table->unsignedBigInteger('statusupahzona')->nullable();
            $table->unsignedBigInteger('zonadari_id')->nullable();
            $table->unsignedBigInteger('zonasampai_id')->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->unsignedBigInteger('statusgudangsama')->nullable();
            $table->unsignedBigInteger('statusbatalmuat')->nullable();
            $table->unsignedBigInteger('statusgandengan')->nullable();
            $table->unsignedBigInteger('gandenganasal_id')->nullable();
            $table->string('gudang', 500)->nullable();
            $table->string('lokasibongkarmuat', 500)->nullable();
            $table->integer('statusapprovaleditsuratpengantar')->Length(11)->nullable();
            $table->string('userapprovaleditsuratpengantar', 50)->nullable();
            $table->date('tglapprovaleditsuratpengantar')->nullable();
            $table->unsignedBigInteger('approvalbukatanggal_id')->nullable();
            $table->dateTime('tglbataseditsuratpengantar')->nullable();
            $table->integer('statusapprovalbiayatitipanemkl')->Length(11)->nullable();
            $table->string('userapprovalbiayatitipanemkl', 50)->nullable();
            $table->date('tglapprovalbiayatitipanemkl')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->string('gajisupir_nobukti', 500)->nullable();
            $table->string('invoice_nobukti', 500)->nullable();
            $table->unsignedBigInteger('statusgajisupir')->nullable();
            $table->unsignedBigInteger('statusinvoice')->nullable();
        });


        $querysuratpengantar = DB::table('suratpengantar')->from(
            DB::raw("suratpengantar with (readuncommitted)")
        )
            ->select(
                'suratpengantar.id',
                'suratpengantar.nobukti',
                'suratpengantar.jobtrucking',
                'suratpengantar.tglbukti',
                'suratpengantar.pelanggan_id',
                'suratpengantar.keterangan',
                'suratpengantar.nourutorder',
                'suratpengantar.upah_id',
                'suratpengantar.dari_id',
                'suratpengantar.sampai_id',
                'suratpengantar.penyesuaian',
                'suratpengantar.container_id',
                'suratpengantar.nocont',
                'suratpengantar.nocont2',
                'suratpengantar.noseal',
                'suratpengantar.noseal2',
                'suratpengantar.statuscontainer_id',
                'suratpengantar.trado_id',
                'suratpengantar.gandengan_id',
                'suratpengantar.supir_id',
                'suratpengantar.nojob',
                'suratpengantar.nojob2',
                'suratpengantar.statuslongtrip',
                'suratpengantar.statuslangsir',
                'suratpengantar.omset',
                'suratpengantar.discount',
                'suratpengantar.totalomset',
                'suratpengantar.gajisupir',
                'suratpengantar.gajikenek',
                'suratpengantar.agen_id',
                'suratpengantar.jenisorder_id',
                'suratpengantar.statusperalihan',
                'suratpengantar.tarif_id',
                'suratpengantar.nominalperalihan',
                'suratpengantar.persentaseperalihan',
                'suratpengantar.biayatambahan_id',
                'suratpengantar.nosp',
                'suratpengantar.tglsp',
                'suratpengantar.statusritasiomset',
                'suratpengantar.cabang_id',
                'suratpengantar.komisisupir',
                'suratpengantar.tolsupir',
                'suratpengantar.jarak',
                'suratpengantar.nosptagihlain',
                'suratpengantar.nilaitagihlain',
                'suratpengantar.tujuantagih',
                'suratpengantar.liter',
                'suratpengantar.nominalstafle',
                'suratpengantar.statusnotif',
                'suratpengantar.statusoneway',
                'suratpengantar.statusedittujuan',
                'suratpengantar.upahbongkardepo',
                'suratpengantar.upahmuatdepo',
                'suratpengantar.hargatol',
                'suratpengantar.qtyton',
                'suratpengantar.totalton',
                'suratpengantar.mandorsupir_id',
                'suratpengantar.mandortrado_id',
                'suratpengantar.statustrip',
                'suratpengantar.notripasal',
                'suratpengantar.tgldoor',
                'suratpengantar.statusdisc',
                'suratpengantar.statusupahzona',
                'suratpengantar.zonadari_id',
                'suratpengantar.zonasampai_id',
                'suratpengantar.statusformat',
                'suratpengantar.statusgudangsama',
                'suratpengantar.statusbatalmuat',
                'suratpengantar.statusgandengan',
                'suratpengantar.gandenganasal_id',
                'suratpengantar.gudang',
                'suratpengantar.lokasibongkarmuat',
                'suratpengantar.statusapprovaleditsuratpengantar',
                'suratpengantar.userapprovaleditsuratpengantar',
                'suratpengantar.tglapprovaleditsuratpengantar',
                'suratpengantar.approvalbukatanggal_id',
                'suratpengantar.tglbataseditsuratpengantar',
                'suratpengantar.statusapprovalbiayatitipanemkl',
                'suratpengantar.userapprovalbiayatitipanemkl',
                'suratpengantar.tglapprovalbiayatitipanemkl',
                'suratpengantar.modifiedby',
                'suratpengantar.created_at',
                'suratpengantar.updated_at',
                'b.nobukti as gajisupir_nobukti',
                'c.nobukti as invoice_nobukti',
                db::raw("(case when isnull(b.nobukti,'')='' then " . $getBelumbuka->id . " else " . $getSudahbuka->id . " end) as statusgajisupir"),
                db::raw("(case when isnull(c.nobukti,'')='' then " . $getBelumbuka->id . " else " . $getSudahbuka->id . " end) as statusinvoice"),
            )
            ->leftJoin(DB::raw("gajisupirdetail as b with (readuncommitted)"), 'suratpengantar.nobukti', 'b.suratpengantar_nobukti')
            ->leftJoin(DB::raw("invoicedetail as c with (readuncommitted)"), 'suratpengantar.jobtrucking', 'c.orderantrucking_nobukti');
        if (request()->tgldari) {
            $querysuratpengantar->whereBetween('suratpengantar.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }

        if ($from == 'tripinap') {
            $getBatasInput = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'BATAS PENGAJUAN TRIP INAP')->where('subgrp', 'BATAS PENGAJUAN TRIP INAP')->first()->text;

            $batas = date('Y-m-d', strtotime("-$getBatasInput days"));
            $querysuratpengantar->whereBetween('suratpengantar.tglbukti', [$batas, date('Y-m-d')]);
        }

        DB::table($tempsuratpengantar)->insertUsing([
            'id',
            'nobukti',
            'jobtrucking',
            'tglbukti',
            'pelanggan_id',
            'keterangan',
            'nourutorder',
            'upah_id',
            'dari_id',
            'sampai_id',
            'penyesuaian',
            'container_id',
            'nocont',
            'nocont2',
            'noseal',
            'noseal2',
            'statuscontainer_id',
            'trado_id',
            'gandengan_id',
            'supir_id',
            'nojob',
            'nojob2',
            'statuslongtrip',
            'statuslangsir',
            'omset',
            'discount',
            'totalomset',
            'gajisupir',
            'gajikenek',
            'agen_id',
            'jenisorder_id',
            'statusperalihan',
            'tarif_id',
            'nominalperalihan',
            'persentaseperalihan',
            'biayatambahan_id',
            'nosp',
            'tglsp',
            'statusritasiomset',
            'cabang_id',
            'komisisupir',
            'tolsupir',
            'jarak',
            'nosptagihlain',
            'nilaitagihlain',
            'tujuantagih',
            'liter',
            'nominalstafle',
            'statusnotif',
            'statusoneway',
            'statusedittujuan',
            'upahbongkardepo',
            'upahmuatdepo',
            'hargatol',
            'qtyton',
            'totalton',
            'mandorsupir_id',
            'mandortrado_id',
            'statustrip',
            'notripasal',
            'tgldoor',
            'statusdisc',
            'statusupahzona',
            'zonadari_id',
            'zonasampai_id',
            'statusformat',
            'statusgudangsama',
            'statusbatalmuat',
            'statusgandengan',
            'gandenganasal_id',
            'gudang',
            'lokasibongkarmuat',
            'statusapprovaleditsuratpengantar',
            'userapprovaleditsuratpengantar',
            'tglapprovaleditsuratpengantar',
            'approvalbukatanggal_id',
            'tglbataseditsuratpengantar',
            'statusapprovalbiayatitipanemkl',
            'userapprovalbiayatitipanemkl',
            'tglapprovalbiayatitipanemkl',
            'modifiedby',
            'created_at',
            'updated_at',
            'gajisupir_nobukti',
            'invoice_nobukti',
            'statusgajisupir',
            'statusinvoice',


        ], $querysuratpengantar);


        $querysuratpengantar = DB::table('saldosuratpengantar')->from(
            DB::raw("saldosuratpengantar suratpengantar with (readuncommitted)")
        )
            ->select(
                'suratpengantar.id',
                'suratpengantar.nobukti',
                'suratpengantar.jobtrucking',
                'suratpengantar.tglbukti',
                'suratpengantar.pelanggan_id',
                'suratpengantar.keterangan',
                'suratpengantar.nourutorder',
                'suratpengantar.upah_id',
                'suratpengantar.dari_id',
                'suratpengantar.sampai_id',
                'suratpengantar.penyesuaian',
                'suratpengantar.container_id',
                'suratpengantar.nocont',
                'suratpengantar.nocont2',
                'suratpengantar.noseal',
                'suratpengantar.noseal2',
                'suratpengantar.statuscontainer_id',
                'suratpengantar.trado_id',
                'suratpengantar.gandengan_id',
                'suratpengantar.supir_id',
                'suratpengantar.nojob',
                'suratpengantar.nojob2',
                'suratpengantar.statuslongtrip',
                'suratpengantar.statuslangsir',
                'suratpengantar.omset',
                'suratpengantar.discount',
                'suratpengantar.totalomset',
                'suratpengantar.gajisupir',
                'suratpengantar.gajikenek',
                'suratpengantar.agen_id',
                'suratpengantar.jenisorder_id',
                'suratpengantar.statusperalihan',
                'suratpengantar.tarif_id',
                'suratpengantar.nominalperalihan',
                'suratpengantar.persentaseperalihan',
                'suratpengantar.biayatambahan_id',
                'suratpengantar.nosp',
                'suratpengantar.tglsp',
                'suratpengantar.statusritasiomset',
                'suratpengantar.cabang_id',
                'suratpengantar.komisisupir',
                'suratpengantar.tolsupir',
                'suratpengantar.jarak',
                'suratpengantar.nosptagihlain',
                'suratpengantar.nilaitagihlain',
                'suratpengantar.tujuantagih',
                'suratpengantar.liter',
                'suratpengantar.nominalstafle',
                'suratpengantar.statusnotif',
                'suratpengantar.statusoneway',
                'suratpengantar.statusedittujuan',
                'suratpengantar.upahbongkardepo',
                'suratpengantar.upahmuatdepo',
                'suratpengantar.hargatol',
                'suratpengantar.qtyton',
                'suratpengantar.totalton',
                'suratpengantar.mandorsupir_id',
                'suratpengantar.mandortrado_id',
                'suratpengantar.statustrip',
                'suratpengantar.notripasal',
                'suratpengantar.tgldoor',
                'suratpengantar.statusdisc',
                'suratpengantar.statusupahzona',
                'suratpengantar.zonadari_id',
                'suratpengantar.zonasampai_id',
                'suratpengantar.statusformat',
                'suratpengantar.statusgudangsama',
                'suratpengantar.statusbatalmuat',
                'suratpengantar.statusgandengan',
                'suratpengantar.gandenganasal_id',
                'suratpengantar.gudang',
                'suratpengantar.lokasibongkarmuat',
                'suratpengantar.statusapprovaleditsuratpengantar',
                'suratpengantar.userapprovaleditsuratpengantar',
                'suratpengantar.tglapprovaleditsuratpengantar',
                'suratpengantar.approvalbukatanggal_id',
                'suratpengantar.modifiedby',
                'suratpengantar.created_at',
                'suratpengantar.updated_at',
            );
        if (request()->tgldari) {
            $querysuratpengantar->whereBetween('suratpengantar.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }

        if ($from == 'tripinap') {
            $getBatasInput = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'BATAS PENGAJUAN TRIP INAP')->where('subgrp', 'BATAS PENGAJUAN TRIP INAP')->first()->text;

            $batas = date('Y-m-d', strtotime("-$getBatasInput days"));
            $querysuratpengantar->whereBetween('suratpengantar.tglbukti', [$batas, date('Y-m-d')]);
        }
        DB::table($tempsuratpengantar)->insertUsing([
            'id',
            'nobukti',
            'jobtrucking',
            'tglbukti',
            'pelanggan_id',
            'keterangan',
            'nourutorder',
            'upah_id',
            'dari_id',
            'sampai_id',
            'penyesuaian',
            'container_id',
            'nocont',
            'nocont2',
            'noseal',
            'noseal2',
            'statuscontainer_id',
            'trado_id',
            'gandengan_id',
            'supir_id',
            'nojob',
            'nojob2',
            'statuslongtrip',
            'statuslangsir',
            'omset',
            'discount',
            'totalomset',
            'gajisupir',
            'gajikenek',
            'agen_id',
            'jenisorder_id',
            'statusperalihan',
            'tarif_id',
            'nominalperalihan',
            'persentaseperalihan',
            'biayatambahan_id',
            'nosp',
            'tglsp',
            'statusritasiomset',
            'cabang_id',
            'komisisupir',
            'tolsupir',
            'jarak',
            'nosptagihlain',
            'nilaitagihlain',
            'tujuantagih',
            'liter',
            'nominalstafle',
            'statusnotif',
            'statusoneway',
            'statusedittujuan',
            'upahbongkardepo',
            'upahmuatdepo',
            'hargatol',
            'qtyton',
            'totalton',
            'mandorsupir_id',
            'mandortrado_id',
            'statustrip',
            'notripasal',
            'tgldoor',
            'statusdisc',
            'statusupahzona',
            'zonadari_id',
            'zonasampai_id',
            'statusformat',
            'statusgudangsama',
            'statusbatalmuat',
            'statusgandengan',
            'gandenganasal_id',
            'gudang',
            'lokasibongkarmuat',
            'statusapprovaleditsuratpengantar',
            'userapprovaleditsuratpengantar',
            'tglapprovaleditsuratpengantar',
            'approvalbukatanggal_id',
            'modifiedby',
            'created_at',
            'updated_at',

        ], $querysuratpengantar);

        $query = DB::table($tempsuratpengantar)->from(
            db::raw($tempsuratpengantar . ' suratpengantar')
        )
            ->select(
                'suratpengantar.id',
                'suratpengantar.jobtrucking',
                'suratpengantar.nobukti',
                'suratpengantar.tglbukti',
                'suratpengantar.nosp',
                'suratpengantar.tglsp',
                'suratpengantar.nojob',
                'pelanggan.namapelanggan as pelanggan_id',
                'suratpengantar.keterangan',
                'kotadari.keterangan as dari_id',
                'kotasampai.keterangan as sampai_id',
                'suratpengantar.penyesuaian',
                'suratpengantar.gajisupir',
                DB::raw("(case when suratpengantar.jarak IS NULL then 0 else suratpengantar.jarak end) as jarak"),
                'agen.namaagen as agen_id',
                'jenisorder.keterangan as jenisorder_id',
                'container.keterangan as container_id',
                'suratpengantar.nocont',
                'suratpengantar.noseal',
                'suratpengantar.omset',
                DB::raw("(case when suratpengantar.nominalperalihan IS NULL then 0 else suratpengantar.nominalperalihan end) as nominalperalihan"),
                'suratpengantar.totalomset',
                'statuscontainer.keterangan as statuscontainer_id',
                'suratpengantar.gudang',
                'trado.kodetrado as trado_id',
                'supir.namasupir as supir_id',
                'suratpengantar.trado_id as tradolookup',
                'suratpengantar.supir_id as supirlookup',
                'gandengan.keterangan as gandengan_id',
                'statuslongtrip.memo as statuslongtrip',
                'statusperalihan.memo as statusperalihan',
                'statusritasiomset.memo as statusritasiomset',
                'statusapprovaleditsuratpengantar.memo as statusapprovaleditsuratpengantar',
                'statusapprovalbiayatitipanemkl.memo as statusapprovalbiayatitipanemkl',
                'tarif.tujuan as tarif_id',
                'mandortrado.namamandor as mandortrado_id',
                'mandorsupir.namamandor as mandorsupir_id',
                'statusgudangsama.memo as statusgudangsama',
                'statusbatalmuat.memo as statusbatalmuat',
                'suratpengantar.userapprovaleditsuratpengantar',
                'suratpengantar.userapprovalbiayatitipanemkl',
                DB::raw("(case when year(isnull(suratpengantar.tglapprovaleditsuratpengantar,'1900/1/1'))<2000 then null else suratpengantar.tglapprovaleditsuratpengantar end) as tglapprovaleditsuratpengantar"),
                DB::raw("(case when year(isnull(suratpengantar.tglbataseditsuratpengantar,'1900/1/1 00:00:00.000'))<2000 then null else suratpengantar.tglbataseditsuratpengantar end) as tglbataseditsuratpengantar"),
                DB::raw("(case when year(isnull(suratpengantar.tglapprovalbiayatitipanemkl,'1900/1/1 00:00:00.000'))<2000 then null else suratpengantar.tglapprovalbiayatitipanemkl end) as tglapprovalbiayatitipanemkl"),
                'suratpengantar.modifiedby',
                'suratpengantar.modifiedby',
                'suratpengantar.created_at',
                'suratpengantar.updated_at',
                'suratpengantar.gajisupir_nobukti',
                'suratpengantar.invoice_nobukti',
                'statusgajisupir.memo as statusgajisupir',
                'statusinvoice.memo as statusinvoice',

            )

            ->leftJoin('pelanggan', 'suratpengantar.pelanggan_id', 'pelanggan.id')
            ->leftJoin('kota as kotadari', 'kotadari.id', '=', 'suratpengantar.dari_id')
            ->leftJoin('kota as kotasampai', 'kotasampai.id', '=', 'suratpengantar.sampai_id')
            ->leftJoin('agen', 'suratpengantar.agen_id', 'agen.id')
            ->leftJoin('jenisorder', 'suratpengantar.jenisorder_id', 'jenisorder.id')
            ->leftJoin('container', 'suratpengantar.container_id', 'container.id')
            ->leftJoin('statuscontainer', 'suratpengantar.statuscontainer_id', 'statuscontainer.id')
            ->leftJoin('trado', 'suratpengantar.trado_id', 'trado.id')
            ->leftJoin('supir', 'suratpengantar.supir_id', 'supir.id')
            ->leftJoin('gandengan', 'suratpengantar.gandengan_id', 'gandengan.id')
            ->leftJoin('parameter as statuslongtrip', 'suratpengantar.statuslongtrip', 'statuslongtrip.id')
            ->leftJoin('parameter as statusperalihan', 'suratpengantar.statusperalihan', 'statusperalihan.id')
            ->leftJoin('parameter as statusritasiomset', 'suratpengantar.statusritasiomset', 'statusritasiomset.id')
            ->leftJoin('parameter as statusgudangsama', 'suratpengantar.statusgudangsama', 'statusgudangsama.id')
            ->leftJoin('parameter as statusbatalmuat', 'suratpengantar.statusbatalmuat', 'statusbatalmuat.id')
            ->leftJoin('parameter as statusapprovaleditsuratpengantar', 'suratpengantar.statusapprovaleditsuratpengantar', 'statusapprovaleditsuratpengantar.id')
            ->leftJoin('parameter as statusapprovalbiayatitipanemkl', 'suratpengantar.statusapprovalbiayatitipanemkl', 'statusapprovalbiayatitipanemkl.id')
            ->leftJoin('parameter as statusgajisupir', 'suratpengantar.statusgajisupir', 'statusgajisupir.id')
            ->leftJoin('parameter as statusinvoice', 'suratpengantar.statusinvoice', 'statusinvoice.id')
            ->leftJoin('mandor as mandortrado', 'suratpengantar.mandortrado_id', 'mandortrado.id')
            ->leftJoin('mandor as mandorsupir', 'suratpengantar.mandorsupir_id', 'mandorsupir.id')
            ->leftJoin('tarif', 'suratpengantar.tarif_id', 'tarif.id');
        if (request()->tgldari) {
            $query->whereBetween('suratpengantar.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }

        if (request()->pengeluarantruckingheader != '') {
            // $query->whereNotIn('suratpengantar.nobukti', function ($query) {
            //     $query->select(DB::raw('DISTINCT pengeluarantruckingdetail.suratpengantar_nobukti'))
            //         ->from('pengeluarantruckingdetail')
            //         ->whereNotNull('pengeluarantruckingdetail.suratpengantar_nobukti')
            //         ->where('pengeluarantruckingdetail.suratpengantar_nobukti', '!=', '');
            // });
            if (request()->jenisorder_id != null) {
                $query->where('suratpengantar.jenisorder_id', request()->jenisorder_id);
            }
        }

        if (request()->biayatambahan != '') {
            $tempTambahan = '##tempTambahan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempTambahan, function ($table) {
                $table->integer('suratpengantar_id')->nullable();
            });
            $tambahan = DB::table("suratpengantarbiayatambahan")->from(DB::raw("suratpengantarbiayatambahan with (readuncommitted)"))
                ->select('suratpengantar_id')
                ->groupBy('suratpengantar_id');
            DB::table($tempTambahan)->insertUsing(['suratpengantar_id'], $tambahan);
            $query->join(DB::raw("$tempTambahan as suratpengantarbiayatambahan with (readuncommitted)"), 'suratpengantar.id', 'suratpengantarbiayatambahan.suratpengantar_id');
        }
        // if (request()->jenisorder_id != null) {
        //     $query->where('suratpengantar.jenisorder_id', request()->jenisorder_id);
        // }
        $isGudangSama = request()->isGudangSama ?? '';
        if ($isGudangSama == 'true') {

            if ($gudangsama == 204) {
                $container_id = request()->container_id ?? 0;
                $agen_id = request()->agen_id ?? 0;
                $upah_id = request()->upah_id ?? 0;
                $pelanggan_id = request()->pelanggan_id ?? 0;
                $trado_id = request()->trado_id ?? 0;
                $supir_id = request()->supir_id ?? 0;
                $query->whereRaw("suratpengantar.jenisorder_id in (2,3)")
                    ->where('suratpengantar.container_id', $container_id)
                    ->where('suratpengantar.agen_id', $agen_id)
                    ->where('suratpengantar.upah_id', $upah_id)
                    ->where('suratpengantar.trado_id', $trado_id)
                    ->where('suratpengantar.pelanggan_id', $pelanggan_id);
            }
        }
        if ($isGudangSama == 'false') {
            if ($longtrip == 66) {
                $jenisorder_id = request()->jenisorder_id ?? 0;
                $container_id = request()->container_id ?? 0;
                $pelanggan_id = request()->pelanggan_id ?? 0;
                $trado_id = request()->trado_id ?? 0;
                $query->where('suratpengantar.dari_id', '!=', 1)
                    ->where('suratpengantar.statuslongtrip', 65)
                    ->where('suratpengantar.trado_id', $trado_id)
                    ->where('suratpengantar.pelanggan_id', $pelanggan_id)
                    ->where('suratpengantar.jenisorder_id', $jenisorder_id)
                    ->where('suratpengantar.container_id', $container_id);
            }
        }

        if ($from == 'tripinap') {
            
            if ($tglabsensi != '') {
                $query->where('suratpengantar.tglbukti', '<=', date('Y-m-d', strtotime($tglabsensi)));
            }
            if ($supir_id != '') {
                $query->where('suratpengantar.supir_id', $supir_id);
            }
            if ($trado_id != '') {
                $query->where('suratpengantar.trado_id', $trado_id);
            }
        }

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();
        $this->totalJarak = $data->sum('jarak');

        return $data;
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->date('tglbukti')->nullable();
            $table->unsignedBigInteger('statuslongtrip')->nullable();
            $table->unsignedBigInteger('statusperalihan')->nullable();
            $table->unsignedBigInteger('statusritasiomset')->nullable();
            $table->unsignedBigInteger('statusgudangsama')->nullable();
            $table->unsignedBigInteger('statusbatalmuat')->nullable();
            $table->unsignedBigInteger('statusgandengan')->nullable();
            $table->unsignedBigInteger('statusupahzona')->nullable();
            $table->unsignedBigInteger('statuslangsir')->nullable();
        });

        $getFormat = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'INPUT TRIP')->where('subgrp', 'FORMAT BATAS INPUT')->first();
        if ($getFormat->text == 'FORMAT 2') {
            $waktu = date('H:i:s');

            $getBatasInput = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JAMBATASINPUTTRIP')->where('subgrp', 'JAMBATASINPUTTRIP')->first();
            if ($waktu < $getBatasInput->text) {
                $tglbukti =  date('Y-m-d', strtotime('-1 days'));
            } else {
                $tglbukti = date('Y-m-d');
            }
        } else {
            $tglbukti = date('Y-m-d');
        }
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS LONGTRIP')
            ->where('subgrp', '=', 'STATUS LONGTRIP')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatuslongtrip = $status->id ?? 0;

        // PERALIHAN
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS PERALIHAN')
            ->where('subgrp', '=', 'STATUS PERALIHAN')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusperalihan = $status->id ?? 0;

        // RITASI OMSET
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS RITASI OMSET')
            ->where('subgrp', '=', 'STATUS RITASI OMSET')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusritasi = $status->id ?? 0;

        // GUDANG SAMA
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS GUDANG SAMA')
            ->where('subgrp', '=', 'STATUS GUDANG SAMA')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusgudang = $status->id ?? 0;

        // BATAL MUAT
        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS BATAL MUAT')
            ->where('subgrp', '=', 'STATUS BATAL MUAT')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusbatal = $status->id ?? 0;

        $status = Parameter::from(

            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS GANDENGAN')
            ->where('subgrp', '=', 'STATUS GANDENGAN')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusgandengan = $status->id ?? 0;

        $status = Parameter::from(

            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS UPAH ZONA')
            ->where('subgrp', '=', 'STATUS UPAH ZONA')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusupahzona = $status->id ?? 0;

        $status = Parameter::from(

            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS LANGSIR')
            ->where('subgrp', '=', 'STATUS LANGSIR')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatuslangsir = $status->id ?? 0;

        DB::table($tempdefault)->insert(
            [
                "tglbukti" => $tglbukti,
                "statuslongtrip" => $iddefaultstatuslongtrip,
                "statusperalihan" => $iddefaultstatusperalihan,
                "statusritasiomset" => $iddefaultstatusritasi,
                "statusgudangsama" => $iddefaultstatusgudang,
                "statusbatalmuat" => $iddefaultstatusbatal,
                "statusgandengan" => $iddefaultstatusgandengan,
                "statusupahzona" => $iddefaultstatusupahzona,
                "statuslangsir" => $iddefaultstatuslangsir,
            ]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'tglbukti',
                'statuslongtrip',
                'statusperalihan',
                'statusritasiomset',
                'statusgudangsama',
                'statusbatalmuat',
                'statusgandengan',
                'statusupahzona',
                'statuslangsir',
            );

        $data = $query->first();

        return $data;
    }

    public function findAll($id)
    {
        $params = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PENDAPATAN SUPIR')->where('subgrp', 'GAJI KENEK')->first();
        $komisi_gajisupir = $params->text;

        $isKomisiReadonly = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'SURAT PENGANTAR')->where('subgrp', 'KOMISI')->first();

        $getBukanUpahZona = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS UPAH ZONA')->where('text', 'NON UPAH ZONA')->first();
        $get = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->select('statusupahzona')->where('id', $id)->first();

        $getGaji = DB::table('suratpengantar')->from(DB::raw("suratpengantar with (readuncommitted)"));
        // if ($komisi_gajisupir == 'YA') {
        //     if (trim($isKomisiReadonly->text) == 'YA') {
        //         $getGaji->select(DB::raw("suratpengantar.id, isnull(upahsupirrincian.nominalsupir,0) - isnull(upahsupirrincian.nominalkenek,0) as nominalsupir, upahsupirrincian.nominalkenek, upahsupirrincian.nominalkomisi, upahsupirrincian.nominaltol, upahsupirrincian.liter"));
        //     } else {
        //         $getGaji->select(DB::raw("suratpengantar.id, isnull(upahsupirrincian.nominalsupir,0) - isnull(suratpengantar.gajikenek,0) as nominalsupir, suratpengantar.gajikenek as nominalkenek, suratpengantar.komisisupir as nominalkomisi, upahsupirrincian.nominaltol, upahsupirrincian.liter"));
        //     }
        // } else {
        $getGaji->select('suratpengantar.id', 'upahsupirrincian.nominalsupir', 'upahsupirrincian.nominalkenek', 'upahsupirrincian.nominalkomisi', 'upahsupirrincian.nominaltol', 'upahsupirrincian.liter');
        // }
        $getGaji->leftJoin(DB::raw("upahsupirrincian with (readuncommitted)"), 'suratpengantar.upah_id', 'upahsupirrincian.upahsupir_id')
            ->where('suratpengantar.id', $id)
            ->whereRaw("upahsupirrincian.container_id = suratpengantar.container_id")
            ->whereRaw("upahsupirrincian.statuscontainer_id = suratpengantar.statuscontainer_id");

        $tempGaji = '##tempGaji' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempGaji, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->decimal('nominalsupir', 15, 2)->nullable();
            $table->decimal('nominalkenek', 15, 2)->nullable();
            $table->decimal('nominalkomisi', 15, 2)->nullable();
            $table->decimal('nominaltol', 15, 2)->nullable();
            $table->decimal('liter', 15, 2)->nullable();
        });

        DB::table($tempGaji)->insertUsing([
            'id',
            'nominalsupir',
            'nominalkenek',
            'nominalkomisi',
            'nominaltol',
            'liter'
        ], $getGaji);

        if ($get->statusupahzona == $getBukanUpahZona->id) {

            $data = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
                ->select(
                    'suratpengantar.id',
                    'suratpengantar.nobukti',
                    'suratpengantar.tglbukti',
                    'suratpengantar.jobtrucking',
                    'suratpengantar.statuslongtrip',
                    'suratpengantar.nosp',
                    'suratpengantar.trado_id',
                    'trado.kodetrado as trado',
                    'trado.nominalplusborongan',
                    'suratpengantar.supir_id',
                    'supir.namasupir as supir',
                    'suratpengantar.dari_id',
                    'kotadari.kodekota as dari',
                    'suratpengantar.gandengan_id',
                    'gandengan.kodegandengan as gandengan',
                    'suratpengantar.container_id',
                    'container.kodecontainer as container',
                    'suratpengantar.nocont',
                    'suratpengantar.noseal',
                    'suratpengantar.statusperalihan',
                    DB::raw("(case when suratpengantar.persentaseperalihan IS NULL then 0 else suratpengantar.persentaseperalihan end) as persentaseperalihan"),
                    'suratpengantar.omset',
                    'suratpengantar.statusritasiomset',
                    'suratpengantar.nosptagihlain as nosp2',
                    'suratpengantar.statusgudangsama',
                    'suratpengantar.keterangan',
                    'suratpengantar.penyesuaian',
                    'suratpengantar.sampai_id',
                    'kotasampai.kodekota as sampai',
                    'suratpengantar.statuscontainer_id',
                    'statuscontainer.kodestatuscontainer as statuscontainer',
                    'suratpengantar.nocont2',
                    'suratpengantar.noseal2',
                    'suratpengantar.pelanggan_id',
                    'pelanggan.namapelanggan as pelanggan',
                    'suratpengantar.agen_id',
                    'agen.namaagen as agen',
                    'suratpengantar.jenisorder_id',
                    'jenisorder.kodejenisorder as jenisorder',
                    'suratpengantar.tarif_id as tarifrincian_id',
                    'tarif.tujuan as tarifrincian',
                    DB::raw("(case when suratpengantar.nominalperalihan IS NULL then 0 else suratpengantar.nominalperalihan end) as nominalperalihan"),
                    'suratpengantar.nojob',
                    'suratpengantar.nojob2',
                    'suratpengantar.cabang_id',
                    'cabang.namacabang as cabang',
                    'suratpengantar.qtyton',
                    'suratpengantar.gudang',
                    'suratpengantar.statusbatalmuat',
                    'suratpengantar.statusupahzona',
                    'suratpengantar.statusgandengan',
                    'suratpengantar.gajisupir',
                    'suratpengantar.gajikenek',
                    'suratpengantar.komisisupir',
                    'suratpengantar.upah_id',
                    'suratpengantar.nobukti_tripasal',
                    'suratpengantar.statusapprovaleditsuratpengantar',
                    'suratpengantar.statusapprovalbiayatitipanemkl',
                    'kotaupah.kodekota as upah'
                )
                ->leftJoin('kota as kotadari', 'kotadari.id', '=', 'suratpengantar.dari_id')
                ->leftJoin('kota as kotasampai', 'kotasampai.id', '=', 'suratpengantar.sampai_id')
                ->leftJoin('agen', 'suratpengantar.agen_id', 'agen.id')
                ->leftJoin('container', 'suratpengantar.container_id', 'container.id')
                ->leftJoin('statuscontainer', 'suratpengantar.statuscontainer_id', 'statuscontainer.id')
                ->leftJoin('trado', 'suratpengantar.trado_id', 'trado.id')
                ->leftJoin('supir', 'suratpengantar.supir_id', 'supir.id')
                ->leftJoin('jenisorder', 'suratpengantar.jenisorder_id', 'jenisorder.id')
                ->leftJoin('tarif', 'suratpengantar.tarif_id', 'tarif.id')
                ->leftJoin('upahsupir', 'suratpengantar.upah_id', 'upahsupir.id')
                ->leftJoin('kota as kotaupah', 'kotaupah.id', '=', 'upahsupir.kotasampai_id')
                ->leftJoin('cabang', 'suratpengantar.cabang_id', 'cabang.id')
                ->leftJoin('pelanggan', 'suratpengantar.pelanggan_id', 'pelanggan.id')
                ->leftJoin('gandengan', 'suratpengantar.gandengan_id', 'gandengan.id')
                // ->leftJoin(DB::raw("$tempGaji with (readuncommitted)"), "$tempGaji.id", "suratpengantar.id")

                ->where('suratpengantar.id', $id)->first();
        } else {

            $data = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
                ->select(
                    'suratpengantar.id',
                    'suratpengantar.nobukti',
                    'suratpengantar.tglbukti',
                    'suratpengantar.jobtrucking',
                    'suratpengantar.statuslongtrip',
                    'suratpengantar.nosp',
                    'suratpengantar.trado_id',
                    'trado.kodetrado as trado',
                    'trado.nominalplusborongan',
                    'suratpengantar.supir_id',
                    'supir.namasupir as supir',
                    'suratpengantar.dari_id',
                    'kotadari.kodekota as dari',
                    'suratpengantar.gandengan_id',
                    'gandengan.kodegandengan as gandengan',
                    'suratpengantar.container_id',
                    'container.kodecontainer as container',
                    'suratpengantar.nocont',
                    'suratpengantar.noseal',
                    'suratpengantar.statusperalihan',
                    'suratpengantar.persentaseperalihan',
                    'suratpengantar.statusritasiomset',
                    'suratpengantar.nosptagihlain as nosp2',
                    'suratpengantar.statusgudangsama',
                    'suratpengantar.keterangan',
                    'suratpengantar.penyesuaian',
                    'suratpengantar.sampai_id',
                    'kotasampai.kodekota as sampai',
                    'suratpengantar.statuscontainer_id',
                    'statuscontainer.kodestatuscontainer as statuscontainer',
                    'suratpengantar.nocont2',
                    'suratpengantar.noseal2',
                    'suratpengantar.pelanggan_id',
                    'pelanggan.namapelanggan as pelanggan',
                    'suratpengantar.agen_id',
                    'agen.namaagen as agen',
                    'suratpengantar.jenisorder_id',
                    'jenisorder.kodejenisorder as jenisorder',
                    'suratpengantar.tarif_id as tarifrincian_id',
                    'tarif.tujuan as tarifrincian',
                    'suratpengantar.nominalperalihan',
                    'suratpengantar.nojob',
                    'suratpengantar.nojob2',
                    'suratpengantar.cabang_id',
                    'cabang.namacabang as cabang',
                    'suratpengantar.qtyton',
                    'suratpengantar.gudang',
                    'suratpengantar.statusbatalmuat',
                    'suratpengantar.statusupahzona',
                    'suratpengantar.statusgandengan',
                    'suratpengantar.gajisupir',
                    'suratpengantar.gajikenek',
                    'suratpengantar.komisisupir',
                    'suratpengantar.upah_id',
                    'suratpengantar.nobukti_tripasal',
                    'suratpengantar.statusapprovaleditsuratpengantar',
                    'suratpengantar.statusapprovalbiayatitipanemkl',
                    'zonaupah.zona as upah'
                )
                ->leftJoin('kota as kotadari', 'kotadari.id', '=', 'suratpengantar.dari_id')
                ->leftJoin('kota as kotasampai', 'kotasampai.id', '=', 'suratpengantar.sampai_id')
                ->leftJoin('agen', 'suratpengantar.agen_id', 'agen.id')
                ->leftJoin('container', 'suratpengantar.container_id', 'container.id')
                ->leftJoin('statuscontainer', 'suratpengantar.statuscontainer_id', 'statuscontainer.id')
                ->leftJoin('trado', 'suratpengantar.trado_id', 'trado.id')
                ->leftJoin('supir', 'suratpengantar.supir_id', 'supir.id')
                ->leftJoin('jenisorder', 'suratpengantar.jenisorder_id', 'jenisorder.id')
                ->leftJoin('tarif', 'suratpengantar.tarif_id', 'tarif.id')
                ->leftJoin('upahsupir', 'suratpengantar.upah_id', 'upahsupir.id')
                ->leftJoin('zona as zonaupah', 'zonaupah.id', '=', 'upahsupir.zonasampai_id')
                ->leftJoin('cabang', 'suratpengantar.cabang_id', 'cabang.id')
                ->leftJoin('pelanggan', 'suratpengantar.pelanggan_id', 'pelanggan.id')
                ->leftJoin('gandengan', 'suratpengantar.gandengan_id', 'gandengan.id')
                // ->leftJoin(DB::raw("$tempGaji with (readuncommitted)"), "$tempGaji.id", "suratpengantar.id")

                ->where('suratpengantar.id', $id)->first();
        }
        // dd('find');
        return $data;
    }

    public function selectColumns()
    { //sesuaikan dengan createtemp
        $tempsuratpengantar = '##tempsuratpengantar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsuratpengantar, function ($table) {
            $table->integer('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('jobtrucking', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('nosp', 50)->nullable();
            $table->date('tglsp')->nullable();
            $table->string('nojob', 50)->nullable();
            $table->unsignedBigInteger('pelanggan_id')->nullable();
            $table->longText('keterangan')->nullable();
            $table->unsignedBigInteger('dari_id')->nullable();
            $table->unsignedBigInteger('sampai_id')->nullable();
            $table->longText('penyesuaian')->nullable();
            $table->decimal('gajisupir', 15, 2)->nullable();
            $table->decimal('jarak', 15, 2)->nullable();
            $table->unsignedBigInteger('agen_id')->nullable();
            $table->unsignedBigInteger('jenisorder_id')->nullable();
            $table->unsignedBigInteger('container_id')->nullable();
            $table->string('nocont', 50)->nullable();
            $table->string('noseal', 50)->nullable();
            $table->decimal('omset', 15, 2)->nullable();
            $table->decimal('nominalperalihan', 15, 2)->nullable();
            $table->decimal('totalomset', 15, 2)->nullable();
            $table->unsignedBigInteger('statuscontainer_id')->nullable();
            $table->string('gudang', 500)->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->unsignedBigInteger('gandengan_id')->nullable();
            $table->integer('statuslongtrip')->length(11)->nullable();
            $table->integer('statusperalihan')->length(11)->nullable();
            $table->integer('statusritasiomset')->length(11)->nullable();
            $table->integer('statusapprovaleditsuratpengantar')->Length(11)->nullable();
            $table->integer('statusapprovalbiayatitipanemkl')->Length(11)->nullable();
            $table->unsignedBigInteger('tarif_id')->nullable();
            $table->unsignedBigInteger('mandortrado_id')->nullable();
            $table->unsignedBigInteger('mandorsupir_id')->nullable();
            $table->unsignedBigInteger('statusgudangsama')->nullable();
            $table->unsignedBigInteger('statusbatalmuat')->nullable();
            $table->string('userapprovaleditsuratpengantar', 50)->nullable();
            $table->string('userapprovalbiayatitipanemkl', 50)->nullable();
            $table->date('tglapprovaleditsuratpengantar')->nullable();
            $table->dateTime('tglbataseditsuratpengantar')->nullable();
            $table->date('tglapprovalbiayatitipanemkl')->nullable();
            $table->string('gajisupir_nobukti', 500)->nullable();
            $table->string('invoice_nobukti', 500)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->unsignedBigInteger('statusgajisupir')->nullable();
            $table->unsignedBigInteger('statusinvoice')->nullable();
        });

        $getSudahbuka = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS SUDAH BUKA')->where('subgrp', 'STATUS SUDAH BUKA')->where('text', 'SUDAH BUKA')->first() ?? 0;
        $getBelumbuka = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS SUDAH BUKA')->where('subgrp', 'STATUS SUDAH BUKA')->where('text', 'BELUM BUKA')->first() ?? 0;


        $querysuratpengantar = DB::table('suratpengantar')->from(
            DB::raw("suratpengantar with (readuncommitted)")
        )
            ->select(
                'suratpengantar.id',
                'suratpengantar.nobukti',
                'suratpengantar.jobtrucking',
                'suratpengantar.tglbukti',
                'suratpengantar.nosp',
                'suratpengantar.tglsp',
                'suratpengantar.nojob',
                'suratpengantar.pelanggan_id',
                'suratpengantar.keterangan',
                'suratpengantar.dari_id',
                'suratpengantar.sampai_id',
                'suratpengantar.penyesuaian',
                'suratpengantar.gajisupir',
                'suratpengantar.jarak',
                'suratpengantar.agen_id',
                'suratpengantar.jenisorder_id',
                'suratpengantar.container_id',
                'suratpengantar.nocont',
                'suratpengantar.noseal',
                'suratpengantar.omset',
                'suratpengantar.nominalperalihan',
                'suratpengantar.totalomset',
                'suratpengantar.statuscontainer_id',
                'suratpengantar.gudang',
                'suratpengantar.trado_id',
                'suratpengantar.supir_id',
                'suratpengantar.gandengan_id',
                'suratpengantar.statuslongtrip',
                'suratpengantar.statusperalihan',
                'suratpengantar.statusritasiomset',
                'suratpengantar.statusapprovaleditsuratpengantar',
                'suratpengantar.statusapprovalbiayatitipanemkl',
                'suratpengantar.tarif_id',
                'suratpengantar.mandortrado_id',
                'suratpengantar.mandorsupir_id',
                'suratpengantar.statusgudangsama',
                'suratpengantar.statusbatalmuat',
                'suratpengantar.userapprovaleditsuratpengantar',
                'suratpengantar.userapprovalbiayatitipanemkl',
                'suratpengantar.tglapprovaleditsuratpengantar',
                'suratpengantar.tglbataseditsuratpengantar',
                'suratpengantar.tglapprovalbiayatitipanemkl',
                'b.nobukti as gajisupir_nobukti',
                'c.nobukti as invoice_nobukti',
                'suratpengantar.modifiedby',
                'suratpengantar.created_at',
                'suratpengantar.updated_at',
                db::raw("(case when isnull(b.nobukti,'')='' then " . $getBelumbuka->id . " else " . $getSudahbuka->id . " end) as statusgajisupir"),
                db::raw("(case when isnull(c.nobukti,'')='' then " . $getBelumbuka->id . " else " . $getSudahbuka->id . " end) as statusinvoice"),

            )
            ->leftJoin(DB::raw("gajisupirdetail as b with (readuncommitted)"), 'suratpengantar.nobukti', 'b.suratpengantar_nobukti')
            ->leftJoin(DB::raw("invoicedetail as c with (readuncommitted)"), 'suratpengantar.jobtrucking', 'c.orderantrucking_nobukti');
        DB::table($tempsuratpengantar)->insertUsing([
            'id',
            'nobukti',
            'jobtrucking',
            'tglbukti',
            'nosp',
            'tglsp',
            'nojob',
            'pelanggan_id',
            'keterangan',
            'dari_id',
            'sampai_id',
            'penyesuaian',
            'gajisupir',
            'jarak',
            'agen_id',
            'jenisorder_id',
            'container_id',
            'nocont',
            'noseal',
            'omset',
            'nominalperalihan',
            'totalomset',
            'statuscontainer_id',
            'gudang',
            'trado_id',
            'supir_id',
            'gandengan_id',
            'statuslongtrip',
            'statusperalihan',
            'statusritasiomset',
            'statusapprovaleditsuratpengantar',
            'statusapprovalbiayatitipanemkl',
            'tarif_id',
            'mandortrado_id',
            'mandorsupir_id',
            'statusgudangsama',
            'statusbatalmuat',
            'userapprovaleditsuratpengantar',
            'userapprovalbiayatitipanemkl',
            'tglapprovaleditsuratpengantar',
            'tglbataseditsuratpengantar',
            'tglapprovalbiayatitipanemkl',
            'gajisupir_nobukti',
            'invoice_nobukti',
            'modifiedby',
            'created_at',
            'updated_at',
            'statusgajisupir',
            'statusinvoice',


        ], $querysuratpengantar);



        return DB::table($tempsuratpengantar)->from(DB::raw("$tempsuratpengantar as suratpengantar"))->select(
            DB::raw(
                "suratpengantar.id,
                suratpengantar.nobukti,
                suratpengantar.jobtrucking,
                suratpengantar.tglbukti,
                suratpengantar.nosp,
                suratpengantar.tglsp,
                suratpengantar.nojob,
                pelanggan.namapelanggan as pelanggan_id,
                suratpengantar.keterangan,
                kotadari.keterangan as dari_id,
                kotasampai.keterangan as sampai_id,
                suratpengantar.penyesuaian,
                suratpengantar.gajisupir,
                suratpengantar.jarak,
                agen.namaagen as agen_id,
                'jenisorder.keterangan as jenisorder_id',
                'container.keterangan as container_id',
                suratpengantar.nocont,
                suratpengantar.noseal,
                suratpengantar.omset,
                suratpengantar.nominalperalihan,
                suratpengantar.totalomset,
                'statuscontainer.keterangan as statuscontainer_id',
                suratpengantar.gudang,
                'trado.kodetrado as trado_id',
                'supir.namasupir as supir_id',
                'gandengan.keterangan as gandengan_id',
                'statuslongtrip.memo as statuslongtrip',
                'statusperalihan.memo as statusperalihan',
                'statusritasiomset.memo as statusritasiomset',
                'statusapprovaleditsuratpengantar.memo as statusapprovaleditsuratpengantar',
                'statusapprovalbiayatitipanemkl.memo as statusapprovalbiayatitipanemkl',
                'tarif.tujuan as tarif_id',
                'mandortrado.namamandor as mandortrado_id',
                'mandorsupir.namamandor as mandorsupir_id',
                'statusgudangsama.memo as statusgudangsama',
                'statusbatalmuat.memo as statusbatalmuat',
                suratpengantar.userapprovaleditsuratpengantar,
                suratpengantar.userapprovalbiayatitipanemkl,
                suratpengantar.tglapprovaleditsuratpengantar,
                suratpengantar.tglbataseditsuratpengantar,
                suratpengantar.tglapprovalbiayatitipanemkl,
                suratpengantar.gajisupir_nobukti,
                suratpengantar.invoice_nobukti,
                suratpengantar.modifiedby,
                suratpengantar.created_at,
                suratpengantar.updated_at,
                'statusgajisupir.memo as statusgajisupir',
                'statusinvoice.memo as statusinvoice'
                "

            )

        )
            ->leftJoin('pelanggan', 'suratpengantar.pelanggan_id', 'pelanggan.id')
            ->leftJoin('kota as kotadari', 'kotadari.id', '=', 'suratpengantar.dari_id')
            ->leftJoin('kota as kotasampai', 'kotasampai.id', '=', 'suratpengantar.sampai_id')
            ->leftJoin('agen', 'suratpengantar.agen_id', 'agen.id')
            ->leftJoin('jenisorder', 'suratpengantar.jenisorder_id', 'jenisorder.id')
            ->leftJoin('container', 'suratpengantar.container_id', 'container.id')
            ->leftJoin('statuscontainer', 'suratpengantar.statuscontainer_id', 'statuscontainer.id')
            ->leftJoin('trado', 'suratpengantar.trado_id', 'trado.id')
            ->leftJoin('supir', 'suratpengantar.supir_id', 'supir.id')
            ->leftJoin('gandengan', 'suratpengantar.gandengan_id', 'gandengan.id')
            ->leftJoin('parameter as statuslongtrip', 'suratpengantar.statuslongtrip', 'statuslongtrip.id')
            ->leftJoin('parameter as statusperalihan', 'suratpengantar.statusperalihan', 'statusperalihan.id')
            ->leftJoin('parameter as statusritasiomset', 'suratpengantar.statusritasiomset', 'statusritasiomset.id')
            ->leftJoin('parameter as statusgudangsama', 'suratpengantar.statusgudangsama', 'statusgudangsama.id')
            ->leftJoin('parameter as statusbatalmuat', 'suratpengantar.statusbatalmuat', 'statusbatalmuat.id')
            ->leftJoin('parameter as statusapprovaleditsuratpengantar', 'suratpengantar.statusapprovaleditsuratpengantar', 'statusapprovaleditsuratpengantar.id')
            ->leftJoin('parameter as statusapprovalbiayatitipanemkl', 'suratpengantar.statusapprovalbiayatitipanemkl', 'statusapprovalbiayatitipanemkl.id')
            ->leftJoin('parameter as statusgajisupir', 'suratpengantar.statusgajisupir', 'statusgajisupir.id')
            ->leftJoin('parameter as statusinvoice', 'suratpengantar.statusinvoice', 'statusinvoice.id')
            ->leftJoin('mandor as mandortrado', 'suratpengantar.mandortrado_id', 'mandortrado.id')
            ->leftJoin('mandor as mandorsupir', 'suratpengantar.mandorsupir_id', 'mandorsupir.id')
            ->leftJoin(DB::raw("gajisupirdetail as b with (readuncommitted)"), 'suratpengantar.nobukti', 'b.suratpengantar_nobukti')
            ->leftJoin(DB::raw("invoicedetail as c with (readuncommitted)"), 'suratpengantar.jobtrucking', 'c.orderantrucking_nobukti')
            ->leftJoin('tarif', 'suratpengantar.tarif_id', 'tarif.id');
    }

    public function getpelabuhan($id)
    {
        $data = DB::table('parameter')
            ->from(DB::raw("parameter with (readuncommitted)"))
            ->select(
                'text as id'
            )
            ->where('grp', '=', 'PELABUHAN CABANG')
            ->where('subgrp', '=', 'PELABUHAN CABANG')
            ->where('text', '=', $id)
            ->first();

        if (isset($data)) {
            $kondisi = ['status' => '0'];
        } else {
            $kondisi = ['status' => '1'];
        }
        return $kondisi;
    }


    public function getHistory()
    {
        $this->setRequestParameters();
        $query = DB::table($this->table)->select(
            'suratpengantar.id',
            'suratpengantar.jobtrucking',
            'suratpengantar.nobukti',
            'suratpengantar.tglbukti',
            'suratpengantar.nosp',
            'suratpengantar.tglsp',
            'suratpengantar.nojob',
            'pelanggan.namapelanggan as pelanggan_id',
            'suratpengantar.keterangan',
            'kotadari.keterangan as dari_id',
            'kotasampai.keterangan as sampai_id',
            'suratpengantar.gajisupir',
            'suratpengantar.jarak',
            'agen.namaagen as agen_id',
            'jenisorder.keterangan as jenisorder_id',
            'container.keterangan as container_id',
            'suratpengantar.nocont',
            'suratpengantar.noseal',
            'statuscontainer.keterangan as statuscontainer_id',
            'suratpengantar.gudang',
            'trado.kodetrado as trado_id',
            'supir.namasupir as supir_id',
            'gandengan.keterangan as gandengan_id',
            'statuslongtrip.memo as statuslongtrip',
            'statusperalihan.memo as statusperalihan',
            'statusritasiomset.memo as statusritasiomset',
            'tarif.tujuan as tarif_id',
            'mandortrado.namamandor as mandortrado_id',
            'mandorsupir.namamandor as mandorsupir_id',
            'statusgudangsama.memo as statusgudangsama',
            'statusbatalmuat.memo as statusbatalmuat',
            'suratpengantar.modifiedby',
            'suratpengantar.created_at',
            'suratpengantar.updated_at'

        )

            ->leftJoin('pelanggan', 'suratpengantar.pelanggan_id', 'pelanggan.id')
            ->leftJoin('kota as kotadari', 'kotadari.id', '=', 'suratpengantar.dari_id')
            ->leftJoin('kota as kotasampai', 'kotasampai.id', '=', 'suratpengantar.sampai_id')
            ->leftJoin('agen', 'suratpengantar.agen_id', 'agen.id')
            ->leftJoin('jenisorder', 'suratpengantar.jenisorder_id', 'jenisorder.id')
            ->leftJoin('container', 'suratpengantar.container_id', 'container.id')
            ->leftJoin('statuscontainer', 'suratpengantar.statuscontainer_id', 'statuscontainer.id')
            ->leftJoin('trado', 'suratpengantar.trado_id', 'trado.id')
            ->leftJoin('supir', 'suratpengantar.supir_id', 'supir.id')
            ->leftJoin('gandengan', 'suratpengantar.gandengan_id', 'gandengan.id')
            ->leftJoin('parameter as statuslongtrip', 'suratpengantar.statuslongtrip', 'statuslongtrip.id')
            ->leftJoin('parameter as statusperalihan', 'suratpengantar.statusperalihan', 'statusperalihan.id')
            ->leftJoin('parameter as statusritasiomset', 'suratpengantar.statusritasiomset', 'statusritasiomset.id')
            ->leftJoin('parameter as statusgudangsama', 'suratpengantar.statusgudangsama', 'statusgudangsama.id')
            ->leftJoin('parameter as statusbatalmuat', 'suratpengantar.statusbatalmuat', 'statusbatalmuat.id')
            ->leftJoin('mandor as mandortrado', 'suratpengantar.mandortrado_id', 'mandortrado.id')
            ->leftJoin('mandor as mandorsupir', 'suratpengantar.mandorsupir_id', 'mandorsupir.id')
            ->leftJoin('tarif', 'suratpengantar.tarif_id', 'tarif.id')
            ->where('suratpengantar.tglbukti', ">", date('Y-m-d', strtotime('- 30 days')))
            ->where('suratpengantar.tglbukti', "<=", date('Y-m-d', strtotime('now')))
            ->where('suratpengantar.supir_id', request()->supir_id);

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }
    public function getListTrip()
    {
        $this->setRequestParameters();
        $query = DB::table($this->table)->select(
            'suratpengantar.id',
            'suratpengantar.jobtrucking',
            'suratpengantar.nobukti',
            'suratpengantar.tglbukti',
            'suratpengantar.nosp',
            'suratpengantar.tglsp',
            'suratpengantar.nojob',
            'pelanggan.namapelanggan as pelanggan_id',
            'suratpengantar.keterangan',
            'kotadari.keterangan as dari_id',
            'kotasampai.keterangan as sampai_id',
            'suratpengantar.gajisupir',
            'suratpengantar.jarak',
            'agen.namaagen as agen_id',
            'jenisorder.keterangan as jenisorder_id',
            'container.keterangan as container_id',
            'suratpengantar.nocont',
            'suratpengantar.noseal',
            'statuscontainer.keterangan as statuscontainer_id',
            'suratpengantar.gudang',
            'trado.kodetrado as trado_id',
            'supir.namasupir as supir_id',
            'gandengan.keterangan as gandengan_id',
            'statuslongtrip.memo as statuslongtrip',
            'statusperalihan.memo as statusperalihan',
            'statusritasiomset.memo as statusritasiomset',
            'tarif.tujuan as tarif_id',
            'mandortrado.namamandor as mandortrado_id',
            'mandorsupir.namamandor as mandorsupir_id',
            'statusgudangsama.memo as statusgudangsama',
            'statusbatalmuat.memo as statusbatalmuat',
            'suratpengantar.modifiedby',
            'suratpengantar.created_at',
            'suratpengantar.updated_at'

        )

            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
            ->leftJoin('pelanggan', 'suratpengantar.pelanggan_id', 'pelanggan.id')
            ->leftJoin('kota as kotadari', 'kotadari.id', '=', 'suratpengantar.dari_id')
            ->leftJoin('kota as kotasampai', 'kotasampai.id', '=', 'suratpengantar.sampai_id')
            ->leftJoin('agen', 'suratpengantar.agen_id', 'agen.id')
            ->leftJoin('jenisorder', 'suratpengantar.jenisorder_id', 'jenisorder.id')
            ->leftJoin('container', 'suratpengantar.container_id', 'container.id')
            ->leftJoin('statuscontainer', 'suratpengantar.statuscontainer_id', 'statuscontainer.id')
            ->leftJoin('trado', 'suratpengantar.trado_id', 'trado.id')
            ->leftJoin('supir', 'suratpengantar.supir_id', 'supir.id')
            ->leftJoin('gandengan', 'suratpengantar.gandengan_id', 'gandengan.id')
            ->leftJoin('parameter as statuslongtrip', 'suratpengantar.statuslongtrip', 'statuslongtrip.id')
            ->leftJoin('parameter as statusperalihan', 'suratpengantar.statusperalihan', 'statusperalihan.id')
            ->leftJoin('parameter as statusritasiomset', 'suratpengantar.statusritasiomset', 'statusritasiomset.id')
            ->leftJoin('parameter as statusgudangsama', 'suratpengantar.statusgudangsama', 'statusgudangsama.id')
            ->leftJoin('parameter as statusbatalmuat', 'suratpengantar.statusbatalmuat', 'statusbatalmuat.id')
            ->leftJoin('mandor as mandortrado', 'suratpengantar.mandortrado_id', 'mandortrado.id')
            ->leftJoin('mandor as mandorsupir', 'suratpengantar.mandorsupir_id', 'mandorsupir.id')
            ->leftJoin('tarif', 'suratpengantar.tarif_id', 'tarif.id')
            ->orderBy('suratpengantar.tglbukti', 'desc');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }


    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('jobtrucking', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('nosp', 50)->nullable();
            $table->date('tglsp')->nullable();
            $table->string('nojob', 100)->nullable();
            $table->string('pelanggan_id')->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('dari_id')->nullable();
            $table->string('sampai_id')->nullable();
            $table->string('penyesuaian')->nullable();
            $table->float('gajisupir')->nullable();
            $table->decimal('jarak')->nullable();
            $table->string('agen_id')->nullable();
            $table->string('jenisorder_id')->nullable();
            $table->string('container_id')->nullable();
            $table->string('nocont', 50)->nullable();
            $table->string('noseal', 50)->nullable();
            $table->float('omset')->nullable();
            $table->float('nominalperalihan')->nullable();
            $table->float('totalomset')->nullable();
            $table->string('statuscontainer_id')->nullable();
            $table->string('gudang')->nullable();
            $table->string('trado_id')->nullable();
            $table->string('supir_id')->nullable();
            $table->string('gandengan_id')->nullable();
            $table->longText('statuslongtrip')->nullable();
            $table->longText('statusperalihan')->nullable();
            $table->longText('statusritasiomset')->nullable();
            $table->longText('statusapprovaleditsuratpengantar')->nullable();
            $table->longText('statusapprovalbiayatitipanemkl')->nullable();
            $table->string('tarif_id')->nullable();
            $table->string('mandortrado_id')->nullable();
            $table->string('mandorsupir_id')->nullable();
            $table->longText('statusgudangsama')->nullable();
            $table->longText('statusbatalmuat')->nullable();
            $table->string('userapprovaleditsuratpengantar')->nullable();
            $table->string('userapprovalbiayatitipanemkl')->nullable();
            $table->date('tglapprovaleditsuratpengantar')->nullable();
            $table->date('tglbataseditsuratpengantar')->nullable();
            $table->date('tglapprovalbiayatitipanemkl')->nullable();
            $table->string('gajisupir_nobukti')->nullable();
            $table->longText('invoice_nobukti')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->longText('statusgajisupir')->nullable();
            $table->longText('statusinvoice')->nullable();

            $table->increments('position');
        });


        $this->setRequestParameters();
        $query = $this->selectColumns();
        // dd($query->get());

        if (request()->tgldariheader) {
            $query->whereBetween('suratpengantar.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        }

        $this->sort($query);


        $models = $this->filter($query);


        DB::table($temp)->insertUsing([
            'id', 'nobukti', 'jobtrucking', 'tglbukti', 'nosp', 'tglsp', 'nojob', 'pelanggan_id', 'keterangan', 'dari_id', 'sampai_id', 'penyesuaian', 'gajisupir', 'jarak', 'agen_id', 'jenisorder_id', 'container_id', 'nocont', 'noseal', 'omset', 'nominalperalihan', 'totalomset', 'statuscontainer_id', 'gudang', 'trado_id', 'supir_id', 'gandengan_id', 'statuslongtrip', 'statusperalihan', 'statusritasiomset', 'statusapprovaleditsuratpengantar', 'statusapprovalbiayatitipanemkl', 'tarif_id', 'mandortrado_id', 'mandorsupir_id', 'statusgudangsama', 'statusbatalmuat', 'userapprovaleditsuratpengantar', 'userapprovalbiayatitipanemkl', 'tglapprovaleditsuratpengantar', 'tglbataseditsuratpengantar', 'tglapprovalbiayatitipanemkl', 'gajisupir_nobukti', 'invoice_nobukti', 'modifiedby', 'created_at', 'updated_at', 'statusgajisupir', 'statusinvoice'
        ], $models);
        // dd('test');

        return  $temp;
    }

    public function getOrderanTrucking($id)
    {
        $data = DB::table('orderantrucking')->select('orderantrucking.*', 'container.keterangan as container', 'agen.namaagen as agen', 'jenisorder.keterangan as jenisorder', 'pelanggan.namapelanggan as pelanggan', 'tarif.tujuan as tarif')
            ->join('container', 'orderantrucking.container_id', 'container.id')
            ->join('agen', 'orderantrucking.agen_id', 'agen.id')
            ->join('jenisorder', 'orderantrucking.jenisorder_id', 'jenisorder.id')
            ->join('pelanggan', 'orderantrucking.pelanggan_id', 'pelanggan.id')
            ->join('tarif', 'orderantrucking.tarif_id', 'tarif.id')
            ->where('orderantrucking.id', $id)
            ->first();

        return $data;
    }


    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'pelanggan_id') {
            return $query->orderBy('pelanggan.namapelanggan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'dari_id') {
            return $query->orderBy('kotadari.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'sampai_id') {
            return $query->orderBy('kotasampai.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'statuscontainer_id') {
            return $query->orderBy('statuscontainer.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'container_id') {
            return $query->orderBy('container.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'trado_id') {
            return $query->orderBy('trado.kodetrado', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'supir_id') {
            return $query->orderBy('supir.namasupir', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'agen_id') {
            return $query->orderBy('agen.namaagen', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'jenisorder_id') {
            return $query->orderBy('jenisorder.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'tarif_id') {
            return $query->orderBy('tarif.tujuan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'mandortrado_id') {
            return $query->orderBy('mandortrado.namamandor', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'mandorsupir_id') {
            return $query->orderBy('mandorsupir.namamandor', $this->params['sortOrder']);
        } else {
            return $query->orderBy('suratpengantar.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'pelanggan_id') {
                                $query = $query->where('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'dari_id') {
                                $query = $query->where('kotadari.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'sampai_id') {
                                $query = $query->where('kotasampai.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'statuscontainer_id') {
                                $query = $query->where('statuscontainer.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'container_id') {
                                $query = $query->where('container.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'trado_id') {
                                $query = $query->where('trado.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'supir_id') {
                                $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'agen_id') {
                                $query = $query->where('agen.namaagen', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'gandengan_id') {
                                $query = $query->where('gandengan.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'jenisorder_id') {
                                $query = $query->where('jenisorder.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tarif_id') {
                                $query = $query->where('tarif.tujuan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'mandortrado_id') {
                                $query = $query->where('mandortrado.namamandor', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'mandorsupir_id') {
                                $query = $query->where('mandorsupir.namamandor', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'statuslongtrip') {
                                $query = $query->where('statuslongtrip.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusperalihan') {
                                $query = $query->where('statusperalihan.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusritasiomset') {
                                $query = $query->where('statusritasiomset.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusgudangsama') {
                                $query = $query->where('statusgudangsama.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusbatalmuat') {
                                $query = $query->where('statusbatalmuat.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusgajisupir') {
                                $query = $query->where('statusgajisupir.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusinvoice') {
                                $query = $query->where('statusinvoice.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusapprovaleditsuratpengantar') {
                                $query = $query->where('statusapprovaleditsuratpengantar.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusapprovalbiayatitipanemkl') {
                                $query = $query->where('statusapprovalbiayatitipanemkl.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'gajisupir' || $filters['field'] == 'jarak' || $filters['field'] == 'omset' || $filters['field'] == 'nominalperalihan' || $filters['field'] == 'totalomset') {
                                $query = $query->whereRaw("format(suratpengantar." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglsp') {
                                $query = $query->whereRaw("format(suratpengantar." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(suratpengantar." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw("suratpengantar.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'pelanggan_id') {
                                    $query = $query->orWhere('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'dari_id') {
                                    $query = $query->orWhere('kotadari.keterangan', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'sampai_id') {
                                    $query = $query->orWhere('kotasampai.keterangan', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'statuscontainer_id') {
                                    $query = $query->orWhere('statuscontainer.keterangan', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'container_id') {
                                    $query = $query->orWhere('container.keterangan', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'trado_id') {
                                    $query = $query->orWhere('trado.keterangan', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'supir_id') {
                                    $query = $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'agen_id') {
                                    $query = $query->orWhere('agen.namaagen', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'gandengan_id') {
                                    $query = $query->orWhere('gandengan.keterangan', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'jenisorder_id') {
                                    $query = $query->orWhere('jenisorder.keterangan', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'tarif_id') {
                                    $query = $query->orWhere('tarif.tujuan', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'mandortrado_id') {
                                    $query = $query->orWhere('mandortrado.namamandor', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'mandorsupir_id') {
                                    $query = $query->orWhere('mandorsupir.namamandor', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'statuslongtrip') {
                                    $query = $query->orWhere('statuslongtrip.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statusgajisupir') {
                                    $query = $query->Orwhere('statusgajisupir.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statusinvoice') {
                                    $query = $query->Orwhere('statusinvoice.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statusperalihan') {
                                    $query = $query->orWhere('statusperalihan.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statusritasiomset') {
                                    $query = $query->orWhere('statusritasiomset.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statusgudangsama') {
                                    $query = $query->orWhere('statusgudangsama.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statusbatalmuat') {
                                    $query = $query->orWhere('statusbatalmuat.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statusapprovaleditsuratpengantar') {
                                    $query = $query->orWhere('statusapprovaleditsuratpengantar.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statusapprovalbiayatitipanemkl') {
                                    $query = $query->orWhere('statusapprovalbiayatitipanemkl.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'gajisupir' || $filters['field'] == 'jarak' || $filters['field'] == 'omset' || $filters['field'] == 'nominalperalihan' || $filters['field'] == 'totalomset') {
                                    $query = $query->orWhereRaw("format(suratpengantar." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglsp') {
                                    $query = $query->orWhereRaw("format(suratpengantar." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(suratpengantar." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else {
                                    // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw("suratpengantar.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                                }
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

    public function processStore(array $data): SuratPengantar
    {
        $inputTripMandor = $data['inputtripmandor'] ?? 0;
        $group = 'SURAT PENGANTAR';
        $subGroup = 'SURAT PENGANTAR';

        $format = DB::table('parameter')
            ->where('grp', $group)
            ->where('subgrp', $subGroup)
            ->first();

        $suratPengantar = new SuratPengantar();

        $statusTidakBolehEditTujuan = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS EDIT TUJUAN')->where('text', '=', 'TIDAK BOLEH EDIT TUJUAN')->first();
        $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        $upahsupir = UpahSupir::where('id', $data['upah_id'])->first();

        $upahsupirRincian = UpahSupirRincian::where('upahsupir_id', $data['upah_id'])->where('container_id', $data['container_id'])->where('statuscontainer_id', $data['statuscontainer_id'])->first();

        $trado = Trado::find($data['trado_id']);
        $supir = Supir::find($data['supir_id']);
        if ($inputTripMandor == 0) {
            $orderanTrucking = OrderanTrucking::where('nobukti', $data['jobtrucking'])->first();

            $tarifrincian = TarifRincian::from(DB::raw("tarifrincian with (readuncommitted)"))->where('tarif_id', $orderanTrucking->tarif_id)->where('container_id', $orderanTrucking->container_id)->first();

            $suratPengantar->jobtrucking = $data['jobtrucking'];
            $suratPengantar->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
            $suratPengantar->pelanggan_id = $orderanTrucking->pelanggan_id;
            $suratPengantar->keterangan = $data['keterangan'] ?? '';
            $suratPengantar->nourutorder = $data['nourutorder'] ?? 1;
            $suratPengantar->upah_id = $upahsupir->id;
            $suratPengantar->dari_id = $data['dari_id'];
            $suratPengantar->sampai_id = $data['sampai_id'];
            $suratPengantar->container_id = $orderanTrucking->container_id;
            $suratPengantar->nocont = $orderanTrucking->nocont;
            $suratPengantar->nocont2 = $orderanTrucking->nocont2 ?? '';
            $suratPengantar->noseal = $orderanTrucking->noseal;
            $suratPengantar->noseal2 = $orderanTrucking->noseal2 ?? '';
            $suratPengantar->statuscontainer_id = $data['statuscontainer_id'];
            $suratPengantar->statusgandengan = $data['statusgandengan'];
            $suratPengantar->trado_id = $data['trado_id'];
            $suratPengantar->supir_id = $data['supir_id'];
            $suratPengantar->gandengan_id = $data['gandengan_id'] ?? 0;
            $suratPengantar->nojob = $orderanTrucking->nojobemkl;
            $suratPengantar->nojob2 = $orderanTrucking->nojobemkl2 ?? '';
            $suratPengantar->statuslongtrip = $data['statuslongtrip'];
            $suratPengantar->omset = $tarifrincian->nominal;
            $suratPengantar->gajisupir = $upahsupirRincian->nominalsupir;
            $suratPengantar->gajikenek = $upahsupirRincian->nominalkenek;
            $suratPengantar->agen_id = $orderanTrucking->agen_id;
            $suratPengantar->jenisorder_id = $orderanTrucking->jenisorder_id;
            $suratPengantar->penyesuaian = $data['penyesuaian'];
            $suratPengantar->statusperalihan = $data['statusperalihan'];
            $suratPengantar->tarif_id = $orderanTrucking->tarif_id;
            $suratPengantar->nominalperalihan = $data['nominalperalihan'] ?? 0;
            $persentaseperalihan = 0;
            if (array_key_exists('nominalperalihan', $data)) {

                if ($data['nominalperalihan'] != 0) {
                    $persentaseperalihan = $data['nominalperalihan'] / $tarifrincian->nominal;
                }
            }

            $suratPengantar->persentaseperalihan = $persentaseperalihan;
            $suratPengantar->discount = $persentaseperalihan;
            $suratPengantar->totalomset = $tarifrincian->nominal - ($tarifrincian->nominal * ($persentaseperalihan / 100));

            $suratPengantar->biayatambahan_id = $data['biayatambahan_id'] ?? 0;
            $suratPengantar->nosp = $data['nosp'];
            $suratPengantar->tglsp = date('Y-m-d', strtotime($data['tglbukti']));
            $suratPengantar->komisisupir = $upahsupirRincian->nominalkomisi;
            $suratPengantar->tolsupir = $upahsupirRincian->nominaltol;
            $statuscontainer_id = $data['statuscontainer_id'] ?? 0;
            $idfullempty = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                ->select(
                    'a.text as id'
                )
                ->where('grp', 'STATUS CONTAINER')
                ->where('subgrp', 'STATUS CONTAINER FULL EMPTY')
                ->first()->id ?? 0;

            if ($statuscontainer_id == $idfullempty) {
                $suratPengantar->jarak = $upahsupir->jarakfullempty;
            } else {
                $suratPengantar->jarak = $upahsupir->jarak;
            }
            $suratPengantar->nosptagihlain = $data['nosptagihlain'] ?? '';
            $suratPengantar->liter = $upahsupirRincian->liter ?? 0;
            $suratPengantar->qtyton = $data['qtyton'] ?? 0;
            $suratPengantar->totalton = $tarifrincian->nominal * $data['qtyton'];
            $suratPengantar->mandorsupir_id = $supir->mandor_id;
            $suratPengantar->mandortrado_id = $trado->mandor_id;
            $suratPengantar->statusgudangsama = $data['statusgudangsama'];
            $suratPengantar->statusbatalmuat = $data['statusbatalmuat'];
            $suratPengantar->gudang = $data['gudang'];
            $suratPengantar->statusapprovaleditsuratpengantar = $statusNonApproval->id;
            $suratPengantar->modifiedby = auth('api')->user()->name;
            $suratPengantar->info = html_entity_decode(request()->info);
            $suratPengantar->statusformat = $format->id;

            $suratPengantar->nobukti = (new RunningNumberService)->get($group, $subGroup, $suratPengantar->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));


            $suratPengantar->statusedittujuan = $statusTidakBolehEditTujuan->id;
        } else {
            $orderanTrucking = OrderanTrucking::where('nobukti', $data['jobtrucking'])->first();
            if (!isset($orderanTrucking)) {
                $orderanTrucking = DB::table("saldoorderantrucking")->from(DB::raw("saldoorderantrucking with (readuncommitted)"))->where('nobukti', $data['jobtrucking'])->first();
            }
            $suratPengantar->jobtrucking = $data['jobtrucking'];
            $suratPengantar->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
            $suratPengantar->pelanggan_id = $data['pelanggan_id'];
            $suratPengantar->upah_id = $data['upah_id'];
            $suratPengantar->dari_id = $data['dari_id'] ?? '';
            $suratPengantar->sampai_id = $data['sampai_id'] ?? '';
            $suratPengantar->zonadari_id = $data['zonadari_id'] ?? '';
            $suratPengantar->zonasampai_id = $data['zonasampai_id'] ?? '';
            $suratPengantar->container_id = $data['container_id'];
            $suratPengantar->statuscontainer_id = $data['statuscontainer_id'];
            $suratPengantar->statusgandengan = $data['statusgandengan'];
            $suratPengantar->trado_id = $data['trado_id'];
            $suratPengantar->supir_id = $data['supir_id'];
            $suratPengantar->gandengan_id = $data['gandengan_id'] ?? 0;
            $suratPengantar->gandenganasal_id = $data['gandenganasal_id'] ?? 0;
            $suratPengantar->omset = $data['omset'] ?? 0;
            $suratPengantar->gajisupir = $data['gajisupir'];
            $suratPengantar->gajikenek = $data['gajikenek'];
            $suratPengantar->agen_id = $data['agen_id'];
            $suratPengantar->jenisorder_id = $data['jenisorder_id'];
            $suratPengantar->statusperalihan = $data['statusperalihan'];
            $suratPengantar->statuslongtrip = $data['statuslongtrip'];
            $suratPengantar->statusgudangsama = $data['statusgudangsama'];
            $suratPengantar->statusupahzona = $data['statusupahzona'];
            $suratPengantar->tarif_id = $data['tarif_id'] ?? '';
            $suratPengantar->komisisupir = $upahsupirRincian->nominalkomisi;
            $suratPengantar->tolsupir = $upahsupirRincian->nominaltol;
            $statuscontainer_id = $data['statuscontainer_id'] ?? 0;
            $idfullempty = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                ->select(
                    'a.text as id'
                )
                ->where('grp', 'STATUS CONTAINER')
                ->where('subgrp', 'STATUS CONTAINER FULL EMPTY')
                ->first()->id ?? 0;

            if ($statuscontainer_id == $idfullempty) {
                $suratPengantar->jarak = $upahsupir->jarakfullempty;
            } else {
                $suratPengantar->jarak = $upahsupir->jarak;
            }
            $suratPengantar->liter = $upahsupirRincian->liter ?? 0;
            $suratPengantar->nocont = $orderanTrucking->nocont ?? '';
            $suratPengantar->nocont2 = $orderanTrucking->nocont2 ?? '';
            $suratPengantar->noseal = $orderanTrucking->noseal ?? '';
            $suratPengantar->noseal2 = $orderanTrucking->noseal2 ?? '';
            $suratPengantar->nojob = $orderanTrucking->nojobemkl ?? '';
            $suratPengantar->nojob2 = $orderanTrucking->nojobemkl2 ?? '';
            $suratPengantar->totalomset = $data['totalomset'] ?? 0;
            $suratPengantar->penyesuaian = $data['penyesuaian'] ?? '';
            $suratPengantar->tglsp = date('Y-m-d', strtotime($data['tglbukti']));
            $suratPengantar->approvalbukatanggal_id = $data['approvalbukatanggal_id'] ?? '';
            $suratPengantar->statusapprovaleditsuratpengantar = $statusNonApproval->id;
            $suratPengantar->statusgudangsama = $data['statusgudangsama'];
            $suratPengantar->statusbatalmuat = $data['statusbatalmuat'];
            $suratPengantar->statusedittujuan = $statusTidakBolehEditTujuan->id;
            $suratPengantar->gudang = $data['gudang'];
            $suratPengantar->mandorsupir_id = $supir->mandor_id;
            $suratPengantar->mandortrado_id = $trado->mandor_id;
            $suratPengantar->lokasibongkarmuat = $data['lokasibongkarmuat'];
            $suratPengantar->modifiedby = auth('api')->user()->name;
            $suratPengantar->info = html_entity_decode(request()->info);
            $suratPengantar->statusformat = $format->id;
            $suratPengantar->tglbataseditsuratpengantar = $data['tglbataseditsuratpengantar'];
            $suratPengantar->nobukti_tripasal = $data['nobukti_tripasal'];
            $suratPengantar->statusapprovalbiayatitipanemkl = $statusNonApproval->id;
            $suratPengantar->nobukti = (new RunningNumberService)->get($group, $subGroup, $suratPengantar->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
        }

        if (!$suratPengantar->save()) {
            throw new \Exception('Error storing surat pengantar.');
        }

        $suratPengantarLogTrail = (new LogTrail())->processStore([
            'namatabel' => $suratPengantar->getTable(),
            'postingdari' => 'ENTRY SURAT PENGANTAR',
            'idtrans' => $suratPengantar->id,
            'nobuktitrans' => $suratPengantar->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $suratPengantar->toArray(),
        ]);

        if ($data['nominal']) {
            if ($data['nominal'][0] != 0) {
                $suratPengantarBiayaTambahans = [];
                for ($i = 0; $i < count($data['nominal']); $i++) {
                    $suratPengantarBiayaTambahan = (new SuratPengantarBiayaTambahan())->processStore($suratPengantar, [
                        'keteranganbiaya' => $data['keterangan_detail'][$i],
                        'nominal' => $data['nominal'][$i],
                        'nominaltagih' => $data['nominalTagih'][$i]
                    ]);
                    $suratPengantarBiayaTambahans[] = $suratPengantarBiayaTambahan->toArray();
                }
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($suratPengantarBiayaTambahan->getTable()),
                    'postingdari' => 'ENTRY SURAT PENGANTAR BIAYA TAMBAHAN',
                    'idtrans' =>  $suratPengantarLogTrail->id,
                    'nobuktitrans' => $suratPengantar->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $suratPengantarBiayaTambahans,
                    'modifiedby' => auth('api')->user()->user,
                ]);
            }
        }
        return $suratPengantar;
    }
    public function processUpdate(SuratPengantar $suratPengantar, array $data): SuratPengantar
    {
        $prosesLain = $data['proseslain'] ?? 0;
        $orderanTrucking = OrderanTrucking::where('nobukti', $data['jobtrucking'])->first();
        if (!isset($orderanTrucking)) {
            $orderanTrucking = DB::table("saldoorderantrucking")->from(DB::raw("saldoorderantrucking"))->where('nobukti', $data['jobtrucking'])->first();
        }
        $isKomisiReadonly = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'SURAT PENGANTAR')->where('subgrp', 'KOMISI')->first();

        $statusTidakBolehEditTujuan = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS EDIT TUJUAN')->where('text', '=', 'TIDAK BOLEH EDIT TUJUAN')->first();

        $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        if ($prosesLain == 0) {
            $trado = Trado::find($data['trado_id']);
            $supir = Supir::find($data['supir_id']);
            $edittripmandor = $data['edittripmandor'] ?? 0;
            $tarif = TarifRincian::where('tarif_id', $data['tarif_id'])->where('container_id', $orderanTrucking->container_id)->first();
            $tarifNominal = $tarif->nominal ?? 0;
            $upahsupir = UpahSupir::where('id', $data['upah_id'])->first();

            $getZona = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS UPAH ZONA')->where('text', 'UPAH ZONA')->first();
            $upahZona = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))->where('id', $data['upah_id'])->first();

            $data['zonadari_id'] = '';
            $data['zonasampai_id'] = '';

            if ($data['statusupahzona'] == $getZona->id) {
                $data['zonadari_id'] = $upahZona->zonadari_id;
                $data['zonasampai_id'] = $upahZona->zonasampai_id;
            }
            // return response($tarif,422);
            $upahsupirRincian = UpahSupirRincian::where('upahsupir_id', $upahsupir->id)->where('container_id', $data['container_id'])->where('statuscontainer_id', $data['statuscontainer_id'])->first();
            $params = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PENDAPATAN SUPIR')->where('subgrp', 'GAJI KENEK')->first();
            $komisi_gajisupir = $params->text;
            // if ($komisi_gajisupir == 'YA') {
            //     if (trim($isKomisiReadonly->text) == 'TIDAK') {
            //         $nominalSupir = $upahsupirRincian->nominalsupir - $data['gajikenek'];
            //     } else {
            //         $nominalSupir = $upahsupirRincian->nominalsupir - $upahsupirRincian->nominalkenek;
            //     }
            // } else {
            $nominalSupir = $upahsupirRincian->nominalsupir;
            // }
            $suratPengantar->jobtrucking = $data['jobtrucking'];
            $suratPengantar->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
            $suratPengantar->pelanggan_id = $orderanTrucking->pelanggan_id;
            $suratPengantar->keterangan = $data['keterangan'] ?? '';
            $suratPengantar->nourutorder = $data['nourutorder'] ?? 1;
            $suratPengantar->upah_id = $upahsupir->id;
            $suratPengantar->dari_id = $data['dari_id'];
            $suratPengantar->sampai_id = $data['sampai_id'];
            $suratPengantar->zonadari_id = $data['zonadari_id'] ?? '';
            $suratPengantar->zonasampai_id = $data['zonasampai_id'] ?? '';
            $suratPengantar->container_id = $orderanTrucking->container_id;
            $suratPengantar->nocont = $orderanTrucking->nocont;
            $suratPengantar->nocont2 = $orderanTrucking->nocont2 ?? '';
            $suratPengantar->statuscontainer_id = $data['statuscontainer_id'];
            $suratPengantar->statusgandengan = $data['statusgandengan'];
            $suratPengantar->trado_id = $data['trado_id'];
            $suratPengantar->supir_id = $data['supir_id'];
            $suratPengantar->gandengan_id = $data['gandengan_id'] ?? 0;
            $suratPengantar->nojob = $orderanTrucking->nojobemkl;
            $suratPengantar->nojob2 = $orderanTrucking->nojobemkl2 ?? '';
            $suratPengantar->noseal = $orderanTrucking->noseal;
            $suratPengantar->noseal2 = $orderanTrucking->noseal2 ?? '';
            $suratPengantar->statuslongtrip = $data['statuslongtrip'];
            $suratPengantar->omset = $tarifNominal;
            $suratPengantar->gajisupir = $nominalSupir;
            $suratPengantar->agen_id = $orderanTrucking->agen_id;
            $suratPengantar->penyesuaian = $data['penyesuaian'];
            $suratPengantar->jenisorder_id = $orderanTrucking->jenisorder_id;
            $suratPengantar->statusperalihan = $data['statusperalihan'];
            $suratPengantar->statusupahzona = $data['statusupahzona'];
            $suratPengantar->tarif_id = $data['tarif_id'] ?? '';
            $nominalPeralihan = 0;
            if ($data['persentaseperalihan'] != 0) {
                $nominalPeralihan = ($tarifNominal * ($data['persentaseperalihan'] / 100));
            }

            // if (trim($isKomisiReadonly->text) == 'TIDAK') {
            $suratPengantar->komisisupir = $data['komisisupir'];
            $suratPengantar->gajikenek = $data['gajikenek'];
            // } else {
            //     $suratPengantar->komisisupir = $upahsupirRincian->nominalkomisi;
            //     $suratPengantar->gajikenek = $upahsupirRincian->nominalkenek;
            // }
            $suratPengantar->nominalperalihan = $nominalPeralihan;
            $suratPengantar->persentaseperalihan = $data['persentaseperalihan'];
            $suratPengantar->discount = $data['persentaseperalihan'];
            $suratPengantar->totalomset = $tarifNominal - ($tarifNominal * ($data['persentaseperalihan'] / 100));
            $suratPengantar->biayatambahan_id = $data['biayatambahan_id'] ?? 0;
            $suratPengantar->nosp = $data['nosp'];
            $suratPengantar->tglsp = date('Y-m-d', strtotime($data['tglbukti']));
            $suratPengantar->tolsupir = $upahsupirRincian->nominaltol;
            $statuscontainer_id = $data['statuscontainer_id'] ?? 0;
            $idfullempty = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                ->select(
                    'a.text as id'
                )
                ->where('grp', 'STATUS CONTAINER')
                ->where('subgrp', 'STATUS CONTAINER FULL EMPTY')
                ->first()->id ?? 0;

            if ($statuscontainer_id == $idfullempty) {
                $suratPengantar->jarak = $upahsupir->jarakfullempty;
            } else {
                $suratPengantar->jarak = $upahsupir->jarak;
            }
            $suratPengantar->nosptagihlain = $data['nosptagihlain'] ?? '';
            $suratPengantar->liter = $upahsupirRincian->liter ?? 0;
            $suratPengantar->qtyton = $data['qtyton'] ?? 0;
            $suratPengantar->totalton = $tarifNominal * $data['qtyton'];
            $suratPengantar->mandorsupir_id = $supir->mandor_id;
            $suratPengantar->mandortrado_id = $trado->mandor_id;
            $suratPengantar->statusgudangsama = $data['statusgudangsama'];
            $suratPengantar->statusbatalmuat = $data['statusbatalmuat'];
            $suratPengantar->gudang = $data['gudang'];
            $suratPengantar->lokasibongkarmuat = $data['lokasibongkarmuat'];
            $suratPengantar->nobukti_tripasal = $data['nobukti_tripasal'];
            $suratPengantar->modifiedby = auth('api')->user()->name;
            $suratPengantar->info = html_entity_decode(request()->info);
            $suratPengantar->statusedittujuan = $statusTidakBolehEditTujuan->id;
            // $suratPengantar->statusapprovaleditsuratpengantar = $statusNonApproval->id;
            if (!$suratPengantar->save()) {
                throw new \Exception('Error edit surat pengantar.');
            }

            $suratPengantarLogTrail = (new LogTrail())->processStore([
                'namatabel' => strtoupper($suratPengantar->getTable()),
                'postingdari' => 'EDIT SURAT PENGANTAR',
                'idtrans' => $suratPengantar->id,
                'nobuktitrans' => $suratPengantar->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $suratPengantar->toArray(),
                'modifiedby' => auth('api')->user()->user
            ]);

            if ($edittripmandor == 0) {

                if ($data['keterangan_detail'][0] != '') {

                    // SuratPengantarBiayaTambahan::where('suratpengantar_id', $suratPengantar->id)->lockForUpdate()->delete();
                    $spbt = DB::table("suratpengantarbiayatambahan")->from(DB::raw("suratpengantarbiayatambahan"))->where('suratpengantar_id', $suratPengantar->id)->get();
                    $pluck = $spbt->pluck('id')->toArray();

                    // Find the difference between $dataIds and $cek
                    $commonElements = array_diff($pluck, $data['tambahan_id']);
                    foreach ($commonElements as $row) {
                        (new SuratPengantarBiayaTambahan())->processDestroy($row);
                    }
                    $suratPengantarBiayaTambahans = [];
                    for ($i = 0; $i < count($data['keterangan_detail']); $i++) {
                        if ($data['tambahan_id'][$i] != '') {
                            $suratPengantarBiayaTambahan = (new SuratPengantarBiayaTambahan())->processUpdate($data['tambahan_id'][$i], [
                                'keteranganbiaya' => $data['keterangan_detail'][$i],
                                'nominal' => $data['nominal'][$i],
                                'nominaltagih' => $data['nominalTagih'][$i]
                            ]);
                        } else {
                            $suratPengantarBiayaTambahan = (new SuratPengantarBiayaTambahan())->processStore($suratPengantar, [
                                'keteranganbiaya' => $data['keterangan_detail'][$i],
                                'nominal' => $data['nominal'][$i],
                                'nominaltagih' => $data['nominalTagih'][$i]
                            ]);
                        }
                        $suratPengantarBiayaTambahans[] = $suratPengantarBiayaTambahan->toArray();
                    }
                    (new LogTrail())->processStore([
                        'namatabel' => strtoupper($suratPengantarBiayaTambahan->getTable()),
                        'postingdari' => 'EDIT SURAT PENGANTAR BIAYA TAMBAHAN',
                        'idtrans' =>  $suratPengantarLogTrail->id,
                        'nobuktitrans' => $suratPengantar->nobukti,
                        'aksi' => 'EDIT',
                        'datajson' => $suratPengantarBiayaTambahans,
                        'modifiedby' => auth('api')->user()->user,
                    ]);
                } else {
                    $cekBiaya = SuratPengantarBiayaTambahan::where('suratpengantar_id', $suratPengantar->id)->first();
                    if ($cekBiaya != null) {
                        $tambahan = SuratPengantarBiayaTambahan::where('suratpengantar_id', $suratPengantar->id)->get();
                        SuratPengantarBiayaTambahan::where('suratpengantar_id', $suratPengantar->id)->lockForUpdate()->delete();
                        (new LogTrail())->processStore([
                            'namatabel' => 'SURATPENGANTARBIAYATAMBAHAN',
                            'postingdari' => 'DELETE SURAT PENGANTAR BIAYA TAMBAHAN',
                            'idtrans' =>  $suratPengantarLogTrail->id,
                            'nobuktitrans' => $suratPengantar->nobukti,
                            'aksi' => 'DELETE',
                            'datajson' => $tambahan->toArray(),
                            'modifiedby' => auth('api')->user()->user,
                        ]);
                    }
                }
            }
        } else {
            $jenisorderanmuatan = DB::table('parameter')->from(db::raw("parameter a  with (readuncommitted)"))->select('a.text as id')
                ->where('a.grp', 'JENIS ORDERAN MUATAN')
                ->where('a.subgrp', 'JENIS ORDERAN MUATAN')
                ->first()->id;

            $jenisorderanbongkaran = DB::table('parameter')->from(db::raw("parameter a  with (readuncommitted)"))->select('a.text as id')
                ->where('a.grp', 'JENIS ORDERAN BONGKARAN')
                ->where('a.subgrp', 'JENIS ORDERAN BONGKARAN')
                ->first()->id;

            $jenisorderanimport = DB::table('parameter')->from(db::raw("parameter a  with (readuncommitted)"))->select('a.text as id')
                ->where('a.grp', 'JENIS ORDERAN IMPORT')
                ->where('a.subgrp', 'JENIS ORDERAN IMPORT')
                ->first()->id;

            $jenisorderanexport = DB::table('parameter')->from(db::raw("parameter a  with (readuncommitted)"))->select('a.text as id')
                ->where('a.grp', 'JENIS ORDERAN EXPORT')
                ->where('a.subgrp', 'JENIS ORDERAN EXPORT')
                ->first()->id;

            $getTarif = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))
                ->select(db::raw("(case when isnull(tarifmuatan.id,0)<>0 and " . $jenisorderanmuatan . "=" . $data['jenisorder_id']  . " then isnull(tarifmuatan.id,0)  
            when isnull(tarifbongkaran.id,0)<>0 and " . $jenisorderanbongkaran . "=" . $data['jenisorder_id']  . "then isnull(tarifbongkaran.id,0)  
            when isnull(tarifimport.id,0)<>0 and " . $jenisorderanimport . "=" . $data['jenisorder_id']  . " then isnull(tarifimport.id,0)  
            when isnull(tarifexport.id,0)<>0 and " . $jenisorderanexport . "=" . $data['jenisorder_id']  . " then isnull(tarifexport.id,0)  
            else  isnull(tarif.id,0) end) as tarif_id"))

                ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'upahsupir.tarif_id', 'tarif.id')
                ->leftJoin(DB::raw("tarif as tarifmuatan with (readuncommitted)"), 'upahsupir.tarifmuatan_id', 'tarifmuatan.id')
                ->leftJoin(DB::raw("tarif as tarifbongkaran with (readuncommitted)"), 'upahsupir.tarifbongkaran_id', 'tarifbongkaran.id')
                ->leftJoin(DB::raw("tarif as tarifimport with (readuncommitted)"), 'upahsupir.tarifimport_id', 'tarifimport.id')
                ->leftJoin(DB::raw("tarif as tarifexport with (readuncommitted)"), 'upahsupir.tarifexport_id', 'tarifexport.id')
                ->where('upahsupir.id', $suratPengantar->upah_id)
                ->first();

            $upahsupirRincian = UpahSupirRincian::where('upahsupir_id', $suratPengantar->upah_id)->where('container_id', $data['container_id'])->where('statuscontainer_id', $suratPengantar->statuscontainer_id)->first();
            $tarif = TarifRincian::where('tarif_id', $getTarif->tarif_id)->where('container_id', $data['container_id'])->first();
            $params = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PENDAPATAN SUPIR')->where('subgrp', 'GAJI KENEK')->first();
            $komisi_gajisupir = $params->text;
            // if ($komisi_gajisupir == 'YA') {
            //     if (trim($isKomisiReadonly->text) == 'TIDAK') {
            //         $nominalSupir = $upahsupirRincian->nominalsupir - $suratPengantar->gajikenek;
            //     } else {
            //         $nominalSupir = $upahsupirRincian->nominalsupir - $upahsupirRincian->nominalkenek;
            //     }
            // } else {
            $nominalSupir = $upahsupirRincian->nominalsupir;
            // }
            $tarifNominal = $tarif->nominal ?? 0;

            $suratPengantar->pelanggan_id = $data['pelanggan_id'];
            $suratPengantar->container_id = $data['container_id'];
            $suratPengantar->tarif_id = $getTarif->tarif_id;
            $suratPengantar->nojob = $data['nojob'];
            $suratPengantar->nojob2 = $data['nojob2'] ?? '';
            $suratPengantar->nocont = $data['nocont'] ?? '';
            $suratPengantar->nocont2 = $data['nocont2'] ?? '';
            $suratPengantar->noseal = $data['noseal'] ?? '';
            $suratPengantar->noseal2 = $data['noseal2'] ?? '';
            $suratPengantar->agen_id = $data['agen_id'];
            $suratPengantar->jenisorder_id = $data['jenisorder_id'];
            $suratPengantar->gajisupir = $nominalSupir;
            // if (trim($isKomisiReadonly->text) == 'YA') {
            //     $suratPengantar->gajikenek = $upahsupirRincian->nominalkenek;
            //     $suratPengantar->komisisupir = $upahsupirRincian->nominalkomisi;
            // }
            $suratPengantar->tolsupir = $upahsupirRincian->nominaltol;
            $suratPengantar->liter = $upahsupirRincian->liter ?? 0;
            $suratPengantar->omset = $tarifNominal;
            $nominalPeralihan = 0;
            if ($suratPengantar->persentaseperalihan != 0) {
                $nominalPeralihan = ($tarifNominal * ($suratPengantar->persentaseperalihan / 100));
            }

            // $suratPengantar->mandorsupir_id = $supir->mandor_id;
            // $suratPengantar->mandortrado_id = $trado->mandor_id;
            $suratPengantar->nominalperalihan = $nominalPeralihan;
            $suratPengantar->persentaseperalihan = $suratPengantar->persentaseperalihan;
            $suratPengantar->totalomset = $tarifNominal - ($tarifNominal * ($suratPengantar->persentaseperalihan / 100));

            // $suratPengantar->tarif_id = $data['tarif_id'];

            if (!$suratPengantar->save()) {
                throw new \Exception('Error edit surat pengantar.');
            }
            $suratPengantarLogTrail = (new LogTrail())->processStore([
                'namatabel' => strtoupper($suratPengantar->getTable()),
                'postingdari' => $data['postingdari'] ?? 'EDIT SURAT PENGANTAR',
                'idtrans' => $suratPengantar->id,
                'nobuktitrans' => $suratPengantar->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $suratPengantar->toArray(),
                'modifiedby' => auth('api')->user()->user
            ]);
        }


        return $suratPengantar;
    }

    public function processDestroy($id): SuratPengantar
    {
        $suratPengantarBiayaTambahan = SuratPengantarBiayaTambahan::where('suratpengantar_id', $id)->get();

        $suratPengantar = new SuratPengantar();
        $suratPengantar = $suratPengantar->lockAndDestroy($id);

        if ($suratPengantar->dari_id == 1) {

            $cekSP = DB::table("orderantrucking")->from(DB::raw("orderantrucking with (readuncommitted)"))->where('nobukti', $suratPengantar->jobtrucking)->first();
            (new OrderanTrucking())->processDestroy($cekSP->id);
        }

        if ($suratPengantar->statusgudangsama == 204) {
            $cekSP = DB::table("orderantrucking")->from(DB::raw("orderantrucking with (readuncommitted)"))->where('nobukti', $suratPengantar->jobtrucking)->first();
            (new OrderanTrucking())->processDestroy($cekSP->id);
        }

        $suratPengantarLogTrail = (new LogTrail())->processStore([
            'namatabel' => $suratPengantar->getTable(),
            'postingdari' => 'DELETE SURAT PENGANTAR',
            'idtrans' => $suratPengantar->id,
            'nobuktitrans' => $suratPengantar->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $suratPengantar->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        if (count($suratPengantarBiayaTambahan->toArray()) > 0) {
            SuratPengantarBiayaTambahan::where('suratpengantar_id', $id)->delete();
            $tes = (new LogTrail())->processStore([
                'namatabel' => 'SURATPENGANTARBIAYATAMBAHAN',
                'postingdari' => 'DELETE SURAT PENGANTAR BIAYA TAMBAHAN',
                'idtrans' => $suratPengantarLogTrail['id'],
                'nobuktitrans' => $suratPengantar->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $suratPengantarBiayaTambahan->toArray(),
                'modifiedby' => auth('api')->user()->name
            ]);
        }


        return $suratPengantar;
    }

    public function getExport($dari, $sampai)
    {
        $this->setRequestParameters();

        $getParameter = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))
            ->select(
                'text as judul',
                DB::raw("'Laporan Surat Pengantar' as judulLaporan")
            )->where('grp', 'JUDULAN LAPORAN')->where('subgrp', 'JUDULAN LAPORAN')->first();

        $query = DB::table($this->table)->select(
            'suratpengantar.id',
            'suratpengantar.jobtrucking',
            'suratpengantar.nobukti',
            'suratpengantar.tglbukti',
            'suratpengantar.nosp',
            'suratpengantar.tglsp',
            'suratpengantar.nojob',
            'pelanggan.namapelanggan as pelanggan_id',
            'suratpengantar.keterangan',
            'kotadari.keterangan as dari_id',
            'kotasampai.keterangan as sampai_id',
            'suratpengantar.gajisupir',
            'suratpengantar.jarak',
            'agen.namaagen as agen_id',
            'jenisorder.keterangan as jenisorder_id',
            'container.keterangan as container_id',
            'suratpengantar.nocont',
            'suratpengantar.noseal',
            'statuscontainer.keterangan as statuscontainer_id',
            'suratpengantar.gudang',
            'trado.kodetrado as trado_id',
            'supir.namasupir as supir_id',
            'gandengan.keterangan as gandengan_id',
            'statuslongtrip.memo as statuslongtrip',
            'statusperalihan.memo as statusperalihan',
            'statusritasiomset.memo as statusritasiomset',
            'tarif.tujuan as tarif_id',
            'mandortrado.namamandor as mandortrado_id',
            'mandorsupir.namamandor as mandorsupir_id',
            DB::raw("'" . $dari . "' as tgldari"),
            DB::raw("'" . $sampai . "' as tglsampai"),
            DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
        )
            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime($dari)), date('Y-m-d', strtotime($sampai))])
            ->leftJoin('pelanggan', 'suratpengantar.pelanggan_id', 'pelanggan.id')
            ->leftJoin('kota as kotadari', 'kotadari.id', '=', 'suratpengantar.dari_id')
            ->leftJoin('kota as kotasampai', 'kotasampai.id', '=', 'suratpengantar.sampai_id')
            ->leftJoin('agen', 'suratpengantar.agen_id', 'agen.id')
            ->leftJoin('jenisorder', 'suratpengantar.jenisorder_id', 'jenisorder.id')
            ->leftJoin('container', 'suratpengantar.container_id', 'container.id')
            ->leftJoin('statuscontainer', 'suratpengantar.statuscontainer_id', 'statuscontainer.id')
            ->leftJoin('trado', 'suratpengantar.trado_id', 'trado.id')
            ->leftJoin('supir', 'suratpengantar.supir_id', 'supir.id')
            ->leftJoin('gandengan', 'suratpengantar.gandengan_id', 'gandengan.id')
            ->leftJoin('parameter as statuslongtrip', 'suratpengantar.statuslongtrip', 'statuslongtrip.id')
            ->leftJoin('parameter as statusperalihan', 'suratpengantar.statusperalihan', 'statusperalihan.id')
            ->leftJoin('parameter as statusritasiomset', 'suratpengantar.statusritasiomset', 'statusritasiomset.id')
            ->leftJoin('parameter as statusgudangsama', 'suratpengantar.statusgudangsama', 'statusgudangsama.id')
            ->leftJoin('parameter as statusbatalmuat', 'suratpengantar.statusbatalmuat', 'statusbatalmuat.id')
            ->leftJoin('mandor as mandortrado', 'suratpengantar.mandortrado_id', 'mandortrado.id')
            ->leftJoin('mandor as mandorsupir', 'suratpengantar.mandorsupir_id', 'mandorsupir.id')
            ->leftJoin('tarif', 'suratpengantar.tarif_id', 'tarif.id');

        $data = $query->get();
        $allData = [
            'data' => $data,
            'parameter' => $getParameter
        ];
        return $allData;
    }

    public function getRekapCustomer($dari, $sampai)
    {
        $query = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->select(DB::raw("agen.namaagen as agen, count(agen_id) as jumlah"))
            ->join(DB::raw("agen with (readuncommitted)"), 'suratpengantar.agen_id', 'agen.id')
            ->whereBetween('suratpengantar.tglbukti', [$dari, $sampai])
            ->groupBy('agen.namaagen');

        return $query->get();
    }

    public function approvalTitipanEmkl(array $data)
    {

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        $jambatas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', '=', 'JAMBATASAPPROVAL')->where('subgrp', '=', 'JAMBATASAPPROVAL')->first();
        $tglbatas = date('Y-m-d') . ' ' . $jambatas->text ?? '00:00:00';

        for ($i = 0; $i < count($data['nobukti']); $i++) {

            $nobukti = $data['nobukti'][$i] ?? '';
            $querysuratpengantar = DB::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
                ->select(
                    'a.id'
                )->where('a.nobukti', $nobukti)
                ->first();

            if (isset($querysuratpengantar)) {
                $id = $querysuratpengantar->id ?? 0;
                $suratPengantar = SuratPengantar::lockForUpdate()->findOrFail($id);

                if ($suratPengantar->statusapprovalbiayatitipanemkl == $statusApproval->id) {
                    $suratPengantar->statusapprovalbiayatitipanemkl = $statusNonApproval->id;
                    $suratPengantar->tglapprovalbiayatitipanemkl = date('Y-m-d', strtotime("1900-01-01"));
                    $suratPengantar->tglbatasbiayatitipanemkl = '';
                    $suratPengantar->userapprovalbiayatitipanemkl = '';
                    $aksi = $statusNonApproval->text;
                } else {
                    $suratPengantar->statusapprovalbiayatitipanemkl = $statusApproval->id;
                    $suratPengantar->tglapprovalbiayatitipanemkl = date('Y-m-d H:i:s');
                    $suratPengantar->tglbatasbiayatitipanemkl = $tglbatas;
                    $suratPengantar->userapprovalbiayatitipanemkl = auth('api')->user()->name;
                    $aksi = $statusApproval->text;
                }
                if ($suratPengantar->save()) {
                    (new LogTrail())->processStore([
                        'namatabel' => strtoupper($suratPengantar->getTable()),
                        'postingdari' =>  "$aksi TITIPAN EMKL SURAT PENGANTAR",
                        'idtrans' => $suratPengantar->id,
                        'nobuktitrans' => $suratPengantar->nobukti,
                        'aksi' => $aksi,
                        'datajson' => $suratPengantar->toArray(),
                        'modifiedby' => auth('api')->user()->user
                    ]);
                }
            } else {
                $querysuratpengantar = DB::table("saldosuratpengantar")->from(db::raw("saldosuratpengantar a with (readuncommitted)"))
                    ->select(
                        'a.id'
                    )->where('a.nobukti', $nobukti)
                    ->first();
                $id = $querysuratpengantar->id ?? 0;

                $saldosuratPengantar = SaldoSuratPengantar::lockForUpdate()->findOrFail($id);

                if ($saldosuratPengantar->statusapprovalbiayatitipanemkl == $statusApproval->id) {
                    $saldosuratPengantar->statusapprovalbiayatitipanemkl = $statusNonApproval->id;
                    $saldosuratPengantar->tglapprovalbiayatitipanemkl = date('Y-m-d', strtotime("1900-01-01"));
                    $saldosuratPengantar->tglbatasbiayatitipanemkl = '';
                    $saldosuratPengantar->userapprovalbiayatitipanemkl = '';
                    $aksi = $statusNonApproval->text;
                } else {
                    $saldosuratPengantar->statusapprovalbiayatitipanemkl = $statusApproval->id;
                    $saldosuratPengantar->tglapprovalbiayatitipanemkl = date('Y-m-d H:i:s');
                    $saldosuratPengantar->tglbatasbiayatitipanemkl = $tglbatas;
                    $saldosuratPengantar->userapprovalbiayatitipanemkl = auth('api')->user()->name;
                    $aksi = $statusApproval->text;
                };
                if ($saldosuratPengantar->save()) {
                    (new LogTrail())->processStore([
                        'namatabel' => strtoupper('saldosuratpengantar'),
                        'postingdari' =>  "$aksi TITIPAN EMKL SURAT PENGANTAR",
                        'idtrans' => $saldosuratPengantar->id,
                        'nobuktitrans' => $saldosuratPengantar->nobukti,
                        'aksi' => $aksi,
                        'datajson' => $saldosuratPengantar->toArray(),
                        'modifiedby' => auth('api')->user()->user
                    ]);
                }
            }
        }


        return $data;
    }

    public function approvalEditTujuan(array $data)
    {

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        $jambatas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', '=', 'JAMBATASAPPROVAL')->where('subgrp', '=', 'JAMBATASAPPROVAL')->first();
        $tglbatas = date('Y-m-d') . ' ' . $jambatas->text ?? '00:00:00';

        for ($i = 0; $i < count($data['nobukti']); $i++) {

            $nobukti = $data['nobukti'][$i] ?? '';
            $querysuratpengantar = DB::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
                ->select(
                    'a.id'
                )->where('a.nobukti', $nobukti)
                ->first();

            if (isset($querysuratpengantar)) {
                $id = $querysuratpengantar->id ?? 0;
                $suratPengantar = SuratPengantar::lockForUpdate()->findOrFail($id);

                if ($suratPengantar->statusapprovaleditsuratpengantar == $statusApproval->id) {
                    $suratPengantar->statusapprovaleditsuratpengantar = $statusNonApproval->id;
                    $suratPengantar->tglapprovaleditsuratpengantar = date('Y-m-d', strtotime("1900-01-01"));
                    $suratPengantar->tglbataseditsuratpengantar = '';
                    $suratPengantar->userapprovaleditsuratpengantar = '';
                    $aksi = $statusNonApproval->text;
                } else {
                    $suratPengantar->statusapprovaleditsuratpengantar = $statusApproval->id;
                    $suratPengantar->tglapprovaleditsuratpengantar = date('Y-m-d H:i:s');
                    $suratPengantar->tglbataseditsuratpengantar = $tglbatas;
                    $suratPengantar->userapprovaleditsuratpengantar = auth('api')->user()->name;
                    $aksi = $statusApproval->text;
                }
                if ($suratPengantar->save()) {
                    (new LogTrail())->processStore([
                        'namatabel' => strtoupper($suratPengantar->getTable()),
                        'postingdari' =>  "$aksi EDIT SURAT PENGANTAR",
                        'idtrans' => $suratPengantar->id,
                        'nobuktitrans' => $suratPengantar->nobukti,
                        'aksi' => $aksi,
                        'datajson' => $suratPengantar->toArray(),
                        'modifiedby' => auth('api')->user()->user
                    ]);
                }
            } else {
                $querysuratpengantar = DB::table("saldosuratpengantar")->from(db::raw("saldosuratpengantar a with (readuncommitted)"))
                    ->select(
                        'a.id'
                    )->where('a.nobukti', $nobukti)
                    ->first();
                $id = $querysuratpengantar->id ?? 0;

                $saldosuratPengantar = SaldoSuratPengantar::lockForUpdate()->findOrFail($id);

                if ($saldosuratPengantar->statusapprovaleditsuratpengantar == $statusApproval->id) {
                    $saldosuratPengantar->statusapprovaleditsuratpengantar = $statusNonApproval->id;
                    $saldosuratPengantar->tglapprovaleditsuratpengantar = date('Y-m-d', strtotime("1900-01-01"));
                    $saldosuratPengantar->tglbataseditsuratpengantar = '';
                    $saldosuratPengantar->userapprovaleditsuratpengantar = '';
                    $aksi = $statusNonApproval->text;
                } else {
                    $saldosuratPengantar->statusapprovaleditsuratpengantar = $statusApproval->id;
                    $saldosuratPengantar->tglapprovaleditsuratpengantar = date('Y-m-d H:i:s');
                    $saldosuratPengantar->tglbataseditsuratpengantar = $tglbatas;
                    $saldosuratPengantar->userapprovaleditsuratpengantar = auth('api')->user()->name;
                    $aksi = $statusApproval->text;
                };
                if ($saldosuratPengantar->save()) {
                    (new LogTrail())->processStore([
                        'namatabel' => strtoupper('saldosuratpengantar'),
                        'postingdari' =>  "$aksi EDIT SURAT PENGANTAR",
                        'idtrans' => $saldosuratPengantar->id,
                        'nobuktitrans' => $saldosuratPengantar->nobukti,
                        'aksi' => $aksi,
                        'datajson' => $saldosuratPengantar->toArray(),
                        'modifiedby' => auth('api')->user()->user
                    ]);
                }
            }
        }


        return $data;
    }

    public function approvalBatalMuat(array $data)
    {

        $statusBatalMuat = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS BATAL MUAT')->where('text', '=', 'BATAL MUAT')->first();
        $statusBukanBatalMuat = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS BATAL MUAT')->where('text', '=', 'BUKAN BATAL MUAT')->first();

        for ($i = 0; $i < count($data['nobukti']); $i++) {

            $nobukti = $data['nobukti'][$i] ?? '';
            $querysuratpengantar = DB::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
                ->select(
                    'a.id'
                )->where('a.nobukti', $nobukti)
                ->first();

            if (isset($querysuratpengantar)) {
                $id = $querysuratpengantar->id ?? 0;
                $suratPengantar = SuratPengantar::lockForUpdate()->findOrFail($id);

                if ($suratPengantar->statusbatalmuat == $statusBatalMuat->id) {
                    $suratPengantar->statusbatalmuat = $statusBukanBatalMuat->id;
                    $aksi = $statusBukanBatalMuat->text;
                } else {
                    $suratPengantar->statusbatalmuat = $statusBatalMuat->id;
                    $aksi = $statusBatalMuat->text;
                }
                if ($suratPengantar->save()) {
                    (new LogTrail())->processStore([
                        'namatabel' => strtoupper($suratPengantar->getTable()),
                        'postingdari' =>  "$aksi BATAL MUAT",
                        'idtrans' => $suratPengantar->id,
                        'nobuktitrans' => $suratPengantar->nobukti,
                        'aksi' => $aksi,
                        'datajson' => $suratPengantar->toArray(),
                        'modifiedby' => auth('api')->user()->user
                    ]);
                }
            } else {
                $querysuratpengantar = DB::table("saldosuratpengantar")->from(db::raw("saldosuratpengantar a with (readuncommitted)"))
                    ->select(
                        'a.id'
                    )->where('a.nobukti', $nobukti)
                    ->first();
                $id = $querysuratpengantar->id ?? 0;

                $saldosuratPengantar = SaldoSuratPengantar::lockForUpdate()->findOrFail($id);

                if ($saldosuratPengantar->statusbatalmuat == $statusBatalMuat->id) {
                    $saldosuratPengantar->statusbatalmuat = $statusBukanBatalMuat->id;
                    $aksi = $statusBukanBatalMuat->text;
                } else {
                    $saldosuratPengantar->statusbatalmuat = $statusBatalMuat->id;
                    $aksi = $statusBatalMuat->text;
                }
                if ($saldosuratPengantar->save()) {
                    (new LogTrail())->processStore([
                        'namatabel' => strtoupper('saldosuratpengantar'),
                        'postingdari' =>  "$aksi BATAL MUAT",
                        'idtrans' => $saldosuratPengantar->id,
                        'nobuktitrans' => $saldosuratPengantar->nobukti,
                        'aksi' => $aksi,
                        'datajson' => $saldosuratPengantar->toArray(),
                        'modifiedby' => auth('api')->user()->user
                    ]);
                }
            }
        }


        return $data;
    }

    public function isUsedTrip($trado_id, $supir_id, $tglabsensi)
    {
        $query = $this->where('supir_id', $supir_id)->where('trado_id', $trado_id)->where('tglbukti', $tglabsensi);
        return $query->first();
    }
}
