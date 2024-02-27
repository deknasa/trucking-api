<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\RunningNumberService;

class AbsensiSupirHeader extends MyModel
{
    use HasFactory;

    protected $table = 'absensisupirheader';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'tgl' => 'date:d-m-Y',
    ];

    public function absensiSupirDetail()
    {
        return $this->hasMany(AbsensiSupirDetail::class, 'absensi_id');
    }


    public function cekvalidasiaksi($nobukti)
    {
        $absensiSupir = DB::table('absensisupirapprovalheader')
            ->from(
                DB::raw("absensisupirapprovalheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.absensisupir_nobukti'
            )
            ->where('a.absensisupir_nobukti', '=', $nobukti)
            ->first();
        if (isset($absensiSupir)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Absensi Supir Posting ' . $absensiSupir->nobukti,
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

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("absensisupirheader with (readuncommitted)"))
            ->select(
                'absensisupirheader.id',
                'absensisupirheader.nobukti',
                'absensisupirheader.tglbukti',
                'absensisupirheader.kasgantung_nobukti',
                DB::raw("(case when absensisupirheader.nominal IS NULL then 0 else absensisupirheader.nominal end) as nominal"),
                DB::raw('(case when (year(absensisupirheader.tglbukacetak) <= 2000) then null else absensisupirheader.tglbukacetak end ) as tglbukacetak'),
                DB::raw('(case when (year(absensisupirheader.tglapprovaleditabsensi) <= 2000) then null else absensisupirheader.tglapprovaleditabsensi end ) as tglapprovaleditabsensi'),
                'statuscetak.memo as statuscetak',
                'statusapprovaleditabsensi.memo as statusapprovaleditabsensi',
                'absensisupirheader.userapprovaleditabsensi',
                'absensisupirheader.userbukacetak',
                'absensisupirheader.jumlahcetak',
                'absensisupirheader.modifiedby',
                'absensisupirheader.created_at',
                'absensisupirheader.updated_at',
                db::raw("cast((format(kasgantungheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderkasgantungheader"),
                db::raw("cast(cast(format((cast((format(kasgantungheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderkasgantungheader"),


            )
            // request()->tgldari ?? date('Y-m-d',strtotime('today'))
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'absensisupirheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("kasgantungheader with (readuncommitted)"), 'absensisupirheader.kasgantung_nobukti', '=', 'kasgantungheader.nobukti')
            ->leftJoin(DB::raw("parameter as statusapprovaleditabsensi with (readuncommitted)"), 'absensisupirheader.statusapprovaleditabsensi', 'statusapprovaleditabsensi.id');
        if (request()->tgldari) {
            $query->whereBetween('absensisupirheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }

        $proses=request()->proses ?? '' ;

        if ($proses=='APPROVALSUPIR') {
            $tempbelumlengkap = '##tempbelumlengkap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

            Schema::create($tempbelumlengkap, function ($table) {
                $table->string('nobukti', 1000)->nullable();
            });

                $querybelumlengkap=db::table("absensisupirdetail")->from(db::raw("absensisupirdetail a with (readuncommitted)"))
                ->select(
                    'a.nobukti'
                )
                ->join(db::raw("absensisupirheader b with (readuncommitted)"),'a.nobukti','b.nobukti')
                ->leftjoin(DB::raw("suratpengantar as c with(readuncommitted)"), function ($join) {
                    $join->on('a.supir_id', '=', 'c.supir_id');
                    $join->on('a.trado_id', '=', 'c.trado_id');
                    $join->on('b.tglbukti', '=', 'c.tglbukti');
                })     
                ->whereBetween('b.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
                ->whereRaw("isnull(a.absen_id,0)=0")
                ->whereRaw("isnull(c.nobukti,'')=''")
                ->groupBy('a.nobukti');

                DB::table($tempbelumlengkap)->insertUsing([
                    'nobukti',
                ], $querybelumlengkap);

                // dd(db::table($tempbelumlengkap)->get());
                $query->leftjoin(db::raw($tempbelumlengkap . " as tempbelumlengkap"),'absensisupirheader.nobukti','tempbelumlengkap.nobukti')
                      ->whereraw("isnull(tempbelumlengkap.nobukti,'')=''");

        }


        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);
        $data = $query->get();
        return $data;
    }

    public function findAll($id)
    {
        $query = DB::table('absensisupirheader')->from(DB::raw("absensisupirheader with (readuncommitted)"))
            ->select(
                'absensisupirheader.id',
                'absensisupirheader.nobukti',
                'absensisupirheader.kasgantung_nobukti',
                'absensisupirheader.tglbukti',
                'absensisupirheader.tglbukacetak',
                'absensisupirheader.statuscetak',
                'absensisupirheader.statusapprovaleditabsensi',
                'absensisupirheader.userbukacetak',
                'absensisupirheader.jumlahcetak',
            )
            ->where('id', $id);
        $data = $query->first();

        return $data;
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
            $this->table.kasgantung_nobukti,
            $this->table.nominal,
            'statuscetak.text as statuscetak',
            $this->table.userbukacetak,
            $this->table.tglbukacetak,
            'statusapprovaleditabsensi.text as statusapprovaleditabsensi',
            $this->table.userapprovaleditabsensi,
            $this->table.tglapprovaleditabsensi,
            $this->table.jumlahcetak,
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )
        )
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'absensisupirheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("parameter as statusapprovaleditabsensi with (readuncommitted)"), 'absensisupirheader.statusapprovaleditabsensi', 'statusapprovaleditabsensi.id');
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->string('tglbukti', 1000)->nullable();
            $table->string('kasgantung_nobukti', 1000)->nullable();
            $table->string('nominal', 1000)->nullable();
            $table->string('statuscetak', 1000)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->string('statusapprovaleditabsensi', 1000)->nullable();
            $table->string('userapprovaleditabsensi', 50)->nullable();
            $table->date('tglapprovaleditabsensi')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->string('modifiedby', 1000)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $query = DB::table($this->table)->from(DB::raw("absensisupirheader with (readuncommitted)"))
            ->select(
                'absensisupirheader.id',
                'absensisupirheader.nobukti',
                'absensisupirheader.tglbukti',
                'absensisupirheader.kasgantung_nobukti',
                DB::raw("(case when absensisupirheader.nominal IS NULL then 0 else absensisupirheader.nominal end) as nominal"),
                'statuscetak.memo as statuscetak',
                'absensisupirheader.userbukacetak',
                DB::raw('(case when (year(absensisupirheader.tglbukacetak) <= 2000) then null else absensisupirheader.tglbukacetak end ) as tglbukacetak'),
                'statusapprovaleditabsensi.memo as statusapprovaleditabsensi',
                'absensisupirheader.userapprovaleditabsensi',
                DB::raw('(case when (year(absensisupirheader.tglapprovaleditabsensi) <= 2000) then null else absensisupirheader.tglapprovaleditabsensi end ) as tglapprovaleditabsensi'),
                'absensisupirheader.jumlahcetak',
                'absensisupirheader.modifiedby',
                'absensisupirheader.created_at',
                'absensisupirheader.updated_at',
            )
            // request()->tgldari ?? date('Y-m-d',strtotime('today'))
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'absensisupirheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("parameter as statusapprovaleditabsensi with (readuncommitted)"), 'absensisupirheader.statusapprovaleditabsensi', 'statusapprovaleditabsensi.id');
        if (request()->tgldari) {
            $query->whereBetween('tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }


        // $query = DB::table($modelTable);
        $query = $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'kasgantung_nobukti',
            'nominal',
            'statuscetak',
            'userbukacetak',
            'tglbukacetak',
            'statusapprovaleditabsensi',
            'userapprovaleditabsensi',
            'tglapprovaleditabsensi',
            'jumlahcetak',
            'modifiedby',
            'created_at',
            'updated_at',
        ], $models);

        return $temp;
    }

    public function getAbsensi($id)
    {
        $statusabsensi = db::table("parameter")->from(db::raw("parameter"))->select('id')
            ->where('grp', 'STATUS ABSENSI SUPIR')
            ->where('subgrp', 'STATUS ABSENSI SUPIR')
            ->where('text', 'ABSENSI SUPIR')
            ->first()->id ?? 0;
        $query = DB::table('absensisupirdetail')->from(DB::raw("absensisupirdetail with (readuncommitted)"))
            ->select(
                'absensisupirdetail.keterangan as keterangan_detail',
                'absensisupirdetail.jam',
                'absensisupirdetail.uangjalan',
                'absensisupirdetail.absensi_id',
                'absensisupirdetail.id',
                'trado.kodetrado as trado',
                'supirutama.namasupir as supir',
                'trado.id as trado_id',
                DB::raw("(case when supirutama.id IS NULL then 0 else supirutama.id end) as supir_id"),

                'absensisupirheader.kasgantung_nobukti',
            )
            ->leftJoin(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirdetail.absensi_id', 'absensisupirheader.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'absensisupirdetail.trado_id', 'trado.id')
            ->leftJoin(DB::raw("supir as supirutama with (readuncommitted)"), 'absensisupirdetail.supir_id', 'supirutama.id')
            ->whereRaw("not EXISTS (
            SELECT absensisupirapprovalheader.absensisupir_nobukti
    FROM absensisupirdetail  with (readuncommitted)        
    left join absensisupirapprovalheader  with (readuncommitted)  on absensisupirapprovalheader.absensisupir_nobukti= absensisupirdetail.nobukti
    WHERE absensisupirapprovalheader.absensisupir_nobukti = absensisupirheader.nobukti 
          )")
            ->where('absensi_id', $id)
            ->where('trado.statusabsensisupir', $statusabsensi);
        //     $this->totalRows = $query->count();
        // $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;


        $data = $query->get();
        $this->totalUangJalan = $query->sum('uangjalan');
        return $data;
    }

    public function getTradoAbsensi($id)
    {
        $query = DB::table('absentrado')
            ->select('absentrado.kodeabsen', DB::raw('COUNT(absensisupirdetail.absen_id) as jumlah'))
            ->leftJoin('absensisupirdetail', function ($join) use ($id) {
                $join->on('absensisupirdetail.absen_id', '=', 'absentrado.id')
                    ->where('absensisupirdetail.absensi_id', '=', $id);
            })
            ->groupBy('absentrado.kodeabsen')
            ->orderBy("absentrado.kodeabsen", "asc")
            ->get();

        return $query;
    }

    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statuscetak') {
                                $query = $query->where('statuscetak.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusapprovaleditabsensi') {
                                $query = $query->where('statusapprovaleditabsensi.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->whereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglbukacetak' || $filters['field'] == 'tglapprovaleditabsensi') {
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
                                if ($filters['field'] == 'statuscetak') {
                                    $query = $query->orWhere('statuscetak.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statusapprovaleditabsensi') {
                                    $query = $query->orWhere('statusapprovaleditabsensi.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'nominal') {
                                    $query = $query->orWhereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglbukacetak' || $filters['field'] == 'tglapprovaleditabsensi') {
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

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function todayValidation($tglbukti)
    {
        $tglbuktistr = strtotime($tglbukti);
        $jam_batas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', 'BATAS JAM EDIT ABSENSI')->where('subgrp', 'BATAS JAM EDIT ABSENSI')->first();
        $jam = substr($jam_batas->text, 0, 2);
        $menit = substr($jam_batas->text, 3, 2);
        $limit = strtotime($tglbukti . ' +' . $jam . ' hours +' . $menit . ' minutes');
        $now = strtotime('now');
        if ($now < $limit) return true;
        return false;
    }
    public function isBukaTanggalValidation($date)
    {
        $date = date('Y-m-d', strtotime($date));
        // $bukaAbsensi = BukaAbsensi::where('tglabsensi', '=', $date)->first();
        
        //user ada dimandor apa aja
        $userMandor = DB::table('mandordetail')->select('mandor_id')->where('user_id',auth()->user()->id);
        //mandor yang dimiliki user login memiliki user apa aja
        $mandorUser = DB::table('mandordetail')
        ->select('mandor_id','user_id')
        ->where(function ($query) use ($userMandor) {
            $query->whereIn('mandor_id', $userMandor);
        })
        ->groupBy('mandor_id','user_id')
        ->get();
        $userArray = [];
        foreach ($mandorUser as $mandor) {
            $userArray[] = $mandor->user_id;
        }
        
        $bukaAbsensi = BukaAbsensi::where('tglabsensi', '=', $date)
        ->whereIn('mandor_user_id',$userArray)
        ->first();

        $tglbatas = $bukaAbsensi->tglbatas ?? 0;
        $limit = strtotime($tglbatas);
        $now = strtotime('now');
        // dd( date('Y-m-d H:i:s',$now), date('Y-m-d H:i:s',$limit));
        if ($now < $limit) return true;
        return false;
    }

    public function isBukaTanggalAbsenMandor($date)
    {
        $date = date('Y-m-d', strtotime($date));        
        //user ada dimandor apa aja
        $userMandor = DB::table('mandordetail')->select('mandor_id')->where('user_id',auth()->user()->id);
        //mandor yang dimiliki user login memiliki user apa aja
        $mandorUser = DB::table('mandordetail')
        ->select('mandor_id')
        ->where(function ($query) use ($userMandor) {
            $query->whereIn('mandor_id', $userMandor);
        })
        ->groupBy('mandor_id')
        ->get();
        $userArray = [];
        // foreach ($mandorUser as $mandor) {
        //     $userArray[] = $mandor->user_id;
        // }
        
       return $mandorUser;
    }


    public function isApproved($nobukti)
    {
        $absensiSupir = DB::table('absensisupirapprovalheader')
            ->from(
                DB::raw("absensisupirapprovalheader as a with (readuncommitted)")
            )
            ->select(
                'a.absensisupir_nobukti'
            )
            ->where('a.absensisupir_nobukti', '=', $nobukti)
            ->first();
        //jika ada return false
        if (empty($absensiSupir)) return true;
        return false;
    }
    public function isEditAble($id)
    {
        $tidakBolehEdit = DB::table('absensisupirheader')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS EDIT ABSENSI')->where('default', 'YA')->first();

        $query = DB::table('absensisupirheader')->from(DB::raw("absensisupirheader with (readuncommitted)"))
            ->select('statusapprovaleditabsensi as statusedit', 'tglbataseditabsensi')
            ->where('id', $id)
            ->first();

        if ($query->statusedit != $tidakBolehEdit->id) {
            $limit = strtotime($query->tglbataseditabsensi);
            $now = strtotime('now');
            if ($now < $limit) return true;
        }
        return false;
    }
    public function isDateAllowed($id)
    {


        $query = DB::table('absensisupirheader')->from(DB::raw("absensisupirheader with (readuncommitted)"))
            ->select('tglbukti')
            ->where('id', $id)
            ->first();

        $date = date('Y-m-d', strtotime($query->tglbukti));
        $bukaAbsensi = BukaAbsensi::where('tglabsensi', '=', $date)->first();
        $tglbatas = $bukaAbsensi->tglbatas ?? 0;
        $limit = strtotime($tglbatas);
        $now = strtotime('now');
        // dd( date('Y-m-d H:i:s',$now), date('Y-m-d H:i:s',$limit));
        if ($now < $limit) return true;
        return false;
    }
    public function isUsedTrip($id)
    {
        $absensisupirheader = DB::table('absensisupirheader')->from(DB::raw("absensisupirheader with (readuncommitted)"))->where('id', $id)->first();
        $tglabsensi = $absensisupirheader->tglbukti;
        $suratpengantar = DB::table('absensisupirheader')->from(DB::raw("suratpengantar with (readuncommitted)"))->where('tglbukti', $tglabsensi)->first();

        // $query = DB::table('absensisupirheader')->from(DB::raw("absensisupirheader with (readuncommitted)"))
        //     ->select('statusapprovaleditabsensi as statusedit')
        //     ->where('id', $id)
        //     ->first();

        if (isset($suratpengantar)) return true;
        return false;
    }

    public function printValidation($id)
    {

        $statusCetak = DB::table('absensisupirheader')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        $query = DB::table('absensisupirheader')->from(DB::raw("absensisupirheader with (readuncommitted)"))
            ->select('statuscetak')
            ->where('id', $id)
            ->first();

        if ($query->statuscetak != $statusCetak->id) return true;
        return false;
    }

    public function isAbsensiRicUsed($tglbukti)
    {
        $absensisupirheader = DB::table('absensisupirheader')->from(DB::raw("absensisupirheader with (readuncommitted)"))->select('nobukti')->where('tglbukti', $tglbukti)->first();
        if (!$absensisupirheader) {
            return true;
        }
        $gajisupiruangjalan = DB::table('gajisupiruangjalan')->from(DB::raw("gajisupiruangjalan with (readuncommitted)"))->where('absensisupir_nobukti', $absensisupirheader->nobukti)->first();
        if (!$gajisupiruangjalan) {
            return true;
        }
        return false;
    }

    public function getExport($id)
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($this->table)->from(DB::raw("absensisupirheader with (readuncommitted)"))
            ->select(
                'absensisupirheader.id',
                'absensisupirheader.nobukti',
                'absensisupirheader.tglbukti',
                'absensisupirheader.kasgantung_nobukti',
                DB::raw('(case when (year(absensisupirheader.tglbukacetak) <= 2000) then null else absensisupirheader.tglbukacetak end ) as tglbukacetak'),
                'statuscetak.memo as statuscetak',
                "statuscetak.id as  statuscetak_id",
                'statusapprovaleditabsensi.memo as statusapprovaleditabsensi',
                'absensisupirheader.userbukacetak',
                'absensisupirheader.jumlahcetak',
                DB::raw("(case when absensisupirheader.nominal IS NULL then 0 else absensisupirheader.nominal end) as nominal"),
                DB::raw("'Laporan Absensi' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'absensisupirheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("parameter as statusapprovaleditabsensi with (readuncommitted)"), 'absensisupirheader.statusapprovaleditabsensi', 'statusapprovaleditabsensi.id');

        $data = $query->first();
        return $data;
    }


    public function processStore(array $data): AbsensiSupirHeader
    {
        $group = 'ABSENSI';
        $subGroup = 'ABSENSI';
        $format = DB::table('parameter')->where('grp', $group)->where('subgrp', $subGroup)->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
        $statusEditAbsensi = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS EDIT ABSENSI')->where('default', 'YA')->first();

        $bukaabsensi = DB::table('bukaabsensi')
            ->select('tglbatas')
            ->from(DB::raw("bukaabsensi with (readuncommitted)"))
            ->where('tglabsensi', date('Y-m-d', strtotime($data['tglbukti'])))
            ->first();

        $query_jam = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', 'BATAS JAM EDIT ABSENSI')->where('subgrp', 'BATAS JAM EDIT ABSENSI')->first();
        $jam = substr($query_jam->text, 0, 2);
        $menit = substr($query_jam->text, 3, 2);
        $tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $query_jam = strtotime($tglbukti . ' ' . $jam . ':' . $menit . ':00');
        $tglbataseditabsensi = date('Y-m-d H:i:s', $query_jam);
        // if (strtotime('now')>strtotime($tglbataseditabsensi)) {
        //     $tglbatas = date('Y-m-d',strtotime('tomorrow')). ' ' . $query_jam ?? '00:00:00';
        // }

        if ($data['tglbataseditabsensi']) {
            $tglbataseditabsensi = $data['tglbataseditabsensi'];
        } else if (isset($bukaabsensi->tglbatas)) {
            $tglbataseditabsensi = $bukaabsensi->tglbatas;
        }


        /* Store header */
        $absensiSupir = new AbsensiSupirHeader();
        $absensiSupir->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $absensiSupir->kasgantung_nobukti = $data['kasgantung_nobukti'] ?? '';
        $absensiSupir->nominal = array_sum($data['uangjalan']);
        $absensiSupir->statusformat = $format->id;
        $absensiSupir->statuscetak = $statusCetak->id ?? 0;
        $absensiSupir->statusapprovaleditabsensi  = $statusEditAbsensi->id;
        $absensiSupir->tglbataseditabsensi  = $tglbataseditabsensi;
        $absensiSupir->modifiedby = auth('api')->user()->name;
        $absensiSupir->info = html_entity_decode(request()->info);
        $absensiSupir->nobukti = (new RunningNumberService)->get($group, $subGroup, $absensiSupir->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$absensiSupir->save()) {
            throw new \Exception("Error storing Absensi Supir Header.");
        }
        /*STORE DETAIL*/
        $absensiSupirDetail = [];
        if (!$data['trado_id']) {
            throw new \Exception("Error storing pengeluaran Stok Detail.");
        }

        $uangJalan = 0;
        for ($i = 0; $i < count($data['trado_id']); $i++) {
            $absensiSupirDetail = AbsensiSupirDetail::processStore($absensiSupir, [
                'absensi_id' => $absensiSupir->id,
                'nobukti' => $absensiSupir->nobukti,
                'trado_id' => $data['trado_id'][$i],
                'supir_id' => $data['supir_id'][$i],
                'supirold_id' => $data['supirold_id'][$i],
                'keterangan' => $data['keterangan_detail'][$i],
                'uangjalan' => $data['uangjalan'][$i],
                'absen_id' => $data['absen_id'][$i] ?? '',
                'jam' => $data['jam'][$i],
                'modifiedby' => $absensiSupir->modifiedby,
            ]);
            $absensiSupirDetails[] = $absensiSupirDetail->toArray();
            $uangJalan += $data['uangjalan'][$i];
        }

        $storeKasgantung = true;
        $getListTampilan = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'UBAH TAMPILAN')->where('text', 'ABSENSISUPIR')->first();
        if ($getListTampilan != null) {

            $getListTampilan = json_decode($getListTampilan->memo);
            $getListTampilan = (explode(",", $getListTampilan->INPUT));
            foreach ($getListTampilan as $value) {
                if ($value == 'UANGJALAN') {
                    $storeKasgantung = false;
                }
            }
        }

        /*STORE KAS GANTUNG*/
        if ($storeKasgantung) {

            $bank = DB::table('bank')->from(DB::raw("bank with (readuncommitted)"))->select('id')->where('tipe', '=', 'KAS')->first();

            $kasGantungRequest = [
                "tglbukti" => $data['tglbukti'],
                "penerima" => '',
                "bank_id" => $bank->id,
                "coakaskeluar" => '',
                "pengeluaran_nobukti" => '',
                "postingdari" => 'ENTRY ABSENSI SUPIR',
                'proseslain' => 'absensisupir',
                "nominal" => [$uangJalan],
                "keterangan_detail" => ["Absensi Supir tgl " . date('Y-m-d', strtotime($data['tglbukti'])) . " " . $absensiSupir->nobukti],
            ];

            $kasgantungHeader = (new KasGantungHeader())->processStore($kasGantungRequest);

            $absensiSupir->kasgantung_nobukti = $kasgantungHeader->nobukti;
            $absensiSupir->save();
        }

        $absensiSupirLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($absensiSupir->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('EDIT ABSENSI SUPIR Header '),
            'idtrans' => $absensiSupir->id,
            'nobuktitrans' => $absensiSupir->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $absensiSupir->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        $absensiSupirDetailLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($absensiSupirDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('EDIT ABSENSI SUPIR detail '),
            'idtrans' => $absensiSupirLogTrail->id,
            'nobuktitrans' => $absensiSupir->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $absensiSupirDetails,
            'modifiedby' => auth('api')->user()->user
        ]);

        // dd($kasgantungHeader);
        return $absensiSupir;
    }

    public function processUpdate(AbsensiSupirHeader $absensiSupir, array $data): AbsensiSupirHeader
    {
        $group = 'ABSENSI';
        $subGroup = 'ABSENSI';
        $format = DB::table('parameter')->where('grp', $group)->where('subgrp', $subGroup)->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
        $statusEditAbsensi = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS EDIT ABSENSI')->where('default', 'YA')->first();

        $bukaabsensi = DB::table('bukaabsensi')
            ->select('tglbatas')
            ->from(DB::raw("bukaabsensi with (readuncommitted)"))
            ->where('tglabsensi', date('Y-m-d', strtotime($data['tglbukti'])))
            ->first();

        // $query_jam = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', 'BATAS JAM EDIT ABSENSI')->where('subgrp', 'BATAS JAM EDIT ABSENSI')->first();
        // $jam = substr($query_jam->text, 0, 2);
        // $menit = substr($query_jam->text, 3, 2);
        // $tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        // $query_jam = strtotime($tglbukti.' '.$jam.':'.$menit.':00' );
        // $tglbataseditabsensi = date('Y-m-d H:i:s',$query_jam);
        $tglbataseditabsensi = $absensiSupir->tglbataseditabsensi;

        if ($data['tglbataseditabsensi']) {
            $tglbataseditabsensi = $data['tglbataseditabsensi'];
        } else if (isset($bukaabsensi->tglbatas)) {
            $tglbataseditabsensi = $bukaabsensi->tglbatas;
        }

        /* Store header */
        $absensiSupir->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $absensiSupir->nominal = array_sum($data['uangjalan']);
        $absensiSupir->statusformat = $format->id;
        $absensiSupir->statuscetak = $statusCetak->id ?? 0;
        // $absensiSupir->statusapprovaleditabsensi  = $statusEditAbsensi->id;
        $absensiSupir->tglbataseditabsensi  = $tglbataseditabsensi;
        $absensiSupir->info = html_entity_decode(request()->info);

        if (!$absensiSupir->save()) {
            throw new \Exception("Error storing Absensi Supir Header.");
        }

        AbsensiSupirDetail::where('absensi_id', $absensiSupir->id)->delete();

        /*STORE DETAIL*/
        $absensiSupirDetail = [];
        if (!$data['trado_id']) {
            throw new \Exception("Error storing pengeluaran Stok Detail.");
        }
        $uangJalan = 0;

        for ($i = 0; $i < count($data['trado_id']); $i++) {
            $absensiSupirDetail = AbsensiSupirDetail::processStore($absensiSupir, [
                'absensi_id' => $absensiSupir->id,
                'nobukti' => $absensiSupir->nobukti,
                'trado_id' => $data['trado_id'][$i],
                'supir_id' => $data['supir_id'][$i],
                'supirold_id' => $data['supir_id'][$i],
                'keterangan' => $data['keterangan_detail'][$i],
                'uangjalan' => $data['uangjalan'][$i],
                'absen_id' => $data['absen_id'][$i] ?? '',
                'jam' => $data['jam'][$i],
                'modifiedby' => $absensiSupir->modifiedby,
            ]);
            $absensiSupirDetails[] = $absensiSupirDetail->toArray();
            $uangJalan += $data['uangjalan'][$i];
        }

        $storeKasgantung = true;
        $getListTampilan = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'UBAH TAMPILAN')->where('text', 'ABSENSISUPIR')->first();
        if ($getListTampilan != null) {

            $getListTampilan = json_decode($getListTampilan->memo);
            $getListTampilan = (explode(",", $getListTampilan->INPUT));
            foreach ($getListTampilan as $value) {
                if ($value == 'UANGJALAN') {
                    $storeKasgantung = false;
                }
            }
        }

        /*STORE KAS GANTUNG*/
        if ($storeKasgantung) {
            $bank = DB::table('bank')->from(DB::raw("bank with (readuncommitted)"))->select('id')->where('tipe', '=', 'KAS')->first();

            $kasGantungRequest = [
                "tglbukti" => $data['tglbukti'],
                "penerima" => null,
                "bank_id" => $bank->id,
                "coakaskeluar" => null,
                "pengeluaran_nobukti" => null,
                "postingdari" => 'ENTRY ABSENSI SUPIR',
                "nominal" => [$uangJalan],
                "keterangan_detail" => ["Absensi Supir tgl " . date('Y-m-d', strtotime($data['tglbukti'])) . " " . $absensiSupir->nobukti],
            ];

            $kasGantungHeader = KasGantungHeader::from(DB::raw("kasgantungheader with (readuncommitted)"))->where('nobukti', $absensiSupir->kasgantung_nobukti)->first();
            $kasGantungHeader = (new KasGantungHeader())->processUpdate($kasGantungHeader, $kasGantungRequest);
        }

        $date = date('Y-m-d', strtotime($absensiSupir->tglbukti));
        $now = date('Y-m-d', strtotime('now'));
        if (!$this->todayValidation($date)) {
            // if (!$this->todayValidation($absensiSupir->id)) {

            $bukaAbsensi = BukaAbsensi::from(DB::raw("BukaAbsensi"))->where('tglabsensi', $absensiSupir->tglbukti)->first();
            if (isset($bukaAbsensi)) {
                // $bukaAbsensi = (new BukaAbsensi())->processDestroy($bukaAbsensi->id);
            }
        }

        $absensiSupirLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($absensiSupir->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('EDIT ABSENSI SUPIR Header '),
            'idtrans' => $absensiSupir->id,
            'nobuktitrans' => $absensiSupir->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $absensiSupir->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        $absensiSupirDetailLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($absensiSupirDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('EDIT ABSENSI SUPIR detail '),
            'idtrans' => $absensiSupirLogTrail->id,
            'nobuktitrans' => $absensiSupir->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $absensiSupirDetails,
            'modifiedby' => auth('api')->user()->user
        ]);

        // dd($kasgantungHeader);
        return $absensiSupir;
    }


    public function processDestroy($id, $postingdari = ""): AbsensiSupirHeader
    {
        $absensiSupir = AbsensiSupirHeader::findOrFail($id);
        $dataHeader =  $absensiSupir->toArray();
        $absensiSupirDetail = AbsensiSupirDetail::where('absensi_id', '=', $absensiSupir->id)->get();
        $dataDetail = $absensiSupirDetail->toArray();

        /*DELETE EXISTING DETAIL*/
        $absensiSupirDetail = AbsensiSupirDetail::where('absensi_id', $absensiSupir->id)->lockForUpdate()->delete();

        /*DELETE EXISTING JURNAL*/
        $kasGantungHeader = KasGantungHeader::where('nobukti', $absensiSupir->kasgantung_nobukti)->first();

        if ($kasGantungHeader) {
            (new KasGantungHeader())->processDestroy($kasGantungHeader->id, ($postingdari == "") ? $postingdari : strtoupper('DELETE ABSENSI SUPIR detail'));
        }

        $absensiSupir = $absensiSupir->lockAndDestroy($id);
        $hutangLogTrail = (new LogTrail())->processStore([
            'namatabel' => $this->table,
            'postingdari' => ($postingdari == "") ? $postingdari : strtoupper('DELETE ABSENSI SUPIR Header'),
            'idtrans' => $absensiSupir->id,
            'nobuktitrans' => $absensiSupir->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $dataHeader,
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'PENERIMAANDETAIL',
            'postingdari' => ($postingdari == "") ? $postingdari : strtoupper('DELETE ABSENSI SUPIR detail'),
            'idtrans' => $hutangLogTrail['id'],
            'nobuktitrans' => $absensiSupir->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $dataDetail,
            'modifiedby' => auth('api')->user()->name
        ]);

        return $absensiSupir;
    }
}
