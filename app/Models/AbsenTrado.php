<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AbsenTrado extends MyModel
{
    use HasFactory;

    protected $table = 'absentrado';

    public function cekvalidasihapus($id)
    {
        // cek sudah ada absensi

        $absen = DB::table('absensisupirdetail')
            ->from(
                DB::raw("absensisupirdetail as a with (readuncommitted)")
            )
            ->select(
                'a.absen_id'
            )
            ->where('a.absen_id', '=', $id)
            ->first();
        if (isset($absen)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Absensi Supir',
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
        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $aktif = request()->aktif ?? '';
        $trado_id = request()->trado_id ?? '';
        $supir_id = request()->supir_id ?? '';
        $tglabsensi = request()->tglabsensi ?? '';
        $dari = request()->dari ?? '';

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'absentrado.id',
                'absentrado.kodeabsen',
                'absentrado.keterangan',
                'parameter.memo as statusaktif',
                'absentrado.modifiedby',
                'absentrado.created_at',
                'absentrado.updated_at',
                DB::raw("'Laporan Absen Trado' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'absentrado.statusaktif', '=', 'parameter.id');






        $this->filter($query);
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('absentrado.statusaktif', '=', $statusaktif->id);
        }

        if ($dari == 'mandorabsensisupir' && ($trado_id != 'null' && $supir_id != 'null')) {
            $isSupirSerap = (new SupirSerap())->isSupirSerap($trado_id,$supir_id,date('Y-m-d',strtotime($tglabsensi)));
            if ($isSupirSerap) {
                $parameter = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->select('text')
                    ->where('grp', '=', 'ABSENSI SUPIR SERAP')
                    ->get();
                
                $query->whereNotIn('absentrado.id', $parameter->toArray());
            }
            $isUsedTrip = (new SuratPengantar())->isUsedTrip($trado_id,$supir_id,date('Y-m-d',strtotime($tglabsensi)));
            if ($isUsedTrip) {
                $query->where('absentrado.id', 0);
                $query->orWhere('absentrado.id', 1);
            }

        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function findAll($id)
    {
        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"))
            ->select(
                'absentrado.id',
                'absentrado.kodeabsen',
                'absentrado.keterangan',

                'absentrado.statusaktif',
                'absentrado.memo',
                'parameter.text as statusaktifnama',
                'absentrado.modifiedby',
                'absentrado.created_at',
                'absentrado.updated_at',
            )
            ->where('absentrado.id', $id)
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'absentrado.statusaktif', '=', 'parameter.id');
        return $query->first();
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->string('statusaktifnama')->nullable();
            $table->unsignedBigInteger('statusaktif')->nullable();
        });

        $statusaktif = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'text',
                'id'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('default', '=', 'YA')
            ->first();
        DB::table($tempdefault)->insert(["statusaktif" => $statusaktif->id, "statusaktifnama" => $statusaktif->text]);

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktifnama',
                'statusaktif'
            );

        $data = $query->first();
        // dd($data);
        return $data;
    }

    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            DB::raw("
                $this->table.id,
                $this->table.kodeabsen,
                $this->table.keterangan,
                'parameter.text as statusaktif',
                $this->table.modifiedby,
                $this->table.created_at,
                $this->table.updated_at
            ")
        )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'absentrado.statusaktif', '=', 'parameter.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kodeabsen', 1000)->nullable();
            $table->string('keterangan', 1000)->nullable();
            $table->string('statusaktif', 1000)->nullable();
            $table->string('modifiedby')->default();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'kodeabsen', 'keterangan', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statusaktif') {
                                $query = $query->where('parameter.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'statusaktif') {
                                    $query = $query->orWhere('parameter.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else {
                                    // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function processStore(array $data, AbsenTrado $absenTrado): AbsenTrado
    {
        // $absenTrado = new AbsenTrado();
        $absenTrado->kodeabsen = $data['kodeabsen'];
        $absenTrado->keterangan = $data['keterangan'] ?? '';
        $absenTrado->statusaktif = $data['statusaktif'];
        $absenTrado->tas_id = $data['tas_id'] ?? '';
        $absenTrado->modifiedby = auth('api')->user()->name;
        $absenTrado->info = html_entity_decode(request()->info);

        $detailmemo = [];
        for ($i = 0; $i < count($data['key']); $i++) {
            $datadetailmemo = [
                $data['key'][$i] => $data['value'][$i],
            ];
            $detailmemo = array_merge($detailmemo, $datadetailmemo);
        }
        $absenTrado->memo = json_encode($detailmemo);

        if (!$absenTrado->save()) {
            throw new \Exception("Error storing service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($absenTrado->getTable()),
            'postingdari' => 'ENTRY ABSEN TRADO',
            'idtrans' => $absenTrado->id,
            'nobuktitrans' => $absenTrado->id,
            'aksi' => 'ENTRY',
            'datajson' => $absenTrado->toArray(),
            'modifiedby' => $absenTrado->modifiedby
        ]);

        return $absenTrado;
    }
    public function processUpdate(AbsenTrado $absentrado, array $data): AbsenTrado
    {

        $absentrado->kodeabsen = $data['kodeabsen'];
        $absentrado->keterangan = $data['keterangan'] ?? '';
        $absentrado->statusaktif = $data['statusaktif'];
        $absentrado->modifiedby = auth('api')->user()->name;
        $absentrado->info = html_entity_decode(request()->info);
        $detailmemo = [];
        for ($i = 0; $i < count($data['key']); $i++) {
            $datadetailmemo = [
                $data['key'][$i] => $data['value'][$i],
            ];
            $detailmemo = array_merge($detailmemo, $datadetailmemo);
        }
        $absentrado->memo = json_encode($detailmemo);


        if (!$absentrado->save()) {
            throw new \Exception("Error update service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($absentrado->getTable()),
            'postingdari' => 'EDIT ABSEN TRADO',
            'idtrans' => $absentrado->id,
            'nobuktitrans' => $absentrado->id,
            'aksi' => 'EDIT',
            'datajson' => $absentrado->toArray(),
            'modifiedby' => $absentrado->modifiedby
        ]);

        return $absentrado;
    }

    public function processDestroy(AbsenTrado $absenTrado): AbsenTrado
    {
        // $absenTrado = new AbsenTrado();
        $absenTrado = $absenTrado->lockAndDestroy($absenTrado->id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($absenTrado->getTable()),
            'postingdari' => 'DELETE ABSEN TRADO',
            'idtrans' => $absenTrado->id,
            'nobuktitrans' => $absenTrado->id,
            'aksi' => 'DELETE',
            'datajson' => $absenTrado->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $absenTrado;
    }

    public function getRekapAbsenTrado($id)
    {
        $query = DB::table("absensisupirheader")->from(DB::raw("absensisupirheader  a with (readuncommitted)"))
            ->select(DB::raw("max(isnull(c.keterangan,'Tanpa Isi Status')) as keterangan, 
            count(b.id) as jumlah"))
            ->join(DB::raw("absensisupirdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->leftjoin(DB::raw("absentrado c with (readuncommitted)"), 'b.absen_id', 'c.id')
            ->where('a.id', $id)
            ->groupBy('b.absen_id');


        return $query->get();
    }

    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $absenTrado = AbsenTrado::find($data['Id'][$i]);

            $absenTrado->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            if ($absenTrado->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($absenTrado->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF ABSEN TRADO',
                    'idtrans' => $absenTrado->id,
                    'nobuktitrans' => $absenTrado->id,
                    'aksi' => $aksi,
                    'datajson' => $absenTrado->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }


        return $absenTrado;
    }
}
