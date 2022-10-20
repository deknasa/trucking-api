<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class Supir extends MyModel
{
    use HasFactory;

    protected $table = 'supir';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function zona()
    {
        return $this->belongsTo(Zona::class, 'zona_id');
    }

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->select(
            'supir.id',
            'supir.namasupir',
            'supir.tgllahir',
            'supir.alamat',
            'supir.kota',
            'supir.telp',
            'parameter.text as statusaktif',
            'supir.nominaldepositsa',
            // 'supir.tglmasuk',
            'supirlama.namasupir as supirold_id',
            'supir.nosim',
            'supir.tglterbitsim',
            'supir.tglexpsim',
            'supir.keterangan',
            'supir.noktp',
            'supir.nokk',
            'supir.statusadaupdategambar',
            'supir.statuslluarkota',
            'supir.statuszonatertentu',
            'zona.keterangan as zona_id',
            'supir.photosupir',
            'supir.photoktp',
            'supir.photosim',
            'supir.photokk',
            'supir.photoskck',
            'supir.photodomisili',
            'supir.keteranganresign',
            'statusblacklist.text as statusblacklist',
            'supir.tglberhentisupir',
            'supir.modifiedby',
            'supir.created_at',
            'supir.updated_at'
        )
            ->leftJoin('zona', 'supir.zona_id', 'zona.id')
            ->leftJoin('parameter', 'supir.statusaktif', '=', 'parameter.id')
            ->leftJoin('parameter as statusadaupdategambar', 'supir.statusadaupdategambar', '=', 'statusadaupdategambar.id')
            ->leftJoin('parameter as statusluarkota', 'supir.statuslluarkota', '=', 'statusluarkota.id')
            ->leftJoin('parameter as statuszonatertentu', 'supir.statuszonatertentu', '=', 'statuszonatertentu.id')
            ->leftJoin('parameter as statusblacklist', 'supir.statusblacklist', '=', 'statusblacklist.id')
            ->leftJoin('supir as supirlama', 'supir.supirold_id', '=', 'supirlama.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function find($id)
    {
        $query = DB::table('supir')->select(
            'supir.id',
            'supir.namasupir',
            'supir.tgllahir',
            'supir.alamat',
            'supir.kota',
            'supir.telp',
            'supir.statusaktif',
            'supir.nominaldepositsa',
            // 'supir.tglmasuk',
            'supir.supirold_id',
            'supir.nosim',
            'supir.tglterbitsim',
            'supir.tglexpsim',
            'supir.keterangan',
            'supir.noktp',
            'supir.nokk',
            'supir.statusadaupdategambar',
            'supir.statuslluarkota',
            'supir.statuszonatertentu',
            'supir.zona_id',
            'supir.photosupir',
            'supir.photoktp',
            'supir.photosim',
            'supir.photokk',
            'supir.photoskck',
            'supir.photodomisili',
            'supir.keteranganresign',
            'supir.statusblacklist',
            'supir.tglberhentisupir',
            'supir.modifiedby',
            'supir.created_at',
            'supir.updated_at'
        )

            ->where('supir.id', $id);

        $data = $query->first();
        return $data;
    }

    public function selectColumns($query)
    { //sesuaikan dengan createtemp
        return $query->select(
            DB::raw(
                "$this->table.id,
                $this->table.namasupir,
                $this->table.alamat,
                $this->table.kota,
                $this->table.telp,
                $this->table.statusaktif,
                supir.nominaldepositsa,
                $this->table.depositke,
                $this->table.tglmasuk,
                $this->table.nominalpinjamansaldoawal,
                supir.namasupir as supirold_id,
                $this->table.tglexpsim,
                $this->table.nosim,
                $this->table.keterangan,
                $this->table.noktp,
                $this->table.nokk,
                $this->table.statusadaupdategambar,
                $this->table.statuslluarkota,
                $this->table.statuszonatertentu,
                $this->table.zona_id,
                $this->table.angsuranpinjaman,
                $this->table.plafondeposito,
                $this->table.photosupir,
                $this->table.photoktp, 
                $this->table.photosim, 
                $this->table.photokk, 
                $this->table.photoskck, 
                $this->table.photodomisili, 
                $this->table.keteranganresign,
                $this->table.statusblacklist,
                $this->table.tglberhentisupir,
                $this->table.tgllahir,
                $this->table.tglterbitsim,

            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )
            
            )

            ->leftJoin('zona', 'supir.zona_id', 'zona.id')
            ->leftJoin('parameter as statusadaupdategambar', 'supir.statusadaupdategambar', '=', 'statusadaupdategambar.id')
            ->leftJoin('parameter as statusluarkota', 'supir.statuslluarkota', '=', 'statusluarkota.id')
            ->leftJoin('parameter as statuszonatertentu', 'supir.statuszonatertentu', '=', 'statuszonatertentu.id')
            ->leftJoin('parameter as statusblacklist', 'supir.statusblacklist', '=', 'statusblacklist.id')
            ->leftJoin('supir as supirlama', 'supir.supirold_id', '=', 'supirlama.id');
            
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('namasupir', 100)->default('');
            $table->string('alamat', 100)->default('');
            $table->string('kota', 100)->default('');
            $table->string('telp', 30)->default('');
            $table->integer('statusaktif')->length(11)->default('0');
            $table->double('nominaldepositsa', 15, 2)->default(0);
            $table->double('depositke', 15, 2)->default(0);
            $table->date('tglmasuk')->default('1900/1/1');
            $table->double('nominalpinjamansaldoawal', 15, 2)->default(0);
            $table->string('supirold_id')->default(0);
            $table->date('tglexpsim')->default('1900/1/1');
            $table->string('nosim', 30)->default('');
            $table->longText('keterangan')->default('');
            $table->string('noktp', 30)->default('');
            $table->string('nokk', 30)->default('');
            $table->string('statusadaupdategambar', 300)->default('')->nullable();
            $table->string('statuslluarkota', 300)->default('')->nullable();
            $table->string('statuszonatertentu', 300)->default('')->nullable();
            $table->unsignedBigInteger('zona_id')->default(0);
            $table->double('angsuranpinjaman', 15, 2)->default(0);
            $table->double('plafondeposito', 15, 2)->default(0);
            $table->string('photosupir', 4000)->default('');
            $table->string('photoktp', 4000)->default('');
            $table->string('photosim', 4000)->default('');
            $table->string('photokk', 4000)->default('');
            $table->string('photoskck', 4000)->default('');
            $table->string('photodomisili', 4000)->default('');
            $table->longText('keteranganresign')->default('');
            $table->string('statusblacklist')->default(0);
            $table->date('tglberhentisupir')->default('1900/1/1');
            $table->date('tgllahir')->default('1900/1/1');
            $table->date('tglterbitsim')->default('1900/1/1');

            $table->string('modifiedby', 50)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing([
            'id',
            'namasupir',
            'alamat',
            'kota',
            'telp',
            'statusaktif',
            'nominaldepositsa',
            'depositke',
            'tglmasuk',
            'nominalpinjamansaldoawal',
            'supirold_id',
            'tglexpsim',
            'nosim',
            'keterangan',
            'noktp',
            'nokk',
            'statusadaupdategambar',
            'statuslluarkota',
            'statuszonatertentu',
            'zona_id',
            'angsuranpinjaman',
            'plafondeposito',
            'photosupir',
            'photoktp',
            'photosim',
            'photokk',
            'photoskck',
            'photodomisili',
            'keteranganresign',
            'statusblacklist',
            'tglberhentisupir',
            'tgllahir',
            'tglterbitsim',
            'modifiedby',
            'created_at',
            'updated_at'
        ], $models);


        return  $temp;
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
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', '=', $filters['data']);
                        } elseif ($filters['field'] == 'zona_id') {
                            $query = $query->where('zona.zona', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'statusluarkota') {
                            $query = $query->where('statusluarkota.text', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }

                        // else if ($filters['field'] == 'statusapproval') {
                        //     $query = $query->where('parameter_statusapproval.text', '=', $filters['data']);
                        // } else if ($filters['field'] == 'statustas') {
                        //     $query = $query->where('parameter_statustas.text', '=', $filters['data']);
                        // } 
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', '=', $filters['data']);
                        } elseif ($filters['field'] == 'zona_id') {
                            $query = $query->orWhere('zona.zona', 'LIKE', "%$filters[data]%");
                        } elseif ($filters['field'] == 'statusluarkota') {
                            $query = $query->orWhere('statusluarkota.text', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }

                        // else if ($filters['field'] == 'statusapproval') {
                        //     $query = $query->orWhere('parameter_statusapproval.text', '=', $filters['data']);
                        // } else if ($filters['field'] == 'statustas') {
                        //     $query = $query->orWhere('parameter_statustas.text', '=', $filters['data']);
                        // } 
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
}
