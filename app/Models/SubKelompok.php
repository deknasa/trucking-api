<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class SubKelompok extends MyModel
{
    use HasFactory;

    protected $table = 'subkelompok';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
    public function cekvalidasihapus($id)
    {

        $stok = DB::table('stok')
            ->from(
                DB::raw("stok as a with (readuncommitted)")
            )
            ->select(
                'a.subkelompok_id'
            )
            ->where('a.subkelompok_id', '=', $id)
            ->first();
        if (isset($stok)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Stok',
            ];


            goto selesai;
        }
        $kategori = DB::table('kategori')
            ->from(
                DB::raw("kategori as a with (readuncommitted)")
            )
            ->select(
                'a.subkelompok_id'
            )
            ->where('a.subkelompok_id', '=', $id)
            ->first();
        if (isset($kategori)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Kategori',
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

        $query = DB::table($this->table)->select(
            'subkelompok.id',
            'subkelompok.kodesubkelompok',
            'subkelompok.keterangan',
            'kelompok.keterangan as kelompok_id',
            'parameter.memo as statusaktif',
            'subkelompok.modifiedby',
            'subkelompok.created_at',
            'subkelompok.updated_at',
            DB::raw("'Laporan Sub Kelompok' as judulLaporan"),
            DB::raw("'" . $getJudul->text . "' as judul"),
            DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :".auth('api')->user()->name."' as usercetak")
        )
            ->leftJoin('kelompok', 'subkelompok.kelompok_id', '=', 'kelompok.id')
            ->leftJoin('parameter', 'subkelompok.statusaktif', '=', 'parameter.id');

        if (request()->kelompok) {
            $query->where('subkelompok.kelompok_id','=',request()->kelompok);
        }
            
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->nullable();
            $table->string('statusaktifnama')->nullable();
        });

        $statusaktif = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id',
                'text'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('default', '=', 'YA')
            ->first();
        DB::table($tempdefault)->insert(["statusaktif" => $statusaktif->id,"statusaktifnama" => $statusaktif->text]);

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusaktifnama',
            );

        $data = $query->first();
        // dd($data);
        return $data;
    }
    function findAll($id) {
        return $subKelompok = SubKelompok::select('subkelompok.*', 'kelompok.keterangan as kelompok','parameter.text as statusaktifnama',)
        ->leftJoin('kelompok', 'subkelompok.kelompok_id', 'kelompok.id')
        ->leftJoin('parameter', 'subkelompok.statusaktif', '=', 'parameter.id')
        ->where('subkelompok.id', $id)->first();
    }

    public function selectColumns($query)
    { //sesuaikan dengan createtemp

        return $query->select(
            DB::raw(
                "$this->table.id,
            $this->table.kodesubkelompok,
            $this->table.keterangan,

            'kelompok.keterangan as kelompok_id',
            'parameter.text as statusaktif',

            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )

        )
            ->leftJoin('kelompok', 'subkelompok.kelompok_id', '=', 'kelompok.id')
            ->leftJoin('parameter', 'subkelompok.statusaktif', '=', 'parameter.id');
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kodesubkelompok', 50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('kelompok_id')->nullable();
            $table->string('statusaktif', 500)->nullable();


            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'kodesubkelompok', 'keterangan', 'kelompok_id', 'statusaktif', 'modifiedby', 'created_at', 'updated_at'], $models);


        return  $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'kelompok_id') {
            return $query->orderBy('kelompok.keterangan', $this->params['sortOrder']);
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
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'kelompok_id') {
                            $query = $query->where('kelompok.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'kelompokid') {
                            $query = $query->where('kelompok.id', '=', "$filters[data]");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'kelompok_id') {
                            $query = $query->orWhere('kelompok.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

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

    public function processStore(array $data): SubKelompok
    {
        $subKelompok = new SubKelompok();
        $subKelompok->kodesubkelompok = $data['kodesubkelompok'];
        $subKelompok->keterangan = $data['keterangan'] ?? '';
        $subKelompok->kelompok_id = $data['kelompok_id'];
        $subKelompok->statusaktif = $data['statusaktif'];
        $subKelompok->modifiedby = auth('api')->user()->name;
        $subKelompok->info = html_entity_decode(request()->info);


        if (!$subKelompok->save()) {
            throw new \Exception("Error storing service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($subKelompok->getTable()),
            'postingdari' => 'ENTRY PARAMETER',
            'idtrans' => $subKelompok->id,
            'nobuktitrans' => $subKelompok->id,
            'aksi' => 'ENTRY',
            'datajson' => $subKelompok->toArray(),
            'modifiedby' => $subKelompok->modifiedby
        ]);

        return $subKelompok;
    }

    public function processUpdate(SubKelompok $subKelompok, array $data): SubKelompok
    {
        $subKelompok->kodesubkelompok = $data['kodesubkelompok'];
        $subKelompok->keterangan = $data['keterangan'] ?? '';
        $subKelompok->kelompok_id = $data['kelompok_id'];
        $subKelompok->statusaktif = $data['statusaktif'];
        $subKelompok->modifiedby = auth('api')->user()->name;
        $subKelompok->info = html_entity_decode(request()->info);

        if (!$subKelompok->save()) {
            throw new \Exception("Error update service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($subKelompok->getTable()),
            'postingdari' => 'EDIT PARAMETER',
            'idtrans' => $subKelompok->id,
            'nobuktitrans' => $subKelompok->id,
            'aksi' => 'EDIT',
            'datajson' => $subKelompok->toArray(),
            'modifiedby' => $subKelompok->modifiedby
        ]);

        return $subKelompok;
    }

    public function processDestroy($id): SubKelompok
    {
        $subKelompok = new SubKelompok();
        $subKelompok = $subKelompok->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($subKelompok->getTable()),
            'postingdari' => 'DELETE PARAMETER',
            'idtrans' => $subKelompok->id,
            'nobuktitrans' => $subKelompok->id,
            'aksi' => 'DELETE',
            'datajson' => $subKelompok->toArray(),
            'modifiedby' => $subKelompok->modifiedby
        ]);

        return $subKelompok;
    }
    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $subKelompok = SubKelompok::find($data['Id'][$i]);

            $subKelompok->statusaktif = $statusnonaktif->id;
            $subKelompok->modifiedby = auth('api')->user()->name;
            $subKelompok->info = html_entity_decode(request()->info);
            $aksi = $statusnonaktif->text;

            if ($subKelompok->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($subKelompok->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF SUB KELOMPOK',
                    'idtrans' => $subKelompok->id,
                    'nobuktitrans' => $subKelompok->id,
                    'aksi' => $aksi,
                    'datajson' => $subKelompok->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $subKelompok;
    }
}
