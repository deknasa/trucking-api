<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\RunningNumberService;

class AbsensiSupirApprovalHeader extends MyModel
{
    use HasFactory;

    protected $table = 'absensisupirapprovalheader';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    public function get()
    {
        $this->setRequestParameters();

        $petik ='"';
        $url = config('app.url_fe').'pengeluaranstokheader';
        $absensisupirproses = DB::table("absensisupirapprovalproses")
            ->from(DB::raw("absensisupirapprovalproses with (readuncommitted)"))
            ->select(
                DB::raw("
                absensisupirapprovalproses.absensisupirapproval_id,
                absensisupirapprovalproses.nobukti,
                STRING_AGG(absensisupirapprovalproses.pengeluaran_nobukti, ', ') as pengeluaran_nobukti,
                STRING_AGG('<a href=$petik".$url."?tgldari='+(format(absensisupirapprovalheader.tglbukti,'yyyy-MM')+'-1')+'&tglsampai='+(format(absensisupirapprovalheader.tglbukti,'yyyy-MM')+'-31')+'&nobukti='+absensisupirapprovalproses.pengeluaran_nobukti+'$petik class=$petik link-color $petik target=$petik _blank $petik title=$petik '+absensisupirapprovalproses.pengeluaran_nobukti+' $petik>'+absensisupirapprovalproses.pengeluaran_nobukti+'</a>', ',') as url"
                ))
            ->join(DB::raw("absensisupirapprovalheader with (readuncommitted)"),'absensisupirapprovalproses.absensisupirapproval_id','absensisupirapprovalheader.id')    
            ->groupBy("absensisupirapprovalproses.absensisupirapproval_id","absensisupirapprovalproses.nobukti");

        $tempurl = '##tempurl' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempurl, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->longText('pengeluaran_nobukti')->nullable();
            $table->longText('url')->nullable();
        }); 
        DB::table($tempurl)->insertUsing(['id','nobukti', 'pengeluaran_nobukti','url'], $absensisupirproses);
                
        $query = DB::table($this->table)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            'absensisupirapprovalheader.id',
            'absensisupirapprovalheader.nobukti',
            'absensisupirapprovalheader.tglbukti',
            'absensisupirapprovalheader.absensisupir_nobukti',
            // 'absensisupirapprovalheader.keterangan',
            'statusapproval.memo as statusapproval',
            db::raw("(case when year(isnull(absensisupirapprovalheader.tglapproval,'1900/1/1'))=1900 then null else absensisupirapprovalheader.tglapproval end) as tglapproval"),
            'absensisupirapprovalheader.userapproval',
            'statusformat.memo as statusformat',
            'absensisupirapprovalheader.pengeluaran_nobukti',
            'akunpusat.keterangancoa as coakaskeluar',
            'absensisupirapprovalheader.postingdari',
            db::raw("(case when year(isnull(absensisupirapprovalheader.tglkaskeluar,'1900/1/1'))=1900 then null else absensisupirapprovalheader.tglkaskeluar end) as tglkaskeluar"),
            'statuscetak.memo as statuscetak',
            db::raw("(case when year(isnull(absensisupirapprovalheader.tglbukacetak,'1900/1/1'))=1900 then null else absensisupirapprovalheader.tglbukacetak end) as tglbukacetak"),
            'absensisupirapprovalheader.userbukacetak',
            'absensisupirapprovalheader.jumlahcetak',
            'proses.pengeluaran_nobukti as pengeluaran',
            'proses.url as pengeluaran_url',
            'absensisupirapprovalheader.modifiedby',
            'absensisupirapprovalheader.updated_at',
            'absensisupirapprovalheader.created_at',
            db::raw("cast((format(pengeluaran.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpengeluaranheader"),
            db::raw("cast(cast(format((cast((format(pengeluaran.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpengeluaranheader"),
            db::raw("cast((format(absensisupir.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderabsensisupirheader"),
            db::raw("cast(cast(format((cast((format(absensisupir.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderabsensisupirheader"),

        )

            ->whereBetween('absensisupirapprovalheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'absensisupirapprovalheader.coakaskeluar', 'akunpusat.coa')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'absensisupirapprovalheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'absensisupirapprovalheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("pengeluaranheader as pengeluaran with (readuncommitted)"), 'absensisupirapprovalheader.pengeluaran_nobukti', '=', 'pengeluaran.nobukti')
            ->leftJoin(DB::raw("$tempurl as proses with (readuncommitted)"), 'absensisupirapprovalheader.id', '=', 'proses.id')
            ->leftJoin(DB::raw("absensisupirheader as absensisupir with (readuncommitted)"), 'absensisupirapprovalheader.absensisupir_nobukti', '=', 'absensisupir.nobukti')
            ->leftJoin(DB::raw("parameter as statusformat with (readuncommitted)"), 'absensisupirapprovalheader.statusformat', 'statusformat.id');




        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function cekvalidasiaksi($id)
    {
        $error = new Error();
        $keteranganerror = $error->cekKeteranganError('SAPP') ?? '';
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';


        $get = DB::table("absensisupirapprovalheader")->from(DB::raw("absensisupirapprovalheader with (readuncommitted)"))->where('id', $id)->first();
        // $nobukti=$get->nobukti ?? '';
        $absensiSupir = DB::table('absensisupirapprovalheader')
            ->from(
                DB::raw("absensisupirapprovalheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.pengeluaran_nobukti'
            )
            ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'a.pengeluaran_nobukti', 'b.nobukti')
            ->where('a.nobukti', '=', $get->nobukti)
            ->first();
        if (isset($absensiSupir)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $absensiSupir->pengeluaran_nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Approval Jurnal ' . $absensiSupir->pengeluaran_nobukti,
                'kodeerror' => 'SAPP'
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

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->nullable();
            $table->string('absensisupir_nobukti', 50)->nullable();
            // $table->longText('keterangan')->nullable();
            $table->string('statusapproval', 1000)->nullable();
            $table->dateTime('tglapproval')->nullable();
            $table->string('userapproval', 200)->nullable();
            $table->string('statusformat', 1000)->nullable();
            $table->string('pengeluaran_nobukti', 50)->nullable();
            $table->string('coakaskeluar')->nullable();
            $table->string('postingdari', 50)->nullable();
            $table->date('tglkaskeluar')->nullable();
            $table->string('statuscetak', 1000)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->string('modifiedby', 1000)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        if ((date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tgldariheader))) || (date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tglsampaiheader)))) {
            request()->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            request()->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
        }

        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);

        $query = $this->sort($query);
        $models = $this->filter($query);
        $models = $query
            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        DB::table($temp)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'absensisupir_nobukti',
            // 'keterangan',
            'statusapproval',
            'tglapproval',
            'userapproval',
            'statusformat',
            'pengeluaran_nobukti',
            'coakaskeluar',
            'postingdari',
            'tglkaskeluar',
            'statuscetak',
            'userbukacetak',
            'tglbukacetak',
            'jumlahcetak',
            'modifiedby',
            'created_at', 'updated_at'
        ], $models);

        return $temp;
    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            DB::raw(
                "$this->table.id,
                $this->table.nobukti,
                $this->table.tglbukti,
                $this->table.absensisupir_nobukti,
                'statusapproval.text as statusapproval',
                $this->table.tglapproval,
                $this->table.userapproval,
                'statusformat.text as statusformat',
                $this->table.pengeluaran_nobukti,
                'akunpusat.keterangancoa as coakaskeluar',
                $this->table.postingdari,
                $this->table.tglkaskeluar,
                'statuscetak.text as statuscetak',
                $this->table . userbukacetak,
                $this->table . tglbukacetak,
                $this->table . jumlahcetak,
                $this->table.modifiedby,
                $this->table.created_at,
                $this->table.updated_at"
            )
        )
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'absensisupirapprovalheader.coakaskeluar', 'akunpusat.coa')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'absensisupirapprovalheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'absensisupirapprovalheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("parameter as statusformat with (readuncommitted)"), 'absensisupirapprovalheader.statusformat', 'statusformat.id');
    }

    public function getApproval($nobukti)
    {

        $statusabsensi = db::table("parameter")->from(db::raw("parameter"))->select('id')
            ->where('grp', 'STATUS ABSENSI SUPIR')
            ->where('subgrp', 'STATUS ABSENSI SUPIR')
            ->where('text', 'ABSENSI SUPIR')
            ->first()->id ?? 0;

        $query = DB::table('absensisupirdetail')->from(
            DB::raw("absensisupirdetail with (readuncommitted)")
        )
            ->select(
                'absensisupirdetail.keterangan as keterangan_detail',
                'absensisupirdetail.jam',
                'absensisupirdetail.uangjalan',
                'absensisupirdetail.absensi_id',
                'absensisupirdetail.id',
                'trado.kodetrado as trado',
                'supirutama.namasupir as supir',
                'trado.id as trado_id',
                'supirutama.id as supir_id',
                'absensisupirheader.kasgantung_nobukti',
            )
            ->leftJoin(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirdetail.absensi_id', 'absensisupirheader.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'absensisupirdetail.trado_id', 'trado.id')
            ->leftJoin(DB::raw("supir as supirutama with (readuncommitted)"), 'absensisupirdetail.supir_id', 'supirutama.id')
            ->whereRaw(" EXISTS (
            SELECT absensisupirapprovalheader.absensisupir_nobukti
    FROM absensisupirdetail    with (readuncommitted)        
    left join absensisupirapprovalheader with (readuncommitted) on absensisupirapprovalheader.absensisupir_nobukti= absensisupirdetail.nobukti
    WHERE absensisupirapprovalheader.absensisupir_nobukti = absensisupirheader.nobukti
          )")
            ->where('absensisupirdetail.nobukti', $nobukti)
            ->whereRaw('isnull(absensisupirdetail.uangjalan,0)<>0')
            ->where('trado.statusabsensisupir', $statusabsensi);

        $data = $query->get();

        $this->totalUangJalan = $query->sum('absensisupirdetail.uangjalan');

        return $data;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'coakaskeluar') {
            return $query->orderBy('akunpusat.keterangancoa', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statusapproval') {
                                $query = $query->where('statusapproval.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statuscetak') {
                                $query = $query->where('statuscetak.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'coakaskeluar') {
                                $query = $query->where('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglkaskeluar' || $filters['field'] == 'tglbukacetak' || $filters['field'] == 'tglapproval') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    }
                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'statusapproval') {
                                    $query = $query->orWhere('statusapproval.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statuscetak') {
                                    $query = $query->orWhere('statuscetak.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'coakaskeluar') {
                                    $query = $query->orWhere('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglkaskeluar' || $filters['field'] == 'tglbukacetak' || $filters['field'] == 'tglapproval') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else {
                                    $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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

    public function findAll($id)
    {
        $this->setRequestParameters();

        $query = DB::table($this->table);
        $query = $this->selectColumns($query)
            ->leftJoin(DB::raw("pengeluaranheader with (readuncommitted)"), 'absensisupirapprovalheader.pengeluaran_nobukti', 'pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirapprovalheader.absensisupir_nobukti', 'absensisupirheader.nobukti')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'absensisupirapprovalheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'absensisupirapprovalheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("parameter as statusformat with (readuncommitted)"), 'absensisupirapprovalheader.statusformat', 'statusformat.id');
        $data = $query->where("$this->table.id", $id)->first();
        return $data;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function getExport($id)
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();


        $query = DB::table($this->table)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            'absensisupirapprovalheader.id',
            'absensisupirapprovalheader.nobukti',
            'absensisupirapprovalheader.tglbukti',
            'absensisupirapprovalheader.absensisupir_nobukti',
            'absensisupirapprovalheader.keterangan',
            'statusapproval.memo as statusapproval',
            db::raw("(case when year(isnull(absensisupirapprovalheader.tglapproval,'1900/1/1'))=1900 then null else absensisupirapprovalheader.tglapproval end) as tglapproval"),
            'absensisupirapprovalheader.userapproval',
            'statusformat.memo as statusformat',
            'absensisupirapprovalheader.pengeluaran_nobukti',
            'akunpusat.keterangancoa as coakaskeluar',
            'absensisupirapprovalheader.postingdari',
            db::raw(' CASE
            WHEN absensisupirapprovalheader.jumlahcetak = 0 THEN NULL
            ELSE absensisupirapprovalheader.jumlahcetak
          END AS jumlahcetak'),
            db::raw("(case when year(isnull(absensisupirapprovalheader.tglkaskeluar,'1900/1/1'))=1900 then null else absensisupirapprovalheader.tglkaskeluar end) as tglkaskeluar"),
            'statuscetak.memo as statuscetak',
            'statuscetak.id as  statuscetak_id',
            db::raw("(case when year(isnull(absensisupirapprovalheader.tglbukacetak,'1900/1/1'))=1900 then null else absensisupirapprovalheader.tglbukacetak end) as tglbukacetak"),
            DB::raw("'Laporan Absensi Supir Posting' as judulLaporan"),
            DB::raw("'" . $getJudul->text . "' as judul"),
            DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
        )

            //->whereBetween('tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), 'absensisupirapprovalheader.coakaskeluar', 'akunpusat.coa')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'absensisupirapprovalheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'absensisupirapprovalheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("parameter as statusformat with (readuncommitted)"), 'absensisupirapprovalheader.statusformat', 'statusformat.id')
            ->where("$this->table.id", $id);

        $data = $query->first();
        return $data;
    }

    public function printValidation($id)
    {

        $statusCetak = DB::table('absensisupirapprovalheader')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        $query = DB::table('absensisupirapprovalheader')->from(DB::raw("absensisupirapprovalheader with (readuncommitted)"))
            ->select('statuscetak')
            ->where('id', $id)
            ->first();

        //jika belum cetak return true
        if ($query->statuscetak != $statusCetak->id) return true;
        return false;
    }

    public function processStore(array $data): AbsensiSupirApprovalHeader
    {
        $group = 'ABSENSI SUPIR APPROVAL BUKTI';
        $subGroup = 'ABSENSI SUPIR APPROVAL BUKTI';
        $format = DB::table('parameter')->where('grp', $group)->where('subgrp', $subGroup)->first();
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $coaKreditApproval = DB::table('parameter')->where('grp', 'JURNAL APPROVAL ABSENSI SUPIR')->where('subgrp', 'KREDIT')->first();
        $memoKredit = json_decode($coaKreditApproval->memo, true);

        $coaDebetApproval = DB::table('parameter')->where('grp', 'JURNAL APPROVAL ABSENSI SUPIR')->where('subgrp', 'DEBET')->first();
        $memoDebet = json_decode($coaDebetApproval->memo, true);


        $absensiSupirApprovalHeader = new AbsensiSupirApprovalHeader();
        $absensiSupirApprovalHeader->tglbukti =  date('Y-m-d', strtotime($data['tglbukti']));
        $absensiSupirApprovalHeader->absensisupir_nobukti =  $data['absensisupir_nobukti'];
        $absensiSupirApprovalHeader->statusapproval = $statusApproval->id;
        $absensiSupirApprovalHeader->statusformat =  $format->id;
        // $absensiSupirApprovalHeader->pengeluaran_nobukti = $data['pengeluaran_nobukti'] ?? '0';
        $absensiSupirApprovalHeader->coakaskeluar = $memoKredit['JURNAL'];
        $absensiSupirApprovalHeader->tglkaskeluar = $data['tglkaskeluar'] ?? '1900/1/1';
        $absensiSupirApprovalHeader->postingdari =  "ABSENSI SUPIR APPROVAL";
        $absensiSupirApprovalHeader->statuscetak = $statusCetak->id ?? 0;
        $absensiSupirApprovalHeader->modifiedby =  auth('api')->user()->name;
        $absensiSupirApprovalHeader->info = html_entity_decode(request()->info);
        $absensiSupirApprovalHeader->nobukti = (new RunningNumberService)->get($group, $subGroup, $absensiSupirApprovalHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$absensiSupirApprovalHeader->save()) {
            throw new \Exception("Error storing absensi Supir Approval Header.");
        }
        $absensiTangki = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'ABSENSI TANGKI')->where('subgrp', 'ABSENSI TANGKI')->first();


        for ($i = 0; $i < count($data['trado_id']); $i++) {
            $absensiSupirApprovalDetail = AbsensiSupirApprovalDetail::processStore($absensiSupirApprovalHeader, [
                "absensisupirapproval_id" => $absensiSupirApprovalHeader->id,
                "nobukti" => $absensiSupirApprovalHeader->nobukti,
                "trado_id" => $data['trado_id'][$i],
                "supir_id" => $data['supir_id'][$i] ?? 0,
                "statusjeniskendaraan" => $data['statusjeniskendaraan'][$i],
                "modifiedby" => auth('api')->user()->name
            ]);
            $absensiSupirApprovalDetails[] = $absensiSupirApprovalDetail->toArray();
        }

        if ($absensiTangki->text == 'YA') {
            // dd('Lorem ipsum, dolor sit amet consectetur adipisicing elit. Quod eligendi optio autem inventore velit enim tempore hic sapiente maxime esse, eaque delectus rerum voluptas. Harum similique cupiditate tempora corrupti in.');
            $absensiApprovalPorsess =[

            ];
            $absensiSupirProses = (new AbsensiSupirApprovalProses())->processStore($absensiSupirApprovalHeader,$absensiApprovalPorsess);
        }else{
            $bank = DB::table('bank')->where('coa', $memoKredit['JURNAL'])->first();
            $kasGantungDetail = DB::table('kasgantungdetail')->where('nobukti', $data['kasgantung_nobukti'])->get();
            foreach ($kasGantungDetail as $detail) {
                $nominalKasGantung[] = $detail->nominal;
                $keteranganKasGantung[] = $detail->keterangan;
                $coakredit[] = $memoKredit['JURNAL'];
                $coadebet[] = $memoDebet['JURNAL'];
            }
            $kasGantungRequest = [
                "tglbukti" => $data['tglbukti'],
                "penerima" => '',
                "bank_id" => $bank->id,
                "postingdari" => 'ENTRY ABSENSI SUPIR APPROVAL',
                "from" => 'AbsensiSupirApprovalHeader',
    
                "coakredit" => $coakredit,
                "coadebet" => $coadebet,
                "nominal" => $nominalKasGantung,
                "keterangan_detail" => $keteranganKasGantung,
            ];
    
            $kasGantung = KasGantungHeader::where('nobukti', $data['kasgantung_nobukti'])->lockForUpdate()->first();
            $kasGantungHeader = (new KasGantungHeader())->processUpdate($kasGantung, $kasGantungRequest);
    
            $absensiSupirApprovalHeader->pengeluaran_nobukti = $kasGantung->pengeluaran_nobukti;
    
            $absensiSupirApprovalHeader->tglkaskeluar = $kasGantung->tglkaskeluar;
            $absensiSupirApprovalHeader->save();
        }
        $absensiSupirApprovalHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($absensiSupirApprovalHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('EDIT ABSENSI SUPIR Header '),
            'idtrans' => $absensiSupirApprovalHeader->id,
            'nobuktitrans' => $absensiSupirApprovalHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $absensiSupirApprovalHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        $absensiSupirApprovalDetailLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($absensiSupirApprovalDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('EDIT ABSENSI SUPIR detail '),
            'idtrans' => $absensiSupirApprovalHeaderLogTrail->id,
            'nobuktitrans' => $absensiSupirApprovalHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $absensiSupirApprovalDetails,
            'modifiedby' => auth('api')->user()->user
        ]);
        return $absensiSupirApprovalHeader;
    }
    public function processUpdate(AbsensiSupirApprovalHeader $absensiSupirApprovalHeader, array $data): AbsensiSupirApprovalHeader
    {
        $group = 'ABSENSI SUPIR APPROVAL BUKTI';
        $subGroup = 'ABSENSI SUPIR APPROVAL BUKTI';
        $format = DB::table('parameter')->where('grp', $group)->where('subgrp', $subGroup)->first();
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $coaKreditApproval = DB::table('parameter')->where('grp', 'JURNAL APPROVAL ABSENSI SUPIR')->where('subgrp', 'KREDIT')->first();
        $memoKredit = json_decode($coaKreditApproval->memo, true);

        $coaDebetApproval = DB::table('parameter')->where('grp', 'JURNAL APPROVAL ABSENSI SUPIR')->where('subgrp', 'DEBET')->first();
        $memoDebet = json_decode($coaDebetApproval->memo, true);



        $absensiSupirApprovalHeader->tglbukti =  date('Y-m-d', strtotime($data['tglbukti']));
        $absensiSupirApprovalHeader->absensisupir_nobukti =  $data['absensisupir_nobukti'];
        $absensiSupirApprovalHeader->statusapproval = $statusApproval->id;
        $absensiSupirApprovalHeader->statusformat =  $format->id;
        // $absensiSupirApprovalHeader->pengeluaran_nobukti = $data['pengeluaran_nobukti'] ?? '0';
        $absensiSupirApprovalHeader->coakaskeluar = $memoKredit['JURNAL'];
        $absensiSupirApprovalHeader->tglkaskeluar = $data['tglkaskeluar'] ?? '1900/1/1';
        $absensiSupirApprovalHeader->postingdari =  "ABSENSI SUPIR APPROVAL";
        $absensiSupirApprovalHeader->statuscetak = $statusCetak->id ?? 0;
        $absensiSupirApprovalHeader->modifiedby =  auth('api')->user()->name;
        $absensiSupirApprovalHeader->editing_by = '';
        $absensiSupirApprovalHeader->editing_at = null;           
        $absensiSupirApprovalHeader->info = html_entity_decode(request()->info);

        if (!$absensiSupirApprovalHeader->save()) {
            throw new \Exception("Error storing absensi Supir Approval Header.");
        }


        for ($i = 0; $i < count($data['trado_id']); $i++) {
            $absensiSupirApprovalDetail = AbsensiSupirApprovalDetail::processStore($absensiSupirApprovalHeader, [
                "absensisupirapproval_id" => $absensiSupirApprovalHeader->id,
                "nobukti" => $absensiSupirApprovalHeader->nobukti,
                "trado_id" => $data['trado_id'][$i],
                "supir_id" => $data['supir_id'][$i] ?? 0,
                "modifiedby" => auth('api')->user()->name
            ]);
            $absensiSupirApprovalDetails[] = $absensiSupirApprovalDetail->toArray();
        }

        $bank = DB::table('bank')->where('coa', $memoKredit['JURNAL'])->first();
        $kasGantungDetail = DB::table('kasgantungdetail')->where('nobukti', $data['kasgantung_nobukti'])->get();
        foreach ($kasGantungDetail as $detail) {
            $nominalKasGantung[] = $detail->nominal;
            $keteranganKasGantung[] = $detail->keterangan;
            $coakredit[] = $memoKredit['JURNAL'];
            $coadebet[] = $memoDebet['JURNAL'];
        }
        $kasGantungRequest = [
            "tglbukti" => $data['tglbukti'],
            "penerima" => '',
            "bank_id" => $bank->id,
            "postingdari" => 'ENTRY ABSENSI SUPIR APPROVAL',

            "coakredit" => $coakredit,
            "coadebet" => $coadebet,
            "nominal" => $nominalKasGantung,
            "keterangan_detail" => $keteranganKasGantung,
        ];

        $kasGantung = KasGantungHeader::where('nobukti', $data['kasgantung_nobukti'])->lockForUpdate()->first();
        $kasGantungHeader = (new KasGantungHeader())->processUpdate($kasGantung, $kasGantungRequest);

        $absensiSupirApprovalHeader->pengeluaran_nobukti = $kasGantung->pengeluaran_nobukti;
        $absensiSupirApprovalHeader->tglkaskeluar = $kasGantung->tglkaskeluar;
        $absensiSupirApprovalHeader->save();
        $absensiSupirApprovalHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($absensiSupirApprovalHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('EDIT ABSENSI SUPIR Header '),
            'idtrans' => $absensiSupirApprovalHeader->id,
            'nobuktitrans' => $absensiSupirApprovalHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $absensiSupirApprovalHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        $absensiSupirApprovalDetailLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($absensiSupirApprovalDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('EDIT ABSENSI SUPIR detail '),
            'idtrans' => $absensiSupirApprovalHeaderLogTrail->id,
            'nobuktitrans' => $absensiSupirApprovalHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $absensiSupirApprovalDetails,
            'modifiedby' => auth('api')->user()->user
        ]);
        return $absensiSupirApprovalHeader;
    }

    public function processDestroy($id, $prosesDari = ""): AbsensiSupirApprovalHeader
    {

        $absensiSupirApprovalHeader = AbsensiSupirApprovalHeader::findOrFail($id);
        $dataHeader =  $absensiSupirApprovalHeader->toArray();
        $absensiSupirApprovalDetail = AbsensiSupirApprovalDetail::where('absensisupirapproval_id', '=', $absensiSupirApprovalHeader->id)->get();
        $dataDetail = $absensiSupirApprovalDetail->toArray();

        $getDetail = AbsensiSupirApprovalDetail::lockForUpdate()->where('absensisupirapproval_id', $id)->get();

        $absensiTangki = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'ABSENSI TANGKI')->where('subgrp', 'ABSENSI TANGKI')->first();
        if ($absensiTangki->text == 'YA') {
            (new AbsensiSupirApprovalProses())->processDestroy($absensiSupirApprovalHeader, ($prosesDari == "") ? $prosesDari : strtoupper('DELETE Absensi Supir Approval'));
        }else {
            $pengeluaran = $absensiSupirApprovalHeader->pengeluaran_nobukti;
            $kasGantung = KasGantungHeader::where('pengeluaran_nobukti', $pengeluaran)->lockForUpdate()->first();
    
            $kasGantung->pengeluaran_nobukti = '';
            $kasGantung->coakaskeluar = '';
            $kasGantung->save();
    
    
            $pengeluaran = PengeluaranHeader::where('nobukti', $absensiSupirApprovalHeader->pengeluaran_nobukti)->lockForUpdate()->first();
            (new PengeluaranHeader())->processDestroy($pengeluaran->id, 'Absensi Supir Approval');
        }
            

        $absensiSupirApprovalHeader = new AbsensiSupirApprovalHeader();
        $absensiSupirApprovalHeader = $absensiSupirApprovalHeader->lockAndDestroy($id);

        $absensiSupirApprovalLogTrail = (new LogTrail())->processStore([
            'namatabel' => $absensiSupirApprovalHeader->getTable(),
            'postingdari' => strtoupper('DELETE absensi Supir Approval Header'),
            'idtrans' => $absensiSupirApprovalHeader->id,
            'nobuktitrans' => $absensiSupirApprovalHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $dataHeader,
            'modifiedby' => auth('api')->user()->name
        ]);


        (new LogTrail())->processStore([
            'namatabel' => 'absensiSupirApprovaldetail',
            'postingdari' => strtoupper('DELETE absensi Supir Approval detail'),
            'idtrans' => $absensiSupirApprovalLogTrail['id'],
            'nobuktitrans' => $absensiSupirApprovalHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $dataDetail,
            'modifiedby' => auth('api')->user()->name
        ]);

        return $absensiSupirApprovalHeader;
    }
}
