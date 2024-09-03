<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class StokPusat extends MyModel
{
    use HasFactory;

    protected $table = 'stokpusat';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get()
    {

        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'StokPusatController';

        if ($proses == 'reload') {
            $temtabel = 'temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel a with (readuncommitted)")
            )
                ->select(
                    'id',
                    'class',
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            if (isset($querydata)) {
                Schema::dropIfExists($querydata->namatabel);
                DB::table('listtemporarytabel')->where('id', $querydata->id)->delete();
            }

            DB::table('listtemporarytabel')->insert(
                [
                    'class' => $class,
                    'namatabel' => $temtabel,
                    'modifiedby' => $user,
                    'created_at' => date('Y/m/d H:i:s'),
                    'updated_at' => date('Y/m/d H:i:s'),
                ]
            );

            Schema::create($temtabel, function (Blueprint $table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->string('namaterpusat', 1000)->nullable();
                $table->unsignedBigInteger('idmedan')->nullable();
                $table->string('namamedan', 1000)->nullable();
                $table->unsignedBigInteger('idsurabaya')->nullable();
                $table->string('namasurabaya', 1000)->nullable();
                $table->unsignedBigInteger('idjakarta')->nullable();
                $table->string('namajakarta', 1000)->nullable();
                $table->unsignedBigInteger('idjakartatnl')->nullable();
                $table->string('namajakartatnl', 1000)->nullable();
                $table->unsignedBigInteger('idmakassar')->nullable();
                $table->string('namamakassar', 1000)->nullable();
                $table->unsignedBigInteger('idmanado')->nullable();
                $table->string('namamanado', 1000)->nullable();
                $table->longText('modifiedby')->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
            });
            $querystok = DB::table("stokpusat")->from(DB::raw("stokpusat with (readuncommitted)"))->select('id', 'namastok as namaterpusat', 'modifiedby', 'created_at', 'updated_at')->get();

            foreach ($querystok as $row) {
                $queryDetail = DB::table("stokpusatrincian")->from(DB::raw("stokpusatrincian with (readuncommitted)"))->select('stok_id', 'cabang_id', 'namastok')->where('stokpusat_id', $row->id)->get();
                $datadetail = [];
                $datadetail['id'] = $row->id;
                $datadetail['namaterpusat'] = $row->namaterpusat;
                foreach ($queryDetail as $detail) {
                    $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('id', $detail->cabang_id)->first();
                    $namacabang = strtolower(str_replace(' ', '', $getCabang->namacabang));
                    $datadetail['id' . $namacabang] = $detail->stok_id;
                    $datadetail['nama' . $namacabang] = $detail->namastok;
                }
                $datadetail['modifiedby'] = $row->modifiedby;
                $datadetail['created_at'] = $row->created_at;
                $datadetail['updated_at'] = $row->updated_at;
                DB::table($temtabel)->insert($datadetail);
            }
        } else {
            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel with (readuncommitted)")
            )
                ->select(
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            $temtabel = $querydata->namatabel;
        }

        $this->setRequestParameters();
        $query = DB::table("$temtabel")->from(DB::raw("$temtabel as a with (readuncommitted)"))
            ->select(
                'a.id',
                'a.namaterpusat',
                'a.idmedan',
                'a.namamedan',
                'a.idsurabaya',
                'a.namasurabaya',
                'a.idjakarta',
                'a.namajakarta',
                'a.idjakartatnl',
                'a.namajakartatnl',
                'a.idmakassar',
                'a.namamakassar',
                'a.idmanado',
                'a.namamanado',
                'modifiedby',
                'created_at',
                'updated_at'
            );
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
        $query = DB::table("stokpusat")->from(DB::raw("stokpusat with (readuncommitted)"))
            ->select(
                'stokpusat.id',
                'stokpusat.namastok',
                'stokpusat.kelompok_id',
                'kelompok.kodekelompok as kelompok'
            )
            ->leftJoin(DB::raw("kelompok with (readuncommitted)"), 'stokpusat.kelompok_id', 'kelompok.id')
            ->where('stokpusat.id', $id);

        return $query->first();
    }

    public function selectColumns()
    {
        $temtabel = '##tempstokpusat' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temtabel, function (Blueprint $table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->string('namaterpusat', 1000)->nullable();
            $table->unsignedBigInteger('idmedan')->nullable();
            $table->string('namamedan', 1000)->nullable();
            $table->unsignedBigInteger('idsurabaya')->nullable();
            $table->string('namasurabaya', 1000)->nullable();
            $table->unsignedBigInteger('idjakarta')->nullable();
            $table->string('namajakarta', 1000)->nullable();
            $table->unsignedBigInteger('idjakartatnl')->nullable();
            $table->string('namajakartatnl', 1000)->nullable();
            $table->unsignedBigInteger('idmakassar')->nullable();
            $table->string('namamakassar', 1000)->nullable();
            $table->unsignedBigInteger('idmanado')->nullable();
            $table->string('namamanado', 1000)->nullable();
            $table->longText('modifiedby')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
        $querystok = DB::table("stokpusat")->from(DB::raw("stokpusat with (readuncommitted)"))->select('id', 'namastok as namaterpusat', 'modifiedby', 'created_at', 'updated_at')->get();

        foreach ($querystok as $row) {
            $queryDetail = DB::table("stokpusatrincian")->from(DB::raw("stokpusatrincian with (readuncommitted)"))->select('stok_id', 'cabang_id', 'namastok')->where('stokpusat_id', $row->id)->get();
            $datadetail = [];
            $datadetail['id'] = $row->id;
            $datadetail['namaterpusat'] = $row->namaterpusat;
            foreach ($queryDetail as $detail) {
                $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('id', $detail->cabang_id)->first();
                $namacabang = strtolower(str_replace(' ', '', $getCabang->namacabang));
                $datadetail['id' . $namacabang] = $detail->stok_id;
                $datadetail['nama' . $namacabang] = $detail->namastok;
            }
            $datadetail['modifiedby'] = $row->modifiedby;
            $datadetail['created_at'] = $row->created_at;
            $datadetail['updated_at'] = $row->updated_at;
            DB::table($temtabel)->insert($datadetail);
        }
        $query = DB::table("$temtabel")->from(DB::raw("$temtabel as a with (readuncommitted)"))
            ->select(
                'a.id',
                'a.namaterpusat',
                'a.idmedan',
                'a.namamedan',
                'a.idsurabaya',
                'a.namasurabaya',
                'a.idjakarta',
                'a.namajakarta',
                'a.idjakartatnl',
                'a.namajakartatnl',
                'a.idmakassar',
                'a.namamakassar',
                'a.idmanado',
                'a.namamanado',
                'modifiedby',
                'created_at',
                'updated_at'
            );
        return $query;
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->string('namaterpusat', 1000)->nullable();
            $table->unsignedBigInteger('idmedan')->nullable();
            $table->string('namamedan', 1000)->nullable();
            $table->unsignedBigInteger('idsurabaya')->nullable();
            $table->string('namasurabaya', 1000)->nullable();
            $table->unsignedBigInteger('idjakarta')->nullable();
            $table->string('namajakarta', 1000)->nullable();
            $table->unsignedBigInteger('idjakartatnl')->nullable();
            $table->string('namajakartatnl', 1000)->nullable();
            $table->unsignedBigInteger('idmakassar')->nullable();
            $table->string('namamakassar', 1000)->nullable();
            $table->unsignedBigInteger('idmanado')->nullable();
            $table->string('namamanado', 1000)->nullable();
            $table->longText('modifiedby')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = $this->selectColumns();
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing([
            'id',
            'namaterpusat',
            'idmedan',
            'namamedan',
            'idsurabaya',
            'namasurabaya',
            'idjakarta',
            'namajakarta',
            'idjakartatnl',
            'namajakartatnl',
            'idmakassar',
            'namamakassar',
            'idmanado',
            'namamanado',
            'modifiedby',
            'created_at',
            'updated_at'
        ], $models);

        return $temp;
    }
    public function sort($query)
    {
        return $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        // if ($filters['field'] == 'kelompok') {
                        //     $query = $query->where('kelompok.kodekelompok', 'LIKE', "%$filters[data]%");
                        // } else
                        if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%' escape '|'");
                        } else {
                            // $query = $query->whereRaw($this->table . ".".  $filters['field'] ." LIKE '%".str_replace($filters['data'],'[','|[') ."%' escape '|'");
                            $query = $query->whereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            // if ($filters['field'] == 'kelompok') {
                            //     $query = $query->orWhere('kelompok.kodekelompok', 'LIKE', "%$filters[data]%");
                            // } else 
                            if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->OrwhereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function processStore(array $data): StokPusat
    {
        $stokPusat = new StokPusat();
        $stokPusat->namastok = str_replace("''", '"',  strtoupper($data['namaterpusat']));
        $stokPusat->kelompok_id = $data['kelompok_id'];
        $stokPusat->modifiedby = auth('api')->user()->name;
        $stokPusat->info = html_entity_decode(request()->info);

        if (!$stokPusat->save()) {
            throw new \Exception("Error storing stokPusat.");
        }

        $storedLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($stokPusat->getTable()),
            'postingdari' => 'ENTRY STOK PUSAT',
            'idtrans' => $stokPusat->id,
            'nobuktitrans' => $stokPusat->id,
            'aksi' => 'ENTRY',
            'datajson' => $stokPusat->toArray(),
            'modifiedby' => $stokPusat->modifiedby
        ]);

        $data['namaterpusat'] = $stokPusat->namastok;
        $detaillog = [];

        if ($data['stok_idmdn'] != null) {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'MDN')->first();
            // $gambarmdn = '';
            // if ($data['gambarmdn'] != null) {
            //     $gambarmdn = $this->saveFiles('stokpusat/mdn/', config('app.pic_url_mdn'), $data['gambarmdn'], str_replace(' ', '_', $data['namastokmdn']));
            // }
            $datadetails = (new StokPusatRincian())->processStore($stokPusat, [
                'namastok' => str_replace("''", '"',  strtoupper($data['namastokmdn'])),
                'kelompok_id' => $data['kelompok_id'],
                'stok_id' => $data['stok_idmdn'],
                'cabang_id' => $getCabang->id,
                'gambar' => ($data['gambarmdn'] != null) ? json_encode([$data['gambarmdn']]) : '',
            ]);
            if ($data['gambarmdn'] != null) {
                $destinationPath = 'stokpusat/mdn/';
                $imageUrl = config('app.server_mdn') . "trucking-api/public/api/stok/" . str_replace(' ', '%20', $data['gambarmdn']) . "/medium";
                $destinationFileName = $data['gambarmdn'];
                $imageData = file_get_contents($imageUrl);
                Storage::put($destinationPath . $destinationFileName, $imageData);
            }
            $detaillog[] = $datadetails->toArray();
        }

        if ($data['stok_idjkt'] != null) {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'JKT')->first();

            $datadetails = (new StokPusatRincian())->processStore($stokPusat, [
                'namastok' => str_replace("''", '"',  strtoupper($data['namastokjkt'])),
                'kelompok_id' => $data['kelompok_id'],
                'stok_id' => $data['stok_idjkt'],
                'cabang_id' => $getCabang->id,
                'gambar' => ($data['gambarjkt'] != null) ? json_encode([$data['gambarjkt']]) : '',
            ]);
            if ($data['gambarjkt'] != null) {
                $destinationPath = 'stokpusat/jkt/';
                $imageUrl = config('app.server_jkt') . "trucking-api/public/api/stok/" . str_replace(' ', '%20', $data['gambarjkt']) . "/medium";
                $destinationFileName = $data['gambarjkt'];
                $imageData = file_get_contents($imageUrl);
                Storage::put($destinationPath . $destinationFileName, $imageData);
            }
            $detaillog[] = $datadetails->toArray();
        }

        if ($data['stok_idjkttnl'] != null) {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'TNL')->first();

            $datadetails = (new StokPusatRincian())->processStore($stokPusat, [
                'namastok' => str_replace("''", '"',  strtoupper($data['namastokjkttnl'])),
                'kelompok_id' => $data['kelompok_id'],
                'stok_id' => $data['stok_idjkttnl'],
                'cabang_id' => $getCabang->id,
                'gambar' => ($data['gambarjkttnl'] != null) ? json_encode([$data['gambarjkttnl']]) : '',
            ]);
            if ($data['gambarjkttnl'] != null) {
                $destinationPath = 'stokpusat/jkttnl/';
                $imageUrl = config('app.server_jkt') . "truckingtnl-api/public/api/stok/" . str_replace(' ', '%20', $data['gambarjkttnl']) . "/medium";
                $destinationFileName = $data['gambarjkttnl'];
                $imageData = file_get_contents($imageUrl);
                Storage::put($destinationPath . $destinationFileName, $imageData);
            }
            $detaillog[] = $datadetails->toArray();
        }

        if ($data['stok_idmks'] != null) {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'MKS')->first();

            $datadetails = (new StokPusatRincian())->processStore($stokPusat, [
                'namastok' => str_replace("''", '"',  strtoupper($data['namastokmks'])),
                'kelompok_id' => $data['kelompok_id'],
                'stok_id' => $data['stok_idmks'],
                'cabang_id' => $getCabang->id,
                'gambar' => ($data['gambarmks'] != null) ? json_encode([$data['gambarmks']]) : '',
            ]);
            if ($data['gambarmks'] != null) {
                $destinationPath = 'stokpusat/mks/';
                $imageUrl = config('app.server_mks') . "trucking-api/public/api/stok/" . str_replace(' ', '%20', $data['gambarmks']) . "/medium";
                $destinationFileName = $data['gambarmks'];
                $imageData = file_get_contents($imageUrl);
                Storage::put($destinationPath . $destinationFileName, $imageData);
            }
            $detaillog[] = $datadetails->toArray();
        }

        if ($data['stok_idsby'] != null) {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'SBY')->first();
            $gambarsby = '';
            if ($data['gambarsby'] != null) {
                $gambarsby = $this->saveFiles('stokpusat/sby/', config('app.pic_url_sby'), $data['gambarsby'], str_replace(' ', '_', $data['namastoksby']));
            }
            $datadetails = (new StokPusatRincian())->processStore($stokPusat, [
                'namastok' => str_replace("''", '"',  strtoupper($data['namastoksby'])),
                'kelompok_id' => $data['kelompok_id'],
                'stok_id' => $data['stok_idsby'],
                'cabang_id' => $getCabang->id,
                'gambar' => ($data['gambarsby'] != null) ? json_encode([$gambarsby]) : '',
            ]);
            $detaillog[] = $datadetails->toArray();
        }

        if ($data['stok_idbtg'] != null) {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'MND')->first();
            $gambarbtg = '';
            if ($data['gambarbtg'] != null) {
                $gambarbtg = $this->saveFiles('stokpusat/btg/', config('app.pic_url_btg'), $data['gambarbtg'], str_replace(' ', '_', $data['namastokbtg']));
            }
            $datadetails = (new StokPusatRincian())->processStore($stokPusat, [
                'namastok' => str_replace("''", '"',  strtoupper($data['namastokbtg'])),
                'kelompok_id' => $data['kelompok_id'],
                'stok_id' => $data['stok_idbtg'],
                'cabang_id' => $getCabang->id,
                'gambar' => ($data['gambarbtg'] != null) ? json_encode([$gambarbtg]) : '',
            ]);
            $detaillog[] = $datadetails->toArray();
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($datadetails->getTable()),
            'postingdari' => 'ENTRY STOK PUSAT RINCIAN',
            'idtrans' =>  $storedLogTrail['id'],
            'nobuktitrans' => $stokPusat->id,
            'aksi' => 'ENTRY',
            'datajson' => $detaillog,
            'modifiedby' => auth('api')->user()->user
        ]);

        $this->saveToCabang($data);
        return $stokPusat;
    }


    public function processUpdate(StokPusat $stokPusat, array $data): StokPusat
    {
        $stokPusat->namastok = str_replace("''", '"',  strtoupper($data['namaterpusat']));
        $stokPusat->kelompok_id = $data['kelompok_id'];
        $stokPusat->modifiedby = auth('api')->user()->name;
        $stokPusat->info = html_entity_decode(request()->info);

        if (!$stokPusat->save()) {
            throw new \Exception("Error storing stokPusat.");
        }

        $storedLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($stokPusat->getTable()),
            'postingdari' => 'EDIT STOK PUSAT',
            'idtrans' => $stokPusat->id,
            'nobuktitrans' => $stokPusat->id,
            'aksi' => 'EDIT',
            'datajson' => $stokPusat->toArray(),
            'modifiedby' => $stokPusat->modifiedby
        ]);

        $detaillog = [];

        $data['namaterpusat'] = $stokPusat->namastok;

        if ($data['stok_idmdn'] != null) {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'MDN')->first();

            $mdn = (new StokPusatRincian())->findMdn($stokPusat->id);
            $gambarmdn = '';
            if ($data['gambarmdn'] != null) {
                if ($mdn != null) {
                    if($mdn->gambar != '') {
                       
                        $gbrMedan = json_decode($mdn->gambar)[0];
                        if ($data['gambarmdn'] != $gbrMedan) {
                            if ($gbrMedan != null) {
                                Storage::delete("stokpusat/mdn/$gbrMedan");
                            }
                        }     
                    }               
                } 

                $destinationPath = 'stokpusat/mdn/';
                $imageUrl = "http://tasmdn.kozow.com:8074/trucking-api/public/api/stok/" . str_replace(' ', '%20', $data['gambarmdn']) . "/medium";
                $destinationFileName = $data['gambarmdn'];
                $imageData = file_get_contents($imageUrl);
                Storage::put($destinationPath . $destinationFileName, $imageData);

            } else {
                if ($mdn != null) {
                    if($mdn->gambar != ''){
                        $gbrMedan = json_decode($mdn->gambar)[0];
                        if ($gbrMedan != null) {
                            Storage::delete("stokpusat/mdn/$gbrMedan");
                        }
                    }
                }
            }

            StokPusatRincian::where('stokpusat_id', $stokPusat->id)->where('cabang_id', $getCabang->id)->delete();
            $datadetails = (new StokPusatRincian())->processStore($stokPusat, [
                'namastok' => str_replace("''", '"',  strtoupper($data['namastokmdn'])),
                'kelompok_id' => $data['kelompok_id'],
                'stok_id' => $data['stok_idmdn'],
                'cabang_id' => $getCabang->id,
                'gambar' => ($data['gambarmdn'] != null) ? json_encode([$data['gambarmdn']]) : '',
            ]);
            $detaillog[] = $datadetails->toArray();
        } else {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'MDN')->first();
            $mdn = (new StokPusatRincian())->findMdn($stokPusat->id);
            if ($mdn != null) {
                if ($mdn->gambar != null) {
                    $gbrMedan = json_decode($mdn->gambar)[0];
                    Storage::delete("stokpusat/mdn/$gbrMedan");
                }
                StokPusatRincian::where('stokpusat_id', $stokPusat->id)->where('cabang_id', $getCabang->id)->delete();
                $data['stok_idmdndel'] = $mdn->stok_id;
            }
        }

        if ($data['stok_idjkt'] != null) {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'JKT')->first();

            $jkt = (new StokPusatRincian())->findJkt($stokPusat->id);
            if ($data['gambarjkt'] != null) {
                if ($jkt != null) {
                    if($jkt->gambar !=''){
                        
                        $gbrJkt = json_decode($jkt->gambar)[0];
                        if ($data['gambarjkt'] != $gbrJkt) {
                            if ($gbrJkt != null) {
                                Storage::delete("stokpusat/jkt/$gbrJkt");
                            }
                        }
                    }
                }

                $destinationPath = 'stokpusat/jkt/';
                $imageUrl = "http://tasjkt.kozow.com:8074/trucking-api/public/api/stok/" . str_replace(' ', '%20', $data['gambarjkt']) . "/medium";
                $destinationFileName = $data['gambarjkt'];
                $imageData = file_get_contents($imageUrl);
                Storage::put($destinationPath . $destinationFileName, $imageData);
            } else {
                if ($jkt != null) {
                    if($jkt->gambar != ''){
                        
                    $gbrJkt = json_decode($jkt->gambar)[0];
                    if ($gbrJkt != null) {
                        Storage::delete("stokpusat/jkt/$gbrJkt");
                    }
                }
                }
            }

            StokPusatRincian::where('stokpusat_id', $stokPusat->id)->where('cabang_id', $getCabang->id)->delete();
            $datadetails = (new StokPusatRincian())->processStore($stokPusat, [
                'namastok' => str_replace("''", '"',  strtoupper($data['namastokjkt'])),
                'kelompok_id' => $data['kelompok_id'],
                'stok_id' => $data['stok_idjkt'],
                'cabang_id' => $getCabang->id,
                'gambar' => ($data['gambarjkt'] != null) ? json_encode([$data['gambarjkt']]) : '',
            ]);
            $detaillog[] = $datadetails->toArray();
        } else {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'JKT')->first();
            $jkt = (new StokPusatRincian())->findJkt($stokPusat->id);
            if ($jkt != null) {
                if ($jkt->gambar != null) {
                    $gbrJkt = json_decode($jkt->gambar)[0];
                    Storage::delete("stokpusat/jkt/$gbrJkt");
                }
                StokPusatRincian::where('stokpusat_id', $stokPusat->id)->where('cabang_id', $getCabang->id)->delete();
                $data['stok_idjktdel'] = $jkt->stok_id;
            }
        }

        if ($data['stok_idjkttnl'] != null) {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'TNL')->first();

            $jkttnl = (new StokPusatRincian())->findJktTnl($stokPusat->id);
            if ($data['gambarjkttnl'] != null) {
                if ($jkttnl != null) {
                    if($jkttnl->gambar !=''){
                        
                    $gbrJktTnl = json_decode($jkttnl->gambar)[0];
                        if ($data['gambarjkttnl'] != $gbrJktTnl) {
                            if ($gbrJktTnl != null) {
                                Storage::delete("stokpusat/jkttnl/$gbrJktTnl");
                            }
                        }
                    }
                }

                $destinationPath = 'stokpusat/jkttnl/';
                $imageUrl = "http://tasjkt.kozow.com:8074/truckingtnl-api/public/api/stok/" . str_replace(' ', '%20', $data['gambarjkttnl']) . "/medium";
                $destinationFileName = $data['gambarjkttnl'];
                $imageData = file_get_contents($imageUrl);
                Storage::put($destinationPath . $destinationFileName, $imageData);
            } else {
                if ($jkttnl != null) {
                    
                    if($jkttnl->gambar != ''){
                    $gbrJktTnl = json_decode($jkttnl->gambar)[0];
                    if ($gbrJktTnl != null) {
                        Storage::delete("stokpusat/jkttnl/$gbrJktTnl");
                    }}
                }
            }

            StokPusatRincian::where('stokpusat_id', $stokPusat->id)->where('cabang_id', $getCabang->id)->delete();
            $datadetails = (new StokPusatRincian())->processStore($stokPusat, [
                'namastok' => str_replace("''", '"',  strtoupper($data['namastokjkttnl'])),
                'kelompok_id' => $data['kelompok_id'],
                'stok_id' => $data['stok_idjkttnl'],
                'cabang_id' => $getCabang->id,
                'gambar' => ($data['gambarjkttnl'] != null) ? json_encode([$data['gambarjkttnl']]) : '',
            ]);
            $detaillog[] = $datadetails->toArray();
        } else {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'TNL')->first();
            $jkttnl = (new StokPusatRincian())->findJktTnl($stokPusat->id);
            if ($jkttnl != null) {
                if ($jkttnl->gambar != null) {
                    $gbrJktTnl = json_decode($jkttnl->gambar)[0];
                    Storage::delete("stokpusat/jkttnl/$gbrJktTnl");
                }
                StokPusatRincian::where('stokpusat_id', $stokPusat->id)->where('cabang_id', $getCabang->id)->delete();
                $data['stok_idjkttnldel'] = $jkttnl->stok_id;
            }
        }

        
        if ($data['stok_idmks'] != null) {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'MKS')->first();

            $mks = (new StokPusatRincian())->findMks($stokPusat->id);
            if ($data['gambarmks'] != null) {
                if ($mks != null) {
                    if($mks->gambar !=''){
                        
                        $gbrMks = json_decode($mks->gambar)[0];
                        if ($data['gambarmks'] != $gbrMks) {
                            if ($gbrMks != null) {
                                Storage::delete("stokpusat/mks/$gbrMks");
                            }
                        }
                    }
                }

                $destinationPath = 'stokpusat/mks/';
                $imageUrl = "http://tasmks.kozow.com:8074/trucking-api/public/api/stok/" . str_replace(' ', '%20', $data['gambarmks']) . "/medium";
                $destinationFileName = $data['gambarmks'];
                $imageData = file_get_contents($imageUrl);
                Storage::put($destinationPath . $destinationFileName, $imageData);
            } else {
                if ($mks != null) {
                    if($mks->gambar != ''){
                        
                    $gbrMks = json_decode($mks->gambar)[0];
                    if ($gbrMks != null) {
                        Storage::delete("stokpusat/mks/$gbrMks");
                    }
                }
                }
            }

            StokPusatRincian::where('stokpusat_id', $stokPusat->id)->where('cabang_id', $getCabang->id)->delete();
            $datadetails = (new StokPusatRincian())->processStore($stokPusat, [
                'namastok' => str_replace("''", '"',  strtoupper($data['namastokmks'])),
                'kelompok_id' => $data['kelompok_id'],
                'stok_id' => $data['stok_idmks'],
                'cabang_id' => $getCabang->id,
                'gambar' => ($data['gambarmks'] != null) ? json_encode([$data['gambarmks']]) : '',
            ]);
            $detaillog[] = $datadetails->toArray();
        } else {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'mks')->first();
            $mks = (new StokPusatRincian())->findMks($stokPusat->id);
            if ($mks != null) {
                if ($mks->gambar != null) {
                    $gbrMks = json_decode($mks->gambar)[0];
                    Storage::delete("stokpusat/mks/$gbrMks");
                }
                StokPusatRincian::where('stokpusat_id', $stokPusat->id)->where('cabang_id', $getCabang->id)->delete();
                $data['stok_idmksdel'] = $mks->stok_id;
            }
        }

        if ($data['stok_idsby'] != null) {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'SBY')->first();

            $sby = (new StokPusatRincian())->findSby($stokPusat->id);
            $gambarsby = '';
            if ($data['gambarsby'] != null) {
                if ($sby != null) {
                    if($sby->gambar != '') {
                        $gbrSby = json_decode($sby->gambar)[0];
                        if (trim($data['gambarsby']) != trim($gbrSby)) {
                            if ($gbrSby != null) {
                                Storage::delete("stokpusat/sby/$gbrSby");
                            }

                            $gambarsby = $this->saveFiles('stokpusat/sby/', config('app.pic_url_sby'), $data['gambarsby'], str_replace(' ', '_', $data['namastoksby']));
                        } else {
                            $gambarsby = $gbrSby;
                        }
                    }else{                        
                        $gambarsby = $this->saveFiles('stokpusat/sby/', config('app.pic_url_sby'), $data['gambarsby'], str_replace(' ', '_', $data['namastoksby']));
                    }
                } else {
                    $gambarsby = $this->saveFiles('stokpusat/sby/', config('app.pic_url_sby'), $data['gambarsby'], str_replace(' ', '_', $data['namastoksby']));
                }
            } else {
                if ($sby != null) {
                    if($sby->gambar != null){
                        $gbrSby = json_decode($sby->gambar)[0];
                        if ($gbrSby != null) {
                            Storage::delete("stokpusat/sby/$gbrSby");
                        }
                    }
                }
            }

            StokPusatRincian::where('stokpusat_id', $stokPusat->id)->where('cabang_id', $getCabang->id)->delete();
            $datadetails = (new StokPusatRincian())->processStore($stokPusat, [
                'namastok' => str_replace("''", '"',  strtoupper($data['namastoksby'])),
                'kelompok_id' => $data['kelompok_id'],
                'stok_id' => $data['stok_idsby'],
                'cabang_id' => $getCabang->id,
                'gambar' => ($data['gambarsby'] != null) ? json_encode([$gambarsby]) : '',
            ]);
            $detaillog[] = $datadetails->toArray();
        } else {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'SBY')->first();
            $sby = (new StokPusatRincian())->findSby($stokPusat->id);
            if ($sby != null) {
                if ($sby->gambar != null) {
                    $gbrSby = json_decode($sby->gambar)[0];
                    Storage::delete("stokpusat/sby/$gbrSby");
                }
                StokPusatRincian::where('stokpusat_id', $stokPusat->id)->where('cabang_id', $getCabang->id)->delete();
                $data['stok_idsbydel'] = $sby->stok_id;
            }
        }

        if ($data['stok_idbtg'] != null) {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'MND')->first();

            $btg = (new StokPusatRincian())->findBtg($stokPusat->id);
            $gambarbtg = '';
            if ($data['gambarbtg'] != null) {
                if ($btg != null) {
                    if($btg->gambar != '') {
                        $gbrBtg = json_decode($btg->gambar)[0];
                        if (trim($data['gambarbtg']) != trim($gbrBtg)) {
                            if ($gbrBtg != null) {
                                Storage::delete("stokpusat/btg/$gbrBtg");
                            }

                            $gambarbtg = $this->saveFiles('stokpusat/btg/', config('app.pic_url_btg'), $data['gambarbtg'], str_replace(' ', '_', $data['namastokbtg']));
                        } else {
                            $gambarbtg = $gbrBtg;
                        }
                    }else{
                        $gambarbtg = $this->saveFiles('stokpusat/btg/', config('app.pic_url_btg'), $data['gambarbtg'], str_replace(' ', '_', $data['namastokbtg']));
                    }
                } else {
                    $gambarbtg = $this->saveFiles('stokpusat/btg/', config('app.pic_url_btg'), $data['gambarbtg'], str_replace(' ', '_', $data['namastokbtg']));
                }
            } else {
                if ($btg != null) {
                    if($btg->gambar != ''){
                        $gbrBtg = json_decode($btg->gambar)[0];
                        if ($gbrBtg != null) {
                            Storage::delete("stokpusat/btg/$gbrBtg");
                        }
                    }
                }
            }

            StokPusatRincian::where('stokpusat_id', $stokPusat->id)->where('cabang_id', $getCabang->id)->delete();
            $datadetails = (new StokPusatRincian())->processStore($stokPusat, [
                'namastok' => str_replace("''", '"',  strtoupper($data['namastokbtg'])),
                'kelompok_id' => $data['kelompok_id'],
                'stok_id' => $data['stok_idbtg'],
                'cabang_id' => $getCabang->id,
                'gambar' => ($data['gambarbtg'] != null) ? json_encode([$gambarbtg]) : '',
            ]);
            $detaillog[] = $datadetails->toArray();
        } else {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'MND')->first();
            $btg = (new StokPusatRincian())->findBtg($stokPusat->id);
            if ($btg != null) {
                if ($btg->gambar != null) {
                    $gbrBtg = json_decode($btg->gambar)[0];
                    Storage::delete("stokpusat/btg/$gbrBtg");
                }
                StokPusatRincian::where('stokpusat_id', $stokPusat->id)->where('cabang_id', $getCabang->id)->delete();
                $data['stok_idbtgdel'] = $btg->stok_id;
            }
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($datadetails->getTable()),
            'postingdari' => 'EDIT STOK PUSAT RINCIAN',
            'idtrans' =>  $storedLogTrail['id'],
            'nobuktitrans' => $stokPusat->id,
            'aksi' => 'EDIT',
            'datajson' => $detaillog,
            'modifiedby' => auth('api')->user()->user
        ]);


        $this->saveToCabang($data);

        // session()->forget('access_token_mdn_stok');
        return $stokPusat;
    }

    public function processDestroy($id, $postingDari = ''): StokPusat
    {
        $stokPusatRincian = StokPusatRincian::lockForUpdate()->where('stokpusat_id', $id)->get();
        $data = [

            'stok_idmdn' => '',
            'stok_idjkt' => '',
            'stok_idjkttnl' => '',
            'stok_idmks' => '',
            'stok_idsby' => '',
            'stok_idbtg' => ''
        ];
        // MDN
        $mdn = (new StokPusatRincian())->findMdn($id);
        if ($mdn != null) {
            $gbrMedan = json_decode($mdn->gambar)[0];
            if ($gbrMedan != null) {
                Storage::delete("stokpusat/mdn/$gbrMedan");
            }
            $data['stok_idmdndel'] = $mdn->stok_id;
        }
        // JKT
        $jkt = (new StokPusatRincian())->findJkt($id);
        if ($jkt != null) {
            $gbrJkt = json_decode($jkt->gambar)[0];
            if ($gbrJkt != null) {
                Storage::delete("stokpusat/jkt/$gbrJkt");
            }
            $data['stok_idjktdel'] = $jkt->stok_id;
        }
        // TNL
        $jkttnl = (new StokPusatRincian())->findJktTnl($id);
        if ($jkttnl != null) {
            $gbrJktTnl = json_decode($jkttnl->gambar)[0];
            if ($gbrJktTnl != null) {
                Storage::delete("stokpusat/jkttnl/$gbrJktTnl");
            }
            $data['stok_idjkttnldel'] = $jkttnl->stok_id;
        }
        // SBY
        $sby = (new StokPusatRincian())->findSby($id);
        if ($sby != null) {
            $gbrSby = json_decode($sby->gambar)[0];
            if ($gbrSby != null) {
                Storage::delete("stokpusat/sby/$gbrSby");
            }
            $data['stok_idsbydel'] = $sby->stok_id;
        }
        // MKS
        $mks = (new StokPusatRincian())->findMks($id);
        if ($mks != null) {
            $gbrMks = json_decode($mks->gambar)[0];
            if ($gbrMks != null) {
                Storage::delete("stokpusat/mks/$gbrMks");
            }
            $data['stok_idmksdel'] = $mks->stok_id;
        }
        // BTG
        $btg = (new StokPusatRincian())->findBtg($id);
        if ($btg != null) {
            $gbrBtg = json_decode($btg->gambar)[0];
            if ($gbrBtg != null) {
                Storage::delete("stokpusat/btg/$gbrBtg");
            }
            $data['stok_idbtgdel'] = $btg->stok_id;
        }

        $stokPusat = new StokPusat();
        $stokPusat = $stokPusat->lockAndDestroy($id);

        $stokPusatLogTrail = (new LogTrail())->processStore([
            'namatabel' => $stokPusat->getTable(),
            'postingdari' => $postingDari,
            'idtrans' => $stokPusat->id,
            'nobuktitrans' => $stokPusat->id,
            'aksi' => 'DELETE',
            'datajson' => $stokPusat->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'STOKPUSATRINCIAN',
            'postingdari' => $postingDari,
            'idtrans' => $stokPusatLogTrail['id'],
            'nobuktitrans' => $stokPusat->id,
            'aksi' => 'DELETE',
            'datajson' => $stokPusatRincian->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        $this->saveToCabang($data);
        return $stokPusat;
    }

    public function getData($kelompok_id)
    {
        $kelompok = DB::table("kelompok")->from(DB::raw("kelompok with (readuncommitted)"))->where('id', $kelompok_id)->first();
        $cabang = Cabang::whereRaw("memo IS NOT NULL")->where('statusaktif', 1)->get();
        $koneksi = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS KONEKSI')->where('text', 'ONLINE')->first();

        foreach ($cabang as $value) {
            $memo = json_decode($value->memo, TRUE);
            $cabang = strtolower($value->kodecabang);
            if ($value->statuskoneksi != $koneksi->text) {
                $data[$cabang] = [];
            } else {

                if ($memo['TARIK_STOK'] == 'YA' && $memo['WEB'] == 'TIDAK') {
                    $query = DB::connection('sqlsrv' . $cabang)->table('Stck')->from(DB::raw("Stck with (readuncommitted)"))->select('FID as id', 'FNstck as namastok', 'FPic1 as gambar', 'FSubKelompok as subkelompok')->where('FKelompok', $kelompok->kodekelompok);
                    $data[$cabang] = $query->get();
                }

                if ($memo['TARIK_STOK'] == 'YA' && $memo['WEB'] == 'YA') {
                    $urlCabang = env($memo['URL']);

                    $userCabang = env('USER_JAKARTA');
                    $passwordCabang = env('PASSWORD_JAKARTA');
                    $getToken = Http::withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ])
                        ->post($urlCabang . 'token', [
                            'user' => $userCabang,
                            'password' => $passwordCabang,
                            'ipclient' => '',
                            'ipserver' => '',
                            'latitude' => '',
                            'longitude' => '',
                            'browser' => '',
                            'os' => '',
                        ]);
                    if ($getToken->getStatusCode() != '200') {
                        $data[$cabang] = [];
                    } else {
                        $access_token = json_decode($getToken, TRUE)['access_token'];

                        $getData = Http::withHeaders([
                            'Accept' => 'application/json',
                            'Authorization' => 'Bearer ' . $access_token,
                            'Content-Type' => 'application/json',
                        ])
                            ->get($urlCabang . "stok?kelompok_id=" . $kelompok_id . "&limit=0");
                        $data[$cabang] = $getData->json()['data'];
                    }
                }
            }
        }
        return $data;
    }


    public function dataMnd($kelompok_id)
    {
        $this->setRequestParameters();

        $cekParam = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS KONEKSI')->where('text', 'OFFLINE')->first();
        $cabang = Cabang::where('id', 9)->first();
        if ($cabang->statuskoneksi != $cekParam->id) {
            $kelompok = DB::table("kelompok")->from(DB::raw("kelompok with (readuncommitted)"))->where('id', $kelompok_id)->first();
            $query = DB::connection('sqlsrvmnd')->table('Stck')->from(DB::raw("Stck as a with (readuncommitted)"))
                ->select('FID as id', 'FNstck as namastok', DB::raw("isnull(FPic1, '') as gambar"), 'FSubKelompok as subkelompok')
                ->where('FKelompok', $kelompok->kodekelompok);

            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $sortIndex = request()->sortIndex ?? 'FID';
            $this->sortData($query, $sortIndex);
            $this->filterData($query);
            $this->paginateData($query);
            $data = $query->get();
        } else {
            $data = [];
        }
        return $data;
    }

    public function dataMdn($kelompok_id)
    {
        $cekParam = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS KONEKSI')->where('text', 'OFFLINE')->first();
        $cabang = Cabang::where('id', 2)->first();
        if ($cabang->statuskoneksi != $cekParam->id) {
            // $this->setRequestParameters();

            // $kelompok = DB::table("kelompok")->from(DB::raw("kelompok with (readuncommitted)"))->where('id', $kelompok_id)->first();
            // $query = DB::connection('sqlsrvmdn')->table('Stck')->from(DB::raw("Stck as a with (readuncommitted)"))
            //     ->select('FID as id', 'FNstck as namastok', DB::raw("isnull(FPic1, '') as gambar"), 'FSubKelompok as subkelompok')
            //     ->where('FKelompok', $kelompok->kodekelompok);

            // $this->totalRows = $query->count();
            // $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            // $sortIndex = request()->sortIndex ?? 'FID';
            // $this->sortData($query, $sortIndex);
            // $this->filterData($query);
            // $this->paginateData($query);
            // $data = $query->get();
            $server = config('app.url_token_mdn');
            $userCabang = env('USER_JAKARTA');
            $passwordCabang = env('PASSWORD_JAKARTA');
            $getToken = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])
                ->post($server, [
                    'user' => $userCabang,
                    'password' => $passwordCabang,
                    'ipclient' => '',
                    'ipserver' => '',
                    'latitude' => '',
                    'longitude' => '',
                    'browser' => '',
                    'os' => '',
                ]);
            if ($getToken->getStatusCode() != '200') {
                throw new \Exception("SERVER MEDAN TIDAK BISA DIAKSES");
            } else {
                $access_token = json_decode($getToken, TRUE)['access_token'];
                session(['access_token_mdn_stok' => $access_token]);
                $data = $access_token;
            }
        } else {
            $data = '';
        }
        return $data;
    }
    public function dataSby($kelompok_id)
    {
        $cekParam = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS KONEKSI')->where('text', 'OFFLINE')->first();
        $cabang = Cabang::where('id', 4)->first();
        if ($cabang->statuskoneksi != $cekParam->id) {
            $this->setRequestParameters();

            $kelompok = DB::table("kelompok")->from(DB::raw("kelompok with (readuncommitted)"))->where('id', $kelompok_id)->first();
            $query = DB::connection('sqlsrvsby')->table('Stck')->from(DB::raw("Stck as a with (readuncommitted)"))
                ->select('FID as id', 'FNstck as namastok', DB::raw("isnull(FPic1, '') as gambar"), 'FSubKelompok as subkelompok')
                ->where('FKelompok', $kelompok->kodekelompok);

            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $sortIndex = request()->sortIndex ?? 'FID';
            $this->sortData($query, $sortIndex);
            $this->filterData($query);
            $this->paginateData($query);
            $data = $query->get();
        } else {
            $data = [];
        }
        return $data;
    }

    public function dataMks($kelompok_id)
    {
        $cekParam = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS KONEKSI')->where('text', 'OFFLINE')->first();
        $cabang = Cabang::where('id', 5)->first();
        if ($cabang->statuskoneksi != $cekParam->id) {
            // $this->setRequestParameters();

            // $kelompok = DB::table("kelompok")->from(DB::raw("kelompok with (readuncommitted)"))->where('id', $kelompok_id)->first();
            // $query = DB::connection('sqlsrvmks')->table('Stck')->from(DB::raw("Stck as a with (readuncommitted)"))
            //     ->select('FID as id', 'FNstck as namastok', DB::raw("isnull(FPic1, '') as gambar"), 'FSubKelompok as subkelompok')
            //     ->where('FKelompok', $kelompok->kodekelompok);

            // $this->totalRows = $query->count();
            // $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            // $sortIndex = request()->sortIndex ?? 'FID';
            // $this->sortData($query, $sortIndex);
            // $this->filterData($query);
            // $this->paginateData($query);
            // $data = $query->get();
            $server = config('app.url_token_mks');
            $userCabang = env('USER_JAKARTA');
            $passwordCabang = env('PASSWORD_JAKARTA');
            $getToken = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])
                ->post($server, [
                    'user' => $userCabang,
                    'password' => $passwordCabang,
                    'ipclient' => '',
                    'ipserver' => '',
                    'latitude' => '',
                    'longitude' => '',
                    'browser' => '',
                    'os' => '',
                ]);
            if ($getToken->getStatusCode() != '200') {
                throw new \Exception("SERVER MAKASSAR TIDAK BISA DIAKSES");
            } else {
                $access_token = json_decode($getToken, TRUE)['access_token'];
                session(['access_token_mks_stok' => $access_token]);
                $data = $access_token;
            }
        } else {
            $data = '';
        }
        return $data;
    }

    public function dataJkt($kelompok_id)
    {
        $cekParam = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS KONEKSI')->where('text', 'OFFLINE')->first();
        $cabang = Cabang::where('id', 3)->first();
        if ($cabang->statuskoneksi != $cekParam->id) {
            $server = config('app.url_token_jkt');
            $userCabang = env('USER_JAKARTA');
            $passwordCabang = env('PASSWORD_JAKARTA');
            $getToken = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])
                ->post($server, [
                    'user' => $userCabang,
                    'password' => $passwordCabang,
                    'ipclient' => '',
                    'ipserver' => '',
                    'latitude' => '',
                    'longitude' => '',
                    'browser' => '',
                    'os' => '',
                ]);
            if ($getToken->getStatusCode() != '200') {
                throw new \Exception("SERVER JAKARTA TIDAK BISA DIAKSES");
            } else {
                $access_token = json_decode($getToken, TRUE)['access_token'];
                session(['access_token_jkt_stok' => $access_token]);
                $data = $access_token;
            }
        } else {
            $data = '';
        }


        return $data;
    }

    public function dataJktTnl($kelompok_id)
    {
        $cekParam = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS KONEKSI')->where('text', 'OFFLINE')->first();
        $cabang = Cabang::where('id', 7)->first();
        if ($cabang->statuskoneksi != $cekParam->id) {
            $server = config('app.url_token_jkttnl');
            $userCabang = env('USER_JAKARTA');
            $passwordCabang = env('PASSWORD_JAKARTA');
            $getToken = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])
                ->post($server, [
                    'user' => $userCabang,
                    'password' => $passwordCabang,
                    'ipclient' => '',
                    'ipserver' => '',
                    'latitude' => '',
                    'longitude' => '',
                    'browser' => '',
                    'os' => '',
                ]);
            if ($getToken->getStatusCode() != '200') {
                throw new \Exception("SERVER JAKARTA TNL TIDAK BISA DIAKSES");
            } else {
                $access_token = json_decode($getToken, TRUE)['access_token'];
                $data = $access_token;
            }
        } else {
            $data = '';
        }

        return $data;
    }

    public function sortData($query, $sortIndex)
    {
        return $query->orderBy('a.' . $sortIndex, $this->params['sortOrder']);
    }

    public function filterData($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'namastok') {
                            $query = $query->where('a.FNstck', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'subkelompok') {
                            $query = $query->where('a.FSubKelompok', 'LIKE', "%$filters[data]%");
                        } else {
                            // $query = $query->whereRaw($this->table . ".".  $filters['field'] ." LIKE '%".str_replace($filters['data'],'[','|[') ."%' escape '|'");
                            $query = $query->whereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'namastok') {
                                $query = $query->orWhere('a.FNstck', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'subkelompok') {
                                $query = $query->orWhere('a.FSubKelompok', 'LIKE', "%$filters[data]%");
                            } else {
                                $query = $query->OrwhereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function paginateData($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }


    private function deleteFiles(UpahSupir $upahsupir)
    {
        $sizeTypes = ['', 'medium_', 'small_'];

        $relatedPhotoUpahSupir = [];
        $photoUpahSupir = json_decode($upahsupir->gambar, true);
        if ($photoUpahSupir) {
            foreach ($photoUpahSupir as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoUpahSupir[] = "upahsupir/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoUpahSupir);
        }
    }

    public function saveFiles($path, $server, $gambar, $namaStok)
    {
        $imageUrl = $server . "view.php?path=" .  urlencode($gambar);
        $extension = pathinfo($imageUrl, PATHINFO_EXTENSION);
        $destinationFileName = $namaStok . '.' . $extension;
        $imageData = file_get_contents($imageUrl);
        Storage::put($path . $destinationFileName, $imageData);
        return $destinationFileName;
    }

    public function saveToCabang($data)
    {
        $cekKoneksi = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('statuskoneksi', 542)->get();
        $cabangTerkoneksi = [];
        foreach ($cekKoneksi as $row) {
            $cabang = $row->kodecabang;
            $cabangTerkoneksi[$cabang] = true;
        }
        $data['cekKoneksi'] = $cabangTerkoneksi;
        if (array_key_exists('MDN', $cabangTerkoneksi)) {

            if ($data['stok_idmdn'] != '') {
                $accessTokenMdnStok = session('access_token_mdn_stok');
                $data['konsolidasi'] = true;

                if (!$accessTokenMdnStok) {
                    $postRequest = [
                        'user' => config('app.user_api'),
                        'password' => config('app.pass_api'),
                        'ipclient' => '',
                        'ipserver' => '',
                        'latitude' => '',
                        'longitude' => '',
                        'browser' => '',
                        'os' => '',
                    ];
                    $token = $this->getToken(config('app.url_token_mdn'), $postRequest);

                    $token = json_decode($token, TRUE);
                    if ($token != '') {

                        if (array_key_exists('access_token', $token)) {
                            $accessToken = $token['access_token'];
                            session(['access_token_mdn_stok' => $token['access_token']]);
                            $send = $this->postData(config('app.url_post_konsol_mdn'), 'POST', $accessToken, $data);
                            $send = json_decode($send, TRUE);

                            if (array_key_exists('status', $send)) {
                                goto selesai;
                            } else {
                                throw new \Exception($send['message']);
                            }
                        } else {
                            throw new \Exception("server Medan tidak bisa diakses");
                        }
                    } else {
                        throw new \Exception("server Medan tidak bisa diakses");
                    }
                } else {
                    $send = $this->postData(config('app.url_post_konsol_mdn'), 'POST', $accessTokenMdnStok, $data);

                    $send = json_decode($send, TRUE);
                    if (array_key_exists('status', $send)) {
                        goto selesai;
                    } else {
                        throw new \Exception($send['message']);
                    }
                }
            }
            if ($data['stok_idmdndel'] != '') {
                $accessTokenMdnStok = session('access_token_mdn_stok');
                $data['konsolidasi'] = true;
                if (!$accessTokenMdnStok) {
                    $postRequest = [
                        'user' => config('app.user_api'),
                        'password' => config('app.pass_api'),
                        'ipclient' => '',
                        'ipserver' => '',
                        'latitude' => '',
                        'longitude' => '',
                        'browser' => '',
                        'os' => '',
                    ];
                    $token = $this->getToken(config('app.url_token_mdn'), $postRequest);

                    $token = json_decode($token, TRUE);
                    if ($token != '') {
                        if (array_key_exists('access_token', $token)) {
                            $accessToken = $token['access_token'];
                            session(['access_token_mdn_stok' => $token['access_token']]);
                            $send = $this->postData(config('app.url_post_konsol_mdn'), 'POST', $accessToken, $data);
                            $send = json_decode($send, TRUE);
                            if (array_key_exists('status', $send)) {
                                goto selesai;
                            } else {
                                throw new \Exception($send['message']);
                            }
                        } else {
                            throw new \Exception("server Medan tidak bisa diakses");
                        }
                    } else {
                        throw new \Exception("server Medan tidak bisa diakses");
                    }
                } else {
                    $send = $this->postData(config('app.url_post_konsol_mdn'), 'POST', $accessTokenMdnStok, $data);

                    $send = json_decode($send, TRUE);
                    if (array_key_exists('status', $send)) {
                        goto selesai;
                    } else {
                        throw new \Exception($send['message']);
                    }
                }
            }
        }

        if (array_key_exists('MND', $cabangTerkoneksi)) {
            if ($data['stok_idbtg'] != '') {
                $accessTokenBtgStok = session('access_token_btg_stok');
                if (!$accessTokenBtgStok) {
                    $postRequest = [
                        'grant_type' => 'client_credentials',
                        'client_id' => config('app.client_id_btg'),
                        'client_secret' =>  config('app.client_secret_btg')
                    ];
                    $token = $this->getToken(config('app.url_token_btg'), $postRequest);

                    $token = json_decode($token, TRUE);
                    if ($token != '') {

                        if (array_key_exists('access_token', $token)) {
                            $accessToken = $token['access_token'];
                            session(['access_token_btg_stok' => $token['access_token']]);
                            $send = $this->postData(config('app.url_post_konsol_btg'), 'POST', $accessToken, $data);
                            $send = json_decode($send, TRUE);
                            if (array_key_exists('status', $send)) {
                                goto selesai;
                            } else {
                                throw new \Exception($send['message']);
                            }
                        } else {
                            throw new \Exception("server Bitung tidak bisa diakses");
                        }
                    } else {
                        throw new \Exception("server Bitung tidak bisa diakses");
                    }
                } else {
                    $send = $this->postData(config('app.url_post_konsol_btg'), 'POST', $accessTokenBtgStok, $data);

                    $send = json_decode($send, TRUE);
                    if (array_key_exists('status', $send)) {
                        goto selesai;
                    } else {
                        throw new \Exception($send['message']);
                    }
                }
            }
            if ($data['stok_idbtgdel'] != '') {
                $accessTokenBtgStok = session('access_token_btg_stok');
                if (!$accessTokenBtgStok) {
                    $postRequest = [
                        'grant_type' => 'client_credentials',
                        'client_id' => config('app.client_id_btg'),
                        'client_secret' =>  config('app.client_secret_btg')
                    ];
                    $token = $this->getToken(config('app.url_token_btg'), $postRequest);

                    $token = json_decode($token, TRUE);
                    if ($token != '') {

                        if (array_key_exists('access_token', $token)) {
                            $accessToken = $token['access_token'];
                            session(['access_token_btg_stok' => $token['access_token']]);
                            $send = $this->postData(config('app.url_post_konsol_btg'), 'POST', $accessToken, $data);
                            $send = json_decode($send, TRUE);
                            if (array_key_exists('status', $send)) {
                                goto selesai;
                            } else {
                                throw new \Exception($send['message']);
                            }
                        } else {
                            throw new \Exception("server Bitung tidak bisa diakses");
                        }
                    } else {
                        throw new \Exception("server Bitung tidak bisa diakses");
                    }
                } else {
                    $send = $this->postData(config('app.url_post_konsol_btg'), 'POST', $accessTokenBtgStok, $data);

                    $send = json_decode($send, TRUE);
                    if (array_key_exists('status', $send)) {
                        goto selesai;
                    } else {
                        throw new \Exception($send['message']);
                    }
                }
            }
        }

        if (array_key_exists('SBY', $cabangTerkoneksi)) {
            if ($data['stok_idsby'] != '') {
                $accessTokenSbyStok = session('access_token_sby_stok');
                if (!$accessTokenSbyStok) {
                    $postRequest = [
                        'grant_type' => 'client_credentials',
                        'client_id' => config('app.client_id_sby'),
                        'client_secret' =>  config('app.client_secret_sby')
                    ];
                    $token = $this->getToken(config('app.url_token_sby'), $postRequest);

                    $token = json_decode($token, TRUE);
                    if ($token != '') {
                        if (array_key_exists('access_token', $token)) {
                            $accessToken = $token['access_token'];
                            session(['access_token_sby_stok' => $token['access_token']]);
                            $send = $this->postData(config('app.url_post_konsol_sby'), 'POST', $accessToken, $data);
                            $send = json_decode($send, TRUE);
                            if (array_key_exists('status', $send)) {
                                goto selesai;
                            } else {
                                throw new \Exception($send['message']);
                            }
                        } else {
                            throw new \Exception("server Surabaya tidak bisa diakses");
                        }
                    } else {
                        throw new \Exception("server Surabaya tidak bisa diakses");
                    }
                } else {
                    $send = $this->postData(config('app.url_post_konsol_sby'), 'POST', $accessTokenSbyStok, $data);

                    $send = json_decode($send, TRUE);

                    if (array_key_exists('status', $send)) {
                        goto selesai;
                    } else {
                        throw new \Exception($send['message']);
                    }
                }
            }
            if ($data['stok_idsbydel'] != '') {
                $accessTokenSbyStok = session('access_token_sby_stok');
                if (!$accessTokenSbyStok) {
                    $postRequest = [
                        'grant_type' => 'client_credentials',
                        'client_id' => config('app.client_id_sby'),
                        'client_secret' =>  config('app.client_secret_sby')
                    ];
                    $token = $this->getToken(config('app.url_token_sby'), $postRequest);
                    $token = json_decode($token, TRUE);
                    if ($token != '') {

                        if (array_key_exists('access_token', $token)) {
                            $accessToken = $token['access_token'];
                            session(['access_token_sby_stok' => $token['access_token']]);
                            $send = $this->postData(config('app.url_post_konsol_sby'), 'POST', $accessToken, $data);
                            $send = json_decode($send, TRUE);
                            if (array_key_exists('status', $send)) {
                                goto selesai;
                            } else {
                                throw new \Exception($send['message']);
                            }
                        } else {
                            throw new \Exception("server Surabaya tidak bisa diakses");
                        }
                    } else {
                        throw new \Exception("server Surabaya tidak bisa diakses");
                    }
                } else {
                    $send = $this->postData(config('app.url_post_konsol_sby'), 'POST', $accessTokenSbyStok, $data);

                    $send = json_decode($send, TRUE);

                    if (array_key_exists('status', $send)) {
                        goto selesai;
                    } else {
                        throw new \Exception($send['message']);
                    }
                }
            }
        }
        

        if (array_key_exists('MKS', $cabangTerkoneksi)) {
            if ($data['stok_idmks'] != '') {
                $accessTokenMksStok = session('access_token_mks_stok');
                $data['konsolidasi'] = true;
                if (!$accessTokenMksStok) {
                    $postRequest = [
                        'user' => config('app.user_api'),
                        'password' => config('app.pass_api'),
                        'ipclient' => '',
                        'ipserver' => '',
                        'latitude' => '',
                        'longitude' => '',
                        'browser' => '',
                        'os' => '',
                    ];
                    $token = $this->getToken(config('app.url_token_mks'), $postRequest);
                    
                    $token = json_decode($token, TRUE);
                    if($token != '' ){
                        
                        if (array_key_exists('access_token', $token)) {
                            $accessToken = $token['access_token'];
                            session(['access_token_mks_stok' => $token['access_token']]);

                            $send = $this->postData(config('app.url_post_konsol_mks'), 'POST', $accessToken, $data);
                            $send = json_decode($send, TRUE);
                            if (array_key_exists('status', $send)) {
                                goto selesai;
                            } else {
                                throw new \Exception($send['message']);
                            }
                        } else {
                            throw new \Exception("server Makassar tidak bisa diakses");
                        }
                    } else {
                        throw new \Exception("server Makassar tidak bisa diakses");
                    }
                } else {
                    $send = $this->postData(config('app.url_post_konsol_mks'), 'POST', $accessTokenMksStok, $data);

                    $send = json_decode($send, TRUE);
                    if (array_key_exists('status', $send)) {
                        goto selesai;
                    } else {
                        throw new \Exception($send['message']);
                    }
                }
            }
            if ($data['stok_idmksdel'] != '') {
                $accessTokenMksStok = session('access_token_mks_stok');
                $data['konsolidasi'] = true;
                if (!$accessTokenMksStok) {
                    $postRequest = [
                        'user' => config('app.user_api'),
                        'password' => config('app.pass_api'),
                        'ipclient' => '',
                        'ipserver' => '',
                        'latitude' => '',
                        'longitude' => '',
                        'browser' => '',
                        'os' => '',
                    ];
                    $token = $this->getToken(config('app.url_token_mks'), $postRequest);
                    
                    $token = json_decode($token, TRUE);
                    if($token != '' ){
                        
                        if (array_key_exists('access_token', $token)) {
                            $accessToken = $token['access_token'];
                            session(['access_token_mks_stok' => $token['access_token']]);

                            $send = $this->postData(config('app.url_post_konsol_mks'), 'POST', $accessToken, $data);
                            $send = json_decode($send, TRUE);
                            if (array_key_exists('status', $send)) {
                                goto selesai;
                            } else {
                                throw new \Exception($send['message']);
                            }
                        } else {
                            throw new \Exception("server Makassar tidak bisa diakses");
                        }
                    } else {
                        throw new \Exception("server Makassar tidak bisa diakses");
                    }
                } else {
                    $send = $this->postData(config('app.url_post_konsol_mks'), 'POST', $accessTokenMksStok, $data);

                    $send = json_decode($send, TRUE);
                    if (array_key_exists('status', $send)) {
                        goto selesai;
                    } else {
                        throw new \Exception($send['message']);
                    }
                }
            }
        }
        if (array_key_exists('JKT', $cabangTerkoneksi)) {
            if ($data['stok_idjkt'] != '') {
                $accessTokenJktStok = session('access_token_jkt_stok');
                $data['konsolidasi'] = true;
                if (!$accessTokenJktStok) {
                    $postRequest = [
                        'user' => config('app.user_api'),
                        'password' => config('app.pass_api'),
                        'ipclient' => '',
                        'ipserver' => '',
                        'latitude' => '',
                        'longitude' => '',
                        'browser' => '',
                        'os' => '',
                    ];
                    $token = $this->getToken(config('app.url_token_jkt'), $postRequest);

                    $token = json_decode($token, TRUE);
                    if ($token != '') {
                        if (array_key_exists('access_token', $token)) {
                            $accessToken = $token['access_token'];
                            session(['access_token_jkt_stok' => $token['access_token']]);

                            $send = $this->postData(config('app.url_post_konsol_jkt'), 'POST', $accessToken, $data);
                            $send = json_decode($send, TRUE);
                            if (array_key_exists('status', $send)) {
                                goto selesai;
                            } else {
                                throw new \Exception($send['message']);
                            }
                        } else {
                            throw new \Exception("server Jakarta tidak bisa diakses");
                        }
                    } else {
                        throw new \Exception("server Jakarta tidak bisa diakses");
                    }
                } else {
                    $send = $this->postData(config('app.url_post_konsol_jkt'), 'POST', $accessTokenJktStok, $data);

                    $send = json_decode($send, TRUE);
                    if (array_key_exists('status', $send)) {
                        goto selesai;
                    } else {
                        throw new \Exception($send['message']);
                    }
                }
            }
            if ($data['stok_idjktdel'] != '') {
                $accessTokenJktStok = session('access_token_jkt_stok');
                $data['konsolidasi'] = true;
                if (!$accessTokenJktStok) {
                    $postRequest = [
                        'user' => config('app.user_api'),
                        'password' => config('app.pass_api'),
                        'ipclient' => '',
                        'ipserver' => '',
                        'latitude' => '',
                        'longitude' => '',
                        'browser' => '',
                        'os' => '',
                    ];
                    $token = $this->getToken(config('app.url_token_jkt'), $postRequest);
                    $token = json_decode($token, TRUE);
                    if ($token != '') {

                        if (array_key_exists('access_token', $token)) {
                            $accessToken = $token['access_token'];
                            session(['access_token_jkt_stok' => $token['access_token']]);

                            $send = $this->postData(config('app.url_post_konsol_jkt'), 'POST', $accessToken, $data);
                            $send = json_decode($send, TRUE);
                            if (array_key_exists('status', $send)) {
                                goto selesai;
                            } else {
                                throw new \Exception($send['message']);
                            }
                        } else {
                            throw new \Exception("server Jakarta tidak bisa diakses");
                        }
                    } else {
                        throw new \Exception("server Jakarta tidak bisa diakses");
                    }
                } else {
                    $send = $this->postData(config('app.url_post_konsol_jkt'), 'POST', $accessTokenJktStok, $data);

                    $send = json_decode($send, TRUE);
                    if (array_key_exists('status', $send)) {
                        goto selesai;
                    } else {
                        throw new \Exception($send['message']);
                    }
                }
            }
        }
        if (array_key_exists('TNL', $cabangTerkoneksi)) {

            if ($data['stok_idjkttnl'] != '') {
                $accessTokenJktTnlStok = session('access_token_jkttnl_stok');
                $data['konsolidasi'] = true;
                if (!$accessTokenJktTnlStok) {
                    $postRequest = [
                        'user' => config('app.user_api'),
                        'password' => config('app.pass_api'),
                        'ipclient' => '',
                        'ipserver' => '',
                        'latitude' => '',
                        'longitude' => '',
                        'browser' => '',
                        'os' => '',
                    ];
                    $token = $this->getToken(config('app.url_token_jkttnl'), $postRequest);

                    $token = json_decode($token, TRUE);
                    if ($token != '') {
                        if (array_key_exists('access_token', $token)) {
                            $accessToken = $token['access_token'];
                            session(['access_token_jkttnl_stok' => $token['access_token']]);

                            $send = $this->postData(config('app.url_post_konsol_jkttnl'), 'POST', $accessToken, $data);
                            $send = json_decode($send, TRUE);
                            if (array_key_exists('status', $send)) {
                                goto selesai;
                            } else {
                                throw new \Exception($send['message']);
                            }
                        } else {
                            throw new \Exception("server Jakarta TNL tidak bisa diakses");
                        }
                    } else {
                        throw new \Exception("server Jakarta TNL tidak bisa diakses");
                    }
                } else {
                    $send = $this->postData(config('app.url_post_konsol_jkttnl'), 'POST', $accessTokenJktTnlStok, $data);

                    $send = json_decode($send, TRUE);
                    if (array_key_exists('status', $send)) {
                        goto selesai;
                    } else {
                        throw new \Exception($send['message']);
                    }
                }
            }
            if ($data['stok_idjkttnldel'] != '') {
                $accessTokenJktTnlStok = session('access_token_jkttnl_stok');
                $data['konsolidasi'] = true;
                if (!$accessTokenJktTnlStok) {
                    $postRequest = [
                        'user' => config('app.user_api'),
                        'password' => config('app.pass_api'),
                        'ipclient' => '',
                        'ipserver' => '',
                        'latitude' => '',
                        'longitude' => '',
                        'browser' => '',
                        'os' => '',
                    ];
                    $token = $this->getToken(config('app.url_token_jkttnl'), $postRequest);

                    $token = json_decode($token, TRUE);
                    if ($token != '') {
                        if (array_key_exists('access_token', $token)) {
                            $accessToken = $token['access_token'];
                            session(['access_token_jkttnl_stok' => $token['access_token']]);

                            $send = $this->postData(config('app.url_post_konsol_jkttnl'), 'POST', $accessToken, $data);
                            $send = json_decode($send, TRUE);
                            if (array_key_exists('status', $send)) {
                                goto selesai;
                            } else {
                                throw new \Exception($send['message']);
                            }
                        } else {
                            throw new \Exception("server Jakarta TNL tidak bisa diakses");
                        }
                    } else {
                        throw new \Exception("server Jakarta TNL tidak bisa diakses");
                    }
                } else {
                    $send = $this->postData(config('app.url_post_konsol_jkttnl'), 'POST', $accessTokenJktTnlStok, $data);

                    $send = json_decode($send, TRUE);
                    if (array_key_exists('status', $send)) {
                        goto selesai;
                    } else {
                        throw new \Exception($send['message']);
                    }
                }
            }
        }

        selesai:
        return true;
    }

    public function http_request(string $url, string $method = 'GET', array $headers = null, array $body = null): string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if (!empty($body)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, ['Content-Type: application/json']));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }

    // public function saveMedan($data)
    // {
    //     DB::connection("sqlsrvmdn")->beginTransaction();
    //     try {
    //         $query = DB::connection('sqlsrvmdn')->table('Stck')->where('FID', $data['stok_idmdn'])->update([
    //             'FNTerpusat' => strtoupper($data['namastok']),
    //         ]);
    //         $this->saveSby($data);
    //         DB::connection("sqlsrvmdn")->commit();
    //     } catch (\Throwable $th) {
    //         DB::connection("sqlsrvmdn")->rollBack();
    //         throw $th;
    //     }
    // }

    // public function saveSby($data)
    // {
    //     DB::connection("sqlsrvsby")->beginTransaction();
    //     try {
    //         $query = DB::connection('sqlsrvsby')->table('Stck')->where('FID', $data['stok_idsby'])->update([
    //             'FNTerpusat' => strtoupper($data['namastok']),
    //         ]);

    //         DB::connection("sqlsrvsby")->commit();
    //     } catch (\Throwable $th) {
    //         DB::connection("sqlsrvsby")->rollBack();
    //         throw $th;
    //     }
    // }

    public function getToken($server, $postRequest)
    {
        $token = $this->http_request(
            $server,
            'POST',
            [
                'Accept: application/json'
            ],
            $postRequest
        );

        return $token;
    }
    public function postData($server, $method, $accessToken, $data)
    {
        $send = $this->http_request(
            $server,
            $method,
            [
                'Authorization: Bearer ' . $accessToken,
                'Accept: application/json',
                'Content-Type: application/json'
            ],
            $data
        );
        return $send;
    }
}
