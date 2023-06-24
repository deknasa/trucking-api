<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MandorAbsensiSupir extends MyModel
{
    use HasFactory;

    protected $table = 'trado';




    public function get()
    {
        $this->setRequestParameters();
        
        $statusaktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->where('text', 'AKTIF')->first();
        $trado = DB::table('trado')
            ->select(
                'trado.id as id',
                'trado.kodetrado as trado_id',
                DB::raw('null as supir_id'),
                DB::raw('null as absen_id'),
                DB::raw('null as keterangan'),
                DB::raw('null as jam'),
                DB::raw('null as tglbukti')
            )
            ->where('trado.statusaktif', $statusaktif->id)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('absensisupirdetail')
                    ->whereRaw('trado.id = absensisupirdetail.trado_id')
                    ->where('absensisupirheader.tglbukti', date('Y-m-d', strtotime('now')))
                    ->leftJoin('absensisupirheader', 'absensisupirdetail.absensi_id', 'absensisupirheader.id');
            });
        $absensisupirdetail = DB::table('absensisupirdetail')
            ->select(
                'trado.id as id',
                'trado.kodetrado as trado_id',
                'supir.namasupir as supir_id',
                'absentrado.keterangan as absen_id',
                'absensisupirdetail.keterangan',
                'absensisupirdetail.jam',
                'absensisupirheader.tglbukti'
            )
            ->where('absensisupirheader.tglbukti', date('Y-m-d', strtotime('now')))
            ->leftJoin(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirdetail.absensi_id', 'absensisupirheader.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'absensisupirdetail.trado_id', 'trado.id')
            ->leftJoin(DB::raw("absentrado with (readuncommitted)"), 'absensisupirdetail.absen_id', 'absentrado.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'absensisupirdetail.supir_id', 'supir.id');
        $query = $trado->union($absensisupirdetail);
        // $this->totalRows = $query->count();
        // $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        // $this->sort($query);
        // $this->filter($query);
        // $this->paginate($query);
        $data = $query->get();
        // dd($query->take(5)->get());
        return $data;
    }

    public function getAll($id)
    {
        return $id;
    }

    public function cekvalidasihapus($trado_id,$supir_id,$tglbukti)
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
                DB::raw("(case when id=". $queryabsen->text ." then 1 else 0 end)  as kodeabsen")
            )
            ->where('absentrado.id', $id)
            ->first();


        return $data;
    }


    public function isAbsen($id)
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
            ->where('absensisupirheader.tglbukti', date('Y-m-d', strtotime('now')))
            ->leftJoin(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirdetail.absensi_id', 'absensisupirheader.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'absensisupirdetail.trado_id', 'trado.id')
            ->leftJoin(DB::raw("absentrado with (readuncommitted)"), 'absensisupirdetail.absen_id', 'absentrado.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'absensisupirdetail.supir_id', 'supir.id');
        return $absensisupirdetail->first();
    }

    public function getTrado($id)
    {
        $absensisupirdetail = DB::table('trado')
            ->select(
                DB::raw('null as id'),
                'trado.id as trado_id',
                'trado.kodetrado as trado',
                DB::raw('null as supir_id'),
                DB::raw('null as absen_id'),
                DB::raw('null as keterangan'),
                DB::raw('null as jam'),
                DB::raw('null as tglbukti')
            )->where('trado.id', $id);
        return $absensisupirdetail->first();
    }


    public function sort($query)
    {
        return $query->orderBy($this->params['sortIndex'], $this->params['sortOrder']);
    }
    public function paginate($query)
    {
        return $query->skip(request()->page * request()->limit)->take(request()->limit);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        switch ($filters['field']) {
                            case 'trado_id':
                                $query = $query->where('trado.kodetrado', 'LIKE', "%$filters[data]%");
                                break;
                            case 'supir':
                                $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                                break;
                            case 'absen':
                                $query = $query->where('absentrado.keterangan ', 'LIKE', "%$filters[data]%");
                                break;
                            default:
                                // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

                                break;
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {

                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            switch ($filters['field']) {
                                case 'trado_id':
                                    $query = $query->orWhere('trado.kodetrado', 'LIKE', "%$filters[data]%");
                                    break;
                                case 'supir':
                                    $query = $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
                                    break;
                                case 'absen':
                                    $query = $query->orWhere('absentrado.keterangan ', 'LIKE', "%$filters[data]%");
                                    break;
                                default:
                                    // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

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
        $AbsensiSupirHeader = AbsensiSupirHeader::where('tglbukti', date('Y-m-d', strtotime('now')))->first();
        
        if (!$AbsensiSupirHeader) {
            $absensiSupirRequest = [
                "tglbukti" =>$data['tglbukti'],
                "kasgantung_nobukti" =>$data['kasgantung_nobukti'],
                "uangjalan" =>[0],
                "trado_id" =>[$data['trado_id']],
                "supir_id" =>[$data['supir_id']],
                "keterangan_detail" =>[$data['keterangan']],
                "absen_id" =>[$data['absen_id']],
                "jam" =>[$data['jam']],
            ];
            $AbsensiSupirHeader = (new AbsensiSupirHeader())->processStore($absensiSupirRequest);
        }

        // $AbsensiSupirDetail = (new AbsensiSupirDetail())->processStore($absensiSupirRequest);
        $absensiSupirDetail = AbsensiSupirDetail::where('absensi_id', $AbsensiSupirHeader->id)->where('trado_id', $data['trado_id'])->lockForUpdate()->first();
        if ($absensiSupirDetail) {
            $absensiSupirDetail->delete();
        }


        $absensiSupirDetail = AbsensiSupirDetail::processStore($AbsensiSupirHeader,[
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

        // dd($AbsensiSupirDetail);

        $absensiSupirDetail = AbsensiSupirDetail::processStore($AbsensiSupirHeader,[
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
    }
        
       
        
}
