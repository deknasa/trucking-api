<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class Supplier extends MyModel
{
    use HasFactory;

    protected $table = 'supplier';

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
        $hutang = DB::table('hutangheader')
            ->from(
                DB::raw("hutangheader as a with (readuncommitted)")
            )
            ->select(
                'a.supplier_id'
            )
            ->where('a.supplier_id', '=', $id)
            ->first();
        if (isset($hutang)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Hutang',
            ];
            goto selesai;
        }

        $hutangBayar = DB::table('hutangbayarheader')
            ->from(
                DB::raw("hutangbayarheader as a with (readuncommitted)")
            )
            ->select(
                'a.supplier_id'
            )
            ->where('a.supplier_id', '=', $id)
            ->first();
        if (isset($hutangBayar)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Hutang Bayar',
            ];
            goto selesai;
        }
        $penerimaanStok = DB::table('penerimaanstokheader')
            ->from(
                DB::raw("penerimaanstokheader as a with (readuncommitted)")
            )
            ->select(
                'a.supplier_id'
            )
            ->where('a.supplier_id', '=', $id)
            ->first();
        if (isset($penerimaanStok)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan Stok',
            ];
            goto selesai;
        }
        $pengeluaranStok = DB::table('pengeluaranstokheader')
            ->from(
                DB::raw("pengeluaranstokheader as a with (readuncommitted)")
            )
            ->select(
                'a.supplier_id'
            )
            ->where('a.supplier_id', '=', $id)
            ->first();
        if (isset($pengeluaranStok)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pengeluaran Stok',
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

        $aktif = request()->aktif ?? '';

        $query = DB::table($this->table)->select(
            // "$this->table.*",
            'supplier.id',
            'supplier.namasupplier',
            'supplier.namasupplier',
            'supplier.namakontak',
            'supplier.alamat',
            'supplier.kota',
            'supplier.kodepos',
            'supplier.notelp1',
            'supplier.notelp2',
            'supplier.email',

            'parameter_statusaktif.memo as statusaktif',
            'supplier.web',
            'supplier.namapemilik',
            'supplier.jenisusaha',
            'supplier.bank',
            'supplier.coa',
            'supplier.rekeningbank',
            'supplier.namarekening',
            'supplier.jabatan',

            'parameter_statusdaftarharga.memo as statusdaftarharga',
            'supplier.kategoriusaha',

            'supplier.modifiedby',
            'supplier.created_at',
            'supplier.updated_at'

        )
            ->leftJoin('parameter as parameter_statusaktif', "supplier.statusaktif", '=', 'parameter_statusaktif.id')
            ->leftJoin('parameter as parameter_statusdaftarharga', "supplier.statusdaftarharga", '=', 'parameter_statusdaftarharga.id');

        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('supplier.statusaktif', '=', $statusaktif->id);
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
            $table->unsignedBigInteger('statusdaftarharga')->nullable();
        });

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusaktif = $status->id ?? 0;

        $status = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS DAFTAR HARGA')
            ->where('subgrp', '=', 'STATUS DAFTAR HARGA')
            ->where('default', '=', 'YA')
            ->first();

        $iddefaultstatusdaftarharga = $status->id ?? 0;


        DB::table($tempdefault)->insert(
            ["statusaktif" => $iddefaultstatusaktif, "statusdaftarharga" => $iddefaultstatusdaftarharga]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusdaftarharga',
            );

        $data = $query->first();

        return $data;
    }

    public function find($id)
    {
        $query = DB::table('supplier')->select(
            'supplier.id',
            'supplier.namasupplier',
            'supplier.namasupplier',
            'supplier.namakontak',
            'supplier.alamat',
            'supplier.kota',
            'supplier.kodepos',
            'supplier.notelp1',
            'supplier.notelp2',
            'supplier.email',

            'supplier.statusaktif',
            'supplier.web',
            'supplier.namapemilik',
            'supplier.jenisusaha',
            'supplier.bank',
            'supplier.coa',
            'supplier.rekeningbank',
            'supplier.namarekening',
            'supplier.jabatan',

            'supplier.statusdaftarharga',
            'supplier.kategoriusaha',

            'supplier.modifiedby',
            'supplier.created_at',
            'supplier.updated_at'

        )
            ->where('supplier.id', $id);

        $data = $query->first();

        return $data;
    }

    public function selectColumns($query)
    { //sesuaikan dengan createtemp

        return $query->select(
            DB::raw(
                "$this->table.id,
            $this->table.namasupplier,
            $this->table.namakontak,
            $this->table.alamat,
            $this->table.kota,
            $this->table.kodepos,
            $this->table.notelp1,
            $this->table.notelp2,
            $this->table.email,
            $this->table.statusaktif,

            $this->table.web,
            $this->table.namapemilik,
            $this->table.jenisusaha,
            $this->table.bank,
            $this->table.coa,
            $this->table.rekeningbank,
            $this->table.namarekening,
            $this->table.jabatan,
            $this->table.statusdaftarharga,
            $this->table.kategoriusaha,

            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )

        );
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->longText('namasupplier')->nullable();
            $table->string('namakontak', 150)->nullable();
            $table->longText('alamat')->nullable();
            $table->string('kota', 150)->nullable();
            $table->string('kodepos', 50)->nullable();
            $table->string('notelp1', 50)->nullable();
            $table->string('notelp2', 50)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('statusaktif')->length(11)->nullable();
            $table->string('web', 50)->nullable();
            $table->string('namapemilik', 150)->nullable();
            $table->string('jenisusaha', 150)->nullable();
            $table->string('bank', 150)->nullable();
            $table->string('coa', 150)->nullable();
            $table->string('rekeningbank', 150)->nullable();
            $table->string('namarekening', 150)->nullable();
            $table->string('jabatan', 150)->nullable();
            $table->string('statusdaftarharga')->length(11)->nullable();
            $table->string('kategoriusaha', 150)->nullable();
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
        DB::table($temp)->insertUsing(['id', 'namasupplier', 'namakontak',  'alamat', 'kota', 'kodepos', 'notelp1', 'notelp2', 'email',  'statusaktif', 'web', 'namapemilik', 'jenisusaha', 'bank', 'coa','rekeningbank',  'namarekening', 'jabatan', 'statusdaftarharga', 'kategoriusaha', 'modifiedby', 'created_at', 'updated_at'], $models);

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
                        } else if ($filters['field'] == 'statusdaftarharga') {
                            $query = $query->where('parameter_statusdaftarharga.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . "." .  $filters['field'] . " LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statusaktif') {
                                $query = $query->orWhere('parameter_statusaktif.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'statusdaftarharga') {
                                $query = $query->orWhere('parameter_statusdaftarharga.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw($this->table . "." .  $filters['field'] . " LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

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
