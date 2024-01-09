<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class MandorAbsensiSupir extends MyModel
{
    use HasFactory;

    protected $table = 'trado';


    public function tableTemp($date = 'now')
    {
        $mandorId = false;
        $isMandor = auth()->user()->isMandor();
        $isAdmin = auth()->user()->isAdmin();

        $statusaktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'AKTIF')->first();
        $statusabsensisupir = DB::table('parameter')->where('grp', 'STATUS ABSENSI SUPIR')->where('subgrp', 'STATUS ABSENSI SUPIR')->where('text', 'ABSENSI SUPIR')->first();
        $tradoMilikSupir = DB::table('parameter')->where('grp', 'ABSENSI SUPIR')->where('subgrp', 'TRADO MILIK SUPIR')->first();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->integer('trado_id')->nullable();
            $table->integer('supir_id')->nullable();
            $table->integer('absen_id')->nullable();
            $table->string('keterangan')->nullable();
            $table->time('jam')->nullable();
            $table->date('tglbukti')->default();
        });



        $tempMandor = '##tempmandor' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempMandor, function ($table) {
            $table->integer('trado_id')->nullable();
            $table->string('kodetrado')->nullable();
            $table->string('namasupir')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('absentrado')->nullable();
            $table->integer('absen_id')->nullable();
            $table->time('jam')->nullable();
            $table->date('tglbukti')->nullable();
            $table->integer('supir_id')->nullable();
        });

        $absensisupirdetail = DB::table('absensisupirdetail')
            ->select(
                'trado.id as trado_id',
                'trado.kodetrado',
                'supir.namasupir',
                'absensisupirdetail.keterangan',
                'absentrado.keterangan as absentrado',
                'absentrado.id as absen_id',
                'absensisupirdetail.jam',
                'absensisupirheader.tglbukti',
                'supir.id as supir_id'
            )
            ->where('absensisupirheader.tglbukti', date('Y-m-d', strtotime($date)))
            ->leftJoin(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirdetail.absensi_id', 'absensisupirheader.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'absensisupirdetail.trado_id', 'trado.id')
            ->leftJoin(DB::raw("absentrado with (readuncommitted)"), 'absensisupirdetail.absen_id', 'absentrado.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'absensisupirdetail.supir_id', 'supir.id');
        if (!$isAdmin) {
            if ($isMandor) {
                $absensisupirdetail->where('trado.mandor_id',$isMandor->mandor_id);
            }else{
                $absensisupirdetail->where('trado.id',0);
            }
        }
        
        DB::table($tempMandor)->insertUsing(['trado_id', 'kodetrado', 'namasupir', 'keterangan', 'absentrado', 'absen_id', 'jam', 'tglbukti', 'supir_id'], $absensisupirdetail);

        $trado = DB::table('trado as a')
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

            )
            ->leftJoin('supir as c', 'a.supir_id', 'c.id')
            ->where('a.statusaktif', $statusaktif->id)
            ->where('a.statusabsensisupir', $statusabsensisupir->id)
            ->whereRaw("a.id not in (select trado_id from $tempMandor)");
        if (!$isAdmin) {
            if ($isMandor) {
                $trado->where('a.mandor_id',$isMandor->mandor_id);
            }else{
                $trado->where('a.id',0);
            }
        }

        if ($tradoMilikSupir->text == 'YA') {
            $trado->where('a.supir_id', '!=', 0);
        }

        DB::table($tempMandor)->insertUsing(['trado_id', 'kodetrado', 'namasupir', 'keterangan', 'absentrado', 'absen_id', 'jam', 'tglbukti', 'supir_id'], $trado);

        $tgl = date('Y-m-d', strtotime($date));
        $trado = DB::table('trado as a')
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
                'c.id as supir_id'
            )
            ->where('a.statusaktif', $statusaktif->id)
            ->where('a.statusabsensisupir', $statusabsensisupir->id)
            ->leftJoin('supirserap as e', 'e.trado_id', 'a.id')
            ->leftJoin('supir as c', 'e.supirserap_id', 'c.id')
            ->where('e.tglabsensi', date('Y-m-d', strtotime($date)))
            ->where('e.statusapproval', 3)
            ->whereRaw("e.supirserap_id not in (select supir_id from absensisupirdetail join absensisupirheader on absensisupirheader.nobukti = absensisupirdetail.nobukti where absensisupirheader.tglbukti='$tgl')");
        if (!$isAdmin) {
            if ($isMandor) {
                $trado->where('a.mandor_id',$isMandor->mandor_id);
            }else{
                $trado->where('a.id',0);
            }
        }

        DB::table($tempMandor)->insertUsing(['trado_id', 'kodetrado', 'namasupir', 'keterangan', 'absentrado', 'absen_id', 'jam', 'tglbukti', 'supir_id'], $trado);

        $query = DB::table($tempMandor)->from(DB::raw("$tempMandor as a"))
            ->select(
                DB::raw("row_number() Over(Order By a.trado_id) as id"),
                'a.trado_id',
                'a.kodetrado',
                'a.namasupir',
                'a.keterangan',
                'a.absentrado',
                'a.absen_id',
                'a.jam',
                           DB::raw("(case when year(isnull(a.tglbukti,'1900/1/1'))=1900 then null else format(a.tglbukti,'dd-MM-yyyy')  end)as tglbukti"),
                'a.supir_id',
            );
        return $query;
    }

    public function get()
    {
        $this->setRequestParameters();
        $tglbukaabsensi = request()->tglbukaabsensi ?? 'now';
        $query = $this->tableTemp($tglbukaabsensi);
        $this->filter($query);
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $this->sort($query);
        $this->paginate($query);
        $data = $query->get();
        // dd($this->totalPages);
        return $data;
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
            ->where('grp', 'TIDAK ADA SUPIR')
            ->where('subgrp', 'TIDAK ADA SUPIR')
            ->first();

        $data = DB::table('absentrado')
            ->from(DB::raw("absentrado with (readuncommitted)"))
            ->select(
                DB::raw("(case when id=" . $queryabsen->text . " then 1 else 0 end)  as kodeabsen")
            )
            ->where('absentrado.id', $id)
            ->first();


        return $data;
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
        $AbsensiSupirHeader = AbsensiSupirHeader::where('tglbukti', date('Y-m-d', strtotime($data['tglbukti'])))->first();
        $tidakadasupir = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', 'TIDAK ADA SUPIR')->where('subgrp', 'TIDAK ADA SUPIR')->first();
        if ($tidakadasupir->text == $data['absen_id']) {
            $data['supir_id'] = "";
        }

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
                "trado_id" => [$data['trado_id']],
                "supir_id" => [$data['supir_id']],
                "keterangan_detail" => [$data['keterangan']],
                "absen_id" => [$data['absen_id']],
                "jam" => [$data['jam']],
            ];
            $AbsensiSupirHeader = (new AbsensiSupirHeader())->processStore($absensiSupirRequest);
        }

        // $AbsensiSupirDetail = (new AbsensiSupirDetail())->processStore($absensiSupirRequest);
        $absensiSupirDetail = AbsensiSupirDetail::where('absensi_id', $AbsensiSupirHeader->id)->where('trado_id', $data['trado_id'])->where('supir_id', $data['supir_id'])->lockForUpdate()->first();
        if ($absensiSupirDetail) {
            $absensiSupirDetail->delete();
        }

        $absensiSupirDetail = AbsensiSupirDetail::processStore($AbsensiSupirHeader, [
            'absensi_id' => $AbsensiSupirHeader->id,
            'nobukti' => $AbsensiSupirHeader->nobukti,
            'trado_id' => $data['trado_id'],
            'supir_id' => $data['supir_id'],
            'keterangan' => $data['keterangan'],
            'absen_id' => $data['absen_id'] ?? '',
            'jam' => (strlen($data['jam'])<5) ?null:$data['jam'],
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

        return $absensiSupirDetail;
    }
    public function processUpdate(AbsensiSupirDetail $AbsensiSupirDetail, array $data)
    {
        $AbsensiSupirHeader = AbsensiSupirHeader::where('id', $AbsensiSupirDetail->absensi_id)->first();
        $AbsensiSupirDetail = AbsensiSupirDetail::where('id', $AbsensiSupirDetail->id)->lockForUpdate()->first();
        $AbsensiSupirDetail->delete();
        $tidakadasupir = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', 'TIDAK ADA SUPIR')->where('subgrp', 'TIDAK ADA SUPIR')->first();
        if ($tidakadasupir->text == $data['absen_id']) {
            $data['supir_id'] = "";
        }
        // dd($AbsensiSupirDetail);

        $absensiSupirDetail = AbsensiSupirDetail::processStore($AbsensiSupirHeader, [
            'absensi_id' => $AbsensiSupirHeader->id,
            'nobukti' => $AbsensiSupirHeader->nobukti,
            'trado_id' => $data['trado_id'],
            'supir_id' => $data['supir_id'],
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
