<?php

namespace App\Models;

use App\Helpers\App;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class Stok extends MyModel
{
    use HasFactory;

    protected $table = 'stok';

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

        $pengeluaranStok = DB::table('pengeluaranstokdetail')
            ->from(
                DB::raw("pengeluaranstokdetail as a with (readuncommitted)")
            )
            ->select(
                'a.stok_id'
            )
            ->where('a.stok_id', '=', $id)
            ->first();
        if (isset($pengeluaranStok)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pengeluaran Stok',
            ];


            goto selesai;
        }

        $penerimaanStok = DB::table('penerimaanstokdetail')
            ->from(
                DB::raw("penerimaanstokdetail as a with (readuncommitted)")
            )
            ->select(
                'a.stok_id'
            )
            ->where('a.stok_id', '=', $id)
            ->first();
        if (isset($penerimaanStok)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan Stok',
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
        $penerimaanstok_id = request()->penerimaanstok_id ?? '';
        $penerimaanstokheader_nobukti = request()->penerimaanstokheader_nobukti ?? '';


        $query = DB::table($this->table)->select(
            'stok.id',
            'stok.namastok',
            'parameter.memo as statusaktif',
            'stok.qtymin',
            'stok.qtymax',
            'stok.keterangan',
            'stok.gambar',
            'stok.namaterpusat',
            'stok.modifiedby',
            'jenistrado.keterangan as jenistrado',
            'kelompok.kodekelompok as kelompok',
            'subkelompok.kodesubkelompok as subkelompok',
            'kategori.kodekategori as kategori',
            'merk.keterangan as merk',
            'stok.created_at',
            'stok.updated_at',
            DB::raw("'Laporan Stok' as judulLaporan"),
            DB::raw("'" . $getJudul->text . "' as judul"),
            DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :".auth('api')->user()->name."' as usercetak")
        )
            ->leftJoin('jenistrado', 'stok.jenistrado_id', 'jenistrado.id')
            ->leftJoin('kelompok', 'stok.kelompok_id', 'kelompok.id')
            ->leftJoin('subkelompok', 'stok.subkelompok_id', 'subkelompok.id')
            ->leftJoin('kategori', 'stok.kategori_id', 'kategori.id')
            ->leftJoin('parameter', 'stok.statusaktif', 'parameter.id')
            ->leftJoin('merk', 'stok.merk_id', 'merk.id');




        $this->filter($query);
        // dd($query->toSql());
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('stok.statusaktif', '=', $statusaktif->id);
        }

        if ($penerimaanstokheader_nobukti) {
            $spb = Parameter::where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();
            if ($spb->text == $penerimaanstok_id) {
                $query->leftJoin('penerimaanstokdetail', 'stok.id', 'penerimaanstokdetail.stok_id')
                    ->where('penerimaanstokdetail.nobukti', $penerimaanstokheader_nobukti);
            }
        }

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->paginate($query);
        // dd($query->toSql());
        $data = $query->get();

        return $data;
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->nullable();
            $table->unsignedBigInteger('statusreuse')->nullable();
            $table->unsignedBigInteger('statusban')->nullable();
        });

        $statusaktif = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('default', '=', 'YA')
            ->first();

        $statusreuse = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS REUSE')
            ->where('subgrp', '=', 'STATUS REUSE')
            ->where('default', '=', 'YA')
            ->first();

        $statusban = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS BAN')
            ->where('subgrp', '=', 'STATUS BAN')
            ->where('default', '=', 'YA')
            ->first();
            
            DB::table($tempdefault)->insert([
                "statusaktif" => $statusaktif->id,
                "statusreuse" => $statusreuse->id,
                "statusban" => $statusban->id
            ]);

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusreuse',
                'statusban'
            );

        $data = $query->first();
        // dd($data);
        return $data;
    }
    public function findAll($id)
    {
        $data = DB::table('stok')->select(
            'stok.id',
            'stok.namastok',
            'stok.statusaktif',
            'stok.qtymin',
            'stok.qtymax',
            'stok.keterangan',
            'stok.gambar',
            'stok.namaterpusat',
            'stok.modifiedby',
            'stok.jenistrado_id',
            'stok.kelompok_id',
            'stok.subkelompok_id',
            'stok.kategori_id',
            'stok.merk_id',
            'stok.statusban',
            'jenistrado.keterangan as jenistrado',
            'kelompok.kodekelompok as kelompok',
            'subkelompok.kodesubkelompok as subkelompok',
            'kategori.kodekategori as kategori',
            'merk.keterangan as merk',
        )
            ->leftJoin('jenistrado', 'stok.jenistrado_id', 'jenistrado.id')
            ->leftJoin('kelompok', 'stok.kelompok_id', 'kelompok.id')
            ->leftJoin('subkelompok', 'stok.subkelompok_id', 'subkelompok.id')
            ->leftJoin('kategori', 'stok.kategori_id', 'kategori.id')
            ->leftJoin('merk', 'stok.merk_id', 'merk.id')
            ->where('stok.id', $id)
            ->first();

        return $data;
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->unsignedBigInteger('jenistrado_id')->nullable();
            $table->unsignedBigInteger('kelompok_id')->nullable();
            $table->unsignedBigInteger('subkelompok_id')->nullable();
            $table->unsignedBigInteger('kategori_id')->nullable();
            $table->unsignedBigInteger('merk_id')->nullable();
            $table->string('namastok', 200)->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->double('qtymin', 15, 2)->nullable();
            $table->double('qtymax', 15, 2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->longText('gambar')->nullable();
            $table->longText('namaterpusat')->nullable();

            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->select(
            'stok.id',
            'stok.jenistrado_id',
            'stok.kelompok_id',
            'stok.subkelompok_id',
            'stok.kategori_id',
            'stok.merk_id',
            'stok.namastok',
            'stok.statusaktif',
            'stok.qtymin',
            'stok.qtymax',
            'stok.keterangan',
            'stok.gambar',
            'stok.namaterpusat',
            'stok.modifiedby',
            'stok.created_at',
            'stok.updated_at'
        )
        ->leftJoin('jenistrado', 'stok.jenistrado_id', 'jenistrado.id')
        ->leftJoin('kelompok', 'stok.kelompok_id', 'kelompok.id')
        ->leftJoin('subkelompok', 'stok.subkelompok_id', 'subkelompok.id')
        ->leftJoin('kategori', 'stok.kategori_id', 'kategori.id')
        ->leftJoin('parameter', 'stok.statusaktif', 'parameter.id')
        ->leftJoin('merk', 'stok.merk_id', 'merk.id');
        $query = $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing([
            'id',
            'jenistrado_id',
            'kelompok_id',
            'subkelompok_id',
            'kategori_id',
            'merk_id',
            'namastok',
            'statusaktif',
            'qtymin',
            'qtymax',
            'keterangan',
            'gambar',
            'namaterpusat',
            'modifiedby',
            'created_at',
            'updated_at'
        ], $models);

        return  $temp;
    }


    public function selectColumns($query)
    {
       
        return $query->select(
            DB::raw(
                "$this->table.id,
                $this->table.namastok,
                parameter.memo as statusaktif,
                $this->table.qtymin,
                $this->table.qtymax,
                $this->table.keterangan,
                $this->table.gambar,
                $this->table.namaterpusat,
                $this->table.modifiedby,
                jenistrado.keterangan as jenistrado,
                kelompok.kodekelompok as kelompok,
                subkelompok.kodesubkelompok as subkelompok,
                kategori.kodekategori as kategori,
                merk.keterangan as merk,
                $this->table.created_at,
                $this->table.updated_at"
            )
        )
        ->leftJoin('jenistrado', 'stok.jenistrado_id', 'jenistrado.id')
        ->leftJoin('kelompok', 'stok.kelompok_id', 'kelompok.id')
        ->leftJoin('subkelompok', 'stok.subkelompok_id', 'subkelompok.id')
        ->leftJoin('kategori', 'stok.kategori_id', 'kategori.id')
        ->leftJoin('parameter', 'stok.statusaktif', 'parameter.id')
        ->leftJoin('merk', 'stok.merk_id', 'merk.id');
    }

    
    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'jenistrado') {
            return $query->orderBy('jenistrado.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'kelompok') {
            return $query->orderBy('kelompok.kodekelompok', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'subkelompok') {
            return $query->orderBy('subkelompok.kodesubkelompok', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'kategori') {
            return $query->orderBy('kategori.kodekategori', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'merk') {
            return $query->orderBy('merk.keterangan', $this->params['sortOrder']);
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
                        if ($filters['field'] == 'jenistrado') {
                            $query = $query->where('jenistrado.keterangan', 'LIKE', "'%$filters[data]%'");
                        } else if ($filters['field'] == 'kelompok') {
                            $query = $query->where('kelompok.kodekelompok', 'LIKE', "'%$filters[data]%'");
                        } else if ($filters['field'] == 'subkelompok') {
                            $query = $query->where('subkelompok.kodesubkelompok', 'LIKE', "'%$filters[data]%'");
                        } else if ($filters['field'] == 'kategori') {
                            $query = $query->where('kategori.kodekategori', 'LIKE', "'%$filters[data]%'");
                        } else if ($filters['field'] == 'merk') {
                            $query = $query->where('merk.keterangan', 'LIKE', "'%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'jenistrado') {
                                $query = $query->orWhere('jenistrado.keterangan', 'LIKE', "'%$filters[data]%'");
                            } else if ($filters['field'] == 'kelompok') {
                                $query = $query->orWhere('kelompok.kodekelompok', 'LIKE', "'%$filters[data]%'");
                            } else if ($filters['field'] == 'subkelompok') {
                                $query = $query->orWhere('subkelompok.kodesubkelompok', 'LIKE', "'%$filters[data]%'");
                            } else if ($filters['field'] == 'kategori') {
                                $query = $query->orWhere('kategori.kodekategori', 'LIKE', "'%$filters[data]%'");
                            } else if ($filters['field'] == 'merk') {
                                $query = $query->orWhere('merk.keterangan', 'LIKE', "'%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function processStore(array $data): Stok
    {
        $stok = new stok();
        $stok->keterangan = $data['keterangan'];
        $stok->namastok = $data['namastok'];
        $stok->namaterpusat = $data['namaterpusat'];
        $stok->statusaktif = $data['statusaktif'];
        $stok->kelompok_id = $data['kelompok_id'];
        $stok->subkelompok_id = $data['subkelompok_id'];
        $stok->kategori_id = $data['kategori_id'];
        $stok->merk_id = $data['merk_id'] ?? 0;
        $stok->jenistrado_id = $data['jenistrado_id'] ?? 0;
        $stok->keterangan = $data['keterangan'] ?? '';
        $stok->qtymin = $data['qtymin'] ?? 0;
        $stok->qtymax = $data['qtymax'] ?? 0;
        $stok->statusreuse = $data['statusreuse'];
        $stok->statusban = $data['statusban'];
        $stok->statusservicerutin = $data['statusservicerutin'];
        $stok->vulkanisirawal = $data['vulkanisirawal'];
        $stok->hargabelimin = $data['hargabelimin'];
        $stok->hargabelimax = $data['hargabelimax'];
        $stok->modifiedby = auth('api')->user()->name;
        if ($data['gambar']) {
            $stok->gambar = $this->storeFiles($data['gambar'], 'stok');
        } else {
            $stok->gambar = '';
        }

        if (!$stok->save()) {
            throw new \Exception("Error storing stok.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($stok->getTable()),
            'postingdari' => 'ENTRY STOK',
            'idtrans' => $stok->id,
            'nobuktitrans' => $stok->id,
            'aksi' => 'ENTRY',
            'datajson' => $stok->toArray(),
            'modifiedby' => $stok->modifiedby
        ]);


        return $stok;
    }
    public function processUpdate(Stok $stok, array $data): Stok
    {

        $stok->keterangan = $data['keterangan'];
        $stok->namastok = $data['namastok'];
        $stok->namaterpusat = $data['namaterpusat'];
        $stok->namaterpusat = $data['namaterpusat'];
        $stok->statusaktif = $data['statusaktif'];
        $stok->kelompok_id = $data['kelompok_id'];
        $stok->subkelompok_id = $data['subkelompok_id'];
        $stok->kategori_id = $data['kategori_id'];
        $stok->merk_id =  $data['merk_id'] ?? 0;
        $stok->jenistrado_id = $data['jenistrado_id'] ?? 0;
        $stok->keterangan = $data['keterangan'] ?? '';
        $stok->qtymin = $data['qtymin'] ?? 0;
        $stok->qtymax = $data['qtymax'] ?? 0;
        $stok->statusban = $data['statusban'];
        $stok->statusservicerutin = $data['statusservicerutin'];
        $stok->hargabelimin = $data['hargabelimin'];
        $stok->hargabelimax = $data['hargabelimax'];
        $stok->modifiedby = auth('api')->user()->name;

        $statusPakai = $this->cekvalidasihapus($stok->id);
        if (!$statusPakai['kondisi']){
            $stok->statusreuse = $data['statusreuse'];
            $stok->vulkanisirawal = $data['vulkanisirawal'];
        }

        $this->deleteFiles($stok);
        if ($data['gambar']) {
            $stok->gambar = $this->storeFiles($data['gambar'], 'stok');
        } else {
            $stok->gambar = '';
        }
        if (!$stok->save()) {
            throw new \Exception("Error updating stok.");
        }


        (new LogTrail())->processStore([
            'namatabel' => strtoupper($stok->getTable()),
            'postingdari' => 'EDIT STOK',
            'idtrans' => $stok->id,
            'nobuktitrans' => $stok->id,
            'aksi' => 'ENTRY',
            'datajson' => $stok->toArray(),
            'modifiedby' => $stok->modifiedby
        ]);

        return $stok;
    }

    public function processDestroy($id): Stok
    {
        $stok = new Stok;
        $stok = $stok->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($stok->getTable()),
            'postingdari' => 'DELETE STOK',
            'idtrans' => $stok->id,
            'nobuktitrans' => $stok->id,
            'aksi' => 'DELETE',
            'datajson' => $stok->toArray(),
            'modifiedby' => $stok->modifiedby
        ]);

        return $stok;
    }




    private function storeFiles(array $files, string $destinationFolder): string
    {
        $storedFiles = [];

        foreach ($files as $file) {
            $originalFileName = $file->hashName();
            $storedFile = Storage::putFileAs($destinationFolder, $file, $originalFileName);
            $resizedFiles = App::imageResize(storage_path("app/$destinationFolder/"), storage_path("app/$storedFile"), $originalFileName);

            $storedFiles[] = $originalFileName;
        }

        return json_encode($storedFiles);
    }


    private function deleteFiles(Stok $stok)
    {
        $sizeTypes = ['', 'medium_', 'small_'];

        $relatedPhotoStok = [];
        $photoStok = json_decode($stok->gambar, true);
        if ($photoStok) {
            foreach ($photoStok as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoStok[] = "stok/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoStok);
        }
    }
}
