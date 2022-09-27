<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class Trado extends MyModel
{
    use HasFactory;

    protected $table = 'trado';

    public function absensiSupir()
    {
        return $this->belongsToMany(AbsensiSupirDetail::class);
    }

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->select(
            'trado.*',
            'parameter_statusaktif.text as statusaktif',
            'parameter_statusstandarisasi.text as statusstandarisasi',
            'parameter_statusmutasi.text as statusmutasi',
            'parameter_statusvalidasikendaraan.text as statusvalidasikendaraan',
            'mandor.namamandor as mandor_id',
        )
            ->leftJoin('parameter as parameter_statusaktif', 'trado.statusaktif', 'parameter_statusaktif.id')
            ->leftJoin('parameter as parameter_statusstandarisasi', 'trado.statusstandarisasi', 'parameter_statusstandarisasi.id')
            ->leftJoin('parameter as parameter_statusmutasi', 'trado.statusmutasi', 'parameter_statusmutasi.id')
            ->leftJoin('parameter as parameter_statusvalidasikendaraan', 'trado.statusvalidasikendaraan', 'parameter_statusvalidasikendaraan.id')
            ->leftJoin('mandor', 'trado.mandor_id', 'mandor.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function selectColumns($query)
    { //sesuaikan dengan createtemp

        return $query->select(
            DB::raw(
                "$this->table.id,           
                $this->table.keterangan,            
                'parameter_statusaktif.text as statusaktif',
                $this->table.kmawal,
                $this->table.kmakhirgantioli,
                $this->table.tglakhirgantioli1,
                $this->table.tglstnkmati1,
                $this->table.tglasuransimati1,
                $this->table.tahun,
                $this->table.akhirproduksi,
                $this->table.merek,
                $this->table.norangka,
                $this->table.nomesin,
                $this->table.nama,
                $this->table.nostnk,
                $this->table.alamatstnk,
                $this->table.modifiedby,
                $this->table.tglstandarisasi,
                $this->table.tglserviceopname,
                'parameter_statusstandarisasi.text as statusstandarisasi',
                $this->table.keteranganprogressstandarisasi;
                $this->table.statusjenisplat,
                $this->table.tglspeksimati,
                $this->table.tglpajakstnk,
                $this->table.tglgantiakiterakhir,
                'parameter_statusmutasi.text as statusmutasi',
                'parameter_statusvalidasikendaraan.text as statusvalidasikendaraan',
                $this->table.tipe,
                $this->table.jenis,
                $this->table.isisilinder,
                $this->table.warna,
                $this->table.jenisbahanbakar,
                $this->table.jumlahsumbu,
                $this->table.jumlahroda,
                $this->table.model,
                $this->table.nobpkb,
                $this->table.statusmobilstoring,
                'mandor.namamandor as mandor_id',
                $this->table.jumlahbanserap,
                $this->table.statusappeditban,
                $this->table.statuslewatvalidasi,
                $this->table.photostnk,
                $this->table.photobpkb,
                $this->table.phototrado,
                
               $this->table.modifiedby,
               $this->table.created_at,
               $this->table.updated_at,
               $this->table.statusformat"
            )

        )

            ->leftJoin('parameter as parameter_statusaktif', 'trado.statusaktif', 'parameter_statusaktif.id')
            ->leftJoin('parameter as parameter_statusstandarisasi', 'trado.statusstandarisasi', 'parameter_statusstandarisasi.id')
            ->leftJoin('parameter as parameter_statusmutasi', 'trado.statusmutasi', 'parameter_statusmutasi.id')
            ->leftJoin('parameter as parameter_statusvalidasikendaraan', 'trado.statusvalidasikendaraan', 'parameter_statusvalidasikendaraan.id')
            ->leftJoin('mandor', 'trado.mandor_id', 'mandor.id');
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->longText('keterangan')->default('');
            $table->integer('statusaktif')->length(11)->default(0);
            $table->double('kmawal', 15, 2)->default(0);
            $table->double('kmakhirgantioli', 15, 2)->default(0);
            $table->date('tglakhirgantioli')->default('1900/1/1');
            $table->date('tglstnkmati')->default('1900/1/1');
            $table->date('tglasuransimati')->default('1900/1/1');
            $table->string('tahun', 40)->default('');
            $table->string('akhirproduksi', 40)->default('');
            $table->string('merek', 40)->default('');
            $table->string('norangka', 40)->default('');
            $table->string('nomesin', 40)->default('');
            $table->string('nama', 40)->default('');
            $table->string('nostnk', 30)->default('');
            $table->string('alamatstnk', 30)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->date('tglstandarisasi')->default('1900/1/1');
            $table->date('tglserviceopname')->default('1900/1/1');
            $table->integer('statusstandarisasi')->length(11)->default(0);
            $table->string('keteranganprogressstandarisasi', 100)->default('');
            $table->integer('statusjenisplat')->length(11)->default(0);
            $table->date('tglspeksimati')->default('1900/1/1');
            $table->date('tglpajakstnk')->default('1900/1/1');
            $table->date('tglgantiakiterakhir')->default('1900/1/1');
            $table->integer('statusmutasi')->length(11)->default(0);
            $table->integer('statusvalidasikendaraan')->length(11)->default(0);
            $table->string('tipe', 30)->default('');
            $table->string('jenis', 30)->default('');
            $table->integer('isisilinder')->length(11)->default(0);
            $table->string('warna', 30)->default('');
            $table->string('jenisbahanbakar', 30)->default('');
            $table->integer('jumlahsumbu')->length(11)->default(0);
            $table->integer('jumlahroda')->length(11)->default(0);
            $table->string('model', 50)->default('');
            $table->string('nobpkb', 50)->default('');
            $table->integer('statusmobilstoring')->length(11)->default(0);
            $table->unsignedBigInteger('mandor_id')->default(0);
            $table->integer('jumlahbanserap')->length(11)->default(0);
            $table->integer('statusappeditban')->length(11)->default(0);
            $table->integer('statuslewatvalidasi')->length(11)->default(0);
            $table->string('photostnk', 1500)->default('');
            $table->string('photobpkb', 1500)->default('');
            $table->string('phototrado', 1500)->default('');

            $table->string('modifiedby', 50)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->bigInteger('statusformat')->default('');
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'keterangan', 'statusaktif', 'kmawal', 'kmakhirgantioli', 'tglakhirgantioli', 'tglstnkmati', 'tglasuransimati', 'tahun', 'akhirproduksi', 'merek', 'norangka', 'nomesin', 'nama', 'nostnk', 'alamatstnk', 'modifiedby', 'tglstandarisasi', 'tglserviceopname', 'statusstandarisasi', 'keteranganprogressstandarisasi', 'statusjenisplat', 'tglspeksimati', 'tglpajakstnk', 'tglgantiakiterakhir', 'statusmutasi', 'statusvalidasikendaraan', 'tipe', 'jenis', 'isisilinder', 'warna', 'jenisbahanbakar', 'jumlahsumbu', 'jumlahroda', 'model', 'nobpkb', 'statusmobilstoring', 'mandor_id', 'jumlahbanserap', 'statusappeditban', 'statuslewatvalidasi', 'photostnk', 'photobpkb', 'phototrado', 'modifiedby', 'created_at', 'updated_at', 'statusformat'], $models);


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
                            $query = $query->where('parameter_statusaktif.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusstandarisasi') {
                            $query = $query->where('parameter_statusstandarisasi.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusmutasi') {
                            $query = $query->where('parameter_statusmutasi.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusvalidasikendaraan') {
                            $query = $query->where('parameter_statusvalidasikendaraan.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'mandor_id') {
                            $query = $query->where('mandor.namamandor', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter_statusaktif.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusstandarisasi') {
                            $query = $query->orWhere('parameter_statusstandarisasi.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusmutasi') {
                            $query = $query->orWhere('parameter_statusmutasi.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statusvalidasikendaraan') {
                            $query = $query->orWhere('parameter_statusvalidasikendaraan.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'mandor_id') {
                            $query = $query->orWhere('mandor.namamandor', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
}
