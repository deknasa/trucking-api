<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PenerimaanGiroHeader extends MyModel
{
    use HasFactory;

    protected $table = 'penerimaangiroheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];


    public function cekvalidasiaksi($nobukti)
    {

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';


        $penerimaan = DB::table('penerimaanheader')
            ->from(
                DB::raw("penerimaanheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.penerimaangiro_nobukti'
            )
            ->where('a.penerimaangiro_nobukti', '=', $nobukti)
            ->first();
        if (isset($penerimaan)) {
            $keteranganerror = $error->cekKeteranganError('SCG') ?? '';

            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti pencairan giro <b>' . $penerimaan->nobukti . '</b> <br> ' . $keterangantambahanerror,
                'kodeerror' => 'SCG'
            ];
            goto selesai;
        }


        $pelunasanPiutang = DB::table('pelunasanpiutangheader')
            ->from(
                DB::raw("pelunasanpiutangheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.penerimaangiro_nobukti'
            )
            ->where('a.penerimaangiro_nobukti', '=', $nobukti)
            ->first();
        if (isset($pelunasanPiutang)) {
            $keteranganerror = $error->cekKeteranganError('TDT') ?? '';

            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti pelunasan <b>' . $pelunasanPiutang->nobukti . '</b> <br> ' . $keterangantambahanerror,
                'kodeerror' => 'TDT'
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

        $user_id = auth('api')->user()->id ?? 0;
        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'PengeluaranHeaderController';
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
                $table->string('agen_id', 1000)->nullable();
                $table->string('postingdari', 1000)->nullable();
                $table->string('diterimadari', 1000)->nullable();
                $table->date('tgllunas', 1000)->nullable();
                $table->longtext('statusapproval')->nullable();
                $table->string('statusapprovaltext', 200)->nullable();
                $table->date('tglapproval')->nullable();
                $table->string('userapproval', 200)->nullable();
                $table->longtext('statuscetak')->nullable();
                $table->string('statuscetaktext', 200)->nullable();
                $table->date('tglbukacetak')->nullable();
                $table->string('userbukacetak', 200)->nullable();
                $table->date('tglkirimberkas')->nullable();
                $table->longtext('statuskirimberkas')->nullable();
                $table->string('statuskirimberkastext', 200)->nullable();
                $table->string('userkirimberkas', 200)->nullable();
                $table->dateTime('created_at')->nullable();
                $table->string('modifiedby', 200)->nullable();
                $table->dateTime('updated_at')->nullable();
            });

            $tempNominal = '##tempNominal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempNominal, function ($table) {
                $table->string('nobukti')->nullable();
                $table->double('nominal', 15, 2)->nullable();
            });
            $getNominal = DB::table("penerimaangirodetail")->from(DB::raw("penerimaangirodetail with (readuncommitted)"))
                ->select(DB::raw("penerimaangiroheader.nobukti,SUM(penerimaangirodetail.nominal) AS nominal"))
                ->join(DB::raw("penerimaangiroheader with (readuncommitted)"), 'penerimaangiroheader.id', 'penerimaangirodetail.penerimaangiro_id')
                ->groupBy("penerimaangiroheader.nobukti");
            if (request()->tgldari && request()->tglsampai) {
                $getNominal->whereBetween('penerimaangiroheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
            }
            DB::table($tempNominal)->insertUsing(['nobukti', 'nominal'], $getNominal);

            $query = DB::table($this->table)->from(DB::raw("penerimaangiroheader with (readuncommitted)"))
                ->select(
                    'penerimaangiroheader.id',
                    'penerimaangiroheader.nobukti',
                    'penerimaangiroheader.tglbukti',
                    'nominal.nominal',
                    'agen.namaagen as agen_id',
                    'penerimaangiroheader.postingdari',
                    'penerimaangiroheader.diterimadari',
                    'penerimaangiroheader.tgllunas',
                    'statusapproval.memo as statusapproval',
                    'statusapproval.text as statusapprovaltext',
                    DB::raw('(case when (year(penerimaangiroheader.tglapproval) <= 2000) then null else penerimaangiroheader.tglapproval end ) as tglapproval'),
                    'penerimaangiroheader.userapproval',
                    'statuscetak.memo as statuscetak',
                    'statuscetak.text as statuscetaktext',
                    DB::raw('(case when (year(penerimaangiroheader.tglbukacetak) <= 2000) then null else penerimaangiroheader.tglbukacetak end ) as tglbukacetak'),
                    'penerimaangiroheader.userbukacetak',
                    DB::raw('(case when (year(penerimaangiroheader.tglkirimberkas) <= 2000) then null else penerimaangiroheader.tglkirimberkas end ) as tglkirimberkas'),
                    'statuskirimberkas.memo as statuskirimberkas',
                    'statuskirimberkas.text as statuskirimberkastext',
                    'penerimaangiroheader.userkirimberkas',
                    'penerimaangiroheader.created_at',
                    'penerimaangiroheader.modifiedby',
                    'penerimaangiroheader.updated_at'
                )
                ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'penerimaangiroheader.pelanggan_id', 'pelanggan.id')
                ->leftJoin(DB::raw("agen with (readuncommitted)"), 'penerimaangiroheader.agen_id', 'agen.id')
                ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'penerimaangiroheader.statuscetak', 'statuscetak.id')
                ->leftJoin(DB::raw("$tempNominal as nominal with (readuncommitted)"), 'penerimaangiroheader.nobukti', 'nominal.nobukti')
                ->leftJoin(DB::raw("parameter as statuskirimberkas with (readuncommitted)"), 'penerimaangiroheader.statuskirimberkas', 'statuskirimberkas.id')
                ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'penerimaangiroheader.statusapproval', 'statusapproval.id');
            if (request()->tgldari && request()->tglsampai) {
                $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
            }

            if ($periode != '') {
                $periode = explode("-", $periode);
                $query->whereRaw("MONTH(penerimaangiroheader.tglbukti) ='" . $periode[0] . "'")
                    ->whereRaw("year(penerimaangiroheader.tglbukti) ='" . $periode[1] . "'");
            }
            if ($statusCetak != '') {
                $query->where("penerimaangiroheader.statuscetak", $statusCetak);
            }
            DB::table($temtabel)->insertUsing([
                'id',
                'nobukti',
                'tglbukti',
                'nominal',
                'agen_id',
                'postingdari',
                'diterimadari',
                'tgllunas',
                'statusapproval',
                'statusapprovaltext',
                'tglapproval',
                'userapproval',
                'statuscetak',
                'statuscetaktext',
                'tglbukacetak',
                'userbukacetak',
                'tglkirimberkas',
                'statuskirimberkas',
                'statuskirimberkastext',
                'userkirimberkas',
                'created_at',
                'modifiedby',
                'updated_at',
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
                'a.agen_id',
                'a.postingdari',
                'a.diterimadari',
                'a.tgllunas',
                'a.statusapproval',
                'a.statusapprovaltext',
                'a.tglapproval',
                'a.userapproval',
                'a.statuscetak',
                'a.statuscetaktext',
                'a.tglbukacetak',
                'a.userbukacetak',
                'a.tglkirimberkas',
                'a.statuskirimberkas',
                'a.statuskirimberkastext',
                'a.userkirimberkas',
                'a.created_at',
                'a.modifiedby',
                'a.updated_at',
            );
        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $data = $query->get();

        return $data;
    }

    public function selectColumns()
    {
        $temp = '##tempselect' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->string('agen_id', 1000)->nullable();
            $table->string('postingdari', 1000)->nullable();
            $table->string('diterimadari', 1000)->nullable();
            $table->date('tgllunas', 1000)->nullable();
            $table->longtext('statusapproval')->nullable();
            $table->string('statusapprovaltext', 200)->nullable();
            $table->date('tglapproval')->nullable();
            $table->string('userapproval', 200)->nullable();
            $table->longtext('statuscetak')->nullable();
            $table->string('statuscetaktext', 200)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->string('userbukacetak', 200)->nullable();
            $table->date('tglkirimberkas')->nullable();
            $table->longtext('statuskirimberkas')->nullable();
            $table->string('statuskirimberkastext', 200)->nullable();
            $table->string('userkirimberkas', 200)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->string('modifiedby', 200)->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $tempNominal = '##tempNominal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempNominal, function ($table) {
            $table->string('nobukti')->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });
        $getNominal = DB::table("penerimaangirodetail")->from(DB::raw("penerimaangirodetail with (readuncommitted)"))
            ->select(DB::raw("penerimaangiroheader.nobukti,SUM(penerimaangirodetail.nominal) AS nominal"))
            ->join(DB::raw("penerimaangiroheader with (readuncommitted)"), 'penerimaangiroheader.id', 'penerimaangirodetail.penerimaangiro_id')
            ->groupBy("penerimaangiroheader.nobukti");
        if (request()->tgldari && request()->tglsampai) {
            $getNominal->whereBetween('penerimaangiroheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        DB::table($tempNominal)->insertUsing(['nobukti', 'nominal'], $getNominal);


        $query = DB::table($this->table)->from(DB::raw("penerimaangiroheader with (readuncommitted)"))
            ->select(
                'penerimaangiroheader.id',
                'penerimaangiroheader.nobukti',
                'penerimaangiroheader.tglbukti',
                'nominal.nominal',
                'agen.namaagen as agen_id',
                'penerimaangiroheader.postingdari',
                'penerimaangiroheader.diterimadari',
                'penerimaangiroheader.tgllunas',
                'statusapproval.memo as statusapproval',
                'statusapproval.text as statusapprovaltext',
                DB::raw('(case when (year(penerimaangiroheader.tglapproval) <= 2000) then null else penerimaangiroheader.tglapproval end ) as tglapproval'),
                'penerimaangiroheader.userapproval',
                'statuscetak.memo as statuscetak',
                'statuscetak.text as statuscetaktext',
                DB::raw('(case when (year(penerimaangiroheader.tglbukacetak) <= 2000) then null else penerimaangiroheader.tglbukacetak end ) as tglbukacetak'),
                'penerimaangiroheader.userbukacetak',
                DB::raw('(case when (year(penerimaangiroheader.tglkirimberkas) <= 2000) then null else penerimaangiroheader.tglkirimberkas end ) as tglkirimberkas'),
                'statuskirimberkas.memo as statuskirimberkas',
                'statuskirimberkas.text as statuskirimberkastext',
                'penerimaangiroheader.userkirimberkas',
                'penerimaangiroheader.created_at',
                'penerimaangiroheader.modifiedby',
                'penerimaangiroheader.updated_at'
            )
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'penerimaangiroheader.pelanggan_id', 'pelanggan.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'penerimaangiroheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'penerimaangiroheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("$tempNominal as nominal with (readuncommitted)"), 'penerimaangiroheader.nobukti', 'nominal.nobukti')
            ->leftJoin(DB::raw("parameter as statuskirimberkas with (readuncommitted)"), 'penerimaangiroheader.statuskirimberkas', 'statuskirimberkas.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'penerimaangiroheader.statusapproval', 'statusapproval.id');
        if (request()->tgldariheader && request()->tglsampaiheader) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        }

        DB::table($temp)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'nominal',
            'agen_id',
            'postingdari',
            'diterimadari',
            'tgllunas',
            'statusapproval',
            'statusapprovaltext',
            'tglapproval',
            'userapproval',
            'statuscetak',
            'statuscetaktext',
            'tglbukacetak',
            'userbukacetak',
            'tglkirimberkas',
            'statuskirimberkas',
            'statuskirimberkastext',
            'userkirimberkas',
            'created_at',
            'modifiedby',
            'updated_at',
        ], $query);

        $query = DB::table($temp)->from(DB::raw($temp . " a "))
            ->select(
                'a.id',
                'a.nobukti',
                'a.tglbukti',
                'a.nominal',
                'a.agen_id',
                'a.postingdari',
                'a.diterimadari',
                'a.tgllunas',
                'a.statusapproval',
                'a.statusapprovaltext',
                'a.tglapproval',
                'a.userapproval',
                'a.statuscetak',
                'a.statuscetaktext',
                'a.tglbukacetak',
                'a.userbukacetak',
                'a.tglkirimberkas',
                'a.statuskirimberkas',
                'a.statuskirimberkastext',
                'a.userkirimberkas',
                'a.created_at',
                'a.modifiedby',
                'a.updated_at',
            );

        return $query;
    }

    public function findAll($id)
    {
        $data = PenerimaanGiroHeader::from(DB::raw("penerimaangiroheader with (readuncommitted)"))
            ->select(
                'penerimaangiroheader.id',
                'penerimaangiroheader.nobukti',
                'penerimaangiroheader.tglbukti',
                'penerimaangiroheader.agen_id',
                'agen.namaagen as agen',
                'penerimaangiroheader.diterimadari',
                'penerimaangiroheader.tgllunas',
                'penerimaangiroheader.statuscetak'
            )
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'penerimaangiroheader.agen_id', 'agen.id')
            ->where('penerimaangiroheader.id', $id)
            ->first();

        return $data;
    }

    public function tarikPelunasan($id)
    {
        if ($id != 'null') {
            $penerimaan = DB::table('penerimaangirodetail')->from(DB::raw("penerimaangirodetail with (readuncommitted)"))
                ->select('pelunasanpiutang_nobukti')->where('penerimaangiro_id', $id)->first();
            $data = DB::table('pelunasanpiutangheader')->from(DB::raw("pelunasanpiutangheader with (readuncommitted)"))
                ->select(DB::raw("pelunasanpiutangheader.id,pelunasanpiutangheader.nobukti,pelunasanpiutangheader.tglbukti, pelanggan.namapelanggan as pelanggan, (SELECT (SUM(pelunasanpiutangdetail.nominal)) FROM pelunasanpiutangdetail WHERE pelunasanpiutangdetail.nobukti= pelunasanpiutangheader.nobukti) AS nominal"))
                ->distinct("pelunasanpiutangheader.nobukti")
                ->join(DB::raw("pelunasanpiutangdetail with (readuncommitted)"), 'pelunasanpiutangheader.id', 'pelunasanpiutangdetail.pelunasanpiutang_id')
                ->join(DB::raw("pelanggan with (readuncommitted)"), 'pelunasanpiutangdetail.pelanggan_id', 'pelanggan.id')

                ->where('pelunasanpiutangheader.nobukti', $penerimaan->pelunasanpiutang_nobukti)
                ->get();
        } else {

            $data = DB::table('pelunasanpiutangheader')->from(DB::raw("pelunasanpiutangheader with (readuncommitted)"))
                ->select(DB::raw("pelunasanpiutangheader.id,pelunasanpiutangheader.nobukti,pelunasanpiutangheader.tglbukti, pelanggan.namapelanggan as pelanggan, (SELECT (SUM(pelunasanpiutangdetail.nominal)) FROM pelunasanpiutangdetail WHERE pelunasanpiutangdetail.nobukti= pelunasanpiutangheader.nobukti) AS nominal"))
                ->distinct("pelunasanpiutangheader.nobukti")
                ->join(DB::raw("pelunasanpiutangdetail with (readuncommitted)"), 'pelunasanpiutangheader.id', 'pelunasanpiutangdetail.pelunasanpiutang_id')
                ->join(DB::raw("pelanggan with (readuncommitted)"), 'pelunasanpiutangdetail.pelanggan_id', 'pelanggan.id')
                ->whereRaw("pelunasanpiutangheader.nobukti not in (select pelunasanpiutang_nobukti from penerimaangirodetail)")
                ->whereRaw("pelunasanpiutangheader.nobukti not in (select pelunasanpiutang_nobukti from penerimaandetail)")
                ->get();
        }

        return $data;
    }

    public function getPelunasan($id)
    {

        $data = DB::table('pelunasanpiutangdetail')->from(
            DB::raw("pelunasanpiutangdetail with (readuncommitted)")
        )
            ->select('id', 'nominal', 'tgljt', 'keterangan', 'invoice_nobukti', 'nobukti')
            ->where('pelunasanpiutang_id', $id)
            ->get();

        return $data;
    }
    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->integer('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->string('agen_id', 1000)->nullable();
            $table->string('postingdari', 1000)->nullable();
            $table->string('diterimadari', 1000)->nullable();
            $table->date('tgllunas', 1000)->nullable();
            $table->longtext('statusapproval')->nullable();
            $table->string('statusapprovaltext', 200)->nullable();
            $table->date('tglapproval')->nullable();
            $table->string('userapproval', 200)->nullable();
            $table->longtext('statuscetak')->nullable();
            $table->string('statuscetaktext', 200)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->string('userbukacetak', 200)->nullable();
            $table->date('tglkirimberkas')->nullable();
            $table->longtext('statuskirimberkas')->nullable();
            $table->string('statuskirimberkastext', 200)->nullable();
            $table->string('userkirimberkas', 200)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->string('modifiedby', 200)->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });
        // if ((date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tgldariheader))) || (date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tglsampaiheader)))) {
        //     request()->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
        //     request()->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
        // }
        $this->setRequestParameters();
        $query = $this->selectColumns();
        $this->sort($query);
        $models = $this->filter($query);
        $models =  $query->whereBetween('a.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        DB::table($temp)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'nominal',
            'agen_id',
            'postingdari',
            'diterimadari',
            'tgllunas',
            'statusapproval',
            'statusapprovaltext',
            'tglapproval',
            'userapproval',
            'statuscetak',
            'statuscetaktext',
            'tglbukacetak',
            'userbukacetak',
            'tglkirimberkas',
            'statuskirimberkas',
            'statuskirimberkastext',
            'userkirimberkas',
            'created_at',
            'modifiedby',
            'updated_at',
        ], $models);


        return  $temp;
    }


    public function sort($query)
    {
        // if ($this->params['sortIndex'] == 'agen_id') {
        //     return $query->orderBy('agen.namaagen', $this->params['sortOrder']);
        // } else {
        return $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
        // }
    }

    public function getPenerimaan()
    {
        $this->setRequestParameters();
        $temp = '##tempDetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $fetch = DB::table("penerimaangirodetail")->from(DB::raw("penerimaangirodetail with (readuncommitted)"))
            ->select(DB::raw("nobukti, isnull(sum(isnull(nominal,0)),0)"))
            ->groupBy('nobukti');

        Schema::create($temp, function ($table) {
            $table->string('nobukti')->nullable();
            $table->bigInteger('nominal')->nullable();
        });
        DB::table($temp)->insertUsing(['nobukti', 'nominal'], $fetch);
        $query = DB::table("penerimaangiroheader")->from(DB::raw("penerimaangiroheader with (readuncommitted)"))
            ->select(
                'penerimaangiroheader.id',
                'penerimaangiroheader.nobukti',
                'penerimaangiroheader.tglbukti',
                'penerimaangiroheader.postingdari',
                'penerimaangiroheader.diterimadari',
                'penerimaangiroheader.tgllunas',
                'penerimaangiroheader.modifiedby',
                'penerimaangiroheader.created_at',
                'penerimaangiroheader.updated_at',
                'agen.namaagen as agen_id',
                'c.nominal'
            )
            ->leftJoin(DB::raw("$temp as c with (readuncommitted)"), 'penerimaangiroheader.nobukti', 'c.nobukti')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'penerimaangiroheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'penerimaangiroheader.pelanggan_id', 'pelanggan.id');

        if (request()->tgldari && request()->tglsampai) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }

        if (request()->nobuktis != '') {
            $nobukti = request()->nobuktis;
            $query->whereNotIn('penerimaangiroheader.nobukti', function ($query) {
                $query->select(DB::raw('DISTINCT penerimaanheader.penerimaangiro_nobukti'))
                    ->from('penerimaanheader')
                    ->whereNotNull('penerimaanheader.penerimaangiro_nobukti')
                    ->where('penerimaanheader.penerimaangiro_nobukti', '!=', '');
            });
            $query->orWhereRaw("penerimaangiroheader.nobukti in ('$nobukti')");
            // dd('asdas');
        } else {
            $query->whereNotIn('penerimaangiroheader.nobukti', function ($query) {
                $query->select(DB::raw('DISTINCT penerimaanheader.penerimaangiro_nobukti'))
                    ->from('penerimaanheader')
                    ->whereNotNull('penerimaanheader.penerimaangiro_nobukti')
                    ->where('penerimaanheader.penerimaangiro_nobukti', '!=', '');
            });
        }

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $data = $query->get();

        return $data;
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
                            } else if ($filters['field'] == 'tgllunas') {
                                $query = $query->whereRaw("format(a.tgllunas,'dd-MM-yyyy') like '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti') {
                                $query = $query->whereRaw("format(a.tglbukti,'dd-MM-yyyy') like '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglapproval') {
                                $query = $query->whereRaw("format(a.tglapproval,'dd-MM-yyyy') like '%$filters[data]%'");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->whereRaw("format(a.nominal,'#,#0.00') like '%$filters[data]%'");
                            } else {
                                // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'statusapproval') {
                                    $query->orWhere('a.statusapprovaltext', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statuscetak') {
                                    $query->orWhere('a.statuscetaktext', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statuskirimberkas') {
                                    $query = $query->orWhere('a.statuskirimberkastext', '=', "$filters[data]");
                                } else if ($filters['field'] == 'created_at') {
                                    $query = $query->orWhereRaw("format(a.created_at,'dd-MM-yyyy HH:mm:ss') like '%$filters[data]%'");
                                } else if ($filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(a.updated_at,'dd-MM-yyyy HH:mm:ss') like '%$filters[data]%'");
                                } else if ($filters['field'] == 'tgllunas') {
                                    $query = $query->orWhereRaw("format(a.tgllunas,'dd-MM-yyyy') like '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglbukti') {
                                    $query = $query->orWhereRaw("format(a.tglbukti,'dd-MM-yyyy') like '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglapproval') {
                                    $query = $query->orWhereRaw("format(a.tglapproval,'dd-MM-yyyy') like '%$filters[data]%'");
                                } else if ($filters['field'] == 'nominal') {
                                    $query = $query->orWhereRaw("format(a.nominal,'#,#0.00') like '%$filters[data]%'");
                                } else {
                                    // $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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
        if (request()->cetak && request()->periode) {
            $query->where('penerimaangiroheader.statuscetak', request()->cetak)
                ->whereYear('penerimaangiroheader.tglbukti', '=', request()->year)
                ->whereMonth('penerimaangiroheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function processStore(array $data): PenerimaanGiroHeader
    {
        $bankid = $data['bank_id'];

        $group = 'PENERIMAAN GIRO BUKTI';
        $subGroup = 'PENERIMAAN GIRO BUKTI';
        $format = DB::table('parameter')->where('grp', $group)->where('subgrp', $subGroup)->first();

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $penerimaanGiroHeader = new PenerimaanGiroHeader();

        $penerimaanGiroHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $penerimaanGiroHeader->pelanggan_id = $data['pelanggan_id'] ?? 0;
        $penerimaanGiroHeader->agen_id = $data['agen_id'] ?? 0;
        $penerimaanGiroHeader->postingdari = $data['postingdari'] ?? 'ENTRY PENERIMAAN GIRO';
        $penerimaanGiroHeader->diterimadari = $data['diterimadari'];
        $penerimaanGiroHeader->tgllunas = date('Y-m-d', strtotime($data['tgllunas']));
        $penerimaanGiroHeader->cabang_id = 0;
        $penerimaanGiroHeader->statusapproval = $statusApproval->id;
        $penerimaanGiroHeader->userapproval = '';
        $penerimaanGiroHeader->tglapproval = '';
        $penerimaanGiroHeader->statusformat = $format->id;
        $penerimaanGiroHeader->statuscetak = $statusCetak->id;
        $penerimaanGiroHeader->modifiedby = auth('api')->user()->name;
        $penerimaanGiroHeader->info = html_entity_decode(request()->info);
        $penerimaanGiroHeader->statusformat = $data['statusformat'] ?? $format->id;
        $penerimaanGiroHeader->nobukti = (new RunningNumberService)->get($group, $subGroup, $penerimaanGiroHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$penerimaanGiroHeader->save()) {
            throw new \Exception("Error storing penerimaan giro header.");
        }

        $penerimaanGiroHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($penerimaanGiroHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? 'ENTRY PENERIMAAN GIRO HEADER',
            'idtrans' => $penerimaanGiroHeader->id,
            'nobuktitrans' => $penerimaanGiroHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $penerimaanGiroHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        $penerimaanDetails = [];
        $coakredit_detail = [];
        $coadebet_detail = [];
        $nominal_detail = [];
        $keterangan_detail = [];

        $coadebet = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->select('memo')->where('grp', 'JURNAL PENERIMAAN GIRO')->where('subgrp', 'DEBET')->first();
        $coakredit = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->select('memo')->where('grp', 'JURNAL PENERIMAAN GIRO')->where('subgrp', 'KREDIT')->first();

        $memodebet = json_decode($coadebet->memo, true);
        $memokredit = json_decode($coakredit->memo, true);
        $getCoa = Agen::from(DB::raw("agen with (readuncommitted)"))->where('id', $data['agen_id'])->first();

        for ($i = 0; $i < count($data['nominal']); $i++) {
            $penerimaanDetail = (new PenerimaanGiroDetail())->processStore($penerimaanGiroHeader, [
                'nowarkat' => $data['nowarkat'][$i],
                'tgljatuhtempo' => date('Y-m-d', strtotime($data['tgljatuhtempo'][$i])),
                'nominal' => $data['nominal'][$i],
                'coadebet' => $memodebet['JURNAL'],
                'coakredit' => $data['coakredit'][$i] ??  $getCoa->coapendapatan,
                'keterangan' => $data['keterangan_detail'][$i],
                'bank_id' => $data['bank_id'][$i],
                'invoice_nobukti' => $data['invoice_nobukti'][$i] ?? '-',
                'bankpelanggan_id' => $data['bankpelanggan_id'][$i] ?? '',
                'jenisbiaya' => $data['jenisbiaya'][$i] ?? '',
                'pelunasanpiutang_nobukti' => $data['pelunasanpiutang_nobukti'][$i] ?? '-',
                'bulanbeban' =>  date('Y-m-d', strtotime($data['bulanbeban'][$i] ?? '1900/1/1')),
                'modifiedby' => auth('api')->user()->name,
            ]);
            $penerimaanDetails[] = $penerimaanDetail->toArray();
            $coakredit_detail[] = $data['coakredit'][$i] ??  $getCoa->coapendapatan;
            $coadebet_detail[] = $memodebet['JURNAL'];
            $nominal_detail[] = $data['nominal'][$i];
            $keterangan_detail[] = $data['keterangan_detail'][$i];
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($penerimaanDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? 'ENTRY PENERIMAAN GIRO DETAIL',
            'idtrans' => $penerimaanGiroHeaderLogTrail->id,
            'nobuktitrans' => $penerimaanGiroHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $penerimaanDetails,
            'modifiedby' => auth('api')->user()->user
        ]);

        /*STORE JURNAL*/
        $jurnalRequest = [
            'tanpaprosesnobukti' => 1,
            'nobukti' => $penerimaanGiroHeader->nobukti,
            'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
            'postingdari' => $data['postingdari'] ?? "ENTRY PENERIMAAN GIRO",
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
        return $penerimaanGiroHeader;
    }

    public function processUpdate(PenerimaanGiroHeader $penerimaanGiroHeader, array $data): PenerimaanGiroHeader
    {
        $nobuktiOld = $penerimaanGiroHeader->nobukti;
        $getTgl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'EDIT TANGGAL BUKTI')->where('subgrp', 'PENERIMAAN GIRO')->first();
        if (trim($getTgl->text) == 'YA') {
            $group = 'PENERIMAAN GIRO BUKTI';
            $subGroup = 'PENERIMAAN GIRO BUKTI';

            $querycek = DB::table('penerimaangiroheader')->from(
                DB::raw("penerimaangiroheader a with (readuncommitted)")
            )
                ->select(
                    'a.nobukti'
                )
                ->where('a.id', $penerimaanGiroHeader->id)
                ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
                ->first();


            if (isset($querycek)) {
                $nobukti = $querycek->nobukti;
            } else {
                $nobukti = (new RunningNumberService)->get($group, $subGroup, $penerimaanGiroHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
            }
            $penerimaanGiroHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
            $penerimaanGiroHeader->nobukti = $nobukti;
        }

        $getCoa = Agen::from(DB::raw("agen with (readuncommitted)"))->where('id', $data['agen_id'])->first();
        $penerimaanGiroHeader->pelanggan_id = $data['pelanggan_id'] ?? '';
        $penerimaanGiroHeader->agen_id = $data['agen_id'] ?? '';
        $penerimaanGiroHeader->diterimadari = $data['diterimadari'];
        $penerimaanGiroHeader->tgllunas = date('Y-m-d', strtotime($data['tgllunas']));
        $penerimaanGiroHeader->modifiedby = auth('api')->user()->name;
        $penerimaanGiroHeader->editing_by = '';
        $penerimaanGiroHeader->editing_at = null;
        $penerimaanGiroHeader->info = html_entity_decode(request()->info);

        if (!$penerimaanGiroHeader->save()) {
            throw new \Exception("Error Update penerimaan giro header.");
        }

        $penerimaanGiroHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($penerimaanGiroHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? 'EDIT PENERIMAAN GIRO HEADER',
            'idtrans' => $penerimaanGiroHeader->id,
            'nobuktitrans' => $penerimaanGiroHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $penerimaanGiroHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        $penerimaanGiroDetail = PenerimaanGiroDetail::where('penerimaangiro_id', $penerimaanGiroHeader->id)->lockForUpdate()->delete();
        $coadebet = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->select('memo')->where('grp', 'JURNAL PENERIMAAN GIRO')->where('subgrp', 'DEBET')->first();
        $coakredit = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->select('memo')->where('grp', 'JURNAL PENERIMAAN GIRO')->where('subgrp', 'KREDIT')->first();

        $memodebet = json_decode($coadebet->memo, true);
        $memokredit = json_decode($coakredit->memo, true);

        $penerimaanGiroDetails = [];
        $coakredit_detail = [];
        $coadebet_detail = [];
        $nominal_detail = [];
        $keterangan_detail = [];

        for ($i = 0; $i < count($data['nominal']); $i++) {
            $penerimaanGiroDetail = (new PenerimaanGiroDetail())->processStore($penerimaanGiroHeader, [
                'nowarkat' => $data['nowarkat'][$i],
                'tgljatuhtempo' => date('Y-m-d', strtotime($data['tgljatuhtempo'][$i])),
                'nominal' => $data['nominal'][$i],
                'coadebet' => $memodebet['JURNAL'],
                'coakredit' => $data['coakredit'][$i] ?? $getCoa->coapendapatan,
                'keterangan' => $data['keterangan_detail'][$i],
                'bank_id' => $data['bank_id'][$i],
                'invoice_nobukti' => $data['invoice_nobukti'][$i] ?? '-',
                'bankpelanggan_id' => $data['bankpelanggan_id'][$i] ?? '',
                'jenisbiaya' => $data['jenisbiaya'][$i] ?? '',
                'pelunasanpiutang_nobukti' => $data['pelunasanpiutang_nobukti'][$i] ?? '-',
                'bulanbeban' =>  date('Y-m-d', strtotime($data['bulanbeban'][$i] ?? '1900/1/1')),
                'modifiedby' => auth('api')->user()->name,
            ]);
            $penerimaanGiroDetails[] = $penerimaanGiroDetail->toArray();
            $coakredit_detail[] = $data['coakredit'][$i] ?? $getCoa->coapendapatan;
            $coadebet_detail[] = $memodebet['JURNAL'];
            $nominal_detail[] = $data['nominal'][$i];
            $keterangan_detail[] = $data['keterangan_detail'][$i];
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($penerimaanGiroDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? 'EDIT PENERIMAAN GIRO DETAIL',
            'idtrans' => $penerimaanGiroHeaderLogTrail->id,
            'nobuktitrans' => $penerimaanGiroHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $penerimaanGiroDetails,
            'modifiedby' => auth('api')->user()->user
        ]);

        /*STORE JURNAL*/
        $jurnalRequest = [
            'tanpaprosesnobukti' => 1,
            'nobukti' => $penerimaanGiroHeader->nobukti,
            'tglbukti' => $penerimaanGiroHeader->tglbukti,
            'postingdari' => $data['postingdari'] ?? 'EDIT PENERIMAAN GIRO DETAIL',
            'coakredit_detail' => $coakredit_detail,
            'coadebet_detail' => $coadebet_detail,
            'nominal_detail' => $nominal_detail,
            'keterangan_detail' => $keterangan_detail
        ];
        $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $nobuktiOld)->first();
        $newJurnal = new JurnalUmumHeader();
        $newJurnal = $newJurnal->find($getJurnal->id);
        (new JurnalUmumHeader())->processUpdate($newJurnal, $jurnalRequest);

        return $penerimaanGiroHeader;
    }

    public function processDestroy($id, $postingDari = ''): PenerimaanGiroHeader
    {
        $penerimaanGiroDetails = PenerimaanGiroDetail::lockForUpdate()->where('penerimaangiro_id', $id)->get();

        $penerimaanGiroHeader = new PenerimaanGiroHeader();
        $penerimaanGiroHeader = $penerimaanGiroHeader->lockAndDestroy($id);

        $penerimaanGiroHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => $penerimaanGiroHeader->getTable(),
            'postingdari' => $postingDari,
            'idtrans' => $penerimaanGiroHeader->id,
            'nobuktitrans' => $penerimaanGiroHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $penerimaanGiroHeader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'PENERIMAANGIRODETAIL',
            'postingdari' => $postingDari,
            'idtrans' => $penerimaanGiroHeaderLogTrail['id'],
            'nobuktitrans' => $penerimaanGiroHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $penerimaanGiroDetails->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);
        $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $penerimaanGiroHeader->nobukti)->first();
        $jurnalumumHeader = (new JurnalUmumHeader())->processDestroy($getJurnal->id, $postingDari);
        return $penerimaanGiroHeader;
    }

    public function processApproval(array $data)
    {
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        for ($i = 0; $i < count($data['giroId']); $i++) {
            $penerimaanGiro = PenerimaanGiroHeader::find($data['giroId'][$i]);

            if ($penerimaanGiro->statusapproval == $statusApproval->id) {
                $penerimaanGiro->statusapproval = $statusNonApproval->id;
                $aksi = $statusNonApproval->text;
            } else {
                $penerimaanGiro->statusapproval = $statusApproval->id;
                $aksi = $statusApproval->text;
            }

            $penerimaanGiro->tglapproval = date('Y-m-d H:i:s');
            $penerimaanGiro->userapproval = auth('api')->user()->name;
            $penerimaanGiro->info = html_entity_decode(request()->info);

            if (!$penerimaanGiro->save()) {
                throw new \Exception('Error Un/approval penerimaan giro.');
            }

            (new LogTrail())->processStore([
                'namatabel' => strtoupper($penerimaanGiro->getTable()),
                'postingdari' => "UN/APPROVAL PENERIMAAN GIRO",
                'idtrans' => $penerimaanGiro->id,
                'nobuktitrans' => $penerimaanGiro->nobukti,
                'aksi' => $aksi,
                'datajson' => $penerimaanGiro->toArray(),
                'modifiedby' => auth('api')->user()->name,
            ]);
            $result[] = $penerimaanGiro;
        }

        return $result;
    }

    public function getExport($id)
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($this->table)->from(DB::raw("penerimaangiroheader with (readuncommitted)"))
            ->select(
                'penerimaangiroheader.id',
                'penerimaangiroheader.nobukti',
                'penerimaangiroheader.tglbukti',
                'cabang.namacabang as cabang',
                'agen.namaagen as agen_id',
                'penerimaangiroheader.diterimadari',
                'detail.nowarkat as nowarkat',
                'bank.namabank as bank',
                'penerimaangiroheader.jumlahcetak',
                'statuscetak.memo as statuscetak',
                'statuscetak.id as  statuscetak_id',
                DB::raw("'Bukti Penerimaan Giro' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")

            )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("penerimaangirodetail as detail with (readuncommitted)"), "penerimaangiroheader.id",  $this->table . '.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'detail.bank_id', 'bank.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'penerimaangiroheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("cabang with (readuncommitted)"), 'penerimaangiroheader.cabang_id', 'cabang.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'penerimaangiroheader.agen_id', 'agen.id');
        $data = $query->first();
        return $data;
    }

    public function editingAt($id, $btn)
    {
        $penerimaanGiro = PenerimaanGiroHeader::find($id);
        $oldUser = $penerimaanGiro->editing_by;
        if ($btn == 'EDIT') {
            $penerimaanGiro->editing_by = auth('api')->user()->name;
            $penerimaanGiro->editing_at = date('Y-m-d H:i:s');
        } else {
            if ($penerimaanGiro->editing_by == auth('api')->user()->name) {
                $penerimaanGiro->editing_by = '';
                $penerimaanGiro->editing_at = null;
            }
        }
        if (!$penerimaanGiro->save()) {
            throw new \Exception("Error Update penerimaan giro header.");
        }

        $penerimaanGiro->oldeditingby = $oldUser;
        return $penerimaanGiro;
    }
}
