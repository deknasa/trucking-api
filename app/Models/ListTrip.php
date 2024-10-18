<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ListTrip extends MyModel
{
    use HasFactory;

    public function cekValidasi($id)
    {

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $cekTanpaBatas = (new Parameter())->cekText('TANPA BATAS TRIP', 'TANPA BATAS TRIP');
        $cabang = (new Parameter())->cekText('CABANG', 'CABANG');

        $jobmanual = (new Parameter())->cekText('JOB TRUCKING MANUAL', 'JOB TRUCKING MANUAL') ?? 'TIDAK';
        $aksi = request()->aksi;
        $trip = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->select('nobukti', 'jobtrucking', 'tglbukti', DB::raw("isnull(approvalbukatanggal_id,0) as approvalbukatanggal_id"), 'tglbataseditsuratpengantar', 'statusapprovaleditsuratpengantar', 'statusjeniskendaraan', 'supir_id', 'trado_id')
            ->where('id', $id)
            ->first();
        $jenisTangki = DB::table('parameter')->from(DB::raw("parameter as a with (readuncommitted)"))
            ->select('a.id')
            ->where('a.grp', '=', 'STATUS JENIS KENDARAAN')
            ->where('a.subgrp', '=', 'STATUS JENIS KENDARAAN')
            ->where('a.text', '=', 'TANGKI')
            ->first();
        if ($trip->statusjeniskendaraan == $jenisTangki->id && $aksi == 'DELETE') {
            $getTripTangki = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
                ->select(db::raw("STRING_AGG(cast(nobukti as nvarchar(max)), ', ') as nobukti"))
                ->where('supir_id', $trip->supir_id)
                ->where('trado_id', $trip->trado_id)
                ->where('tglbukti', date('Y-m-d', strtotime($trip->tglbukti)))
                ->where('statusjeniskendaraan', $jenisTangki->id)
                ->where('id', '>', $id)
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
        $getBatasInput = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JAMBATASINPUTTRIP')->where('subgrp', 'JAMBATASINPUTTRIP')->first()->text;
        $getBatasHari = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'BATASHARIINPUTTRIP')->where('subgrp', 'BATASHARIINPUTTRIP')->first()->text;

        $tglbatasdelete = date('Y-m-d', strtotime($trip->tglbukti . "+$getBatasHari days")) . ' ' . $getBatasInput;


        if ($aksi == 'DELETE' && date('Y-m-d H:i:s') > $tglbatasdelete) {
            $keteranganerror = $error->cekKeteranganError('TBH') ?? '';
            $keteranganerrortambahan = $error->cekKeteranganError('SHP') ?? '';
            $data = [
                'kondisi' => true,
                'keterangan' => 'trip <b>' . $trip->nobukti . '</b><br>' . $keteranganerror . '<br>' . $keteranganerrortambahan,
                'kodeerror' => 'SHP',
            ];

            goto selesai;
        }
        $nobukti = $trip->nobukti;
        $jobtrucking = $trip->jobtrucking;

        $parameter = new Parameter();
        $idkandang = $parameter->cekText('KANDANG', 'KANDANG') ?? 0;

        $cekSP = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->select('dari_id', 'jobtrucking', 'statuslongtrip', 'nobukti_tripasal')->where('nobukti', $nobukti)->first();
        if ($jobmanual == 'TIDAK') {
            if ($cekSP->dari_id == 1 || ($cekSP->dari_id == $idkandang && $cekSP->nobukti_tripasal != '')) {
                $cekJob = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->where('jobtrucking', $cekSP->jobtrucking)->where('nobukti', '<>', $nobukti)->first();
                if ($cekJob != '') {

                    $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
                    $data = [
                        'kondisi' => true,
                        'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti trip <b>' . $cekJob->nobukti . '</b> <br> ' . $keterangantambahanerror,
                        'kodeerror' => 'SATL2',
                    ];


                    goto selesai;
                }
            }
        }
        if ($cekSP->statuslongtrip == 65) {
            $cekJob = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->where('jobtrucking', $cekSP->jobtrucking)->where('nobukti', '<>', $nobukti)->where('jobtrucking', '<>', '')->first();
            if ($cekJob != '') {
                $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
                $data = [
                    'kondisi' => true,
                    'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti trip <b>' . $cekJob->nobukti . '</b> <br> ' . $keterangantambahanerror,
                    'kodeerror' => 'SATL2',
                ];


                goto selesai;
            }
        }
        $cekJob = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->where('nobukti_tripasal', $nobukti)->where('dari_id', '!=', $idkandang)->first();
        if ($cekJob != '') {

            $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti trip <b>' . $cekJob->nobukti . '</b> <br> ' . $keterangantambahanerror,
                'kodeerror' => 'SATL2',
            ];


            goto selesai;
        }
        if ($cabang == 'MEDAN') {
            $statusCetak = (new Parameter())->cekId('STATUSCETAK', 'STATUSCETAK', 'CETAK');

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
                $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
                $data = [
                    'kondisi' => true,
                    'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti gaji supir <b>' . $gajiSupir->nobukti . '</b> <br> ' . $keterangantambahanerror,
                    'kodeerror' => 'SATL2',
                ];


                goto selesai;
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
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti invoice <b>' . $query->nobukti . '</b> <br> ' . $keterangantambahanerror,
                'kodeerror' => 'SATL2',
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
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti pendapatan supir <b>' . $query->nobukti . '</b> <br> ' . $keterangantambahanerror,
                'kodeerror' => 'SATL2',
            ];
            goto selesai;
        }

        $cekSP = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->select('dari_id', 'jobtrucking')->where('nobukti', $nobukti)->first();
        if ($cekSP->dari_id == 1 && $jobmanual == 'TIDAK') {
            $cekJob = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->where('jobtrucking', $cekSP->jobtrucking)->where('nobukti', '<>', $nobukti)->first();
            if ($cekJob != '') {
                $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
                $data = [
                    'kondisi' => true,
                    'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti trip <b>' . $cekJob->nobukti . '</b> <br> ' . $keterangantambahanerror,
                    'kodeerror' => 'SATL2',
                ];


                goto selesai;
            }
        }


        if ($cekTanpaBatas == 'TIDAK') {
            if ($trip->approvalbukatanggal_id > 0) {
                $getTglBatasApproval = DB::table("suratpengantarapprovalinputtrip")->from(DB::raw("suratpengantarapprovalinputtrip with (readuncommitted)"))
                    ->where('id', $trip->approvalbukatanggal_id)
                    ->first();

                if (date('Y-m-d H:i:s') > date('Y-m-d H:i:s', strtotime($getTglBatasApproval->tglbatas))) {
                    if (date('Y-m-d H:i:s') > date('Y-m-d H:i:s', strtotime($trip->tglbataseditsuratpengantar))) {
                        $keteranganerror = $error->cekKeteranganError('LB') ?? '';
                        $data = [
                            'kondisi' => true,
                            'keterangan' => $keteranganerror . "<br> BATAS $aksi " . date('d-m-Y H:i:s', strtotime($trip->tglbataseditsuratpengantar)) . ' <br> ' . $keterangantambahanerror,
                            'kodeerror' => 'LB',
                        ];

                        goto selesai;
                    }
                }
            } else {
                $tanggal = date('Y-m-d', strtotime($trip->tglbukti));

                $batasHari = $getBatasHari;
                $kondisi = true;
                if ($getBatasHari != 0) {

                    while ($kondisi) {
                        $cekHarilibur = DB::table("harilibur")->from(DB::raw("harilibur with (readuncommitted)"))
                            ->where('tgl', $tanggal)
                            ->first();
                        $todayIsSunday = date('l', strtotime($tanggal));
                        $tomorrowIsSunday = date('l', strtotime($tanggal . "+1 days"));
                        if ($cekHarilibur == '') {
                            $kondisi = false;
                            $allowed = true;
                            if (strtolower($todayIsSunday) == 'sunday') {
                                $kondisi = true;
                                $batasHari += 1;
                            }
                            if (strtolower($tomorrowIsSunday) == 'sunday') {
                                $kondisi = true;
                                $batasHari += 1;
                            }
                            // if (strtolower($todayIsSunday) != 'sunday' && strtolower($tomorrowIsSunday) != 'sunday') {
                            //     if ($batasHari > 1) {
                            //         $batasHari -= 1;
                            //     }
                            // }
                        } else {
                            $batasHari += 1;
                        }
                        $tanggal = date('Y-m-d', strtotime($trip->tglbukti . "+$batasHari days"));
                    }
                }
                $batas = $tanggal . ' ' . $getBatasInput;
                if (date('Y-m-d H:i:s') > $batas) {
                    if ($trip->statusapprovaleditsuratpengantar == 3) {
                        if (date('Y-m-d H:i:s') > date('Y-m-d H:i:s', strtotime($trip->tglbataseditsuratpengantar))) {
                            $keteranganerror = $error->cekKeteranganError('LB') ?? '';

                            $data = [
                                'kondisi' => true,
                                'keterangan' =>  $keteranganerror . "<br> BATAS $aksi " . date('d-m-Y', strtotime($trip->tglbukti . "+$getBatasHari days")) . ' ' . $getBatasInput . ' <br> ' . $keterangantambahanerror,
                                'kodeerror' => 'LB',
                            ];


                            goto selesai;
                        }
                    } else {
                        $keteranganerror = $error->cekKeteranganError('LB') ?? '';
                        $data = [
                            'kondisi' => true,
                            'keterangan' => $keteranganerror . "<br> BATAS $aksi " . date('d-m-Y', strtotime($trip->tglbukti . "+$getBatasHari days")) . ' ' . $getBatasInput  . ' <br> ' . $keterangantambahanerror,
                            'kodeerror' => 'LB',
                        ];

                        goto selesai;
                    }
                }
            }
        }
        $tempMandor = '##tempMandor' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempMandor, function ($table) {
            $table->id();
            $table->unsignedBigInteger('mandor_id')->nullable();
        });

        $querymandor = DB::table("mandordetail")->from(DB::raw("mandordetail with (readuncommitted)"))
            ->select('mandor_id')->where('user_id', auth('api')->user()->id);
        DB::table($tempMandor)->insertUsing([
            'mandor_id',
        ],  $querymandor);

        $cektrado = DB::table($tempMandor)->from(DB::raw("$tempMandor as mandor with (readuncommitted)"))
            ->join('trado', 'trado.mandor_id', 'mandor.mandor_id')
            ->join('suratpengantar', 'suratpengantar.trado_id', 'trado.id')
            ->where('suratpengantar.nobukti', $nobukti)
            ->first();

        if ($cektrado == '') {
            $keteranganerror = $error->cekKeteranganError('TPH') ?? '';
            $data = [
                'kondisi' => true,
                'keterangan' => $keteranganerror . '<br> trip milik pengurus lain <br> ' . $keterangantambahanerror,
                'kodeerror' => 'TPH',
            ];
            goto selesai;
        }


        $data = [
            'kondisi' => false,
            'keterangan' => '',
            'kodeerror' => '',
        ];


        selesai:

        return $data;
    }

    public function findAll($id)
    {
        $getBukanUpahZona = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS UPAH ZONA')->where('text', 'NON UPAH ZONA')->first();
        $jenisTangki = DB::table('parameter')->from(DB::raw("parameter as a with (readuncommitted)"))
            ->select('a.id')
            ->where('a.grp', '=', 'STATUS JENIS KENDARAAN')
            ->where('a.subgrp', '=', 'STATUS JENIS KENDARAAN')
            ->where('a.text', '=', 'TANGKI')
            ->first();

        $get = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->select('statusupahzona', 'statusjeniskendaraan')->where('id', $id)->first();
        // $pelabuhan = DB::table('parameter')
        //     ->from(DB::raw("parameter with (readuncommitted)"))
        //     ->select('text')
        //     ->where('grp', '=', 'PELABUHAN CABANG')
        //     ->where('subgrp', '=', 'PELABUHAN CABANG')
        //     ->first();

        $parameter = new Parameter();
        $statuspelabuhan = $parameter->cekId('STATUS PELABUHAN', 'STATUS PELABUHAN', 'PELABUHAN') ?? 0;
        $pelabuhan = db::table("kota")->from(db::raw("kota a with (readuncommitted)"))
            ->select(
                db::raw("STRING_AGG(id,',') as id"),
            )
            ->where('a.statuspelabuhan', $statuspelabuhan)
            ->first()->id ?? 1;


        $idkandang = (new Parameter())->cekText('KANDANG', 'KANDANG') ?? 0;
        // $pelabuhan = $pelabuhan->text . ',' . $idkandang;
        $pelabuhan = $pelabuhan . ',' . $idkandang;

        if ($get->statusjeniskendaraan == $jenisTangki->id) {

            $data = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
                ->select(
                    'suratpengantar.id',
                    'suratpengantar.nobukti',
                    'suratpengantar.tglbukti',
                    'suratpengantar.jobtrucking',
                    'suratpengantar.statusjeniskendaraan',
                    'suratpengantar.statuskandang',
                    'suratpengantar.statuslongtrip',
                    'suratpengantar.statusupahzona',
                    DB::raw("(case when isnull(suratpengantar.statuslangsir,0)=0 then 80 else
                        suratpengantar.statuslangsir
                    end) as statuslangsir"),
                    DB::raw("(case when isnull(suratpengantar.statuspenyesuaian,'')='' then
                        (case when suratpengantar.penyesuaian='' then 663 ELSE 662 end) else
                        suratpengantar.statuspenyesuaian
                    end) as statuspenyesuaian"),
                    'suratpengantar.trado_id',
                    DB::raw("(trim(trado.kodetrado)+' - '+trim(supir.namasupir)) as trado"),
                    'suratpengantar.supir_id',
                    'supir.namasupir as supir',
                    'suratpengantar.dari_id',
                    'kotadari.kodekota as dari',
                    'suratpengantar.gandengan_id',
                    'gandengan.kodegandengan as gandengan',
                    'suratpengantar.statusgudangsama',
                    'suratpengantar.keterangan',
                    'suratpengantar.penyesuaian',
                    'suratpengantar.sampai_id',
                    'kotasampai.kodekota as sampai',
                    'suratpengantar.pelanggan_id',
                    'pelanggan.namapelanggan as pelanggan',
                    'suratpengantar.agen_id',
                    'agen.namaagen as agen',
                    'suratpengantar.tariftangki_id as tarifrincian_id',
                    'tariftangki.tujuan as tarifrincian',
                    'suratpengantar.triptangki_id',
                    'triptangki.keterangan as triptangki',
                    'suratpengantar.statusgandengan',
                    'suratpengantar.gudang',
                    'suratpengantar.upahsupirtangki_id as upah_id',
                    'suratpengantar.nobukti_tripasal',
                    DB::raw("(trim(kotadari.kodekota)+' - '+trim(kotasampai.kodekota)) as upah"),
                    DB::raw("(CASE WHEN (suratpengantar.sampai_id in ($pelabuhan)) then 1 else 0 end) as statuspelabuhan"),
                    'absensisupirdetail.id as absensidetail_id'
                    // 'kotaupah.kodekota as upah'
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
                ->leftJoin('absensisupirheader', 'suratpengantar.tglbukti', 'absensisupirheader.tglbukti')
                ->leftJoin('absensisupirdetail', 'absensisupirheader.id', 'absensisupirdetail.absensi_id')
                ->where('suratpengantar.id', $id)->first();
        } else {

            // if ($get->statusupahzona == $getBukanUpahZona->id) {

            $data = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
                ->select(
                    'suratpengantar.id',
                    'suratpengantar.nobukti',
                    'suratpengantar.tglbukti',
                    'suratpengantar.jobtrucking',
                    'suratpengantar.statuskandang',
                    'suratpengantar.statuslongtrip',
                    'suratpengantar.statusjeniskendaraan',
                    'suratpengantar.statusupahzona',
                    // 'orderantrucking.statuslangsir',
                    // DB::raw("(case when isnull(orderantrucking.statuslangsir,'')='' then saldoorderantrucking.statuslangsir else orderantrucking.statuslangsir end) as statuslangsir"),
                    DB::raw("(case when isnull(suratpengantar.statuslangsir,0)=0 then 80 else
                    suratpengantar.statuslangsir end) as statuslangsir"),
                    DB::raw("(case when isnull(suratpengantar.statuspenyesuaian,'')='' then
                            (case when suratpengantar.penyesuaian='' then 663 ELSE 662 end) else
                            suratpengantar.statuspenyesuaian
                        end) as statuspenyesuaian"),
                    'suratpengantar.trado_id',
                    DB::raw("(trim(trado.kodetrado)+' - '+trim(supir.namasupir)) as trado"),
                    'suratpengantar.supir_id',
                    'supir.namasupir as supir',
                    'suratpengantar.dari_id',
                    'kotadari.kodekota as dari',
                    'suratpengantar.gandengan_id',
                    'gandengan.kodegandengan as gandengan',
                    'suratpengantar.container_id',
                    'container.kodecontainer as container',
                    'suratpengantar.statusgudangsama',
                    'suratpengantar.keterangan',
                    'suratpengantar.penyesuaian',
                    'suratpengantar.sampai_id',
                    'kotasampai.kodekota as sampai',
                    'suratpengantar.statuscontainer_id',
                    'statuscontainer.kodestatuscontainer as statuscontainer',
                    'suratpengantar.pelanggan_id',
                    'pelanggan.namapelanggan as pelanggan',
                    'suratpengantar.agen_id',
                    'agen.namaagen as agen',
                    'suratpengantar.jenisorder_id',
                    'jenisorder.keterangan as jenisorder',
                    'suratpengantar.tarif_id as tarifrincian_id',
                    'tarif.tujuan as tarifrincian',
                    'suratpengantar.gudang',
                    'suratpengantar.upah_id',
                    'suratpengantar.statusgandengan',
                    'suratpengantar.nobukti_tripasal',
                    DB::raw("(trim(kotadari.kodekota)+' - '+trim(kotasampai.kodekota)) as upah"),
                    DB::raw("(CASE WHEN (suratpengantar.sampai_id in ($pelabuhan)) then 1 else 0 end) as statuspelabuhan"),
                    'trado.statusgerobak',
                    'absensisupirdetail.id as absensidetail_id'
                    // 'kotaupah.kodekota as upah'
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
                ->leftJoin('pelanggan', 'suratpengantar.pelanggan_id', 'pelanggan.id')
                ->leftJoin('gandengan', 'suratpengantar.gandengan_id', 'gandengan.id')
                ->leftJoin('orderantrucking', 'suratpengantar.jobtrucking', 'orderantrucking.nobukti')
                ->leftJoin('saldoorderantrucking', 'suratpengantar.jobtrucking', 'saldoorderantrucking.nobukti')
                ->leftJoin('absensisupirheader', 'suratpengantar.tglbukti', 'absensisupirheader.tglbukti')
                ->leftJoin('absensisupirdetail', 'absensisupirheader.id', 'absensisupirdetail.absensi_id')
                ->where('suratpengantar.id', $id)->first();
            // } else {

            //     $data = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
            //         ->select(
            //             'suratpengantar.id',
            //             'suratpengantar.nobukti',
            //             'suratpengantar.tglbukti',
            //             'suratpengantar.jobtrucking',
            //             'suratpengantar.statuskandang',
            //             'suratpengantar.statusjeniskendaraan',
            //             'suratpengantar.statuslongtrip',
            //             'suratpengantar.statusupahzona',
            //             'orderantrucking.statuslangsir',
            //             DB::raw("(case when isnull(suratpengantar.statuspenyesuaian,'')='' then
            //                 (case when suratpengantar.penyesuaian='' then 663 ELSE 662 end) else
            //                 suratpengantar.statuspenyesuaian
            //             end) as statuspenyesuaian"),
            //             'suratpengantar.trado_id',
            //             'trado.kodetrado as trado',
            //             'suratpengantar.supir_id',
            //             'supir.namasupir as supir',
            //             'suratpengantar.dari_id',
            //             'kotadari.kodekota as dari',
            //             'suratpengantar.gandengan_id',
            //             'gandengan.kodegandengan as gandengan',
            //             'suratpengantar.container_id',
            //             'container.kodecontainer as container',
            //             'suratpengantar.statusgudangsama',
            //             'suratpengantar.keterangan',
            //             'suratpengantar.penyesuaian',
            //             'suratpengantar.sampai_id',
            //             'kotasampai.kodekota as sampai',
            //             'suratpengantar.statuscontainer_id',
            //             'statuscontainer.kodestatuscontainer as statuscontainer',
            //             'suratpengantar.pelanggan_id',
            //             'pelanggan.namapelanggan as pelanggan',
            //             'suratpengantar.agen_id',
            //             'agen.namaagen as agen',
            //             'suratpengantar.jenisorder_id',
            //             'jenisorder.keterangan as jenisorder',
            //             'suratpengantar.tarif_id as tarifrincian_id',
            //             'tarif.tujuan as tarifrincian',
            //             'suratpengantar.gudang',
            //             'suratpengantar.upah_id',
            //             'suratpengantar.nobukti_tripasal',
            //             'suratpengantar.statusgandengan',
            //             'zonaupah.zona as upah',
            //             'absensisupirdetail.id as absensidetail_id'
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
            //         ->leftJoin('orderantrucking', 'suratpengantar.jobtrucking', 'orderantrucking.nobukti')
            //         ->leftJoin('absensisupirheader', 'suratpengantar.tglbukti', 'absensisupirheader.tglbukti')
            //         ->leftJoin('absensisupirdetail', 'absensisupirheader.id', 'absensisupirdetail.absensi_id')
            //         // ->leftJoin(DB::raw("$tempGaji with (readuncommitted)"), "$tempGaji.id", "suratpengantar.id")

            //         ->where('suratpengantar.id', $id)->first();
            // }
        }
        // dd('find');
        return $data;
    }

    public function findRitasi($nobukti)
    {

        $query = DB::table("ritasi")->from(DB::raw("ritasi with (readuncommitted)"))
            ->select(DB::raw("ritasi.id, parameter.text as jenisritasi, ritasi.dataritasi_id as jenisritasi_id, ritasi.dari_id as ritasidari_id, dari.kodekota as ritasidari, ritasi.sampai_id as ritasike_id, sampai.kodekota as ritasike, isnull(ritasi.statusapprovalmandor, 4) as statusapprovalmandor"))
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'ritasi.statusritasi', 'parameter.id')
            ->leftJoin(DB::raw("kota as dari with (readuncommitted)"), 'ritasi.dari_id', 'dari.id')
            ->leftJoin(DB::raw("kota as sampai with (readuncommitted)"), 'ritasi.sampai_id', 'sampai.id')
            ->where('ritasi.suratpengantar_nobukti', $nobukti)
            ->get();
        return $query;
    }

    public function processUpdate($id, array $data)
    {
        $trip = SuratPengantar::findOrFail($id);
        $isDifferent = false;
        $isTripPulang = false;
        $cabang = (new Parameter())->cekText('CABANG', 'CABANG');
        $jenisTangki = DB::table('parameter')->from(DB::raw("parameter as a with (readuncommitted)"))
            ->select('a.id')
            ->where('a.grp', '=', 'STATUS JENIS KENDARAAN')
            ->where('a.subgrp', '=', 'STATUS JENIS KENDARAAN')
            ->where('a.text', '=', 'TANGKI')
            ->first();

        $idkandang = (new Parameter())->cekText('KANDANG', 'KANDANG') ?? 0;
        $data['upahtangki_id'] = 0;
        $data['tariftangki_id'] = 0;
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

        $pelabuhan = (new Parameter())->cekId('STATUS PELABUHAN', 'STATUS PELABUHAN', 'PELABUHAN');
        $cekkota = db::table('kota')->from(db::raw("kota with (readuncommitted)"))->where('id', $trip->dari_id)->first()->statuspelabuhan;
        if ($data['statusjeniskendaraan'] == $jenisTangki->id) {
            // $data['upahtangki_id'] = $data['upah_id'];
            // $data['upah_id'] = '';
            // $data['tariftangki_id'] = $data['tarifrincian_id'];
            // $data['tarifrincian_id'] = '';
            $getJobtrucking = OrderanTrucking::from(DB::raw("orderantrucking with (readuncommitted)"))->where('nobukti', $trip->jobtrucking)->first();
            $orderan = [
                'container_id' => $data['container_id'],
                'agen_id' => $data['agen_id'],
                'jenisorder_id' => $data['jenisorder_id'],
                'jenisorderemkl_id' => $getJobtrucking['jenisorderemkl_id'],
                'pelanggan_id' => $data['pelanggan_id'],
                'nojobemkl' => $getJobtrucking['nojobemkl'],
                'nocont' => $getJobtrucking['nocont'] ?? '',
                'noseal' => $getJobtrucking['noseal'] ?? '',
                'nojobemkl2' => $getJobtrucking['nojobemkl2'] ?? '',
                'nocont2' => $getJobtrucking['nocont2'] ?? '',
                'noseal2' => $getJobtrucking['noseal2'] ?? '',
                'statuslangsir' => $data['statuslangsir'] ?? $statuslangsir->id,
                'gandengan_id' => $data['gandengan_id'],
                'statusperalihan' => $statusperalihan->id,
                'inputtripmandor' =>  'true',
            ];

            $orderanTrucking = (new OrderanTrucking())->processUpdate($getJobtrucking, $orderan);
            goto trip;
        }
        if ($data['statuslangsir'] == $statuslangsir->id) {
            $getJobtrucking = OrderanTrucking::from(DB::raw("orderantrucking with (readuncommitted)"))->where('nobukti', $trip->jobtrucking)->first();
            if ($getJobtrucking != '') {
                if ($data['statuslangsir'] != $getJobtrucking->statuslangsir) {
                    $tglBatasEdit = date('Y-m-d', strtotime($data['tglbukti'])) . ' ' . '12:00:00';
                    $orderan = [
                        'tglbukti' => $data['tglbukti'],
                        'container_id' => $data['container_id'],
                        'agen_id' => $data['agen_id'],
                        'jenisorder_id' => $data['jenisorder_id'],
                        'pelanggan_id' => $data['pelanggan_id'],
                        'tarifrincian_id' => $data['tarifrincian_id'],
                        'statusjeniskendaraan' => $data['statusjeniskendaraan'],
                        'nojobemkl' => $data['nojobemkl'] ?? '',
                        'nocont' => $data['nocont'] ?? '',
                        'noseal' => $data['noseal'] ?? '',
                        'nojobemkl2' => $data['nojobemkl2'] ?? '',
                        'nocont2' => $data['nocont2'] ?? '',
                        'noseal2' => $data['noseal2'] ?? '',
                        'statuslangsir' => $data['statuslangsir'] ?? $statuslangsir->id,
                        'gandengan_id' => $data['gandengan_id'],
                        'statusperalihan' => $statusperalihan->id,
                        'tglbataseditorderantrucking' => $tglBatasEdit,
                        'inputtripmandor' =>  '1',
                    ];
                    $orderanTrucking = (new OrderanTrucking())->processStore($orderan);
                    $trip->jobtrucking = $orderanTrucking->nobukti;
                } else {

                    $orderan = [
                        'container_id' => $data['container_id'],
                        'agen_id' => $data['agen_id'],
                        'jenisorder_id' => $data['jenisorder_id'],
                        'jenisorderemkl_id' => $getJobtrucking['jenisorderemkl_id'],
                        'pelanggan_id' => $data['pelanggan_id'],
                        'nojobemkl' => $getJobtrucking['nojobemkl'],
                        'nocont' => $getJobtrucking['nocont'] ?? '',
                        'noseal' => $getJobtrucking['noseal'] ?? '',
                        'nojobemkl2' => $getJobtrucking['nojobemkl2'] ?? '',
                        'nocont2' => $getJobtrucking['nocont2'] ?? '',
                        'noseal2' => $getJobtrucking['noseal2'] ?? '',
                        'statuslangsir' => $data['statuslangsir'] ?? $statuslangsir->id,
                        'gandengan_id' => $data['gandengan_id'],
                        'statusperalihan' => $statusperalihan->id,
                        'inputtripmandor' =>  'true',
                    ];
                    $orderanTrucking = (new OrderanTrucking())->processUpdate($getJobtrucking, $orderan);
                }
            }
            goto trip;
        }
        $jobmanual = (new Parameter())->cekText('JOB TRUCKING MANUAL', 'JOB TRUCKING MANUAL') ?? 'TIDAK';
        if ($jobmanual == 'YA') {
            // CEK APAKAH TRIP ORIGINALNYA DARI PELABUHAN
            if ($trip->dari_id == 1) {
                // jika kota dari terganti menjadi bukan pelabuhan, jobtrucking dihapus dan job di trip dihilangkan
                if ($data['dari_id'] != $trip->dari_id) {
                    $getId = DB::table("orderantrucking")->from(DB::raw("orderantrucking with (readuncommitted)"))->where('nobukti', $trip->jobtrucking)->first();
                    if ($getId != '') {
                        (new OrderanTrucking())->processDestroy($getId->id);
                    }
                    DB::update(DB::raw("UPDATE SURATPENGANTAR SET jobtrucking='' where jobtrucking='$trip->jobtrucking'"));

                    $trip->jobtrucking = '';
                    // $trip->nocont = '';
                    // $trip->nocont2 = '';
                    // $trip->noseal = '';
                    // $trip->noseal2 = '';

                    goto trip;
                }
                // jika kota sampai berbeda dari sebelumnya, job di trip dihilangkan
                if ($data['sampai_id'] != $trip->sampai_id) {
                    DB::update(DB::raw("UPDATE SURATPENGANTAR SET jobtrucking='' where jobtrucking='$trip->jobtrucking' and id!=$trip->id"));

                    goto trip;
                }
            } else {
                if ($data['dari_id'] != $trip->dari_id || $data['sampai_id'] != $trip->sampai_id) {
                    if ($data['dari_id'] == 1) {
                        $tglBatasEdit = date('Y-m-d', strtotime($data['tglbukti'])) . ' ' . '12:00:00';
                        $orderan = [
                            'tglbukti' => $data['tglbukti'],
                            'container_id' => $data['container_id'],
                            'agen_id' => $data['agen_id'],
                            'jenisorder_id' => $data['jenisorder_id'],
                            'pelanggan_id' => $data['pelanggan_id'],
                            'tarifrincian_id' => $data['tarifrincian_id'],
                            'statusjeniskendaraan' => $data['statusjeniskendaraan'],
                            'nojobemkl' => $data['nojobemkl'] ?? '',
                            'nocont' => $data['nocont'] ?? '',
                            'noseal' => $data['noseal'] ?? '',
                            'nojobemkl2' => $data['nojobemkl2'] ?? '',
                            'nocont2' => $data['nocont2'] ?? '',
                            'noseal2' => $data['noseal2'] ?? '',
                            'statuslangsir' => $data['statuslangsir'] ?? $statuslangsir->id,
                            'gandengan_id' => $data['gandengan_id'],
                            'statusperalihan' => $statusperalihan->id,
                            'tglbataseditorderantrucking' => $tglBatasEdit,
                            'inputtripmandor' =>  '1',
                        ];
                        $orderanTrucking = (new OrderanTrucking())->processStore($orderan);
                        $trip->jobtrucking = $orderanTrucking->nobukti;
                        // $trip->nocont = '';
                        // $trip->nocont2 = '';
                        // $trip->noseal = '';
                        // $trip->noseal2 = '';
                    } else {
                        if ($cabang == 'MEDAN') {
                            DB::update(DB::raw("UPDATE SURATPENGANTAR SET jobtrucking='' where id=$trip->id"));
                        } else {
                            DB::update(DB::raw("UPDATE SURATPENGANTAR SET jobtrucking='',nocont='',nocont2='',noseal='',noseal2='' where id=$trip->id"));
                        }
                    }

                    goto trip;
                }
                goto trip;
            }
        }
        if ($cekkota == $pelabuhan) {
            if ($data['dari_id'] == $trip->dari_id) {
                $agen_id = $data['agen_id'];
                $pelanggan_id = $data['pelanggan_id'];
                $jenisorder_id = $data['jenisorder_id'];
                $container_id = $data['container_id'];
                $gandengan_id = $data['gandengan_id'];
                DB::update(DB::raw("UPDATE SURATPENGANTAR SET nocont='$trip->nocont',nocont2='$trip->nocont2',noseal='$trip->noseal',noseal2='$trip->noseal2', agen_id='$agen_id', pelanggan_id='$pelanggan_id',container_id='$container_id',jenisorder_id='$jenisorder_id',gandengan_id='$gandengan_id' where jobtrucking='$trip->jobtrucking'"));
            }
        }

        if ($data['jobtrucking'] == '') {

            $statuslangsir = DB::table('parameter')->from(
                DB::raw("parameter as a with (readuncommitted)")
            )
                ->select(
                    'a.id'
                )
                ->where('a.grp', '=', 'STATUS LANGSIR')
                ->where('a.subgrp', '=', 'STATUS LANGSIR')
                ->where('a.text', '=', 'BUKAN LANGSIR')
                ->first();

            $tglBatasEdit = date('Y-m-d', strtotime($data['tglbukti'])) . ' ' . '12:00:00';

            if ($data['statuslongtrip'] == 65) {

                $getId = DB::table("orderantrucking")->from(DB::raw("orderantrucking with (readuncommitted)"))->where('nobukti', $trip->jobtrucking)->first();
                if (isset($getId)) {
                    (new OrderanTrucking())->processDestroy($getId->id);
                }
            }
            // dd($data['dari_id'] != $idkandang,$data['dari_id'], $idkandang);
            // if ($data['dari_id'] != $idkandang) {

            //     $orderan = [
            //         'tglbukti' => $data['tglbukti'],
            //         'container_id' => $data['container_id'],
            //         'agen_id' => $data['agen_id'],
            //         'jenisorder_id' => $data['jenisorder_id'],
            //         'pelanggan_id' => $data['pelanggan_id'],
            //         'tarifrincian_id' => $data['tarifrincian_id'],
            //         'statusjeniskendaraan' => $data['statusjeniskendaraan'],
            //         'nojobemkl' =>  '',
            //         'nocont' =>   '',
            //         'noseal' =>  '',
            //         'nojobemkl2' => '',
            //         'nocont2' => '',
            //         'noseal2' => '',
            //         'statuslangsir' => $statuslangsir->id,
            //         'statusperalihan' => $statusperalihan->id,
            //         'gandengan_id' => $data['gandengan_id'],
            //         'tglbataseditorderantrucking' => $tglBatasEdit,
            //         'inputtripmandor' =>  '1',
            //     ];
            //     $orderanTrucking = (new OrderanTrucking())->processStore($orderan);
            //     $trip->jobtrucking = $orderanTrucking->nobukti;
            //     goto trip;
            // }
        }

        // sebelumnya bukan longtrip, lalu diganti jadi longtrip, dan trip semula adalah trip dari pelabuhan
        if ($trip->statuslongtrip != $data['statuslongtrip'] && $data['statuslongtrip'] == 65) {
            if ($cekkota == $pelabuhan) {
                $getJobtrucking = OrderanTrucking::from(DB::raw("orderantrucking with (readuncommitted)"))->where('nobukti', $trip->jobtrucking)->first();

                if ($getJobtrucking != '') {
                    $orderan = [
                        'container_id' => $data['container_id'],
                        'agen_id' => $data['agen_id'],
                        'jenisorder_id' => $data['jenisorder_id'],
                        'jenisorderemkl_id' => $getJobtrucking['jenisorderemkl_id'],
                        'pelanggan_id' => $data['pelanggan_id'],
                        'nojobemkl' => $getJobtrucking['nojobemkl'],
                        'nocont' => $getJobtrucking['nocont'] ?? '',
                        'noseal' => $getJobtrucking['noseal'] ?? '',
                        'nojobemkl2' => $getJobtrucking['nojobemkl2'] ?? '',
                        'nocont2' => $getJobtrucking['nocont2'] ?? '',
                        'noseal2' => $getJobtrucking['noseal2'] ?? '',
                        'statuslangsir' => $data['statuslangsir'] ?? $statuslangsir->id,
                        'gandengan_id' => $data['gandengan_id'],
                        'statusperalihan' => $statusperalihan->id,
                        'inputtripmandor' =>  'true',
                    ];
                    $orderanTrucking = (new OrderanTrucking())->processUpdate($getJobtrucking, $orderan);
                }
                goto trip;
            }
        }

        if ($trip->statuscontainer_id != 3) {
            // if ($trip->dari_id != 1 && $data['dari_id'] != $idkandang) {
            if ($cekkota != $pelabuhan && $data['dari_id'] != $idkandang) {
                $cek = [$trip->agen_id, $trip->jenisorder_id, $trip->statuscontainer_id, $trip->container_id, $trip->upah_id, $trip->pelanggan_id];

                $toCek = [$data['agen_id'], $data['jenisorder_id'], $data['statuscontainer_id'], $data['container_id'], $data['upah_id'], $data['pelanggan_id']];

                $differences = array_diff_assoc($cek, $toCek);
                if (!empty($differences)) {
                    if ($data['jobtrucking'] == '') {
                        if ($trip->statuslongtrip != $data['statuslongtrip']) {
                            if ($data['statuslongtrip'] != 65) {
                                $getId = DB::table("orderantrucking")->from(DB::raw("orderantrucking with (readuncommitted)"))->where('nobukti', $trip->jobtrucking)->first();
                                if ($getId != '') {
                                    (new OrderanTrucking())->processDestroy($getId->id);
                                }
                            }
                        }
                        $isDifferent = true;
                        $isTripPulang = true;
                    } else {
                        if ($trip->statusgudangsama != $data['statusgudangsama']) {
                            if ($data['statusgudangsama'] != 204) {
                                $getId = DB::table("orderantrucking")->from(DB::raw("orderantrucking with (readuncommitted)"))->where('nobukti', $trip->jobtrucking)->first();
                                if ($getId != '') {
                                    (new OrderanTrucking())->processDestroy($getId->id);
                                }
                            }
                        }
                        $trip->jobtrucking = $data['jobtrucking'];
                        $isTripPulang = true;
                    }
                } else {
                    $trip->jobtrucking = $data['jobtrucking'];
                }
            } else {
                $idkandang = (new Parameter())->cekText('KANDANG', 'KANDANG') ?? 0;

                if ($cekkota != $pelabuhan && $data['dari_id'] != $idkandang) {

                    if ($data['statusgudangsama'] != 204) {

                        $count = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->where('jobtrucking', $trip->jobtrucking)->count();
                        if ($count == 1) {
                            $getId = DB::table("orderantrucking")->from(DB::raw("orderantrucking with (readuncommitted)"))->where('nobukti', $trip->jobtrucking)->first();

                            if ($getId != '') {
                                (new OrderanTrucking())->processDestroy($getId->id);
                            }
                        }

                        $trip->jobtrucking = $data['jobtrucking'];
                        $isTripPulang = true;
                    }
                }

                if ($data['statuslongtrip'] == 65) {
                    $tglBatasEdit = date('Y-m-d', strtotime($data['tglbukti'])) . ' ' . '12:00:00';
                    $orderan = [
                        'tglbukti' => $data['tglbukti'],
                        'container_id' => $data['container_id'],
                        'agen_id' => $data['agen_id'],
                        'jenisorder_id' => $data['jenisorder_id'],
                        'pelanggan_id' => $data['pelanggan_id'],
                        'tarifrincian_id' => $data['tarifrincian_id'],
                        'statusjeniskendaraan' => $data['statusjeniskendaraan'],
                        'nojobemkl' => $data['nojobemkl'] ?? '',
                        'nocont' => $data['nocont'] ?? '',
                        'noseal' => $data['noseal'] ?? '',
                        'nojobemkl2' => $data['nojobemkl2'] ?? '',
                        'nocont2' => $data['nocont2'] ?? '',
                        'noseal2' => $data['noseal2'] ?? '',
                        'statuslangsir' => $data['statuslangsir'] ?? $statuslangsir->id,
                        'gandengan_id' => $data['gandengan_id'],
                        'statusperalihan' => $statusperalihan->id,
                        'tglbataseditorderantrucking' => $tglBatasEdit,
                        'inputtripmandor' =>  '1',
                    ];
                    $orderanTrucking = (new OrderanTrucking())->processStore($orderan);
                    $trip->jobtrucking = $orderanTrucking->nobukti;
                }
            }
        } else {

            if ($cekkota != $pelabuhan) {
                $cek = [$trip->agen_id, $trip->jenisorder_id, $trip->statuscontainer_id, $trip->container_id, $trip->upah_id, $trip->pelanggan_id];

                $toCek = [$data['agen_id'], $data['jenisorder_id'], $data['statuscontainer_id'], $data['container_id'], $data['upah_id'], $data['pelanggan_id']];

                $differences = array_diff_assoc($cek, $toCek);
                if (!empty($differences)) {
                    if ($data['jobtrucking'] == '') {
                        $isDifferent = true;
                        $isTripPulang = true;
                    } else {
                        if ($trip->statusgudangsama != $data['statusgudangsama']) {
                            if ($data['statusgudangsama'] != 204) {
                                $getId = DB::table("orderantrucking")->from(DB::raw("orderantrucking with (readuncommitted)"))->where('nobukti', $trip->jobtrucking)->first();
                                if ($getId != '') {
                                    (new OrderanTrucking())->processDestroy($getId->id);
                                }
                            }
                        }
                        $trip->jobtrucking = $data['jobtrucking'];
                        $isTripPulang = true;
                    }
                }
            } else {
                if ($data['nobukti_tripasal'] != '') {
                    $getId = DB::table("orderantrucking")->from(DB::raw("orderantrucking with (readuncommitted)"))->where('nobukti', $trip->jobtrucking)->first();
                    if ($getId != '') {
                        (new OrderanTrucking())->processDestroy($getId->id);
                    }
                    if ($data['jobtrucking'] != '') {
                        $trip->jobtrucking = $data['jobtrucking'];
                    } else {

                        $isTripPulang = true;
                        $isDifferent = true;
                    }
                } else {
                    if ($data['jobtrucking'] != '') {
                        $trip->jobtrucking = $data['jobtrucking'];
                    }
                }
            }
        }
        // dd($trip->jobtrucking,  $isTripPulang, $isDifferent);
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
            ->where('a.text', '=', 'BUKAN LANGSIR')
            ->first();
        if (!$isDifferent) {

            if (!$isTripPulang) {
                $getJobtrucking = OrderanTrucking::from(DB::raw("orderantrucking with (readuncommitted)"))->where('nobukti', $trip->jobtrucking)->first();

                if ($getJobtrucking != '') {
                    $orderan = [
                        'container_id' => $data['container_id'],
                        'agen_id' => $data['agen_id'],
                        'jenisorder_id' => $data['jenisorder_id'],
                        'jenisorderemkl_id' => $getJobtrucking['jenisorderemkl_id'],
                        'pelanggan_id' => $data['pelanggan_id'],
                        'nojobemkl' => $getJobtrucking['nojobemkl'],
                        'nocont' => $getJobtrucking['nocont'] ?? '',
                        'noseal' => $getJobtrucking['noseal'] ?? '',
                        'nojobemkl2' => $getJobtrucking['nojobemkl2'] ?? '',
                        'nocont2' => $getJobtrucking['nocont2'] ?? '',
                        'noseal2' => $getJobtrucking['noseal2'] ?? '',
                        'statuslangsir' => $data['statuslangsir'] ?? $statuslangsir->id,
                        'gandengan_id' => $data['gandengan_id'],
                        'statusperalihan' => $statusperalihan->id,
                        'inputtripmandor' =>  'true',
                    ];
                    $orderanTrucking = (new OrderanTrucking())->processUpdate($getJobtrucking, $orderan);
                }
            }
            if ($cekkota != $pelabuhan && $data['statuslongtrip'] != 65) {
                $pelabuhandari_id = db::table('kota')->from(db::raw("kota with (readuncommitted)"))->where('id', $data['dari_id'])->first()->statuspelabuhan;
                if ($pelabuhandari_id == $pelabuhan) {
                    $tglBatasEdit = date('Y-m-d', strtotime($data['tglbukti'])) . ' ' . '12:00:00';
                    $orderan = [
                        'tglbukti' => $data['tglbukti'],
                        'container_id' => $data['container_id'],
                        'agen_id' => $data['agen_id'],
                        'jenisorder_id' => $data['jenisorder_id'],
                        'pelanggan_id' => $data['pelanggan_id'],
                        'tarifrincian_id' => $data['tarifrincian_id'],
                        'statusjeniskendaraan' => $data['statusjeniskendaraan'],
                        'nojobemkl' => $data['nojobemkl'] ?? '',
                        'nocont' => $data['nocont'] ?? '',
                        'noseal' => $data['noseal'] ?? '',
                        'nojobemkl2' => $data['nojobemkl2'] ?? '',
                        'nocont2' => $data['nocont2'] ?? '',
                        'noseal2' => $data['noseal2'] ?? '',
                        'statuslangsir' => $data['statuslangsir'] ?? $statuslangsir->id,
                        'gandengan_id' => $data['gandengan_id'],
                        'statusperalihan' => $statusperalihan->id,
                        'tglbataseditorderantrucking' => $tglBatasEdit,
                        'inputtripmandor' =>  '1',
                    ];
                    $orderanTrucking = (new OrderanTrucking())->processStore($orderan);
                    $trip->jobtrucking = $orderanTrucking->nobukti;
                }
            }
        } else {
            if ($trip->statusgudangsama != $data['statusgudangsama']) {
                if (($data['jenisorder_id'] == 3 || $data['jenisorder_id'] == 2) && $data['statuscontainer_id'] == 1) {
                    $isTripPulang = false;
                }
                if (($data['jenisorder_id'] == 1 || $data['jenisorder_id'] == 4) && $data['statuscontainer_id'] == 2) {
                    $isTripPulang = false;
                }
            }
            if (!$isTripPulang) {

                $getJobtrucking = OrderanTrucking::from(DB::raw("orderantrucking with (readuncommitted)"))->where('nobukti', $trip->jobtrucking)->first();
                if ($getJobtrucking != '') {
                    $orderan = [
                        'container_id' => $data['container_id'],
                        'agen_id' => $data['agen_id'],
                        'jenisorder_id' => $data['jenisorder_id'],
                        'jenisorderemkl_id' => $getJobtrucking['jenisorderemkl_id'],
                        'pelanggan_id' => $data['pelanggan_id'],
                        'nojobemkl' => $getJobtrucking['nojobemkl'],
                        'nocont' => $getJobtrucking['nocont'] ?? '',
                        'noseal' => $getJobtrucking['noseal'] ?? '',
                        'nojobemkl2' => $getJobtrucking['nojobemkl2'] ?? '',
                        'nocont2' => $getJobtrucking['nocont2'] ?? '',
                        'noseal2' => $getJobtrucking['noseal2'] ?? '',
                        'statuslangsir' => $data['statuslangsir'] ?? $statuslangsir->id,
                        'gandengan_id' => $data['gandengan_id'],
                        'statusperalihan' => $statusperalihan->id,
                        'inputtripmandor' =>  'true',
                    ];

                    $orderanTrucking = (new OrderanTrucking())->processUpdate($getJobtrucking, $orderan);
                }
            } else {
                $tglBatasEdit = date('Y-m-d', strtotime($data['tglbukti'])) . ' ' . '12:00:00';
                $orderan = [
                    'tglbukti' => $data['tglbukti'],
                    'container_id' => $data['container_id'],
                    'agen_id' => $data['agen_id'],
                    'jenisorder_id' => $data['jenisorder_id'],
                    'pelanggan_id' => $data['pelanggan_id'],
                    'tarifrincian_id' => $data['tarifrincian_id'],
                    'statusjeniskendaraan' => $data['statusjeniskendaraan'],
                    'nojobemkl' => $data['nojobemkl'] ?? '',
                    'nocont' => $data['nocont'] ?? '',
                    'noseal' => $data['noseal'] ?? '',
                    'nojobemkl2' => $data['nojobemkl2'] ?? '',
                    'nocont2' => $data['nocont2'] ?? '',
                    'noseal2' => $data['noseal2'] ?? '',
                    'statuslangsir' => $data['statuslangsir'] ?? $statuslangsir->id,
                    'gandengan_id' => $data['gandengan_id'],
                    'statusperalihan' => $statusperalihan->id,
                    'tglbataseditorderantrucking' => $tglBatasEdit,
                    'inputtripmandor' =>  '1',
                ];
                $orderanTrucking = (new OrderanTrucking())->processStore($orderan);
                $trip->jobtrucking = $orderanTrucking->nobukti;
            }
        }

        trip:
        $upahsupirRincian = DB::table('UpahSupirRincian')->from(
            DB::Raw("UpahSupirRincian with (readuncommitted)")
        )
            ->where('upahsupir_id', $data['upah_id'])
            ->where('container_id', $data['container_id'])
            ->where('statuscontainer_id', $data['statuscontainer_id'])
            ->first();
        if ($data['statuslongtrip'] == 66) {
            $tarifrincian = TarifRincian::where('tarif_id', $data['tarifrincian_id'])->where('container_id', $data['container_id'])->first();
        }
        $getZona = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS UPAH ZONA')->where('text', 'UPAH ZONA')->first();
        $upahZona = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))->where('id', $data['upah_id'])->first();

        $data['zonadari_id'] = '';
        $data['zonasampai_id'] = '';

        $nominalspr = 0;
        $nominalkenek = 0;
        $nominalkomisi = 0;
        if ($data['statusjeniskendaraan'] != $jenisTangki->id) {
            if ($data['statusupahzona'] == $getZona->id) {
                $data['zonadari_id'] = $upahZona->zonadari_id;
                $data['zonasampai_id'] = $upahZona->zonasampai_id;
            }
            $params = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PENDAPATAN SUPIR')->where('subgrp', 'GAJI KENEK')->first();
            $komisi_gajisupir = $params->text;
            // if ($komisi_gajisupir == 'YA') {
            //     $nominalSupir = $upahsupirRincian->nominalsupir - $upahsupirRincian->nominalkenek;
            // } else {
            $nominalSupir = $upahsupirRincian->nominalsupir;
            // }
            // dd($trip->jobtrucking);
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
                ->where('b.statuscontainer_id', $data['statuscontainer_id'])
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


            if ($data['statuskandang'] == $idstatuskandang) {
                $nominalspr = $nominalSupir - $nominalsupirkandang;
                $nominalkenek = $upahsupirRincian->nominalkenek - $nominalkenekkandang;
                $nominalkomisi = $upahsupirRincian->nominalkomisi - $nominalkomisikandang;
            } else {
                if ($trip->container_id != $data['container_id'] || $trip->statuscontainer_id != $data['statuscontainer_id']) {
                    $nominalspr = $nominalSupir;
                    $nominalkenek = $upahsupirRincian->nominalkenek;
                    $nominalkomisi = $upahsupirRincian->nominalkomisi;
                } else {
                    if ($trip->upah_id != $data['upah_id']) {
                        $nominalspr = $nominalSupir;
                        $nominalkenek = $upahsupirRincian->nominalkenek;
                        $nominalkomisi = $upahsupirRincian->nominalkomisi;
                    } else {
                        $nominalspr = $nominalSupir;
                        $nominalkenek = $trip->gajikenek;
                        $nominalkomisi = $trip->komisisupir;
                    }
                }
            }
        } else {
            // if ($data['supir_id'] != $trip->supir_id && $data['trado_id'] != $trip->trado_id) {
            //     $getTripTangki = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
            //         ->select('triptangki_id')
            //         ->where('supir_id', $data['supir_id'])
            //         ->where('trado_id', $data['trado_id'])
            //         ->where('tglbukti', date('Y-m-d', strtotime($data['tglbukti'])))
            //         ->where('statusjeniskendaraan', $jenisTangki->id)
            //         ->orderBy('id', 'desc')
            //         ->count();
            //     if ($getTripTangki > 0) {
            //         $triptangki = $getTripTangki + 1;
            //     } else {
            //         $triptangki = 1;
            //     }
            //     $getTangki = DB::table("triptangki")->from(DB::raw("triptangki with (readuncommitted)"))
            //         ->where('kodetangki', $triptangki)
            //         ->first();

            //     $tangki_id = $getTangki->id;
            // } else {
            //     $tangki_id = $trip->triptangki_id;
            // }
            $upahsupir = DB::table("upahsupirtangkirincian")->where('upahsupirtangki_id', $data['upah_id'])->where('triptangki_id', $data['triptangki_id'])->first()->nominalsupir ?? 0;
            $nominalspr = $upahsupir;
        }
        $dataSP = [

            'jobtrucking' => $trip->jobtrucking,
            'tglbukti' => $data['tglbukti'],
            'pelanggan_id' => $data['pelanggan_id'],
            'upah_id' => $data['upah_id'],
            'triptangki_id' => $data['triptangki_id'],
            'dari_id' => $data['dari_id'],
            'sampai_id' => $data['sampai_id'],
            'container_id' => $data['container_id'],
            'statuscontainer_id' => $data['statuscontainer_id'],
            'penyesuaian' => $data['penyesuaian'],
            'trado_id' => $data['trado_id'],
            'supir_id' => $data['supir_id'],
            'gandengan_id' => $data['gandengan_id'],
            'gandenganasal_id' => $data['gandenganasal_id'],
            'statuslongtrip' => $data['statuslongtrip'],
            'statusgandengan' => $data['statusgandengan'],
            'statusupahzona' => $data['statusupahzona'],
            'statuslangsir' => $data['statuslangsir'] ?? $statuslangsir->id,
            'omset' => $tarifrincian->nominal ?? 0,
            'komisisupir' => $nominalkomisi,
            'gajikenek' => $nominalkenek,
            'gajisupir' => $nominalspr,
            'agen_id' => $data['agen_id'],
            'zonadari_id' => $data['zonadari_id'],
            'zonasampai_id' => $data['zonasampai_id'],
            'jenisorder_id' => $data['jenisorder_id'],
            'jenisorderemkl_id' => $data['jenisorder_id'],
            'statusperalihan' => $statusperalihan->id,
            'totalomset' => $tarifrincian->nominal ?? 0,
            'tglsp' => $data['tglbukti'],
            'statusbatalmuat' => $trip->statusbatalmuat,
            'statusjeniskendaraan' => $data['statusjeniskendaraan'],
            'statusgudangsama' => $data['statusgudangsama'],
            'statuskandang' => $data['statuskandang'],
            'gudang' => $data['gudang'],
            'lokasibongkarmuat' => $data['lokasibongkarmuat'],
            'tarif_id' => $data['tarifrincian_id'],
            'qtyton' => $trip->qtyton,
            'edittripmandor' => '1',
            'nominal' => '',
            'nobukti_tripasal' => $data['nobukti_tripasal'],
            'statuspenyesuaian' => $data['statuspenyesuaian'],
            'keterangan' => $trip->keterangan,
            'nourutorder' => $trip->nourutorder,
            'nosptagihlain' => $trip->nosptagihlain,
            'qtyton' => $trip->qtyton,
            'biayatambahan_id' => $trip->biayatambahan_id,
            'persentaseperalihan' => $trip->persentaseperalihan ?? 0,
            'nominalperalihan' => $trip->nominalperalihan ?? 0,
            'statustolakan' => $trip->statustolakan ?? 4,
            'nosp' => $trip->nosp,
            'nocont' => $trip->nocont,
            'nocont2' => $trip->nocont2,
            'noseal' => $trip->noseal,
            'noseal2' => $trip->noseal2,
        ];
        // dd($dataSP);
        $suratPengantar = (new SuratPengantar())->processUpdate($trip, $dataSP);

        $jenisRitasi = false;
        foreach ($data['jenisritasi_id'] as $value) {
            if ($value != null || $value != 0) {
                $jenisRitasi = true;
                break;
            }
        }
        if ($jenisRitasi) {
            // Ritasi::where('suratpengantar_nobukti', $suratPengantar->nobukti)->lockForUpdate()->delete();

            $requestData = json_encode($data['ritasi_id']);
            if ($requestData != 'null') {
                $queryIdRitasi = db::table('a')->from(DB::raw("OPENJSON ('$requestData')"))
                    ->select(db::raw("[value]"))
                    ->whereRaw("isnull([value],0) != 0")
                    ->groupBy('value');
                $tempritasi = '##tempritasi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempritasi, function ($table) {
                    $table->string('value')->nullable();
                });
                DB::table($tempritasi)->insertUsing(['value'], $queryIdRitasi);

                $cekDataDeleteRitasi = DB::table('ritasi as a')
                    ->select('a.id', 'b.value')
                    ->leftJoin("$tempritasi as b", 'a.id', '=', 'b.value')
                    ->whereNull('b.value')
                    ->where('a.suratpengantar_nobukti', "=", $suratPengantar->nobukti)
                    ->get();
            }
            if (count($cekDataDeleteRitasi) > 0) {
                foreach ($cekDataDeleteRitasi as $row => $value) {
                    (new Ritasi())->processDestroy($value->id);
                }
            }
            for ($i = 0; $i < count($data['jenisritasi_id']); $i++) {
                $ritasi_id = $data['ritasi_id'][$i] ?? 0;

                if ($ritasi_id == 0) {
                    $ritasiData = [
                        'tglbukti' => $suratPengantar->tglbukti,
                        'statusritasi_id' => $data['jenisritasi_id'][$i],
                        'suratpengantar_nobukti' => $suratPengantar->nobukti,
                        'supir_id' => $data['supir_id'],
                        'trado_id' => $data['trado_id'],
                        'dari_id' => $data['ritasidari_id'][$i],
                        'sampai_id' => $data['ritasike_id'][$i],
                    ];
                    (new Ritasi())->processStore($ritasiData);
                } else {
                    $ritasiData = [
                        'tglbukti' => $suratPengantar->tglbukti,
                        'statusritasi_id' => $data['jenisritasi_id'][$i],
                        'suratpengantar_nobukti' => $suratPengantar->nobukti,
                        'supir_id' => $data['supir_id'],
                        'trado_id' => $data['trado_id'],
                        'dari_id' => $data['ritasidari_id'][$i],
                        'sampai_id' => $data['ritasike_id'][$i],
                    ];
                    $newRitasi = new Ritasi();
                    $newRitasi = $newRitasi->findOrFail($ritasi_id);
                    (new Ritasi())->processUpdate($newRitasi, $ritasiData);
                }
            }
        } else {
            $cekRitasi = DB::table("ritasi")->from(db::raw("ritasi with (readuncommitted)"))
                ->where('suratpengantar_nobukti', $suratPengantar->nobukti)
                ->first();
            if ($cekRitasi != '') {
                Ritasi::where('suratpengantar_nobukti', $suratPengantar->nobukti)->lockForUpdate()->delete();
            }
        }

        return $suratPengantar;
    }

    public function processDestroy($id): SuratPengantar
    {
        // $suratPengantarBiayaTambahan = SuratPengantarBiayaTambahan::where('suratpengantar_id', $id)->get();
        $cekSP = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->select('suratpengantar.dari_id', 'orderantrucking.id', 'suratpengantar.statusgudangsama', 'suratpengantar.statuslongtrip')
            ->leftJoin(DB::raw("orderantrucking with (readuncommitted)"), 'suratpengantar.jobtrucking', 'orderantrucking.nobukti')->where('suratpengantar.id', $id)->first();

        if ($cekSP->dari_id == 1) {
            (new OrderanTrucking())->processDestroy($cekSP->id);
        }
        if ($cekSP->statusgudangsama == 204) {
            (new OrderanTrucking())->processDestroy($cekSP->id);
        }
        if ($cekSP->statuslongtrip == 65) {
            (new OrderanTrucking())->processDestroy($cekSP->id);
        }
        $suratPengantar = new SuratPengantar();
        $suratPengantar = $suratPengantar->lockAndDestroy($id);

        $suratPengantarLogTrail = (new LogTrail())->processStore([
            'namatabel' => $suratPengantar->getTable(),
            'postingdari' => 'DELETE SURAT PENGANTAR',
            'idtrans' => $suratPengantar->id,
            'nobuktitrans' => $suratPengantar->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $suratPengantar->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        // if (count($suratPengantarBiayaTambahan->toArray()) > 0) {
        //     SuratPengantarBiayaTambahan::where('suratpengantar_id', $id)->delete();
        //     $tes = (new LogTrail())->processStore([
        //         'namatabel' => 'SURATPENGANTARBIAYATAMBAHAN',
        //         'postingdari' => 'DELETE SURAT PENGANTAR BIAYA TAMBAHAN',
        //         'idtrans' => $suratPengantarLogTrail['id'],
        //         'nobuktitrans' => $suratPengantar->nobukti,
        //         'aksi' => 'DELETE',
        //         'datajson' => $suratPengantarBiayaTambahan->toArray(),
        //         'modifiedby' => auth('api')->user()->name
        //     ]);
        // }


        return $suratPengantar;
    }

    public function approval(array $data)
    {
        $statusApproval = (new Parameter())->cekId('STATUS APPROVAL', 'STATUS APPROVAL', 'APPROVAL');
        $statusNonApproval = (new Parameter())->cekId('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');
        $buktiNon = [];
        for ($i = 0; $i < count($data['nobukti']); $i++) {
            $nobukti = $data['nobukti'][$i];
            $nomor = substr($data['nobukti'][$i], 0, 3);
            // dd($nomor);
            if ($nomor == 'RTT') {
                $getRitasi = DB::table("ritasi")->from(DB::raw("ritasi with (readuncommitted)"))->where('nobukti', $nobukti)->first()->id ?? 0;
                if ($getRitasi != 0) {
                    $ritasi = Ritasi::lockForUpdate()->findOrFail($getRitasi);

                    if ($ritasi->statusapprovalmandor == $statusApproval) {
                        $ritasi->statusapprovalmandor = $statusNonApproval;
                        $buktiNon[] = $ritasi->nobukti;
                    } else {
                        $ritasi->statusapprovalmandor = $statusApproval;
                    }
                    $ritasi->userapprovalmandor = auth('api')->user()->name;
                    $ritasi->tglapprovalmandor = date('Y-m-d H:i:s');
                    if ($ritasi->save()) {
                        (new LogTrail())->processStore([
                            'namatabel' => strtoupper($ritasi->getTable()),
                            'postingdari' =>  "APPROVAL/UN MANDOR",
                            'idtrans' => $ritasi->id,
                            'nobuktitrans' => $ritasi->nobukti,
                            'aksi' => 'APPROVAL/UN MANDOR',
                            'datajson' => $ritasi->toArray(),
                            'modifiedby' => auth('api')->user()->user
                        ]);
                    }
                }
            } else {
                $getSuratpengantar = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->where('nobukti', $nobukti)->first()->id ?? 0;
                if ($getSuratpengantar != 0) {
                    $suratPengantar = SuratPengantar::lockForUpdate()->findOrFail($getSuratpengantar);

                    if ($suratPengantar->statusapprovalmandor == $statusApproval) {
                        $suratPengantar->statusapprovalmandor = $statusNonApproval;
                        $buktiNon[] = $suratPengantar->nobukti;
                    } else {
                        $suratPengantar->statusapprovalmandor = $statusApproval;
                    }
                    $suratPengantar->userapprovalmandor = auth('api')->user()->name;
                    $suratPengantar->tglapprovalmandor = date('Y-m-d H:i:s');
                    if ($suratPengantar->save()) {
                        (new LogTrail())->processStore([
                            'namatabel' => strtoupper($suratPengantar->getTable()),
                            'postingdari' =>  "APPROVAL/UN MANDOR",
                            'idtrans' => $suratPengantar->id,
                            'nobuktitrans' => $suratPengantar->nobukti,
                            'aksi' => 'APPROVAL/UN MANDOR',
                            'datajson' => $suratPengantar->toArray(),
                            'modifiedby' => auth('api')->user()->user
                        ]);
                    }
                }
            }
        }

        if (count($buktiNon) > 0) {


            $requestData = json_encode($data['nobukti']);
            $query = db::table('a')->from(DB::raw("OPENJSON ('$requestData')"))
                ->select(db::raw("[value]"))
                ->groupBy('value');

            $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temp, function ($table) {
                $table->string('value')->nullable();
            });
            DB::table($temp)->insertUsing(['value'], $query);

            $trip = DB::table("gajisupirdetail")->from(DB::raw("gajisupirdetail as a with (readuncommitted)"))
                ->select('a.nobukti')
                ->join(db::raw("$temp as b with (readuncommitted)"), 'a.suratpengantar_nobukti', 'b.value')
                ->groupBy('a.nobukti');

            $temptrip = '##temptrip' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temptrip, function ($table) {
                $table->string('nobukti')->nullable();
            });
            DB::table($temptrip)->insertUsing(['nobukti'], $trip);

            $ritasi = DB::table("gajisupirdetail")->from(DB::raw("gajisupirdetail as a with (readuncommitted)"))
                ->select('a.nobukti')
                ->join(db::raw("$temp as b with (readuncommitted)"), 'a.ritasi_nobukti', 'b.value')
                ->leftJoin(db::raw("$temptrip as c with (readuncommitted)"), 'a.nobukti', 'c.nobukti')
                ->whereRaw("isnull(c.nobukti,'')=''")
                ->groupBy('a.nobukti');
            DB::table($temptrip)->insertUsing(['nobukti'], $ritasi);

            $statusCetak = (new Parameter())->cekId('STATUSCETAK', 'STATUSCETAK', 'CETAK');
            $getRic = DB::table("gajisupirheader")->from(DB::raw("gajisupirheader as a with (readuncommitted)"))
                ->select('a.id', 'a.nobukti')
                ->join(DB::raw("$temptrip as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
                ->where('a.statuscetak', $statusCetak)
                ->get();

            if (count($getRic) > 0) {
                foreach ($getRic as $row => $value) {
                    $tableId[] = $value->id;
                    $bukti[] = $value->nobukti;
                }
                $dataBukaCetak = [
                    'tableId' => $tableId,
                    'bukti' => $bukti,
                    'table' => 'GAJISUPIRHEADER'
                ];
                (new ApprovalBukaCetak())->processStore($dataBukaCetak);
            }
        }
        return $data;
    }
}
