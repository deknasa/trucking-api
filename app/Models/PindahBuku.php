<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PindahBuku extends MyModel
{
    use HasFactory;
    protected $table = 'pindahbuku';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('bank', 255)->nullable();
            $table->unsignedBigInteger('alatbayar_id')->nullable();
            $table->string('alatbayar', 255)->nullable();
        });
        $bank = DB::table('bank')->from(
            DB::raw('bank with (readuncommitted)')
        )
            ->select(
                'id as bank_id',
                'namabank as bank',
                'tipe'

            )
            ->where('tipe', '=', 'KAS')
            ->first();

        $statusdefault = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS DEFAULT')
            ->where('subgrp', '=', 'STATUS DEFAULT')
            ->where('text', '=', 'DEFAULT')
            ->first();

        $alatbayardefault = $statusdefault->id ?? 0;

        $alatbayar = DB::table('alatbayar')->from(
            DB::raw('alatbayar with (readuncommitted)')
        )
            ->select(
                'id as alatbayar_id',
                'namaalatbayar as alatbayar',

            )
            ->where('statusdefault', '=', $alatbayardefault)
            ->where('tipe', '=', $bank->tipe)
            ->first();


        DB::table($tempdefault)->insert(
            ["bank_id" => $bank->bank_id, "bank" => $bank->bank, "alatbayar_id" => $alatbayar->alatbayar_id, "alatbayar" => $alatbayar->alatbayar]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'bank_id as bankdari_id',
                'bank as bankdari',
                'alatbayar_id',
                'alatbayar',
            );
        $data = $query->first();

        return $data;
    }

    public function get()
    {
        $this->setRequestParameters();

        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';
        $query = DB::table($this->table)->from(
            DB::raw("pindahbuku with (readuncommitted)")
        )
            ->select(
                'pindahbuku.id',
                'pindahbuku.nobukti',
                'pindahbuku.tglbukti',
                'bankdari.namabank as bankdari',
                'bankke.namabank as bankke',
                'coadebet.keterangancoa as coadebet',
                'coakredit.keterangancoa as coakredit',
                'alatbayar.namaalatbayar as alatbayar',
                'pindahbuku.nowarkat',
                'pindahbuku.tgljatuhtempo',
                'pindahbuku.nominal',
                'pindahbuku.keterangan',
                DB::raw('(case when (year(pindahbuku.tglapproval) <= 2000) then null else pindahbuku.tglapproval end ) as tglapproval'),
                'statusapproval.memo as statusapproval',
                'pindahbuku.userapproval',
                DB::raw('(case when (year(pindahbuku.tglbukacetak) <= 2000) then null else pindahbuku.tglbukacetak end ) as tglbukacetak'),
                'statuscetak.memo as statuscetak',
                'pindahbuku.userbukacetak',
                'pindahbuku.jumlahcetak',
                'pindahbuku.modifiedby',
                'pindahbuku.created_at',
                'pindahbuku.updated_at'
            )
            ->leftJoin(DB::raw("bank as bankdari with (readuncommitted)"), 'pindahbuku.bankdari_id', 'bankdari.id')
            ->leftJoin(DB::raw("bank as bankke with (readuncommitted)"), 'pindahbuku.bankke_id', 'bankke.id')
            ->leftJoin(DB::raw("akunpusat as coadebet with (readuncommitted)"), 'pindahbuku.coadebet', 'coadebet.coa')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'pindahbuku.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'pindahbuku.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("akunpusat as coakredit with (readuncommitted)"), 'pindahbuku.coakredit', 'coakredit.coa')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pindahbuku.alatbayar_id', 'alatbayar.id');
        if (request()->tgldari && request()->tglsampai) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(pindahbuku.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(pindahbuku.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("pindahbuku.statuscetak", $statusCetak);
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }


    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
                "$this->table.id,
                 $this->table.nobukti,
                 $this->table.tglbukti,
                 'bankdari.namabank as bankdari',
                'bankke.namabank as bankke',
                'coadebet.keterangancoa as coadebet',
                'coakredit.keterangancoa as coakredit',
                'alatbayar.namaalatbayar as alatbayar',
                 $this->table.nowarkat,
                 $this->table.tgljatuhtempo,
                 $this->table.nominal,
                 $this->table.keterangan,
                 'statuscetak.text as statuscetak',
                 $this->table.userbukacetak,
                 $this->table.tglbukacetak,
                 'statusapproval.text as statusapproval',
                 $this->table.userapproval,
                 $this->table.tglapproval,
                 $this->table.modifiedby,
                 $this->table.created_at,
                 $this->table.updated_at"
            )
        )
            ->leftJoin(DB::raw("bank as bankdari with (readuncommitted)"), 'pindahbuku.bankdari_id', 'bankdari.id')
            ->leftJoin(DB::raw("bank as bankke with (readuncommitted)"), 'pindahbuku.bankke_id', 'bankke.id')
            ->leftJoin(DB::raw("akunpusat as coadebet with (readuncommitted)"), 'pindahbuku.coadebet', 'coadebet.coa')
            ->leftJoin(DB::raw("akunpusat as coakredit with (readuncommitted)"), 'pindahbuku.coakredit', 'coakredit.coa')
            ->leftJoin('parameter as statusapproval', 'pindahbuku.statusapproval', 'statusapproval.id')
            ->leftJoin('parameter as statuscetak', 'pindahbuku.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pindahbuku.alatbayar_id', 'alatbayar.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('bankdari', 1000)->nullable();
            $table->string('bankke', 1000)->nullable();
            $table->string('coadebet', 1000)->nullable();
            $table->string('coakredit', 1000)->nullable();
            $table->string('alatbayar', 1000)->nullable();
            $table->string('nowarkat', 1000)->nullable();
            $table->date('tgljatuhtempo')->nullable();
            $table->float('nominal')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('statuscetak', 1000)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->string('statusapproval', 1000)->nullable();
            $table->string('userapproval', 50)->nullable();
            $table->date('tglapproval')->nullable();
            $table->string('modifiedby')->default();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });
        if ((date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tgldariheader))) || (date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tglsampaiheader)))) {
            request()->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            request()->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
        }
        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        $models = $query
            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'bankdari', 'bankke', 'coadebet', 'coakredit', 'alatbayar', 'nowarkat', 'tgljatuhtempo', 'nominal', 'keterangan', 'statuscetak', 'userbukacetak', 'tglbukacetak', 'statusapproval', 'userapproval', 'tglapproval', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }

    public function findAll($id)
    {
        $query = DB::table($this->table)->from(DB::raw("pindahbuku with (readuncommitted)"))
            ->select(
                'pindahbuku.id',
                'pindahbuku.nobukti',
                'pindahbuku.tglbukti',
                'pindahbuku.bankdari_id',
                'bankdari.namabank as bankdari',
                'pindahbuku.bankke_id',
                'bankke.namabank as bankke',
                'pindahbuku.alatbayar_id',
                'alatbayar.namaalatbayar as alatbayar',
                'pindahbuku.nowarkat',
                'pindahbuku.tgljatuhtempo',
                'pindahbuku.nominal',
                'pindahbuku.keterangan'
            )
            ->leftJoin(DB::raw("bank as bankdari with (readuncommitted)"), 'pindahbuku.bankdari_id', 'bankdari.id')
            ->leftJoin(DB::raw("bank as bankke with (readuncommitted)"), 'pindahbuku.bankke_id', 'bankke.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pindahbuku.alatbayar_id', 'alatbayar.id')
            ->where('pindahbuku.id', $id);

        return $query->first();
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'bankdari') {
            return $query->orderBy('bankdari.namabank', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'bankke') {
            return $query->orderBy('bankke.namabank', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'coadebet') {
            return $query->orderBy('coadebet.keterangancoa', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'coakredit') {
            return $query->orderBy('coakredit.keterangancoa', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'alatbayar') {
            return $query->orderBy('alatbayar.namaalatbayar', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statuscetak') {
                                $query = $query->where('statuscetak.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'statusapproval') {
                                $query = $query->where('statusapproval.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'bankdari') {
                                $query = $query->where('bankdari.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'bankke') {
                                $query = $query->where('bankke.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coadebet') {
                                $query = $query->where('coadebet.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coakredit') {
                                $query = $query->where('coakredit.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'alatbayar') {
                                $query = $query->where('alatbayar.namaalatbayar', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->whereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgljatuhtempo') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
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
                                if ($filters['field'] == 'statuscetak') {
                                    $query = $query->orWhere('statuscetak.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'statusapproval') {
                                    $query = $query->orWhere('statusapproval.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'bankdari') {
                                    $query = $query->orWhere('bankdari.namabank', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'bankke') {
                                    $query = $query->orWhere('bankke.namabank', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'coadebet') {
                                    $query = $query->orWhere('coadebet.keterangancoa', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'coakredit') {
                                    $query = $query->orWhere('coakredit.keterangancoa', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'alatbayar') {
                                    $query = $query->orWhere('alatbayar.namaalatbayar', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'nominal') {
                                    $query = $query->orWhereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgljatuhtempo') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
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
        }

        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function processStore(array $data): PindahBuku
    {

        $group = 'PINDAH BUKU';
        $subgroup = 'NOMOR PINDAH BUKU';

        $format = DB::table('parameter')
            ->where('grp', $group)
            ->where('subgrp', $subgroup)
            ->first();

        $pindahBuku = new PindahBuku();
        $alatabayargiro = DB::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.text',
                'a.memo'
            )
            ->where('a.grp', 'ALAT BAYAR GIRO')
            ->where('a.subgrp', 'ALAT BAYAR GIRO')
            ->first();

        $getCoaKredit = Bank::from(DB::raw("bank with (readuncommitted)"))->where('id', $data['bankdari_id'])->first();

        $alatabayarid = $data['alatbayar_id'] ?? 0;
        if ($alatabayarid == $alatabayargiro->text) {
            $memo = json_decode($alatabayargiro->memo, true);
            $coakredit_detail[] = $memo['JURNAL'];
            $coaKredit = $memo['JURNAL'];
        } else {
            $coaKredit = $getCoaKredit->coa;
            $coakredit_detail[] = $getCoaKredit->coa;
        }

        $getCoaDebet = Bank::from(DB::raw("bank with (readuncommitted)"))->where('id', $data['bankke_id'])->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

        $pindahBuku->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $pindahBuku->bankdari_id = $data['bankdari_id'];
        $pindahBuku->bankke_id = $data['bankke_id'];
        $pindahBuku->statusapproval = $statusApproval->id;
        $pindahBuku->coadebet = $getCoaDebet->coa;
        $pindahBuku->coakredit = $coaKredit;
        $pindahBuku->alatbayar_id = $data['alatbayar_id'];
        $pindahBuku->nowarkat = $data['nowarkat'] ?? '';
        $pindahBuku->tgljatuhtempo = date('Y-m-d', strtotime($data['tgljatuhtempo']));
        $pindahBuku->nominal = $data['nominal'];
        $pindahBuku->keterangan = $data['keterangan'];
        $pindahBuku->statusformat = $format->id;
        $pindahBuku->statuscetak = $statusCetak->id;
        $pindahBuku->modifiedby = auth('api')->user()->name;
        $pindahBuku->info = html_entity_decode(request()->info);


        $pindahBuku->nobukti = (new RunningNumberService)->get($group, $subgroup, $pindahBuku->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$pindahBuku->save()) {
            throw new \Exception("Error storing pindah buku.");
        }

        $pindahBukuLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($pindahBuku->getTable()),
            'postingdari' => 'ENTRY PINDAH BUKU',
            'idtrans' => $pindahBuku->id,
            'nobuktitrans' => $pindahBuku->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $pindahBuku->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        $coadebet_detail[] = $getCoaDebet->coa;
        $keterangan_detail[] = $data['keterangan'];
        $nominal_detail[] = $data['nominal'];


        $jurnalRequest = [
            'tanpaprosesnobukti' => 1,
            'nobukti' => $pindahBuku->nobukti,
            'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
            'postingdari' => 'ENTRY PINDAH BUKU',
            'statusformat' => "0",
            'coakredit_detail' => $coakredit_detail,
            'coadebet_detail' => $coadebet_detail,
            'nominal_detail' => $nominal_detail,
            'keterangan_detail' => $keterangan_detail
        ];

        (new JurnalUmumHeader())->processStore($jurnalRequest);
        return $pindahBuku;
    }
    public function processUpdate(PindahBuku $pindahBuku, array $data): PindahBuku
    {
        $group = 'PINDAH BUKU';
        $subgroup = 'NOMOR PINDAH BUKU';

        $nobuktiOld = $pindahBuku->nobukti;
        $querycek = DB::table('pindahbuku')->from(
            DB::raw("pindahbuku a with (readuncommitted)")
        )
            ->select(
                'a.nobukti'
            )
            ->where('a.id', $pindahBuku->id)
            ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
            ->first();

        if (isset($querycek)) {
            $nobukti = $querycek->nobukti;
        } else {
            $nobukti = (new RunningNumberService)->get($group, $subgroup, $pindahBuku->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
        }
        $alatabayargiro = DB::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.text',
                'a.memo'
            )
            ->where('a.grp', 'ALAT BAYAR GIRO')
            ->where('a.subgrp', 'ALAT BAYAR GIRO')
            ->first();

        $getCoaKredit = Bank::from(DB::raw("bank with (readuncommitted)"))->where('id', $data['bankdari_id'])->first();

        $alatabayarid = $data['alatbayar_id'] ?? 0;
        if ($alatabayarid == $alatabayargiro->text) {
            $memo = json_decode($alatabayargiro->memo, true);
            $coakredit_detail[] = $memo['JURNAL'];
            $coaKredit = $memo['JURNAL'];
        } else {
            $coaKredit = $getCoaKredit->coa;
            $coakredit_detail[] = $getCoaKredit->coa;
        }
        $getCoaDebet = Bank::from(DB::raw("bank with (readuncommitted)"))->where('id', $data['bankke_id'])->first();

        $pindahBuku->nobukti = $nobukti;
        $pindahBuku->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $pindahBuku->bankdari_id = $data['bankdari_id'];
        $pindahBuku->bankke_id = $data['bankke_id'];
        $pindahBuku->coadebet = $getCoaDebet->coa;
        $pindahBuku->coakredit = $coaKredit;
        $pindahBuku->alatbayar_id = $data['alatbayar_id'];
        $pindahBuku->nowarkat = $data['nowarkat'] ?? '';
        $pindahBuku->tgljatuhtempo = date('Y-m-d', strtotime($data['tgljatuhtempo']));
        $pindahBuku->nominal = $data['nominal'];
        $pindahBuku->keterangan = $data['keterangan'];
        $pindahBuku->modifiedby = auth('api')->user()->name;
        $pindahBuku->info = html_entity_decode(request()->info);

        if (!$pindahBuku->save()) {
            throw new \Exception("Error updating pindah buku.");
        }

        $pindahBukuLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($pindahBuku->getTable()),
            'postingdari' => 'EDIT PINDAH BUKU',
            'idtrans' => $pindahBuku->id,
            'nobuktitrans' => $pindahBuku->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $pindahBuku->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        $coadebet_detail[] = $getCoaDebet->coa;
        // $coakredit_detail[] = $getCoaKredit->coa;
        $keterangan_detail[] = $data['keterangan'];
        $nominal_detail[] = $data['nominal'];


        $jurnalRequest = [
            'tanpaprosesnobukti' => 1,
            'nobukti' => $pindahBuku->nobukti,
            'tglbukti' => $data['tglbukti'],
            'postingdari' => 'EDIT PINDAH BUKU',
            'statusformat' => "0",
            'coakredit_detail' => $coakredit_detail,
            'coadebet_detail' => $coadebet_detail,
            'nominal_detail' => $nominal_detail,
            'keterangan_detail' => $keterangan_detail
        ];
        $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $nobuktiOld)->first();

        if (isset($getJurnal)) {
            $newJurnal = new JurnalUmumHeader();
            $newJurnal = $newJurnal->find($getJurnal->id);
            $jurnalumumHeader = (new JurnalUmumHeader())->processUpdate($newJurnal, $jurnalRequest);
        } else {
            $jurnalRequest = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $pindahBuku->nobukti,
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'postingdari' => 'ENTRY PINDAH BUKU',
                'statusformat' => "0",
                'coakredit_detail' => $coakredit_detail,
                'coadebet_detail' => $coadebet_detail,
                'nominal_detail' => $nominal_detail,
                'keterangan_detail' => $keterangan_detail
            ];

            (new JurnalUmumHeader())->processStore($jurnalRequest);
        }



        return $pindahBuku;
    }

    public function processDestroy($id, $postingDari = ''): PindahBuku
    {

        $pindahBuku = new PindahBuku();
        $pindahBuku = $pindahBuku->lockAndDestroy($id);

        $pindahBukuLogTrail = (new LogTrail())->processStore([
            'namatabel' => $pindahBuku->getTable(),
            'postingdari' => $postingDari,
            'idtrans' => $pindahBuku->id,
            'nobuktitrans' => $pindahBuku->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $pindahBuku->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $pindahBuku->nobukti)->first();
        if ($getJurnal != '') {
            $jurnalumumHeader = (new JurnalUmumHeader())->processDestroy($getJurnal->id, $postingDari);
        }
        return $pindahBuku;
    }

    public function getExport($id)
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($this->table)->from(DB::raw("pindahbuku with (readuncommitted)"))
            ->select(
                'pindahbuku.id',
                'pindahbuku.nobukti',
                'pindahbuku.tglbukti',
                'bankdari.kodebank as bankdari',
                'bankke.kodebank as bankke',
                'alatbayar.kodealatbayar',
                'pindahbuku.tgljatuhtempo',
                'pindahbuku.keterangan',
                'pindahbuku.nowarkat',
                'pindahbuku.nominal',
                'statuscetak.memo as statuscetak',
                'statuscetak.id as  statuscetak_id',
                'pindahbuku.jumlahcetak',
                DB::raw("'Bukti Pindah Buku' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak"),
                db::raw("format(pindahbuku.tgljatuhtempo,'dd/')+
                    (case when month(pindahbuku.tgljatuhtempo)=1 then 'JAN'
                          when month(pindahbuku.tgljatuhtempo)=2 then 'FEB'
                          when month(pindahbuku.tgljatuhtempo)=3 then 'MAR'
                          when month(pindahbuku.tgljatuhtempo)=4 then 'APR'
                          when month(pindahbuku.tgljatuhtempo)=5 then 'MAY'
                          when month(pindahbuku.tgljatuhtempo)=6 then 'JUN'
                          when month(pindahbuku.tgljatuhtempo)=7 then 'JUL'
                          when month(pindahbuku.tgljatuhtempo)=8 then 'AGU'
                          when month(pindahbuku.tgljatuhtempo)=9 then 'SEP'
                          when month(pindahbuku.tgljatuhtempo)=10 then 'OKT'
                          when month(pindahbuku.tgljatuhtempo)=11 then 'NOV'
                          when month(pindahbuku.tgljatuhtempo)=12 then 'DES' ELSE '' END)

                    +format(pindahbuku.tgljatuhtempo,'/yy')   as tgljatuhtempoformat"),                
            )
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), "pindahbuku.alatbayar_id", "alatbayar.id")
            ->leftJoin(DB::raw("bank as bankdari with (readuncommitted)"), "pindahbuku.bankdari_id", "bankdari.id")
            ->leftJoin(DB::raw("bank as bankke with (readuncommitted)"), "pindahbuku.bankke_id", "bankke.id")
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'pindahbuku.statuscetak', 'statuscetak.id')
            ->where('pindahbuku.id', $id);

        $data = $query->first();
        return $data;
    }

    public function approvalData(array $data)
    {
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        for ($i = 0; $i < count($data['pindahId']); $i++) {
            $pengeluaranHeader = PindahBuku::find($data['pindahId'][$i]);
            if ($pengeluaranHeader->statusapproval == $statusApproval->id) {
                $pengeluaranHeader->statusapproval = $statusNonApproval->id;
                $aksi = $statusNonApproval->text;
            } else {
                $pengeluaranHeader->statusapproval = $statusApproval->id;
                $aksi = $statusApproval->text;
            }

            $pengeluaranHeader->tglapproval = date('Y-m-d', time());
            $pengeluaranHeader->userapproval = auth('api')->user()->name;

            if (!$pengeluaranHeader->save()) {
                throw new \Exception("Error approval pindah buku.");
            }

            (new LogTrail())->processStore([
                'namatabel' => strtoupper($pengeluaranHeader->getTable()),
                'postingdari' => 'APPROVAL PINDAH BUKU',
                'idtrans' =>  $pengeluaranHeader->id,
                'nobuktitrans' => $pengeluaranHeader->nobukti,
                'aksi' => $aksi,
                'datajson' => $pengeluaranHeader->toArray(),
                'modifiedby' => auth('api')->user()->name,
            ]);
        }
    }
}
