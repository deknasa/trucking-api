<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NotaKreditHeader extends MyModel
{
    use HasFactory;

    protected $table = 'notakreditheader';

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
        $query = DB::table($this->table)->from(DB::raw("notakreditheader with (readuncommitted)"))
            ->select(
                "$this->table.id",
                "$this->table.nobukti",
                "$this->table.nobukti as nobuktihidden",
                "$this->table.pelunasanpiutang_nobukti",
                "$this->table.tglbukti",
                DB::raw('(case when (year(notakreditheader.tglapproval) <= 2000) then null else notakreditheader.tglapproval end ) as tglapproval'),
                "$this->table.postingdari",
                "$this->table.statusapproval",
                "$this->table.tgllunas",
                "$this->table.userapproval",
                "$this->table.userbukacetak",
                DB::raw('(case when (year(notakreditheader.tglbukacetak) <= 2000) then null else notakreditheader.tglbukacetak end ) as tglbukacetak'),
                "$this->table.statusformat",
                "$this->table.modifiedby",
                "$this->table.statuscetak",
                "$this->table.created_at",
                "$this->table.updated_at",
                "parameter.memo as  statusapproval_memo",
                "statuscetak.memo as  statuscetak_memo",
                "$this->table.pengeluaran_nobukti",
                "agen.namaagen as agen",
                "bank.namabank as bank",
                "alatbayar.namaalatbayar as alatbayar",
                DB::raw('(case when (year(notakreditheader.tglkirimberkas) <= 2000) then null else notakreditheader.tglkirimberkas end ) as tglkirimberkas'),
                'statuskirimberkas.memo as statuskirimberkas',
                'notakreditheader.userkirimberkas',
                db::raw("cast((format(pengeluaranheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpengeluaranheader"),
                db::raw("cast(cast(format((cast((format(pengeluaranheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpengeluaranheader"),
                db::raw("cast((format(pelunasanpiutangheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpelunasanpiutangheader"),
                db::raw("cast(cast(format((cast((format(pelunasanpiutangheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpelunasanpiutangheader"),
            )
            ->leftJoin(DB::raw("pelunasanpiutangheader with (readuncommitted)"), 'notakreditheader.pelunasanpiutang_nobukti', '=', 'pelunasanpiutangheader.nobukti')
            ->leftJoin(DB::raw("pengeluaranheader with (readuncommitted)"), 'notakreditheader.pengeluaran_nobukti', '=', 'pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'notakreditheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'notakreditheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'notakreditheader.alatbayar_id', 'alatbayar.id')
            ->leftJoin('parameter as statuscetak', 'notakreditheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("parameter as statuskirimberkas with (readuncommitted)"), 'notakreditheader.statuskirimberkas', 'statuskirimberkas.id')
            ->leftJoin('parameter', 'notakreditheader.statusapproval', 'parameter.id');
        if (request()->tgldari && request()->tglsampai) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(notakreditheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(notakreditheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("notakreditheader.statuscetak", $statusCetak);
        }

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function cekvalidasiaksi($id)
    {
        $error = new Error();
        $keteranganerror = $error->cekKeteranganError('TDT') ?? '';
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $notaKredit = DB::table("notakreditheader")->from(DB::raw("notakreditheader with (readuncommitted)"))->where('id', $id)->first();

        $pelunasanPiutang = DB::table('pelunasanpiutangheader')
            ->from(
                DB::raw("pelunasanpiutangheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.notakredit_nobukti'
            )
            ->where('a.notakredit_nobukti', '=', $notaKredit->nobukti)
            ->first();
        if (isset($pelunasanPiutang)) {
            $data = [
                'kondisi' => true,
                'keterangan' =>  'No Bukti <b>' . $notaKredit->nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti PELUNASAN PIUTANG <b>' . $pelunasanPiutang->nobukti . '</b> <br> ' . $keterangantambahanerror,
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }
        $pelunasanPiutang = DB::table('pelunasanpiutangheader')
            ->from(
                DB::raw("pelunasanpiutangheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.notakreditpph_nobukti'
            )
            ->where('a.notakreditpph_nobukti', '=', $notaKredit->nobukti)
            ->first();
        if (isset($pelunasanPiutang)) {
            $data = [
                'kondisi' => true,
                'keterangan' =>  'No Bukti <b>' . $notaKredit->nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti PELUNASAN PIUTANG <b>' . $pelunasanPiutang->nobukti . '</b> <br> ' . $keterangantambahanerror,
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('SAPP') ?? '';
        if ($notaKredit->pengeluaran_nobukti != '') {
            $jurnal = DB::table('pengeluaranheader')
                ->from(
                    DB::raw("pengeluaranheader as a with (readuncommitted)")
                )
                ->select(
                    'a.nobukti'
                )
                ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
                ->where('a.nobukti', '=', $notaKredit->pengeluaran_nobukti)
                ->first();

            if (isset($jurnal)) {
                $data = [
                    'kondisi' => true,
                    'keterangan' =>  'No Bukti <b>' . $notaKredit->nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti Approval Jurnal <b>' . $jurnal->nobukti . '</b> <br> ' . $keterangantambahanerror,
                    'kodeerror' => 'SAPP'
                ];
                goto selesai;
            }
        } else {

            $jurnal = DB::table('notakreditheader')
                ->from(
                    DB::raw("notakreditheader as a with (readuncommitted)")
                )
                ->select(
                    'a.nobukti'
                )
                ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
                ->where('a.nobukti', '=', $notaKredit->nobukti)
                ->first();
            if (isset($jurnal)) {
                $data = [
                    'kondisi' => true,
                    'keterangan' =>  'No Bukti <b>' . $notaKredit->nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti Approval Jurnal <b>' . $jurnal->nobukti . '</b> <br> ' . $keterangantambahanerror,
                    'kodeerror' => 'SAPP'
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
            ->first();


        DB::table($tempdefault)->insert(
            ["bank_id" => $bank->bank_id, "bank" => $bank->bank, "alatbayar_id" => $alatbayar->alatbayar_id, "alatbayar" => $alatbayar->alatbayar]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'bank_id',
                'bank',
                'alatbayar_id',
                'alatbayar',
            );

        $data = $query->first();

        return $data;
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->nullable();
            $table->date('tgllunas')->nullable();
            $table->string('agen', 200)->nullable();
            $table->string('pelunasanpiutang_nobukti', 50)->nullable();
            $table->string('bank', 50)->nullable();
            $table->string('alatbayar', 50)->nullable();
            $table->string('pengeluaran_nobukti', 50)->nullable();
            $table->string('postingdari', 50)->nullable();
            $table->string('statusapproval_memo')->nullable();
            $table->string('userapproval', 50)->nullable();
            $table->date('tglapproval')->nullable();
            $table->string('statuscetak_memo')->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->longtext('statuskirimberkas')->nullable();
            $table->string('userkirimberkas', 200)->nullable();
            $table->date('tglkirimberkas')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });
        if ((date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tgldariheader))) || (date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tglsampaiheader)))) {
            request()->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            request()->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
        }


        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $query = $this->sort($query);
        $models = $this->filter($query);
        $models = $query
            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);

        DB::table($temp)->insertUsing([
            "id",
            "nobukti",
            "tglbukti",
            "tgllunas",
            "agen",
            "pelunasanpiutang_nobukti",
            "bank",
            "alatbayar",
            "pengeluaran_nobukti",
            "postingdari",
            "statusapproval_memo",
            "userapproval",
            "tglapproval",
            "statuscetak_memo",
            "userbukacetak",
            "tglbukacetak",
            "statuskirimberkas",
            "userkirimberkas",
            "tglkirimberkas",
            "modifiedby",
            "created_at",
            "updated_at",
        ], $models);
        return $temp;
    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                "$this->table.id",
                "$this->table.nobukti",
                "$this->table.tglbukti",
                "$this->table.tgllunas",
                'agen.namaagen as agen',
                "$this->table.pelunasanpiutang_nobukti",
                'bank.namabank as bank',
                'alatbayar.namaalatbayar as alatbayar',
                "$this->table.pengeluaran_nobukti",
                "$this->table.postingdari",
                "parameter.text as  statusapproval_memo",
                "$this->table.userapproval",
                DB::raw('(case when (year(notakreditheader.tglapproval) <= 2000) then null else notakreditheader.tglapproval end ) as tglapproval'),
                "statuscetak.text as  statuscetak_memo",
                "$this->table.userbukacetak",
                DB::raw('(case when (year(notakreditheader.tglbukacetak) <= 2000) then null else notakreditheader.tglbukacetak end ) as tglbukacetak'),
                "statuskirimberkas.text as statuskirimberkas",
                "$this->table.userkirimberkas",
                "$this->table.tglkirimberkas",
                "$this->table.modifiedby",
                "$this->table.created_at",
                "$this->table.updated_at",
            )

            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'notakreditheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'notakreditheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'notakreditheader.alatbayar_id', 'alatbayar.id')
            ->leftJoin('parameter as statuscetak', 'notakreditheader.statuscetak', 'statuscetak.id')
            ->leftJoin('parameter', 'notakreditheader.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statuskirimberkas with (readuncommitted)"), 'notakreditheader.statuskirimberkas', 'statuskirimberkas.id');
    }

    public function getNotaKredit($id)
    {
        $this->setRequestParameters();

        $query = DB::table('pelunasanpiutangdetail')
            ->select(DB::raw('
        pelunasanpiutangdetail.id as detail_id,
        pelunasanpiutangdetail.nobukti,
        pelunasanpiutangdetail.tglcair,
        pelunasanpiutangdetail.nominal as nominalbayar,
        pelunasanpiutangdetail.nominal as nominal,
        pelunasanpiutangdetail.piutang_nobukti,
        pelunasanpiutangdetail.invoice_nobukti,
        notakreditheader.keterangan,
        pelunasanpiutangdetail.coapenyesuaian,
        COALESCE (pelunasanpiutangdetail.penyesuaian, 0) as penyesuaian '))

            ->leftJoin('piutangheader', 'piutangheader.nobukti', 'pelunasanpiutangdetail.piutang_nobukti')
            ->leftJoin('notakreditheader', 'notakreditheader.pelunasanpiutang_nobukti', 'pelunasanpiutangdetail.nobukti')
            ->leftJoin('pelanggan', 'pelunasanpiutangdetail.pelanggan_id', 'pelanggan.id')
            ->leftJoin('agen', 'pelunasanpiutangdetail.agen_id', 'agen.id')
            ->whereRaw(" EXISTS (
            SELECT notakreditheader.pelunasanpiutang_nobukti
            FROM notakreditdetail
			left join notakreditheader on notakreditdetail.notakredit_id = notakreditheader.id
            WHERE notakreditheader.pelunasanpiutang_nobukti = pelunasanpiutangdetail.nobukti   
          )")
            ->where('pelunasanpiutangdetail.penyesuaian', '>', 0)
            ->where('notakreditheader.id', $id);



        $data = $query->get();

        return $data;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'agen') {
            return $query->orderBy('agen.namaagen', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'bank') {
            return $query->orderBy('bank.namabank', $this->params['sortOrder']);
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
                            if ($filters['field'] == 'statusapproval_memo') {
                                $query = $query->where('parameter.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'statuscetak_memo') {
                                $query = $query->where('statuscetak.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'statuskirimberkas') {
                                $query = $query->where('statuskirimberkas.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'agen') {
                                $query = $query->where('agen.namaagen', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'bank') {
                                $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'alatbayar') {
                                $query = $query->where('alatbayar.namaalatbayar', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgllunas' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak') {
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
                                if ($filters['field'] == 'statusapproval_memo') {
                                    $query = $query->orWhere('parameter.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'statuscetak_memo') {
                                    $query = $query->orWhere('statuscetak.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'statuskirimberkas') {
                                    $query = $query->orWhere('statuskirimberkas.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'agen') {
                                    $query = $query->orWhere('agen.namaagen', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'bank') {
                                    $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'alatbayar') {
                                    $query = $query->orWhere('alatbayar.namaalatbayar', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgllunas' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak') {
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

            $this->totalRows = $query->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }
        if (request()->cetak && request()->periode) {
            $query->where('notakreditheader.statuscetak', '<>', request()->cetak)
                ->whereYear('notakreditheader.tglbukti', '=', request()->year)
                ->whereMonth('notakreditheader.tglbukti', '=', request()->month);
            return $query;
        }

        return $query;
    }
    public function findAll($id)
    {
        $query = NotaKreditHeader::from(DB::raw("notakreditheader with (readuncommitted)"))
            ->select(
                'notakreditheader.id',
                'notakreditheader.nobukti',
                'notakreditheader.tglbukti',
                'notakreditheader.tgllunas',
                'notakreditheader.agen_id',
                'agen.namaagen as agen',
                'notakreditheader.bank_id',
                'bank.namabank as bank',
                'notakreditheader.alatbayar_id',
                'alatbayar.namaalatbayar as alatbayar',
                'notakreditheader.nowarkat',
                'notakreditheader.pengeluaran_nobukti'
            )
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'notakreditheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'notakreditheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'notakreditheader.alatbayar_id', 'alatbayar.id');

        $data = $query->where("notakreditheader.id", $id)->first();
        return $data;
    }
    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function processStore(array $data): NotaKreditHeader
    {
        $tanpaprosesnobukti = $data['tanpaprosesnobukti'] ?? '';
        $group = 'NOTA KREDIT BUKTI';
        $subGroup = 'NOTA KREDIT BUKTI';
        $format = DB::table('parameter')->where('grp', $group)->where('subgrp', $subGroup)->first();

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $notaKreditHeader = new NotaKreditHeader();

        $notaKreditHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $notaKreditHeader->pelunasanpiutang_nobukti = $data['pelunasanpiutang_nobukti'] ?? '';
        $notaKreditHeader->agen_id = $data['agen_id'];
        $notaKreditHeader->bank_id = $data['bank_id'] ?? '';
        $notaKreditHeader->alatbayar_id = $data['alatbayar_id'] ?? '';
        $notaKreditHeader->nowarkat = $data['nowarkat'] ?? '';
        $notaKreditHeader->statusapproval = $statusApproval->id;
        $notaKreditHeader->tgllunas = date('Y-m-d', strtotime($data['tgllunas']));
        $notaKreditHeader->statusformat = $format->id;
        $notaKreditHeader->statuscetak = $statusCetak->id;
        $notaKreditHeader->postingdari = $data['postingdari'] ?? 'ENTRY NOTA KREDIT HEADER';
        $notaKreditHeader->modifiedby = auth('api')->user()->name;
        $notaKreditHeader->info = html_entity_decode(request()->info);
        $notaKreditHeader->nobukti = (new RunningNumberService)->get($group, $subGroup, $notaKreditHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$notaKreditHeader->save()) {
            throw new \Exception("Error storing nota kredit header.");
        }

        $notaKreditHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($notaKreditHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? 'ENTRY NOTA KREDIT HEADER',
            'idtrans' => $notaKreditHeader->id,
            'nobuktitrans' => $notaKreditHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $notaKreditHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        $notaKreditDetails = [];
        $coakredit_detail = [];
        $coadebet_detail = [];
        $nominal_detail = [];
        $keterangan_detail = [];

        $getCoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'TIPENOTAKREDIT')->where('text', 'UANG DIBAYAR DIMUKA')->first();

        $memoNotaKreditCoa = json_decode($getCoa->memo, true);
        if ($tanpaprosesnobukti != 1) {
            $data['cekcoadebet'] = $memoNotaKreditCoa['JURNAL'];
        }
        for ($i = 0; $i < count($data['potongan']); $i++) {
            $notaKreditDetail = (new NotaKreditDetail())->processStore($notaKreditHeader, [
                "invoice_nobukti" => $data['invoice_nobukti'][$i] ?? '',
                "nominal" => $data['nominalpiutang'][$i] ?? 0,
                "nominalbayar" => $data['nominal'][$i] ?? 0,
                "penyesuaian" => $data['potongan'][$i],
                "keterangandetail" => $data['keteranganpotongan'][$i],
                "coaadjust" => $data['coadebet'][$i] ?? $memoNotaKreditCoa['JURNAL']
            ]);
            $notaKreditDetails[] = $notaKreditDetail->toArray();
            $coakredit_detail[] = $data['coakredit'][$i] ?? '';
            $coadebet_detail[] = $data['coadebet'][$i] ?? $memoNotaKreditCoa['JURNAL'];
            $nominal_detail[] = $data['potongan'][$i];
            $keterangan_detail[] = $data['keteranganpotongan'][$i];
            $tglkasmasuk[] = $notaKreditHeader->tglbukti;
            $nowarkat[] = $data['nowarkat'] ?? '';
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($notaKreditDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? 'ENTRY NOTA KREDIT DETAIL',
            'idtrans' => $notaKreditHeaderLogTrail->id,
            'nobuktitrans' => $notaKreditHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $notaKreditDetails,
            'modifiedby' => auth('api')->user()->user
        ]);

        if ($data['cekcoadebet'] == $memoNotaKreditCoa['JURNAL']) {
            /*STORE PENGELUARAN*/
            $pengeluaranRequest = [
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'pelanggan_id' => 0,
                'postingdari' => $data['postingdari'] ?? "ENTRY NOTA KREDIT",
                'statusapproval' => $statusApproval->id,
                'dibayarke' => $data['agen'],
                'alatbayar_id' => $data['alatbayar_id'],
                'bank_id' => $data['bank_id'],
                'transferkeac' => "",
                'transferkean' => "",
                'transferkebank' => "",
                'userapproval' => "",
                'tglapproval' => "",

                'nowarkat' => $nowarkat,
                'tgljatuhtempo' => $tglkasmasuk,
                "nominal_detail" => $nominal_detail,
                'coadebet' => $coadebet_detail,
                "keterangan_detail" => $keterangan_detail,
                'bulanbeban' => $tglkasmasuk,
            ];

            $pengeluaranHeader = (new PengeluaranHeader())->processStore($pengeluaranRequest);

            $notaKreditHeader->pengeluaran_nobukti = $pengeluaranHeader->nobukti;
            $notaKreditHeader->save();
        } else {
            /*STORE JURNAL*/
            $jurnalRequest = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $notaKreditHeader->nobukti,
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'postingdari' => $data['postingdari'],
                'statusapproval' => $statusApproval->id,
                'userapproval' => "",
                'tglapproval' => "",
                'modifiedby' => auth('api')->user()->name,
                'statusformat' => "0",
                'coakredit_detail' => $coakredit_detail,
                'coadebet_detail' => $coadebet_detail,
                'nominal_detail' => $nominal_detail,
                'keterangan_detail' => $keterangan_detail
            ];

            (new JurnalUmumHeader())->processStore($jurnalRequest);
        }
        return $notaKreditHeader;
    }

    public function processUpdate(NotaKreditHeader $notaKreditHeader, array $data): NotaKreditHeader
    {
        $tanpaprosesnobukti = $data['tanpaprosesnobukti'] ?? '';
        $nobuktiOld = $notaKreditHeader->nobukti;
        $getTgl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'EDIT TANGGAL BUKTI')->where('subgrp', 'NOTA KREDIT')->first();

        if (trim($getTgl->text) == 'YA') {
            $group = 'NOTA KREDIT BUKTI';
            $subGroup = 'NOTA KREDIT BUKTI';
            $querycek = DB::table('notakreditheader')->from(
                DB::raw("notakreditheader a with (readuncommitted)")
            )
                ->select(
                    'a.nobukti'
                )
                ->where('a.id', $notaKreditHeader->id)
                ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
                ->first();

            if (isset($querycek)) {
                $nobukti = $querycek->nobukti;
            } else {
                $nobukti = (new RunningNumberService)->get($group, $subGroup, $notaKreditHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
            }

            $notaKreditHeader->nobukti = $nobukti;
            $notaKreditHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        }
        $notaKreditHeader->tgllunas = date('Y-m-d', strtotime($data['tgllunas']));
        $notaKreditHeader->nowarkat = $data['nowarkat'] ?? '';
        $notaKreditHeader->agen_id = $data['agen_id'] ?? '';
        $notaKreditHeader->pelunasanpiutang_nobukti = $data['pelunasanpiutang_nobukti'] ?? '';
        $notaKreditHeader->modifiedby = auth('api')->user()->name;
        $notaKreditHeader->info = html_entity_decode(request()->info);

        if (!$notaKreditHeader->save()) {
            throw new \Exception("Error Update nota kredit header.");
        }

        $notaKreditHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($notaKreditHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? 'EDIT NOTA KREDIT HEADER',
            'idtrans' => $notaKreditHeader->id,
            'nobuktitrans' => $notaKreditHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $notaKreditHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        $getPreviousCoa = DB::table("notakreditdetail")->from(DB::raw("notakreditdetail with (readuncommitted)"))->select('coaadjust')->where('notakredit_id', $notaKreditHeader->id)->first();

        NotaKreditDetail::where('notakredit_id', $notaKreditHeader->id)->lockForUpdate()->delete();

        $notaKreditDetails = [];
        $coakredit_detail = [];
        $coadebet_detail = [];
        $nominal_detail = [];
        $keterangan_detail = [];
        $getCoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'TIPENOTAKREDIT')->where('text', 'UANG DIBAYAR DIMUKA')->first();

        $memoNotaKreditCoa = json_decode($getCoa->memo, true);
        if ($tanpaprosesnobukti != 1) {
            $data['cekcoadebet'] = $memoNotaKreditCoa['JURNAL'];
        }
        for ($i = 0; $i < count($data['potongan']); $i++) {
            $notaKreditDetail = (new NotaKreditDetail())->processStore($notaKreditHeader, [
                "invoice_nobukti" => $data['invoice_nobukti'][$i] ?? '',
                "nominal" => $data['nominalpiutang'][$i] ?? 0,
                "nominalbayar" => $data['nominal'][$i] ?? 0,
                "penyesuaian" => $data['potongan'][$i],
                "keterangandetail" => $data['keteranganpotongan'][$i],
                "coaadjust" => $data['coadebet'][$i] ?? $memoNotaKreditCoa['JURNAL']
            ]);
            $notaKreditDetails[] = $notaKreditDetail->toArray();
            $coakredit_detail[] = $data['coakredit'][$i] ?? '';
            $coadebet_detail[] = $data['coadebet'][$i] ?? $memoNotaKreditCoa['JURNAL'];
            $nominal_detail[] = $data['potongan'][$i];
            $keterangan_detail[] = $data['keteranganpotongan'][$i];
            $tglkasmasuk[] = $notaKreditHeader->tglbukti;
            $nowarkat[] = $data['nowarkat'] ?? '';
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($notaKreditDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? 'EDIT NOTA KREDIT DETAIL',
            'idtrans' => $notaKreditHeaderLogTrail->id,
            'nobuktitrans' => $notaKreditHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $notaKreditDetails,
            'modifiedby' => auth('api')->user()->user
        ]);

        if ($tanpaprosesnobukti == 1) {
            // CEK JIKA COA BERGANTI
            if ($data['cekcoadebet'] != $getPreviousCoa->coaadjust) {
                // DELETE EXISTED NO BUKTI
                if ($getPreviousCoa->coaadjust == $memoNotaKreditCoa['JURNAL']) {
                    $getPenerimaan = PengeluaranHeader::from(DB::raw("pengeluaranheader with (readuncommitted)"))->where('nobukti', $notaKreditHeader->pengeluaran_nobukti)->first();
                    (new PengeluaranHeader())->processDestroy($getPenerimaan->id, $data['postingdari']);

                    $notaKreditHeader->pengeluaran_nobukti = '';
                    $notaKreditHeader->save();
                } else {
                    $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $notaKreditHeader->nobukti)->first();
                    (new JurnalUmumHeader())->processDestroy($getJurnal->id, $data['postingdari']);
                }
                $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

                if ($data['cekcoadebet'] == $memoNotaKreditCoa['JURNAL']) {
                    /*STORE PENGELUARAN*/
                    $pengeluaranRequest = [
                        'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                        'pelanggan_id' => 0,
                        'postingdari' => $data['postingdari'] ?? "ENTRY NOTA KREDIT",
                        'statusapproval' => $statusApproval->id,
                        'dibayarke' => $data['agen'],
                        'alatbayar_id' => $data['alatbayar_id'],
                        'bank_id' => $data['bank_id'],
                        'transferkeac' => "",
                        'transferkean' => "",
                        'transferkebank' => "",
                        'userapproval' => "",
                        'tglapproval' => "",

                        'nowarkat' => $nowarkat,
                        'tgljatuhtempo' => $tglkasmasuk,
                        "nominal_detail" => $nominal_detail,
                        'coadebet' => $coadebet_detail,
                        "keterangan_detail" => $keterangan_detail,
                        'bulanbeban' => $tglkasmasuk,
                    ];

                    $pengeluaranHeader = (new PengeluaranHeader())->processStore($pengeluaranRequest);

                    $notaKreditHeader->pengeluaran_nobukti = $pengeluaranHeader->nobukti;
                    $notaKreditHeader->save();
                } else {
                    /*STORE JURNAL*/
                    $jurnalRequest = [
                        'tanpaprosesnobukti' => 1,
                        'nobukti' => $notaKreditHeader->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                        'postingdari' => $data['postingdari'],
                        'statusapproval' => $statusApproval->id,
                        'userapproval' => "",
                        'tglapproval' => "",
                        'modifiedby' => auth('api')->user()->name,
                        'statusformat' => "0",
                        'coakredit_detail' => $coakredit_detail,
                        'coadebet_detail' => $coadebet_detail,
                        'nominal_detail' => $nominal_detail,
                        'keterangan_detail' => $keterangan_detail
                    ];

                    (new JurnalUmumHeader())->processStore($jurnalRequest);
                }
            }
        }

        if ($data['cekcoadebet'] == $getPreviousCoa->coaadjust) {
            if ($data['cekcoadebet'] == $memoNotaKreditCoa['JURNAL']) {
                $pengeluaranRequest = [
                    'tglbukti' => $notaKreditHeader->tglbukti,
                    'pelanggan_id' => 0,
                    'postingdari' => $data['postingdari'] ?? "EDIT NOTA KREDIT",
                    'dibayarke' => $data['agen'],
                    'alatbayar_id' => $data['alatbayar_id'],
                    'bank_id' => $data['bank_id'],
                    'transferkeac' => "",
                    'transferkean' => "",
                    'transferkebank' => "",
                    'userapproval' => "",
                    'tglapproval' => "",

                    'nowarkat' => $nowarkat,
                    'tgljatuhtempo' => $tglkasmasuk,
                    "nominal_detail" => $nominal_detail,
                    'coadebet' => $coadebet_detail,
                    "keterangan_detail" => $keterangan_detail,
                    'bulanbeban' => $tglkasmasuk,
                ];

                $pengeluaranHeader = PengeluaranHeader::where('nobukti', $notaKreditHeader->pengeluaran_nobukti)->first();
                $pengeluaranHeader = (new PengeluaranHeader())->processUpdate($pengeluaranHeader, $pengeluaranRequest);

                $notaKreditHeader->pengeluaran_nobukti = $pengeluaranHeader->nobukti;
                $notaKreditHeader->save();
            } else {
                /*STORE JURNAL*/
                $jurnalRequest = [
                    'tanpaprosesnobukti' => 1,
                    'nobukti' => $notaKreditHeader->nobukti,
                    'tglbukti' => $notaKreditHeader->tglbukti,
                    'postingdari' => $data['postingdari'],
                    'coakredit_detail' => $coakredit_detail,
                    'coadebet_detail' => $coadebet_detail,
                    'nominal_detail' => $nominal_detail,
                    'keterangan_detail' => $keterangan_detail
                ];
                $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $nobuktiOld)->first();
                $newJurnal = new JurnalUmumHeader();
                $newJurnal = $newJurnal->find($getJurnal->id);
                (new JurnalUmumHeader())->processUpdate($newJurnal, $jurnalRequest);
            }
        }
        return $notaKreditHeader;
    }

    public function processDestroy($id, $postingDari = ''): NotaKreditHeader
    {
        $notaKreditDetails = NotaKreditDetail::lockForUpdate()->where('notakredit_id', $id)->get();

        $notaKreditHeader = new NotaKreditHeader();
        $notaKreditHeader = $notaKreditHeader->lockAndDestroy($id);

        $notaKreditHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => $notaKreditHeader->getTable(),
            'postingdari' => $postingDari,
            'idtrans' => $notaKreditHeader->id,
            'nobuktitrans' => $notaKreditHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $notaKreditHeader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'NOTAKREDITDETAIL',
            'postingdari' => $postingDari,
            'idtrans' => $notaKreditHeaderLogTrail['id'],
            'nobuktitrans' => $notaKreditHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $notaKreditDetails->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);
        if ($notaKreditHeader->pengeluaran_nobukti != '') {
            $getPenerimaan = PengeluaranHeader::from(DB::raw("pengeluaranheader with (readuncommitted)"))->where('nobukti', $notaKreditHeader->pengeluaran_nobukti)->first();
            (new PengeluaranHeader())->processDestroy($getPenerimaan->id, $postingDari);
        } else {
            $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $notaKreditHeader->nobukti)->first();
            (new JurnalUmumHeader())->processDestroy($getJurnal->id, $postingDari);
        }
        return $notaKreditHeader;
    }

    public function getExport($id)
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($this->table)->from(DB::raw("notakreditheader with (readuncommitted)"))
            ->select(
                "$this->table.id",
                "$this->table.nobukti",
                "$this->table.pelunasanpiutang_nobukti",
                "$this->table.tglbukti",
                "$this->table.postingdari",
                "$this->table.tgllunas",
                "$this->table.jumlahcetak",
                'pelunasanpiutang.penerimaan_nobukti',
                'statuscetak.memo as statuscetak',
                'statuscetak.id as  statuscetak_id',
                DB::raw("'Nota Kredit' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'notakreditheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("pelunasanpiutangheader as pelunasanpiutang with (readuncommitted)"), 'notakreditheader.pelunasanpiutang_nobukti', 'pelunasanpiutang.nobukti');
        $data = $query->first();
        return $data;
    }
}
