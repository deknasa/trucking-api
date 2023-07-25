<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChargeGandengan extends MyModel
{
    use HasFactory;

    // protected $table = 'orderantrucking';
    protected $table;

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'tglbukti' => 'date:d-m-Y',
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];
    public function get()
    {
        $this->setRequestParameters();
        $orderaTrucking = new OrderanTrucking();




        $proses = request()->proses ?? 'reload';
        $rules = false; 
        $class = 'ChergeGandengan';
        $user = auth('api')->user()->name;
        if ($this->params['filters']) {
            $rules = true; 
        }
        
        if ((request()->proses == 'reload')) {
            $temtabel = 'temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;
            $querydata = DB::table('listtemporarytabel')
                        ->from(DB::raw("listtemporarytabel a with (readuncommitted)"))
                        ->select('id','class','namatabel',)
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

            Schema::create($temtabel, function ($table) {
                $table->id();
                $table->string('jobtrucking', 50)->nullable();
                $table->string('gandengan', 500)->nullable();
                $table->date('tglawal')->nullable();
                $table->date('tglkembali')->nullable();
                $table->integer('jumlahhari')->nullable();
                $table->string('jenisorder', 500)->nullable();
                $table->string('namaemkl', 500)->nullable();
                $table->string('ukurancontainer', 500)->nullable();
                $table->string('nojob', 500)->nullable();
                $table->string('nojob2', 500)->nullable();
                $table->string('nocont', 500)->nullable();
                $table->string('nocont2', 500)->nullable();
                $table->string('kodetrado', 500)->nullable();
                $table->string('supir', 500)->nullable();
                $table->string('namagudang', 500)->nullable();
                $table->string('noinvoice', 500)->nullable();
                $table->integer('trado_id')->nullable();
                $table->integer('gandengan_id')->nullable();
                $table->integer('agen_id')->nullable();
            });

            DB::table($temtabel)->insertUsing([
                'jobtrucking',
                'gandengan',
                'tglawal',
                'tglkembali',
                'jumlahhari',
                'jenisorder',
                'namaemkl',
                'ukurancontainer',
                'nojob',
                'nojob2',
                'nocont',
                'nocont2',
                'kodetrado',
                'supir',
                'namagudang',
                'noinvoice',
                'trado_id',
                'gandengan_id',
                'agen_id',
            ], $orderaTrucking->reminderchargegandengan());
            $this->table = $temtabel;
        }else{
            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel with (readuncommitted)")
            )
                ->select(
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            // dd($querydata);
            $this->table = $querydata->namatabel;
        }

        $query = DB::table(DB::raw($this->table))->from(DB::raw(DB::raw($this->table) . " a with (readuncommitted)"))
        ->select(
            'a.jobtrucking',
            'a.gandengan',
            'a.tglawal',
            'a.tglkembali',
            'a.jumlahhari',
            'a.jenisorder',
            'a.namaemkl',
            'a.ukurancontainer',
            'a.nojob',
            'a.nojob2',
            'a.nocont',
            'a.nocont2',
            'a.kodetrado as trado',
            'a.supir',
            'a.namagudang',
            'a.noinvoice',
        );
        // $reminderchargegandengan = $orderaTrucking->reminderchargegandengan();
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);


        $data = $query->get();

        return $data;
    }
    
    public function getExport($dari, $sampai)
    {
        $this->setRequestParameters();
        $orderaTrucking = new OrderanTrucking();


        $proses = request()->proses ?? 'reload';
        $rules = false; 
        $class = 'ChergeGandengan';
        $user = auth('api')->user()->name;
        
        
        $querydata = DB::table('listtemporarytabel')->from(
            DB::raw("listtemporarytabel with (readuncommitted)")
        )
            ->select(
                'namatabel',
            )
            ->where('class', '=', $class)
            ->where('modifiedby', '=', $user)
            ->first();

        // dd($querydata);
        $this->table = $querydata->namatabel;
        
        

        $query = DB::table(DB::raw($this->table))->from(DB::raw(DB::raw($this->table) . " a with (readuncommitted)"))
        ->select(
            'a.jobtrucking',
            'a.gandengan',
            'a.tglawal',
            'a.tglkembali',
            'a.jumlahhari',
            'a.jenisorder',
            'a.namaemkl',
            'a.ukurancontainer',
            'a.nojob',
            'a.nojob2',
            'a.nocont',
            'a.nocont2',
            'a.kodetrado as trado',
            'a.supir',
            'a.namagudang',
            'a.noinvoice',
            DB::raw("'" . $dari . "' as tgldari"),
                DB::raw("'" . $sampai . "' as tglsampai"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
        );
        
        // ->whereBetween('a.tglawal', [date('Y-m-d', strtotime($dari)), date('Y-m-d', strtotime($sampai))]);

        // $reminderchargegandengan = $orderaTrucking->reminderchargegandengan();
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);


        $data = $query->get();

        return $data;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'container_id') {
            return $query->orderBy('container.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'agen_id') {
            return $query->orderBy('agen.namaagen', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'jenisorder_id') {
            return $query->orderBy('jenisorder.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'pelanggan_id') {
            return $query->orderBy('pelanggan.namapelanggan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'tarif_id') {
            return $query->orderBy('tarif.tujuan', $this->params['sortOrder']);
        } else {
            return $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            
                                $query = $query->whereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statuslangsir') {
                                $query = $query->orWhere('parameter.text', '', "$filters[data]");
                            } elseif ($filters['field'] == 'statusperalihan') {
                                $query = $query->orWhere('param2.text', '', "$filters[data]");
                            } elseif ($filters['field'] == 'statusapprovalbukatrip') {
                                $query = $query->orWhere('statusapprovalbukatrip.text', '', "$filters[data]");
                            } elseif ($filters['field'] == 'agen_id') {
                                $query = $query->orWhere('agen.namaagen', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'pelanggan_id') {
                                $query = $query->orWhere('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'container_id') {
                                $query = $query->orWhere('container.keterangan', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'tarif_id') {
                                $query = $query->orWhere('tarif.tujuan', 'LIKE', "%$filters[data]%");
                            } elseif ($filters['field'] == 'jenisorder_id') {
                                $query = $query->orWhere('jenisorder.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->orWhereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
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
