<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Pelanggan extends MyModel
{
    use HasFactory;

    protected $table = 'pelanggan';

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

        $penerimaan = DB::table('penerimaanheader')
            ->from(
                DB::raw("penerimaanheader as a with (readuncommitted)")
            )
            ->select(
                'a.pelanggan_id'
            )
            ->where('a.pelanggan_id', '=', $id)
            ->first();
        if (isset($penerimaan)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan Kas/Bank',
            ];
            goto selesai;
        }

        $pengeluaran = DB::table('pengeluaranheader')
            ->from(
                DB::raw("pengeluaranheader as a with (readuncommitted)")
            )
            ->select(
                'a.pelanggan_id'
            )
            ->where('a.pelanggan_id', '=', $id)
            ->first();
        if (isset($pengeluaran)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pengeluaran Kas/Bank',
            ];
            goto selesai;
        }

        $penerimaanGiro = DB::table('penerimaangiroheader')
            ->from(
                DB::raw("penerimaangiroheader as a with (readuncommitted)")
            )
            ->select(
                'a.pelanggan_id'
            )
            ->where('a.pelanggan_id', '=', $id)
            ->first();
        if (isset($penerimaanGiro)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Penerimaan Giro',
            ];
            goto selesai;
        }

        $orderanTrucking = DB::table('orderantrucking')
            ->from(
                DB::raw("orderantrucking as a with (readuncommitted)")
            )
            ->select(
                'a.pelanggan_id'
            )
            ->where('a.pelanggan_id', '=', $id)
            ->first();
        if (isset($orderanTrucking)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Orderan Trucking',
            ];
            goto selesai;
        }
        $suratPengantar = DB::table('suratpengantar')
            ->from(
                DB::raw("suratpengantar as a with (readuncommitted)")
            )
            ->select(
                'a.pelanggan_id'
            )
            ->where('a.pelanggan_id', '=', $id)
            ->first();
        if (isset($suratPengantar)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Surat Pengantar',
            ];
            goto selesai;
        }
        $invoiceExtra = DB::table('invoiceextraheader')
            ->from(
                DB::raw("invoiceextraheader as a with (readuncommitted)")
            )
            ->select(
                'a.pelanggan_id'
            )
            ->where('a.pelanggan_id', '=', $id)
            ->first();
        if (isset($invoiceExtra)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Invoice Extra',
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

        $query = DB::table($this->table)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            'pelanggan.id',
            'pelanggan.kodepelanggan',
            'pelanggan.namapelanggan',
            'pelanggan.namakontak',
            'pelanggan.keterangan',
            'pelanggan.telp',
            'pelanggan.alamat',
            'pelanggan.alamat2',
            'pelanggan.kota',
            'pelanggan.kodepos',
            'pelanggan.modifiedby',
            'parameter.memo as statusaktif',
            'pelanggan.created_at',
            'pelanggan.updated_at',
            DB::raw("'Laporan Shipper' as judulLaporan"),
            DB::raw("'" . $getJudul->text . "' as judul"),
            DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
        )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'pelanggan.statusaktif', 'parameter.id');



        $this->filter($query);
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('pelanggan.statusaktif', '=', $statusaktif->id);
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->paginate($query);

        $data = $query->get();

        // dd($data);
        return $data;
    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "$this->table.id,
            $this->table.kodepelanggan,
            $this->table.namapelanggan,
            $this->table.namakontak,
            $this->table.keterangan,
            $this->table.telp,
            parameter.text as statusaktif,
            $this->table.alamat,
            $this->table.alamat2,
            $this->table.kota,
            $this->table.kodepos,
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
                )
            )->leftJoin(DB::raw("parameter with (readuncommitted)"), 'pelanggan.statusaktif', '=', 'parameter.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kodepelanggan', 1000)->nullable();
            $table->string('namapelanggan', 1000)->nullable();
            $table->string('namakontak', 1000)->nullable();
            $table->string('keterangan', 1000)->nullable();
            $table->string('telp', 1000)->nullable();
            $table->string('alamat', 1000)->nullable();
            $table->string('alamat2', 1000)->nullable()->nullable();
            $table->string('kota', 1000)->nullable();
            $table->string('kodepos', 1000)->nullable();
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
        DB::table($temp)->insertUsing(['id', 'kodepelanggan', 'namapelanggan','namakontak', 'keterangan', 'telp', 'alamat', 'alamat2', 'kota', 'kodepos', 'modifiedby', 'statusaktif', 'created_at', 'updated_at'], $models);


        return  $temp;
    }

    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }


    public function default()
    {




        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->nullable();
        });

        $statusaktif = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('DEFAULT', '=', 'YA')
            ->first();

        DB::table($tempdefault)->insert(["statusaktif" => $statusaktif->id]);




        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif'
            );

        $data = $query->first();
        // dd($data);
        return $data;
    }
    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
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
                            if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
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

    public function processStore(array $data, Pelanggan $pelanggan): Pelanggan
    {
        // $pelanggan = new Pelanggan();
        $pelanggan->kodepelanggan = $data['kodepelanggan'];
        $pelanggan->namapelanggan = $data['namapelanggan'];
        $pelanggan->namakontak = $data['namakontak'];
        $pelanggan->telp = $data['telp'];
        $pelanggan->alamat = $data['alamat'];
        $pelanggan->alamat2 = $data['alamat2'] ?? '';
        $pelanggan->kota = $data['kota'];
        $pelanggan->kodepos = $data['kodepos'];
        $pelanggan->keterangan = $data['keterangan'] ?? '';
        $pelanggan->modifiedby = auth('api')->user()->name;
        $pelanggan->info = html_entity_decode(request()->info);
        $pelanggan->statusaktif = $data['statusaktif'];
        $pelanggan->tas_id = $data['tas_id'] ?? '';

        if (!$pelanggan->save()) {
            throw new \Exception("Error storing service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($pelanggan->getTable()),
            'postingdari' => 'ENTRY PELANGGAN',
            'idtrans' => $pelanggan->id,
            'nobuktitrans' => $pelanggan->id,
            'aksi' => 'ENTRY',
            'datajson' => $pelanggan->toArray(),
            'modifiedby' => $pelanggan->modifiedby
        ]);

        return $pelanggan;
    }

    public function processUpdate(Pelanggan $pelanggan, array $data): Pelanggan
    {
        $pelanggan->kodepelanggan = $data['kodepelanggan'];
        $pelanggan->namapelanggan = $data['namapelanggan'];
        $pelanggan->namakontak = $data['namakontak'];
        $pelanggan->telp = $data['telp'];
        $pelanggan->alamat = $data['alamat'];
        $pelanggan->alamat2 = $data['alamat2'] ?? '';
        $pelanggan->kota = $data['kota'];
        $pelanggan->kodepos = $data['kodepos'];
        $pelanggan->keterangan = $data['keterangan'] ?? '';
        $pelanggan->statusaktif = $data['statusaktif'];
        $pelanggan->modifiedby = auth('api')->user()->name;
        $pelanggan->info = html_entity_decode(request()->info);

        if (!$pelanggan->save()) {
            throw new \Exception("Error update service in header.");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($pelanggan->getTable()),
            'postingdari' => 'EDIT PELANGGAN',
            'idtrans' => $pelanggan->id,
            'nobuktitrans' => $pelanggan->id,
            'aksi' => 'EDIT',
            'datajson' => $pelanggan->toArray(),
            'modifiedby' => $pelanggan->modifiedbyf
        ]);

        return $pelanggan;
    }

    public function processDestroy(Pelanggan $pelanggan): Pelanggan
    {
        // $pelanggan = new Pelanggan();
        $pelanggan = $pelanggan->lockAndDestroy($pelanggan->id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($pelanggan->getTable()),
            'postingdari' => 'DELETE PARAMETER',
            'idtrans' => $pelanggan->id,
            'nobuktitrans' => $pelanggan->id,
            'aksi' => 'DELETE',
            'datajson' => $pelanggan->toArray(),
            'modifiedby' => $pelanggan->modifiedby
        ]);

        return $pelanggan;
    }

    public function processApprovalnonaktif(array $data)
    {

        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $Pelanggan = Pelanggan::find($data['Id'][$i]);

                $Pelanggan->statusaktif = $statusnonaktif->id;
                $aksi = $statusnonaktif->text;

                // dd($Pelanggan);
            if ($Pelanggan->save()) {
                
                (new LogTrail())->processStore([
                    
                    'namatabel' => strtoupper($Pelanggan->getTable()),
                    'postingdari' => 'APPROVAL Pelanggan',
                    'idtrans' => $Pelanggan->id,
                    'nobuktitrans' => $Pelanggan->id,
                    'aksi' => $aksi,
                    'datajson' => $Pelanggan->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }


        return $Pelanggan;
    }

    
}
