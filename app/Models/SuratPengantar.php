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
        $cekTanpaBatas = (new Parameter())->cekText('TANPA BATAS TRIP', 'TANPA BATAS TRIP');
        if ($cekTanpaBatas == 'TIDAK') {

            $query = DB::table('suratpengantar')->from(DB::raw("suratpengantar with (readuncommitted)"))
                ->select('tglbataseditsuratpengantar as tglbatasedit')
                ->where('id', $id)
                ->first();
            if (date('Y-m-d H:i:s', strtotime($query->tglbatasedit)) < date('Y-m-d H:i:s')) {
                return false;
            }
        }
        // if ($query->tglbatasedit == $approval->id) return true;
        return true;
    }

    public function cekvalidasihapus($nobukti, $jobtrucking, $trip)
    {

        $cabang = (new Parameter())->cekText('CABANG', 'CABANG');
        $error = new Error();
        $aksi = request()->aksi;
        $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $jenisTangki = DB::table('parameter')->from(DB::raw("parameter as a with (readuncommitted)"))
            ->select('a.id')
            ->where('a.grp', '=', 'STATUS JENIS KENDARAAN')
            ->where('a.subgrp', '=', 'STATUS JENIS KENDARAAN')
            ->where('a.text', '=', 'TANGKI')
            ->first();

        // if ($aksi == 'EDIT') {
        //     $batasJamAdmin = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'BATAS EDIT TRIP ADMIN')->where('subgrp', 'JAM')->first()->text;
        //     $batasHariAdmin = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'BATAS EDIT TRIP ADMIN')->where('subgrp', 'HARI')->first()->text;

        //     $tglbatasedit = date('Y-m-d', strtotime($trip->tglbukti . "+$batasHariAdmin days")) . ' ' . $batasJamAdmin;

        //     if (date('Y-m-d H:i:s') > $tglbatasedit) {

        //         if (date('Y-m-d H:i:s') > date('Y-m-d H:i:s', strtotime($trip->tglbataseditsuratpengantar))) {
        //             $keteranganerror = $error->cekKeteranganError('LB') ?? '';
        //             $data = [
        //                 'kondisi' => true, 
        //                 'keterangan' =>  $keteranganerror . "<br> BATAS $aksi TRIP <b>$nobukti</b> di <br> <b>" . date('d-m-Y', strtotime($trip->tglbukti . "+$batasHariAdmin days")) . ' ' . $batasJamAdmin . '</b> <br> ' . $keterangantambahanerror,
        //                 'kodeerror' => 'LB',
        //             ];

        //             goto selesai;
        //         }
        //     }
        // }
        if ($cabang == 'MEDAN') {
            $statusCetak = (new Parameter())->cekId('STATUSCETAK', 'STATUSCETAK', 'CETAK');

            $querytrip = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
                ->where('nobukti', $nobukti)
                ->where('statusapprovalmandor', 3)
                ->first();
            if ($querytrip != '') {
                $keteranganerror = $error->cekKeteranganError('SAP') ?? '';
                $data = [
                    'kondisi' => true,
                    'keterangan' => 'No Bukti <b>' . $querytrip->nobukti . '</b> ' . $keteranganerror . ' mandor',
                    'kodeerror' => 'SAP',
                ];

                goto selesai;
            }

            $gajiSupir = DB::table('gajisupirdetail')
                ->from(
                    DB::raw("gajisupirdetail as a with (readuncommitted)")
                )
                ->select(
                    'a.suratpengantar_nobukti',
                    'a.nobukti',
                    'b.statuscetak',
                    db::raw("isnull(c.nobukti,'') as nobuktiebs")
                )
                ->join(DB::raw("gajisupirheader as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
                ->join(DB::raw("prosesgajisupirdetail as c with (readuncommitted)"), 'b.nobukti', 'c.gajisupir_nobukti')
                ->where('a.suratpengantar_nobukti', '=', $nobukti)
                ->first();

            if (isset($gajiSupir)) {
                if ($gajiSupir->statuscetak == $statusCetak) {
                    $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
                    $data = [
                        'kondisi' => true,
                        'keterangan' => 'No Bukti gaji supir <b>' . $gajiSupir->nobukti . '</b> ' . $keteranganerror,
                        'kodeerror' => 'SDC',
                    ];

                    goto selesai;
                } else {
                    if ($gajiSupir->nobuktiebs != '') {
                        $keteranganerror = $error->cekKeteranganError('SPOST') ?? '';
                        $data = [
                            'kondisi' => true,
                            'keterangan' => 'No Bukti gaji supir <b>' . $gajiSupir->nobukti . '</b> ' . $keteranganerror . '<br> No Bukti Posting <b>' . $gajiSupir->nobuktiebs . '</b>',
                            'kodeerror' => 'SPOST',
                        ];

                        goto selesai;
                    }
                }
            }
        } else {
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
                    'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> GAji Supir <b>' . $gajiSupir->nobukti . '</b> <br> ' . $keterangantambahanerror,
                    // 'keterangan' => 'gaji supir ' . $gajiSupir->nobukti,
                    'kodeerror' => 'SATL2'
                ];


                goto selesai;
            }
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
                    'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> Ritasi <b>' . $ritasi->nobukti . '</b> <br> ' . $keterangantambahanerror,
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
                        'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> Trip <b>' . $cekJob->nobukti . '</b> <br> ' . $keterangantambahanerror,
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
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> Invoice <b>' . $query->nobukti . '</b> <br> ' . $keterangantambahanerror,
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
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> pendapatan supir <b>' . $query->nobukti . '</b> <br> ' . $keterangantambahanerror,
                'kodeerror' => 'SATL2'

            ];
            goto selesai;
        }

        if ($trip->statusjeniskendaraan == $jenisTangki->id && $aksi == 'DELETE') {
            $getTripTangki = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
                ->select(db::raw("STRING_AGG(cast(nobukti  as nvarchar(max)), ', ') as nobukti"))
                ->where('supir_id', $trip->supir_id)
                ->where('trado_id', $trip->trado_id)
                ->where('tglbukti', date('Y-m-d', strtotime($trip->tglbukti)))
                ->where('statusjeniskendaraan', $jenisTangki->id)
                ->where('id', '>', $trip->id)
                ->first();
            if ($getTripTangki->nobukti != '') {
                $keteranganerror = $error->cekKeteranganError('ATBB') ?? '';
                $data = [
                    'kondisi' => true,
                    'keterangan' =>  $keteranganerror . ' <b>' . $getTripTangki->nobukti . '</b><br> ' . $keterangantambahanerror,
                    'kodeerror' => 'SATL2',
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

    public function get()
    {
        $this->setRequestParameters();
        $tglabsensi = request()->tglabsensi ?? '';
        $trado_id = request()->trado_id ?? '';
        $supir_id = request()->supir_id ?? '';
        $gudangsama = request()->gudangsama ?? 0;
        $longtrip = request()->longtrip ?? 0;
        $dari_id = request()->dari_id ?? 0;
        $sampai_id = request()->sampai_id ?? 0;
        $gandengan_id = request()->gandengan_id ?? 0;
        $from = request()->from ?? '';
        $nobuktitrip = request()->nobukti ?? '';
        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'SurataPengantarController';

        $getSudahbuka = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS SUDAH BUKA')->where('subgrp', 'STATUS SUDAH BUKA')->where('text', 'SUDAH BUKA')->first() ?? 0;
        $getBelumbuka = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS SUDAH BUKA')->where('subgrp', 'STATUS SUDAH BUKA')->where('text', 'BELUM BUKA')->first() ?? 0;

        if ($proses == 'reload') {

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

            $tempsuratpengantar = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            $tempsuratpengantarlist = 'temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

            DB::table('listtemporarytabel')->insert(
                [
                    'class' => $class,
                    'namatabel' => $tempsuratpengantarlist,
                    'modifiedby' => $user,
                    'created_at' => date('Y/m/d H:i:s'),
                    'updated_at' => date('Y/m/d H:i:s'),
                ]
            );
            // $temtabel = 'temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


            // $tempsuratpengantar = '##tempsuratpengantar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
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
                $table->unsignedBigInteger('statustolakan')->nullable();
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
                $table->string('prosesgajisupir_nobukti', 500)->nullable();
                $table->string('invoice_nobukti', 500)->nullable();
                $table->unsignedBigInteger('statusgajisupir')->nullable();
                $table->unsignedBigInteger('statusinvoice')->nullable();
                $table->datetime('tgldariorderantrucking')->nullable();
                $table->datetime('tglsampaiorderantrucking')->nullable();
                $table->integer('statusapprovalbiayaextra')->Length(11)->nullable();
                $table->string('userapprovalbiayaextra', 50)->nullable();
                $table->date('tglapprovalbiayaextra')->nullable();
                $table->datetime('tglbatasapprovalbiayaextra')->nullable();
            });

            $tempspric = '##tempspric' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempspric, function ($table) {
                $table->string('nobukti', 50)->nullable();
                $table->string('ebsnobukti', 50)->nullable();
                $table->string('suratpengantar_nobukti', 50)->nullable();
            });
            $queryric = DB::table("gajisupirdetail")->from(DB::raw("gajisupirdetail a with (readuncommitted)"))
                ->select(
                    db::raw("max(a.nobukti) as nobukti"),
                    db::raw("max(b.nobukti) as ebsnobukti"),
                    'a.suratpengantar_nobukti'
                )
                ->leftjoin(db::raw("prosesgajisupirdetail b"), 'a.nobukti', 'b.gajisupir_nobukti')
                ->groupBy('a.suratpengantar_nobukti');
            DB::table($tempspric)->insertUsing([
                'nobukti',
                'ebsnobukti',
                'suratpengantar_nobukti',
            ], $queryric);

            $tempspinv = '##tempspinv' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempspinv, function ($table) {
                $table->string('nobukti', 50)->nullable();
                $table->string('orderantrucking_nobukti', 50)->nullable();
            });
            $queryinv = DB::table("invoicedetail")->from(DB::raw("invoicedetail a with (readuncommitted)"))
                ->select(
                    db::raw("max(a.nobukti) as nobukti"),
                    'a.orderantrucking_nobukti'
                )
                ->groupBy('a.orderantrucking_nobukti');
            DB::table($tempspinv)->insertUsing([
                'nobukti',
                'orderantrucking_nobukti',
            ], $queryinv);

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
                    'suratpengantar.statustolakan',
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
                    db::raw("isnull(b.nobukti,'') as gajisupir_nobukti"),
                    db::raw("isnull(b.ebsnobukti,'') as prosesgajisupir_nobukti"),
                    'c.nobukti as invoice_nobukti',
                    db::raw("(case when isnull(b.nobukti,'')='' then " . $getBelumbuka->id . " else " . $getSudahbuka->id . " end) as statusgajisupir"),
                    db::raw("(case when isnull(c.nobukti,'')='' then " . $getBelumbuka->id . " else " . $getSudahbuka->id . " end) as statusinvoice"),
                    db::raw("cast((format(orderantrucking.tglbukti,'yyyy/MM')+'/1') as date) as tgldariorderantrucking"),
                    db::raw("cast(cast(format((cast((format(orderantrucking.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiorderantrucking"),
                    'statusapprovalbiayaextra',
                    'userapprovalbiayaextra',
                    'tglapprovalbiayaextra',
                    'tglbatasapprovalbiayaextra'
                )
                ->leftJoin(DB::raw("$tempspric as b with (readuncommitted)"), 'suratpengantar.nobukti', 'b.suratpengantar_nobukti')
                ->leftJoin(DB::raw("$tempspinv as c with (readuncommitted)"), 'suratpengantar.jobtrucking', 'c.orderantrucking_nobukti')
                ->leftJoin(DB::raw("orderantrucking  with (readuncommitted)"), 'suratpengantar.jobtrucking', 'orderantrucking.nobukti');


            if (request()->tgldari) {
                $querysuratpengantar->whereBetween('suratpengantar.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
            }


            if ($from == 'tripinap') {
                // $getBatasInput = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'BATAS PENGAJUAN TRIP INAP')->where('subgrp', 'BATAS PENGAJUAN TRIP INAP')->first()->text;

                // $batas = date('Y-m-d', strtotime("-$getBatasInput days"));
                // $querysuratpengantar->whereBetween('suratpengantar.tglbukti', [$batas, date('Y-m-d')]);
                $querysuratpengantar->where('suratpengantar.tglbukti', date('Y-m-d', strtotime(request()->tglabsensi)));
            }
            if ($from == 'biayaextrasupir') {
                $tglBatas = date('Y-m-d', strtotime('-3 days'));
                $querysuratpengantar->whereBetween('suratpengantar.tglbukti', [$tglBatas, date('Y-m-d')]);
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
                'statustolakan',
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
                'prosesgajisupir_nobukti',
                'invoice_nobukti',
                'statusgajisupir',
                'statusinvoice',
                'tgldariorderantrucking',
                'tglsampaiorderantrucking',
                'statusapprovalbiayaextra',
                'userapprovalbiayaextra',
                'tglapprovalbiayaextra',
                'tglbatasapprovalbiayaextra'


            ], $querysuratpengantar);



            if ($from == 'biayaextrasupir') {
                $querysuratpengantar = DB::table('suratpengantar')->from(
                    DB::raw("suratpengantar with (readuncommitted)")
                )
                    ->select(
                        'suratpengantar.id',
                        'suratpengantar.nobukti',
                        'suratpengantar.jobtrucking',
                        'suratpengantar.tglbukti',
                        'suratpengantar.pelanggan_id',
                        'suratpengantar.jenisorder_id',
                        'suratpengantar.statuscontainer_id',
                        'suratpengantar.dari_id',
                        'suratpengantar.sampai_id',
                        'suratpengantar.agen_id',
                        'suratpengantar.container_id',
                        'suratpengantar.nocont',
                        'suratpengantar.nocont2',
                        'suratpengantar.trado_id',
                        'suratpengantar.supir_id',
                        'suratpengantar.gandengan_id',
                        'suratpengantar.keterangan',
                        'suratpengantar.nojob',
                        'suratpengantar.nojob2',
                        'suratpengantar.statuslongtrip',
                        'suratpengantar.gajisupir',
                        'suratpengantar.gajikenek',
                        'suratpengantar.statusperalihan',
                        'suratpengantar.tarif_id',
                        'suratpengantar.nominalperalihan',
                        'suratpengantar.nosp',
                        'suratpengantar.tglsp',
                        'suratpengantar.upah_id',
                        'suratpengantar.penyesuaian',
                        'suratpengantar.modifiedby',
                        'suratpengantar.created_at',
                        'suratpengantar.updated_at',

                    )
                    ->where('suratpengantar.statusapprovalbiayaextra', 3)
                    ->where('suratpengantar.tglbatasapprovalbiayaextra', '>=', date('Y-m-d H:i:s'));
                DB::table($tempsuratpengantar)->insertUsing([
                    'id',
                    'nobukti',
                    'jobtrucking',
                    'tglbukti',
                    'pelanggan_id',
                    'jenisorder_id',
                    'statuscontainer_id',
                    'dari_id',
                    'sampai_id',
                    'agen_id',
                    'container_id',
                    'nocont',
                    'nocont2',
                    'trado_id',
                    'supir_id',
                    'gandengan_id',
                    'keterangan',
                    'nojob',
                    'nojob2',
                    'statuslongtrip',
                    'gajisupir',
                    'gajikenek',
                    'statusperalihan',
                    'tarif_id',
                    'nominalperalihan',
                    'nosp',
                    'tglsp',
                    'upah_id',
                    'penyesuaian',
                    'modifiedby',
                    'created_at',
                    'updated_at'
                ], $querysuratpengantar);
            }
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
                // $getBatasInput = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'BATAS PENGAJUAN TRIP INAP')->where('subgrp', 'BATAS PENGAJUAN TRIP INAP')->first()->text;

                // $batas = date('Y-m-d', strtotime("-$getBatasInput days"));
                // $querysuratpengantar->whereBetween('suratpengantar.tglbukti', [$batas, date('Y-m-d')]);
                $querysuratpengantar->where('suratpengantar.tglbukti', date('Y-m-d', strtotime(request()->tglabsensi)));
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

            $temppelanggan = '##temppelanggan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temppelanggan, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->longtext('namapelanggan')->nullable();
                $table->index('id', 'temppelanggan_id_index');
            });

            $querypelanggan = db::table("pelanggan")->from(db::raw("pelanggan a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.namapelanggan'
                )
                ->orderby('a.id');

            DB::table($temppelanggan)->insert([
                'id' => 0,
                'namapelanggan' => '',
            ]);

            DB::table($temppelanggan)->insertUsing([
                'id',
                'namapelanggan'
            ], $querypelanggan);

            // kota
            $tempkota = '##tempkota' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempkota, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->longtext('kodekota')->nullable();
                $table->index('id', 'tempkota_id_index');
            });

            $querykota = db::table("kota")->from(db::raw("kota a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.kodekota'
                )
                ->orderby('a.id');

            DB::table($tempkota)->insert([
                'id' => 0,
                'kodekota' => '',
            ]);

            DB::table($tempkota)->insertUsing([
                'id',
                'kodekota'
            ], $querykota);

            // agen
            $tempagen = '##tempagen' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempagen, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->longtext('namaagen')->nullable();
                $table->index('id', 'tempagen_id_index');
            });

            $queryagen = db::table("agen")->from(db::raw("agen a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.namaagen'
                )
                ->orderby('a.id');

            DB::table($tempagen)->insert([
                'id' => 0,
                'namaagen' => '',
            ]);

            DB::table($tempagen)->insertUsing([
                'id',
                'namaagen'
            ], $queryagen);

            // jenisorder
            $tempjenisorder = '##tempjenisorder' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempjenisorder, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->longtext('keterangan')->nullable();
                $table->index('id', 'tempjenisorder_id_index');
            });

            $queryjenisorder = db::table("jenisorder")->from(db::raw("jenisorder a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.keterangan'
                )
                ->orderby('a.id');

            DB::table($tempjenisorder)->insert([
                'id' => 0,
                'keterangan' => '',
            ]);

            DB::table($tempjenisorder)->insertUsing([
                'id',
                'keterangan'
            ], $queryjenisorder);

            // container
            $tempcontainer = '##tempcontainer' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempcontainer, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->longtext('keterangan')->nullable();
                $table->index('id', 'tempcontainer_id_index');
            });

            $querycontainer = db::table("container")->from(db::raw("container a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.keterangan'
                )
                ->orderby('a.id');

            DB::table($tempcontainer)->insert([
                'id' => 0,
                'keterangan' => '',
            ]);

            DB::table($tempcontainer)->insertUsing([
                'id',
                'keterangan'
            ], $querycontainer);

            // statuscontainer
            $tempstatuscontainer = '##tempstatuscontainer' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempstatuscontainer, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->longtext('keterangan')->nullable();
                $table->index('id', 'tempstatuscontainer_id_index');
            });

            $querystatuscontainer = db::table("statuscontainer")->from(db::raw("statuscontainer a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.keterangan'
                )
                ->orderby('a.id');

            DB::table($tempstatuscontainer)->insert([
                'id' => 0,
                'keterangan' => '',
            ]);

            DB::table($tempstatuscontainer)->insertUsing([
                'id',
                'keterangan'
            ], $querystatuscontainer);


            // gandengan
            $tempgandengan = '##tempgandengan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempgandengan, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->longtext('keterangan')->nullable();
                $table->index('id', 'tempgandengan_id_index');
            });

            $querygandengan = db::table("gandengan")->from(db::raw("gandengan a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.keterangan'
                )
                ->orderby('a.id');

            DB::table($tempgandengan)->insert([
                'id' => 0,
                'keterangan' => '',
            ]);

            DB::table($tempgandengan)->insertUsing([
                'id',
                'keterangan'
            ], $querygandengan);

            // supir
            $tempsupir = '##tempsupir' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempsupir, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->longtext('namasupir')->nullable();
                $table->index('id', 'tempsupir_id_index');
            });

            $querysupir = db::table("supir")->from(db::raw("supir a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.namasupir'
                )
                ->orderby('a.id');

            DB::table($tempsupir)->insert([
                'id' => 0,
                'namasupir' => '',
            ]);

            DB::table($tempsupir)->insertUsing([
                'id',
                'namasupir'
            ], $querysupir);

            // trado
            $temptrado = '##temptrado' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temptrado, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->longtext('kodetrado')->nullable();
                $table->index('id', 'temptrado_id_index');
            });

            $querytrado = db::table("trado")->from(db::raw("trado a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.kodetrado'
                )
                ->orderby('a.id');

            DB::table($temptrado)->insert([
                'id' => 0,
                'kodetrado' => '',
            ]);

            DB::table($temptrado)->insertUsing([
                'id',
                'kodetrado'
            ], $querytrado);

            // mandor
            $tempmandor = '##tempmandor' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempmandor, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->longtext('namamandor')->nullable();
                $table->index('id', 'tempmandor_id_index');
            });

            $querymandor = db::table("mandor")->from(db::raw("mandor a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.namamandor'
                )
                ->orderby('a.id');

            DB::table($tempmandor)->insert([
                'id' => 0,
                'namamandor' => '',
            ]);

            DB::table($tempmandor)->insertUsing([
                'id',
                'namamandor'
            ], $querymandor);

            // parameter
            $tempparameter = '##tempparameter' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempparameter, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->longtext('memo')->nullable();
                $table->longtext('text')->nullable();
                $table->index('id', 'tempparameter_id_index');
            });

            $queryparameter = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.memo',
                    'a.text'
                )
                ->orderby('a.id');

            DB::table($tempparameter)->insert([
                'id' => 0,
                'memo' => '',
                'text' => '',
            ]);

            DB::table($tempparameter)->insertUsing([
                'id',
                'memo',
                'text'
            ], $queryparameter);

            // tarif
            $temptarif = '##temptarif' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temptarif, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->longtext('tujuan')->nullable();
                $table->index('id', 'temptarif_id_index');
            });

            $querytarif = db::table("tarif")->from(db::raw("tarif a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.tujuan'
                )
                ->orderby('a.id');

            DB::table($temptarif)->insert([
                'id' => 0,
                'tujuan' => '',
            ]);

            DB::table($temptarif)->insertUsing([
                'id',
                'tujuan'
            ], $querytarif);

            // orderntrucking
            $temporderantrucking = '##temporderantrucking' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temporderantrucking, function ($table) {
                $table->string('nobukti', 50)->nullable();
                $table->date('tglbukti')->nullable();
                $table->index('nobukti', 'temporderantrucking_nobukti_index');
            });

            $queryorderantrucking = db::table("orderantrucking")->from(db::raw("orderantrucking a with (readuncommitted)"))
                ->select(
                    'a.nobukti',
                    db::raw("max(a.tglbukti) as tglbukti")
                )
                ->join(db::raw($tempsuratpengantar . "  b "), 'a.nobukti', 'b.jobtrucking')
                ->groupby('a.nobukti');

            DB::table($temporderantrucking)->insert([
                'nobukti' => '',
                'tglbukti' => '1900/1/1',
            ]);

            DB::table($temporderantrucking)->insertUsing([
                'nobukti',
                'tglbukti'
            ], $queryorderantrucking);

            // gajisupirheader
            $tempgajisupirheader = '##tempgajisupirheader' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempgajisupirheader, function ($table) {
                $table->string('nobukti', 50)->nullable();
                $table->date('tglbukti')->nullable();
                $table->index('nobukti', 'tempgajisupirheader_nobukti_index');
            });

            $querygajisupirheader = db::table("gajisupirheader")->from(db::raw("gajisupirheader a with (readuncommitted)"))
                ->select(
                    'a.nobukti',
                    db::raw("max(a.tglbukti) as tglbukti")
                )
                ->join(db::raw($tempsuratpengantar . "  b "), 'a.nobukti', 'b.gajisupir_nobukti')
                ->Groupby('a.nobukti');

            DB::table($tempgajisupirheader)->insert([
                'nobukti' => '',
                'tglbukti' => '1900/1/1',
            ]);

            DB::table($tempgajisupirheader)->insertUsing([
                'nobukti',
                'tglbukti'
            ], $querygajisupirheader);

            // invoiceheader
            $tempinvoiceheader = '##tempinvoiceheader' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempinvoiceheader, function ($table) {
                $table->string('nobukti', 50)->nullable();
                $table->date('tglbukti')->nullable();
                $table->index('nobukti', 'tempinvoiceheader_nobukti_index');
            });

            $queryinvoiceheader = db::table("invoiceheader")->from(db::raw("invoiceheader a with (readuncommitted)"))
                ->select(
                    'a.nobukti',
                    db::raw("max(a.tglbukti) as tglbukti")
                )
                ->join(db::raw($tempsuratpengantar . "  b "), 'a.nobukti', 'b.invoice_nobukti')
                ->Groupby('a.nobukti');

            DB::table($tempinvoiceheader)->insert([
                'nobukti' => '',
                'tglbukti' => '1900/1/1',
            ]);

            DB::table($tempinvoiceheader)->insertUsing([
                'nobukti',
                'tglbukti'
            ], $queryinvoiceheader);

            Schema::create($tempsuratpengantarlist, function ($table) {
                $table->integer('id')->nullable();
                $table->string('nobukti', 50)->nullable();
                $table->string('jobtrucking', 50)->nullable();
                $table->date('tglbukti')->nullable();
                $table->longtext('nosp')->nullable();
                $table->date('tglsp')->nullable();
                $table->longtext('nojob')->nullable();
                $table->longtext('nojob2')->nullable();
                $table->longtext('pelanggan_id')->nullable();
                $table->longtext('pelangganid')->nullable();
                $table->longText('keterangan')->nullable();
                $table->longtext('dari_id')->nullable();
                $table->longtext('sampai_id')->nullable();
                $table->longtext('sampaiid')->nullable();
                $table->longText('penyesuaian')->nullable();
                $table->double('gajisupir', 15, 2)->nullable();
                $table->double('jarak', 15, 2)->nullable();
                $table->longtext('agen_id')->nullable();
                $table->longtext('jenisorder_id')->nullable();
                $table->longtext('container_id')->nullable();
                $table->string('nocont', 50)->nullable();
                $table->string('noseal', 50)->nullable();
                $table->string('nocont2', 50)->nullable();
                $table->string('noseal2', 50)->nullable();
                $table->double('omset', 15, 2)->nullable();
                $table->double('nominalperalihan', 15, 2)->nullable();
                $table->double('totalomset', 15, 2)->nullable();
                $table->longtext('statuscontainer_id')->nullable();
                $table->longtext('gudang')->nullable();
                $table->longtext('trado_id')->nullable();
                $table->longtext('supir_id')->nullable();
                $table->longtext('tradolookup')->nullable();
                $table->longtext('supirlookup')->nullable();
                $table->longtext('gandengan_id')->nullable();
                $table->longtext('gandenganid')->nullable();
                $table->longtext('statuslongtrip')->nullable();
                $table->longtext('statuslongtriptext')->nullable();
                $table->longtext('statuslangsir')->nullable();
                $table->longtext('statuslangsirtext')->nullable();
                $table->longtext('statusperalihan')->nullable();
                $table->longtext('statusperalihantext')->nullable();
                $table->longtext('statusritasiomset')->nullable();
                $table->longtext('statusapprovaleditsuratpengantar')->nullable();
                $table->longtext('statusapprovaleditsuratpengantartext')->nullable();
                $table->longtext('statusapprovalbiayatitipanemkl')->nullable();
                $table->longtext('statusapprovalbiayatitipanemkltext')->nullable();
                $table->longtext('tarif_id')->nullable();
                $table->longtext('mandortrado_id')->nullable();
                $table->longtext('mandorsupir_id')->nullable();
                $table->longtext('statustolakan')->nullable();
                $table->longtext('statustolakantext')->nullable();
                $table->longtext('statusgudangsama')->nullable();
                $table->longtext('statusgudangsamatext')->nullable();
                $table->longtext('statusbatalmuat')->nullable();
                $table->longtext('statusbatalmuattext')->nullable();
                $table->longtext('userapprovaleditsuratpengantar')->nullable();
                $table->longtext('userapprovalbiayatitipanemkl')->nullable();
                $table->date('tglapprovaleditsuratpengantar')->nullable();
                $table->dateTime('tglbataseditsuratpengantar')->nullable();
                $table->date('tglapprovalbiayatitipanemkl')->nullable();
                $table->longtext('modifiedby')->nullable();
                $table->datetime('created_at')->nullable();
                $table->datetime('updated_at')->nullable();
                $table->longtext('gajisupir_nobukti')->nullable();
                $table->longtext('prosesgajisupir_nobukti')->nullable();
                $table->longtext('invoice_nobukti')->nullable();
                $table->longtext('statusgajisupir')->nullable();
                $table->longtext('statusgajisupirtext')->nullable();
                $table->longtext('statusinvoice')->nullable();
                $table->longtext('statusinvoicetext')->nullable();
                $table->date('tgldariorderantrucking')->nullable();
                $table->date('tglsampaiorderantrucking')->nullable();
                $table->date('tgldarigajisupirheader')->nullable();
                $table->date('tglsampaigajisupirheader')->nullable();
                $table->date('tgldariinvoiceheader')->nullable();
                $table->date('tglsampaiinvoiceheader')->nullable();
                $table->longtext('statusapprovalbiayaextra')->nullable();
                $table->longtext('statusapprovalbiayaextratext')->nullable();
                $table->longtext('userapprovalbiayaextra')->nullable();
                $table->date('tglapprovalbiayaextra')->nullable();
                $table->date('tglbatasapprovalbiayaextra')->nullable();
            });


            $query = DB::table($tempsuratpengantar)->from(
                db::raw($tempsuratpengantar . ' suratpengantar')
            )
                ->select(
                    'suratpengantar.id',
                    'suratpengantar.nobukti',
                    'suratpengantar.jobtrucking',
                    'suratpengantar.tglbukti',
                    'suratpengantar.nosp',
                    'suratpengantar.tglsp',
                    'suratpengantar.nojob',
                    'suratpengantar.nojob2',
                    'pelanggan.namapelanggan as pelanggan_id',
                    'suratpengantar.pelanggan_id as pelangganid',
                    'suratpengantar.keterangan',
                    'kotadari.kodekota as dari_id',
                    'kotasampai.kodekota as sampai_id',
                    'suratpengantar.sampai_id as sampaiid',
                    'suratpengantar.penyesuaian',
                    'suratpengantar.gajisupir',
                    DB::raw("(case when suratpengantar.jarak IS NULL then 0 else suratpengantar.jarak end) as jarak"),
                    'agen.namaagen as agen_id',
                    'jenisorder.keterangan as jenisorder_id',
                    'container.keterangan as container_id',
                    'suratpengantar.nocont',
                    'suratpengantar.noseal',
                    'suratpengantar.nocont2',
                    'suratpengantar.noseal2',
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
                    'gandengan.id as gandenganid',
                    'statuslongtrip.memo as statuslongtrip',
                    'statuslongtrip.text as statuslongtriptext',
                    'statuslangsir.memo as statuslangsir',
                    'statuslangsir.text as statuslangsirtext',
                    'statusperalihan.memo as statusperalihan',
                    'statusperalihan.text as statusperalihantext',
                    'statusritasiomset.memo as statusritasiomset',
                    'statusapprovaleditsuratpengantar.memo as statusapprovaleditsuratpengantar',
                    'statusapprovaleditsuratpengantar.text as statusapprovaleditsuratpengantartext',
                    'statusapprovalbiayatitipanemkl.memo as statusapprovalbiayatitipanemkl',
                    'statusapprovalbiayatitipanemkl.text as statusapprovalbiayatitipanemkltext',
                    'tarif.tujuan as tarif_id',
                    'mandortrado.namamandor as mandortrado_id',
                    'mandorsupir.namamandor as mandorsupir_id',
                    'statustolakan.memo as statustolakan',
                    'statustolakan.text as statustolakantext',
                    'statusgudangsama.memo as statusgudangsama',
                    'statusgudangsama.text as statusgudangsamatext',
                    'statusbatalmuat.memo as statusbatalmuat',
                    'statusbatalmuat.text as statusbatalmuattext',
                    'suratpengantar.userapprovaleditsuratpengantar',
                    'suratpengantar.userapprovalbiayatitipanemkl',
                    DB::raw("(case when year(isnull(suratpengantar.tglapprovaleditsuratpengantar,'1900/1/1'))<2000 then null else suratpengantar.tglapprovaleditsuratpengantar end) as tglapprovaleditsuratpengantar"),
                    DB::raw("(case when year(isnull(suratpengantar.tglbataseditsuratpengantar,'1900/1/1 00:00:00.000'))<2000 then null else suratpengantar.tglbataseditsuratpengantar end) as tglbataseditsuratpengantar"),
                    DB::raw("(case when year(isnull(suratpengantar.tglapprovalbiayatitipanemkl,'1900/1/1 00:00:00.000'))<2000 then null else suratpengantar.tglapprovalbiayatitipanemkl end) as tglapprovalbiayatitipanemkl"),
                    'suratpengantar.modifiedby',
                    'suratpengantar.created_at',
                    'suratpengantar.updated_at',
                    'suratpengantar.gajisupir_nobukti',
                    'suratpengantar.prosesgajisupir_nobukti',
                    'suratpengantar.invoice_nobukti',
                    'statusgajisupir.memo as statusgajisupir',
                    'statusgajisupir.text as statusgajisupirtext',
                    'statusinvoice.memo as statusinvoice',
                    'statusinvoice.text as statusinvoicetext',
                    db::raw("cast((format(orderantrucking.tglbukti,'yyyy/MM')+'/1') as date) as tgldariorderantrucking"),
                    db::raw("cast(cast(format((cast((format(orderantrucking.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiorderantrucking"),
                    db::raw("cast((format(gajisupirheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldarigajisupirheader"),
                    db::raw("cast(cast(format((cast((format(gajisupirheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaigajisupirheader"),
                    db::raw("cast((format(invoiceheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariinvoiceheader"),
                    db::raw("cast(cast(format((cast((format(invoiceheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiinvoiceheader"),

                    'statusapprovalbiayaextra.memo as statusapprovalbiayaextra',
                    'statusapprovalbiayaextra.text as statusapprovalbiayaextratext',
                    'suratpengantar.userapprovalbiayaextra',
                    DB::raw("(case when year(isnull(suratpengantar.tglapprovalbiayaextra,'1900/1/1'))<2000 then null else suratpengantar.tglapprovalbiayaextra end) as tglapprovalbiayaextra"),
                    DB::raw("(case when year(isnull(suratpengantar.tglbatasapprovalbiayaextra,'1900/1/1 00:00:00.000'))<2000 then null else suratpengantar.tglbatasapprovalbiayaextra end) as tglbatasapprovalbiayaextra"),
                )

                ->Join(db::raw($temppelanggan . " as pelanggan"), db::raw("isnull(suratpengantar.pelanggan_id,0)"), 'pelanggan.id')
                ->Join(db::raw($tempkota . " as kotadari"), 'kotadari.id', '=', db::raw("isnull(suratpengantar.dari_id,0)"))
                ->Join(db::raw($tempkota . " as kotasampai"), 'kotasampai.id', '=', db::raw("isnull(suratpengantar.sampai_id,0)"))
                ->Join(db::raw($tempagen . " as agen"), db::raw("isnull(suratpengantar.agen_id,0)"), 'agen.id')
                ->Join(db::raw($tempjenisorder . " as jenisorder"), db::raw("isnull(suratpengantar.jenisorder_id,0)"), 'jenisorder.id')
                ->Join(db::raw($tempcontainer . " as container"), db::raw("isnull(suratpengantar.container_id,0)"), 'container.id')
                ->Join(db::raw($tempstatuscontainer . " as statuscontainer"), db::raw("isnull(suratpengantar.statuscontainer_id,0)"), 'statuscontainer.id')
                ->Join(db::raw($temptrado . " as trado"), db::raw("isnull(suratpengantar.trado_id,0)"), 'trado.id')
                ->Join(db::raw($tempsupir . " as supir"), db::raw("isnull(suratpengantar.supir_id,0)"), 'supir.id')
                ->Join(db::raw($tempgandengan . " as gandengan"), db::raw("isnull(suratpengantar.gandengan_id,0)"), 'gandengan.id')
                ->Join(db::raw($tempparameter . " as statuslongtrip"), db::raw("isnull(suratpengantar.statuslongtrip,0)"), 'statuslongtrip.id')
                ->Join(db::raw($tempparameter . " as statuslangsir"), db::raw("isnull(suratpengantar.statuslangsir,0)"), 'statuslangsir.id')
                ->Join(db::raw($tempparameter . " as statusperalihan"), db::raw("isnull(suratpengantar.statusperalihan,0)"), 'statusperalihan.id')
                ->Join(db::raw($tempparameter . " as statusritasiomset"), db::raw("isnull(suratpengantar.statusritasiomset,0)"), 'statusritasiomset.id')
                ->Join(db::raw($tempparameter . " as statusgudangsama"), db::raw("isnull(suratpengantar.statusgudangsama,0)"), 'statusgudangsama.id')
                ->Join(db::raw($tempparameter . " as statustolakan"), db::raw("isnull(suratpengantar.statustolakan,0)"), 'statustolakan.id')
                ->Join(db::raw($tempparameter . " as statusbatalmuat"), db::raw("isnull(suratpengantar.statusbatalmuat,0)"), 'statusbatalmuat.id')
                ->Join(db::raw($tempparameter . " as statusapprovaleditsuratpengantar"), db::raw("isnull(suratpengantar.statusapprovaleditsuratpengantar,0)"), 'statusapprovaleditsuratpengantar.id')
                ->Join(db::raw($tempparameter . " as statusapprovalbiayaextra"), db::raw("isnull(suratpengantar.statusapprovalbiayaextra,0)"), 'statusapprovalbiayaextra.id')
                ->Join(db::raw($tempparameter . " as statusapprovalbiayatitipanemkl"), db::raw("isnull(suratpengantar.statusapprovalbiayatitipanemkl,0)"), 'statusapprovalbiayatitipanemkl.id')
                ->Join(db::raw($tempparameter . " as statusgajisupir"), db::raw("isnull(suratpengantar.statusgajisupir,0)"), 'statusgajisupir.id')
                ->Join(db::raw($tempparameter . " as statusinvoice"),  db::raw("isnull(suratpengantar.statusinvoice,0)"), 'statusinvoice.id')
                ->Join(db::raw($tempmandor . " as mandortrado"), db::raw("isnull(suratpengantar.mandortrado_id,0)"), 'mandortrado.id')
                ->Join(db::raw($tempmandor . " as mandorsupir"), db::raw("isnull(suratpengantar.mandorsupir_id,0)"), 'mandorsupir.id')
                ->Join(db::raw($temptarif . " as tarif"), db::raw("isnull(suratpengantar.tarif_id,0)"), 'tarif.id')
                ->leftJoin(DB::raw($temporderantrucking . " as orderantrucking with (readuncommitted)"), db::raw("isnull(suratpengantar.jobtrucking,'')"), 'orderantrucking.nobukti')
                ->Join(DB::raw($tempgajisupirheader . " as gajisupirheader  with (readuncommitted)"), db::raw("isnull(suratpengantar.gajisupir_nobukti,'')"), 'gajisupirheader.nobukti')
                // ->leftJoin(DB::raw("prosesgajisupirdetail  with (readuncommitted)"), 'gajisupirheader.nobukti', 'prosesgajisupirdetail.gajisupir_nobukti')
                ->Join(DB::raw($tempinvoiceheader . " as invoiceheader  with (readuncommitted)"), db::raw("isnull(suratpengantar.invoice_nobukti,'')"), 'invoiceheader.nobukti');

            DB::table($tempsuratpengantarlist)->insertUsing([
                'id',
                'nobukti',
                'jobtrucking',
                'tglbukti',
                'nosp',
                'tglsp',
                'nojob',
                'nojob2',
                'pelanggan_id',
                'pelangganid',
                'keterangan',
                'dari_id',
                'sampai_id',
                'sampaiid',
                'penyesuaian',
                'gajisupir',
                'jarak',
                'agen_id',
                'jenisorder_id',
                'container_id',
                'nocont',
                'noseal',
                'nocont2',
                'noseal2',
                'omset',
                'nominalperalihan',
                'totalomset',
                'statuscontainer_id',
                'gudang',
                'trado_id',
                'supir_id',
                'tradolookup',
                'supirlookup',
                'gandengan_id',
                'gandenganid',
                'statuslongtrip',
                'statuslongtriptext',
                'statuslangsir',
                'statuslangsirtext',
                'statusperalihan',
                'statusperalihantext',
                'statusritasiomset',
                'statusapprovaleditsuratpengantar',
                'statusapprovaleditsuratpengantartext',
                'statusapprovalbiayatitipanemkl',
                'statusapprovalbiayatitipanemkltext',
                'tarif_id',
                'mandortrado_id',
                'mandorsupir_id',
                'statustolakan',
                'statustolakantext',
                'statusgudangsama',
                'statusgudangsamatext',
                'statusbatalmuat',
                'statusbatalmuattext',
                'userapprovaleditsuratpengantar',
                'userapprovalbiayatitipanemkl',
                'tglapprovaleditsuratpengantar',
                'tglbataseditsuratpengantar',
                'tglapprovalbiayatitipanemkl',
                'modifiedby',
                'created_at',
                'updated_at',
                'gajisupir_nobukti',
                'prosesgajisupir_nobukti',
                'invoice_nobukti',
                'statusgajisupir',
                'statusgajisupirtext',
                'statusinvoice',
                'statusinvoicetext',
                'tgldariorderantrucking',
                'tglsampaiorderantrucking',
                'tgldarigajisupirheader',
                'tglsampaigajisupirheader',
                'tgldariinvoiceheader',
                'tglsampaiinvoiceheader',
                'statusapprovalbiayaextra',
                'statusapprovalbiayaextratext',
                'userapprovalbiayaextra',
                'tglapprovalbiayaextra',
                'tglbatasapprovalbiayaextra',
            ], $query);

            $tempsuratpengantar = $tempsuratpengantarlist;
            // dd($tempsuratpengantarlist);
        } else {
            // dd($class,$user);
            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel with (readuncommitted)")
            )
                ->select(
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            $tempsuratpengantar = $querydata->namatabel;
        }

        $query = db::table($tempsuratpengantar)->from(db::raw($tempsuratpengantar  . " suratpengantar "))
            ->select(
                'suratpengantar.id',
                'suratpengantar.nobukti',
                'suratpengantar.jobtrucking',
                'suratpengantar.tglbukti',
                'suratpengantar.nosp',
                'suratpengantar.tglsp',
                'suratpengantar.nojob',
                'suratpengantar.nojob2',
                'suratpengantar.pelanggan_id',
                'suratpengantar.pelangganid',
                'suratpengantar.keterangan',
                'suratpengantar.dari_id',
                'suratpengantar.sampai_id',
                'suratpengantar.sampaiid',
                'suratpengantar.penyesuaian',
                'suratpengantar.gajisupir',
                'suratpengantar.jarak',
                'suratpengantar.agen_id',
                'suratpengantar.jenisorder_id',
                'suratpengantar.container_id',
                'suratpengantar.nocont',
                'suratpengantar.noseal',
                'suratpengantar.nocont2',
                'suratpengantar.noseal2',
                'suratpengantar.omset',
                'suratpengantar.nominalperalihan',
                'suratpengantar.totalomset',
                'suratpengantar.statuscontainer_id',
                'suratpengantar.gudang',
                'suratpengantar.trado_id',
                'suratpengantar.supir_id',
                'suratpengantar.tradolookup',
                'suratpengantar.supirlookup',
                'suratpengantar.gandengan_id',
                'suratpengantar.gandenganid',
                'suratpengantar.statuslongtrip',
                'suratpengantar.statuslangsir',
                'suratpengantar.statusperalihan',
                'suratpengantar.statusritasiomset',
                'suratpengantar.statusapprovaleditsuratpengantar',
                'suratpengantar.statusapprovalbiayatitipanemkl',
                'suratpengantar.tarif_id',
                'suratpengantar.mandortrado_id',
                'suratpengantar.mandorsupir_id',
                'suratpengantar.statustolakan',
                'suratpengantar.statusgudangsama',
                'suratpengantar.statusbatalmuat',
                'suratpengantar.userapprovaleditsuratpengantar',
                'suratpengantar.userapprovalbiayatitipanemkl',
                'suratpengantar.tglapprovaleditsuratpengantar',
                'suratpengantar.tglbataseditsuratpengantar',
                'suratpengantar.tglapprovalbiayatitipanemkl',
                'suratpengantar.modifiedby',
                'suratpengantar.created_at',
                'suratpengantar.updated_at',
                'suratpengantar.gajisupir_nobukti',
                'suratpengantar.prosesgajisupir_nobukti',
                'suratpengantar.invoice_nobukti',
                'suratpengantar.statusgajisupir',
                'suratpengantar.statusinvoice',
                'suratpengantar.tgldariorderantrucking',
                'suratpengantar.tglsampaiorderantrucking',
                'suratpengantar.tgldarigajisupirheader',
                'suratpengantar.tglsampaigajisupirheader',
                'suratpengantar.tgldariinvoiceheader',
                'suratpengantar.tglsampaiinvoiceheader',
                'suratpengantar.statusapprovalbiayaextra',
                'suratpengantar.userapprovalbiayaextra',
                'suratpengantar.tglapprovalbiayaextra',
                'suratpengantar.tglbatasapprovalbiayaextra',


            )
            ->join(db::raw("suratpengantar c with (readuncommitted)"), 'suratpengantar.nobukti', 'c.nobukti');

        // dd($query->get());
        if (request()->tgldari) {
            $query->whereBetween('suratpengantar.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        // dd('test');
        // dd(request()->nobukti);
        if (request()->nobukti) {
            // dd($nobuktitrip);
            $queryutama = db::table($tempsuratpengantar)->from(db::raw($tempsuratpengantar . " a with (readuncommitted)"))
                ->select(
                    'a.jobtrucking',
                    'a.nocont',
                    'a.nocont2',
                    'a.noseal',
                    'a.noseal2',
                    'a.nojob',
                    'a.nojob2',
                    'c.pelanggan_id',
                    'a.penyesuaian',
                    'c.container_id',
                    'c.trado_id',
                    'c.gandengan_id',
                    'c.agen_id',
                    'c.jenisorder_id',
                    'c.tarif_id',
                    'c.sampai_id',
                    'c.statuslongtrip',
                    'b.statusgerobak'
                )
                ->join(db::raw("suratpengantar c with (readuncommitted)"), 'a.nobukti', 'c.nobukti')
                ->join(db::raw("trado b with (readuncommitted)"), 'c.trado_id', 'b.id')
                ->where('a.nobukti', $nobuktitrip)
                ->first();
            // dd($queryutama);

            $pelanggan_idtrip = $queryutama->pelanggan_id ?? 0;
            $penyesuaiantrip = $queryutama->penyesuaian ?? '';
            $container_idtrip = $queryutama->container_id ?? 0;
            $trado_idtrip = $queryutama->trado_id ?? 0;
            $gandengan_idtrip = $queryutama->gandengan_id ?? 0;
            $agen_idtrip = $queryutama->agen_id ?? 0;
            $jenisorder_idtrip = $queryutama->jenisorder_id ?? 0;
            $tarif_idtrip = $queryutama->tarif_id ?? 0;
            $statusgerobaktrip = $queryutama->statusgerobak ?? 0;
            $noconttrip = $queryutama->nocont ?? '';
            $nocont2trip = $queryutama->nocont2 ?? '';
            $nosealtrip = $queryutama->noseal ?? '';
            $noseal2trip = $queryutama->noseal2 ?? '';
            $nojobtrip = $queryutama->nojob ?? '';
            $nojob2trip = $queryutama->nojob2 ?? '';
            $jobtruckingtrip = $queryutama->jobtrucking ?? '';
            $statuslongtrip = $queryutama->statuslongtrip ?? 0;
            $sampai_id = $queryutama->sampai_id ?? 0;


            // dd($pelanggan_idtrip, $penyesuaiantrip, $container_idtrip, $gandengan_idtrip, $agen_idtrip, $jenisorder_idtrip, $tarif_idtrip);

            // dd($nobuktitrip);

            if ($statuslongtrip == 65) {
                $query->whereRaw("(isnull(c.pelanggan_id,0)=" . $pelanggan_idtrip);
                $query->whereRaw("isnull(c.container_id,0)=" . $container_idtrip);
                $query->whereRaw("isnull(c.gandengan_id,0)=" . $gandengan_idtrip);
                $query->whereRaw("isnull(c.agen_id,0)=" . $agen_idtrip);
                $query->whereRaw("isnull(c.jenisorder_id,0)=" . $jenisorder_idtrip);
                $query->whereRaw("isnull(c.dari_id,0)=" . $sampai_id . ")  or ( c.nobukti='" . $nobuktitrip . "')");
            } else {

                $tripasal = db::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
                    ->select(
                        'a.nobukti_tripasal'
                    )
                    ->where('a.nobukti', $nobuktitrip)
                    ->first()->nobukti_tripasal ?? '';
                // dd($tripasal);
                $query->whereRaw("(isnull(c.pelanggan_id,0)=" . $pelanggan_idtrip);
                $query->whereRaw("isnull(c.penyesuaian,'')='" . $penyesuaiantrip . "'");
                $query->whereRaw("isnull(c.container_id,0)=" . $container_idtrip);
                $query->whereRaw("isnull(c.gandengan_id,0)=" . $gandengan_idtrip);
                $query->whereRaw("isnull(c.agen_id,0)=" . $agen_idtrip);
                $query->whereRaw("isnull(c.jenisorder_id,0)=" . $jenisorder_idtrip . ") ");
                $query->whereRaw("(isnull(c.tarif_id,0)=" . $tarif_idtrip . " or (c.nobukti='" . $tripasal . "' and isnull(c.nobukti_tripasal,'')<>'' ))");
                // $query->OrwhereRaw("c.nobukti_tripasal='" . $nobuktitrip . "'");
                // dd($query->tosql());
            }
            // dd($query->get());

        }

        if (request()->pengeluarantruckingheader != '') {
            // $query->whereNotIn('suratpengantar.nobukti', function ($query) {
            //     $query->select(DB::raw('DISTINCT pengeluarantruckingdetail.suratpengantar_nobukti'))
            //         ->from('pengeluarantruckingdetail')
            //         ->whereNotNull('pengeluarantruckingdetail.suratpengantar_nobukti')
            //         ->where('pengeluarantruckingdetail.suratpengantar_nobukti', '!=', '');
            // });
            if (request()->jenisorder_id != null) {
                $query->where('c.jenisorder_id', request()->jenisorder_id);
            }
        }

        if (request()->biayatambahan != '') {

            $tempApprovalTambahan = '##tempApprovalTambahan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempApprovalTambahan, function ($table) {
                $table->integer('suratpengantar_id')->nullable();
                $table->integer('statusapproval')->nullable();
            });

            $parameter = new Parameter();
            $idstatusapproval = $parameter->cekId('STATUS APPROVAL', 'STATUS APPROVAL', 'APPROVAL') ?? 0;
            $idstatusnonapproval = $parameter->cekId('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL') ?? 0;
            $queryapprovaltambahan = db::table('suratpengantar')->from(db::raw("suratpengantar a with (readuncommitted)"))
                ->select(
                    'b.suratpengantar_id',
                    db::raw("max(b.statusapproval) as statusapproval")
                )
                ->join(db::raw("suratpengantarbiayatambahan b with (readuncommitted)"), 'a.id', 'b.suratpengantar_id')
                ->where('b.statusapproval', $idstatusnonapproval)
                ->whereBetween('a.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
                ->groupby('b.suratpengantar_id');

            DB::table($tempApprovalTambahan)->insertUsing(['suratpengantar_id', 'statusapproval'], $queryapprovaltambahan);


            $queryapprovaltambahan = db::table('suratpengantar')->from(db::raw("suratpengantar a with (readuncommitted)"))
                ->select(
                    'b.suratpengantar_id',
                    db::raw("max(b.statusapproval) as statusapproval")
                )
                ->join(db::raw("suratpengantarbiayatambahan b with (readuncommitted)"), 'a.id', 'b.suratpengantar_id')
                ->leftjoin(db::raw($tempApprovalTambahan . " c"), 'b.id', 'c.suratpengantar_id')
                ->where('b.statusapproval', $idstatusapproval)
                ->whereBetween('a.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
                ->whereRaw("isnull(c.suratpengantar_id,0)=0")
                ->groupby('b.suratpengantar_id');

            DB::table($tempApprovalTambahan)->insertUsing(['suratpengantar_id', 'statusapproval'], $queryapprovaltambahan);


            $query->addSelect('paramapp.memo as statusapproval')
                ->join(DB::raw("$tempApprovalTambahan as approvaltambahan with (readuncommitted)"), 'suratpengantar.id', 'approvaltambahan.suratpengantar_id')
                ->join(DB::raw("parameter  as paramapp"), 'approvaltambahan.statusapproval', 'paramapp.id');
            // dd('test');

            $tempTambahan = '##tempTambahan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempTambahan, function ($table) {
                $table->integer('suratpengantar_id')->nullable();
                $table->longText('ketextra')->nullable();
                $table->double('biayaextra', 15, 2)->nullable();
                $table->double('biayatagih', 15, 2)->nullable();
                $table->longText('ketextratagih')->nullable();
            });
            $tambahan = DB::table("suratpengantarbiayatambahan")->from(DB::raw("suratpengantarbiayatambahan with (readuncommitted)"))
                ->select(DB::raw("suratpengantar_id, STRING_AGG(cast(keteranganbiaya+' ('+ FORMAT(nominal,'#,#0.00')+')'  as nvarchar(max)),', ') as ketextra, sum(nominal) as biayaextra, sum(nominaltagih) as biayatagih,
                STRING_AGG(cast(keteranganbiaya+' ('+ FORMAT(nominaltagih,'#,#0.00')+')'  as nvarchar(max)),', ') as ketextratagih"))
                ->join(db::raw("suratpengantar b with (readuncommitted)"), 'suratpengantarbiayatambahan.suratpengantar_id', 'b.id')
                ->whereBetween('b.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
                ->groupBy('suratpengantar_id');
            DB::table($tempTambahan)->insertUsing(['suratpengantar_id', 'ketextra', 'biayaextra', 'biayatagih', 'ketextratagih'], $tambahan);
            $query->addSelect('suratpengantarbiayatambahan.ketextra', 'suratpengantarbiayatambahan.biayaextra', 'suratpengantarbiayatambahan.biayatagih', 'suratpengantarbiayatambahan.ketextratagih')
                ->join(DB::raw("$tempTambahan as suratpengantarbiayatambahan with (readuncommitted)"), 'suratpengantar.id', 'suratpengantarbiayatambahan.suratpengantar_id');
        }
        // if (request()->jenisorder_id != null) {
        //     $query->where('suratpengantar.jenisorder_id', request()->jenisorder_id);
        // }
        $isGudangSama = request()->isGudangSama ?? '';
        if ($isGudangSama == 'true') {

            if ($gudangsama == 204) {
                $tempTripAsal = '##tempTripAsal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempTripAsal, function ($table) {
                    $table->string('nobukti_tripasal', 50)->nullable();
                });

                $querytripasal = DB::table('suratpengantar')->from(db::raw("suratpengantar a with (readuncommitted)"))
                    ->select(
                        'a.nobukti_tripasal'
                    )
                    ->whereraw("isnull(a.nobukti_tripasal,'')<>''")
                    ->groupBY('a.nobukti_tripasal');

                DB::table($tempTripAsal)->insertUsing([
                    'nobukti_tripasal',
                ],  $querytripasal);

                // dd(db::table($tempTripAsal)->get());

                $container_id = request()->container_id ?? 0;
                $agen_id = request()->agen_id ?? 0;
                $upah_id = request()->upah_id ?? 0;
                $pelanggan_id = request()->pelanggan_id ?? 0;
                $trado_id = request()->trado_id ?? 0;
                $supir_id = request()->supir_id ?? 0;
                $query->leftjoin(db::raw($tempTripAsal . " a"), 'suratpengantar.nobukti', 'a.nobukti_tripasal')
                    ->whereRaw("c.jenisorder_id in (2,3)")
                    ->where('c.container_id', $container_id)
                    ->where('c.agen_id', $agen_id)
                    ->where('c.upah_id', $upah_id)
                    ->where('c.trado_id', $trado_id)
                    ->whereRaw("isnull(a.nobukti_tripasal,'')=''")
                    ->where('c.pelanggan_id', $pelanggan_id);
            }
        }
        if ($isGudangSama == 'false') {
            if ($longtrip == 66) {
                $parameter = new Parameter();
                $idkandang = $parameter->cekText('KANDANG', 'KANDANG') ?? 0;
                if ($idkandang == 0) {
                    goto bukankandang;
                }
                if ($dari_id == $idkandang) {
                    $tempJobAwal = '##tempJobAwal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                    Schema::create($tempJobAwal, function ($table) {
                        $table->string('jobtrucking', 50)->nullable();
                        $table->string('nobukti', 50)->nullable();
                    });

                    $queryJobtruckingAwal = DB::table('suratpengantar')->from(db::raw("suratpengantar a with (readuncommitted)"))
                        ->select(
                            'a.jobtrucking',
                            'a.nobukti'
                        )
                        ->whereraw("a.dari_id = 1")
                        ->whereraw("a.sampai_id != $idkandang");

                    DB::table($tempJobAwal)->insertUsing([
                        'jobtrucking',
                        'nobukti'
                    ],  $queryJobtruckingAwal);
                    $queryJobtruckingAwal = DB::table('saldosuratpengantar')->from(db::raw("saldosuratpengantar a with (readuncommitted)"))
                        ->select(
                            'a.jobtrucking',
                            'a.nobukti'
                        )
                        ->whereraw("a.dari_id = 1")
                        ->whereraw("a.sampai_id != $idkandang");

                    DB::table($tempJobAwal)->insertUsing([
                        'jobtrucking',
                        'nobukti'
                    ],  $queryJobtruckingAwal);


                    $tempJobAkhir = '##tempJobAkhir' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                    Schema::create($tempJobAkhir, function ($table) {
                        $table->string('jobtrucking', 50)->nullable();
                        $table->string('nobukti', 50)->nullable();
                    });

                    $queryJobtruckingAkhir = DB::table('suratpengantar')->from(db::raw("suratpengantar a with (readuncommitted)"))
                        ->select(
                            'a.jobtrucking',
                            'a.nobukti'
                        )
                        ->whereraw("a.sampai_id = 1");

                    DB::table($tempJobAkhir)->insertUsing([
                        'jobtrucking',
                        'nobukti'
                    ],  $queryJobtruckingAkhir);

                    $queryJobtruckingAkhir = DB::table('saldosuratpengantar')->from(db::raw("saldosuratpengantar a with (readuncommitted)"))
                        ->select(
                            'a.jobtrucking',
                            'a.nobukti'
                        )
                        ->whereraw("a.sampai_id = 1");

                    DB::table($tempJobAkhir)->insertUsing([
                        'jobtrucking',
                        'nobukti'
                    ],  $queryJobtruckingAkhir);

                    $tempJobFinal = '##tempJobFinal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                    Schema::create($tempJobFinal, function ($table) {
                        $table->string('jobtrucking', 50)->nullable();
                        $table->string('nobukti', 50)->nullable();
                    });

                    $queryJobTruckingFinal = DB::table($tempJobAwal)->from(db::raw("$tempJobAwal as A"))
                        ->select('A.jobtrucking', 'A.nobukti')
                        ->leftjoin(db::raw($tempJobAkhir . " as B"), 'A.jobtrucking', 'B.jobtrucking')
                        ->whereRaw("isnull(B.jobtrucking,'')='' ");

                    DB::table($tempJobFinal)->insertUsing([
                        'jobtrucking',
                        'nobukti'
                    ],  $queryJobTruckingFinal);

                    $tempTripAsal = '##tempTripAsal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                    Schema::create($tempTripAsal, function ($table) {
                        $table->string('nobukti_tripasal', 50)->nullable();
                    });

                    $querytripasal = DB::table('suratpengantar')->from(db::raw("suratpengantar a with (readuncommitted)"))
                        ->select(
                            'a.nobukti_tripasal'
                        )
                        ->whereraw("isnull(a.nobukti_tripasal,'')<>''");

                    $idtrip = request()->idTrip ?? 0;
                    if ($idtrip != 0) {
                        $querytripasal->where('id', '<>', $idtrip);
                    }
                    $querytripasal->groupBY('a.nobukti_tripasal');

                    DB::table($tempTripAsal)->insertUsing([
                        'nobukti_tripasal',
                    ],  $querytripasal);

                    $tempGetJobTripasal = '##tempGetJobTripasal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                    Schema::create($tempGetJobTripasal, function ($table) {
                        $table->string('jobtrucking', 50)->nullable();
                    });
                    $querygetjobtripasal = DB::table('suratpengantar')->from(db::raw("suratpengantar a with (readuncommitted)"))
                        ->select(
                            'a.jobtrucking'
                        )
                        ->join(DB::raw("$tempTripAsal as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti_tripasal');

                    DB::table($tempGetJobTripasal)->insertUsing([
                        'jobtrucking',
                    ],  $querygetjobtripasal);


                    $tempJobSelesai = '##tempJobSelesai' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                    Schema::create($tempJobSelesai, function ($table) {
                        $table->string('jobtrucking', 50)->nullable();
                        $table->string('nobukti', 50)->nullable();
                    });

                    $queryJobTruckingSelesai = DB::table($tempJobFinal)->from(db::raw("$tempJobFinal as A"))
                        ->select('A.jobtrucking', 'A.nobukti')
                        ->leftjoin(db::raw($tempGetJobTripasal . " as B"), 'A.jobtrucking', 'B.jobtrucking')
                        ->whereRaw("isnull(B.jobtrucking,'')='' ");

                    DB::table($tempJobSelesai)->insertUsing([
                        'jobtrucking',
                        'nobukti'
                    ],  $queryJobTruckingSelesai);


                    $tempJobKandang = '##tempJobKandang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                    Schema::create($tempJobKandang, function ($table) {
                        $table->string('jobtrucking', 50)->nullable();
                        $table->string('nobukti', 50)->nullable();
                    });
                    if ($sampai_id != 1) {
                        $queryJobKandang = DB::table("suratpengantar")->from(DB::raw("suratpengantar as a with (readuncommitted)"))
                            ->select('a.jobtrucking', 'a.nobukti')
                            ->join(DB::raw("$tempJobFinal as b with (readuncommitted)"), 'a.jobtrucking', 'b.jobtrucking')
                            ->where('a.statuslongtrip', 66)
                            ->where('a.sampai_id', $idkandang)
                            ->where('a.dari_id', '!=', 1);
                        DB::table($tempJobKandang)->insertUsing([
                            'jobtrucking',
                            'nobukti'
                        ],  $queryJobKandang);
                    }
                    $query
                        ->join(db::raw($tempJobKandang . " a"), 'suratpengantar.nobukti', 'a.nobukti')
                        ->where('c.statuscontainer_id', '!=', 3)
                        ->where('c.gandengan_id', $gandengan_id);
                } else {
                    bukankandang:
                    $idtrip = request()->idTrip ?? 0;
                    $queryGetLongtrip = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
                        ->select('nobukti', 'jobtrucking')
                        ->where('statuslongtrip', 65);

                    $tempLongtripawal = '##tempLongtripawal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                    Schema::create($tempLongtripawal, function ($table) {
                        $table->string('nobukti', 50)->nullable();
                        $table->string('jobtrucking', 50)->nullable();
                    });
                    DB::table($tempLongtripawal)->insertUsing([
                        'nobukti',
                        'jobtrucking',
                    ],  $queryGetLongtrip);

                    $queryGetPulangLongtrip = DB::table("suratpengantar")->from(DB::raw("suratpengantar as a with (readuncommitted)"))
                        ->select('a.nobukti', 'a.jobtrucking')
                        ->join(DB::raw("$tempLongtripawal as longtrip with (readuncommitted)"), 'a.jobtrucking', 'longtrip.jobtrucking')
                        ->whereRaw("a.statuslongtrip = 66 ");
                    if ($idtrip != 0) {
                        $queryGetPulangLongtrip->where('id', '<>', $idtrip);
                    }
                    $tempLongtripakhir = '##tempLongtripakhir' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                    Schema::create($tempLongtripakhir, function ($table) {
                        $table->string('nobukti', 50)->nullable();
                        $table->string('jobtrucking', 50)->nullable();
                    });
                    DB::table($tempLongtripakhir)->insertUsing([
                        'nobukti',
                        'jobtrucking',
                    ],  $queryGetPulangLongtrip);

                    $queryFinalLongtrip = DB::table($tempLongtripawal)->from(DB::raw("$tempLongtripawal as a with (readuncommitted)"))
                        ->select('a.nobukti', 'a.jobtrucking')
                        ->leftJoin(DB::raw("$tempLongtripakhir as b with (readuncommitted)"), 'a.jobtrucking', 'b.jobtrucking')
                        ->whereRaw("isnull(b.nobukti,'')=''");

                    $tempLongtripFinal = '##tempLongtripFinal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                    Schema::create($tempLongtripFinal, function ($table) {
                        $table->string('nobukti', 50)->nullable();
                        $table->string('jobtrucking', 50)->nullable();
                    });
                    DB::table($tempLongtripFinal)->insertUsing([
                        'nobukti',
                        'jobtrucking',
                    ],  $queryFinalLongtrip);
                    $tempTripAsal = '##tempTripAsal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                    Schema::create($tempTripAsal, function ($table) {
                        $table->string('nobukti_tripasal', 50)->nullable();
                    });

                    $querytripasal = DB::table('suratpengantar')->from(db::raw("suratpengantar a with (readuncommitted)"))
                        ->select(
                            'a.nobukti_tripasal'
                        )
                        ->whereraw("isnull(a.nobukti_tripasal,'')<>''");
                    if ($idtrip != 0) {
                        $querytripasal->where('id', '<>', $idtrip);
                    }
                    $querytripasal->groupBY('a.nobukti_tripasal');

                    DB::table($tempTripAsal)->insertUsing([
                        'nobukti_tripasal',
                    ],  $querytripasal);


                    $jenisorder_id = request()->jenisorder_id ?? 0;
                    $container_id = request()->container_id ?? 0;
                    $agen_id = request()->agen_id ?? 0;

                    $query
                        ->join(db::raw($tempLongtripFinal . " jobfinal"), 'suratpengantar.jobtrucking', 'jobfinal.jobtrucking')
                        ->leftjoin(db::raw($tempTripAsal . " a"), 'suratpengantar.nobukti', 'a.nobukti_tripasal')
                        ->whereRaw("isnull(a.nobukti_tripasal,'')=''")
                        ->where('c.jenisorder_id', $jenisorder_id)
                        ->where('c.agen_id', $agen_id)
                        ->where('c.statuscontainer_id', '!=', 3)
                        ->where('c.container_id', $container_id);

                    if ($idtrip != 0) {
                        $query->where('suratpengantar.id', '<>', $idtrip);
                    }
                    // $tempTripAsal = '##tempTripAsal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                    // Schema::create($tempTripAsal, function ($table) {
                    //     $table->string('nobukti_tripasal', 50)->nullable();
                    // });

                    // $querytripasal = DB::table('suratpengantar')->from(db::raw("suratpengantar a with (readuncommitted)"))
                    //     ->select(
                    //         'a.nobukti_tripasal'
                    //     )
                    //     ->whereraw("isnull(a.nobukti_tripasal,'')<>''");
                    // if ($idtrip != 0) {
                    //     $querytripasal->where('id', '<>', $idtrip);
                    // }
                    // $querytripasal->groupBY('a.nobukti_tripasal');

                    // DB::table($tempTripAsal)->insertUsing([
                    //     'nobukti_tripasal',
                    // ],  $querytripasal);
                    // $pelanggan_id = request()->pelanggan_id ?? 0;
                    // $trado_id = request()->trado_id ?? 0;
                    // $upah_id = request()->upah_id ?? 0;

                    // $getKota = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))->where('id', $upah_id)->first();
                    // $kotaSampai = 0;
                    // if ($getKota != '') {
                    //     $kotaSampai = $getKota->kotasampai_id;
                    // }

                    // $query->leftjoin(db::raw($tempTripAsal . " a"), 'suratpengantar.nobukti', 'a.nobukti_tripasal')
                    //     ->whereRaw("isnull(a.nobukti_tripasal,'')=''")
                    //     ->where('suratpengantar.dari_id', '!=', 1)
                    //     ->where('suratpengantar.statuslongtrip', 65)
                    //     ->where('suratpengantar.trado_id', $trado_id)
                    //     ->where('suratpengantar.sampai_id', $kotaSampai)
                    //     ->where('suratpengantar.pelanggan_id', $pelanggan_id)
                    //     ->where('suratpengantar.jenisorder_id', $jenisorder_id)
                    //     ->where('suratpengantar.container_id', $container_id);

                }
            } else {

                $idtrip = request()->idTrip ?? 0;

                $tempTripAsal = '##tempTripAsal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempTripAsal, function ($table) {
                    $table->string('nobukti_tripasal', 50)->nullable();
                });

                $querytripasal = DB::table('suratpengantar')->from(db::raw("suratpengantar a with (readuncommitted)"))
                    ->select(
                        'a.nobukti_tripasal'
                    )
                    ->whereraw("isnull(a.nobukti_tripasal,'')<>''");
                if ($idtrip != 0) {
                    $querytripasal->where('id', '<>', $idtrip);
                }
                $querytripasal->groupBY('a.nobukti_tripasal');

                DB::table($tempTripAsal)->insertUsing([
                    'nobukti_tripasal',
                ],  $querytripasal);

                $tempJobAwal = '##tempJobAwal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempJobAwal, function ($table) {
                    $table->string('jobtrucking', 50)->nullable();
                });

                $queryJobtruckingAwal = DB::table('suratpengantar')->from(db::raw("suratpengantar a with (readuncommitted)"))
                    ->select(
                        'a.jobtrucking'
                    )
                    ->whereraw("a.dari_id = 1");

                DB::table($tempJobAwal)->insertUsing([
                    'jobtrucking',
                ],  $queryJobtruckingAwal);
                $queryJobtruckingAwal = DB::table('saldosuratpengantar')->from(db::raw("saldosuratpengantar a with (readuncommitted)"))
                    ->select(
                        'a.jobtrucking'
                    )
                    ->whereraw("a.dari_id = 1");

                DB::table($tempJobAwal)->insertUsing([
                    'jobtrucking',
                ],  $queryJobtruckingAwal);


                $idkandang = (new Parameter())->cekText('KANDANG', 'KANDANG') ?? 0;
                $pelabuhan = '1,' . $idkandang;
                $tempJobAkhir = '##tempJobAkhir' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempJobAkhir, function ($table) {
                    $table->string('jobtrucking', 50)->nullable();
                });

                $queryJobtruckingAkhir = DB::table('suratpengantar')->from(db::raw("suratpengantar a with (readuncommitted)"))
                    ->select(
                        'a.jobtrucking'
                    )
                    ->whereraw("a.sampai_id in ($pelabuhan)");

                DB::table($tempJobAkhir)->insertUsing([
                    'jobtrucking',
                ],  $queryJobtruckingAkhir);

                $queryJobtruckingAkhir = DB::table('saldosuratpengantar')->from(db::raw("saldosuratpengantar a with (readuncommitted)"))
                    ->select(
                        'a.jobtrucking'
                    )
                    ->whereraw("a.sampai_id in ($pelabuhan)");

                DB::table($tempJobAkhir)->insertUsing([
                    'jobtrucking',
                ],  $queryJobtruckingAkhir);

                $tempJobFinal = '##tempJobFinal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempJobFinal, function ($table) {
                    $table->string('jobtrucking', 50)->nullable();
                });

                $queryJobTruckingFinal = DB::table($tempJobAwal)->from(db::raw("$tempJobAwal as A"))
                    ->select('A.jobtrucking')
                    ->leftjoin(db::raw($tempJobAkhir . " as B"), 'A.jobtrucking', 'B.jobtrucking')
                    ->whereRaw("isnull(B.jobtrucking,'')='' ");

                DB::table($tempJobFinal)->insertUsing([
                    'jobtrucking',
                ],  $queryJobTruckingFinal);
                $queryLongtrip = DB::table('suratpengantar')->from(db::raw("suratpengantar a with (readuncommitted)"))
                    ->select(
                        'a.jobtrucking'
                    )
                    ->whereraw("a.statuslongtrip=65");
                DB::table($tempJobFinal)->insertUsing([
                    'jobtrucking',
                ],  $queryLongtrip);
                $container_id = request()->container_id ?? 0;
                $agen_id = request()->agen_id ?? 0;
                $idTinggalGandengan = (new Parameter())->cekId('STATUS GANDENGAN', 'STATUS GANDENGAN', 'TINGGAL GANDENGAN') ?? 0;
                //         dd(
                //     DB::table($tempJobFinal)->get(),
                // DB::table($tempTripAsal)->get());
                $cabang = (new Parameter())->cekText('CABANG', 'CABANG');
                $query
                    ->leftjoin(db::raw($tempJobFinal . " jobfinal"), 'suratpengantar.jobtrucking', 'jobfinal.jobtrucking')
                    ->leftjoin(db::raw($tempTripAsal . " a"), 'suratpengantar.nobukti', 'a.nobukti_tripasal')
                    ->whereRaw("isnull(a.nobukti_tripasal,'')=''")
                    ->whereRaw("isnull(jobfinal.jobtrucking,'')!='' ")
                    ->where('c.sampai_id', '!=', 1)
                    ->where('c.agen_id', $agen_id)
                    ->where('c.container_id', $container_id)
                    ->where('c.statusgandengan', $idTinggalGandengan);

                if ($cabang == 'SURABAYA') {
                    $query->where('c.statuscontainer_id', 3);
                } else {
                    $query->where('c.statuscontainer_id', '!=', 3);
                }
                if ($idtrip != 0) {
                    $query->where('suratpengantar.id', '<>', $idtrip);
                }
            }
        }

        if ($from == 'tripinap') {

            if ($tglabsensi != '') {
                $query->where('suratpengantar.tglbukti', date('Y-m-d', strtotime($tglabsensi)));
            }
            if ($supir_id != '') {
                $query->where('c.supir_id', $supir_id);
            }
            if ($trado_id != '') {
                $query->where('c.trado_id', $trado_id);
            }
        }

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        // if ($clearfilter==false) {
        $this->filter($query);
        // }

        $this->paginate($query);
        // dd($query->tosql());
        $data = $query->get();
        // dd('test');
        $this->totalJarak = $data->sum('jarak');

        return $data;
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->date('tglbukti')->nullable();
            $table->unsignedBigInteger('statusjeniskendaraan')->nullable();
            $table->unsignedBigInteger('statuslongtrip')->nullable();
            $table->unsignedBigInteger('statusperalihan')->nullable();
            $table->unsignedBigInteger('statusritasiomset')->nullable();
            $table->unsignedBigInteger('statusgudangsama')->nullable();
            $table->unsignedBigInteger('statusbatalmuat')->nullable();
            $table->unsignedBigInteger('statusgandengan')->nullable();
            $table->unsignedBigInteger('statusupahzona')->nullable();
            $table->unsignedBigInteger('statuslangsir')->nullable();
            $table->unsignedBigInteger('statuskandang')->nullable();
            $table->unsignedBigInteger('statuspenyesuaian')->nullable();
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
        $status = Parameter::from(

            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS KANDANG')
            ->where('subgrp', '=', 'STATUS KANDANG')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatuskandang = $status->id ?? 0;

        $status = Parameter::from(

            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS JENIS KENDARAAN')
            ->where('subgrp', '=', 'STATUS JENIS KENDARAAN')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusjeniskendaraan = $status->id ?? 0;

        $status = Parameter::from(

            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS PENYESUAIAN')
            ->where('subgrp', '=', 'STATUS PENYESUAIAN')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatuspenyesuaian = $status->id ?? 0;

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
                "statuskandang" => $iddefaultstatuskandang,
                "statusjeniskendaraan" => $iddefaultstatusjeniskendaraan,
                "statuspenyesuaian" => $iddefaultstatuspenyesuaian,
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
                'statuskandang',
                'statusjeniskendaraan',
                'statuspenyesuaian'
            );

        $data = $query->first();

        return $data;
    }

    public function findAll($id)
    {
        $jenisTangki = DB::table('parameter')->from(DB::raw("parameter as a with (readuncommitted)"))
            ->select('a.id')
            ->where('a.grp', '=', 'STATUS JENIS KENDARAAN')
            ->where('a.subgrp', '=', 'STATUS JENIS KENDARAAN')
            ->where('a.text', '=', 'TANGKI')
            ->first();

        $get = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->select('statusupahzona', 'statusjeniskendaraan', 'tariftangki_id', 'upahsupirtangki_id', 'triptangki_id')->where('id', $id)->first();

        if ($get->statusjeniskendaraan == $jenisTangki->id) {
            $getHargaTon = DB::table("upahsupirtangkirincian")->from(DB::raw("upahsupirtangkirincian with (readuncommitted)"))->where('upahsupirtangki_id', $get->upahsupirtangki_id)->where('triptangki_id', $get->triptangki_id)->first()->nominalsupir ?? 0;
            $getHargaTonTarif = DB::table("tariftangki")->from(DB::raw("tariftangki with (readuncommitted)"))->where('id', $get->tariftangki_id)->first()->nominal ?? 0;
            $data = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
                ->select(
                    'suratpengantar.id',
                    'suratpengantar.nobukti',
                    'suratpengantar.tglbukti',
                    'suratpengantar.jobtrucking',
                    'suratpengantar.statuskandang',
                    'suratpengantar.statuslongtrip',
                    DB::raw("(case when isnull(suratpengantar.statuslangsir,0)=0 then 80 else
                        suratpengantar.statuslangsir
                    end) as statuslangsir"),
                    DB::raw("(case when isnull(suratpengantar.statuspenyesuaian,'')='' then
                        (case when suratpengantar.penyesuaian='' then 663 ELSE 662 end) else
                        suratpengantar.statuspenyesuaian
                    end) as statuspenyesuaian"),
                    'suratpengantar.nosp',
                    'suratpengantar.trado_id',
                    'trado.kodetrado as trado',
                    'trado.nominalplusborongan',
                    'suratpengantar.supir_id',
                    'supir.namasupir as supir',
                    'suratpengantar.dari_id',
                    'kotadari.kodekota as dari',
                    'suratpengantar.gandengan_id',
                    'gandengan.keterangan as gandengan',
                    'suratpengantar.nocont',
                    'suratpengantar.noseal',
                    'suratpengantar.statusperalihan',
                    DB::raw("(case when suratpengantar.persentaseperalihan IS NULL then 0 else suratpengantar.persentaseperalihan end) as persentaseperalihan"),
                    DB::raw("$getHargaTon as hargaperton"),
                    DB::raw("$getHargaTonTarif as hargapertontarif"),
                    'suratpengantar.omset',
                    'suratpengantar.statusritasiomset',
                    'suratpengantar.nosptagihlain as nosp2',
                    'suratpengantar.statusgudangsama',
                    'suratpengantar.keterangan',
                    'suratpengantar.penyesuaian',
                    'suratpengantar.sampai_id',
                    'suratpengantar.statusjeniskendaraan',
                    'kotasampai.kodekota as sampai',
                    'suratpengantar.nocont2',
                    'suratpengantar.noseal2',
                    'suratpengantar.pelanggan_id',
                    'pelanggan.namapelanggan as pelanggan',
                    'suratpengantar.agen_id',
                    'agen.namaagen as agen',
                    'suratpengantar.tariftangki_id as tarifrincian_id',
                    'tariftangki.tujuan as tarifrincian',
                    DB::raw("(case when suratpengantar.nominalperalihan IS NULL then 0 else suratpengantar.nominalperalihan end) as nominalperalihan"),
                    'suratpengantar.nojob',
                    'suratpengantar.nojob2',
                    'suratpengantar.cabang_id',
                    'suratpengantar.qtyton',
                    'suratpengantar.triptangki_id',
                    'triptangki.keterangan as triptangki',
                    'suratpengantar.gudang',
                    DB::raw("isnull(suratpengantar.statustolakan, 4) as statustolakan"),
                    'suratpengantar.statusbatalmuat',
                    'suratpengantar.statusupahzona',
                    'suratpengantar.statusgandengan',
                    'suratpengantar.gajisupir',
                    'suratpengantar.gajikenek',
                    'suratpengantar.komisisupir',
                    'suratpengantar.upahsupirtangki_id as upah_id',
                    'suratpengantar.nobukti_tripasal',
                    'suratpengantar.statusapprovaleditsuratpengantar',
                    'suratpengantar.statusapprovalbiayatitipanemkl',
                    'kotaupah.kodekota as upah'
                )
                ->leftJoin('kota as kotadari', 'kotadari.id', '=', 'suratpengantar.dari_id')
                ->leftJoin('kota as kotasampai', 'kotasampai.id', '=', 'suratpengantar.sampai_id')
                ->leftJoin('agen', 'suratpengantar.agen_id', 'agen.id')
                ->leftJoin('trado', 'suratpengantar.trado_id', 'trado.id')
                ->leftJoin('supir', 'suratpengantar.supir_id', 'supir.id')
                ->leftJoin('tariftangki', 'suratpengantar.tariftangki_id', 'tariftangki.id')
                ->leftJoin('triptangki', 'suratpengantar.triptangki_id', 'triptangki.id')
                ->leftJoin('upahsupirtangki', 'suratpengantar.upahsupirtangki_id', 'upahsupirtangki.id')
                ->leftJoin('kota as kotaupah', 'kotaupah.id', '=', 'upahsupirtangki.kotasampai_id')
                ->leftJoin('pelanggan', 'suratpengantar.pelanggan_id', 'pelanggan.id')
                ->leftJoin('gandengan', 'suratpengantar.gandengan_id', 'gandengan.id')
                ->leftJoin('orderantrucking', 'suratpengantar.jobtrucking', 'orderantrucking.nobukti')
                // ->leftJoin(DB::raw("$tempGaji with (readuncommitted)"), "$tempGaji.id", "suratpengantar.id")

                ->where('suratpengantar.id', $id)->first();
        } else {

            $params = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PENDAPATAN SUPIR')->where('subgrp', 'GAJI KENEK')->first();
            $komisi_gajisupir = $params->text;

            $isKomisiReadonly = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'SURAT PENGANTAR')->where('subgrp', 'KOMISI')->first();

            $getBukanUpahZona = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS UPAH ZONA')->where('text', 'NON UPAH ZONA')->first();

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

            // if ($get->statusupahzona == $getBukanUpahZona->id) {

            $data = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
                ->select(
                    'suratpengantar.id',
                    'suratpengantar.nobukti',
                    'suratpengantar.tglbukti',
                    'suratpengantar.jobtrucking',
                    'suratpengantar.statuskandang',
                    'suratpengantar.statuslongtrip',
                    // 'orderantrucking.statuslangsir',
                    DB::raw("(case when isnull(suratpengantar.statuslangsir,0)=0 then 80 else
                        suratpengantar.statuslangsir
                    end) as statuslangsir"),
                    DB::raw("(case when isnull(suratpengantar.statuspenyesuaian,'')='' then
                            (case when suratpengantar.penyesuaian='' then 663 ELSE 662 end) else
                            suratpengantar.statuspenyesuaian
                        end) as statuspenyesuaian"),
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
                    'suratpengantar.statusjeniskendaraan',
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
                    DB::raw("isnull(suratpengantar.statustolakan, 4) as statustolakan"),
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
                    db::raw("(case when upahsupir.kotasampai_id=0 then kotasampai.kodekota else kotaupah.kodekota end) as upah")
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
                ->leftJoin('orderantrucking', 'suratpengantar.jobtrucking', 'orderantrucking.nobukti')
                ->leftJoin('saldoorderantrucking', 'suratpengantar.jobtrucking', 'saldoorderantrucking.nobukti')
                // ->leftJoin(DB::raw("$tempGaji with (readuncommitted)"), "$tempGaji.id", "suratpengantar.id")

                ->where('suratpengantar.id', $id)->first();
            // } else {

            //     $data = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
            //         ->select(
            //             'suratpengantar.id',
            //             'suratpengantar.nobukti',
            //             'suratpengantar.tglbukti',
            //             'suratpengantar.jobtrucking',
            //             'suratpengantar.statuskandang',
            //             'suratpengantar.statuslongtrip',
            //             DB::raw("(case when isnull(suratpengantar.statuspenyesuaian,'')='' then
            //                 (case when suratpengantar.penyesuaian='' then 663 ELSE 662 end) else
            //                 suratpengantar.statuspenyesuaian
            //             end) as statuspenyesuaian"),
            //             'suratpengantar.nosp',
            //             'suratpengantar.trado_id',
            //             'trado.kodetrado as trado',
            //             'trado.nominalplusborongan',
            //             'suratpengantar.supir_id',
            //             'supir.namasupir as supir',
            //             'suratpengantar.dari_id',
            //             'kotadari.kodekota as dari',
            //             'suratpengantar.gandengan_id',
            //             'gandengan.kodegandengan as gandengan',
            //             'suratpengantar.container_id',
            //             'container.kodecontainer as container',
            //             'suratpengantar.statusjeniskendaraan',
            //             DB::raw("isnull(suratpengantar.statustolakan, 4) as statustolakan"),
            //             'suratpengantar.nocont',
            //             'suratpengantar.noseal',
            //             'suratpengantar.statusperalihan',
            //             'suratpengantar.persentaseperalihan',
            //             'suratpengantar.statusritasiomset',
            //             'suratpengantar.nosptagihlain as nosp2',
            //             'suratpengantar.statusgudangsama',
            //             'suratpengantar.keterangan',
            //             'suratpengantar.penyesuaian',
            //             'suratpengantar.sampai_id',
            //             'kotasampai.kodekota as sampai',
            //             'suratpengantar.statuscontainer_id',
            //             'statuscontainer.kodestatuscontainer as statuscontainer',
            //             'suratpengantar.nocont2',
            //             'suratpengantar.noseal2',
            //             'suratpengantar.pelanggan_id',
            //             'pelanggan.namapelanggan as pelanggan',
            //             'suratpengantar.agen_id',
            //             'agen.namaagen as agen',
            //             'suratpengantar.jenisorder_id',
            //             'jenisorder.kodejenisorder as jenisorder',
            //             'suratpengantar.tarif_id as tarifrincian_id',
            //             'tarif.tujuan as tarifrincian',
            //             'suratpengantar.nominalperalihan',
            //             'suratpengantar.nojob',
            //             'suratpengantar.nojob2',
            //             'suratpengantar.cabang_id',
            //             'cabang.namacabang as cabang',
            //             'suratpengantar.qtyton',
            //             'suratpengantar.gudang',
            //             'suratpengantar.statusbatalmuat',
            //             'suratpengantar.statusupahzona',
            //             'suratpengantar.statusgandengan',
            //             'suratpengantar.gajisupir',
            //             'suratpengantar.gajikenek',
            //             'suratpengantar.komisisupir',
            //             'suratpengantar.upah_id',
            //             'suratpengantar.nobukti_tripasal',
            //             'suratpengantar.statusapprovaleditsuratpengantar',
            //             'suratpengantar.statusapprovalbiayatitipanemkl',
            //             'zonaupah.zona as upah'
            //         )
            //         ->leftJoin('kota as kotadari', 'kotadari.id', '=', 'suratpengantar.dari_id')
            //         ->leftJoin('kota as kotasampai', 'kotasampai.id', '=', 'suratpengantar.sampai_id')
            //         ->leftJoin('agen', 'suratpengantar.agen_id', 'agen.id')
            //         ->leftJoin('container', 'suratpengantar.container_id', 'container.id')
            //         ->leftJoin('statuscontainer', 'suratpengantar.statuscontainer_id', 'statuscontainer.id')
            //         ->leftJoin('trado', 'suratpengantar.trado_id', 'trado.id')
            //         ->leftJoin('supir', 'suratpengantar.supir_id', 'supir.id')
            //         ->leftJoin('jenisorder', 'suratpengantar.jenisorder_id', 'jenisorder.id')
            //         ->leftJoin('tarif', 'suratpengantar.tarif_id', 'tarif.id')
            //         ->leftJoin('upahsupir', 'suratpengantar.upah_id', 'upahsupir.id')
            //         ->leftJoin('zona as zonaupah', 'zonaupah.id', '=', 'upahsupir.zonasampai_id')
            //         ->leftJoin('cabang', 'suratpengantar.cabang_id', 'cabang.id')
            //         ->leftJoin('pelanggan', 'suratpengantar.pelanggan_id', 'pelanggan.id')
            //         ->leftJoin('gandengan', 'suratpengantar.gandengan_id', 'gandengan.id')
            //         // ->leftJoin(DB::raw("$tempGaji with (readuncommitted)"), "$tempGaji.id", "suratpengantar.id")

            //         ->where('suratpengantar.id', $id)->first();
            // }
            // dd('find');

        }
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
            $table->string('nocont2', 50)->nullable();
            $table->string('noseal', 50)->nullable();
            $table->string('noseal2', 50)->nullable();
            $table->decimal('omset', 15, 2)->nullable();
            $table->decimal('nominalperalihan', 15, 2)->nullable();
            $table->decimal('totalomset', 15, 2)->nullable();
            $table->unsignedBigInteger('statuscontainer_id')->nullable();
            $table->string('gudang', 500)->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->unsignedBigInteger('gandengan_id')->nullable();
            $table->integer('statuslongtrip')->length(11)->nullable();
            $table->integer('statuslangsir')->length(11)->nullable();
            $table->integer('statusperalihan')->length(11)->nullable();
            $table->integer('statusritasiomset')->length(11)->nullable();
            $table->integer('statusapprovaleditsuratpengantar')->Length(11)->nullable();
            $table->integer('statusapprovalbiayatitipanemkl')->Length(11)->nullable();
            $table->unsignedBigInteger('tarif_id')->nullable();
            $table->unsignedBigInteger('mandortrado_id')->nullable();
            $table->unsignedBigInteger('mandorsupir_id')->nullable();
            $table->unsignedBigInteger('statustolakan')->nullable();
            $table->unsignedBigInteger('statusgudangsama')->nullable();
            $table->unsignedBigInteger('statusbatalmuat')->nullable();
            $table->string('userapprovaleditsuratpengantar', 50)->nullable();
            $table->string('userapprovalbiayatitipanemkl', 50)->nullable();
            $table->date('tglapprovaleditsuratpengantar')->nullable();
            $table->dateTime('tglbataseditsuratpengantar')->nullable();
            $table->date('tglapprovalbiayatitipanemkl')->nullable();
            $table->string('gajisupir_nobukti', 500)->nullable();
            $table->string('prosesgajisupir_nobukti', 500)->nullable();
            $table->string('invoice_nobukti', 500)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->unsignedBigInteger('statusgajisupir')->nullable();
            $table->unsignedBigInteger('statusinvoice')->nullable();
            $table->integer('statusapprovalbiayaextra')->Length(11)->nullable();
            $table->string('userapprovalbiayaextra', 50)->nullable();
            $table->date('tglapprovalbiayaextra')->nullable();
            $table->datetime('tglbatasapprovalbiayaextra')->nullable();
        });

        $getSudahbuka = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS SUDAH BUKA')->where('subgrp', 'STATUS SUDAH BUKA')->where('text', 'SUDAH BUKA')->first() ?? 0;
        $getBelumbuka = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS SUDAH BUKA')->where('subgrp', 'STATUS SUDAH BUKA')->where('text', 'BELUM BUKA')->first() ?? 0;

        $tempspric = '##tempspric' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempspric, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->string('ebsnobukti', 50)->nullable();
            $table->string('suratpengantar_nobukti', 50)->nullable();
        });
        $queryric = DB::table("gajisupirdetail")->from(DB::raw("gajisupirdetail a with (readuncommitted)"))
            ->select(
                db::raw("max(a.nobukti) as nobukti"),
                db::raw("max(b.nobukti) as ebsnobukti"),
                'a.suratpengantar_nobukti'
            )
            ->leftjoin(db::raw("prosesgajisupirdetail b"), 'a.nobukti', 'b.gajisupir_nobukti')
            ->groupBy('a.suratpengantar_nobukti');
        DB::table($tempspric)->insertUsing([
            'nobukti',
            'ebsnobukti',
            'suratpengantar_nobukti',
        ], $queryric);

        $tempspinv = '##tempspinv' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempspinv, function ($table) {
            $table->string('nobukti', 50)->nullable();
            $table->string('orderantrucking_nobukti', 50)->nullable();
        });
        $queryinv = DB::table("invoicedetail")->from(DB::raw("invoicedetail a with (readuncommitted)"))
            ->select(
                db::raw("max(a.nobukti) as nobukti"),
                'a.orderantrucking_nobukti'
            )
            ->groupBy('a.orderantrucking_nobukti');
        DB::table($tempspinv)->insertUsing([
            'nobukti',
            'orderantrucking_nobukti',
        ], $queryinv);

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
                'suratpengantar.nocont2',
                'suratpengantar.noseal',
                'suratpengantar.noseal2',
                'suratpengantar.omset',
                'suratpengantar.nominalperalihan',
                'suratpengantar.totalomset',
                'suratpengantar.statuscontainer_id',
                'suratpengantar.gudang',
                'suratpengantar.trado_id',
                'suratpengantar.supir_id',
                'suratpengantar.gandengan_id',
                'suratpengantar.statuslongtrip',
                'suratpengantar.statuslangsir',
                'suratpengantar.statusperalihan',
                'suratpengantar.statusritasiomset',
                'suratpengantar.statusapprovaleditsuratpengantar',
                'suratpengantar.statusapprovalbiayatitipanemkl',
                'suratpengantar.tarif_id',
                'suratpengantar.mandortrado_id',
                'suratpengantar.mandorsupir_id',
                'suratpengantar.statustolakan',
                'suratpengantar.statusgudangsama',
                'suratpengantar.statusbatalmuat',
                'suratpengantar.userapprovaleditsuratpengantar',
                'suratpengantar.userapprovalbiayatitipanemkl',
                'suratpengantar.tglapprovaleditsuratpengantar',
                'suratpengantar.tglbataseditsuratpengantar',
                'suratpengantar.tglapprovalbiayatitipanemkl',
                'b.nobukti as gajisupir_nobukti',
                'b.ebsnobukti as prosesgajisupir_nobukti',
                'c.nobukti as invoice_nobukti',
                'suratpengantar.modifiedby',
                'suratpengantar.created_at',
                'suratpengantar.updated_at',
                db::raw("(case when isnull(b.nobukti,'')='' then " . $getBelumbuka->id . " else " . $getSudahbuka->id . " end) as statusgajisupir"),
                db::raw("(case when isnull(c.nobukti,'')='' then " . $getBelumbuka->id . " else " . $getSudahbuka->id . " end) as statusinvoice"),
                'statusapprovalbiayaextra',
                'userapprovalbiayaextra',
                'tglapprovalbiayaextra',
                'tglbatasapprovalbiayaextra'

            )
            ->leftJoin(DB::raw("$tempspric as b with (readuncommitted)"), 'suratpengantar.nobukti', 'b.suratpengantar_nobukti')
            ->leftJoin(DB::raw("$tempspinv as c with (readuncommitted)"), 'suratpengantar.jobtrucking', 'c.orderantrucking_nobukti');
        if (request()->tgldariheader) {
            $querysuratpengantar->whereBetween('suratpengantar.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        }

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
            'nocont2',
            'noseal',
            'noseal2',
            'omset',
            'nominalperalihan',
            'totalomset',
            'statuscontainer_id',
            'gudang',
            'trado_id',
            'supir_id',
            'gandengan_id',
            'statuslongtrip',
            'statuslangsir',
            'statusperalihan',
            'statusritasiomset',
            'statusapprovaleditsuratpengantar',
            'statusapprovalbiayatitipanemkl',
            'tarif_id',
            'mandortrado_id',
            'mandorsupir_id',
            'statustolakan',
            'statusgudangsama',
            'statusbatalmuat',
            'userapprovaleditsuratpengantar',
            'userapprovalbiayatitipanemkl',
            'tglapprovaleditsuratpengantar',
            'tglbataseditsuratpengantar',
            'tglapprovalbiayatitipanemkl',
            'gajisupir_nobukti',
            'prosesgajisupir_nobukti',
            'invoice_nobukti',
            'modifiedby',
            'created_at',
            'updated_at',
            'statusgajisupir',
            'statusinvoice',
            'statusapprovalbiayaextra',
            'userapprovalbiayaextra',
            'tglapprovalbiayaextra',
            'tglbatasapprovalbiayaextra'


        ], $querysuratpengantar);



        $query = DB::table($tempsuratpengantar)->from(DB::raw("$tempsuratpengantar as suratpengantar"))->select(
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
                kotadari.kodekota as dari_id,
                kotasampai.kodekota as sampai_id,
                suratpengantar.penyesuaian,
                suratpengantar.gajisupir,
                suratpengantar.jarak,
                agen.namaagen as agen_id,
                jenisorder.keterangan as jenisorder_id,
                container.keterangan as container_id,
                suratpengantar.nocont,
                suratpengantar.nocont2,
                suratpengantar.noseal,
                suratpengantar.noseal2,
                suratpengantar.omset,
                suratpengantar.nominalperalihan,
                suratpengantar.totalomset,
                statuscontainer.keterangan as statuscontainer_id,
                suratpengantar.gudang,
                trado.kodetrado as trado_id,
                supir.namasupir as supir_id,
                gandengan.keterangan as gandengan_id,
                statuslongtrip.memo as statuslongtrip,
                statuslongtrip.text as statuslongtriptext,
                statuslangsir.memo as statuslangsir,
                statuslangsir.text as statuslangsirtext,
                statusperalihan.memo as statusperalihan,
                statusperalihan.text as statusperalihantext,
                statusritasiomset.memo as statusritasiomset,
                statusapprovaleditsuratpengantar.memo as statusapprovaleditsuratpengantar,
                statusapprovaleditsuratpengantar.text as statusapprovaleditsuratpengantartext,
                statusapprovalbiayatitipanemkl.memo as statusapprovalbiayatitipanemkl,
                statusapprovalbiayatitipanemkl.text as statusapprovalbiayatitipanemkltext,
                tarif.tujuan as tarif_id,
                mandortrado.namamandor as mandortrado_id,
                mandorsupir.namamandor as mandorsupir_id,
                statustolakan.memo as statustolakan,
                statustolakan.text as statustolakantext,
                statusgudangsama.memo as statusgudangsama,
                statusgudangsama.text as statusgudangsamatext,
                statusbatalmuat.memo as statusbatalmuat,
                statusbatalmuat.text as statusbatalmuattext,
                suratpengantar.userapprovaleditsuratpengantar,
                suratpengantar.userapprovalbiayatitipanemkl,
                suratpengantar.tglapprovaleditsuratpengantar,
                suratpengantar.tglbataseditsuratpengantar,
                suratpengantar.tglapprovalbiayatitipanemkl,
                suratpengantar.gajisupir_nobukti,
                suratpengantar.prosesgajisupir_nobukti,
                suratpengantar.invoice_nobukti,
                suratpengantar.modifiedby,
                suratpengantar.created_at,
                suratpengantar.updated_at,
                statusgajisupir.memo as statusgajisupir,
                statusgajisupir.text as statusgajisupirtext,
                statusinvoice.memo as statusinvoice,
                statusinvoice.text as statusinvoicetext,
                statusapprovalbiayaextra.memo as statusapprovalbiayaextra,
                statusapprovalbiayaextra.text as statusapprovalbiayaextratext,
                suratpengantar.userapprovalbiayaextra,
                suratpengantar.tglapprovalbiayaextra,
                suratpengantar.tglbatasapprovalbiayaextra
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
            ->leftJoin('parameter as statuslangsir', 'suratpengantar.statuslangsir', 'statuslangsir.id')
            ->leftJoin('parameter as statusperalihan', 'suratpengantar.statusperalihan', 'statusperalihan.id')
            ->leftJoin('parameter as statusritasiomset', 'suratpengantar.statusritasiomset', 'statusritasiomset.id')
            ->leftJoin('parameter as statusgudangsama', 'suratpengantar.statusgudangsama', 'statusgudangsama.id')
            ->leftJoin('parameter as statustolakan', 'suratpengantar.statustolakan', 'statustolakan.id')
            ->leftJoin('parameter as statusbatalmuat', 'suratpengantar.statusbatalmuat', 'statusbatalmuat.id')
            ->leftJoin('parameter as statusapprovaleditsuratpengantar', 'suratpengantar.statusapprovaleditsuratpengantar', 'statusapprovaleditsuratpengantar.id')
            ->leftJoin('parameter as statusapprovalbiayaextra', 'suratpengantar.statusapprovalbiayaextra', 'statusapprovalbiayaextra.id')
            ->leftJoin('parameter as statusapprovalbiayatitipanemkl', 'suratpengantar.statusapprovalbiayatitipanemkl', 'statusapprovalbiayatitipanemkl.id')
            ->leftJoin('parameter as statusgajisupir', 'suratpengantar.statusgajisupir', 'statusgajisupir.id')
            ->leftJoin('parameter as statusinvoice', 'suratpengantar.statusinvoice', 'statusinvoice.id')
            ->leftJoin('mandor as mandortrado', 'suratpengantar.mandortrado_id', 'mandortrado.id')
            ->leftJoin('mandor as mandorsupir', 'suratpengantar.mandorsupir_id', 'mandorsupir.id')
            // ->leftJoin(DB::raw("gajisupirdetail as b with (readuncommitted)"), 'suratpengantar.nobukti', 'b.suratpengantar_nobukti')
            // ->leftJoin(DB::raw("invoicedetail as c with (readuncommitted)"), 'suratpengantar.jobtrucking', 'c.orderantrucking_nobukti')
            ->leftJoin('tarif', 'suratpengantar.tarif_id', 'tarif.id');
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
            $table->string('nocont2', 50)->nullable();
            $table->string('noseal', 50)->nullable();
            $table->string('noseal2', 50)->nullable();
            $table->float('omset')->nullable();
            $table->float('nominalperalihan')->nullable();
            $table->float('totalomset')->nullable();
            $table->string('statuscontainer_id')->nullable();
            $table->string('gudang')->nullable();
            $table->string('trado_id')->nullable();
            $table->string('supir_id')->nullable();
            $table->string('gandengan_id')->nullable();
            $table->longText('statuslongtrip')->nullable();
            $table->longText('statuslongtriptext')->nullable();
            $table->longText('statuslangsir')->nullable();
            $table->longText('statuslangsirtext')->nullable();
            $table->longText('statusperalihan')->nullable();
            $table->longText('statusperalihantext')->nullable();
            $table->longText('statusritasiomset')->nullable();
            $table->longText('statusapprovaleditsuratpengantar')->nullable();
            $table->longText('statusapprovaleditsuratpengantartext')->nullable();
            $table->longText('statusapprovalbiayatitipanemkl')->nullable();
            $table->longText('statusapprovalbiayatitipanemkltext')->nullable();
            $table->string('tarif_id')->nullable();
            $table->string('mandortrado_id')->nullable();
            $table->string('mandorsupir_id')->nullable();
            $table->longText('statustolakan')->nullable();
            $table->longText('statustolakantext')->nullable();
            $table->longText('statusgudangsama')->nullable();
            $table->longText('statusgudangsamatext')->nullable();
            $table->longText('statusbatalmuat')->nullable();
            $table->longText('statusbatalmuattext')->nullable();
            $table->string('userapprovaleditsuratpengantar')->nullable();
            $table->string('userapprovalbiayatitipanemkl')->nullable();
            $table->date('tglapprovaleditsuratpengantar')->nullable();
            $table->dateTime('tglbataseditsuratpengantar')->nullable();
            $table->date('tglapprovalbiayatitipanemkl')->nullable();
            $table->string('gajisupir_nobukti')->nullable();
            $table->string('prosesgajisupir_nobukti')->nullable();
            $table->longText('invoice_nobukti')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->longText('statusgajisupir')->nullable();
            $table->longText('statusgajisupirtext')->nullable();
            $table->longText('statusinvoice')->nullable();
            $table->longText('statusinvoicetext')->nullable();
            $table->longText('statusapprovalbiayaextra')->nullable();
            $table->longText('statusapprovalbiayaextratext')->nullable();
            $table->string('userapprovalbiayaextra', 50)->nullable();
            $table->date('tglapprovalbiayaextra')->nullable();
            $table->datetime('tglbatasapprovalbiayaextra')->nullable();
        });
        DB::table($temp)->insertUsing([
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
            'nocont2',
            'noseal',
            'noseal2',
            'omset',
            'nominalperalihan',
            'totalomset',
            'statuscontainer_id',
            'gudang',
            'trado_id',
            'supir_id',
            'gandengan_id',
            'statuslongtrip',
            'statuslongtriptext',
            'statuslangsir',
            'statuslangsirtext',
            'statusperalihan',
            'statusperalihantext',
            'statusritasiomset',
            'statusapprovaleditsuratpengantar',
            'statusapprovaleditsuratpengantartext',
            'statusapprovalbiayatitipanemkl',
            'statusapprovalbiayatitipanemkltext',
            'tarif_id',
            'mandortrado_id',
            'mandorsupir_id',
            'statustolakan',
            'statustolakantext',
            'statusgudangsama',
            'statusgudangsamatext',
            'statusbatalmuat',
            'statusbatalmuattext',
            'userapprovaleditsuratpengantar',
            'userapprovalbiayatitipanemkl',
            'tglapprovaleditsuratpengantar',
            'tglbataseditsuratpengantar',
            'tglapprovalbiayatitipanemkl',
            'gajisupir_nobukti',
            'prosesgajisupir_nobukti',
            'invoice_nobukti',
            'modifiedby',
            'created_at',
            'updated_at',
            'statusgajisupir',
            'statusgajisupirtext',
            'statusinvoice',
            'statusinvoicetext',
            'statusapprovalbiayaextra',
            'statusapprovalbiayaextratext',
            'userapprovalbiayaextra',
            'tglapprovalbiayaextra',
            'tglbatasapprovalbiayaextra'
        ], $query);

        return DB::table($temp)->from(DB::raw("$temp as suratpengantar with (readuncommitted)"))
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
                'suratpengantar.nocont2',
                'suratpengantar.noseal',
                'suratpengantar.noseal2',
                'suratpengantar.omset',
                'suratpengantar.nominalperalihan',
                'suratpengantar.totalomset',
                'suratpengantar.statuscontainer_id',
                'suratpengantar.gudang',
                'suratpengantar.trado_id',
                'suratpengantar.supir_id',
                'suratpengantar.gandengan_id',
                'suratpengantar.statuslongtrip',
                'suratpengantar.statuslongtriptext',
                'suratpengantar.statuslangsir',
                'suratpengantar.statuslangsirtext',
                'suratpengantar.statusperalihan',
                'suratpengantar.statusperalihantext',
                'suratpengantar.statusritasiomset',
                'suratpengantar.statusapprovaleditsuratpengantar',
                'suratpengantar.statusapprovaleditsuratpengantartext',
                'suratpengantar.statusapprovalbiayatitipanemkl',
                'suratpengantar.statusapprovalbiayatitipanemkltext',
                'suratpengantar.tarif_id',
                'suratpengantar.mandortrado_id',
                'suratpengantar.mandorsupir_id',
                'suratpengantar.statustolakan',
                'suratpengantar.statustolakantext',
                'suratpengantar.statusgudangsama',
                'suratpengantar.statusgudangsamatext',
                'suratpengantar.statusbatalmuat',
                'suratpengantar.statusbatalmuattext',
                'suratpengantar.userapprovaleditsuratpengantar',
                'suratpengantar.userapprovalbiayatitipanemkl',
                'suratpengantar.tglapprovaleditsuratpengantar',
                'suratpengantar.tglbataseditsuratpengantar',
                'suratpengantar.tglapprovalbiayatitipanemkl',
                'suratpengantar.gajisupir_nobukti',
                'suratpengantar.prosesgajisupir_nobukti',
                'suratpengantar.invoice_nobukti',
                'suratpengantar.modifiedby',
                'suratpengantar.created_at',
                'suratpengantar.updated_at',
                'suratpengantar.statusgajisupir',
                'suratpengantar.statusgajisupirtext',
                'suratpengantar.statusinvoice',
                'suratpengantar.statusinvoicetext',
                'suratpengantar.statusapprovalbiayaextra',
                'suratpengantar.statusapprovalbiayaextratext',
                'suratpengantar.userapprovalbiayaextra',
                'suratpengantar.tglapprovalbiayaextra',
                'suratpengantar.tglbatasapprovalbiayaextra'
            );
    }

    public function getpelabuhan($id)
    {
        // $data = DB::table('parameter')
        //     ->from(DB::raw("parameter with (readuncommitted)"))
        //     ->select(
        //         'text as id'
        //     )
        //     ->where('grp', '=', 'PELABUHAN CABANG')
        //     ->where('subgrp', '=', 'PELABUHAN CABANG')
        //     ->where('text', '=', $id)
        //     ->first();

        $parameter = new Parameter();
        $statuspelabuhan = $parameter->cekId('STATUS PELABUHAN', 'STATUS PELABUHAN', 'PELABUHAN') ?? 0;


        $data = DB::table('kota')
            ->from(DB::raw("kota with (readuncommitted)"))
            ->select(
                'id'
            )
            ->where('statuspelabuhan', $statuspelabuhan)
            ->where('id', '=', $id)
            ->first();


        // $datakandang = DB::table('parameter')
        //     ->from(DB::raw("parameter with (readuncommitted)"))
        //     ->select(
        //         'text as id'
        //     )
        //     ->where('grp', '=', 'KANDANG')
        //     ->where('subgrp', '=', 'KANDANG')
        //     ->where('text', '=', $id)
        //     ->first();


        // if (isset($data) || isset($datakandang)) {
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
            'kotadari.kodekota as dari_id',
            'kotasampai.kodekota as sampai_id',
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

        $isMandor = auth()->user()->isMandor();
        $isAdmin = auth()->user()->isAdmin();
        $proses = request()->proses ?? 'reload';
        $supirheader = request()->supirheader ?? 0;
        $user = auth('api')->user()->name;
        $class = 'SuratPengantarController';
        $cabang = (new Parameter())->cekText('CABANG', 'CABANG');

        if ($proses == 'reload') {
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

            $tempsuratpengantar = 'temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            DB::table('listtemporarytabel')->insert(
                [
                    'class' => $class,
                    'namatabel' => $tempsuratpengantar,
                    'modifiedby' => $user,
                    'created_at' => date('Y/m/d H:i:s'),
                    'updated_at' => date('Y/m/d H:i:s'),
                ]
            );

            Schema::create($tempsuratpengantar, function ($table) {
                $table->integer('id')->nullable();
                $table->integer('idoriginal')->nullable();
                $table->string('jobtrucking', 50)->nullable();
                $table->string('nobukti', 50)->nullable();
                $table->string('ritasi_nobukti', 50)->nullable();
                $table->date('tglbukti')->nullable();
                $table->string('nosp', 50)->nullable();
                $table->date('tglsp')->nullable();
                $table->string('nojob', 50)->nullable();
                $table->string('pelanggan_id')->nullable();
                $table->longText('keterangan')->nullable();
                $table->string('dari_id')->nullable();
                $table->string('sampai_id')->nullable();
                $table->string('keteranganritasi')->nullable();
                $table->decimal('gajisupir', 15, 2)->nullable();
                $table->decimal('jarak', 15, 2)->nullable();
                $table->longText('penyesuaian')->nullable();
                $table->string('agen_id')->nullable();
                $table->string('jenisorder_id')->nullable();
                $table->string('container_id')->nullable();
                $table->string('nocont')->nullable();
                $table->string('noseal')->nullable();
                $table->string('statuscontainer_id')->nullable();
                $table->string('gudang')->nullable();
                $table->string('trado_id')->nullable();
                $table->string('supir_id')->nullable();
                $table->string('gandengan_id')->nullable();
                $table->longText('statuslongtrip')->nullable();
                $table->longText('statuslongtriptext')->nullable();
                $table->longText('statusperalihan')->nullable();
                $table->longText('statusperalihantext')->nullable();
                $table->longText('statusritasiomset')->nullable();
                $table->longText('statusapprovalmandor')->nullable();
                $table->longText('statusapprovalmandortext')->nullable();
                $table->dateTime('tglapprovalmandor')->nullable();
                $table->string('userapprovalmandor')->nullable();
                $table->string('tarif_id')->nullable();
                $table->string('mandortrado_id')->nullable();
                $table->string('mandorsupir_id')->nullable();
                $table->longText('statusgudangsama')->nullable();
                $table->longText('statusgudangsamatext')->nullable();
                $table->longText('statusbatalmuat')->nullable();
                $table->longText('statusbatalmuattext')->nullable();
                $table->string('modifiedby')->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->integer('flag')->nullable();
                $table->string('gajisupir_nobukti', 500)->nullable();
                $table->string('prosesgajisupir_nobukti', 500)->nullable();
                $table->longText('statusgajisupir')->nullable();
                $table->longText('statusgajisupirtext')->nullable();
                $table->date('tgldarigajisupirheader')->nullable();
                $table->date('tglsampaigajisupirheader')->nullable();
                $table->date('tgldariebs')->nullable();
                $table->date('tglsampaiebs')->nullable();
            });
            $query = DB::table($this->table)->select(
                'suratpengantar.id',
                'suratpengantar.id as idoriginal',
                'suratpengantar.jobtrucking',
                'suratpengantar.nobukti',
                'suratpengantar.tglbukti',
                'suratpengantar.nosp',
                'suratpengantar.tglsp',
                'suratpengantar.nojob',
                'pelanggan.namapelanggan as pelanggan_id',
                'suratpengantar.keterangan',
                'kotadari.kodekota as dari_id',
                'kotasampai.kodekota as sampai_id',
                'suratpengantar.gajisupir',
                'suratpengantar.jarak',
                'suratpengantar.penyesuaian',
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
                'statuslongtrip.text as statuslongtriptext',
                'statusperalihan.memo as statusperalihan',
                'statusperalihan.text as statusperalihantext',
                'statusritasiomset.memo as statusritasiomset',
                'statusapprovalmandor.memo as statusapprovalmandor',
                'statusapprovalmandor.text as statusapprovalmandortext',
                DB::raw('(case when (year(suratpengantar.tglapprovalmandor) <= 2000) then null else suratpengantar.tglapprovalmandor end ) as tglapprovalmandor'),
                'suratpengantar.userapprovalmandor',
                'tarif.tujuan as tarif_id',
                'mandortrado.namamandor as mandortrado_id',
                'mandorsupir.namamandor as mandorsupir_id',
                'statusgudangsama.memo as statusgudangsama',
                'statusgudangsama.text as statusgudangsamatext',
                'statusbatalmuat.memo as statusbatalmuat',
                'statusbatalmuat.text as statusbatalmuattext',
                'suratpengantar.modifiedby',
                'suratpengantar.created_at',
                'suratpengantar.updated_at',
                DB::raw("1 as flag")

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
                ->leftJoin('parameter as statusapprovalmandor', 'suratpengantar.statusapprovalmandor', 'statusapprovalmandor.id')
                ->leftJoin('mandor as mandortrado', 'suratpengantar.mandortrado_id', 'mandortrado.id')
                ->leftJoin('mandor as mandorsupir', 'suratpengantar.mandorsupir_id', 'mandorsupir.id')
                ->leftJoin('tarif', 'suratpengantar.tarif_id', 'tarif.id');
            // ->orderBy('suratpengantar.tglbukti', 'desc');
            if (!$isAdmin) {
                if ($isMandor) {
                    $query->Join(DB::raw($tempmandordetail . " as mandordetail"), 'trado.mandor_id', 'mandordetail.mandor_id');
                }
            }
            if ($supirheader != 0) {
                $query->where('suratpengantar.supir_id', $supirheader);
            }

            if ($cabang == 'MEDAN') {

                $getSudahbuka = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS SUDAH BUKA')->where('subgrp', 'STATUS SUDAH BUKA')->where('text', 'SUDAH BUKA')->first() ?? 0;
                $getBelumbuka = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS SUDAH BUKA')->where('subgrp', 'STATUS SUDAH BUKA')->where('text', 'BELUM BUKA')->first() ?? 0;

                $tempsuratpengantarrinci = '##tempsuratpengantarrinci' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempsuratpengantarrinci, function ($table) {
                    $table->integer('id')->nullable();
                    $table->integer('idoriginal')->nullable();
                    $table->string('jobtrucking', 50)->nullable();
                    $table->string('nobukti', 50)->nullable();
                    $table->string('ritasi_nobukti', 50)->nullable();
                    $table->date('tglbukti')->nullable();
                    $table->string('nosp', 50)->nullable();
                    $table->date('tglsp')->nullable();
                    $table->string('nojob', 50)->nullable();
                    $table->string('pelanggan_id')->nullable();
                    $table->longText('keterangan')->nullable();
                    $table->string('dari_id')->nullable();
                    $table->string('sampai_id')->nullable();
                    $table->string('keteranganritasi')->nullable();
                    $table->decimal('gajisupir', 15, 2)->nullable();
                    $table->decimal('jarak', 15, 2)->nullable();
                    $table->longText('penyesuaian')->nullable();
                    $table->string('agen_id')->nullable();
                    $table->string('jenisorder_id')->nullable();
                    $table->string('container_id')->nullable();
                    $table->string('nocont')->nullable();
                    $table->string('noseal')->nullable();
                    $table->string('statuscontainer_id')->nullable();
                    $table->string('gudang')->nullable();
                    $table->string('trado_id')->nullable();
                    $table->string('supir_id')->nullable();
                    $table->string('gandengan_id')->nullable();
                    $table->longText('statuslongtrip')->nullable();
                    $table->longText('statuslongtriptext')->nullable();
                    $table->longText('statusperalihan')->nullable();
                    $table->longText('statusperalihantext')->nullable();
                    $table->longText('statusritasiomset')->nullable();
                    $table->longText('statusapprovalmandor')->nullable();
                    $table->longText('statusapprovalmandortext')->nullable();
                    $table->dateTime('tglapprovalmandor')->nullable();
                    $table->string('userapprovalmandor')->nullable();
                    $table->string('tarif_id')->nullable();
                    $table->string('mandortrado_id')->nullable();
                    $table->string('mandorsupir_id')->nullable();
                    $table->longText('statusgudangsama')->nullable();
                    $table->longText('statusgudangsamatext')->nullable();
                    $table->longText('statusbatalmuat')->nullable();
                    $table->longText('statusbatalmuattext')->nullable();
                    $table->string('modifiedby')->nullable();
                    $table->dateTime('created_at')->nullable();
                    $table->dateTime('updated_at')->nullable();
                    $table->integer('flag')->nullable();
                    $table->string('gajisupir_nobukti', 500)->nullable();
                    $table->string('prosesgajisupir_nobukti', 500)->nullable();
                    $table->unsignedBigInteger('statusgajisupir')->nullable();
                    $table->date('tgldarigajisupirheader')->nullable();
                    $table->date('tglsampaigajisupirheader')->nullable();
                });

                $tempspric = '##tempspric' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempspric, function ($table) {
                    $table->string('nobukti', 50)->nullable();
                    $table->string('ebsnobukti', 50)->nullable();
                    $table->string('suratpengantar_nobukti', 50)->nullable();
                    $table->date('tglbukti')->nullable();
                });
                $queryric = DB::table("gajisupirdetail")->from(DB::raw("gajisupirdetail a with (readuncommitted)"))
                    ->select(
                        db::raw("max(a.nobukti) as nobukti"),
                        db::raw("max(d.nobukti) as ebsnobukti"),
                        'a.suratpengantar_nobukti',
                        db::raw("max(c.tglbukti) as tglbukti")
                    )
                    ->join(db::raw("suratpengantar as b with (readuncommitted)"), 'a.suratpengantar_nobukti', 'b.nobukti')
                    ->join(db::raw("gajisupirheader as c with (readuncommitted)"), 'a.nobukti', 'c.nobukti')
                    ->leftjoin(db::raw("prosesgajisupirdetail d with (readuncommitted)"), 'a.nobukti', 'd.gajisupir_nobukti')
                    ->whereBetween('b.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
                    ->groupBy('a.suratpengantar_nobukti');

                if ($supirheader != 0) {
                    $queryric->where('b.supir_id', $supirheader);
                }
                DB::table($tempspric)->insertUsing([
                    'nobukti',
                    'ebsnobukti',
                    'suratpengantar_nobukti',
                    'tglbukti'
                ], $queryric);
                $query->addSelect(db::raw("isnull(gajisupir.nobukti,'') as gajisupir_nobukti,isnull(gajisupir.ebsnobukti,'') as prosesgajisupir_nobukti, (case when isnull(gajisupir.nobukti,'')='' then " . $getBelumbuka->id . " else " . $getSudahbuka->id . " end) as statusgajisupir, cast((format(gajisupir.tglbukti,'yyyy/MM')+'/1') as date) as tgldarigajisupirheader, cast(cast(format((cast((format(gajisupir.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaigajisupirheader"))
                    ->leftJoin(DB::raw("$tempspric as gajisupir with (readuncommitted)"), 'suratpengantar.nobukti', 'gajisupir.suratpengantar_nobukti');

                DB::table($tempsuratpengantarrinci)->insertUsing([
                    'id',
                    'idoriginal',
                    'jobtrucking',
                    'nobukti',
                    'tglbukti',
                    'nosp',
                    'tglsp',
                    'nojob',
                    'pelanggan_id',
                    'keterangan',
                    'dari_id',
                    'sampai_id',
                    'gajisupir',
                    'jarak',
                    'penyesuaian',
                    'agen_id',
                    'jenisorder_id',
                    'container_id',
                    'nocont',
                    'noseal',
                    'statuscontainer_id',
                    'gudang',
                    'trado_id',
                    'supir_id',
                    'gandengan_id',
                    'statuslongtrip',
                    'statuslongtriptext',
                    'statusperalihan',
                    'statusperalihantext',
                    'statusritasiomset',
                    'statusapprovalmandor',
                    'statusapprovalmandortext',
                    'tglapprovalmandor',
                    'userapprovalmandor',
                    'tarif_id',
                    'mandortrado_id',
                    'mandorsupir_id',
                    'statusgudangsama',
                    'statusgudangsamatext',
                    'statusbatalmuat',
                    'statusbatalmuattext',
                    'modifiedby',
                    'created_at',
                    'updated_at',
                    'flag',
                    'gajisupir_nobukti',
                    'prosesgajisupir_nobukti',
                    'statusgajisupir',
                    'tgldarigajisupirheader',
                    'tglsampaigajisupirheader'
                ], $query);

                $tempspric = '##tempspric' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempspric, function ($table) {
                    $table->string('nobukti', 50)->nullable();
                    $table->string('ebsnobukti', 50)->nullable();
                    $table->string('ritasi_nobukti', 50)->nullable();
                    $table->date('tglbukti')->nullable();
                });
                $queryric = DB::table("gajisupirdetail")->from(DB::raw("gajisupirdetail a with (readuncommitted)"))
                    ->select(
                        db::raw("max(a.nobukti) as nobukti"),
                        db::raw("max(d.nobukti) as ebsnobukti"),
                        'a.ritasi_nobukti',
                        db::raw("max(c.tglbukti) as tglbukti")
                    )
                    ->join(db::raw("ritasi as b with (readuncommitted)"), 'a.ritasi_nobukti', 'b.nobukti')
                    ->join(db::raw("gajisupirheader as c with (readuncommitted)"), 'a.nobukti', 'c.nobukti')
                    ->leftjoin(db::raw("prosesgajisupirdetail as d with (readuncommitted)"), 'a.nobukti', 'd.gajisupir_nobukti')
                    ->whereBetween('b.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
                    ->groupBy('a.ritasi_nobukti');

                if ($supirheader != 0) {
                    $queryric->where('b.supir_id', $supirheader);
                }
                DB::table($tempspric)->insertUsing([
                    'nobukti',
                    'ebsnobukti',
                    'ritasi_nobukti',
                    'tglbukti'
                ], $queryric);

                $query = DB::table($this->table)->select(
                    'suratpengantar.id',
                    'ritasi.id as idoriginal',
                    'suratpengantar.jobtrucking',
                    'suratpengantar.nobukti',
                    'ritasi.nobukti as ritasi_nobukti',
                    'suratpengantar.tglbukti',
                    'suratpengantar.nosp',
                    'suratpengantar.tglsp',
                    'suratpengantar.nojob',
                    'pelanggan.namapelanggan as pelanggan_id',
                    'suratpengantar.keterangan',
                    'kotadari.kodekota as dari_id',
                    'kotasampai.kodekota as sampai_id',
                    'statusritasi.text as keteranganritasi',
                    'ritasi.gaji as gajisupir',
                    'ritasi.jarak',
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
                    'statuslongtrip.text as statuslongtriptext',
                    'statusperalihan.memo as statusperalihan',
                    'statusperalihan.text as statusperalihantext',
                    'statusritasiomset.memo as statusritasiomset',
                    'statusapprovalmandor.memo as statusapprovalmandor',
                    'statusapprovalmandor.text as statusapprovalmandortext',
                    DB::raw('(case when (year(ritasi.tglapprovalmandor) <= 2000) then null else ritasi.tglapprovalmandor end ) as tglapprovalmandor'),
                    'ritasi.userapprovalmandor',
                    'tarif.tujuan as tarif_id',
                    'mandortrado.namamandor as mandortrado_id',
                    'mandorsupir.namamandor as mandorsupir_id',
                    'statusgudangsama.memo as statusgudangsama',
                    'statusgudangsama.text as statusgudangsamatext',
                    'statusbatalmuat.memo as statusbatalmuat',
                    'statusbatalmuat.text as statusbatalmuattext',
                    'suratpengantar.modifiedby',
                    'suratpengantar.created_at',
                    'suratpengantar.updated_at',
                    DB::raw("2 as flag"),
                    db::raw("isnull(gajisupir.nobukti,'') as gajisupir_nobukti, isnull(gajisupir.ebsnobukti,'') as prosesgajisupir_nobukti, 
                    (case when isnull(gajisupir.nobukti,'')='' then " . $getBelumbuka->id . " else " . $getSudahbuka->id . " end) as statusgajisupir,  cast((format(gajisupir.tglbukti,'yyyy/MM')+'/1') as date) as tgldarigajisupirheader, cast(cast(format((cast((format(gajisupir.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaigajisupirheader")

                )

                    ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
                    ->join('ritasi', 'suratpengantar.nobukti', 'ritasi.suratpengantar_nobukti')
                    ->join('parameter as statusritasi', 'ritasi.statusritasi', 'statusritasi.id')
                    ->leftJoin('pelanggan', 'suratpengantar.pelanggan_id', 'pelanggan.id')
                    ->leftJoin('kota as kotadari', 'kotadari.id', '=', 'ritasi.dari_id')
                    ->leftJoin('kota as kotasampai', 'kotasampai.id', '=', 'ritasi.sampai_id')
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
                    ->leftJoin('parameter as statusapprovalmandor', 'ritasi.statusapprovalmandor', 'statusapprovalmandor.id')
                    ->leftJoin('mandor as mandortrado', 'suratpengantar.mandortrado_id', 'mandortrado.id')
                    ->leftJoin('mandor as mandorsupir', 'suratpengantar.mandorsupir_id', 'mandorsupir.id')
                    ->leftJoin('tarif', 'suratpengantar.tarif_id', 'tarif.id')
                    ->leftJoin(DB::raw("$tempspric as gajisupir with (readuncommitted)"), 'ritasi.nobukti', 'gajisupir.ritasi_nobukti');

                // ->orderBy('suratpengantar.tglbukti', 'desc');
                if (!$isAdmin) {
                    if ($isMandor) {
                        $query->Join(DB::raw($tempmandordetail . " as mandordetail"), 'trado.mandor_id', 'mandordetail.mandor_id');
                    }
                }
                if ($supirheader != 0) {
                    $query->where('suratpengantar.supir_id', $supirheader);
                }


                DB::table($tempsuratpengantarrinci)->insertUsing([
                    'id',
                    'idoriginal',
                    'jobtrucking',
                    'nobukti',
                    'ritasi_nobukti',
                    'tglbukti',
                    'nosp',
                    'tglsp',
                    'nojob',
                    'pelanggan_id',
                    'keterangan',
                    'dari_id',
                    'sampai_id',
                    'keteranganritasi',
                    'gajisupir',
                    'jarak',
                    'agen_id',
                    'jenisorder_id',
                    'container_id',
                    'nocont',
                    'noseal',
                    'statuscontainer_id',
                    'gudang',
                    'trado_id',
                    'supir_id',
                    'gandengan_id',
                    'statuslongtrip',
                    'statuslongtriptext',
                    'statusperalihan',
                    'statusperalihantext',
                    'statusritasiomset',
                    'statusapprovalmandor',
                    'statusapprovalmandortext',
                    'tglapprovalmandor',
                    'userapprovalmandor',
                    'tarif_id',
                    'mandortrado_id',
                    'mandorsupir_id',
                    'statusgudangsama',
                    'statusgudangsamatext',
                    'statusbatalmuat',
                    'statusbatalmuattext',
                    'modifiedby',
                    'created_at',
                    'updated_at',
                    'flag',
                    'gajisupir_nobukti',
                    'prosesgajisupir_nobukti',
                    'statusgajisupir',
                    'tgldarigajisupirheader',
                    'tglsampaigajisupirheader'
                ], $query);

                $queryfinal = DB::table($tempsuratpengantarrinci)->from(DB::raw("$tempsuratpengantarrinci as a with (readuncommitted)"))
                    ->select(
                        DB::raw("row_number() Over(Order By a.tglbukti, a.nobukti, a.flag,a.ritasi_nobukti) as id"),
                        'a.idoriginal',
                        'a.jobtrucking',
                        'a.nobukti',
                        'a.ritasi_nobukti',
                        'a.tglbukti',
                        'a.nosp',
                        'a.tglsp',
                        'a.nojob',
                        'a.pelanggan_id',
                        'a.keterangan',
                        'a.dari_id',
                        'a.sampai_id',
                        'a.keteranganritasi',
                        'a.gajisupir',
                        'a.jarak',
                        'a.penyesuaian',
                        'a.agen_id',
                        'a.jenisorder_id',
                        'a.container_id',
                        'a.nocont',
                        'a.noseal',
                        'a.statuscontainer_id',
                        'a.gudang',
                        'a.trado_id',
                        'a.supir_id',
                        'a.gandengan_id',
                        'a.statuslongtrip',
                        'a.statuslongtriptext',
                        'a.statusperalihan',
                        'a.statusperalihantext',
                        'a.statusritasiomset',
                        'a.statusapprovalmandor',
                        'a.statusapprovalmandortext',
                        'a.tglapprovalmandor',
                        'a.userapprovalmandor',
                        'a.tarif_id',
                        'a.mandortrado_id',
                        'a.mandorsupir_id',
                        'a.statusgudangsama',
                        'a.statusgudangsamatext',
                        'a.statusbatalmuat',
                        'a.statusbatalmuattext',
                        'a.modifiedby',
                        'a.created_at',
                        'a.updated_at',
                        'a.flag',
                        'a.gajisupir_nobukti',
                        'a.prosesgajisupir_nobukti',
                        'statusgajisupir.memo as statusgajisupir',
                        'statusgajisupir.text as statusgajisupirtext',
                        'a.tgldarigajisupirheader',
                        'a.tglsampaigajisupirheader',
                        DB::raw("cast((format(prosesgajisupirheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariebs, cast(cast(format((cast((format(prosesgajisupirheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiebs")
                    )
                    ->leftJoin(DB::raw("prosesgajisupirheader with (readuncommitted)"), 'a.prosesgajisupir_nobukti', 'prosesgajisupirheader.nobukti')
                    ->leftJoin('parameter as statusgajisupir', 'a.statusgajisupir', 'statusgajisupir.id');
                // dd($queryfinal->get());
                DB::table($tempsuratpengantar)->insertUsing([
                    'id',
                    'idoriginal',
                    'jobtrucking',
                    'nobukti',
                    'ritasi_nobukti',
                    'tglbukti',
                    'nosp',
                    'tglsp',
                    'nojob',
                    'pelanggan_id',
                    'keterangan',
                    'dari_id',
                    'sampai_id',
                    'keteranganritasi',
                    'gajisupir',
                    'jarak',
                    'penyesuaian',
                    'agen_id',
                    'jenisorder_id',
                    'container_id',
                    'nocont',
                    'noseal',
                    'statuscontainer_id',
                    'gudang',
                    'trado_id',
                    'supir_id',
                    'gandengan_id',
                    'statuslongtrip',
                    'statuslongtriptext',
                    'statusperalihan',
                    'statusperalihantext',
                    'statusritasiomset',
                    'statusapprovalmandor',
                    'statusapprovalmandortext',
                    'tglapprovalmandor',
                    'userapprovalmandor',
                    'tarif_id',
                    'mandortrado_id',
                    'mandorsupir_id',
                    'statusgudangsama',
                    'statusgudangsamatext',
                    'statusbatalmuat',
                    'statusbatalmuattext',
                    'modifiedby',
                    'created_at',
                    'updated_at',
                    'flag',
                    'gajisupir_nobukti',
                    'prosesgajisupir_nobukti',
                    'statusgajisupir',
                    'statusgajisupirtext',
                    'tgldarigajisupirheader',
                    'tglsampaigajisupirheader',
                    'tgldariebs',
                    'tglsampaiebs'
                ], $queryfinal);
            } else {


                DB::table($tempsuratpengantar)->insertUsing([
                    'id',
                    'idoriginal',
                    'jobtrucking',
                    'nobukti',
                    'tglbukti',
                    'nosp',
                    'tglsp',
                    'nojob',
                    'pelanggan_id',
                    'keterangan',
                    'dari_id',
                    'sampai_id',
                    'gajisupir',
                    'jarak',
                    'penyesuaian',
                    'agen_id',
                    'jenisorder_id',
                    'container_id',
                    'nocont',
                    'noseal',
                    'statuscontainer_id',
                    'gudang',
                    'trado_id',
                    'supir_id',
                    'gandengan_id',
                    'statuslongtrip',
                    'statuslongtriptext',
                    'statusperalihan',
                    'statusperalihantext',
                    'statusritasiomset',
                    'statusapprovalmandor',
                    'statusapprovalmandortext',
                    'tglapprovalmandor',
                    'userapprovalmandor',
                    'tarif_id',
                    'mandortrado_id',
                    'mandorsupir_id',
                    'statusgudangsama',
                    'statusgudangsamatext',
                    'statusbatalmuat',
                    'statusbatalmuattext',
                    'modifiedby',
                    'created_at',
                    'updated_at',
                    'flag'
                ], $query);
            }
        } else {
            // dd($class,$user);
            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel with (readuncommitted)")
            )
                ->select(
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            $tempsuratpengantar = $querydata->namatabel;
        }

        $query = DB::table($tempsuratpengantar)->from(db::raw("$tempsuratpengantar as suratpengantar with (readuncommitted)"))
            ->select(
                'suratpengantar.id',
                'suratpengantar.idoriginal',
                'suratpengantar.jobtrucking',
                'suratpengantar.nobukti',
                'suratpengantar.ritasi_nobukti',
                'suratpengantar.tglbukti',
                'suratpengantar.nosp',
                'suratpengantar.tglsp',
                'suratpengantar.nojob',
                'suratpengantar.pelanggan_id',
                'suratpengantar.keterangan',
                'suratpengantar.dari_id',
                'suratpengantar.sampai_id',
                'suratpengantar.keteranganritasi',
                'suratpengantar.gajisupir',
                'suratpengantar.jarak',
                'suratpengantar.penyesuaian',
                'suratpengantar.agen_id',
                'suratpengantar.jenisorder_id',
                'suratpengantar.container_id',
                'suratpengantar.nocont',
                'suratpengantar.noseal',
                'suratpengantar.statuscontainer_id',
                'suratpengantar.gudang',
                'suratpengantar.trado_id',
                'suratpengantar.supir_id',
                'suratpengantar.gandengan_id',
                'suratpengantar.statuslongtrip',
                'suratpengantar.statusperalihan',
                'suratpengantar.statusritasiomset',
                'suratpengantar.statusapprovalmandor',
                'suratpengantar.tglapprovalmandor',
                'suratpengantar.userapprovalmandor',
                'suratpengantar.tarif_id',
                'suratpengantar.mandortrado_id',
                'suratpengantar.mandorsupir_id',
                'suratpengantar.statusgudangsama',
                'suratpengantar.statusbatalmuat',
                'suratpengantar.modifiedby',
                'suratpengantar.created_at',
                'suratpengantar.updated_at',
                'suratpengantar.flag',
                'suratpengantar.gajisupir_nobukti',
                'suratpengantar.prosesgajisupir_nobukti',
                'suratpengantar.statusgajisupir',
                'suratpengantar.tgldarigajisupirheader',
                'suratpengantar.tglsampaigajisupirheader',
                'suratpengantar.tgldariebs',
                'suratpengantar.tglsampaiebs'

            );

        //     ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
        //     ->leftJoin('pelanggan', 'suratpengantar.pelanggan_id', 'pelanggan.id')
        //     ->leftJoin('kota as kotadari', 'kotadari.id', '=', 'suratpengantar.dari_id')
        //     ->leftJoin('kota as kotasampai', 'kotasampai.id', '=', 'suratpengantar.sampai_id')
        //     ->leftJoin('agen', 'suratpengantar.agen_id', 'agen.id')
        //     ->leftJoin('jenisorder', 'suratpengantar.jenisorder_id', 'jenisorder.id')
        //     ->leftJoin('container', 'suratpengantar.container_id', 'container.id')
        //     ->leftJoin('statuscontainer', 'suratpengantar.statuscontainer_id', 'statuscontainer.id')
        //     ->leftJoin('trado', 'suratpengantar.trado_id', 'trado.id')
        //     ->leftJoin('supir', 'suratpengantar.supir_id', 'supir.id')
        //     ->leftJoin('gandengan', 'suratpengantar.gandengan_id', 'gandengan.id')
        //     ->leftJoin('parameter as statuslongtrip', 'suratpengantar.statuslongtrip', 'statuslongtrip.id')
        //     ->leftJoin('parameter as statusperalihan', 'suratpengantar.statusperalihan', 'statusperalihan.id')
        //     ->leftJoin('parameter as statusritasiomset', 'suratpengantar.statusritasiomset', 'statusritasiomset.id')
        //     ->leftJoin('parameter as statusgudangsama', 'suratpengantar.statusgudangsama', 'statusgudangsama.id')
        //     ->leftJoin('parameter as statusbatalmuat', 'suratpengantar.statusbatalmuat', 'statusbatalmuat.id')
        //     ->leftJoin('mandor as mandortrado', 'suratpengantar.mandortrado_id', 'mandortrado.id')
        //     ->leftJoin('mandor as mandorsupir', 'suratpengantar.mandorsupir_id', 'mandorsupir.id')
        //     ->leftJoin('tarif', 'suratpengantar.tarif_id', 'tarif.id');
        // // ->orderBy('suratpengantar.tglbukti', 'desc');
        // if (!$isAdmin) {
        //     if ($isMandor) {
        //         $query->Join(DB::raw($tempmandordetail . " as mandordetail"), 'trado.mandor_id', 'mandordetail.mandor_id');
        //     }
        // }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        // $this->sort($query);
        if ($this->params['sortIndex'] == 'nobukti') {
            $query->orderBy('suratpengantar.' . $this->params['sortIndex'], $this->params['sortOrder'])
                ->orderBy('suratpengantar.flag', $this->params['sortOrder'])
                ->orderBy('suratpengantar.ritasi_nobukti', $this->params['sortOrder']);
        } else {
            $query->orderBy('suratpengantar.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
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
            $table->string('nocont2', 50)->nullable();
            $table->string('noseal', 50)->nullable();
            $table->string('noseal2', 50)->nullable();
            $table->float('omset')->nullable();
            $table->float('nominalperalihan')->nullable();
            $table->float('totalomset')->nullable();
            $table->string('statuscontainer_id')->nullable();
            $table->string('gudang')->nullable();
            $table->string('trado_id')->nullable();
            $table->string('supir_id')->nullable();
            $table->string('gandengan_id')->nullable();
            $table->longText('statuslongtrip')->nullable();
            $table->longText('statuslongtriptext')->nullable();
            $table->longText('statuslangsir')->nullable();
            $table->longText('statuslangsirtext')->nullable();
            $table->longText('statusperalihan')->nullable();
            $table->longText('statusperalihantext')->nullable();
            $table->longText('statusritasiomset')->nullable();
            $table->longText('statusapprovaleditsuratpengantar')->nullable();
            $table->longText('statusapprovaleditsuratpengantartext')->nullable();
            $table->longText('statusapprovalbiayatitipanemkl')->nullable();
            $table->longText('statusapprovalbiayatitipanemkltext')->nullable();
            $table->string('tarif_id')->nullable();
            $table->string('mandortrado_id')->nullable();
            $table->string('mandorsupir_id')->nullable();
            $table->longText('statustolakan')->nullable();
            $table->longText('statustolakantext')->nullable();
            $table->longText('statusgudangsama')->nullable();
            $table->longText('statusgudangsamatext')->nullable();
            $table->longText('statusbatalmuat')->nullable();
            $table->longText('statusbatalmuattext')->nullable();
            $table->string('userapprovaleditsuratpengantar')->nullable();
            $table->string('userapprovalbiayatitipanemkl')->nullable();
            $table->date('tglapprovaleditsuratpengantar')->nullable();
            $table->dateTime('tglbataseditsuratpengantar')->nullable();
            $table->date('tglapprovalbiayatitipanemkl')->nullable();
            $table->string('gajisupir_nobukti')->nullable();
            $table->string('prosesgajisupir_nobukti')->nullable();
            $table->longText('invoice_nobukti')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->longText('statusgajisupir')->nullable();
            $table->longText('statusgajisupirtext')->nullable();
            $table->longText('statusinvoice')->nullable();
            $table->longText('statusinvoicetext')->nullable();
            $table->longText('statusapprovalbiayaextra')->nullable();
            $table->longText('statusapprovalbiayaextratext')->nullable();
            $table->string('userapprovalbiayaextra', 50)->nullable();
            $table->date('tglapprovalbiayaextra')->nullable();
            $table->datetime('tglbatasapprovalbiayaextra')->nullable();

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
            'nocont2',
            'noseal',
            'noseal2',
            'omset',
            'nominalperalihan',
            'totalomset',
            'statuscontainer_id',
            'gudang',
            'trado_id',
            'supir_id',
            'gandengan_id',
            'statuslongtrip',
            'statuslongtriptext',
            'statuslangsir',
            'statuslangsirtext',
            'statusperalihan',
            'statusperalihantext',
            'statusritasiomset',
            'statusapprovaleditsuratpengantar',
            'statusapprovaleditsuratpengantartext',
            'statusapprovalbiayatitipanemkl',
            'statusapprovalbiayatitipanemkltext',
            'tarif_id',
            'mandortrado_id',
            'mandorsupir_id',
            'statustolakan',
            'statustolakantext',
            'statusgudangsama',
            'statusgudangsamatext',
            'statusbatalmuat',
            'statusbatalmuattext',
            'userapprovaleditsuratpengantar',
            'userapprovalbiayatitipanemkl',
            'tglapprovaleditsuratpengantar',
            'tglbataseditsuratpengantar',
            'tglapprovalbiayatitipanemkl',
            'gajisupir_nobukti',
            'prosesgajisupir_nobukti',
            'invoice_nobukti',
            'modifiedby',
            'created_at',
            'updated_at',
            'statusgajisupir',
            'statusgajisupirtext',
            'statusinvoice',
            'statusinvoicetext',
            'statusapprovalbiayaextra',
            'statusapprovalbiayaextratext',
            'userapprovalbiayaextra',
            'tglapprovalbiayaextra',
            'tglbatasapprovalbiayaextra'
        ], $models);
        // dd('test');

        return  $temp;
    }

    public function getOrderanTrucking()
    {
        $nobukti = request()->nobukti;
        $data = DB::table('orderantrucking')->select('orderantrucking.*', 'container.keterangan as container', 'agen.namaagen as agen', 'jenisorder.keterangan as jenisorder', 'pelanggan.namapelanggan as pelanggan')
            ->leftJoin('container', 'orderantrucking.container_id', 'container.id')
            ->leftJoin('agen', 'orderantrucking.agen_id', 'agen.id')
            ->leftJoin('jenisorder', 'orderantrucking.jenisorder_id', 'jenisorder.id')
            ->leftJoin('pelanggan', 'orderantrucking.pelanggan_id', 'pelanggan.id')
            ->leftJoin('tarif', 'orderantrucking.tarif_id', 'tarif.id')
            ->where('orderantrucking.nobukti', $nobukti)
            ->first();

        if ($data == '') {

            $data = DB::table('saldoorderantrucking')->select('saldoorderantrucking.*', 'container.keterangan as container', 'agen.namaagen as agen', 'jenisorder.keterangan as jenisorder', 'pelanggan.namapelanggan as pelanggan')
                ->leftJoin('container', 'saldoorderantrucking.container_id', 'container.id')
                ->leftJoin('agen', 'saldoorderantrucking.agen_id', 'agen.id')
                ->leftJoin('jenisorder', 'saldoorderantrucking.jenisorder_id', 'jenisorder.id')
                ->leftJoin('pelanggan', 'saldoorderantrucking.pelanggan_id', 'pelanggan.id')
                ->leftJoin('tarif', 'saldoorderantrucking.tarif_id', 'tarif.id')
                ->where('saldoorderantrucking.nobukti', $nobukti)
                ->first();
        }

        return $data;
    }


    public function sort($query)
    {
        // if ($this->params['sortIndex'] == 'pelanggan_id') {
        //     return $query->orderBy('pelanggan.namapelanggan', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'dari_id') {
        //     return $query->orderBy('kotadari.kodekota', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'sampai_id') {
        //     return $query->orderBy('kotasampai.kodekota', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'statuscontainer_id') {
        //     return $query->orderBy('statuscontainer.keterangan', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'container_id') {
        //     return $query->orderBy('container.keterangan', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'trado_id') {
        //     return $query->orderBy('trado.kodetrado', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'supir_id') {
        //     return $query->orderBy('supir.namasupir', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'agen_id') {
        //     return $query->orderBy('agen.namaagen', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'jenisorder_id') {
        //     return $query->orderBy('jenisorder.keterangan', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'tarif_id') {
        //     return $query->orderBy('tarif.tujuan', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'mandortrado_id') {
        //     return $query->orderBy('mandortrado.namamandor', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'mandorsupir_id') {
        //     return $query->orderBy('mandorsupir.namamandor', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'ketextra' || $this->params['sortIndex'] == 'biayaextra' || $this->params['sortIndex'] == 'biayatagih' || $this->params['sortIndex'] == 'ketextratagih') {
        //     return $query->orderBy('suratpengantarbiayatambahan.' . $this->params['sortIndex'], $this->params['sortOrder']);
        // } else {
        return $query->orderBy('suratpengantar.' . $this->params['sortIndex'], $this->params['sortOrder']);
        // }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            //     if ($filters['field'] == 'pelanggan_id') {
                            //         $query = $query->where('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                            //     } else if ($filters['field'] == 'dari_id') {
                            //         $query = $query->where('kotadari.kodekota', 'LIKE', "%$filters[data]%");
                            //     } else if ($filters['field'] == 'sampai_id') {
                            //         $query = $query->where('kotasampai.kodekota', 'LIKE', "%$filters[data]%");
                            //     } else if ($filters['field'] == 'statuscontainer_id') {
                            //         $query = $query->where('statuscontainer.keterangan', 'LIKE', "%$filters[data]%");
                            //     } else if ($filters['field'] == 'container_id') {
                            //         $query = $query->where('container.keterangan', 'LIKE', "%$filters[data]%");
                            //     } else if ($filters['field'] == 'trado_id') {
                            //         $query = $query->where('trado.kodetrado', 'LIKE', "%$filters[data]%");
                            //     } else if ($filters['field'] == 'supir_id') {
                            //         $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                            //     } else if ($filters['field'] == 'agen_id') {
                            //         $query = $query->where('agen.namaagen', 'LIKE', "%$filters[data]%");
                            //     } else if ($filters['field'] == 'gandengan_id') {
                            //         $query = $query->where('gandengan.keterangan', 'LIKE', "%$filters[data]%");
                            //     } else if ($filters['field'] == 'jenisorder_id') {
                            //         $query = $query->where('jenisorder.keterangan', 'LIKE', "%$filters[data]%");
                            //     } else if ($filters['field'] == 'tarif_id') {
                            //         $query = $query->where('tarif.tujuan', 'LIKE', "%$filters[data]%");
                            //     } else if ($filters['field'] == 'mandortrado_id') {
                            //         $query = $query->where('mandortrado.namamandor', 'LIKE', "%$filters[data]%");
                            //     } else if ($filters['field'] == 'mandorsupir_id') {
                            //         $query = $query->where('mandorsupir.namamandor', 'LIKE', "%$filters[data]%");
                            //     } else if ($filters['field'] == 'ketextra' || $filters['field'] == 'ketextratagih') {
                            //         $query = $query->where('suratpengantarbiayatambahan.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            //     } else 
                            if ($filters['field'] == 'statuslongtrip') {
                                $query = $query->where('suratpengantar.statuslongtriptext', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusperalihan') {
                                $query = $query->where('suratpengantar.statusperalihantext', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statuslangsir') {
                                $query = $query->where('suratpengantar.statuslangsirtext', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusgudangsama') {
                                $query = $query->where('suratpengantar.statusgudangsamatext', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statustolakan') {
                                $query = $query->where('suratpengantar.statustolakantext', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusbatalmuat') {
                                $query = $query->where('suratpengantar.statusbatalmuattext', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusgajisupir') {
                                $query = $query->where('suratpengantar.statusgajisupirtext', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusinvoice') {
                                $query = $query->where('suratpengantar.statusinvoicetext', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusapprovaleditsuratpengantar') {
                                $query = $query->where('suratpengantar.statusapprovaleditsuratpengantartext', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusapprovalbiayaextra') {
                                $query = $query->where('suratpengantar.statusapprovalbiayaextratext', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusapprovalbiayatitipanemkl') {
                                $query = $query->where('suratpengantar.statusapprovalbiayatitipanemkltext', '=', "$filters[data]");
                            } else if ($filters['field'] == 'gajisupir' || $filters['field'] == 'jarak' || $filters['field'] == 'omset' || $filters['field'] == 'nominalperalihan' || $filters['field'] == 'totalomset') {
                                $query = $query->whereRaw("format(suratpengantar." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'biayaextra' || $filters['field'] == 'biayatagih') {
                                $query = $query->whereRaw("format(suratpengantarbiayatambahan." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglsp' || $filters['field'] == 'tglapprovalbiayaextra' || $filters['field'] == 'tglapprovaleditsuratpengantar') {
                                $query = $query->whereRaw("format(suratpengantar." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at' || $filters['field'] == 'tglbatasapprovalbiayaextra' || $filters['field'] == 'tglbataseditsuratpengantar') {
                                $query = $query->whereRaw("format(suratpengantar." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'statusapprovalmandor') {
                                $query = $query->where('suratpengantar.statusapprovalmandortext', '=', "$filters[data]");
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
                                // if ($filters['field'] == 'pelanggan_id') {
                                //     $query = $query->orWhere('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                                // } else if ($filters['field'] == 'dari_id') {
                                //     $query = $query->orWhere('kotadari.kodekota', 'LIKE', "%$filters[data]%");
                                // } else if ($filters['field'] == 'sampai_id') {
                                //     $query = $query->orWhere('kotasampai.kodekota', 'LIKE', "%$filters[data]%");
                                // } else if ($filters['field'] == 'statuscontainer_id') {
                                //     $query = $query->orWhere('statuscontainer.keterangan', 'LIKE', "%$filters[data]%");
                                // } else if ($filters['field'] == 'container_id') {
                                //     $query = $query->orWhere('container.keterangan', 'LIKE', "%$filters[data]%");
                                // } else if ($filters['field'] == 'trado_id') {
                                //     $query = $query->orWhere('trado.kodetrado', 'LIKE', "%$filters[data]%");
                                // } else if ($filters['field'] == 'supir_id') {
                                //     $query = $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
                                // } else if ($filters['field'] == 'agen_id') {
                                //     $query = $query->orWhere('agen.namaagen', 'LIKE', "%$filters[data]%");
                                // } else if ($filters['field'] == 'gandengan_id') {
                                //     $query = $query->orWhere('gandengan.keterangan', 'LIKE', "%$filters[data]%");
                                // } else if ($filters['field'] == 'jenisorder_id') {
                                //     $query = $query->orWhere('jenisorder.keterangan', 'LIKE', "%$filters[data]%");
                                // } else if ($filters['field'] == 'tarif_id') {
                                //     $query = $query->orWhere('tarif.tujuan', 'LIKE', "%$filters[data]%");
                                // } else if ($filters['field'] == 'ketextra' || $filters['field'] == 'ketextratagih') {
                                //     $query = $query->orWhere('suratpengantarbiayatambahan.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                // } else if ($filters['field'] == 'mandortrado_id') {
                                //     $query = $query->orWhere('mandortrado.namamandor', 'LIKE', "%$filters[data]%");
                                // } else if ($filters['field'] == 'mandorsupir_id') {
                                //     $query = $query->orWhere('mandorsupir.namamandor', 'LIKE', "%$filters[data]%");
                                // } 
                                if ($filters['field'] == 'statuslongtrip') {
                                    $query = $query->orWhere('suratpengantar.statuslongtriptext', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statusgajisupir') {
                                    $query = $query->Orwhere('suratpengantar.statusgajisupirtext', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statusinvoice') {
                                    $query = $query->Orwhere('suratpengantar.statusinvoicetext', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statusperalihan') {
                                    $query = $query->orWhere('suratpengantar.statusperalihantext', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statuslangsir') {
                                    $query = $query->orWhere('suratpengantar.statuslangsirtext', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statusgudangsama') {
                                    $query = $query->orWhere('suratpengantar.statusgudangsamatext', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statustolakan') {
                                    $query = $query->orWhere('suratpengantar.statustolakantext', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statusbatalmuat') {
                                    $query = $query->orWhere('suratpengantar.statusbatalmuattext', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statusapprovaleditsuratpengantar') {
                                    $query = $query->orWhere('suratpengantar.statusapprovaleditsuratpengantartext', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statusapprovalbiayaextra') {
                                    $query = $query->orWhere('suratpengantar.statusapprovalbiayaextratext', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statusapprovalbiayatitipanemkl') {
                                    $query = $query->orWhere('suratpengantar.statusapprovalbiayatitipanemkltext', '=', "$filters[data]");
                                } else if ($filters['field'] == 'gajisupir' || $filters['field'] == 'jarak' || $filters['field'] == 'omset' || $filters['field'] == 'nominalperalihan' || $filters['field'] == 'totalomset') {
                                    $query = $query->orWhereRaw("format(suratpengantar." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'biayaextra' || $filters['field'] == 'biayatagih') {
                                    $query = $query->orWhereRaw("format(suratpengantarbiayatambahan." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglsp' || $filters['field'] == 'tglapprovalbiayaextra' || $filters['field'] == 'tglapprovaleditsuratpengantar') {
                                    $query = $query->orWhereRaw("format(suratpengantar." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at' || $filters['field'] == 'tglbatasapprovalbiayaextra' || $filters['field'] == 'tglbataseditsuratpengantar') {
                                    $query = $query->orWhereRaw("format(suratpengantar." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'statusapprovalmandor') {
                                    $query = $query->orWhere('suratpengantar.statusapprovalmandortext', '=', "$filters[data]");
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

    function returnUnApprovalEdit()
    {
        DB::beginTransaction();
        try {
            $suratPengantar = new SuratPengantar();
            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();
            $now = date('Y-m-d H:i:s', strtotime('now'));
            $data = DB::table("suratpengantar")->where('statusapprovaleditsuratpengantar', $statusApproval->id)
                ->where('tglbataseditsuratpengantar', '<', $now)
                ->update(['statusapprovaleditsuratpengantar' => $statusNonApproval->id]);
            $data = DB::table("suratpengantar")->where('statusapprovalbiayaextra', $statusApproval->id)
                ->where('tglbatasapprovalbiayaextra', '<', $now)
                ->update(['statusapprovalbiayaextra' => $statusNonApproval->id]);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
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
        $ytrado_id = $data['trado_id'] ?? 0;
        $mandor_id = db::table("absensisupirdetail")->from(db::raw("absensisupirdetail a with (readuncommitted)"))
            ->select('a.mandor_id')
            ->join(db::raw("absensisupirheader b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->where('a.trado_id', $ytrado_id)
            ->where('b.tglbukti', date('Y-m-d', strtotime($data['tglbukti'])))
            ->first();


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
            $suratPengantar->mandortrado_id = $mandor_id->mandor_id ?? 0;
            $suratPengantar->statusgudangsama = $data['statusgudangsama'];
            $suratPengantar->statusbatalmuat = $data['statusbatalmuat'];
            $suratPengantar->gudang = $data['gudang'];
            $suratPengantar->statusapprovaleditsuratpengantar = $statusNonApproval->id;
            $suratPengantar->statustolakan = $statusNonApproval->id;
            $suratPengantar->modifiedby = auth('api')->user()->name;
            $suratPengantar->info = html_entity_decode(request()->info);
            $suratPengantar->statusformat = $format->id;

            $suratPengantar->nobukti = (new RunningNumberService)->get($group, $subGroup, $suratPengantar->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));


            $suratPengantar->statusedittujuan = $statusTidakBolehEditTujuan->id;
        } else {
            $jenisTangki = DB::table('parameter')->from(
                DB::raw("parameter as a with (readuncommitted)")
            )
                ->select(
                    'a.id'
                )
                ->where('a.grp', '=', 'STATUS JENIS KENDARAAN')
                ->where('a.subgrp', '=', 'STATUS JENIS KENDARAAN')
                ->where('a.text', '=', 'TANGKI')
                ->first();

            $orderanTrucking = OrderanTrucking::where('nobukti', $data['jobtrucking'])->first();
            if (!isset($orderanTrucking)) {
                $orderanTrucking = DB::table("saldoorderantrucking")->from(DB::raw("saldoorderantrucking with (readuncommitted)"))->where('nobukti', $data['jobtrucking'])->first();
            }
            $suratPengantar->jobtrucking = $data['jobtrucking'];
            $suratPengantar->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
            $suratPengantar->pelanggan_id = $data['pelanggan_id'];
            $suratPengantar->upah_id = $data['upah_id'];
            $suratPengantar->upahsupirtangki_id = $data['upahtangki_id'];
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
            $suratPengantar->statuskandang = $data['statuskandang'];
            $suratPengantar->statuslangsir = $data['statuslangsir'];
            $suratPengantar->statusapprovalmandor = 4;
            $suratPengantar->tarif_id = $data['tarif_id'] ?? '';
            $suratPengantar->tariftangki_id = $data['tariftangki_id'] ?? '';
            $suratPengantar->triptangki_id = $data['triptangki_id'] ?? '';
            $suratPengantar->komisisupir = $upahsupirRincian->nominalkomisi ?? 0;
            $suratPengantar->tolsupir = $upahsupirRincian->nominaltol ?? 0;
            $statuscontainer_id = $data['statuscontainer_id'] ?? 0;
            $idfullempty = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
                ->select(
                    'a.text as id'
                )
                ->where('grp', 'STATUS CONTAINER')
                ->where('subgrp', 'STATUS CONTAINER FULL EMPTY')
                ->first()->id ?? 0;
            if ($data['statusjeniskendaraan'] == $jenisTangki->id) {
                $getJarakTangki = DB::table("upahsupirtangki")->from(DB::raw("upahsupirtangki with (readuncommitted)"))->where('id', $data['upahtangki_id'])->first();
                $suratPengantar->jarak = $getJarakTangki->jarak;
            } else {

                if ($statuscontainer_id == $idfullempty) {
                    $suratPengantar->jarak = $upahsupir->jarakfullempty;
                } else {
                    $suratPengantar->jarak = $upahsupir->jarak;
                }
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
            $suratPengantar->statusjeniskendaraan = $data['statusjeniskendaraan'];
            $suratPengantar->statusedittujuan = $statusTidakBolehEditTujuan->id;
            $suratPengantar->gudang = $data['gudang'];
            $suratPengantar->mandorsupir_id = $supir->mandor_id;
            $suratPengantar->mandortrado_id = $mandor_id->mandor_id ?? 0;
            $suratPengantar->lokasibongkarmuat = $data['lokasibongkarmuat'];
            $suratPengantar->modifiedby = auth('api')->user()->name;
            $suratPengantar->info = html_entity_decode(request()->info);
            $suratPengantar->statusformat = $format->id;
            $suratPengantar->tglbataseditsuratpengantar = $data['tglbataseditsuratpengantar'];
            $suratPengantar->nobukti_tripasal = $data['nobukti_tripasal'];
            $suratPengantar->statuspenyesuaian = $data['statuspenyesuaian'];
            $suratPengantar->statusapprovalbiayatitipanemkl = $statusNonApproval->id;
            $suratPengantar->statustolakan = $statusNonApproval->id;
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
        $ytrado_id = $data['trado_id'] ?? 0;
        // dd(date('Y-m-d', strtotime($data['tglbukti'])));
        $mandor_id = db::table("absensisupirdetail")->from(db::raw("absensisupirdetail a with (readuncommitted)"))
            ->select('a.mandor_id')
            ->join(db::raw("absensisupirheader b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->where('a.trado_id', $ytrado_id)
            ->whereraw("b.tglbukti='" . date('Y-m-d', strtotime($data['tglbukti'])) . "'")
            ->first();

        $cabang = (new Parameter())->cekText('CABANG', 'CABANG');
        $jenisTangki = DB::table('parameter')->from(
            DB::raw("parameter as a with (readuncommitted)")
        )
            ->select(
                'a.id'
            )
            ->where('a.grp', '=', 'STATUS JENIS KENDARAAN')
            ->where('a.subgrp', '=', 'STATUS JENIS KENDARAAN')
            ->where('a.text', '=', 'TANGKI')
            ->first();
        // dd($mandor_id->tosql());
        if ($prosesLain == 2) {
            $suratPengantar->jobtrucking = $data['jobtrucking'];
            $suratPengantar->nocont = $data['nocont'] ?? '';
            $suratPengantar->nocont2 =  $data['nocont2'] ?? '';
            $suratPengantar->nojob =  $data['nojob'] ?? '';
            $suratPengantar->nojob2 = $data['nojob2'] ?? '';
            $suratPengantar->noseal = $data['noseal'] ?? '';
            $suratPengantar->noseal2 = $data['noseal2'] ?? '';
            $suratPengantar->gandengan_id = $data['gandengan_id']  ?? '';
            $suratPengantar->container_id = $data['container_id']  ?? '';
            $suratPengantar->agen_id = $data['agen_id']  ?? '';
            $suratPengantar->jenisorder_id = $data['jenisorder_id']  ?? '';
            $suratPengantar->pelanggan_id = $data['pelanggan_id']  ?? '';

            $suratPengantar->nocontold = $data['nocontold'] ?? '';
            $suratPengantar->nocont2old =  $data['nocont2old'] ?? '';
            $suratPengantar->nojobold =  $data['nojobold'] ?? '';
            $suratPengantar->nojob2old = $data['nojob2old'] ?? '';
            $suratPengantar->nosealold = $data['nosealold'] ?? '';
            $suratPengantar->noseal2old = $data['noseal2old'] ?? '';
            $suratPengantar->gandenganold_id = $data['gandenganold_id']  ?? '';
            $suratPengantar->containerold_id = $data['containerold_id']  ?? '';
            $suratPengantar->agenold_id = $data['agenold_id']  ?? '';
            $suratPengantar->jenisorderold_id = $data['jenisorderold_id']  ?? '';
            $suratPengantar->pelangganold_id = $data['pelangganold_id']  ?? '';

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

            goto lanjut;
        }
        if ($prosesLain == 0) {
            $trado = Trado::find($data['trado_id']);
            $supir = Supir::find($data['supir_id']);
            $edittripmandor = $data['edittripmandor'] ?? 0;
            // if ($orderanTrucking == '') {
            $container = $data['container_id'] ?? $orderanTrucking->container_id;
            $pelanggan = $data['pelanggan_id'] ?? $orderanTrucking->pelanggan_id;
            // } else {
            //     $container = $orderanTrucking->container_id;
            //     $pelanggan = $orderanTrucking->pelanggan_id;
            // }

            $data['upahtangki_id'] = 0;
            $data['tariftangki_id'] = 0;
            if ($data['statusjeniskendaraan'] == $jenisTangki->id) {

                $data['upahtangki_id'] = $data['upah_id'];
                $data['upah_id'] = '';
                $data['tariftangki_id'] = $data['tarif_id'];
                $data['tarif_id'] = '';

                $tarif = DB::table("tariftangki")->where('id', $data['tariftangki_id'])->first()->nominal ?? 0;
                $total = round($tarif * $data['qtyton']);
                $tarifNominal = $total;

                $upahsupir = DB::table("upahsupirtangki")->where('id', $data['upahtangki_id'])->first();
                $suratPengantar->triptangki_id = $data['triptangki_id'];
            } else {

                if ($data['statuslongtrip'] == 66) {
                    $tarif = TarifRincian::where('tarif_id', $data['tarif_id'])->where('container_id', $container)->first();
                }
                $parameter = new Parameter();
                $idkandang = $parameter->cekText('KANDANG', 'KANDANG') ?? 0;
                if ($data['dari_id'] == $idkandang) {
                    $tarif = TarifRincian::where('tarif_id', $data['tarif_id'])->where('container_id', $container)->first();
                }
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
                $nominalSupir = $upahsupirRincian->nominalsupir ?? 0;
                // }

            }

            if ($edittripmandor == 0 && $cabang == 'MEDAN') {
                if ($suratPengantar->dari_id != 1) {
                    if ($data['dari_id'] == 1 && $data['dari_id'] != $suratPengantar->dari_id) {
                        if ($data['jobtrucking'] == '') {
                            $statusperalihan = DB::table('parameter')->from(
                                DB::raw("parameter as a with (readuncommitted)")
                            )
                                ->select(
                                    'a.id'
                                )
                                ->where('a.grp', '=', 'STATUS PERALIHAN')
                                ->where('a.subgrp', '=', 'STATUS PERALIHAN')
                                ->where('a.text', '=', 'BUKAN PERALIHAN')
                                ->first();
                            $statuslangsir = DB::table('parameter')->from(
                                DB::raw("parameter as a with (readuncommitted)")
                            )
                                ->select(
                                    'a.id'
                                )
                                ->where('a.grp', '=', 'STATUS LANGSIR')
                                ->where('a.subgrp', '=', 'STATUS LANGSIR')
                                ->where('a.text', '=', 'LANGSIR')
                                ->first();

                            $tglBatasEdit = date('Y-m-d', strtotime($data['tglbukti'])) . ' ' . '12:00:00';
                            $orderan = [
                                'tglbukti' => $data['tglbukti'],
                                'container_id' => $data['container_id'],
                                'agen_id' => $data['agen_id'],
                                'jenisorder_id' => $data['jenisorder_id'],
                                'pelanggan_id' => $data['pelanggan_id'],
                                'tarifrincian_id' => $data['tarif_id'],
                                'statusjeniskendaraan' => $data['statusjeniskendaraan'],
                                'nojobemkl' => $data['nojobemkl'] ?? '',
                                'nocont' => $data['nocont'] ?? '',
                                'noseal' => $data['noseal'] ?? '',
                                'nojobemkl2' => $data['nojobemkl2'] ?? '',
                                'nocont2' => $data['nocont2'] ?? '',
                                'noseal2' => $data['noseal2'] ?? '',
                                'statuslangsir' => $statuslangsir->id,
                                'gandengan_id' => $data['gandengan_id'],
                                'statusperalihan' => $statusperalihan->id,
                                'tglbataseditorderantrucking' => $tglBatasEdit,
                                'inputtripmandor' =>  '1',
                            ];
                            $orderanTrucking = (new OrderanTrucking())->processStore($orderan);
                            $data['jobtrucking'] = $orderanTrucking->nobukti;
                        }
                    }
                }
            }

            if ($cabang == 'SURABAYA') {
                // jika awalnya longtrip
                if ($suratPengantar->statuslongtrip == 65) {
                    // awalnya longtrip, diubah jadi bukan longtrip
                    if ($data['statuslongtrip'] == 66) {
                        $dataTripAsal = [
                            'nobukti_tripasal' => $suratPengantar->nobukti_tripasal
                        ];
                        (new SuratPengantar())->updateStatusContainerLongtrip($dataTripAsal, 'DELETE');
                    }
                } else {
                    // awalnya bukan longtrip, lalu diubah jadi longtrip
                    if ($data['statuslongtrip'] == 65) {
                        $dataTripAsal = [
                            'nobukti_tripasal' => $data['nobukti_tripasal']
                        ];
                        (new SuratPengantar())->updateStatusContainerLongtrip($dataTripAsal, 'ADD');
                    }
                }
            }
            $suratPengantar->jobtrucking = $data['jobtrucking'];
            $suratPengantar->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
            $suratPengantar->pelanggan_id = $pelanggan;
            $suratPengantar->keterangan = $data['keterangan'] ?? '';
            $suratPengantar->nourutorder = $data['nourutorder'] ?? 1;
            $suratPengantar->upah_id = $data['upah_id'] ?? '';
            $suratPengantar->upahsupirtangki_id = $data['upahtangki_id'];
            $suratPengantar->dari_id = $data['dari_id'];
            $suratPengantar->sampai_id = $data['sampai_id'];
            $suratPengantar->zonadari_id = $data['zonadari_id'] ?? '';
            $suratPengantar->zonasampai_id = $data['zonasampai_id'] ?? '';
            $suratPengantar->container_id = $container;
            $suratPengantar->statuscontainer_id = $data['statuscontainer_id'];
            $suratPengantar->statusgandengan = $data['statusgandengan'];
            $suratPengantar->trado_id = $data['trado_id'];
            $suratPengantar->supir_id = $data['supir_id'];
            $suratPengantar->gandengan_id = $data['gandengan_id'] ?? 0;
            if ($cabang == 'MEDAN') {
                $suratPengantar->nocont = $data['nocont'] ?? '';
                $suratPengantar->nocont2 = $data['nocont2'] ?? '';
                $suratPengantar->noseal = $data['noseal'] ?? '';
                $suratPengantar->noseal2 = $data['noseal2'] ?? '';
            } else {
                $suratPengantar->nocont = $orderanTrucking->nocont ?? '';
                $suratPengantar->nocont2 = $orderanTrucking->nocont2 ?? '';
                $suratPengantar->noseal = $orderanTrucking->noseal ?? '';
                $suratPengantar->noseal2 = $orderanTrucking->noseal2 ?? '';
            }
            $suratPengantar->nojob = $orderanTrucking->nojobemkl ?? '';
            $suratPengantar->nojob2 = $orderanTrucking->nojobemkl2 ?? '';
            $suratPengantar->statuslongtrip = $data['statuslongtrip'];
            $suratPengantar->omset = $tarifNominal;
            $suratPengantar->gajisupir = $data['gajisupir'];
            $suratPengantar->agen_id = $data['agen_id'] ?? $orderanTrucking->agen_id;
            $suratPengantar->penyesuaian = $data['penyesuaian'];
            $suratPengantar->jenisorder_id = $data['jenisorder_id'] ?? $orderanTrucking->jenisorder_id;
            $suratPengantar->statusperalihan = $data['statusperalihan'];
            $suratPengantar->statusupahzona = $data['statusupahzona'];
            $suratPengantar->statuskandang = $data['statuskandang'];
            $suratPengantar->statuslangsir = $data['statuslangsir'];
            $suratPengantar->statustolakan = $data['statustolakan'] ?? 4;
            $suratPengantar->tarif_id = $data['tarif_id'] ?? '';
            $suratPengantar->tariftangki_id = $data['tariftangki_id'] ?? '';
            // $nominalPeralihan = 0;
            // if ($data['persentaseperalihan'] != 0) {
            //     $nominalPeralihan = ($tarifNominal * ($data['persentaseperalihan'] / 100));
            // }

            // if (trim($isKomisiReadonly->text) == 'TIDAK') {
            $suratPengantar->komisisupir = $data['komisisupir'];
            $suratPengantar->gajikenek = $data['gajikenek'];
            // } else {
            //     $suratPengantar->komisisupir = $upahsupirRincian->nominalkomisi;
            //     $suratPengantar->gajikenek = $upahsupirRincian->nominalkenek;
            // }
            $totalOmset = $tarifNominal - $data['nominalperalihan'];
            if ($data['statuslongtrip'] == 65) {
                $totalOmset = $data['nominalperalihan'];
            }
            $suratPengantar->nominalperalihan = $data['nominalperalihan'];
            $suratPengantar->persentaseperalihan = $data['persentaseperalihan'];
            $suratPengantar->discount = $data['persentaseperalihan'];
            $suratPengantar->totalomset = $totalOmset;
            $suratPengantar->biayatambahan_id = $data['biayatambahan_id'] ?? 0;
            $suratPengantar->nosp = $data['nosp'];
            $suratPengantar->tglsp = date('Y-m-d', strtotime($data['tglbukti']));
            $suratPengantar->tolsupir = $upahsupirRincian->nominaltol ?? 0;
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
            $suratPengantar->totalton = $tarifNominal;
            $suratPengantar->mandorsupir_id = $supir->mandor_id;
            $suratPengantar->mandortrado_id = $mandor_id->mandor_id ?? 0;
            $suratPengantar->statusgudangsama = $data['statusgudangsama'];
            $suratPengantar->statusbatalmuat = $data['statusbatalmuat'];
            $suratPengantar->gudang = $data['gudang'];
            $suratPengantar->lokasibongkarmuat = $data['lokasibongkarmuat'];
            $suratPengantar->nobukti_tripasal = $data['nobukti_tripasal'];
            $suratPengantar->statuspenyesuaian = $data['statuspenyesuaian'];
            $suratPengantar->editing_by = '';
            $suratPengantar->editing_at = null;
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

            $jobmanual = (new Parameter())->cekText('JOB TRUCKING MANUAL', 'JOB TRUCKING MANUAL') ?? 'TIDAK';
            // if ($jobmanual == 'YA') {

            if ($cabang == 'MEDAN' || $cabang == 'BITUNG') {
                $suratPengantar->nocont = $data['nocont'] ?? '';
                $suratPengantar->nocont2 = $data['nocont2'] ?? '';

                $suratPengantar->noseal = $data['noseal'] ?? '';
                $suratPengantar->noseal2 = $data['noseal2'] ?? '';
                $suratPengantar->save();
                if ($suratPengantar->jobtrucking != '') {
                    DB::update(DB::raw("UPDATE SURATPENGANTAR SET nocont='$suratPengantar->nocont',nocont2='$suratPengantar->nocont2',noseal='$suratPengantar->noseal',noseal2='$suratPengantar->noseal2',agen_id='$suratPengantar->agen_id',jenisorder_id='$suratPengantar->jenisorder_id',pelanggan_id='$suratPengantar->pelanggan_id',container_id='$suratPengantar->container_id',gandengan_id='$suratPengantar->gandengan_id' where jobtrucking='$suratPengantar->jobtrucking'"));

                    DB::update(DB::raw("UPDATE orderantrucking SET nocont='$suratPengantar->nocont',nocont2='$suratPengantar->nocont2',noseal='$suratPengantar->noseal',noseal2='$suratPengantar->noseal2',agen_id='$suratPengantar->agen_id',jenisorder_id='$suratPengantar->jenisorder_id',pelanggan_id='$suratPengantar->pelanggan_id',container_id='$suratPengantar->container_id',gandengan_id='$suratPengantar->gandengan_id' where nobukti='$suratPengantar->jobtrucking'"));
                }
                if ($suratPengantar->jobtrucking != '' && $cabang == 'MEDAN') {
                    $statuscontainerEmpty = DB::table("statuscontainer")->from(DB::raw("statuscontainer with (readuncommitted)"))->where('kodestatuscontainer', 'EMPTY')->first()->id;

                    if ($suratPengantar->statuscontainer_id == $statuscontainerEmpty) {
                        DB::update(DB::raw("UPDATE orderantrucking SET nospempty='$suratPengantar->nosp' where nobukti='$suratPengantar->jobtrucking'"));
                    } else {
                        DB::update(DB::raw("UPDATE orderantrucking SET nospfull='$suratPengantar->nosp' where nobukti='$suratPengantar->jobtrucking'"));
                    }
                }
            }
            if ($cabang == 'MEDAN') {
                $cekRic = DB::table("gajisupirdetail")->from(DB::raw("gajisupirdetail with (readuncommitted)"))
                    ->where('suratpengantar_nobukti', $suratPengantar->nobukti)
                    ->first();

                if ($cekRic != '') {
                    $dataRic = [
                        'id_detail' => $cekRic->id,
                        'nobukti' => $cekRic->nobukti,
                        'suratpengantar_nobukti' => $suratPengantar->nobukti,
                        'ritasi_nobukti' => $cekRic->ritasi_nobukti,
                        'gajisupir' => $suratPengantar->gajisupir,
                        'komisisupir' => $suratPengantar->komisisupir,
                        'gajiritasi' => $cekRic->gajiritasi,
                    ];
                    (new GajiSupirHeader())->processUpdateTrip($dataRic, 'edit');
                }
            }
            // }
        } else {
            if ($suratPengantar->statusjeniskendaraan == $jenisTangki->id) {

                $suratPengantar->agen_id = $data['agen_id'];
                $suratPengantar->pelanggan_id = $data['pelanggan_id'];
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

                $idkandang = (new Parameter())->cekText('KANDANG', 'KANDANG') ?? 0;
                if (($suratPengantar->dari_id == 1 && $suratPengantar->sampai_id == $idkandang) || ($suratPengantar->dari_id == $idkandang && $suratPengantar->sampai_id == 1)) {
                    $tarifId = $suratPengantar->tarifrincian_id;
                    goto cek;
                }
                if ($suratPengantar->statuslongtrip == 65) {
                    $tarifId = $suratPengantar->tarifrincian_id;
                    goto cek;
                }
                if ($suratPengantar->statuslongtrip == 66 && $suratPengantar->nobukti_tripasal != '') {
                    $tarifId = $suratPengantar->tarifrincian_id;
                    goto cek;
                }
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

                $tarifId = $getTarif->tarif_id ?? 0;
                cek:
                $upahsupirRincian = UpahSupirRincian::where('upahsupir_id', $suratPengantar->upah_id)->where('container_id', $data['container_id'])->where('statuscontainer_id', $suratPengantar->statuscontainer_id)->first();
                $tarif = TarifRincian::where('tarif_id', $tarifId)->where('container_id', $data['container_id'])->first();
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
                $oldContainer = $suratPengantar->container_id;

                if ($oldContainer != $data['container_id']) {
                    $parameter = new Parameter();
                    $idstatuskandang = $parameter->cekId('STATUS KANDANG', 'STATUS KANDANG', 'KANDANG') ?? 0;
                    $idkandang = $parameter->cekText('KANDANG', 'KANDANG') ?? 0;
                    // $idpelabuhan = $parameter->cekText('PELABUHAN CABANG', 'PELABUHAN CABANG') ?? 0;
                    $statuspelabuhan = $parameter->cekId('STATUS PELABUHAN', 'STATUS PELABUHAN', 'PELABUHAN') ?? 0;
                    $idpelabuhan = db::table("kota")->from(db::raw("kota a with (readuncommitted)"))
                        ->select(
                            db::raw("STRING_AGG(id,',') as id"),
                        )
                        ->where('a.statuspelabuhan', $statuspelabuhan)
                        ->first()->id ?? 1;


                    $upahsupirkandnag = db::table("upahsupir")->from(db::raw("upahsupir a with (readuncommitted)"))
                        ->select(
                            'b.id',
                            'a.kotadari_id',
                            'a.kotasampai_id',
                            'b.upahsupir_id',
                            'b.container_id',
                            'b.statuscontainer_id',
                            'b.nominalsupir',
                            'b.nominalkenek',
                            'b.nominalkomisi',
                            'b.nominaltol',
                            'b.liter',
                            'b.tas_id',
                            'b.info',
                            'b.modifiedby',
                        )
                        ->join(db::raw("upahsupirrincian b with (readuncommitted)"), 'a.id', 'b.upahsupir_id')
                        ->whereraw("a.kotadari_id in(" . $idpelabuhan . ")")
                        ->where('a.kotasampai_id', $idkandang)
                        ->where('b.container_id', $data['container_id'])
                        ->where('b.statuscontainer_id', $suratPengantar->statuscontainer_id)
                        ->whereraw("isnull(a.penyesuaian,'')=''");

                    $tempupahsupirkandang = '##tempupahsupirkandang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                    Schema::create($tempupahsupirkandang, function ($table) {
                        $table->bigInteger('id')->nullable();
                        $table->unsignedBigInteger('kotadari_id')->nullable();
                        $table->unsignedBigInteger('kotasampai_id')->nullable();
                        $table->unsignedBigInteger('upahsupir_id')->nullable();
                        $table->unsignedBigInteger('container_id')->nullable();
                        $table->unsignedBigInteger('statuscontainer_id')->nullable();
                        $table->double('nominalsupir', 15, 2)->nullable();
                        $table->double('nominalkenek', 15, 2)->nullable();
                        $table->double('nominalkomisi', 15, 2)->nullable();
                        $table->double('nominaltol', 15, 2)->nullable();
                        $table->double('liter', 15, 2)->nullable();
                        $table->unsignedBigInteger('tas_id')->nullable();
                        $table->longText('info')->nullable();
                        $table->string('modifiedby', 50)->nullable();
                    });

                    DB::table($tempupahsupirkandang)->insertUsing([
                        'id',
                        'kotadari_id',
                        'kotasampai_id',
                        'upahsupir_id',
                        'container_id',
                        'statuscontainer_id',
                        'nominalsupir',
                        'nominalkenek',
                        'nominalkomisi',
                        'nominaltol',
                        'liter',
                        'tas_id',
                        'info',
                        'modifiedby',
                    ],  $upahsupirkandnag);

                    $querynominal = db::table($tempupahsupirkandang)->from(db::raw($tempupahsupirkandang . " a"))
                        ->select(
                            'a.nominalsupir',
                            'a.nominalkenek',
                            'a.nominalkomisi',
                        )->first();

                    if (isset($querynominal)) {
                        $nominalsupirkandang = $querynominal->nominalsupir ?? 0;
                        $nominalkenekkandang = $querynominal->nominalkenek ?? 0;
                        $nominalkomisikandang = $querynominal->nominalkomisi ?? 0;
                    } else {
                        $nominalsupirkandang = 0;
                        $nominalkenekkandang = 0;
                        $nominalkomisikandang = 0;
                    }

                    if ($suratPengantar->statuskandang == $idstatuskandang) {

                        $suratPengantar->gajikenek = $upahsupirRincian->nominalkenek - $nominalkenekkandang;
                        $suratPengantar->komisisupir = $upahsupirRincian->nominalkomisi - $nominalkomisikandang;
                        $suratPengantar->gajisupir = $upahsupirRincian->nominalsupir - $nominalsupirkandang;
                    } else {


                        $suratPengantar->gajikenek = $upahsupirRincian->nominalkenek;
                        $suratPengantar->komisisupir = $upahsupirRincian->nominalkomisi;
                        $suratPengantar->gajisupir = $upahsupirRincian->nominalsupir;
                    }
                }
                $updatenocont = $data['updatenocont'] ?? 0;
                if ($cabang == 'MEDAN' && $updatenocont == 0) {
                    $suratPengantar->nosp = $data['nosp'];
                }

                $suratPengantar->pelanggan_id = $data['pelanggan_id'];
                $suratPengantar->container_id = $data['container_id'];
                $suratPengantar->gandengan_id = $data['gandengan_id'];
                $suratPengantar->tarif_id = $tarifId;
                $suratPengantar->nojob = $data['nojob'];
                $suratPengantar->nojob2 = $data['nojob2'] ?? '';
                $suratPengantar->nocont = $data['nocont'] ?? '';
                $suratPengantar->nocont2 = $data['nocont2'] ?? '';
                $suratPengantar->noseal = $data['noseal'] ?? '';
                $suratPengantar->noseal2 = $data['noseal2'] ?? '';
                $suratPengantar->agen_id = $data['agen_id'];
                $suratPengantar->jenisorder_id = $data['jenisorder_id'];
                // $suratPengantar->gajisupir = $nominalSupir;
                // if (trim($isKomisiReadonly->text) == 'YA') {
                //     $suratPengantar->gajikenek = $upahsupirRincian->nominalkenek;
                //     $suratPengantar->komisisupir = $upahsupirRincian->nominalkomisi;
                // }
                $suratPengantar->tolsupir = $upahsupirRincian->nominaltol;
                $suratPengantar->liter = $upahsupirRincian->liter ?? 0;
                $suratPengantar->omset = $tarifNominal;
                // $nominalPeralihan = 0;
                // if ($suratPengantar->persentaseperalihan != 0) {
                //     $nominalPeralihan = ($tarifNominal * ($suratPengantar->persentaseperalihan / 100));
                // }

                // $suratPengantar->mandorsupir_id = $supir->mandor_id;
                // $suratPengantar->mandortrado_id = $trado->mandor_id;
                $totalOmset = $tarifNominal - $suratPengantar->nominalperalihan;
                if ($suratPengantar->statuslongtrip == 65) {
                    $totalOmset = $suratPengantar->nominalperalihan;
                }

                // if ($suratPengantar->statuslongtrip == 66 && $suratPengantar->nobukti_tripasal != '') {
                //     $totalOmset = $suratPengantar->nominalperalihan;
                // }
                $suratPengantar->nominalperalihan = $suratPengantar->nominalperalihan;
                $suratPengantar->persentaseperalihan = $suratPengantar->persentaseperalihan;
                $suratPengantar->totalomset = $totalOmset;

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
        }
        lanjut:

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
        if ($suratPengantar->statuslongtrip == 65) {
            $cekSP = DB::table("orderantrucking")->from(DB::raw("orderantrucking with (readuncommitted)"))->where('nobukti', $suratPengantar->jobtrucking)->first();
            (new OrderanTrucking())->processDestroy($cekSP->id);
            $cabang = (new Parameter())->cekText('CABANG', 'CABANG');
            if ($cabang == 'SURABAYA') {
                $dataTripAsal = [
                    'nobukti_tripasal' => $suratPengantar->nobukti_tripasal
                ];
                (new SuratPengantar())->updateStatusContainerLongtrip($dataTripAsal, 'DELETE');
            }
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

    public function getExport()
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
            'kotadari.kodekota as dari_id',
            'kotasampai.kodekota as sampai_id',
            'suratpengantar.penyesuaian',
            'suratpengantar.gajisupir',
            'suratpengantar.totalomset',
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
            DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
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
            ->leftJoin('tarif', 'suratpengantar.tarif_id', 'tarif.id');
        $tempsuratpengantar = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsuratpengantar, function ($table) {
            $table->integer('id')->nullable();
            $table->string('jobtrucking', 50)->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('nosp', 50)->nullable();
            $table->date('tglsp')->nullable();
            $table->string('nojob', 50)->nullable();
            $table->longText('pelanggan_id')->nullable();
            $table->longText('keterangan')->nullable();
            $table->longText('dari_id')->nullable();
            $table->longText('sampai_id')->nullable();
            $table->longText('penyesuaian')->nullable();
            $table->decimal('gajisupir', 15, 2)->nullable();
            $table->decimal('totalomset', 15, 2)->nullable();
            $table->decimal('jarak', 15, 2)->nullable();
            $table->longText('agen_id')->nullable();
            $table->longText('jenisorder_id')->nullable();
            $table->longText('container_id')->nullable();
            $table->string('nocont', 50)->nullable();
            $table->string('noseal', 50)->nullable();
            $table->string('statuscontainer_id')->nullable();
            $table->string('gudang')->nullable();
            $table->string('trado_id')->nullable();
            $table->string('supir_id')->nullable();
            $table->string('gandengan_id')->nullable();
            $table->longText('statuslongtrip')->nullable();
            $table->longText('statusperalihan')->nullable();
            $table->longText('statusritasiomset')->nullable();
            $table->longText('tarif_id')->nullable();
            $table->longText('mandortrado_id')->nullable();
            $table->longText('mandorsupir_id')->nullable();
            $table->longText('tglcetak')->nullable();
            $table->longText('usercetak')->nullable();
        });

        DB::table($tempsuratpengantar)->insertUsing([
            'id',
            'jobtrucking',
            'nobukti',
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
            'totalomset',
            'jarak',
            'agen_id',
            'jenisorder_id',
            'container_id',
            'nocont',
            'noseal',
            'statuscontainer_id',
            'gudang',
            'trado_id',
            'supir_id',
            'gandengan_id',
            'statuslongtrip',
            'statusperalihan',
            'statusritasiomset',
            'tarif_id',
            'mandortrado_id',
            'mandorsupir_id',
            'tglcetak',
            'usercetak'
        ], $query);

        $query = DB::table($tempsuratpengantar)->from(db::raw("$tempsuratpengantar as suratpengantar with (readuncommitted)"));
        $this->filter($query);
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

    public function approvalGabungJobtrucking(array $data)
    {
        $parameter = new Parameter();

        // $pelabuhancabang = $parameter->cekText('PELABUHAN CABANG', 'PELABUHAN CABANG') ?? '0';
        $statuspelabuhan = $parameter->cekId('STATUS PELABUHAN', 'STATUS PELABUHAN', 'PELABUHAN') ?? 0;
        $pelabuhancabang = db::table("kota")->from(db::raw("kota a with (readuncommitted)"))
            ->select(
                db::raw("STRING_AGG(id,',') as id"),
            )
            ->where('a.statuspelabuhan', $statuspelabuhan)
            ->first()->id ?? 1;

        $bjumlah = 0;
        $orderanTruckingId = [];
        for ($i = 0; $i < count($data['nobukti']); $i++) {
            $nobukti = $data['nobukti'][$i] ?? '';
            $querypelabuhan = db::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
                ->select(
                    'a.jobtrucking',
                    'a.nobukti'
                )
                ->where('a.nobukti', $nobukti)
                ->whereraw("(a.dari_id in(" . $pelabuhancabang . ") or isnull(a.statuslongtrip,0)=65)")
                ->first();

            if (isset($querypelabuhan)) {
                $bjumlah = $bjumlah + 1;
                $nobuktipelabuhan = $querypelabuhan->nobukti ?? '';
            }
        }

        if ($bjumlah == 1) {




            $queryutama = db::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
                ->select(
                    'a.jobtrucking',
                    'a.nocont',
                    'a.nocont2',
                    'a.noseal',
                    'a.noseal2',
                    'a.nojob',
                    'a.nojob2',
                    'a.pelanggan_id',
                    'a.penyesuaian',
                    'a.container_id',
                    'a.trado_id',
                    'a.gandengan_id',
                    'a.agen_id',
                    'a.jenisorder_id',
                    'a.tarif_id',
                    'b.statusgerobak'
                )
                ->join(db::raw("trado b with (readuncommitted)"), 'a.trado_id', 'b.id')
                ->where('a.nobukti', $nobuktipelabuhan)
                ->first();


            $pelanggan_id = $queryutama->pelanggan_id;
            $penyesuaian = $queryutama->penyesuaian;
            $container_id = $queryutama->container_id;
            $trado_id = $queryutama->trado_id;
            $gandengan_id = $queryutama->gandengan_id;
            $agen_id = $queryutama->agen_id;
            $jenisorder_id = $queryutama->jenisorder_id;
            $tarif_id = $queryutama->tarif_id;
            $statusgerobak = $queryutama->statusgerobak;
            $nocont = $queryutama->nocont;
            $nocont2 = $queryutama->nocont2;
            $noseal = $queryutama->noseal;
            $noseal2 = $queryutama->noseal2;
            $nojob = $queryutama->nojob;
            $nojob2 = $queryutama->nojob2;
            $jobtrucking = $queryutama->jobtrucking;

            for ($i = 0; $i < count($data['nobukti']); $i++) {
                $nobukti = $data['nobukti'][$i] ?? '';
                if ($nobukti != $nobuktipelabuhan) {
                    $querysp = db::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
                        ->select(
                            'a.id',
                            'a.tglbukti',
                            'a.jobtrucking',
                            'a.nocontold',
                            'a.nosealold',
                            'a.nojobold',
                            'a.nocont2old',
                            'a.noseal2old',
                            'a.nojob2old',
                            'a.gandenganold_id',
                            'a.containerold_id',
                            'a.agenold_id',
                            'a.jenisorderold_id',
                            'a.pelangganold_id',
                            'a.nocont',
                            'a.noseal',
                            'a.nojob',
                            'a.nocont2',
                            'a.noseal2',
                            'a.nojob2',
                            'a.gandengan_id',
                            'a.container_id',
                            'a.agen_id',
                            'a.jenisorder_id',
                            'a.pelanggan_id',

                        )
                        ->where('a.nobukti', $nobukti)
                        ->first();

                    $buktijob = $querysp->jobtrucking ?? '';
                    if ($buktijob != '') {
                        $jobtrucking = '';
                        $nocont = $querysp->nocontold ?? '';
                        $noseal = $querysp->nosealold ?? '';
                        $nojob = $querysp->nojobold ?? '';
                        $nojob2 = $querysp->nojob2old ?? '';
                        $nocont2 = $querysp->nocont2old ?? '';
                        $noseal2 = $querysp->noseal2old ?? '';
                        $pelanggan_id = $querysp->pelangganold_id ?? '';
                        $jenisorder_id = $querysp->jenisorderold_id ?? '';
                        $agen_id = $querysp->agenold_id ?? '';
                        $gandengan_id = $querysp->gandenganold_id ?? '';
                        $container_id = $querysp->containerold_id ?? '';
                    }

                    $suratPengantar = [
                        'proseslain' => '2',
                        'jobtrucking' => $jobtrucking,
                        'tglbukti' =>  $querysp->tglbukti,
                        'nojob' =>  $nojob ?? '',
                        'gandengan_id' =>  $gandengan_id ?? '',
                        'nocont' =>  $nocont ?? '',
                        'noseal' =>  $noseal ?? '',
                        'nojob2' =>  $nojob2 ?? '',
                        'nocont2' =>  $nocont2 ?? '',
                        'noseal2' =>  $noseal2 ?? '',
                        'container_id' => $container_id,
                        'agen_id' => $agen_id,
                        'jenisorder_id' => $jenisorder_id,
                        'pelanggan_id' => $pelanggan_id,

                        'nojobold' =>  $querysp->nojob ?? '',
                        'gandenganold_id' =>  $querysp->gandengan_id ?? 0,
                        'nocontold' =>  $querysp->nocont ?? '',
                        'nosealold' =>  $querysp->noseal ?? '',
                        'nojob2old' =>  $querysp->nojob2 ?? '',
                        'nocont2old' =>  $querysp->nocont2 ?? '',
                        'noseal2old' =>  $querysp->noseal2 ?? '',
                        'containerold_id' => $querysp->container_id ?? 0,
                        'agenold_id' => $querysp->agen_id ?? 0,
                        'jenisorderold_id' => $querysp->jenisorder_id ?? 0,
                        'pelangganold_id' => $querysp->pelanggan_id ?? 0,

                        // 'tarif_id' => $data['tarifrincian_id'],
                        'postingdari' => 'APPROVAL GABUNG JOB TRUCKING'
                    ];
                    $newSuratPengantar = new SuratPengantar();
                    $newSuratPengantar = $newSuratPengantar->findAll($querysp->id);
                    (new SuratPengantar())->processUpdate($newSuratPengantar, $suratPengantar);
                }
            }
        }
    }
    public function approvalEditTujuan(array $data)
    {

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        $jambatas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', '=', 'JAMBATASAPPROVAL')->where('subgrp', '=', 'JAMBATASAPPROVAL')->first();
        $tglbatas = date('Y-m-d') . ' ' . $jambatas->text ?? '00:00:00';

        $orderanTruckingId = [];
        for ($i = 0; $i < count($data['nobukti']); $i++) {

            $nobukti = $data['nobukti'][$i] ?? '';
            $querysuratpengantar = DB::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
                ->select(
                    'a.id'
                )->where('a.nobukti', $nobukti)
                ->first();

            $queridorderantrucking = db::table('suratpengantar')->from(db::raw("suratpengantar a with (readuncommitted)"))
                ->select(
                    'b.id'
                )
                ->join(db::raw("orderantrucking b with (readuncommitted)"), 'a.jobtrucking', 'b.nobukti')
                ->where('a.nobukti', $nobukti)
                ->first();


            $orderanTruckingId[] = $queridorderantrucking->id ?? 0;


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

        // dd($orderanTruckingId);
        $orderantruckingUpdate = [
            'orderanTruckingId' => $orderanTruckingId,
        ];
        (new OrderanTrucking())->processApprovalEdit($orderantruckingUpdate);


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

    public function getTolakan($id)
    {
        $query = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->select(DB::raw("isnull(suratpengantar.statustolakan, 4) as statustolakan, isnull(omset,0) as omsettolakan, nominalperalihan as nominalperalihantolakan, persentaseperalihan as persentaseperalihantolakan,  nobukti as nobuktitrans,jobtrucking as jobtruckingtrans"))
            ->where("id", $id)
            ->first();

        return $query;
    }

    public function processTolakan(array $data)
    {

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();

        $getOmset = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->select('id', 'omset')
            ->where('nobukti', $data['nobukti'])
            ->first();

        $suratPengantar = SuratPengantar::lockForUpdate()->findOrFail($getOmset->id);
        if ($data['statustolakan'] == $statusApproval->id) {

            $totalomset = $getOmset->omset - $data['nominalperalihan'];

            $suratPengantar->statustolakan = $data['statustolakan'];
            $suratPengantar->totalomset = $totalomset;
            $suratPengantar->nominalperalihan = $data['nominalperalihan'];
            $suratPengantar->persentaseperalihan = $data['persentaseperalihan'];
            $aksi = $statusApproval->text;
        } else {

            $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();
            $suratPengantar->statustolakan = $data['statustolakan'];
            $suratPengantar->totalomset = $getOmset->omset;
            $suratPengantar->nominalperalihan = 0;
            $suratPengantar->persentaseperalihan = 0;
            $aksi = $statusNonApproval->text;
        }

        if ($suratPengantar->save()) {
            (new LogTrail())->processStore([
                'namatabel' => strtoupper($suratPengantar->getTable()),
                'postingdari' =>  "APPROVAL/UN TOLAKAN",
                'idtrans' => $suratPengantar->id,
                'nobuktitrans' => $suratPengantar->nobukti,
                'aksi' => $aksi,
                'datajson' => $suratPengantar->toArray(),
                'modifiedby' => auth('api')->user()->user
            ]);
        }

        return $suratPengantar;
    }

    public function approvalBiayaExtra(array $data)
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

            $id = $querysuratpengantar->id ?? 0;
            $suratPengantar = SuratPengantar::lockForUpdate()->findOrFail($id);

            if ($suratPengantar->statusapprovalbiayaextra == $statusApproval->id) {
                $suratPengantar->statusapprovalbiayaextra = $statusNonApproval->id;
                $suratPengantar->tglapprovalbiayaextra = null;
                $suratPengantar->tglbatasapprovalbiayaextra = null;
                $suratPengantar->userapprovalbiayaextra = '';
                $aksi = $statusNonApproval->text;
            } else {
                $suratPengantar->statusapprovalbiayaextra = $statusApproval->id;
                $suratPengantar->tglapprovalbiayaextra = date('Y-m-d');
                $suratPengantar->tglbatasapprovalbiayaextra = $tglbatas;
                $suratPengantar->userapprovalbiayaextra = auth('api')->user()->name;
                $aksi = $statusApproval->text;
            }
            if ($suratPengantar->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($suratPengantar->getTable()),
                    'postingdari' =>  "$aksi BIAYA EXTRA SURAT PENGANTAR",
                    'idtrans' => $suratPengantar->id,
                    'nobuktitrans' => $suratPengantar->nobukti,
                    'aksi' => $aksi,
                    'datajson' => $suratPengantar->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }

        return $data;
    }

    public function getEditSp($id)
    {
        $dari = date('Y-m-d', strtotime(request()->tgldari));
        $sampai = date('Y-m-d', strtotime(request()->tglsampai));
        $temptambahan = '##temptambahan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temptambahan, function ($table) {
            $table->string('nobukti')->nullable();
            $table->double('extra', 15, 2)->nullable();
        });

        $queryTambahan = DB::table("suratpengantar")->from(db::raw("suratpengantar as sp with (readuncommitted)"))
            ->select(DB::raw("sp.nobukti, sum(st.nominal) as extra"))
            ->join(DB::raw("suratpengantarbiayatambahan as st with (readuncommitted)"), 'sp.id', 'st.suratpengantar_id')
            ->where('sp.supir_id', $id)
            ->whereBetween('sp.tglbukti', [$dari, $sampai])
            ->groupBy('sp.nobukti');
        DB::table($temptambahan)->insertUsing(['nobukti', 'extra'], $queryTambahan);

        $tempric = '##tempric' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($tempric, function ($table) {
            $table->string('suratpengantar_nobukti')->nullable();
        });
        $queryRic = DB::table("gajisupirdetail")->from(DB::raw("gajisupirdetail as gd with (readuncommitted)"))
            ->select('gd.suratpengantar_nobukti')
            ->join(DB::raw("suratpengantar as sp with (readuncommitted)"), 'gd.suratpengantar_nobukti', 'sp.nobukti')
            ->where('sp.supir_id', $id)
            ->whereBetween('sp.tglbukti', [$dari, $sampai]);
        DB::table($tempric)->insertUsing(['suratpengantar_nobukti'], $queryRic);

        $statusPelabuhan = (new Parameter())->cekId('STATUS PELABUHAN', 'STATUS PELABUHAN', 'PELABUHAN');
        $statusLangsir = (new Parameter())->cekId('STATUS LANGSIR', 'STATUS LANGSIR', 'LANGSIR');
        $statusLongtrip = (new Parameter())->cekId('STATUS LONGTRIP', 'STATUS LONGTRIP', 'LONGTRIP');

        $query = DB::table("suratpengantar")->from(db::raw("suratpengantar as sp with (readuncommitted)"))
            ->select(
                db::raw("sp.id, sp.nobukti as nobuktiedit,isnull(sp.jobtrucking,'') as jobtruckingedit,format(sp.tglbukti,'dd') as tglbuktiedit, sp.nosp as nospedit, sp.nocont as nocontedit, sp.nocont2 as nocont2edit, gandengan.kodegandengan as gandenganedit,pelanggan.namapelanggan as pelangganedit, container.kodecontainer as containeredit, statuscontainer.keterangan as statuscontaineredit,jenisorder.keterangan as jenisorderedit, dari.kodekota as dariedit, sampai.kodekota as sampaiedit, sp.penyesuaian as penyesuaianedit, (dari.kodekota + '-' + sampai.kodekota + (case when isnull(sp.penyesuaian,'')!='' then ' ('+sp.penyesuaian+')' else '' end)) as tujuanedit, sp.gajisupir as boronganedit, isnull(tambahan.extra,0) as extraedit,agen.kodeagen as agenedit,(case when dari.statuspelabuhan = $statusPelabuhan then 1 else 0 end) as ispelabuhan,(case when orderantrucking.statuslangsir = $statusLangsir then 1 else 0 end) as islangsir,(case when sp.statuslongtrip = $statusLongtrip then 1 else 0 end) as islongtrip")
            )
            ->leftJoin(DB::raw("container with (readuncommitted)"), 'sp.container_id', 'container.id')
            ->leftJoin(DB::raw("gandengan with (readuncommitted)"), 'sp.gandengan_id', 'gandengan.id')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'sp.pelanggan_id', 'pelanggan.id')
            ->leftJoin(DB::raw("statuscontainer with (readuncommitted)"), 'sp.statuscontainer_id', 'statuscontainer.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'sp.jenisorder_id', 'jenisorder.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'sp.agen_id', 'agen.id')
            ->leftJoin(DB::raw("orderantrucking with (readuncommitted)"), 'sp.jobtrucking', 'orderantrucking.nobukti')
            ->leftJoin(DB::raw("kota as dari with (readuncommitted)"), 'sp.dari_id', 'dari.id')
            ->leftJoin(DB::raw("kota as sampai with (readuncommitted)"), 'sp.sampai_id', 'sampai.id')
            ->leftJoin(DB::raw("$temptambahan as tambahan with (readuncommitted)"), 'sp.nobukti', 'tambahan.nobukti')
            ->leftJoin(DB::raw("$tempric as ric with (readuncommitted)"), 'sp.nobukti', 'ric.suratpengantar_nobukti')
            ->where('sp.supir_id', $id)
            ->whereBetween('sp.tglbukti', [$dari, $sampai])
            ->whereRaw("isnull(ric.suratpengantar_nobukti,'')=''")
            ->orderBy('sp.tglbukti')
            ->orderBy('sp.nobukti')
            ->get();

        return $query;
    }

    public function editSp(array $data)
    {
        $statusLangsir = (new Parameter())->cekId('STATUS LANGSIR', 'STATUS LANGSIR', 'LANGSIR');
        $statusLongtrip = (new Parameter())->cekId('STATUS LONGTRIP', 'STATUS LONGTRIP', 'LONGTRIP');
        for ($i = 0; $i < count($data['id']); $i++) {
            $id = $data['id'][$i] ?? 0;
            $suratPengantar = SuratPengantar::lockForUpdate()->findOrFail($id);
            if ($suratPengantar->dari_id == 1 || $suratPengantar->statuslongtrip == $statusLongtrip) {
                $suratPengantar->nocont = $data['nocont'][$i];
                $suratPengantar->nocont2 = $data['nocont2'][$i];
                $usermodif = auth('api')->user()->name;
                if ($suratPengantar->jobtrucking != '') {
                    DB::update(DB::raw("UPDATE SURATPENGANTAR SET nocont='$suratPengantar->nocont',nocont2='$suratPengantar->nocont2',modifiedby='$usermodif' where jobtrucking='$suratPengantar->jobtrucking'"));

                    DB::update(DB::raw("UPDATE orderantrucking SET nocont='$suratPengantar->nocont',nocont2='$suratPengantar->nocont2',modifiedby='$usermodif' where nobukti='$suratPengantar->jobtrucking'"));
                }
            }
            $getJob = DB::table("orderantrucking")->from(db::raw("orderantrucking with (readuncommitted)"))->where('nobukti', $suratPengantar->jobtrucking)->where('statuslangsir', $statusLangsir)->first();
            if ($getJob != '') {
                $suratPengantar->nocont = $data['nocont'][$i];
                $suratPengantar->nocont2 = $data['nocont2'][$i];
                $usermodif = auth('api')->user()->name;
                if ($suratPengantar->jobtrucking != '') {
                    DB::update(DB::raw("UPDATE SURATPENGANTAR SET nocont='$suratPengantar->nocont',nocont2='$suratPengantar->nocont2',modifiedby='$usermodif' where jobtrucking='$suratPengantar->jobtrucking'"));

                    DB::update(DB::raw("UPDATE orderantrucking SET nocont='$suratPengantar->nocont',nocont2='$suratPengantar->nocont2',modifiedby='$usermodif' where nobukti='$suratPengantar->jobtrucking'"));
                }
            }
            if ($suratPengantar->jobtrucking == '') {
                $suratPengantar->nocont = $data['nocont'][$i];
                $suratPengantar->nocont2 = $data['nocont2'][$i];
            }
            $suratPengantar->nosp = $data['nosp'][$i];
            $suratPengantar->modifiedby = auth('api')->user()->name;

            if ($suratPengantar->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($suratPengantar->getTable()),
                    'postingdari' =>  "EDIT SP SURAT PENGANTAR",
                    'idtrans' => $suratPengantar->id,
                    'nobuktitrans' => $suratPengantar->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $suratPengantar->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }

        return $data;
    }

    public function editTripAsal(array $data)
    {
        $suratPengantar = SuratPengantar::findOrFail($data['id']);
        $suratPengantar->nobukti_tripasal = $data['nobukti_tripasal'];
        if (!$suratPengantar->save()) {
            throw new \Exception('Error edit trip asal.');
        }

        $suratPengantarLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($suratPengantar->getTable()),
            'postingdari' => 'EDIT TRIP ASAL',
            'idtrans' => $suratPengantar->id,
            'nobuktitrans' => $suratPengantar->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $suratPengantar->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        return $suratPengantar;
    }

    public function updateStatusContainerLongtrip(array $data, $aksi)
    {
        $getDataTripAsal = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->where('nobukti', $data['nobukti_tripasal'])
            ->first();
        $suratPengantar = SuratPengantar::findOrFail($getDataTripAsal->id);
        if ($aksi == 'ADD') {

            $getJarak = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))->where('id', $getDataTripAsal->upah_id)->first()->jarak ?? 0;

            if ($getDataTripAsal->jenisorder_id == 1 || $getDataTripAsal->jenisorder_id == 4) {
                $suratPengantar->statuscontainer_id = 2;
                $getUpah = DB::table("upahsupirrincian")->from(db::raw("upahsupirrincian with (readuncommitted)"))
                    ->where('upahsupir_id', $getDataTripAsal->upah_id)
                    ->where('container_id', $getDataTripAsal->container_id)
                    ->where('statuscontainer_id', 2)
                    ->first();

                if ($getUpah != '') {
                    $suratPengantar->gajisupir = $getUpah->nominalsupir;
                    $suratPengantar->gajikenek = $getUpah->nominalkenek;
                    $suratPengantar->komisisupir = $getUpah->nominalkomisi;
                    $suratPengantar->jarak = $getJarak;
                }
            } else {
                $suratPengantar->statuscontainer_id = 1;
                $getUpah = DB::table("upahsupirrincian")->from(db::raw("upahsupirrincian with (readuncommitted)"))
                    ->where('upahsupir_id', $getDataTripAsal->upah_id)
                    ->where('container_id', $getDataTripAsal->container_id)
                    ->where('statuscontainer_id', 1)
                    ->first();
                if ($getUpah != '') {
                    $suratPengantar->gajisupir = $getUpah->nominalsupir;
                    $suratPengantar->gajikenek = $getUpah->nominalkenek;
                    $suratPengantar->komisisupir = $getUpah->nominalkomisi;
                    $suratPengantar->jarak = $getJarak;
                }
            }
        }
        if ($aksi == 'DELETE') {
            $suratPengantar->statuscontainer_id = 3;
            $getUpah = DB::table("upahsupirrincian")->from(db::raw("upahsupirrincian with (readuncommitted)"))
                ->where('upahsupir_id', $getDataTripAsal->upah_id)
                ->where('container_id', $getDataTripAsal->container_id)
                ->where('statuscontainer_id', 3)
                ->first();
            $getJarak = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))->where('id', $getDataTripAsal->upah_id)->first()->jarakfullempty ?? 0;
            if ($getUpah != '') {
                $suratPengantar->gajisupir = $getUpah->nominalsupir;
                $suratPengantar->gajikenek = $getUpah->nominalkenek;
                $suratPengantar->komisisupir = $getUpah->nominalkomisi;
                $suratPengantar->jarak = $getJarak;
            }
        }
        if (!$suratPengantar->save()) {
            throw new \Exception('Error UPDATE STATUS CONTAINER LONGTRIP.');
        }

        $suratPengantarLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($suratPengantar->getTable()),
            'postingdari' => 'UPDATE STATUS CONTAINER LONGTRIP',
            'idtrans' => $suratPengantar->id,
            'nobuktitrans' => $suratPengantar->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $suratPengantar->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        return $suratPengantar;
    }
}
