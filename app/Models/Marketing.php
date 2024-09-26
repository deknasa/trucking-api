<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Marketing extends MyModel
{
    use HasFactory;
    
    protected $table = 'marketing';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get()
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $aktif = request()->aktif ?? '';

        $querymarketing = db::table("marketingdetail")->from(db::raw("marketingdetail a with (readuncommitted)"))
            ->select(
                'a.marketing_id',
                'b.user as name'
                )
                ->join(db::raw("[user] b with (readuncommitted)"),'a.user_id','b.id')
                ->orderBy('a.marketing_id','asc');

        $tempmarketingdetail = '##tempmarketingdetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempmarketingdetail, function ($table) {
            $table->id();
            $table->unsignedBigInteger('marketing_id')->nullable();
            $table->longText('name')->nullable();
        });

        DB::table($tempmarketingdetail)->insertUsing([
            'marketing_id',
            'name',
        ],  $querymarketing);

        $tempmarketingdetailrekap = '##tempmarketingdetailrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempmarketingdetailrekap, function ($table) {
            $table->id();
            $table->unsignedBigInteger('marketing_id')->nullable();
            $table->longText('name')->nullable();
        });

        $querylist=db::table($tempmarketingdetail)->from(db::raw($tempmarketingdetail . " b"))
        ->select(
            db::raw("
             distinct b.marketing_id,Stuff((SELECT DISTINCT ', ' + a.name
              FROM ".$tempmarketingdetail ." a
              WHERE  a.marketing_id=B.marketing_id
              FOR XML PATH('')), 1, 2, '') AS name
            ")
        );

        DB::table($tempmarketingdetailrekap)->insertUsing([
            'marketing_id',
            'name',
        ],  $querylist);



        $query = DB::table($this->table)->from(DB::raw("marketing with (readuncommitted)"))
            ->select(
                'marketing.id',
                'marketing.kodemarketing',
                'marketing.keterangan',
                'user.name as user',
                'parameter.memo as statusaktif',
                'marketing.modifiedby',
                'marketing.created_at',
                'marketing.updated_at',
                DB::raw("'Laporan Marketing' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak :'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'marketing.statusaktif', '=', 'parameter.id')
            // ->leftJoin(DB::raw("[user] with (readuncommitted)"), 'marketing.user_id', '=', db::raw("[user].id"));
            ->leftJoin(DB::raw($tempmarketingdetailrekap . " as [user]"), 'marketing.id', db::raw("[user].marketing_id"));



        $this->filter($query);
        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('marketing.statusaktif', '=', $statusaktif->id);
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
    { //sesuaikan dengan createtemp

        return $query->select(
            'marketing.id',
            'marketing.kodemarketing',
            'marketing.keterangan',
            'marketing.statusaktif',
            'statusaktif.memo as statusaktif_memo',
            'statusaktif.text as statusaktifnama',
            'marketing.modifiedby',
            'marketing.created_at',
            'marketing.updated_at'

        )
            ->leftJoin(DB::raw("parameter as statusaktif with (readuncommitted)"), 'marketing.statusaktif', 'statusaktif.id');
    }

    public function findAll($id) 
    {
        $query = DB::table($this->table)->from(
            DB::raw("marketing with (readuncommitted)")
        );

        $query = $this->selectColumns($query);
        $query->where('marketing.id', $id);

        $data = $query->first();

        return $data;
    }

    
    public function createTemp(string $modelTable)
    { //sesuaikan dengan column index
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('kodemarketing',50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('statusaktif', 1000)->nullable();
            $table->string('statusaktif_memo', 1000)->nullable();
            $table->string('statusaktifnama', 1000)->nullable();
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
        
        DB::table($temp)->insertUsing(['id','kodemarketing','keterangan','statusaktif','statusaktif_memo','statusaktifnama','modifiedby','created_at','updated_at'], $models);


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
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statusaktif_memo') {
                                $query = $query->where('statusaktif.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->where('marketing.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw('marketing' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'statusaktif_memo') {
                                    $query = $query->OrwhereRaw("statusaktif.text LIKE '%". escapeLike($filters['data']) ."%' escape '|'");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else {
                                    // $query = $query->orWhere('marketing.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw('marketing' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusaktif')->nullable();
            $table->string('statusaktifnama')->nullable();
        });

        $statusaktif = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'text',
                'id'
            )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('subgrp', '=', 'STATUS AKTIF')
            ->where('default', '=', 'YA')
            ->first();
        DB::table($tempdefault)->insert(["statusaktif" => $statusaktif->id,"statusaktifnama" => $statusaktif->text]);

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusaktif',
                'statusaktifnama'
            );

        $data = $query->first();
        // dd($data);
        return $data;
    }

    public function processStore(array $data, Marketing $marketing): Marketing
    {
       
        $marketing->kodemarketing = $data['kodemarketing'];
        $marketing->keterangan = $data['keterangan'];
        $marketing->statusaktif = $data['statusaktif'];
        $marketing->modifiedby = auth('api')->user()->name;
        $marketing->info = html_entity_decode(request()->info);
        $data['sortname'] = $data['sortname'] ?? 'id';
        $data['sortorder'] = $data['sortorder'] ?? 'asc';

        if (!$marketing->save()) {
            throw new \Exception('Error storing marketing.');
        }


        $marketingLogTrail =  (new LogTrail())->processStore([
            'namatabel' => strtoupper($marketing->getTable()),
            'postingdari' => 'ENTRY marketing',
            'idtrans' => $marketing->id,
            'nobuktitrans' => $marketing->id,
            'aksi' => 'ENTRY',
            'datajson' => $marketing->toArray(),
            'modifiedby' => $marketing->modifiedby
        ]);

        if (is_iterable($data['users'])) {
            $marketingDetails = [];
            for ($i = 0; $i < count($data['users']); $i++) {
                $datadetail = [
                    'user_id' => $data['users'][$i],
                    'marketing_id' => $marketing->id,
                    'tas_id' => $data['detail_tas_id'][$i]??0,
                ];
                $marketingDetail = new MarketingDetail();
                
                $marketingDetail->processStore($datadetail, $marketingDetail);
                
                $marketing->detailTasId[] = $marketingDetail->id;

                $marketingDetails[] = $marketingDetail->toArray();
            }
            
            $logtrail = new LogTrail();
            $logtrail->processStore([
                'namatabel' => strtoupper($marketingDetail->getTable()),
                'postingdari' => 'ENTRY marketing DETAIL',
                'idtrans' =>  $marketingLogTrail->id,
                'nobuktitrans' => $marketing->id,
                'aksi' => 'ENTRY',
                'datajson' => $marketingDetails,
                'modifiedby' => auth('api')->user()->user,
            ]);
        }


        return $marketing;
    }

    public function processUpdate(Marketing $marketing, array $data) {
        $marketing->kodemarketing = $data['kodemarketing'];
        $marketing->keterangan = $data['keterangan'];
        $marketing->statusaktif = $data['statusaktif'];
        $marketing->modifiedby = auth('api')->user()->name;
        $marketing->info = html_entity_decode(request()->info);
        if (!$marketing->save()) {
            throw new \Exception('Error updating marketing.');
        }

        $marketingLogTrail= (new LogTrail())->processStore([
            'namatabel' => strtoupper($marketing->getTable()),
            'postingdari' => 'EDIT marketing',
            'idtrans' => $marketing->id,
            'nobuktitrans' => $marketing->id,
            'aksi' => 'EDIT',
            'datajson' => $marketing->toArray(),
            'modifiedby' => $marketing->modifiedby
        ]);

        if (is_iterable($data['users'])) {
            $marketingDetail = new MarketingDetail();
            $marketingDetail->where('marketing_id', $marketing->id)->delete();
            $marketingDetails = [];
            for ($i = 0; $i < count($data['users']); $i++) {
                $datadetail = [
                    'user_id' => $data['users'][$i],
                    'marketing_id' => $marketing->id,
                ];
                $marketingDetail = new MarketingDetail();
                $marketingDetail->processStore($datadetail, $marketingDetail);
                // $marketing->detailTasId[] = $marketingDetail->id;

                $marketingDetails[] = $marketingDetail->toArray();
            }
            (new LogTrail())->processStore([
                'namatabel' => strtoupper($marketingDetail->getTable()),
                'postingdari' => 'EDIT marketing DETAIL',
                'idtrans' =>  $marketingLogTrail->id,
                'nobuktitrans' => $marketing->id,
                'aksi' => 'EDIT',
                'datajson' => $marketingDetails,
                'modifiedby' => auth('api')->user()->user,
            ]);
        } else {
            $checkDetail = DB::table('marketingdetail')->from(DB::raw("marketingdetail with (readuncommitted)"));
            $checkDetailExist = $checkDetail->where('marketing_id', $marketing->id)->first();
            if ($checkDetailExist != '') {
                $marketingDetail = DB::table('marketingdetail')->from(DB::raw("marketingdetail with (readuncommitted)"));
                $marketingDetail->where('marketing_id', $marketing->id)->get();
                $marketingDetail = new marketingDetail();
                $marketingDetail->where('marketing_id', $marketing->id)->delete();

                (new LogTrail())->processStore([
                    'namatabel' => strtoupper('marketingdetail'),
                    'postingdari' => 'EDIT marketing DELETE DETAIL',
                    'idtrans' =>  $marketingLogTrail->id,
                    'nobuktitrans' => $marketing->id,
                    'aksi' => 'EDIT',
                    'datajson' => $marketingDetail->toArray(),
                    'modifiedby' => auth('api')->user()->user,
                ]);
            }
        }

        return $marketing;
    }

    public function processDestroy(Marketing $marketing): Marketing
    {
        // $marketing = new JenisOrder();
        $marketing = $marketing->lockAndDestroy($marketing->id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($marketing->getTable()),
            'postingdari' => 'DELETE marketing',
            'idtrans' => $marketing->id,
            'nobuktitrans' => $marketing->id,
            'aksi' => 'DELETE',
            'datajson' => $marketing->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $marketing;
    }

    public function processApprovalnonaktif(array $data)
    {
        $statusnonaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'NON AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $merk = Marketing::find($data['Id'][$i]);

            $merk->statusaktif = $statusnonaktif->id;
            $merk->modifiedby = auth('api')->user()->name;
            $merk->info = html_entity_decode(request()->info);
            $aksi = $statusnonaktif->text;

            if ($merk->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($merk->getTable()),
                    'postingdari' => 'APPROVAL NON AKTIF MERK',
                    'idtrans' => $merk->id,
                    'nobuktitrans' => $merk->id,
                    'aksi' => $aksi,
                    'datajson' => $merk->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $merk;
    }

    public function processApprovalaktif(array $data)
    {
        $statusaktif = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS AKTIF')->where('text', '=', 'AKTIF')->first();
        for ($i = 0; $i < count($data['Id']); $i++) {
            $merk = Marketing::find($data['Id'][$i]);

            $merk->statusaktif = $statusaktif->id;
            $merk->modifiedby = auth('api')->user()->name;
            $merk->info = html_entity_decode(request()->info);
            $aksi = $statusaktif->text;

            if ($merk->save()) {
                (new LogTrail())->processStore([
                    'namatabel' => strtoupper($merk->getTable()),
                    'postingdari' => 'APPROVAL AKTIF MERK',
                    'idtrans' => $merk->id,
                    'nobuktitrans' => $merk->id,
                    'aksi' => $aksi,
                    'datajson' => $merk->toArray(),
                    'modifiedby' => auth('api')->user()->user
                ]);
            }
        }
        return $merk;
    }
}
