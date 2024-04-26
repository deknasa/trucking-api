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

        $aksi = request()->aksi;
        $trip = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->select('nobukti', 'jobtrucking', 'tglbukti', DB::raw("isnull(approvalbukatanggal_id,0) as approvalbukatanggal_id"), 'tglbataseditsuratpengantar', 'statusapprovaleditsuratpengantar')
            ->where('id', $id)
            ->first();


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

        $cekSP = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->select('dari_id', 'jobtrucking', 'statuslongtrip')->where('nobukti', $nobukti)->first();
        if ($cekSP->dari_id == 1) {
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

        $cekJob = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->where('nobukti_tripasal', $nobukti)->first();
        if ($cekJob != '') {

            $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti trip <b>' . $cekJob->nobukti . '</b> <br> ' . $keterangantambahanerror,
                'kodeerror' => 'SATL2',
            ];


            goto selesai;
        }
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
            $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti ritasi <b>' . $ritasi->nobukti . '</b> <br> ' . $keterangantambahanerror,
                'kodeerror' => 'SATL2',
            ];


            goto selesai;
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
        if ($cekSP->dari_id == 1) {
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

        if ($trip->approvalbukatanggal_id > 0) {
            $getTglBatasApproval = DB::table("suratpengantarapprovalinputtrip")->from(DB::raw("suratpengantarapprovalinputtrip with (readuncommitted)"))
                ->where('id', $trip->approvalbukatanggal_id)
                ->first();

            if (date('Y-m-d H:i:s') > date('Y-m-d H:i:s', strtotime($getTglBatasApproval->tglbatas))) {
                if (date('Y-m-d H:i:s') > date('Y-m-d H:i:s', strtotime($trip->tglbataseditsuratpengantar))) {
                    $keteranganerror = $error->cekKeteranganError('LB') ?? '';
                    $data = [
                        'kondisi' => true,
                        'keterangan' => $keteranganerror . "<br> BATAS $aksi " . date('d-m-Y H:i:s', strtotime($getTglBatasApproval->tglbatas)) . ' <br> ' . $keterangantambahanerror,
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
                        if (strtolower($todayIsSunday) != 'sunday' && strtolower($tomorrowIsSunday) != 'sunday') {
                            if ($batasHari > 1) {
                                $batasHari -= 1;
                            }
                        }
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
        $get = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->select('statusupahzona')->where('id', $id)->first();
        $pelabuhan = DB::table('parameter')
            ->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', '=', 'PELABUHAN CABANG')
            ->where('subgrp', '=', 'PELABUHAN CABANG')
            ->first();
        $idkandang = (new Parameter())->cekText('KANDANG', 'KANDANG') ?? 0;
        $pelabuhan = $pelabuhan->text . ',' . $idkandang;

        if ($get->statusupahzona == $getBukanUpahZona->id) {

            $data = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
                ->select(
                    'suratpengantar.id',
                    'suratpengantar.nobukti',
                    'suratpengantar.tglbukti',
                    'suratpengantar.jobtrucking',
                    'suratpengantar.statuskandang',
                    'suratpengantar.statuslongtrip',
                    'suratpengantar.statusupahzona',
                    'orderantrucking.statuslangsir',
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
                    'suratpengantar.nobukti_tripasal',
                    DB::raw("(trim(kotadari.kodekota)+' - '+trim(kotasampai.kodekota)) as upah"),
                    DB::raw("(CASE WHEN (suratpengantar.sampai_id in ($pelabuhan)) then 1 else 0 end) as statuspelabuhan"),
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
                ->leftJoin('absensisupirheader', 'suratpengantar.tglbukti', 'absensisupirheader.tglbukti')
                ->leftJoin('absensisupirdetail', 'absensisupirheader.id', 'absensisupirdetail.absensi_id')
                ->where('suratpengantar.id', $id)->first();
        } else {

            $data = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
                ->select(
                    'suratpengantar.id',
                    'suratpengantar.nobukti',
                    'suratpengantar.tglbukti',
                    'suratpengantar.jobtrucking',
                    'suratpengantar.statuskandang',
                    'suratpengantar.statuslongtrip',
                    'suratpengantar.statusupahzona',
                    'orderantrucking.statuslangsir',
                    'suratpengantar.trado_id',
                    'trado.kodetrado as trado',
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
                    'suratpengantar.nobukti_tripasal',
                    'zonaupah.zona as upah',
                    'absensisupirdetail.id as absensidetail_id'
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
                ->leftJoin('orderantrucking', 'suratpengantar.jobtrucking', 'orderantrucking.nobukti')
                ->leftJoin('absensisupirheader', 'suratpengantar.tglbukti', 'absensisupirheader.tglbukti')
                ->leftJoin('absensisupirdetail', 'absensisupirheader.id', 'absensisupirdetail.absensi_id')
                // ->leftJoin(DB::raw("$tempGaji with (readuncommitted)"), "$tempGaji.id", "suratpengantar.id")

                ->where('suratpengantar.id', $id)->first();
        }
        // dd('find');
        return $data;
    }

    public function processUpdate($id, array $data)
    {
        $trip = SuratPengantar::findOrFail($id);
        $isDifferent = false;
        $isTripPulang = false;
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
                ->where('a.text', '=', 'BUKAN LANGSIR')
                ->first();

            $tglBatasEdit = date('Y-m-d', strtotime($data['tglbukti'])) . ' ' . '12:00:00';
            $orderan = [
                'tglbukti' => $data['tglbukti'],
                'container_id' => $data['container_id'],
                'agen_id' => $data['agen_id'],
                'jenisorder_id' => $data['jenisorder_id'],
                'pelanggan_id' => $data['pelanggan_id'],
                'tarifrincian_id' => $data['tarifrincian_id'],
                'nojobemkl' =>  '',
                'nocont' =>   '',
                'noseal' =>  '',
                'nojobemkl2' => '',
                'nocont2' => '',
                'noseal2' => '',
                'statuslangsir' => $statuslangsir->id,
                'statusperalihan' => $statusperalihan->id,
                'tglbataseditorderantrucking' => $tglBatasEdit,
                'inputtripmandor' =>  '1',
            ];
            $orderanTrucking = (new OrderanTrucking())->processStore($orderan);
            $trip->jobtrucking = $orderanTrucking->nobukti;
            goto trip;
        }

        if ($trip->statuscontainer_id != 3) {
            if ($trip->dari_id != 1) {
                $cek = [$trip->agen_id, $trip->jenisorder_id, $trip->statuscontainer_id, $trip->container_id, $trip->upah_id, $trip->pelanggan_id];

                $toCek = [$data['agen_id'], $data['jenisorder_id'], $data['statuscontainer_id'], $data['container_id'], $data['upah_id'], $data['pelanggan_id']];

                $differences = array_diff_assoc($cek, $toCek);
                if (!empty($differences)) {
                    if ($data['jobtrucking'] == '') {
                        if ($trip->statuslongtrip != $data['statuslongtrip']) {
                            if ($data['statusgudangsama'] != 65) {
                                $getId = DB::table("orderantrucking")->from(DB::raw("orderantrucking with (readuncommitted)"))->where('nobukti', $trip->jobtrucking)->first();
                                (new OrderanTrucking())->processDestroy($getId->id);
                            }
                        }
                        $isDifferent = true;
                        $isTripPulang = true;
                    } else {
                        if ($trip->statusgudangsama != $data['statusgudangsama']) {
                            if ($data['statusgudangsama'] != 204) {
                                $getId = DB::table("orderantrucking")->from(DB::raw("orderantrucking with (readuncommitted)"))->where('nobukti', $trip->jobtrucking)->first();
                                (new OrderanTrucking())->processDestroy($getId->id);
                            }
                        }
                        $trip->jobtrucking = $data['jobtrucking'];
                        $isTripPulang = true;
                    }
                }
            } else {
                $idkandang = (new Parameter())->cekText('KANDANG', 'KANDANG') ?? 0;

                if ($data['dari_id'] != 1 && $data['dari_id'] != $idkandang) {

                    if ($data['statusgudangsama'] != 204) {

                        $count = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->where('jobtrucking', $trip->jobtrucking)->count();
                        if ($count == 1) {
                            $getId = DB::table("orderantrucking")->from(DB::raw("orderantrucking with (readuncommitted)"))->where('nobukti', $trip->jobtrucking)->first();
                            (new OrderanTrucking())->processDestroy($getId->id);
                        }

                        $trip->jobtrucking = $data['jobtrucking'];
                        $isTripPulang = true;
                    }
                }
            }
        } else {

            if ($trip->dari_id != 1) {
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
                                (new OrderanTrucking())->processDestroy($getId->id);
                            }
                        }
                        $trip->jobtrucking = $data['jobtrucking'];
                        $isTripPulang = true;
                    }
                }
            } else {
                if ($data['nobukti_tripasal'] != '') {
                    $getId = DB::table("orderantrucking")->from(DB::raw("orderantrucking with (readuncommitted)"))->where('nobukti', $trip->jobtrucking)->first();
                    (new OrderanTrucking())->processDestroy($getId->id);
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
                    'statusperalihan' => $statusperalihan->id,
                    'inputtripmandor' =>  'true',
                ];

                $orderanTrucking = (new OrderanTrucking())->processUpdate($getJobtrucking, $orderan);
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
                    'statusperalihan' => $statusperalihan->id,
                    'inputtripmandor' =>  'true',
                ];

                $orderanTrucking = (new OrderanTrucking())->processUpdate($getJobtrucking, $orderan);
            } else {
                $tglBatasEdit = date('Y-m-d', strtotime($data['tglbukti'])) . ' ' . '12:00:00';
                $orderan = [
                    'tglbukti' => $data['tglbukti'],
                    'container_id' => $data['container_id'],
                    'agen_id' => $data['agen_id'],
                    'jenisorder_id' => $data['jenisorder_id'],
                    'pelanggan_id' => $data['pelanggan_id'],
                    'tarifrincian_id' => $data['tarifrincian_id'],
                    'nojobemkl' => $data['nojobemkl'] ?? '',
                    'nocont' => $data['nocont'] ?? '',
                    'noseal' => $data['noseal'] ?? '',
                    'nojobemkl2' => $data['nojobemkl2'] ?? '',
                    'nocont2' => $data['nocont2'] ?? '',
                    'noseal2' => $data['noseal2'] ?? '',
                    'statuslangsir' => $data['statuslangsir'] ?? $statuslangsir->id,
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
        $tarifrincian = TarifRincian::where('tarif_id', $data['tarifrincian_id'])->where('container_id', $data['container_id'])->first();

        $getZona = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS UPAH ZONA')->where('text', 'UPAH ZONA')->first();
        $upahZona = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))->where('id', $data['upah_id'])->first();

        $data['zonadari_id'] = '';
        $data['zonasampai_id'] = '';

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
        $idpelabuhan = $parameter->cekText('PELABUHAN CABANG', 'PELABUHAN CABANG') ?? 0;

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
            ->where('a.kotadari_id', $idpelabuhan)
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
                $nominalkomisi = $trip->komisisupir;
            } else {
                $nominalspr = $nominalSupir;
                $nominalkenek = $trip->gajikenek;
                $nominalkomisi = $trip->komisisupir;
            }
        }
        $dataSP = [

            'jobtrucking' => $trip->jobtrucking,
            'tglbukti' => $data['tglbukti'],
            'pelanggan_id' => $data['pelanggan_id'],
            'upah_id' => $data['upah_id'],
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
            'statusgudangsama' => $data['statusgudangsama'],
            'statuskandang' => $data['statuskandang'],
            'gudang' => $data['gudang'],
            'lokasibongkarmuat' => $data['lokasibongkarmuat'],
            'tarif_id' => $data['tarifrincian_id'],
            'edittripmandor' => '1',
            'nominal' => '',
            'nobukti_tripasal' => $data['nobukti_tripasal'],
            'keterangan' => $trip->keterangan,
            'nourutorder' => $trip->nourutorder,
            'nosptagihlain' => $trip->nosptagihlain,
            'qtyton' => $trip->qtyton,
            'biayatambahan_id' => $trip->biayatambahan_id,
            'persentaseperalihan' => $trip->persentaseperalihan,
            'nosp' => $trip->nosp,
        ];
        $suratPengantar = (new SuratPengantar())->processUpdate($trip, $dataSP);

        // $jenisRitasi = false;
        // foreach ($data['jenisritasi_id'] as $value) {
        //     if ($value != null || $value != 0) {
        //         $jenisRitasi = true;
        //         break;
        //     }
        // }

        // if ($jenisRitasi) {
        //     for ($i = 0; $i < count($data['jenisritasi_id']); $i++) {
        //         $ritasiData = [
        //             'statusritasi_id' => $data['jenisritasi_id'][$i],
        //             'suratpengantar_nobukti' => $suratPengantar->nobukti,
        //             'supir_id' => $data['supir_id'],
        //             'trado_id' => $data['trado_id'],
        //             'dari_id' => $data['ritasidari_id'][$i],
        //             'sampai_id' => $data['ritasike_id'][$i],
        //         ];
        //         $ritasi = Ritasi::findOrFail($data['ritasi_id'][$i]);
        //         (new Ritasi())->processUpdate($ritasi, $ritasiData);
        //     }
        // }

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
}
