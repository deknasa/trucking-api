<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExpStnk extends MyModel
{
    use HasFactory;

    public function get()
    {
        $this->setRequestParameters();
        $statusaktif = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('text', '=', 'AKTIF')
            ->first();

        $statusabsensisupir = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', '=', 'STATUS ABSENSI SUPIR')
            ->where('subgrp', '=', 'STATUS ABSENSI SUPIR')
            ->where('text', '=', 'ABSENSI SUPIR')
            ->first();

        $batasMax = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', '=', 'BATAS MAX EXPIRED')
            ->first();
        $rentang = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', '=', 'RENTANG EXPIRED')
            ->first();
        $sudahExp =  Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', '=', 'STATUS EXPIRED')
            ->where('text', '=', 'SUDAH EXPIRED')
            ->first();
        $hampirExp =  Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', '=', 'STATUS EXPIRED')
            ->where('text', '=', 'HAMPIR EXPIRED')
            ->first();
        $belumExp =  Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', '=', 'STATUS EXPIRED')
            ->where('text', '=', '30 HARI SEBELUM EXPIRED')
            ->first();

        $class = 'ExpStnkController';
        $user = auth('api')->user()->name;
        $proses = request()->proses ?? 'reload';

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
                $table->string('kodetrado', 1000)->nullable();
                $table->date('tglstnkmati', 1000)->nullable();
                $table->integer('status')->nullable();
            });
            $getQuery = DB::table('trado')->from(DB::raw("trado with (readuncommitted)"))
                ->select(
                    'trado.id',
                    'trado.kodetrado',
                    DB::raw('(case when (year(trado.tglstnkmati) <= 2000) then null else trado.tglstnkmati end ) as tglstnkmati'),

                    DB::raw("(case 
                    when DATEDIFF(dd,getdate(),tglstnkmati)>$batasMax->text then $belumExp->id 
                    when tglstnkmati <= getdate() then $sudahExp->id
                    else $hampirExp->id end) 
                    
                    as status")
                )
                ->where('statusaktif', $statusaktif->id)
                ->where('statusabsensisupir', $statusabsensisupir->id)

                ->where('tglstnkmati', '<=', date('Y/m/d', strtotime("+$rentang->text days")));

            DB::table($temtabel)->insertUsing(['id', 'kodetrado', 'tglstnkmati', 'status'], $getQuery);
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

        $query = DB::table(DB::raw($temtabel))->from(
            DB::raw(DB::raw($temtabel) . " trado with (readuncommitted)")
        )
            ->select(
                'trado.id',
                'trado.kodetrado',
                'trado.tglstnkmati',
                'parameter.memo as status',
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'trado.status', 'parameter.id');


        $this->filter($query);

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->paginate($query);
        $data = $query->get();

        return $data;
    }

    public function reminderemailstnk()
    {
        $pjlhhariremind = 30;
        $tglremind = DB::select("select format(DATEADD(d," . $pjlhhariremind . ",GETDATE()),'yyyy/MM/dd') as dadd");
        $ptglremind = json_decode(json_encode($tglremind), true)[0]['dadd'];

        $reminderemail = 1;
        $listtoemail = db::table("toemail")->from(db::raw("toemail a with (readuncommitted)"))
            ->select(
                'a.email'
            )
            ->where('a.reminderemail_id', $reminderemail)
            ->orderby('a.id', 'asc')
            ->get();

        $datadetailtoemail = json_decode($listtoemail, true);
        $hittoemail = 0;
        $toemail = '';
        foreach ($datadetailtoemail as $item) {

            if ($hittoemail == 0) {
                $toemail = $toemail . $item['email'];
            } else {
                $toemail = $toemail . ';' . $item['email'];
            }
            $hittoemail = $hittoemail + 1;
        }

        $listccemail = db::table("ccemail")->from(db::raw("ccemail a with (readuncommitted)"))
            ->select(
                'a.email'
            )
            ->where('a.reminderemail_id', $reminderemail)
            ->orderby('a.id', 'asc')
            ->get();

        $datadetailccemail = json_decode($listccemail, true);
        $hitccemail = 0;
        $ccemail = '';
        foreach ($datadetailccemail as $item) {

            if ($hitccemail == 0) {
                $ccemail = $ccemail . $item['email'];
            } else {
                $ccemail = $ccemail . ';' . $item['email'];
            }
            $hitccemail = $hitccemail + 1;
        }

        $listbccemail = db::table("bccemail")->from(db::raw("bccemail a with (readuncommitted)"))
            ->select(
                'a.email'
            )
            ->where('a.reminderemail_id', $reminderemail)
            ->orderby('a.id', 'asc')
            ->get();

        $datadetailbccemail = json_decode($listbccemail, true);
        $hitbccemail = 0;
        $bccemail = '';
        foreach ($datadetailbccemail as $item) {

            if ($hitbccemail == 0) {
                $bccemail = $bccemail . $item['email'];
            } else {
                $bccemail = $bccemail . ';' . $item['email'];
            }
            $hitbccemail = $hitbccemail + 1;
        }
        $tempreminder = '##tempreminder' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempreminder, function ($table) {
            $table->id();
            $table->date('tgl')->nullable();
            $table->string('kodetrado', 500)->nullable();
            $table->string('jenis', 500)->nullable();
            $table->string('tglstr', 500)->nullable();
            $table->string('warna', 500)->nullable();
        });


        $statusaktif = DB::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))
            ->select('a.id')
            ->where('a.grp', 'STATUS AKTIF')->where('a.subgrp', 'STATUS AKTIF')->where('a.text', 'AKTIF')->first()
            ->id ?? 0;

        $statusabsensisupir = DB::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))
            ->select('a.id')
            ->where('a.grp', 'STATUS ABSENSI SUPIR')->where('a.subgrp', 'STATUS ABSENSI SUPIR')->where('a.text', 'ABSENSI SUPIR')->first()
            ->id ?? 0;

        //asuransi mati
        $queryreminder = db::table("trado")->from(db::raw("trado a with (readuncommitted)"))
            ->select(
                'a.tglasuransimati AS tgl',
                'kodetrado as kodetrado',
                db::raw("'Jatuh Tempo Asuransi' AS jenis"),
                db::raw("CAST(DAY(tglasuransimati) AS VARCHAR(2)) + '-' +  
                (case when month(tglasuransimati)=1 then 'Januari'
                      when month(tglasuransimati)=2 then 'Februari'
                      when month(tglasuransimati)=3 then 'Maret'
                      when month(tglasuransimati)=4 then 'April'
                      when month(tglasuransimati)=5 then 'Mei'
                      when month(tglasuransimati)=6 then 'Juni'
                      when month(tglasuransimati)=7 then 'Juli'
                      when month(tglasuransimati)=8 then 'Agustus'
                      when month(tglasuransimati)=9 then 'September'
                      when month(tglasuransimati)=10 then 'Oktober'
                      when month(tglasuransimati)=11 then 'November'
                      when month(tglasuransimati)=12 then 'Desember'
                  else '' end)
                + '-' + 
                 CAST(YEAR(tglasuransimati) AS VARCHAR(4)) AS tglstr"),
                db::raw("CASE SIGN(DATEDIFF(d,GETDATE(),tglasuransimati)) WHEN -1 THEN 'RED' ELSE 'YELLOW' END AS warna")
            )
            ->whereraw("ISNULL(tglasuransimati,'1900-01-01')<>'1900-01-01'")
            ->whereraw("DATEDIFF(d,tglasuransimati,'" . $ptglremind . "')>=0")
            ->where('a.statusaktif', $statusaktif)
            ->where('a.statusabsensisupir', $statusabsensisupir);

        // dd($queryreminder->tosql()); 

        DB::table($tempreminder)->insertUsing([
            'tgl',
            'kodetrado',
            'jenis',
            'tglstr',
            'warna',
        ], $queryreminder);


        //stnk mati
        $queryreminder = db::table("trado")->from(db::raw("trado a with (readuncommitted)"))
            ->select(
                'a.tglstnkmati AS tgl',
                'a.kodetrado as kodetrado',
                db::raw("'Jatuh Tempo Pajak STNK (Ganti Plat)' AS jenis"),
                db::raw("CAST(DAY(tglstnkmati) AS VARCHAR(2)) + '-' +  
			(case when month(tglstnkmati)=1 then 'Januari'
			      when month(tglstnkmati)=2 then 'Februari'
			      when month(tglstnkmati)=3 then 'Maret'
			      when month(tglstnkmati)=4 then 'April'
			      when month(tglstnkmati)=5 then 'Mei'
			      when month(tglstnkmati)=6 then 'Juni'
			      when month(tglstnkmati)=7 then 'Juli'
			      when month(tglstnkmati)=8 then 'Agustus'
			      when month(tglstnkmati)=9 then 'September'
			      when month(tglstnkmati)=10 then 'Oktober'
			      when month(tglstnkmati)=11 then 'November'
			      when month(tglstnkmati)=12 then 'Desember'
			  else '' end)
			+ '-' + 
			 CAST(YEAR(tglstnkmati) AS VARCHAR(4)) AS tglstr"),
                db::raw("CASE SIGN(DATEDIFF(d,GETDATE(),tglstnkmati)) WHEN -1 THEN 'RED' ELSE 'YELLOW' END AS warna ")
            )
            ->whereraw("ISNULL(tglstnkmati,'1900-01-01')<>'1900-01-01'")
            ->whereraw("DATEDIFF(d,tglstnkmati,'" . $ptglremind . "')>=0")
            ->where('a.statusaktif', $statusaktif)
            ->where('a.statusabsensisupir', $statusabsensisupir);

        DB::table($tempreminder)->insertUsing([
            'tgl',
            'kodetrado',
            'jenis',
            'tglstr',
            'warna',
        ], $queryreminder);


        //tgl pajak stnk mati
        $queryreminder = db::table("trado")->from(db::raw("trado a with (readuncommitted)"))
            ->select(
                'a.tglpajakstnk AS tgl',
                'a.kodetrado as kodetrado',
                db::raw("'Jatuh Tempo Pajak STNK' AS jenis"),
                db::raw("CAST(DAY(tglpajakstnk) AS VARCHAR(2)) + '-' +  
		(case when month(tglstnkmati)=1 then 'Januari'
			      when month(tglpajakstnk)=2 then 'Februari'
			      when month(tglpajakstnk)=3 then 'Maret'
			      when month(tglpajakstnk)=4 then 'April'
			      when month(tglpajakstnk)=5 then 'Mei'
			      when month(tglpajakstnk)=6 then 'Juni'
			      when month(tglpajakstnk)=7 then 'Juli'
			      when month(tglpajakstnk)=8 then 'Agustus'
			      when month(tglpajakstnk)=9 then 'September'
			      when month(tglpajakstnk)=10 then 'Oktober'
			      when month(tglpajakstnk)=11 then 'November'
			      when month(tglpajakstnk)=12 then 'Desember'
			  else '' end)
			+ '-' + 
			 CAST(YEAR(tglpajakstnk) AS VARCHAR(4)) AS tglstr"),
                db::raw("CASE SIGN(DATEDIFF(d,GETDATE(),tglpajakstnk)) WHEN -1 THEN 'RED' ELSE 'YELLOW' END AS warna")
            )
            ->whereraw("ISNULL(tglpajakstnk,'1900-01-01')<>'1900-01-01'")
            ->whereraw("DATEDIFF(d,tglpajakstnk,'" . $ptglremind . "')>=0")
            ->where('a.statusaktif', $statusaktif)
            ->where('a.statusabsensisupir', $statusabsensisupir);

        DB::table($tempreminder)->insertUsing([
            'tgl',
            'kodetrado',
            'jenis',
            'tglstr',
            'warna',
        ], $queryreminder);

        //tgl speksi mati
        $queryreminder = db::table("trado")->from(db::raw("trado a with (readuncommitted)"))
            ->select(
                'a.tglspeksimati AS tgl',
                'a.kodetrado as kodetrado',
                db::raw("'Jatuh Tempo Speksi (KIR)' AS jenis"),
                db::raw("CAST(DAY(tglspeksimati) AS VARCHAR(2)) + '-' +  
			(case when month(tglspeksimati)=1 then 'Januari'
			      when month(tglspeksimati)=2 then 'Februari'
			      when month(tglspeksimati)=3 then 'Maret'
			      when month(tglspeksimati)=4 then 'April'
			      when month(tglspeksimati)=5 then 'Mei'
			      when month(tglspeksimati)=6 then 'Juni'
			      when month(tglspeksimati)=7 then 'Juli'
			      when month(tglspeksimati)=8 then 'Agustus'
			      when month(tglspeksimati)=9 then 'September'
			      when month(tglspeksimati)=10 then 'Oktober'
			      when month(tglspeksimati)=11 then 'November'
			      when month(tglspeksimati)=12 then 'Desember'
			  else '' end)
			 + '-' + 
			 CAST(YEAR(tglspeksimati) AS VARCHAR(4)) AS tglstr"),
                db::raw("CASE SIGN(DATEDIFF(d,GETDATE(),tglspeksimati)) WHEN -1 THEN 'RED' ELSE 'YELLOW' END AS warna")
            )
            ->whereraw("ISNULL(tglspeksimati,'1900-01-01')<>'1900-01-01'")
            ->whereraw("DATEDIFF(d,tglspeksimati,'" . $ptglremind . "')>=0")
            ->where('a.statusaktif', $statusaktif)
            ->where('a.statusabsensisupir', $statusabsensisupir);

        DB::table($tempreminder)->insertUsing([
            'tgl',
            'kodetrado',
            'jenis',
            'tglstr',
            'warna',
        ], $queryreminder);


        $cabang = DB::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))
            ->select('a.text')
            ->where('a.grp', 'CABANG')->where('a.subgrp', 'CABANG')->first()
            ->text ?? '';

        $query = db::table($tempreminder)->from(db::raw($tempreminder . " a"))
            ->select(
                'a.tgl',
                'a.kodetrado',
                'a.jenis',
                'a.tglstr',
                'a.warna',
                db::raw("'" . $toemail . "' as toemail"),
                db::raw("'" . $ccemail . "' as ccemail"),
                db::raw("'" . $bccemail . "' as bccemail"),
                db::raw("'Reminder Pajak STNK, Asuransi dan KIR Akan Jatuh Tempo 30 Hari Ke Depan ( " . $cabang . " )' as judul"),
            )->orderby('a.id', 'asc')
            ->get();

        return $query;
    }




    public function sort($query)
    {

        return $query->orderBy('trado.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'status') {
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'tglstnkmati') {
                            $query = $query->whereRaw("format(trado." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw("trado.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'status') {
                                $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'tglstnkmati') {
                                $query = $query->orWhereRaw("format(trado." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw("trado.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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
}
