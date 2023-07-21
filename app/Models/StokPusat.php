<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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
        $this->setRequestParameters();
        $query = DB::table("stokpusat")->from(DB::raw("stokpusat with (readuncommitted)"))
            ->select(
                'stokpusat.id',
                'stokpusat.namastok',
                'kelompok.kodekelompok as kelompok',
                'stokpusat.modifiedby',
                'stokpusat.created_at',
                'stokpusat.updated_at'
            )
            ->leftJoin(DB::raw("kelompok with (readuncommitted)"), 'stokpusat.kelompok_id', 'kelompok.id');

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

    public function selectColumns($query)
    {

        return $query->select(
            DB::raw(
                "$this->table.id,
                 $this->table.namastok,
                 'kelompok.kodekelompok as kelompok',
                 $this->table.modifiedby,
                 $this->table.created_at,
                 $this->table.updated_at"
            )
        )
            ->leftJoin(DB::raw("kelompok with (readuncommitted)"), 'stokpusat.kelompok_id', 'kelompok.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('namastok', 1000)->nullable();
            $table->string('kelompok', 1000)->nullable();
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
        DB::table($temp)->insertUsing(['id', 'namastok', 'kelompok', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }
    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'kelompok') {
            return $query->orderBy('kelompok.kodekelompok', $this->params['sortOrder']);
        }
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'kelompok') {
                            $query = $query->where('kelompok.kodekelompok', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%' escape '|'");
                        } else {
                            // $query = $query->whereRaw($this->table . ".".  $filters['field'] ." LIKE '%".str_replace($filters['data'],'[','|[') ."%' escape '|'");
                            $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'kelompok') {
                                $query = $query->orWhere('kelompok.kodekelompok', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
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

    public function processStore(array $data): StokPusat
    {
        $stokPusat = new StokPusat();
        $stokPusat->namastok = $data['namastok'];
        $stokPusat->kelompok_id = $data['kelompok_id'];
        $stokPusat->modifiedby = auth('api')->user()->name;

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

        $detaillog = [];

        if ($data['stok_idmdn'] != null) {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'MDN')->first();
            $datadetails = (new StokPusatRincian())->processStore($stokPusat, [
                'namastok' => $data['namastokmdn'],
                'kelompok_id' => $data['kelompok_id'],
                'stok_id' => $data['stok_idmdn'],
                'cabang_id' => $getCabang->id,
                'gambar' => ($data['gambarmdn'] != null) ? json_encode([$data['gambarmdn']]) : '',
            ]);
            if ($data['gambarmdn'] != null) {
                $destinationPath = 'stokpusat/';
                $imageUrl = "https://tasmdn.kozow.com:8074/trucking-api/public/api/stok/" . $data['gambarmdn'] . "/medium";
                $destinationFileName = $data['gambarmdn'];
                $imageData = file_get_contents($imageUrl);
                Storage::put($destinationPath . $destinationFileName, $imageData);
            }
            $detaillog[] = $datadetails->toArray();
        }

        if ($data['stok_idjkt'] != null) {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'JKT')->first();

            $datadetails = (new StokPusatRincian())->processStore($stokPusat, [
                'namastok' => $data['namastokjkt'],
                'kelompok_id' => $data['kelompok_id'],
                'stok_id' => $data['stok_idjkt'],
                'cabang_id' => $getCabang->id,
                'gambar' => ($data['gambarjkt'] != null) ? json_encode([$data['gambarjkt']]) : '',
            ]);
            if ($data['gambarjkt'] != null) {
                $destinationPath = 'stokpusat/';
                $imageUrl = "http://tasjkt.kozow.com:8074/trucking-api/public/api/stok/" . $data['gambarjkt'] . "/medium";
                $destinationFileName = $data['gambarjkt'];
                $imageData = file_get_contents($imageUrl);
                Storage::put($destinationPath . $destinationFileName, $imageData);
            }
            $detaillog[] = $datadetails->toArray();
        }

        if ($data['stok_idjkttnl'] != null) {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'TNL')->first();

            $datadetails = (new StokPusatRincian())->processStore($stokPusat, [
                'namastok' => $data['namastokjkttnl'],
                'kelompok_id' => $data['kelompok_id'],
                'stok_id' => $data['stok_idjkttnl'],
                'cabang_id' => $getCabang->id,
                'gambar' => ($data['gambarjkttnl'] != null) ? json_encode([$data['gambarjkttnl']]) : '',
            ]);
            if ($data['gambarjkttnl'] != null) {
                $destinationPath = 'stokpusat/';
                $imageUrl = "http://tasjkt.kozow.com:8074/truckingtnl-api/public/api/stok/" . $data['gambarjkttnl'] . "/medium";
                $destinationFileName = $data['gambarjkttnl'];
                $imageData = file_get_contents($imageUrl);
                Storage::put($destinationPath . $destinationFileName, $imageData);
            }
            $detaillog[] = $datadetails->toArray();
        }


        if ($data['stok_idmks'] != null) {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'MKS')->first();

            $datadetails = (new StokPusatRincian())->processStore($stokPusat, [
                'namastok' => $data['namastokmks'],
                'kelompok_id' => $data['kelompok_id'],
                'stok_id' => $data['stok_idmks'],
                'cabang_id' => $getCabang->id,
                'gambar' => ($data['gambarmks'] != null) ? json_encode([$data['gambarmks']]) : '',
            ]);
            if ($data['gambarmks'] != null) {
                $destinationPath = 'stokpusat/';
                $imageUrl = "http://tasmks.kozow.com:8074/trucking-api/public/api/stok/" . $data['gambarmks'] . "/medium";
                $destinationFileName = $data['gambarmks'];
                $imageData = file_get_contents($imageUrl);
                Storage::put($destinationPath . $destinationFileName, $imageData);
            }
            $detaillog[] = $datadetails->toArray();
        }

        if ($data['stok_idsby'] != null) {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'SBY')->first();

            $datadetails = (new StokPusatRincian())->processStore($stokPusat, [
                'namastok' => $data['namastoksby'],
                'kelompok_id' => $data['kelompok_id'],
                'stok_id' => $data['stok_idsby'],
                'cabang_id' => $getCabang->id,
                'gambar' => ($data['gambarsby'] != null) ? json_encode([$data['gambarsby']]) : '',
            ]);
            if ($data['gambarsby'] != null) {
                $destinationPath = 'stokpusat/';
                $imageUrl = "http://tassby.kozow.com:8074/trucking-api/public/api/stok/" . $data['gambarsby'] . "/medium";
                $destinationFileName = $data['gambarsby'];
                $imageData = file_get_contents($imageUrl);
                Storage::put($destinationPath . $destinationFileName, $imageData);
            }
            $detaillog[] = $datadetails->toArray();
        }

        if ($data['stok_idbtg'] != null) {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'BTG')->first();

            $datadetails = (new StokPusatRincian())->processStore($stokPusat, [
                'namastok' => $data['namastokbtg'],
                'kelompok_id' => $data['kelompok_id'],
                'stok_id' => $data['stok_idbtg'],
                'cabang_id' => $getCabang->id,
                'gambar' => ($data['gambarbtg'] != null) ? json_encode([$data['gambarbtg']]) : '',
            ]);
            if ($data['gambarbtg'] != null) {
                $destinationPath = 'stokpusat/';
                $imageUrl = "http://tasbtg.kozow.com:8074/trucking-api/public/api/stok/" . $data['gambarbtg'] . "/medium";
                $destinationFileName = $data['gambarbtg'];
                $imageData = file_get_contents($imageUrl);
                Storage::put($destinationPath . $destinationFileName, $imageData);
            }
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

        return $stokPusat;
    }


    public function processUpdate(StokPusat $stokPusat, array $data): StokPusat
    {
        $stokPusat->namastok = $data['namastok'];
        $stokPusat->kelompok_id = $data['kelompok_id'];
        $stokPusat->modifiedby = auth('api')->user()->name;

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

        if ($data['stok_idmdn'] != null) {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'MDN')->first();

            $mdn = (new StokPusatRincian())->findMdn($stokPusat->id);
            if ($data['gambarmdn'] != null) {
                if ($mdn != null) {
                    $gbrMedan = json_decode($mdn->gambar)[0];
                    if ($data['gambarmdn'] != $gbrMedan) {
                        if ($gbrMedan != null) {
                            Storage::delete("stokpusat/$gbrMedan");
                        }
                    }
                }

                $destinationPath = 'stokpusat/';
                $imageUrl = "https://tasmdn.kozow.com:8074/trucking-api/public/api/stok/" . $data['gambarmdn'] . "/medium";
                $destinationFileName = $data['gambarmdn'];
                $imageData = file_get_contents($imageUrl);
                Storage::put($destinationPath . $destinationFileName, $imageData);
            } else {
                if ($mdn != null) {
                    $gbrMedan = json_decode($mdn->gambar)[0];
                    if ($gbrMedan != null) {
                        Storage::delete("stokpusat/$gbrMedan");
                    }
                }
            }

            StokPusatRincian::where('stokpusat_id', $stokPusat->id)->where('cabang_id', $getCabang->id)->delete();
            $datadetails = (new StokPusatRincian())->processStore($stokPusat, [
                'namastok' => $data['namastokmdn'],
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
                $gbrMedan = json_decode($mdn->gambar)[0];
                if ($gbrMedan != null) {
                    Storage::delete("stokpusat/$gbrMedan");
                }
                StokPusatRincian::where('stokpusat_id', $stokPusat->id)->where('cabang_id', $getCabang->id)->delete();
            }
        }

        if ($data['stok_idjkt'] != null) {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'JKT')->first();

            $jkt = (new StokPusatRincian())->findJkt($stokPusat->id);
            if ($data['gambarjkt'] != null) {
                if ($jkt != null) {
                    $gbrJkt = json_decode($jkt->gambar)[0];
                    if ($data['gambarjkt'] != $gbrJkt) {
                        if ($gbrJkt != null) {
                            Storage::delete("stokpusat/$gbrJkt");
                        }
                    }
                }

                $destinationPath = 'stokpusat/';
                $imageUrl = "http://tasjkt.kozow.com:8074/trucking-api/public/api/stok/" . $data['gambarjkt'] . "/medium";
                $destinationFileName = $data['gambarjkt'];
                $imageData = file_get_contents($imageUrl);
                Storage::put($destinationPath . $destinationFileName, $imageData);
            } else {
                if ($jkt != null) {
                    $gbrJkt = json_decode($jkt->gambar)[0];
                    if ($gbrJkt != null) {
                        Storage::delete("stokpusat/$gbrJkt");
                    }
                }
            }

            StokPusatRincian::where('stokpusat_id', $stokPusat->id)->where('cabang_id', $getCabang->id)->delete();
            $datadetails = (new StokPusatRincian())->processStore($stokPusat, [
                'namastok' => $data['namastokjkt'],
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
                $gbrJkt = json_decode($jkt->gambar)[0];
                if ($gbrJkt != null) {
                    Storage::delete("stokpusat/$gbrJkt");
                }
                StokPusatRincian::where('stokpusat_id', $stokPusat->id)->where('cabang_id', $getCabang->id)->delete();
            }
        }

        if ($data['stok_idjkttnl'] != null) {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'TNL')->first();

            $jkttnl = (new StokPusatRincian())->findJktTnl($stokPusat->id);
            if ($data['gambarjkttnl'] != null) {
                if ($jkttnl != null) {
                    $gbrJktTnl = json_decode($jkttnl->gambar)[0];
                    if ($data['gambarjkttnl'] != $gbrJktTnl) {
                        if ($gbrJktTnl != null) {
                            Storage::delete("stokpusat/$gbrJktTnl");
                        }
                    }
                }

                $destinationPath = 'stokpusat/';
                $imageUrl = "http://tasjkt.kozow.com:8074/truckingtnl-api/public/api/stok/" . $data['gambarjkttnl'] . "/medium";
                $destinationFileName = $data['gambarjkttnl'];
                $imageData = file_get_contents($imageUrl);
                Storage::put($destinationPath . $destinationFileName, $imageData);
            } else {
                if ($jkttnl != null) {
                    $gbrJktTnl = json_decode($jkttnl->gambar)[0];
                    if ($gbrJktTnl != null) {
                        Storage::delete("stokpusat/$gbrJktTnl");
                    }
                }
            }

            StokPusatRincian::where('stokpusat_id', $stokPusat->id)->where('cabang_id', $getCabang->id)->delete();
            $datadetails = (new StokPusatRincian())->processStore($stokPusat, [
                'namastok' => $data['namastokjkttnl'],
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
                $gbrJktTnl = json_decode($jkttnl->gambar)[0];
                if ($gbrJktTnl != null) {
                    Storage::delete("stokpusat/$gbrJktTnl");
                }
                StokPusatRincian::where('stokpusat_id', $stokPusat->id)->where('cabang_id', $getCabang->id)->delete();
            }
        }


        if ($data['stok_idmks'] != null) {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'MKS')->first();

            $mks = (new StokPusatRincian())->findMks($stokPusat->id);
            if ($data['gambarmks'] != null) {
                if ($mks != null) {
                    $gbrMks = json_decode($mks->gambar)[0];
                    if ($data['gambarmks'] != $gbrMks) {
                        if ($gbrMks != null) {
                            Storage::delete("stokpusat/$gbrMks");
                        }
                    }
                }

                $destinationPath = 'stokpusat/';
                $imageUrl = "http://tasmks.kozow.com:8074/trucking-api/public/api/stok/" . $data['gambarmks'] . "/medium";
                $destinationFileName = $data['gambarmks'];
                $imageData = file_get_contents($imageUrl);
                Storage::put($destinationPath . $destinationFileName, $imageData);
            } else {
                if ($mks != null) {
                    $gbrMks = json_decode($mks->gambar)[0];
                    if ($gbrMks != null) {
                        Storage::delete("stokpusat/$gbrMks");
                    }
                }
            }

            StokPusatRincian::where('stokpusat_id', $stokPusat->id)->where('cabang_id', $getCabang->id)->delete();
            $datadetails = (new StokPusatRincian())->processStore($stokPusat, [
                'namastok' => $data['namastokmks'],
                'kelompok_id' => $data['kelompok_id'],
                'stok_id' => $data['stok_idmks'],
                'cabang_id' => $getCabang->id,
                'gambar' => ($data['gambarmks'] != null) ? json_encode([$data['gambarmks']]) : '',
            ]);
            $detaillog[] = $datadetails->toArray();
        } else {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'MKS')->first();
            $mks = (new StokPusatRincian())->findMks($stokPusat->id);
            if ($mks != null) {
                $gbrMks = json_decode($mks->gambar)[0];
                if ($gbrMks != null) {
                    Storage::delete("stokpusat/$gbrMks");
                }
                StokPusatRincian::where('stokpusat_id', $stokPusat->id)->where('cabang_id', $getCabang->id)->delete();
            }
        }

        if ($data['stok_idsby'] != null) {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'SBY')->first();

            $sby = (new StokPusatRincian())->findSby($stokPusat->id);
            if ($data['gambarsby'] != null) {
                if ($sby != null) {
                    $gbrSby = json_decode($sby->gambar)[0];
                    if ($data['gambarsby'] != $gbrSby) {
                        if ($gbrSby != null) {
                            Storage::delete("stokpusat/$gbrSby");
                        }
                    }
                }
                $destinationPath = 'stokpusat/';
                $imageUrl = "http://tassby.kozow.com:8074/trucking-api/public/api/stok/" . $data['gambarsby'] . "/medium";
                $destinationFileName = $data['gambarsby'];
                $imageData = file_get_contents($imageUrl);
                Storage::put($destinationPath . $destinationFileName, $imageData);
            } else {
                if ($sby != null) {
                    $gbrSby = json_decode($sby->gambar)[0];
                    if ($gbrSby != null) {
                        Storage::delete("stokpusat/$gbrSby");
                    }
                }
            }

            StokPusatRincian::where('stokpusat_id', $stokPusat->id)->where('cabang_id', $getCabang->id)->delete();
            $datadetails = (new StokPusatRincian())->processStore($stokPusat, [
                'namastok' => $data['namastoksby'],
                'kelompok_id' => $data['kelompok_id'],
                'stok_id' => $data['stok_idsby'],
                'cabang_id' => $getCabang->id,
                'gambar' => ($data['gambarsby'] != null) ? json_encode([$data['gambarsby']]) : '',
            ]);
            $detaillog[] = $datadetails->toArray();
        } else {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'SBY')->first();
            $sby = (new StokPusatRincian())->findSby($stokPusat->id);
            if ($sby != null) {
                $gbrSby = json_decode($sby->gambar)[0];
                if ($gbrSby != null) {
                    Storage::delete("stokpusat/$gbrSby");
                }
                StokPusatRincian::where('stokpusat_id', $stokPusat->id)->where('cabang_id', $getCabang->id)->delete();
            }
        }

        if ($data['stok_idbtg'] != null) {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'BTG')->first();

            $btg = (new StokPusatRincian())->findBtg($stokPusat->id);
            if ($data['gambarbtg'] != null) {
                if ($btg != null) {
                    $gbrBtg = json_decode($btg->gambar)[0];
                    if ($data['gambarbtg'] != $gbrBtg) {
                        if ($gbrBtg != null) {
                            Storage::delete("stokpusat/$gbrBtg");
                        }
                    }
                }

                $destinationPath = 'stokpusat/';
                $imageUrl = "http://tasbtg.kozow.com:8074/trucking-api/public/api/stok/" . $data['gambarbtg'] . "/medium";
                $destinationFileName = $data['gambarbtg'];
                $imageData = file_get_contents($imageUrl);
                Storage::put($destinationPath . $destinationFileName, $imageData);
            } else {
                if ($btg != null) {
                    $gbrBtg = json_decode($btg->gambar)[0];
                    if ($gbrBtg != null) {
                        Storage::delete("stokpusat/$gbrBtg");
                    }
                }
            }

            StokPusatRincian::where('stokpusat_id', $stokPusat->id)->where('cabang_id', $getCabang->id)->delete();
            $datadetails = (new StokPusatRincian())->processStore($stokPusat, [
                'namastok' => $data['namastokbtg'],
                'kelompok_id' => $data['kelompok_id'],
                'stok_id' => $data['stok_idbtg'],
                'cabang_id' => $getCabang->id,
                'gambar' => ($data['gambarbtg'] != null) ? json_encode([$data['gambarbtg']]) : '',
            ]);
            $detaillog[] = $datadetails->toArray();
        } else {
            $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'BTG')->first();
            $btg = (new StokPusatRincian())->findBtg($stokPusat->id);
            if ($btg != null) {
                $gbrBtg = json_decode($btg->gambar)[0];
                if ($gbrBtg != null) {
                    Storage::delete("stokpusat/$gbrBtg");
                }
                StokPusatRincian::where('stokpusat_id', $stokPusat->id)->where('cabang_id', $getCabang->id)->delete();
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

        return $stokPusat;
    }

    public function processDestroy($id, $postingDari = ''): StokPusat
    {
        $stokPusatRincian = StokPusatRincian::lockForUpdate()->where('stokpusat_id', $id)->get();

        // MDN
        $mdn = (new StokPusatRincian())->findMdn($id);
        if ($mdn != null) {
            $gbrMedan = json_decode($mdn->gambar)[0];
            if ($gbrMedan != null) {
                Storage::delete("stokpusat/$gbrMedan");
            }
        }
        // JKT
        $jkt = (new StokPusatRincian())->findJkt($id);
        if ($jkt != null) {
            $gbrJkt = json_decode($jkt->gambar)[0];
            if ($gbrJkt != null) {
                Storage::delete("stokpusat/$gbrJkt");
            }
        }
        // TNL
        $jkttnl = (new StokPusatRincian())->findJktTnl($id);
        if ($jkttnl != null) {
            $gbrJktTnl = json_decode($jkttnl->gambar)[0];
            if ($gbrJktTnl != null) {
                Storage::delete("stokpusat/$gbrJktTnl");
            }
        }
        // SBY
        $sby = (new StokPusatRincian())->findSby($id);
        if ($sby != null) {
            $gbrSby = json_decode($sby->gambar)[0];
            if ($gbrSby != null) {
                Storage::delete("stokpusat/$gbrSby");
            }
        }
        // MKS
        $mks = (new StokPusatRincian())->findMks($id);
        if ($mks != null) {
            $gbrMks = json_decode($mks->gambar)[0];
            if ($gbrMks != null) {
                Storage::delete("stokpusat/$gbrMks");
            }
        }
        // BTG
        $btg = (new StokPusatRincian())->findBtg($id);
        if ($btg != null) {
            $gbrBtg = json_decode($btg->gambar)[0];
            if ($gbrBtg != null) {
                Storage::delete("stokpusat/$gbrBtg");
            }
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

        return $stokPusat;
    }
}
