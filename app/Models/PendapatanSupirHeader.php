<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\RunningNumberService;

class PendapatanSupirHeader extends MyModel
{
    use HasFactory;

    protected $table = 'pendapatansupirheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get()
    {
        $this->setRequestParameters();
        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';
        $query = DB::table($this->table)->from(DB::raw("pendapatansupirheader with (readuncommitted)"))
            ->select(
                'pendapatansupirheader.id',
                'pendapatansupirheader.nobukti',
                'pendapatansupirheader.tglbukti',
                'bank.namabank as bank_id',
                'pendapatansupirheader.tgldari',
                'pendapatansupirheader.tglsampai',
                'statusapproval.memo as statusapproval',
                'pendapatansupirheader.userapproval',
                DB::raw('(case when (year(pendapatansupirheader.tglapproval) <= 2000) then null else pendapatansupirheader.tglapproval end ) as tglapproval'),
                DB::raw('(case when (year(pendapatansupirheader.tglbukacetak) <= 2000) then null else pendapatansupirheader.tglbukacetak end ) as tglbukacetak'),
                'statuscetak.memo as statuscetak',
                'pendapatansupirheader.userbukacetak',
                'pendapatansupirheader.jumlahcetak',
                'pendapatansupirheader.periode',
                'pendapatansupirheader.modifiedby',
                'pendapatansupirheader.created_at',
                'pendapatansupirheader.updated_at'
            )
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pendapatansupirheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'pendapatansupirheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'pendapatansupirheader.statuscetak', 'statuscetak.id');
        if (request()->tgldari && request()->tglsampai) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(pendapatansupirheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(pendapatansupirheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("pendapatansupirheader.statuscetak", $statusCetak);
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function findUpdate($id)
    {
        $data = DB::table('pendapatansupirheader')->from(DB::raw("pendapatansupirheader with (readuncommitted)"))
            ->select(
                'pendapatansupirheader.id',
                'pendapatansupirheader.nobukti',
                'pendapatansupirheader.tglbukti',
                'pendapatansupirheader.bank_id',
                'bank.namabank as bank',
                'pendapatansupirheader.tgldari',
                'pendapatansupirheader.tglsampai',
                'pendapatansupirheader.periode',
                'pendapatansupirheader.statuscetak',
            )
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pendapatansupirheader.bank_id', 'bank.id')
            ->where('pendapatansupirheader.id', $id)
            ->first();

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
                 $this->table.nobukti,
                 $this->table.tglbukti,
                 'bank.namabank as bank_id', 
                 $this->table.tgldari,
                 $this->table.tglsampai,
                'parameter.text as statusapproval',
                 $this->table.userapproval,
                 $this->table.tglapproval,
                 'statuscetak.text as statuscetak',
                 $this->table.userbukacetak,
                 $this->table.tglbukacetak,
                 $this->table.jumlahcetak,
                 $this->table.periode,
                 $this->table.modifiedby,
                 $this->table.created_at,
                 $this->table.updated_at"
                )
            )
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pendapatansupirheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'pendapatansupirheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'pendapatansupirheader.statuscetak', 'statuscetak.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('bank_id', 1000)->nullable();
            $table->date('tgldari')->nullable();
            $table->date('tglsampai')->nullable();
            $table->string('statusapproval')->nullable();
            $table->string('userapproval')->nullable();
            $table->date('tglapproval')->nullable();
            $table->string('statuscetak', 1000)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->date('periode')->nullable();
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
        $models =  $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'bank_id', 'tgldari', 'tglsampai', 'statusapproval', 'userapproval', 'tglapproval', 'statuscetak', 'userbukacetak', 'tglbukacetak', 'jumlahcetak', 'periode', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
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
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'bank_id') {
                                $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'statusapproval') {
                                $query = $query->where('statusapproval.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statuscetak') {
                                $query = $query->where('statuscetak.text', '=', "$filters[data]");
                            } else {
                                // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'bank_id') {
                                    $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'statusapproval') {
                                    $query = $query->orWhere('statusapproval.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statuscetak') {
                                    $query = $query->orWhere('statuscetak.text', '=', "$filters[data]");
                                } else {
                                    // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                                }
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
        if (request()->cetak && request()->periode) {
            $query->where('pendapatansupirheader.statuscetak', '<>', request()->cetak)
                ->whereYear('pendapatansupirheader.tglbukti', '=', request()->year)
                ->whereMonth('pendapatansupirheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function getExport($id)
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
        ->select('text')
        ->where('grp', 'JUDULAN LAPORAN')
        ->where('subgrp', 'JUDULAN LAPORAN')
        ->first();

        $query = DB::table($this->table)->from(DB::raw("pendapatansupirheader with (readuncommitted)"))
            ->select(
                'pendapatansupirheader.id',
                'pendapatansupirheader.nobukti',
                'pendapatansupirheader.tglbukti',
                'bank.namabank as bank_id',
                'pendapatansupirheader.tgldari',
                'pendapatansupirheader.tglsampai',
                'pendapatansupirheader.periode',
                DB::raw("'Laporan Pendapatan Supir' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :".auth('api')->user()->name."' as usercetak")
            )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pendapatansupirheader.bank_id', 'bank.id');

        $data = $query->first();
        return $data;
    }


    public function processStore(array $data): PendapatanSupirHeader
    {
        /* Store header */
        $group = 'PENDAPATAN SUPIR BUKTI';
        $subGroup = 'PENDAPATAN SUPIR BUKTI';
        $format = DB::table('parameter')->where('grp', $group)->where('subgrp', $subGroup)->first();

        $statusApp = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
        
        $pendapatanSupirHeader = new PendapatanSupirHeader();

        $pendapatanSupirHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $pendapatanSupirHeader->bank_id  = $data['bank_id'];
        $pendapatanSupirHeader->tgldari  = date('Y-m-d', strtotime($data['tgldari']));
        $pendapatanSupirHeader->tglsampai  = date('Y-m-d', strtotime($data['tglsampai']));
        $pendapatanSupirHeader->statusapproval  = $statusApp->id;
        $pendapatanSupirHeader->userapproval  = '';
        $pendapatanSupirHeader->tglapproval  = '';
        $pendapatanSupirHeader->periode  = date('Y-m-d', strtotime($data['periode']));
        $pendapatanSupirHeader->statusformat = $format->id;
        $pendapatanSupirHeader->statuscetak = $statusCetak->id;
        $pendapatanSupirHeader->modifiedby = auth('api')->user()->name;
        $pendapatanSupirHeader->nobukti = (new RunningNumberService)->get($group, $subGroup, $pendapatanSupirHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
        
        if (!$pendapatanSupirHeader->save()) {
            throw new \Exception("Error storing pendapatan Supir header.");
        }


        for ($i = 0; $i < count($data['nominal']); $i++) {
            $pendapatanSupirDetail = (new PendapatanSupirDetail)->processStore($pendapatanSupirHeader,[
                'pendapatansupir_id' => $pendapatanSupirHeader->id,
                'nobukti' => $pendapatanSupirHeader->nobukti,
                'supir_id' => $data['supir_id'][$i],
                'nominal' => $data['nominal'][$i],
                'keterangan' => $data['keterangan_detail'][$i],
                'modifiedby' => $pendapatanSupirHeader->modifiedby,
            ]);
            $pendapatanSupirs[] = $pendapatanSupirHeader->toArray();
        }


        $pendapatanSupirLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($pendapatanSupirHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? 'ENTRY pendapatan Supir HEADER',
            'idtrans' => $pendapatanSupirHeader->id,
            'nobuktitrans' => $pendapatanSupirHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $pendapatanSupirHeader->toArray(),
            'modifiedby' => $pendapatanSupirHeader->modifiedby
        ]);
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($pendapatanSupirDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? 'ENTRY pendapatan Supir HEADER',
            'idtrans' => $pendapatanSupirHeader->id,
            'nobuktitrans' => $pendapatanSupirHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $pendapatanSupirs,
            'modifiedby' => $pendapatanSupirHeader->modifiedby
        ]);
       
        return $pendapatanSupirHeader;
    }


    public function processUpdate(PendapatanSupirHeader $pendapatanSupirHeader, array $data): PendapatanSupirHeader
    {
        $pendapatanSupirHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $pendapatanSupirHeader->bank_id = $data['bank_id'];
        $pendapatanSupirHeader->tgldari = date('Y-m-d', strtotime($data['tgldari']));
        $pendapatanSupirHeader->tglsampai = date('Y-m-d', strtotime($data['tglsampai']));
        $pendapatanSupirHeader->periode = date('Y-m-d', strtotime($data['periode']));

        if (!$pendapatanSupirHeader->save()) {
            throw new \Exception("Error storing pendapatan Supir header.");
        }

        PendapatanSupirDetail::where('pendapatansupir_id', $pendapatanSupirHeader->id)->lockForUpdate()->delete();

        for ($i = 0; $i < count($data['nominal']); $i++) {
            $pendapatanSupirDetail = (new PendapatanSupirDetail)->processStore($pendapatanSupirHeader,[
                'pendapatansupir_id' => $pendapatanSupirHeader->id,
                'nobukti' => $pendapatanSupirHeader->nobukti,
                'supir_id' => $data['supir_id'][$i],
                'nominal' => $data['nominal'][$i],
                'keterangan' => $data['keterangan_detail'][$i],
                'modifiedby' => $pendapatanSupirHeader->modifiedby,
            ]);
            $pendapatanSupirs[] = $pendapatanSupirHeader->toArray();
        }

        $pendapatanSupirLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($pendapatanSupirHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? 'EDIT pendapatan Supir HEADER',
            'idtrans' => $pendapatanSupirHeader->id,
            'nobuktitrans' => $pendapatanSupirHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $pendapatanSupirHeader->toArray(),
            'modifiedby' => $pendapatanSupirHeader->modifiedby
        ]);
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($pendapatanSupirDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? 'EDIT pendapatan Supir HEADER',
            'idtrans' => $pendapatanSupirHeader->id,
            'nobuktitrans' => $pendapatanSupirHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $pendapatanSupirs,
            'modifiedby' => $pendapatanSupirHeader->modifiedby
        ]);
       

        return $pendapatanSupirHeader;
    }

    public function processDestroy($id, $postingDari = ''): PendapatanSupirHeader
    {
        $pendapatanSupirHeader = PendapatanSupirHeader::findOrFail($id);
        $dataHeader =  $pendapatanSupirHeader->toArray();
        $pendapatanSupirDetail = PendapatanSupirDetail::where('pendapatansupir_id', '=', $pendapatanSupirHeader->id)->get();
        $dataDetail = $pendapatanSupirDetail->toArray();
        
        $pendapatanSupirDetail = PendapatanSupirDetail::where('pendapatansupir_id', $pendapatanSupirHeader->id)->lockForUpdate()->delete();
 
         $pendapatanSupirHeader = $pendapatanSupirHeader->lockAndDestroy($id);
         $hutangLogTrail = (new LogTrail())->processStore([
             'namatabel' => $pendapatanSupirHeader->getTable(),
             'postingdari' => strtoupper('DELETE PENDAPATAN SUPIR HEADAER'),
             'idtrans' => $pendapatanSupirHeader->id,
             'nobuktitrans' => $pendapatanSupirHeader->nobukti,
             'aksi' => 'DELETE',
             'datajson' =>$dataHeader,
             'modifiedby' => auth('api')->user()->name
         ]);
 
         (new LogTrail())->processStore([
             'namatabel' => 'PENDAPATANSUPIRDETAIL',
             'postingdari' => strtoupper('DELETE PENDAPATAN SUPIR DETAIL'),
             'idtrans' => $hutangLogTrail['id'],
             'nobuktitrans' => $pendapatanSupirHeader->nobukti,
             'aksi' => 'DELETE',
             'datajson' =>$dataDetail,
             'modifiedby' => auth('api')->user()->name
         ]);
 
         return $pendapatanSupirHeader;
        
    }
}
