<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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

        $pelunasanHutang = DB::table('pelunasanhutangheader')
            ->from(
                DB::raw("pelunasanhutangheader as a with (readuncommitted)")
            )
            ->select(
                'a.supplier_id'
            )
            ->where('a.supplier_id', '=', $id)
            ->first();
        if (isset($pelunasanHutang)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pelunasan Hutang',
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

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();


        $aktif = request()->aktif ?? '';
        $from = request()->from ?? '';

        $query = DB::table($this->table)->select(
            // "$this->table.*",
            'supplier.id',
            'supplier.namasupplier',
            'supplier.namakontak',
            'supplier.top',
            'supplier.keterangan',
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
            'statusapproval.memo as statusapproval',
            'statuspostingtnl.memo as statuspostingtnl',
            'supplier.modifiedby',
            'supplier.created_at',
            'supplier.updated_at',
            DB::raw("'Laporan Supplier' as judulLaporan"),
            DB::raw("'" . $getJudul->text . "' as judul"),
            DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")

        )
            ->leftJoin('parameter as parameter_statusaktif', "supplier.statusaktif", '=', 'parameter_statusaktif.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'supplier.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("parameter as statuspostingtnl with (readuncommitted)"), 'supplier.statuspostingtnl', 'statuspostingtnl.id')
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
        if ($from == 'pelunasanhutangheader') {
            $statusapproval = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS APPROVAL')
                ->where('text', '=', 'APPROVAL')
                ->first();

            $query->where('supplier.statusapproval', '=', $statusapproval->id);
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
            $table->string('statusaktifnama', 300)->nullable();
            $table->unsignedBigInteger('statusdaftarharga')->nullable();
            $table->string('statusdaftarharganama', 300)->nullable();
            $table->unsignedBigInteger('statuspostingtnl')->nullable();
            $table->string('statuspostingtnlnama')->nullable();
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

        $statusdaftarharga = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id',
                'text'
            )
            ->where('grp', '=', 'STATUS DAFTAR HARGA')
            ->where('subgrp', '=', 'STATUS DAFTAR HARGA')
            ->where('default', '=', 'YA')
            ->first();

        $statuspostingtnl = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id',
                'text'
            )
            ->where('grp', '=', 'STATUS POSTING TNL')
            ->where('subgrp', '=', 'STATUS POSTING TNL')
            ->where('default', '=', 'YA')
            ->first();

        DB::table($tempdefault)->insert(
            [
                "statusaktif" => $statusaktif->id ?? 0,
                "statusaktifnama" => $statusaktif->text ?? "",
                "statusdaftarharga" => $statusdaftarharga->id ?? 0,
                "statusdaftarharganama" => $statusdaftarharga->text ?? "",
                "statuspostingtnl" => $statuspostingtnl->id ?? 0,
                "statuspostingtnlnama" => $statuspostingtnl->text ?? "",
            ]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusaktifnama',
                'statusdaftarharga',
                'statusdaftarharganama',
                'statuspostingtnl',
                'statuspostingtnlnama',
            );

        $data = $query->first();

        return $data;
    }

    public function findAll($id)
    {
        $query = DB::table('supplier')->select(
            'supplier.id',
            'supplier.namasupplier',
            'supplier.namakontak',
            'supplier.[top]',
            'supplier.keterangan',
            'supplier.alamat',
            'supplier.kota',
            'supplier.kodepos',
            'supplier.notelp1',
            'supplier.notelp2',
            'supplier.email',
            'supplier.statusapproval',
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
            'supplier.statuspostingtnl',
            'supplier.kategoriusaha',
            'supplier.statusapproval',
            'supplier.tglapproval',
            'supplier.userapproval',
            'supplier.modifiedby',
            'supplier.created_at',
            'supplier.updated_at'

        )
            ->where('supplier.id', $id);

        $data = $query->first();

        return $data;
    }


    public function getAll($id)
    {
        $query = DB::table('supplier')->select(
            'supplier.id',
            'supplier.namasupplier',
            'supplier.namakontak',
            'supplier.top',
            'supplier.keterangan',
            'supplier.alamat',
            'supplier.kota',
            'supplier.kodepos',
            'supplier.notelp1',
            'supplier.notelp2',
            'supplier.email',
            'supplier.statusapproval',
            'supplier.statusaktif',
            'supplier.web',
            'supplier.namapemilik',
            'supplier.jenisusaha',
            'supplier.bank',
            'supplier.coa',
            DB::raw("(trim(akunpusat.coa)+' - '+trim(akunpusat.keterangancoa)) as ketcoa"),
            'supplier.rekeningbank',
            'supplier.namarekening',
            'supplier.jabatan',
            'supplier.statusdaftarharga',
            'supplier.statuspostingtnl',
            'supplier.kategoriusaha',
            'supplier.statusapproval',
            'supplier.tglapproval',
            'supplier.userapproval',
            'supplier.modifiedby',
            'supplier.created_at',
            'supplier.updated_at',
            'param_aktif.text as statusaktifnama',
            'param_daftarharga.text as statusdaftarharganama',
        )
            ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), "supplier.coa", 'akunpusat.coa')
            ->leftJoin(DB::raw("parameter as param_aktif with (readuncommitted)"), 'supplier.statusaktif', '=', 'param_aktif.id')
            ->leftJoin(DB::raw("parameter as param_daftarharga with (readuncommitted)"), 'supplier.statusdaftarharga', '=', 'param_daftarharga.id')
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
            $this->table.[top],
            $this->table.keterangan,
            $this->table.alamat,
            $this->table.kota,
            $this->table.kodepos,
            $this->table.notelp1,
            $this->table.notelp2,
            $this->table.email,
            'parameter_statusaktif.text as statusaktif',

            $this->table.web,
            $this->table.namapemilik,
            $this->table.jenisusaha,
            $this->table.bank,
            $this->table.coa,
            $this->table.rekeningbank,
            $this->table.namarekening,
            $this->table.jabatan,
            'parameter_statusdaftarharga.text as statusdaftarharga',
            $this->table.kategoriusaha,
            'statusapproval.text as statusapproval',
            $this->table.tglapproval,
            $this->table.userapproval,
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )

        )
            ->leftJoin('parameter as parameter_statusaktif', "supplier.statusaktif", '=', 'parameter_statusaktif.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'supplier.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("parameter as statuspostingtnl with (readuncommitted)"), 'supplier.statuspostingtnl', 'statuspostingtnl.id')
            ->leftJoin('parameter as parameter_statusdaftarharga', "supplier.statusdaftarharga", '=', 'parameter_statusdaftarharga.id');
    }

    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->longText('namasupplier')->nullable();
            $table->string('namakontak', 150)->nullable();
            $table->integer('top')->length(11)->nullable();
            $table->longText('keterangan')->nullable();
            $table->longText('alamat')->nullable();
            $table->string('kota', 150)->nullable();
            $table->string('kodepos', 50)->nullable();
            $table->string('notelp1', 50)->nullable();
            $table->string('notelp2', 50)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('statusaktif')->nullable();
            $table->string('web', 50)->nullable();
            $table->string('namapemilik', 150)->nullable();
            $table->string('jenisusaha', 150)->nullable();
            $table->string('bank', 150)->nullable();
            $table->string('coa', 150)->nullable();
            $table->string('rekeningbank', 150)->nullable();
            $table->string('namarekening', 150)->nullable();
            $table->string('jabatan', 150)->nullable();
            $table->string('statusdaftarharga')->nullable();
            $table->string('kategoriusaha', 150)->nullable();
            $table->string('statusapproval', 150)->nullable();
            $table->date('tglapproval')->nullable();
            $table->string('userapproval', 50)->nullable();
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
        // dd($models->get());
        DB::table($temp)->insertUsing(['id', 'namasupplier', 'namakontak', 'top', 'keterangan',  'alamat', 'kota', 'kodepos', 'notelp1', 'notelp2', 'email',  'statusaktif', 'web', 'namapemilik', 'jenisusaha', 'bank', 'coa', 'rekeningbank',  'namarekening', 'jabatan', 'statusdaftarharga', 'kategoriusaha', 'statusapproval', 'tglapproval', 'userapproval', 'modifiedby', 'created_at', 'updated_at'], $models);

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
                        } else if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('statusapproval.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statuspostingtnl') {
                            $query = $query->where('statuspostingtnl.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'check') {
                            $query = $query->whereRaw('1 = 1');
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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
                            } else if ($filters['field'] == 'statusapproval') {
                                $query = $query->orWhere('statusapproval.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'statuspostingtnl') {
                                $query = $query->orWhere('statuspostingtnl.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'check') {
                                $query = $query->whereRaw('1 = 1');
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

    public function processStore(array $data, Supplier $supplier): Supplier
    {

        $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        // $supplier = new Supplier();
        $supplier->namasupplier = trim($data['namasupplier']);
        $supplier->namakontak = $data['namakontak'];
        $supplier->top = $data['top'];
        $supplier->keterangan = $data['keterangan'];
        $supplier->statusapproval = $statusNonApproval->id;
        $supplier->alamat = $data['alamat'];
        $supplier->kota = $data['kota'];
        $supplier->kodepos = $data['kodepos'];
        $supplier->notelp1 = $data['notelp1'];
        $supplier->notelp2 = $data['notelp2'] ?? '';
        $supplier->email = $data['email'];
        $supplier->statusaktif = $data['statusaktif'];
        $supplier->web = $data['web'];
        $supplier->namapemilik = $data['namapemilik'];
        $supplier->jenisusaha = $data['jenisusaha'];
        // $supplier->top = $request->top;
        $supplier->bank = $data['bank'];
        $supplier->coa = $data['coa'];
        $supplier->rekeningbank = $data['rekeningbank'];
        $supplier->namarekening = $data['namarekening'];
        $supplier->jabatan = $data['jabatan'];
        $supplier->statusdaftarharga = $data['statusdaftarharga'];
        $supplier->statuspostingtnl = $data['statuspostingtnl'];
        $supplier->kategoriusaha = $data['kategoriusaha'];
        $supplier->tas_id = $data['tas_id'] ?? '';
        $supplier->modifiedby = auth('api')->user()->name;
        $supplier->info = html_entity_decode(request()->info);


        if (!$supplier->save()) {
            throw new \Exception("Error storing service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($supplier->getTable()),
            'postingdari' => 'ENTRY SUPPLIER',
            'idtrans' => $supplier->id,
            'nobuktitrans' => $supplier->id,
            'aksi' => 'ENTRY',
            'datajson' => $supplier->toArray(),
            'modifiedby' => $supplier->modifiedby
        ]);

        return $supplier;
    }

    public function processUpdate(Supplier $supplier, array $data): Supplier
    {
        $supplier->namasupplier = trim($data['namasupplier']);
        $supplier->namakontak = $data['namakontak'];
        $supplier->top = $data['top'];
        $supplier->keterangan = $data['keterangan'];
        $supplier->alamat = $data['alamat'];
        $supplier->kota = $data['kota'];
        $supplier->kodepos = $data['kodepos'];
        $supplier->notelp1 = $data['notelp1'];
        $supplier->notelp2 = $data['notelp2'] ?? '';
        $supplier->email = $data['email'];
        $supplier->statusaktif = $data['statusaktif'];
        $supplier->web = $data['web'];
        $supplier->namapemilik = $data['namapemilik'];
        $supplier->jenisusaha = $data['jenisusaha'];
        // $supplier->top = $request->top;
        $supplier->bank = $data['bank'];
        $supplier->coa = $data['coa'];
        $supplier->rekeningbank = $data['rekeningbank'];
        $supplier->namarekening = $data['namarekening'];
        $supplier->jabatan = $data['jabatan'];
        $supplier->statusdaftarharga = $data['statusdaftarharga'];
        $supplier->kategoriusaha = $data['kategoriusaha'];
        $supplier->modifiedby = auth('api')->user()->name;
        $supplier->info = html_entity_decode(request()->info);

        if (!$supplier->save()) {
            throw new \Exception("Error update service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($supplier->getTable()),
            'postingdari' => 'EDIT SUPPLIER',
            'idtrans' => $supplier->id,
            'nobuktitrans' => $supplier->id,
            'aksi' => 'EDIT',
            'datajson' => $supplier->toArray(),
            'modifiedby' => $supplier->modifiedby
        ]);

        return $supplier;
    }
    public function processDestroy(Supplier $supplier): Supplier
    {
        // $supplier = new Supplier();
        $supplier = $supplier->lockAndDestroy($supplier->id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($supplier->getTable()),
            'postingdari' => 'DELETE SUPPLIER',
            'idtrans' => $supplier->id,
            'nobuktitrans' => $supplier->id,
            'aksi' => 'DELETE',
            'datajson' => $supplier->toArray(),
            'modifiedby' => $supplier->modifiedby
        ]);

        return $supplier;
    }

    public function postingTnl($data)
    {
        $server = config('app.server_tnl');
        // dd($server . 'truckingtnl-api/public/api/token');
        $getToken = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])
            ->post($server . 'truckingtnl-api/public/api/token', [
                'user' => 'ADMIN',
                'password' => config('app.password_tnl'),
                'ipclient' => '',
                'ipserver' => '',
                'latitude' => '',
                'longitude' => '',
                'browser' => '',
                'os' => '',
            ]);

        if ($getToken->getStatusCode() == '404') {
            throw new \Exception("Akun Tidak Terdaftar di Trucking TNL");
        } else if ($getToken->getStatusCode() == '200') {
            $data['from'] = 'jkt';

            $access_token = json_decode($getToken, TRUE)['access_token'];
            // dump($access_token);
            // dump(json_encode($data));
            // dd($server . 'truckingtnl-api/public/api/supplier');
            $transferTarif = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $access_token
            ])->post($server . 'truckingtnl-api/public/api/supplier', $data);
            // dd($data);
            // dd($transferTarif);
            $tesResp = $transferTarif->toPsrResponse();
            $response = [
                'statuscode' => $tesResp->getStatusCode(),
                'data' => $transferTarif->json(),
            ];
            $dataResp = $transferTarif->json();
            if ($tesResp->getStatusCode() != 201) {
                if ($tesResp->getStatusCode() == 422) {
                    throw new \Exception($dataResp['errors']['namasupplier'][0] . ' di TNL');
                } else {
                    throw new \Exception($dataResp['message']);
                }
            }
            return $response;
            // return true;
        } else {
            throw new \Exception("server tidak bisa diakses");
        }
    }

    public function processApproval(array $data)
    {

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $Supplier = Supplier::find($data['Id'][$i]);

            if ($Supplier->statusapproval == $statusApproval->id) {
                $Supplier->statusapproval = $statusNonApproval->id;
                $aksi = $statusNonApproval->text;
            } else {
                $Supplier->statusapproval = $statusApproval->id;
                $aksi = $statusApproval->text;
            }

            $Supplier->tglapproval = date('Y-m-d', time());
            $Supplier->userapproval = auth('api')->user()->name;
            if ($Supplier->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($Supplier->getTable()),
                    'postingdari' => 'APPROVAL SUPPLIER',
                    'idtrans' => $Supplier->id,
                    'nobuktitrans' => $Supplier->id,
                    'aksi' => $aksi,
                    'datajson' => $Supplier->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }

        $params = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'APPROVAL TNL')->where('subgrp', 'APPROVAL TNL')->first();
        $approvalTnl = $params->text;
        if ($approvalTnl == 'YA') {
            (new Supplier())->approvalToTNL($data);
        }

        return $Supplier;
    }


    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $Supplier = Supplier::find($data['Id'][$i]);

            $Supplier->statusaktif = $statusnonaktif->id;
            $aksi = $statusnonaktif->text;

            if ($Supplier->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($Supplier->getTable()),
                    'postingdari' => 'APPROVAL SUPPLIER',
                    'idtrans' => $Supplier->id,
                    'nobuktitrans' => $Supplier->id,
                    'aksi' => $aksi,
                    'datajson' => $Supplier->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }


        return $Supplier;
    }

    public function approvalToTNL($data)
    {
        $server = config('app.server_jkt');
        $getToken = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])
            ->post($server . 'truckingtnl-api/public/api/token', [
                'user' => 'ADMIN',
                'password' => getenv('PASSWORD_TNL'),
                'ipclient' => '',
                'ipserver' => '',
                'latitude' => '',
                'longitude' => '',
                'browser' => '',
                'os' => '',
            ]);

        if ($getToken->getStatusCode() == '404') {
            throw new \Exception("Akun Tidak Terdaftar di Trucking TNL");
        } else if ($getToken->getStatusCode() == '200') {

            $access_token = json_decode($getToken, TRUE)['access_token'];
            $transferTarif = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $access_token
            ])->post($server . 'truckingtnl-api/public/api/supplier/approvalTNL', $data);
            $tesResp = $transferTarif->toPsrResponse();
            $response = [
                'statuscode' => $tesResp->getStatusCode(),
                'data' => $transferTarif->json(),
            ];
            return $response;
        } else {
            throw new \Exception("server tidak bisa diakses");
        }
    }
    public function processApprovalTnl(array $data)
    {

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        for ($i = 0; $i < count($data['nama']); $i++) {
            $Supplier = Supplier::where('namasupplier', trim($data['nama'][$i]))->first();

            if ($Supplier->statusapproval == $statusApproval->id) {
                DB::table('supplier')->where('namasupplier', $data['nama'][$i])->update([
                    'statusapproval' =>  $statusNonApproval->id,
                    'tglapproval' => date('Y-m-d', time()),
                    'userapproval' => auth('api')->user()->name
                ]);
                $aksi = $statusNonApproval->text;
            } else {
                DB::table('supplier')->where('namasupplier', $data['nama'][$i])->update([
                    'statusapproval' =>  $statusApproval->id,
                    'tglapproval' => date('Y-m-d', time()),
                    'userapproval' => auth('api')->user()->name
                ]);
                $aksi = $statusApproval->text;
            }

            if ($Supplier->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($Supplier->getTable()),
                    'postingdari' => 'APPROVAL SUPPLIER',
                    'idtrans' => $Supplier->id,
                    'nobuktitrans' => $Supplier->id,
                    'aksi' => $aksi,
                    'datajson' => $Supplier->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }

        return $Supplier;
    }

    public function cekdataText($id)
    {
        $query = DB::table('supplier')->from(db::raw("supplier a with (readuncommitted)"))
            ->select(
                'a.namasupplier as keterangan'
            )
            ->where('id', $id)
            ->first();

        $keterangan = $query->keterangan ?? '';

        return $keterangan;
    }
}
