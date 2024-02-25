<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SuratPengantarApprovalInputTrip extends MyModel
{
    use HasFactory;

    protected $table = 'suratpengantarapprovalinputtrip';

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

        $query = DB::table($this->table)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            "suratpengantarapprovalinputtrip.id",
            "suratpengantarapprovalinputtrip.tglbukti",
            "suratpengantarapprovalinputtrip.tglbatas",
            "suratpengantarapprovalinputtrip.jumlahtrip",
            'parameter.memo as statusapproval',
            "suratpengantarapprovalinputtrip.modifiedby",
            "suratpengantarapprovalinputtrip.created_at",
            "suratpengantarapprovalinputtrip.updated_at",
        )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'suratpengantarapprovalinputtrip.statusapproval', 'parameter.id');

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $data = $query->get();

        return $data;
    }

    public function cekvalidasiaksi($id, $Aksi)
    {
        if ($Aksi == 'DELETE') {

            $suratPengantar = DB::table('suratpengantar')
                ->from(
                    DB::raw("suratpengantar as a with (readuncommitted)")
                )
                ->select(
                    'a.approvalbukatanggal_id'
                )
                ->where('a.approvalbukatanggal_id', '=', $id)
                ->first();
            if (isset($suratPengantar)) {
                $data = [
                    'kondisi' => true,
                    'keterangan' => 'Surat Pengantar',
                    'kodeerror' => 'SATL'
                ];
                goto selesai;
            }
        }else{
            $getTglBukti = DB::table("suratpengantarapprovalinputtrip")->from(DB::raw("suratpengantarapprovalinputtrip as a with (readuncommitted)"))->where('id',$id)->first();
            $cek = DB::table("suratpengantarapprovalinputtrip")->from(DB::raw("suratpengantarapprovalinputtrip as a with (readuncommitted)"))
            ->where('tglbukti', $getTglBukti->tglbukti)
            ->where('id', '>', $id)
            ->first();

            if(isset($cek)) {
                $data = [
                    'kondisi' => true,
                    'keterangan' => date('d-m-Y', strtotime($getTglBukti->tglbukti)),
                    'kodeerror' => 'ABTT'
                ];
                goto selesai;
            }
            
            $tanggal = date('Y-m-d', strtotime('+1 days')). ' ' . '10:00:00';
            $cek = DB::table("suratpengantarapprovalinputtrip")->from(DB::raw("suratpengantarapprovalinputtrip as a with (readuncommitted)"))
            ->where('tglbatas','<', $tanggal)
            ->where('id', $id)
            ->first();

            if(isset($cek)) {
                $data = [
                    'kondisi' => true,
                    'keterangan' => date('d-m-Y H:i:s', strtotime($getTglBukti->tglbatas)),
                    'kodeerror' => 'TBABT'
                ];
                goto selesai;
            }
        }
        $data = [
            'kondisi' => false,
            'keterangan' => '',
        ];
        selesai:
        return $data;
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('statusapproval')->nullable();
        });

        $statusapproval = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS APPROVAL')
            ->where('subgrp', '=', 'STATUS APPROVAL')
            ->where('text', '=', 'APPROVAL')
            ->first();

        DB::table($tempdefault)->insert(["statusapproval" => $statusapproval->id]);

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'statusapproval'
            );

        $data = $query->first();
        return $data;
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'tglbukti') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('parameter.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%' escape '|'");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'tglbukti') {
                            $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'statusapproval') {
                            $query = $query->orWhere('parameter.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
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

    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function isTanggalAvaillable()
    {

        $tutupbuku = DB::table('parameter')->from(
            DB::raw("parameter as a with (readuncommitted)")
        )
            ->where('a.grp', '=', 'TUTUP BUKU')
            ->where('a.subgrp', '=', 'TUTUP BUKU')
            ->first();
        $approval = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where("grp", 'STATUS APPROVAL')->where("text", "APPROVAL")->first();
        // $bukaAbsensi = DB::table('suratpengantar')
        // ->select('suratpengantar.tglbukti', DB::raw('COUNT(suratpengantar.tglbukti) as data_tanggal'), 'subquery.jumlahtrip')
        // ->join(DB::raw('(SELECT tglbukti, SUM(jumlahtrip) as jumlahtrip FROM suratpengantarapprovalinputtrip GROUP BY tglbukti) AS subquery'), 'suratpengantar.tglbukti', '=', 'subquery.tglbukti')
        // ->groupBy('suratpengantar.tglbukti', 'subquery.jumlahtrip')
        // ->where('suratpengantar.tglbukti', '>', '2022-12-25')

        // ->havingRaw('COUNT(suratpengantar.tglbukti) < subquery.jumlahtrip')
        // ->get();


        // $bukaAbsensi = DB::table('suratpengantar')
        //     ->join(DB::raw('(SELECT tglbukti, SUM(jumlahtrip) as jumlahtrip, statusapproval FROM suratpengantarapprovalinputtrip GROUP BY tglbukti,statusapproval) as subquery'), function ($join) {
        //         $join->on('suratpengantar.tglbukti', '=', 'subquery.tglbukti');
        //     })
        //     ->select('suratpengantar.tglbukti', DB::raw('COUNT(suratpengantar.tglbukti) as data_tanggal'), 'subquery.jumlahtrip')
        //     ->where('suratpengantar.tglbukti', '>', $tutupbuku->text)
        //     ->where('subquery.statusapproval', $approval->id)
        //     ->groupBy('suratpengantar.tglbukti', 'subquery.jumlahtrip')
        //     ->havingRaw('COUNT(suratpengantar.tglbukti) < subquery.jumlahtrip')

        //     ->get();

        $bukaAbsensi = DB::table("suratpengantarapprovalinputtrip")->from(DB::raw("suratpengantarapprovalinputtrip with (readuncommitted)"))
            ->select(DB::raw("suratpengantarapprovalinputtrip.tglbukti, count(suratpengantar.approvalbukatanggal_id) as jumlah"))
            ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), 'suratpengantarapprovalinputtrip.id', "suratpengantar.approvalbukatanggal_id")
            ->where('suratpengantarapprovalinputtrip.tglbukti', '>', $tutupbuku->text)
            ->where('suratpengantarapprovalinputtrip.statusapproval', $approval->id)
            ->groupBy('suratpengantarapprovalinputtrip.tglbukti')
            ->get();

        return $bukaAbsensi;
    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            "$this->table.id",
            "$this->table.tglbukti",
            "$this->table.jumlahtrip",
            "parameter.text as statusapproval",
            "$this->table.modifiedby",
            "$this->table.created_at",
            "$this->table.updated_at",
        )->leftJoin(DB::raw("parameter with (readuncommitted)"), 'suratpengantarapprovalinputtrip.statusapproval', 'parameter.id');
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->date('tglbukti')->nullable();
            $table->integer('jumlahtrip')->nullable();
            $table->string('statusapproval', 500)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $query = $this->sort($query);
        $models = $this->filter($query);

        DB::table($temp)->insertUsing([
            'id',
            'tglbukti',
            'jumlahtrip',
            'statusapproval',
            'modifiedby',
            'created_at',
            'updated_at'
        ], $models);

        return  $temp;
    }


    public function processStore(array $data): SuratPengantarApprovalInputTrip
    {
        $approvalBukaTanggal = new SuratPengantarApprovalInputTrip();

        $tanggal = date('Y-m-d', strtotime('+1 days'));

        $approvalBukaTanggal->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $approvalBukaTanggal->jumlahtrip = $data['jumlahtrip'];
        $approvalBukaTanggal->statusapproval = $data['statusapproval'];
        $approvalBukaTanggal->tglbatas = date('Y-m-d', strtotime($tanggal)) . ' ' . '10:00:00';
        $approvalBukaTanggal->modifiedby = auth('api')->user()->user;
        $approvalBukaTanggal->info = html_entity_decode(request()->info);

        if (!$approvalBukaTanggal->save()) {
            throw new \Exception('Error storing surat pengantar approval input trip.');
        }

        (new LogTrail())->processStore([
            'namatabel' => $approvalBukaTanggal->getTable(),
            'postingdari' => 'ENTRY SURAT PENGANTAR APPROVAL INPUT TRIP',
            'idtrans' => $approvalBukaTanggal->id,
            'nobuktitrans' => $approvalBukaTanggal->id,
            'aksi' => 'ENTRY',
            'datajson' => $approvalBukaTanggal->toArray(),
        ]);

        return $approvalBukaTanggal;
    }

    public function processUpdate(SuratPengantarApprovalInputTrip $approvalBukaTanggal, array $data): SuratPengantarApprovalInputTrip
    {
        $approvalBukaTanggal->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $approvalBukaTanggal->jumlahtrip = $data['jumlahtrip'];
        $approvalBukaTanggal->statusapproval = $data['statusapproval'];
        $approvalBukaTanggal->modifiedby = auth('api')->user()->user;
        $approvalBukaTanggal->info = html_entity_decode(request()->info);

        if (!$approvalBukaTanggal->save()) {
            throw new \Exception('Error updating surat pengantar approval input trip.');
        }

        (new LogTrail())->processStore([
            'namatabel' => $approvalBukaTanggal->getTable(),
            'postingdari' => 'EDIT SURAT PENGANTAR APPROVAL INPUT TRIP',
            'idtrans' => $approvalBukaTanggal->id,
            'nobuktitrans' => $approvalBukaTanggal->id,
            'aksi' => 'EDIT',
            'datajson' => $approvalBukaTanggal->toArray(),
        ]);

        return $approvalBukaTanggal;
    }

    public function processDestroy($id): SuratPengantarApprovalInputTrip
    {
        $approvalBukaTanggal = new SuratPengantarApprovalInputTrip();
        $approvalBukaTanggal = $approvalBukaTanggal->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($approvalBukaTanggal->getTable()),
            'postingdari' => 'DELETE SURAT PENGANTAR APPROVAL INPUT TRIP',
            'idtrans' => $approvalBukaTanggal->id,
            'nobuktitrans' => $approvalBukaTanggal->id,
            'aksi' => 'DELETE',
            'datajson' => $approvalBukaTanggal->toArray(),
        ]);

        return $approvalBukaTanggal;
    }

    public function storeTglValidation($tanggal)
    {
        $query = DB::table("suratpengantarapprovalinputtrip")->from(DB::raw("suratpengantarapprovalinputtrip with (readuncommitted)"))
            ->where('tglbukti', date('Y-m-d', strtotime($tanggal)))
            ->where('tglbatas', '<=', $tanggal = date('Y-m-d', strtotime('+1 days')) . ' ' . '10:00:00')
            ->first();

        return $query;
    }
    public function updateTglValidation($tanggal, $id)
    {
        $query = DB::table("suratpengantarapprovalinputtrip")->from(DB::raw("suratpengantarapprovalinputtrip with (readuncommitted)"))
            ->where('tglbukti', date('Y-m-d', strtotime($tanggal)))
            ->where('id', '<>', $id)
            ->first();

        return $query;
    }

    public function validationJumlahTrip($id)
    {
        $query = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->select(DB::raw("COUNT(approvalbukatanggal_id) as jumlah"))
            ->where('approvalbukatanggal_id', $id)
            ->first();
        return $query;
    }

    public function updateApproval()
    {
        $now = date('Y-m-d');

        $nonApproval = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where("grp", 'STATUS APPROVAL')->where("text", "NON APPROVAL")->first();
        $query = DB::table("suratpengantarapprovalinputtrip")->whereRaw("CAST(created_at AS DATE) = '$now'")->get();

        foreach ($query as $value) {
            $getSP = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
                ->select(DB::raw("COUNT(approvalbukatanggal_id) as jumlah"))
                ->where('approvalbukatanggal_id', $value->id)
                ->first();

            $result = DB::table("suratpengantarapprovalinputtrip")->where('tglbukti', $value->tglbukti)->update([
                'jumlahtrip' => $getSP->jumlah,
                'statusapproval' => $nonApproval->id,
            ]);
        }

        return $result;
    }
}
