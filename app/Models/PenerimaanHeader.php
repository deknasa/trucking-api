<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\RunningNumberService;
use Illuminate\Database\Schema\Blueprint;

class PenerimaanHeader extends MyModel
{
    use HasFactory;

    protected $table = 'penerimaanheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];


    public function default()
    {

        $bankId = request()->bank_id;

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('bank', 255)->nullable();
            $table->unsignedBigInteger('alatbayar_id')->nullable();
            $table->string('alatbayar', 255)->nullable();
        });


        $bank = DB::table('bank')->from(
            DB::raw('bank with (readuncommitted)')
        )
            ->select(
                'id as bank_id',
                'namabank as bank',
                'tipe'

            )
            ->where('id', '=', $bankId)
            ->first();


        $statusdefault = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS DEFAULT')
            ->where('subgrp', '=', 'STATUS DEFAULT')
            ->where('text', '=', 'DEFAULT')
            ->first();

        $alatbayardefault = $statusdefault->id ?? 0;

        $alatbayar = DB::table('alatbayar')->from(
            DB::raw('alatbayar with (readuncommitted)')
        )
            ->select(
                'id as alatbayar_id',
                'namaalatbayar as alatbayar',

            )
            ->where('statusdefault', '=', $alatbayardefault)
            ->where('tipe', $bank->tipe)
            ->first();

        DB::table($tempdefault)->insert(
            ["bank_id" => $bank->bank_id, "bank" => $bank->bank, "alatbayar_id" => $alatbayar->alatbayar_id, "alatbayar" => $alatbayar->alatbayar]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'bank_id',
                'bank',
                'alatbayar_id',
                'alatbayar',
            );

        $data = $query->first();



        $data = $query->first();

        return $data;
    }

    public function penerimaandetail()
    {
        return $this->hasMany(penerimaandetail::class, 'penerimaan_id');
    }

    public function cekvalidasiaksi($nobukti)
    {

        $error = new Error();
        $keteranganerror = $error->cekKeteranganError('SAPP') ?? '';
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';


        $jurnal = DB::table('penerimaanheader')
            ->from(
                DB::raw("penerimaanheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($jurnal)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $jurnal->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Approval Jurnal ' . $jurnal->nobukti,
                'kodeerror' => 'SAPP',
                'editcoa' => false
            ];
            goto selesai;
        }


        $keteranganerror = $error->cekKeteranganError('TDT') ?? '';

        $pelunasanPiutang = DB::table('pelunasanpiutangheader')
            ->from(
                DB::raw("pelunasanpiutangheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.penerimaan_nobukti'
            )
            ->where('a.penerimaan_nobukti', '=', $nobukti)
            ->first();
        if (isset($pelunasanPiutang)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti Pelunasan Piutang <b>' . $pelunasanPiutang->nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Pelunasan Piutang ' . $pelunasanPiutang->nobukti,
                'kodeerror' => 'TDT',
                'editcoa' => false
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('TDT') ?? '';
        $penerimaanTrucking = DB::table('penerimaantruckingheader')
            ->from(
                DB::raw("penerimaantruckingheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.penerimaan_nobukti'
            )
            ->where('a.penerimaan_nobukti', '=', $nobukti)
            ->first();
        if (isset($penerimaanTrucking)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti penerimaan trucking <b>' . $penerimaanTrucking->nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'penerimaan trucking ' . $penerimaanTrucking->nobukti,
                'kodeerror' => 'TDT',
                'editcoa' => false
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('TDT') ?? '';

        $pengembalianKasgantung = DB::table('pengembaliankasgantungheader')
            ->from(
                DB::raw("pengembaliankasgantungheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.penerimaan_nobukti'
            )
            ->where('a.penerimaan_nobukti', '=', $nobukti)
            ->first();
        if (isset($pengembalianKasgantung)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti pengembalian kas gantung <b>' . $pengembalianKasgantung->nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'pengembalian kas gantung ' . $pengembalianKasgantung->nobukti,
                'kodeerror' => 'TDT',
                'editcoa' => false
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('TDT') ?? '';
        $prosesUangjalan = DB::table('prosesuangjalansupirdetail')
            ->from(
                DB::raw("prosesuangjalansupirdetail as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.penerimaantrucking_nobukti'
            )
            ->where('a.penerimaantrucking_nobukti', '=', $nobukti)
            ->first();
        if (isset($prosesUangjalan)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti proses uang jalan supir <b>' . $prosesUangjalan->nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'proses uang jalan supir ' . $prosesUangjalan->nobukti,
                'kodeerror' => 'TDT',
                'editcoa' => false
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('TDT') ?? '';
        $pengeluaranStok = DB::table('pengeluaranstokheader')
            ->from(
                DB::raw("pengeluaranstokheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.penerimaan_nobukti'
            )
            ->where('a.penerimaan_nobukti', '=', $nobukti)
            ->first();
        if (isset($pengeluaranStok)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti pengeluaran stok <b>' . $pengeluaranStok->nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'pengeluaran stok ' . $pengeluaranStok->nobukti,
                'kodeerror' => 'TDT',
                'editcoa' => false
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('TDT') ?? '';
        $pemutihanSupir = DB::table('pemutihansupirheader')
            ->from(
                DB::raw("pemutihansupirheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.penerimaan_nobukti'
            )
            ->where('a.penerimaan_nobukti', '=', $nobukti)
            ->first();
        if (isset($pemutihanSupir)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti pemutihan supir <b>' . $pemutihanSupir->nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'pemutihan supir ' . $pemutihanSupir->nobukti,
                'kodeerror' => 'TDT',
                'editcoa' => false
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('TDT') ?? '';
        $cekGiro = DB::table('penerimaanheader')
            ->from(
                DB::raw("penerimaanheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.penerimaangiro_nobukti'
            )
            ->where('a.nobukti', '=', $nobukti)
            ->where('a.penerimaangiro_nobukti', '!=', '')
            ->first();
        if (isset($cekGiro)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti GIRO <b>' . $cekGiro->penerimaangiro_nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'pemutihan supir ' . $pemutihanSupir->nobukti,
                'kodeerror' => 'TDT',
                'editcoa' => false
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
        $rekap = DB::table('rekappenerimaandetail')
            ->from(
                DB::raw("rekappenerimaandetail as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.penerimaan_nobukti'
            )
            ->where('a.penerimaan_nobukti', '=', $nobukti)
            ->first();
        if (isset($rekap)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> Rekap Penerimaan <b>' . $rekap->nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Rekap Penerimaan ' . $rekap->nobukti,
                'kodeerror' => 'SATL2',
                'editcoa' => true
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
        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';
        $bankId = request()->bankId ?? '';
        $isBmt = request()->isBmt ?? false;

        $user_id = auth('api')->user()->id ?? 0;
        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'PenerimaanHeaderController';
        if ($proses == 'reload') {
            $temtabel = 'temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;

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
                $table->string('nobukti', 50)->nullable();
                $table->date('tglbukti')->nullable();
                $table->double('nominal', 15, 2)->nullable();
                $table->string('alatbayar_id', 50)->nullable();
                $table->string('agen_id', 1000)->nullable();
                $table->string('bank_id', 1000)->nullable();
                $table->string('postingdari', 1000)->nullable();
                $table->string('diterimadari', 1000)->nullable();
                $table->date('tgllunas')->nullable();
                $table->longtext('userapproval')->nullable();
                $table->date('tglapproval')->nullable();
                $table->longtext('statuscetak')->nullable();
                $table->string('statuscetaktext', 200)->nullable();
                $table->string('userbukacetak', 200)->nullable();
                $table->date('tglbukacetak')->nullable();
                $table->integer('jumlahcetak')->nullable();
                $table->date('tglkirimberkas')->nullable();
                $table->longtext('statuskirimberkas')->nullable();
                $table->string('statuskirimberkastext', 200)->nullable();
                $table->string('userkirimberkas', 200)->nullable();
                $table->string('modifiedby', 200)->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->longtext('statusapproval')->nullable();
                $table->longtext('statusapprovaltext')->nullable();
            });
            $tempNominal = '##tempNominal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempNominal, function ($table) {
                $table->string('nobukti')->nullable();
                $table->double('nominal', 15, 2)->nullable();
            });
            $getNominal = DB::table("penerimaandetail")->from(DB::raw("penerimaandetail with (readuncommitted)"))
                ->select(DB::raw("penerimaanheader.nobukti,SUM(penerimaandetail.nominal) AS nominal"))
                ->join(DB::raw("penerimaanheader with (readuncommitted)"), 'penerimaanheader.id', 'penerimaandetail.penerimaan_id')
                ->groupBy("penerimaanheader.nobukti");
            if (request()->tgldari && request()->tglsampai) {
                $getNominal->whereBetween('penerimaanheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
                    ->where('penerimaanheader.bank_id', request()->bank);
            }

            DB::table($tempNominal)->insertUsing(['nobukti', 'nominal'], $getNominal);
            $query = DB::table($this->table)->from(DB::raw("penerimaanheader with (readuncommitted)"))
                ->select(
                    'penerimaanheader.id',
                    'penerimaanheader.nobukti',
                    'penerimaanheader.tglbukti',
                    'nominal.nominal',
                    'alatbayar.namaalatbayar as alatbayar_id',
                    'agen.namaagen as agen_id',
                    'bank.namabank as bank_id',
                    'penerimaanheader.postingdari',
                    'penerimaanheader.diterimadari',
                    DB::raw('(case when (year(penerimaanheader.tgllunas) <= 2000) then null else penerimaanheader.tgllunas end ) as tgllunas'),
                    'penerimaanheader.userapproval',
                    DB::raw('(case when (year(penerimaanheader.tglapproval) <= 2000) then null else penerimaanheader.tglapproval end ) as tglapproval'),

                    'statuscetak.memo as statuscetak',
                    'statuscetak.text as statuscetaktext',
                    'penerimaanheader.userbukacetak',
                    DB::raw('(case when (year(penerimaanheader.tglbukacetak) <= 2000) then null else penerimaanheader.tglbukacetak end ) as tglbukacetak'),
                    'penerimaanheader.jumlahcetak',
                    DB::raw('(case when (year(penerimaanheader.tglkirimberkas) <= 2000) then null else penerimaanheader.tglkirimberkas end ) as tglkirimberkas'),
                    'statuskirimberkas.memo as statuskirimberkas',
                    'statuskirimberkas.text as statuskirimberkastext',
                    'penerimaanheader.userkirimberkas',

                    'penerimaanheader.modifiedby',
                    'penerimaanheader.created_at',
                    'penerimaanheader.updated_at',
                    'statusapproval.memo as statusapproval',
                    'statusapproval.text as statusapprovaltext',
                )

                ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'penerimaanheader.statusapproval', 'statusapproval.id')
                ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'penerimaanheader.alatbayar_id', 'alatbayar.id')
                ->leftJoin(DB::raw("bank with (readuncommitted)"), 'penerimaanheader.bank_id', 'bank.id')
                ->leftJoin(DB::raw("agen with (readuncommitted)"), 'penerimaanheader.agen_id', 'agen.id')
                ->leftJoin(DB::raw("$tempNominal as nominal with (readuncommitted)"), 'penerimaanheader.nobukti', 'nominal.nobukti')
                ->leftJoin(DB::raw("parameter as statuskirimberkas with (readuncommitted)"), 'penerimaanheader.statuskirimberkas', 'statuskirimberkas.id')
                ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'penerimaanheader.statuscetak', 'statuscetak.id');
            if (request()->tgldari && request()->tglsampai) {
                $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);

                if (request()->bank) {
                    $query->where('penerimaanheader.bank_id', request()->bank);
                }
            }
            if ($isBmt == true) {
                $getBankBmt = DB::table("bank")->from(DB::raw("bank with (readuncommitted)"))->whereRaw("coa = (select coa from bank where id=3)")->where('id', '!=', $bankId)->first();
                $query->where('penerimaanheader.bank_id', $getBankBmt->id);
                if (request()->nobuktiBmt != '') {
                    $nobukti = request()->nobuktiBmt;
                    $query->whereNotIn('penerimaanheader.nobukti', function ($query) {
                        $query->select(DB::raw('DISTINCT pengeluaranheader.penerimaan_nobukti'))
                            ->from('pengeluaranheader')
                            ->whereNotNull('pengeluaranheader.penerimaan_nobukti')
                            ->where('pengeluaranheader.penerimaan_nobukti', '!=', '');
                    });
                    $query->orWhereRaw("penerimaanheader.nobukti in ('$nobukti')");
                } else {
                    $query->whereNotIn('penerimaanheader.nobukti', function ($query) {
                        $query->select(DB::raw('DISTINCT pengeluaranheader.penerimaan_nobukti'))
                            ->from('pengeluaranheader')
                            ->whereNotNull('pengeluaranheader.penerimaan_nobukti')
                            ->where('pengeluaranheader.penerimaan_nobukti', '!=', '');
                    });
                }
            }
            if ($periode != '') {
                $periode = explode("-", $periode);
                $query->whereRaw("MONTH(penerimaanheader.tglbukti) ='" . $periode[0] . "'")
                    ->whereRaw("year(penerimaanheader.tglbukti) ='" . $periode[1] . "'");
            }
            if ($statusCetak != '') {
                $query->where("penerimaanheader.statuscetak", $statusCetak);
            }
            DB::table($temtabel)->insertUsing([
                'id',
                'nobukti',
                'tglbukti',
                'nominal',
                'alatbayar_id',
                'agen_id',
                'bank_id',
                'postingdari',
                'diterimadari',
                'tgllunas',
                'userapproval',
                'tglapproval',
                'statuscetak',
                'statuscetaktext',
                'userbukacetak',
                'tglbukacetak',
                'jumlahcetak',
                'tglkirimberkas',
                'statuskirimberkas',
                'statuskirimberkastext',
                'userkirimberkas',
                'modifiedby',
                'created_at',
                'updated_at',
                'statusapproval',
                'statusapprovaltext',
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

            // dd($querydata);
            $temtabel = $querydata->namatabel;
        }



        $query = DB::table($temtabel)->from(DB::raw($temtabel . " a "))
            ->select(
                'a.id',
                'a.nobukti',
                'a.tglbukti',
                'a.nominal',
                'a.alatbayar_id',
                'a.agen_id',
                'a.bank_id',
                'a.postingdari',
                'a.diterimadari',
                'a.tgllunas',
                'a.userapproval',
                'a.statusapproval',
                'a.statusapprovaltext',
                'a.tglapproval',
                'a.statuscetak',
                'a.statuscetaktext',
                'a.userbukacetak',
                'a.tglbukacetak',
                'a.jumlahcetak',
                'a.tglkirimberkas',
                'a.statuskirimberkastext',
                'a.statuskirimberkas',
                'a.userkirimberkas',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
            );

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);
        $data = $query->get();


        return $data;
    }

    public function tarikPelunasan($id)
    {
        if ($id != 'null') {
            $penerimaan = DB::table('penerimaandetail')->from(DB::raw("penerimaandetail with (readuncommitted)"))
                ->select('pelunasanpiutang_nobukti')->distinct('pelunasanpiutang_nobukti')->where('penerimaan_id', $id)->get();
            $data = [];
            foreach ($penerimaan as $index => $value) {
                $tbl = substr($value->pelunasanpiutang_nobukti, 0, 3);
                if ($tbl == 'PPT') {
                    $pelunasan = DB::table('pelunasanpiutangheader')->from(DB::raw("pelunasanpiutangheader with (readuncommitted)"))
                        ->select(DB::raw("pelunasanpiutangheader.id,pelunasanpiutangheader.nobukti,pelunasanpiutangheader.tglbukti, pelanggan.namapelanggan as pelanggan, (SELECT (SUM(pelunasanpiutangdetail.nominal)) FROM pelunasanpiutangdetail WHERE pelunasanpiutangdetail.nobukti= pelunasanpiutangheader.nobukti) AS nominal"))
                        ->distinct("pelunasanpiutangheader.nobukti")
                        ->leftJoin(DB::raw("pelunasanpiutangdetail with (readuncommitted)"), 'pelunasanpiutangheader.id', 'pelunasanpiutangdetail.pelunasanpiutang_id')
                        ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'pelunasanpiutangdetail.pelanggan_id', 'pelanggan.id')

                        ->where('pelunasanpiutangheader.nobukti', $value->pelunasanpiutang_nobukti)
                        ->get();
                    foreach ($pelunasan as $index => $value) {
                        $data[] = $value;
                    }
                } else {
                    $giro = DB::table('penerimaangiroheader')->from(DB::raw("penerimaangiroheader with (readuncommitted)"))
                        ->select(DB::raw("penerimaangiroheader.id,penerimaangiroheader.nobukti,penerimaangiroheader.tglbukti,pelanggan.namapelanggan as pelanggan,penerimaangirodetail.pelunasanpiutang_nobukti, (SELECT (SUM(penerimaangirodetail.nominal)) FROM penerimaangirodetail WHERE penerimaangirodetail.nobukti = penerimaangiroheader.nobukti) AS nominal"))
                        ->leftJoin(DB::raw("penerimaangirodetail with (readuncommitted)"), 'penerimaangirodetail.nobukti', 'penerimaangiroheader.nobukti')
                        ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'penerimaangiroheader.pelanggan_id', 'pelanggan.id')
                        ->where("penerimaangiroheader.nobukti", $value->pelunasanpiutang_nobukti)
                        ->get();

                    foreach ($giro as $index => $value) {
                        $data[] = $value;
                    }
                }
            }
            return $data;
        } else {
            $tempPelunasan = $this->createTempPelunasan();
            $tempGiro = $this->createTempGiro();

            $pelunasan = DB::table("$tempPelunasan as a")->from(DB::raw("$tempPelunasan as a with (readuncommitted)"))
                ->select(DB::raw("a.nobukti as nobukti, a.id as id,a.tglbukti as tglbukti, a.pelanggan as pelangggan, a.nominal as nominal,null as pelunasanpiutang_nobukti"))
                ->distinct("a.nobukti")
                ->join(DB::raw("$tempGiro as B with (readuncommitted)"), "a.nobukti", "=", "B.pelunasanpiutang_nobukti", "left outer");

            $giro = DB::table($tempGiro)->from(DB::raw("$tempGiro with (readuncommitted)"))
                ->select(DB::raw("nobukti,id,tglbukti,pelanggan,nominal,pelunasanpiutang_nobukti"))

                ->distinct("nobukti")
                ->unionAll($pelunasan);
            $data = $giro->get();
        }

        return $data;
    }
    public function createTempPelunasan()
    {
        $temp = '##tempPelunasan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('pelunasanpiutangheader')->from(DB::raw("pelunasanpiutangheader with (readuncommitted)"))
            ->select(DB::raw("pelunasanpiutangheader.id,pelunasanpiutangheader.nobukti,pelunasanpiutangheader.tglbukti,pelanggan.namapelanggan as pelanggan, (SELECT (SUM(pelunasanpiutangdetail.nominal)) FROM pelunasanpiutangdetail WHERE pelunasanpiutangdetail.nobukti = pelunasanpiutangheader.nobukti) AS nominal"))
            ->join(DB::raw("pelunasanpiutangdetail with (readuncommitted)"), 'pelunasanpiutangheader.id', 'pelunasanpiutangdetail.pelunasanpiutang_id')
            ->join(DB::raw("pelanggan with (readuncommitted)"), 'pelunasanpiutangdetail.pelanggan_id', 'pelanggan.id')
            ->whereRaw("pelunasanpiutangheader.nobukti not in (select pelunasanpiutang_nobukti from penerimaandetail)")
            ->whereRaw("pelunasanpiutangheader.nobukti not in (select pelunasanpiutang_nobukti from penerimaangirodetail)");

        Schema::create($temp, function ($table) {
            $table->bigInteger('id');
            $table->string('nobukti');
            $table->date('tglbukti')->nullable();
            $table->string('pelanggan');
            $table->bigInteger('nominal')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'pelanggan', 'nominal'], $fetch);

        return $temp;
    }

    public function createTempGiro()
    {
        $temp = '##tempGiro' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('penerimaangiroheader')->from(DB::raw("penerimaangiroheader with (readuncommitted)"))
            ->select(DB::raw("penerimaangiroheader.id,penerimaangiroheader.nobukti,penerimaangiroheader.tglbukti,pelanggan.namapelanggan as pelanggan,penerimaangirodetail.pelunasanpiutang_nobukti, (SELECT (SUM(penerimaangirodetail.nominal)) FROM penerimaangirodetail WHERE penerimaangirodetail.nobukti = penerimaangiroheader.nobukti) AS nominal"))
            ->leftJoin('penerimaangirodetail', 'penerimaangirodetail.nobukti', 'penerimaangiroheader.nobukti')
            ->leftJoin('pelanggan', 'penerimaangiroheader.pelanggan_id', 'pelanggan.id')
            ->whereRaw("penerimaangiroheader.nobukti not in (select pelunasanpiutang_nobukti from penerimaandetail)")
            ->whereRaw("penerimaangirodetail.pelunasanpiutang_nobukti != '-'");

        Schema::create($temp, function ($table) {
            $table->bigInteger('id');
            $table->string('nobukti');
            $table->date('tglbukti')->nullable();
            $table->string('pelanggan');
            $table->string('pelunasanpiutang_nobukti');
            $table->bigInteger('nominal')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'pelanggan', 'pelunasanpiutang_nobukti', 'nominal'], $fetch);

        return $temp;
    }

    public function getPelunasan($id, $table)
    {
        if ($table == 'giro') {
            $data = DB::table('penerimaangirodetail')->from(DB::raw("penerimaangirodetail with (readuncommitted)"))
                ->select('id', 'nominal', 'tgljatuhtempo as tgljt', 'invoice_nobukti', 'nobukti')
                ->where('penerimaangiro_id', $id)
                ->get();
        } else {
            $data = DB::table('pelunasanpiutangdetail')->from(DB::raw("pelunasanpiutangdetail with (readuncommitted)"))
                ->select('id', 'nominal', 'tgljt', 'invoice_nobukti', 'nobukti')
                ->where('pelunasanpiutang_id', $id)
                ->get();
        }



        return $data;
    }

    public function findAll($id)
    {
        // dd($id);
        $data = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))
            ->select(
                'penerimaanheader.id',
                'penerimaanheader.nobukti',
                'penerimaanheader.tglbukti',
                'penerimaanheader.penerimaangiro_nobukti',
                DB::raw("(case when penerimaanheader.pelanggan_id=0 then null else penerimaanheader.pelanggan_id end) as pelanggan_id"),
                'pelanggan.namapelanggan as pelanggan',
                'penerimaanheader.statuscetak',
                'penerimaanheader.diterimadari',
                'penerimaanheader.tgllunas',
                'penerimaanheader.bank_id',
                'bank.namabank as bank',
                'penerimaanheader.alatbayar_id',
                'alatbayar.namaalatbayar as alatbayar'
            )
            ->leftjoin(DB::raw("pelanggan with (readuncommitted)"), 'penerimaanheader.pelanggan_id', 'pelanggan.id')
            ->leftjoin(DB::raw("bank with (readuncommitted)"), 'penerimaanheader.bank_id', 'bank.id')
            ->leftjoin(DB::raw("alatbayar with (readuncommitted)"), 'penerimaanheader.alatbayar_id', 'alatbayar.id')
            ->where('penerimaanheader.id', '=', $id)
            ->first();


        return $data;
    }

    public function selectColumns()
    {
        $temp = '##tempselect' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function (Blueprint $table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->string('alatbayar_id', 50)->nullable();
            $table->string('agen_id', 1000)->nullable();
            $table->string('bank', 50)->nullable();
            $table->integer('bank_id')->nullable();
            $table->string('postingdari', 1000)->nullable();
            $table->string('diterimadari', 1000)->nullable();
            $table->date('tgllunas')->nullable();
            $table->longtext('userapproval')->nullable();
            $table->date('tglapproval')->nullable();
            $table->longtext('statuscetak')->nullable();
            $table->string('statuscetaktext', 200)->nullable();
            $table->string('userbukacetak', 200)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->unsignedBigInteger('jumlahcetak')->nullable();
            $table->date('tglkirimberkas')->nullable();
            $table->longtext('statuskirimberkas')->nullable();
            $table->string('statuskirimberkastext', 200)->nullable();
            $table->string('userkirimberkas', 200)->nullable();
            $table->string('modifiedby', 200)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->longtext('statusapproval')->nullable();
            $table->longtext('statusapprovaltext')->nullable();
        });
        $tempNominal = '##tempNominal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempNominal, function ($table) {
            $table->string('nobukti')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });
        $getNominal = DB::table("penerimaandetail")->from(DB::raw("penerimaandetail with (readuncommitted)"))
            ->select(DB::raw("penerimaanheader.nobukti,SUM(penerimaandetail.nominal) AS nominal"))
            ->join(DB::raw("penerimaanheader with (readuncommitted)"), 'penerimaanheader.id', 'penerimaandetail.penerimaan_id')
            ->groupBy("penerimaanheader.nobukti");
        if (request()->tgldariheader && request()->tglsampaiheader) {
            $getNominal->whereBetween('penerimaanheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))])
                ->where('penerimaanheader.bank_id', request()->bankheader);
        }

        DB::table($tempNominal)->insertUsing(['nobukti', 'nominal'], $getNominal);

        $query = DB::table($this->table)->from(DB::raw("penerimaanheader with (readuncommitted)"))
            ->select(
                'penerimaanheader.id',
                'penerimaanheader.nobukti',
                'penerimaanheader.tglbukti',
                'nominal.nominal',
                'alatbayar.namaalatbayar as alatbayar_id',
                'agen.namaagen as agen_id',
                'bank.namabank as bank',
                'penerimaanheader.bank_id',
                'penerimaanheader.postingdari',
                'penerimaanheader.diterimadari',
                DB::raw('(case when (year(penerimaanheader.tgllunas) <= 2000) then null else penerimaanheader.tgllunas end ) as tgllunas'),
                'penerimaanheader.userapproval',
                DB::raw('(case when (year(penerimaanheader.tglapproval) <= 2000) then null else penerimaanheader.tglapproval end ) as tglapproval'),

                'statuscetak.memo as statuscetak',
                'statuscetak.text as statuscetaktext',
                'penerimaanheader.userbukacetak',
                DB::raw('(case when (year(penerimaanheader.tglbukacetak) <= 2000) then null else penerimaanheader.tglbukacetak end ) as tglbukacetak'),
                'penerimaanheader.jumlahcetak',
                DB::raw('(case when (year(penerimaanheader.tglkirimberkas) <= 2000) then null else penerimaanheader.tglkirimberkas end ) as tglkirimberkas'),
                'statuskirimberkas.memo as statuskirimberkas',
                'statuskirimberkas.text as statuskirimberkastext',
                'penerimaanheader.userkirimberkas',

                'penerimaanheader.modifiedby',
                'penerimaanheader.created_at',
                'penerimaanheader.updated_at',
                'statusapproval.memo as statusapproval',
                'statusapproval.text as statusapprovaltext',
            )

            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'penerimaanheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'penerimaanheader.alatbayar_id', 'alatbayar.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'penerimaanheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'penerimaanheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw("$tempNominal as nominal with (readuncommitted)"), 'penerimaanheader.nobukti', 'nominal.nobukti')
            ->leftJoin(DB::raw("parameter as statuskirimberkas with (readuncommitted)"), 'penerimaanheader.statuskirimberkas', 'statuskirimberkas.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'penerimaanheader.statuscetak', 'statuscetak.id');
        if (request()->tgldariheader && request()->tglsampaiheader) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);

            if (request()->bankheader) {
                $query->where('penerimaanheader.bank_id', request()->bankheader);
            }
        }
        DB::table($temp)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'nominal',
            'alatbayar_id',
            'agen_id',
            'bank',
            'bank_id',
            'postingdari',
            'diterimadari',
            'tgllunas',
            'userapproval',
            'tglapproval',
            'statuscetak',
            'statuscetaktext',
            'userbukacetak',
            'tglbukacetak',
            'jumlahcetak',
            'tglkirimberkas',
            'statuskirimberkas',
            'statuskirimberkastext',
            'userkirimberkas',
            'modifiedby',
            'created_at',
            'updated_at',
            'statusapproval',
            'statusapprovaltext',
        ], $query);
        $query = DB::table($temp)->from(DB::raw($temp . " a "))
            ->select(
                'a.id',
                'a.nobukti',
                'a.tglbukti',
                'a.nominal',
                'a.alatbayar_id',
                'a.agen_id',
                'a.bank',
                'a.bank_id',
                'a.postingdari',
                'a.diterimadari',
                'a.tgllunas',
                'a.userapproval',
                'a.statusapproval',
                'a.statusapprovaltext',
                'a.tglapproval',
                'a.statuscetak',
                'a.statuscetaktext',
                'a.userbukacetak',
                'a.tglbukacetak',
                'a.jumlahcetak',
                'a.tglkirimberkas',
                'a.statuskirimberkastext',
                'a.statuskirimberkas',
                'a.userkirimberkas',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
            );

        return $query;
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->integer('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->string('alatbayar_id', 50)->nullable();
            $table->string('agen_id', 1000)->nullable();
            $table->string('bank', 50)->nullable();
            $table->integer('bank_id')->nullable();
            $table->string('postingdari', 1000)->nullable();
            $table->string('diterimadari', 1000)->nullable();
            $table->date('tgllunas')->nullable();
            $table->longtext('userapproval')->nullable();
            $table->longtext('statusapproval')->nullable();
            $table->longtext('statusapprovaltext')->nullable();
            $table->date('tglapproval')->nullable();
            $table->longtext('statuscetak')->nullable();
            $table->string('statuscetaktext', 200)->nullable();
            $table->string('userbukacetak', 200)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->nullable();
            $table->date('tglkirimberkas')->nullable();
            $table->string('statuskirimberkastext', 200)->nullable();
            $table->longtext('statuskirimberkas')->nullable();
            $table->string('userkirimberkas', 200)->nullable();
            $table->string('modifiedby', 200)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });
        if ((date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tgldariheader))) || (date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tglsampaiheader)))) {
            request()->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            request()->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
        }
        $this->setRequestParameters();
        $query = $this->selectColumns();
        $this->sort($query);
        $models = $this->filter($query);
        $models =  $query->whereBetween('a.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))])->where('a.bank_id', request()->bankheader);
        DB::table($temp)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'nominal',
            'alatbayar_id',
            'agen_id',
            'bank',
            'bank_id',
            'postingdari',
            'diterimadari',
            'tgllunas',
            'userapproval',
            'statusapproval',
            'statusapprovaltext',
            'tglapproval',
            'statuscetak',
            'statuscetaktext',
            'userbukacetak',
            'tglbukacetak',
            'jumlahcetak',
            'tglkirimberkas',
            'statuskirimberkastext',
            'statuskirimberkas',
            'userkirimberkas',
            'modifiedby',
            'created_at',
            'updated_at',
        ], $models);

        return  $temp;
    }


    public function sort($query)
    {
        // if ($this->params['sortIndex'] == 'bank_id') {
        //     return $query->orderBy('bank.namabank', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'agen_id') {
        //     return $query->orderBy('agen.namaagen', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'alatbayar_id') {
        //     return $query->orderBy('alatbayar.namaalatbayar', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'nobukti_penerimaan') {
        //     return $query->orderBy('penerimaanheader.nobukti', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'tglbukti_penerimaan') {
        //     return $query->orderBy('penerimaanheader.tglbukti', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'nominal_detail') {
        //     return $query->orderBy('penerimaanheader.nominal', $this->params['sortOrder']);
        // } else {
        return $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
        // }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statusapproval') {
                                $query = $query->where('a.statusapprovaltext', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statuscetak') {
                                $query = $query->where('a.statuscetaktext', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statuskirimberkas') {
                                $query = $query->where('a.statuskirimberkastext', '=', "$filters[data]");
                            } else if ($filters['field'] == 'created_at') {
                                $query = $query->whereRaw("format(a.created_at,'dd-MM-yyyy HH:mm:ss') like '%$filters[data]%'");
                            } else if ($filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(a.updated_at,'dd-MM-yyyy HH:mm:ss') like '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti') {
                                $query = $query->whereRaw("format(a.tglbukti,'dd-MM-yyyy') like '%$filters[data]%'");
                            } else if ($filters['field'] == 'tgllunas') {
                                $query = $query->whereRaw("format(a.tgllunas,'dd-MM-yyyy') like '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglapproval') {
                                $query = $query->whereRaw("format(a.tglapproval,'dd-MM-yyyy') like '%$filters[data]%'");
                                // } else if ($filters['field'] == 'tglbukti_penerimaan') {
                                //     $query = $query->whereRaw("format(a.tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                // } else if ($filters['field'] == 'nobukti_penerimaan') {
                                //     $query = $query->where('penerimaanheader.nobukti', 'LIKE', "%$filters[data]%");
                                // } else if ($filters['field'] == 'keterangan_detail') {
                                //     $query = $query->where('penerimaandetail.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->whereRaw("format(a.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'statusapproval') {
                                    $query = $query->orWhere('a.statusapprovaltext', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statuscetak') {
                                    $query = $query->orWhere('a.statuscetaktext', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statuskirimberkas') {
                                    $query = $query->orWhere('a.statuskirimberkastext', '=', "$filters[data]");
                                } else if ($filters['field'] == 'created_at') {
                                    $query = $query->whereRaw("format(a.created_at,'dd-MM-yyyy HH:mm:ss') like '%$filters[data]%'");
                                } else if ($filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(a.updated_at,'dd-MM-yyyy HH:mm:ss') like '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglbukti') {
                                    $query = $query->orWhereRaw("format(a.tglbukti,'dd-MM-yyyy') like '%$filters[data]%'");
                                } else if ($filters['field'] == 'tgllunas') {
                                    $query = $query->orWhereRaw("format(a.tgllunas,'dd-MM-yyyy') like '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglapproval') {
                                    $query = $query->orWhereRaw("format(a.tglapproval,'dd-MM-yyyy') like '%$filters[data]%'");
                                    // } else if ($filters['field'] == 'tglbukti_penerimaan') {
                                    //     $query = $query->orWhereRaw("format(a.tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                    // } else if ($filters['field'] == 'nobukti_penerimaan') {
                                    //     $query = $query->orWhere('penerimaanheader.nobukti', 'LIKE', "%$filters[data]%");
                                    // } else if ($filters['field'] == 'keterangan_detail') {
                                    //     $query = $query->orWhere('penerimaandetail.keterangan', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'nominal') {
                                    $query = $query->orWhereRaw("format(a.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                                } else {
                                    $query = $query->orWhere('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
        if (request()->approve && request()->periode) {
            $query->where('a.statusapproval', request()->approve)
                ->whereYear('a.tglbukti', '=', request()->year)
                ->whereMonth('a.tglbukti', '=', request()->month);
            return $query;
        }
        if (request()->cetak && request()->periode) {
            $query->where('a.statuscetak', '<>', request()->cetak)
                ->whereYear('a.tglbukti', '=', request()->year)
                ->whereMonth('a.tglbukti', '=', request()->month);
            return $query;
        }
        if (request()->cetak && request()->periode) {
            $query->where('a.statuscetak', '<>', request()->cetak)
                ->whereYear('a.tglbukti', '=', request()->year)
                ->whereMonth('a.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function getRekapPenerimaanHeader($bank, $tglbukti)
    {
        $this->setRequestParameters();
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $query = DB::table("penerimaanheader")->from(DB::raw("penerimaanheader"))
            ->select(
                'penerimaanheader.nobukti as nobukti_penerimaan',
                'penerimaanheader.tglbukti as tglbukti_penerimaan',
                DB::raw("SUM(penerimaandetail.nominal) as nominal_detail")

            )
            ->leftJoin(DB::raw("penerimaandetail with (readuncommitted)"), 'penerimaanheader.nobukti', 'penerimaandetail.nobukti')
            ->where('penerimaanheader.bank_id', $bank)
            ->where('penerimaanheader.tglbukti', $tglbukti)
            ->whereRaw("penerimaanheader.nobukti not in (select penerimaan_nobukti from rekappenerimaandetail)")
            ->groupBy('penerimaanheader.nobukti')
            ->groupBy('penerimaanheader.tglbukti');

        Schema::create($temp, function ($table) {
            $table->string('nobukti_penerimaan')->nullable();
            $table->date('tglbukti_penerimaan')->nullable();
            $table->double('nominal_detail', 15, 2)->nullable();
        });

        DB::table($temp)->insertUsing(['nobukti_penerimaan', 'tglbukti_penerimaan', 'nominal_detail'], $query);

        $dataTemp =  DB::table("$temp")->from(DB::raw("$temp"))
            ->select(
                $temp . '.nobukti_penerimaan',
                $temp . '.tglbukti_penerimaan',
                $temp . '.nominal_detail',
            );

        $this->filterRekap($dataTemp, $temp);
        $this->totalNominal = $dataTemp->sum($temp . '.nominal_detail');
        $this->totalRows = $dataTemp->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $dataTemp->orderBy($temp . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        $dataTemp->skip($this->params['offset'])->take($this->params['limit']);

        $data = $dataTemp->get();

        return $data;
    }

    public function filterRekap($dataTemp, $temp, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'tglbukti_penerimaan') {
                                $dataTemp = $dataTemp->whereRaw("format(" . $temp . ".tglbukti_penerimaan, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'nominal_detail') {
                                $dataTemp = $dataTemp->whereRaw("format($temp.nominal_detail, '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                // $dataTemp = $dataTemp->where($temp . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $dataTemp = $dataTemp->whereRaw($temp . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    $dataTemp = $dataTemp->where(function ($dataTemp, $temp) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'tglbukti_penerimaan') {
                                    $dataTemp = $dataTemp->orWhereRaw("format(" . $temp . ".tglbukti_penerimaan, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'nominal_detail') {
                                    $dataTemp = $dataTemp->orWhereRaw("format($temp.nominal_detail, '#,#0.00') LIKE '%$filters[data]%'");
                                } else {
                                    // $dataTemp->orWhere($temp . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $dataTemp = $dataTemp->OrwhereRaw($temp . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                                }
                            }
                        }
                    });

                    break;
                default:

                    break;
            }
        }

        return $dataTemp;
    }


    public function processStore(array $data): PenerimaanHeader
    {
        $bankid = $data['bank_id'];

        $querysubgrppenerimaan = Bank::from(DB::raw("bank with (readuncommitted)"))
            ->select(
                'parameter.grp',
                'parameter.subgrp',
                'bank.formatpenerimaan',
                'bank.coa',
                'bank.tipe'
            )
            ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpenerimaan', 'parameter.id')
            ->whereRaw("bank.id = $bankid")
            ->first();
        $group = $querysubgrppenerimaan->grp;
        $subGroup = $querysubgrppenerimaan->subgrp;
        $format = DB::table('parameter')->where('grp', $group)->where('subgrp', $subGroup)->first();

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statuscetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
        $statusKirimBerkas = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSKIRIMBERKAS')->where('text', 'BELUM KIRIM BERKAS')->first();

        $statusdefault = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS DEFAULT')
            ->where('subgrp', '=', 'STATUS DEFAULT')
            ->where('text', '=', 'DEFAULT')
            ->first();

        $alatbayardefault = $statusdefault->id ?? 0;

        $alatbayar = DB::table('alatbayar')->from(
            DB::raw('alatbayar with (readuncommitted)')
        )
            ->select(
                'id as alatbayar_id',
                'namaalatbayar as alatbayar',

            )
            ->where('statusdefault', '=', $alatbayardefault)
            ->where('tipe', $querysubgrppenerimaan->tipe)
            ->first();

        $penerimaanHeader = new PenerimaanHeader();

        $penerimaanHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $penerimaanHeader->pelanggan_id = $data['pelanggan_id'] ?? '';
        $penerimaanHeader->agen_id = $data['agen_id'] ?? '';
        $penerimaanHeader->postingdari = $data['postingdari'] ?? 'ENTRY PENERIMAAN KAS/BANK';
        $penerimaanHeader->diterimadari = $data['diterimadari'] ?? '';
        $penerimaanHeader->tgllunas = date('Y-m-d', strtotime($data['tgllunas']));
        $penerimaanHeader->bank_id = $data['bank_id'] ?? '';
        $penerimaanHeader->alatbayar_id = $data['alatbayar_id'] ?? $alatbayar->alatbayar_id;
        $penerimaanHeader->penerimaangiro_nobukti = $data['penerimaangiro_nobukti'] ?? '';
        $penerimaanHeader->statusapproval = $statusApproval->id;
        $penerimaanHeader->statuscetak = $statuscetak->id;
        $penerimaanHeader->modifiedby = auth('api')->user()->name;
        $penerimaanHeader->statuskirimberkas = $statusKirimBerkas->id;
        $penerimaanHeader->userkirimberkas = '';
        $penerimaanHeader->tglkirimberkas = '';
        $penerimaanHeader->info = html_entity_decode(request()->info);
        $penerimaanHeader->statusformat = $data['statusformat'] ?? $querysubgrppenerimaan->formatpenerimaan;
        $penerimaanHeader->nobukti = (new RunningNumberService)->get($group, $subGroup, $penerimaanHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$penerimaanHeader->save()) {
            throw new \Exception("Error storing Hutang header.");
        }

        $penerimaanHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($penerimaanHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY penerimaan Header '),
            'idtrans' => $penerimaanHeader->id,
            'nobuktitrans' => $penerimaanHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $penerimaanHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        $penerimaanDetails = [];
        $coakredit_detail = [];
        $coadebet_detail = [];
        $nominal_detail = [];
        $keterangan_detail = [];
        for ($i = 0; $i < count($data['nominal_detail']); $i++) {
            $penerimaanDetail = (new PenerimaanDetail())->processStore($penerimaanHeader, [
                'penerimaan_id' => $penerimaanHeader->id,
                'nobukti' => $penerimaanHeader->nobukti,
                'nowarkat' => $data['nowarkat'][$i] ?? '',
                'tgljatuhtempo' =>  date('Y-m-d', strtotime($data['tgljatuhtempo'][$i])),
                'nominal' => $data['nominal_detail'][$i],
                'coadebet' => $data['coadebet'][$i] ?? $querysubgrppenerimaan->coa,
                'coakredit' => $data['coakredit'][$i],
                'keterangan' => $data['keterangan_detail'][$i],
                'bank_id' => $penerimaanHeader->bank_id,
                'invoice_nobukti' => $data['invoice_nobukti'][$i] ?? '-',
                'bankpelanggan_id' => $data['bankpelanggan_id'][$i] ?? '',
                'penerimaangiro_nobukti' => $data['penerimaangiro_nobukti'] ?? '',
                'pelunasanpiutang_nobukti' => $data['pelunasanpiutang_nobukti'][$i] ?? '-',
                'bulanbeban' =>  date('Y-m-d', strtotime($data['bulanbeban'][$i] ?? '1900/1/1')),
                'modifiedby' => auth('api')->user()->name,
            ]);
            $penerimaanDetails[] = $penerimaanDetail->toArray();
            $coakredit_detail[] = $data['coakredit'][$i];
            $coadebet_detail[] = $querysubgrppenerimaan->coa;
            $nominal_detail[] = $data['nominal_detail'][$i];
            $keterangan_detail[] = $data['keterangan_detail'][$i];
        }

        $penerimaanDetailLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($penerimaanDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY penerimaan detail '),
            'idtrans' => $penerimaanHeaderLogTrail->id,
            'nobuktitrans' => $penerimaanHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $penerimaanDetails,
            'modifiedby' => auth('api')->user()->user
        ]);

        /*STORE JURNAL*/
        $jurnalRequest = [
            'tanpaprosesnobukti' => 1,
            'nobukti' => $penerimaanHeader->nobukti,
            'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
            'postingdari' => $data['postingdari'] ?? "ENTRY PENERIMAAN",
            'statusapproval' => $statusApproval->id,
            'userapproval' => "",
            'tglapproval' => "",
            'modifiedby' => auth('api')->user()->name,
            'statusformat' => "0",
            'coakredit_detail' => $coakredit_detail,
            'coadebet_detail' => $coadebet_detail,
            'nominal_detail' => $nominal_detail,
            'keterangan_detail' => $keterangan_detail
        ];
        $jurnalUmumHeader = (new JurnalUmumHeader())->processStore($jurnalRequest);
        return $penerimaanHeader;
    }
    public function processUpdate(PenerimaanHeader $penerimaanHeader, array $data): PenerimaanHeader
    {
        $getTgl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'EDIT TANGGAL BUKTI')->where('subgrp', 'PENERIMAAN KAS/BANK')->first();

        $nobuktiOld = $penerimaanHeader->nobukti;
        $bankid = $data['bank_id'];

        $querysubgrppenerimaan = Bank::from(DB::raw("bank with (readuncommitted)"))
            ->select(
                'parameter.grp',
                'parameter.subgrp',
                'bank.formatpenerimaan',
                'bank.coa',
                'bank.tipe'
            )
            ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpenerimaan', 'parameter.id')
            ->whereRaw("bank.id = $bankid")
            ->first();
        $group = $querysubgrppenerimaan->grp;
        $subGroup = $querysubgrppenerimaan->subgrp;

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statuscetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        if (trim($getTgl->text) == 'YA') {
            $querycek = DB::table('penerimaanheader')->from(
                DB::raw("penerimaanheader a with (readuncommitted)")
            )
                ->select(
                    'a.nobukti'
                )
                ->where('a.id', $penerimaanHeader->id)
                ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
                ->first();


            if (isset($querycek)) {
                $nobukti = $querycek->nobukti;
            } else {
                $nobukti = (new RunningNumberService)->get($group, $subGroup, $penerimaanHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
            }

            $penerimaanHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
            $penerimaanHeader->nobukti = $nobukti;
        }

        $penerimaanHeader->pelanggan_id = $data['pelanggan_id'] ?? '';
        $penerimaanHeader->postingdari = $data['postingdari'] ?? 'EDIT PENERIMAAN KAS/BANK';
        $penerimaanHeader->diterimadari = $data['diterimadari'] ?? '';
        $penerimaanHeader->tgllunas = date('Y-m-d', strtotime($data['tgllunas']));
        $penerimaanHeader->bank_id = $data['bank_id'] ?? '';
        $penerimaanHeader->penerimaangiro_nobukti = $data['penerimaangiro_nobukti'] ?? '';
        $penerimaanHeader->modifiedby = auth('api')->user()->name;
        $penerimaanHeader->editing_by = '';
        $penerimaanHeader->editing_at = null;
        $penerimaanHeader->info = html_entity_decode(request()->info);
        $penerimaanHeader->agen_id = $data['agen_id'] ?? '';

        if (!$penerimaanHeader->save()) {
            throw new \Exception("Error Update penerimaan header.");
        }

        $penerimaanHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($penerimaanHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('EDIT penerimaan Header '),
            'idtrans' => $penerimaanHeader->id,
            'nobuktitrans' => $penerimaanHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $penerimaanHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        /*DELETE EXISTING Penerimaan*/
        $penerimaanDetail = PenerimaanDetail::where('penerimaan_id', $penerimaanHeader->id)->lockForUpdate()->delete();


        $penerimaanDetails = [];
        $coakredit_detail = [];
        $coadebet_detail = [];
        $nominal_detail = [];
        $keterangan_detail = [];
        for ($i = 0; $i < count($data['nominal_detail']); $i++) {
            $penerimaanDetail = (new PenerimaanDetail())->processStore($penerimaanHeader, [
                'penerimaan_id' => $penerimaanHeader->id,
                'nobukti' => $penerimaanHeader->nobukti,
                'nowarkat' => $data['nowarkat'][$i] ?? '',
                'tgljatuhtempo' =>  date('Y-m-d', strtotime($data['tgljatuhtempo'][$i])),
                'nominal' => $data['nominal_detail'][$i],
                'coadebet' => $querysubgrppenerimaan->coa,
                'coakredit' => $data['coakredit'][$i],
                'keterangan' => $data['keterangan_detail'][$i],
                'bank_id' => $penerimaanHeader->bank_id,
                'invoice_nobukti' => $data['invoice_nobukti'][$i] ?? '-',
                'bankpelanggan_id' => $data['bankpelanggan_id'][$i] ?? '',
                'penerimaangiro_nobukti' => $data['penerimaangiro_nobukti'] ?? '',
                'pelunasanpiutang_nobukti' => $data['pelunasanpiutang_nobukti'][$i] ?? '-',
                'bulanbeban' =>  date('Y-m-d', strtotime($data['bulanbeban'][$i] ?? '1900/1/1')),
                'modifiedby' => auth('api')->user()->name,
            ]);
            $penerimaanDetails[] = $penerimaanDetail->toArray();
            $coakredit_detail[] = $data['coakredit'][$i];
            $coadebet_detail[] = $querysubgrppenerimaan->coa;
            $nominal_detail[] = $data['nominal_detail'][$i];
            $keterangan_detail[] = $data['keterangan_detail'][$i];
        }

        $penerimaanDetailLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($penerimaanDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY penerimaan detail '),
            'idtrans' => $penerimaanHeaderLogTrail->id,
            'nobuktitrans' => $penerimaanHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $penerimaanDetails,
            'modifiedby' => auth('api')->user()->user
        ]);

        /*STORE JURNAL*/
        $jurnalRequest = [
            'tanpaprosesnobukti' => 1,
            'nobukti' => $penerimaanHeader->nobukti,
            'tglbukti' => $penerimaanHeader->tglbukti,
            'postingdari' => $data['postingdari'] ?? "ENTRY PENERIMAAN",
            'statusapproval' => $statusApproval->id,
            'userapproval' => "",
            'tglapproval' => "",
            'modifiedby' => auth('api')->user()->name,
            'statusformat' => "0",
            'coakredit_detail' => $coakredit_detail,
            'coadebet_detail' => $coadebet_detail,
            'nominal_detail' => $nominal_detail,
            'keterangan_detail' => $keterangan_detail
        ];
        /*DELETE EXISTING JURNAL*/
        $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $nobuktiOld)->first();
        $newJurnal = new JurnalUmumHeader();
        $newJurnal = $newJurnal->find($getJurnal->id);
        (new JurnalUmumHeader())->processUpdate($newJurnal, $jurnalRequest);

        return $penerimaanHeader;
    }


    public function processDestroy($id, $postingdari = ""): PenerimaanHeader
    {
        $penerimaanDetail = PenerimaanDetail::where('penerimaan_id', '=', $id)->get();
        $dataDetail = $penerimaanDetail->toArray();

        $penerimaanHeader = new PenerimaanHeader();
        $penerimaanHeader = $penerimaanHeader->lockAndDestroy($id);
        $hutangLogTrail = (new LogTrail())->processStore([
            'namatabel' => $this->table,
            'postingdari' => ($postingdari == "") ? $postingdari : strtoupper('DELETE penerimaan  Header'),
            'idtrans' => $penerimaanHeader->id,
            'nobuktitrans' => $penerimaanHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $penerimaanHeader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'PENERIMAANDETAIL',
            'postingdari' => ($postingdari == "") ? $postingdari : strtoupper('DELETE penerimaan  detail'),
            'idtrans' => $hutangLogTrail['id'],
            'nobuktitrans' => $penerimaanHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $dataDetail,
            'modifiedby' => auth('api')->user()->name
        ]);

        /*DELETE EXISTING JURNAL*/
        $jurnalUmumHeader = JurnalUmumHeader::where('nobukti', $penerimaanHeader->nobukti)->first();
        if ($jurnalUmumHeader) {
            (new JurnalUmumHeader())->processDestroy($jurnalUmumHeader->id, ($postingdari == "") ? $postingdari : strtoupper('DELETE penerimaan  detail'));
        }
        return $penerimaanHeader;
    }

    public function getExport($id)
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($this->table)->from(DB::raw("penerimaanheader with (readuncommitted)"))
            ->select(
                'penerimaanheader.id',
                'penerimaanheader.nobukti',
                'penerimaanheader.tglbukti',
                'penerimaanheader.jumlahcetak',
                'bank.namabank as bank_id',
                'bank.tipe as tipe_bank',
                'penerimaanheader.diterimadari',
                'statuscetak.memo as statuscetak',
                'statuscetak.id as  statuscetak_id',
                DB::raw('(case when (year(penerimaanheader.tgllunas) <= 2000) then null else penerimaanheader.tgllunas end ) as tgllunas'),
                'penerimaanheader.userapproval',
                DB::raw("'Bukti Penerimaan' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'penerimaanheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'penerimaanheader.pelanggan_id', 'pelanggan.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'penerimaanheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'penerimaanheader.agen_id', 'agen.id');

        $data = $query->first();
        return $data;
    }
}
