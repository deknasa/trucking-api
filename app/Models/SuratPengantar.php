<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SuratPengantar extends MyModel
{
    use HasFactory;

    protected $table = 'suratpengantar';

    protected $casts = [
        'tglbukti' => 'date:d-m-Y',
        'tglsp' => 'date:d-m-Y',
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function suratpengantarBiaya()
    {
        return $this->hasMany(SuratPengantarBiayaTambahan::class, 'suratpengantar_id');
    }

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->select(
            'suratpengantar.id',
            'suratpengantar.nobukti',
            'suratpengantar.tglbukti',
            'pelanggan.namapelanggan as pelanggan_id',

            // 'upahsupir.id as upahsupir_id',
            'suratpengantar.upah_id',

            'suratpengantar.keterangan',
            'suratpengantar.nourutorder',
            'kotadari.keterangan as dari_id',
            'kotasampai.keterangan as sampai_id',
            'suratpengantar.container_id',
            'suratpengantar.nocont',
            'suratpengantar.nocont2',
            'statuscontainer.keterangan as statuscontainer_id',
            'trado.keterangan as trado_id',
            'supir.namasupir as supir_id',
            'suratpengantar.nojob',
            'suratpengantar.nojob2',
            'statuslongtrip.text as statuslongtrip',
            'agen.namaagen as agen_id',
            'jenisorder.keterangan as jenisorder_id',
            'statusperalihan.text as statusperalihan',
            'tarif.tujuan as tarif_id',
            'suratpengantar.nominalperalihan',
            'suratpengantar.persentaseperalihan',
            'suratpengantar.nosp',
            'suratpengantar.tglsp',
            'suratpengantar.statusritasiomset',
            'suratpengantar.cabang_id',
            'suratpengantar.komisisupir',
            'suratpengantar.tolsupir',
            'suratpengantar.jarak',
            'suratpengantar.nosptagihlain',
            'suratpengantar.nilaitagihlain',
            'suratpengantar.tujuantagih',
            'suratpengantar.liter',
            'suratpengantar.nominalstafle',
            'suratpengantar.statusnotif',
            'suratpengantar.statusoneway',
            'suratpengantar.statusedittujuan',
            'suratpengantar.upahbongkardepo',
            'suratpengantar.upahmuatdepo',
            'suratpengantar.hargatol',
            'suratpengantar.qtyton',
            'suratpengantar.totalton',
            'suratpengantar.mandorsupir_id',
            'suratpengantar.mandortrado_id',
            'suratpengantar.statustrip',
            'suratpengantar.notripasal',
            'suratpengantar.tgldoor',
            'suratpengantar.statusdisc',
            'suratpengantar.gajisupir',
            'suratpengantar.gajikenek',
            'suratpengantar.modifiedby',
            'suratpengantar.created_at',
            'suratpengantar.updated_at'

        )->join('kota as kotadari', 'kotadari.id', '=', 'suratpengantar.dari_id')
            ->join('kota as kotasampai', 'kotasampai.id', '=', 'suratpengantar.sampai_id')

            ->leftJoin('pelanggan', 'suratpengantar.pelanggan_id', 'pelanggan.id')
            ->leftJoin('upahsupir', 'suratpengantar.upahsupir_id', 'upahsupir.id');

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
        // dd('find');
        $data = DB::table('suratpengantar')->select(
            'suratpengantar.id',
            'suratpengantar.nobukti',
            'suratpengantar.tglbukti',
            'pelanggan.namapelanggan as pelanggan',
            'pelanggan.id as pelanggan_id',
            'suratpengantar.keterangan',
            'suratpengantar.nourutorder',

            'kotadari.id as dari_id',
            'kotadari.keterangan as dari',
            'kotasampai.id as sampai_id',
            'kotasampai.keterangan as sampai',

            'suratpengantar.container_id',
            'suratpengantar.nocont',
            'suratpengantar.nocont2',
            'suratpengantar.statuscontainer_id',
            'suratpengantar.trado_id',
            'suratpengantar.supir_id',
            'suratpengantar.nojob',
            'suratpengantar.nojob2',
            'suratpengantar.statuslongtrip',
            'suratpengantar.omset',
            'suratpengantar.discount',
            'suratpengantar.totalomset',
            'suratpengantar.gajisupir',
            'suratpengantar.gajikenek',
            'suratpengantar.agen_id',
            'suratpengantar.jenisorder_id',
            'suratpengantar.statusperalihan',
            'suratpengantar.tarif_id',
            'suratpengantar.nominalperalihan',
            'suratpengantar.persentaseperalihan',
            'suratpengantar.nosp',
            'suratpengantar.tglsp',
            'suratpengantar.statusritasiomset',
            'suratpengantar.cabang_id',
            'suratpengantar.komisisupir',
            'suratpengantar.tolsupir',
            'suratpengantar.jarak',
            'suratpengantar.nosptagihlain',
            'suratpengantar.nilaitagihlain',
            'suratpengantar.tujuantagih',
            'suratpengantar.liter',
            'suratpengantar.nominalstafle',
            'suratpengantar.statusnotif',
            'suratpengantar.statusoneway',
            'suratpengantar.statusedittujuan',
            'suratpengantar.upahbongkardepo',
            'suratpengantar.upahmuatdepo',
            'suratpengantar.hargatol',
            'suratpengantar.qtyton',
            'suratpengantar.totalton',
            'suratpengantar.mandorsupir_id',
            'suratpengantar.mandortrado_id',
            'suratpengantar.statustrip',
            'suratpengantar.notripasal',
            'suratpengantar.tgldoor',
            'suratpengantar.statusdisc',

            'suratpengantar.modifiedby',
            'suratpengantar.created_at',
            'suratpengantar.updated_at'

        )->join('kota as kotadari', 'kotadari.id', '=', 'suratpengantar.dari_id')
            ->join('kota as kotasampai', 'kotasampai.id', '=', 'suratpengantar.sampai_id')

            ->leftJoin('pelanggan', 'suratpengantar.pelanggan_id', 'pelanggan.id')
            ->leftJoin('upahsupir', 'suratpengantar.upah_id', 'upahsupir.id')

            ->where('suratpengantar.id', $id)->first();

        return $data;
    }

    public function selectColumns($query)
    { //sesuaikan dengan createtemp
        return $query->select(
            DB::raw(
                "$this->table.id,
                $this->table.nobukti,
                $this->table.jobtrucking,
                $this->table.tglbukti,
                pelanggan.namapelanggan as pelanggan_id,
                $this->table.keterangan,
                $this->table.nourutorder,
                upahsupir.id as upah_id,
                kotadari.keterangan as dari_id,
                kotasampai.keterangan as sampai_id,
                $this->table.container_id,
                $this->table.nocont,
                $this->table.nocont2,
                $this->table.statuscontainer_id,
                $this->table.trado_id,
                $this->table.supir_id,
                $this->table.nojob,
                $this->table.nojob2,
                $this->table.statuslongtrip,
                $this->table.omset,
                $this->table.discount,
                $this->table.totalomset,
                $this->table.gajisupir,
                $this->table.gajikenek,
                $this->table.agen_id,
                $this->table.jenisorder_id,
                $this->table.statusperalihan,
                $this->table.tarif_id,
                $this->table.nominalperalihan,
                $this->table.persentaseperalihan,
                $this->table.biayatambahan_id,
                $this->table.nosp,
                $this->table.tglsp,
                $this->table.statusritasiomset,
                $this->table.cabang_id,
                $this->table.komisisupir,
                $this->table.tolsupir,
                $this->table.jarak,
                $this->table.nosptagihlain,
                $this->table.nilaitagihlain,
                $this->table.tujuantagih,
                $this->table.liter,
                $this->table.nominalstafle,
                $this->table.statusnotif,
                $this->table.statusoneway,
                $this->table.statusedittujuan,
                $this->table.upahbongkardepo,
                $this->table.upahmuatdepo,
                $this->table.hargatol,
                $this->table.qtyton,
                $this->table.totalton,
                $this->table.mandorsupir_id,
                $this->table.mandortrado_id,
                $this->table.statustrip,
                $this->table.notripasal,
                $this->table.tgldoor,
                $this->table.statusdisc,
                $this->table.statusformat,

            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )

        )
            ->join('kota as kotadari', 'kotadari.id', '=', 'suratpengantar.dari_id')
            ->join('kota as kotasampai', 'kotasampai.id', '=', 'suratpengantar.sampai_id')

            ->leftJoin('pelanggan', 'suratpengantar.pelanggan_id', 'pelanggan.id')
            ->leftJoin('upahsupir', 'suratpengantar.upah_id', 'upahsupir.id')

            // ->leftJoin('akunpusat', 'hutangbayarheader.coa', 'akunpusat.coa')
        ;
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 50)->unique();
            $table->string('jobtrucking', 50)->default('');
            $table->date('tglbukti')->default('1900/1/1');
            $table->string('pelanggan_id')->default('0');
            $table->longText('keterangan')->default('');
            $table->bigInteger('nourutorder')->default('0');
            $table->string('upah_id')->default('0');
            $table->string('dari_id')->default('0');
            $table->string('sampai_id')->default('0');
            $table->string('container_id')->default('0');
            $table->string('nocont', 50)->default('');
            $table->string('nocont2', 50)->default('');
            $table->string('statuscontainer_id')->default('0');
            $table->string('trado_id')->default('0');
            $table->string('supir_id')->default('0');
            $table->string('nojob', 50)->default('');
            $table->string('nojob2', 50)->default('');
            $table->integer('statuslongtrip')->length(11)->default('0');
            $table->decimal('omset', 15, 2)->default('0');
            $table->decimal('discount', 15, 2)->default('0');
            $table->decimal('totalomset', 15, 2)->default('0');
            $table->decimal('gajisupir', 15, 2)->default('0');
            $table->decimal('gajikenek', 15, 2)->default('0');
            $table->string('agen_id')->default('0');
            $table->string('jenisorder_id')->default('0');
            $table->integer('statusperalihan')->length(11)->default('0');
            $table->string('tarif_id')->default('0');
            $table->decimal('nominalperalihan', 15, 2)->default('0');
            $table->decimal('persentaseperalihan', 15, 2)->default('0');
            $table->string('biayatambahan_id')->default('0');
            $table->string('nosp', 50)->default('');
            $table->date('tglsp')->default('1900/1/1');
            $table->integer('statusritasiomset')->length(11)->default('0');
            $table->string('cabang_id')->default('0');
            $table->decimal('komisisupir', 15, 2)->default('0');
            $table->decimal('tolsupir', 15, 2)->default('0');
            $table->decimal('jarak', 15, 2)->default('0');
            $table->string('nosptagihlain', 50)->default('');
            $table->decimal('nilaitagihlain', 15, 2)->default('0');
            $table->string('tujuantagih', 50)->default('');
            $table->decimal('liter', 15, 2)->default('0');
            $table->decimal('nominalstafle', 15, 2)->default('0');
            $table->integer('statusnotif')->length(11)->default('0');
            $table->integer('statusoneway')->length(11)->default('0');
            $table->integer('statusedittujuan')->length(11)->default('0');
            $table->decimal('upahbongkardepo', 15, 2)->default('0');
            $table->decimal('upahmuatdepo', 15, 2)->default('0');
            $table->decimal('hargatol', 15, 2)->default('0');
            $table->decimal('qtyton', 15, 2)->default('0');
            $table->decimal('totalton', 15, 2)->default('0');
            $table->string('mandorsupir_id')->default('0');
            $table->string('mandortrado_id')->default('0');
            $table->integer('statustrip')->length(11)->default('0');
            $table->string('notripasal', 50)->default('');
            $table->date('tgldoor')->default('1900/1/1');
            $table->integer('statusdisc')->length(11)->default('0');
            $table->unsignedBigInteger('statusformat')->default(0);

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
            'id', 'nobukti', 'jobtrucking', 'tglbukti', 'pelanggan_id', 'keterangan', 'nourutorder', 'upah_id',
            'dari_id', 'sampai_id', 'container_id', 'nocont', 'nocont2', 'statuscontainer_id', 'trado_id', 'supir_id',
            'nojob', 'nojob2', 'statuslongtrip', 'omset', 'discount', 'totalomset', 'gajisupir', 'gajikenek', 'agen_id',
            'jenisorder_id', 'statusperalihan', 'tarif_id', 'nominalperalihan', 'persentaseperalihan', 'biayatambahan_id',
            'nosp', 'tglsp', 'statusritasiomset', 'cabang_id', 'komisisupir', 'tolsupir', 'jarak', 'nosptagihlain', 'nilaitagihlain',
            'tujuantagih', 'liter', 'nominalstafle', 'statusnotif', 'statusoneway', 'statusedittujuan', 'upahbongkardepo', 'upahmuatdepo', 'hargatol',
            'qtyton', 'totalton', 'mandorsupir_id', 'mandortrado_id', 'statustrip', 'notripasal', 'tgldoor', 'statusdisc', 'statusformat', 'modifiedby', 'created_at', 'updated_at'
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
                        if ($filters['field'] == 'suratpengantar_id') {
                            $query = $query->where('suratpengantar.id', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'suratpengantar_id') {
                            $query = $query->orWhere('suratpengantar.id', 'LIKE', "%$filters[data]%");
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
