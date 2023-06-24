<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\RunningNumberService;

class PengeluaranHeader extends MyModel
{
    use HasFactory;

    protected $table = 'pengeluaranheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function pengeluarandetail()
    {
        return $this->hasMany(pengeluarandetail::class, 'pengeluaran_id');
    }

    public function get()
    {
        $this->setRequestParameters();
        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';

        $query = DB::table($this->table)->from(DB::raw("pengeluaranheader with (readuncommitted)"))
            ->select(
                'pengeluaranheader.id',
                'pengeluaranheader.nobukti',
                'pengeluaranheader.tglbukti',

                'pelanggan.namapelanggan as pelanggan_id',

                'pengeluaranheader.postingdari',
                'pengeluaranheader.dibayarke',
                'alatbayar.namaalatbayar as alatbayar_id',
                'bank.namabank as bank_id',
                'statusapproval.memo as statusapproval',
                DB::raw('(case when (year(pengeluaranheader.tglapproval) <= 2000) then null else pengeluaranheader.tglapproval end ) as tglapproval'),
                'pengeluaranheader.userapproval',
                'pengeluaranheader.userbukacetak',
                'pengeluaranheader.transferkeac',
                'pengeluaranheader.transferkean',
                'pengeluaranheader.transferkebank',
                DB::raw('(case when (year(pengeluaranheader.tglbukacetak) <= 2000) then null else pengeluaranheader.tglbukacetak end ) as tglbukacetak'),
                'statuscetak.memo as statuscetak',
                'pengeluaranheader.userbukacetak',
                'pengeluaranheader.jumlahcetak',
                'pengeluaranheader.modifiedby',
                'pengeluaranheader.created_at',
                'pengeluaranheader.updated_at'

            )

            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'pengeluaranheader.pelanggan_id', 'pelanggan.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pengeluaranheader.alatbayar_id', 'alatbayar.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluaranheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'pengeluaranheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'pengeluaranheader.statuscetak', 'statuscetak.id');
        if (request()->tgldari && request()->tglsampai) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
                ->where('pengeluaranheader.bank_id', request()->bank_id);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(pengeluaranheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(pengeluaranheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("pengeluaranheader.statuscetak", $statusCetak);
        }
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
        $query =  PengeluaranHeader::from(DB::raw("pengeluaranheader with (readuncommitted)"))
            ->select(
                'pengeluaranheader.id',
                'pengeluaranheader.nobukti',
                'pengeluaranheader.tglbukti',
                'pengeluaranheader.pelanggan_id',
                'pelanggan.namapelanggan as pelanggan',
                'pengeluaranheader.alatbayar_id',
                'alatbayar.namaalatbayar as alatbayar',
                'pengeluaranheader.statuscetak',
                'pengeluaranheader.dibayarke',
                'pengeluaranheader.bank_id',
                'bank.namabank as bank',
                'pengeluaranheader.transferkeac',
                'pengeluaranheader.transferkean',
                'pengeluaranheader.transferkebank',
                'pengeluaranheader.statuscetak',
                'pengeluaranheader.userbukacetak',
                'pengeluaranheader.jumlahcetak',
                'pengeluaranheader.tglbukacetak',
            )
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'pengeluaranheader.pelanggan_id', 'pelanggan.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pengeluaranheader.alatbayar_id', 'alatbayar.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluaranheader.bank_id', 'bank.id')
            ->where('pengeluaranheader.id', $id);

        $data = $query->first();

        return $data;
    }

    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
                "$this->table.id,
                 $this->table.nobukti,
                 $this->table.tglbukti,
                 'pelanggan.namapelanggan as pelanggan_id',
                 $this->table.postingdari,
                 $this->table.dibayarke,
                 'alatbayar.namaalatbayar as alatbayar_id',
                 'bank.namabank as bank_id',
                 'statusapproval.text as statusapproval',
                 $this->table.transferkeac,
                 $this->table.transferkean,
                 $this->table.transferkebank,
                 'statuscetak.text as statuscetak',
                 $this->table.userbukacetak,
                 $this->table.tglbukacetak,
                 $this->table.jumlahcetak,
                 $this->table.modifiedby,
                 $this->table.created_at,
                 $this->table.updated_at"
            )
        )
            ->leftJoin('pelanggan', 'pengeluaranheader.pelanggan_id', 'pelanggan.id')
            ->leftJoin('alatbayar', 'pengeluaranheader.alatbayar_id', 'alatbayar.id')
            ->leftJoin('bank', 'pengeluaranheader.bank_id', 'bank.id')
            ->leftJoin('parameter as statusapproval', 'pengeluaranheader.statusapproval', 'statusapproval.id')
            ->leftJoin('parameter as statuscetak', 'pengeluaranheader.statuscetak', 'statuscetak.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('pelanggan_id', 1000)->nullable()->nullable();
            $table->string('postingdari', 1000)->nullable();
            $table->string('dibayarke', 1000)->nullable();
            $table->string('alatbayar_id', 1000)->nullable();
            $table->string('bank_id', 1000)->nullable();
            $table->string('statusapproval')->nullable();
            $table->string('transferkeac')->nullable();
            $table->string('transferkean')->nullable();
            $table->string('transferkebank')->nullable();
            $table->string('statuscetak', 1000)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
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
        $models =  $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))])->where($this->table . '.bank_id', request()->bankheader);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'pelanggan_id', 'postingdari', 'dibayarke', 'alatbayar_id', 'bank_id', 'statusapproval', 'transferkeac', 'transferkean', 'transferkebank', 'statuscetak', 'userbukacetak', 'tglbukacetak', 'jumlahcetak', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }
    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'pelanggan_id') {
            return $query->orderBy('pelanggan.namapelanggan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'alatbayar_id') {
            return $query->orderBy('alatbayar.namaalatbayar', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'bank_id') {
            return $query->orderBy('bank.namabank', $this->params['sortOrder']);
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
                            if ($filters['field'] == 'statusapproval') {
                                $query = $query->where('statusapproval.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statuscetak') {
                                $query = $query->where('statuscetak.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'pelanggan_id') {
                                $query = $query->where('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'alatbayar_id') {
                                $query = $query->where('alatbayar.namaalatbayar', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'bank_id') {
                                $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti_pengeluaran') {
                                $query = $query->whereRaw("format(" . $this->table . ".tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'nobukti_pengeluaran') {
                                $query = $query->where('pengeluaranheader.nobukti', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'keterangan_detail') {
                                $query = $query->where('pengeluarandetail.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'nominal_detail') {
                                $query = $query->whereRaw("format(c.nominal, '#,#0.00') LIKE '%$filters[data]%'");
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
                                if ($filters['field'] == 'statusapproval') {
                                    $query->orWhere('statusapproval.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statuscetak') {
                                    $query->orWhere('statuscetak.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'pelanggan_id') {
                                    $query->orWhere('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'alatbayar_id') {
                                    $query->orWhere('alatbayar.namaalatbayar', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'bank_id') {
                                    $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglbukti_pengeluaran') {
                                    $query = $query->orWhereRaw("format(" . $this->table . ".tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'nobukti_pengeluaran') {
                                    $query = $query->orWhere('pengeluaranheader.nobukti', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'keterangan_detail') {
                                    $query = $query->orWhere('pengeluarandetail.keterangan', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'nominal_detail') {
                                    $query = $query->orWhereRaw("format(c.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                                } else {
                                    // $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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

        if (request()->approve && request()->periode) {
            $query->where('pengeluaranheader.statusapproval', request()->approve)
                ->whereYear('pengeluaranheader.tglbukti', '=', request()->year)
                ->whereMonth('pengeluaranheader.tglbukti', '=', request()->month);
            return $query;
        }
        if (request()->cetak && request()->periode) {
            $query->where('pengeluaranheader.statuscetak', '<>', request()->cetak)
                ->whereYear('pengeluaranheader.tglbukti', '=', request()->year)
                ->whereMonth('pengeluaranheader.tglbukti', '=', request()->month);
            return $query;
        }

        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function getRekapPengeluaranHeader($bank, $tglbukti)
    {
        $this->setRequestParameters();

        $temp = '##tempDetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $pengeluaranDetail = DB::table("pengeluarandetail")->from(DB::raw("pengeluarandetail with (readuncommitted)"))
        ->select(
            'nobukti',
            'keterangan',
            DB::raw('SUM(nominal) AS nominal')
        )
        ->groupBy('nobukti', 'keterangan');
        Schema::create($temp, function ($table) {
            $table->string('nobukti')->nullable();
            $table->string('keterangan')->nullable();
            $table->bigInteger('nominal')->nullable();
        });
        DB::table($temp)->insertUsing(['nobukti', 'keterangan', 'nominal'], $pengeluaranDetail);

        $query = DB::table($this->table)->from(DB::raw("pengeluaranheader with (readuncommitted)"))
            ->select(
                'pengeluaranheader.id',
                'pengeluaranheader.nobukti as nobukti_pengeluaran',
                'pengeluaranheader.tglbukti as tglbukti_pengeluaran',
                'c.keterangan as keterangan_detail',
                'c.nominal as nominal_detail',
            )
            ->where('pengeluaranheader.bank_id', $bank)
            ->where('pengeluaranheader.tglbukti', $tglbukti)
            ->whereRaw(" NOT EXISTS (
                SELECT pengeluaran_nobukti
                FROM rekappengeluarandetail
                WHERE pengeluaran_nobukti = pengeluaranheader.nobukti   
              )")
            ->leftJoin(DB::raw("$temp as c with (readuncommitted)"), 'pengeluaranheader.nobukti', 'c.nobukti')
            ->groupBy('pengeluaranheader.nobukti', 'pengeluaranheader.id', 'pengeluaranheader.tglbukti', 'c.keterangan', 'c.nominal');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);
        $data = $query->get();

        return $data;
    }

    public function cekvalidasiaksi($nobukti)
    {
        $rekap = DB::table('rekappengeluarandetail')
            ->from(
                DB::raw("rekappengeluarandetail as a with (readuncommitted)")
            )
            ->select(
                'a.pengeluaran_nobukti'
            )
            ->where('a.pengeluaran_nobukti', '=', $nobukti)
            ->first();
        if (isset($rekap)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Rekap Pengeluaran',
                'kodeerror' => 'SATL'
            ];
            goto selesai;
        }
        $hutangBayar = DB::table('hutangbayarheader')
            ->from(
                DB::raw("hutangbayarheader as a with (readuncommitted)")
            )
            ->select(
                'a.pengeluaran_nobukti'
            )
            ->where('a.pengeluaran_nobukti', '=', $nobukti)
            ->first();
        if (isset($hutangBayar)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pembayaran Hutang',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }

        $kasGantung = DB::table('kasgantungheader')
            ->from(
                DB::raw("kasgantungheader as a with (readuncommitted)")
            )
            ->select(
                'a.pengeluaran_nobukti'
            )
            ->where('a.pengeluaran_nobukti', '=', $nobukti)
            ->first();
        if (isset($kasGantung)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'kas gantung',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }
        $absensiApproval = DB::table('absensisupirapprovalheader')
            ->from(
                DB::raw("absensisupirapprovalheader as a with (readuncommitted)")
            )
            ->select(
                'a.pengeluaran_nobukti'
            )
            ->where('a.pengeluaran_nobukti', '=', $nobukti)
            ->first();
        if (isset($absensiApproval)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Absensi Supir posting',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }

        $prosesUangjalan = DB::table('prosesuangjalansupirdetail')
            ->from(
                DB::raw("prosesuangjalansupirdetail as a with (readuncommitted)")
            )
            ->select(
                'a.pengeluarantrucking_nobukti'
            )
            ->where('a.pengeluarantrucking_nobukti', '=', $nobukti)
            ->first();
        if (isset($prosesUangjalan)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'proses uang jalan supir',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }

        $hutangbayarheader = DB::table('hutangbayarheader')
            ->from(
                DB::raw("hutangbayarheader as a with (readuncommitted)")
            )
            ->select(
                'a.pengeluaran_nobukti'
            )
            ->where('a.pengeluaran_nobukti', '=', $nobukti)
            ->first();
        if (isset($hutangbayarheader)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Hutang bayar header',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }

        $pengeluaranTrucking = DB::table('pengeluarantruckingheader')
            ->from(
                DB::raw("pengeluarantruckingheader as a with (readuncommitted)")
            )
            ->select(
                'a.pengeluaran_nobukti'
            )
            ->where('a.pengeluaran_nobukti', '=', $nobukti)
            ->first();
        if (isset($pengeluaranTrucking)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'pengeluaran trucking',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }

        $pengembalianKasbank = DB::table('pengembaliankasbankheader')
            ->from(
                DB::raw("pengembaliankasbankheader as a with (readuncommitted)")
            )
            ->select(
                'a.pengeluaran_nobukti'
            )
            ->where('a.pengeluaran_nobukti', '=', $nobukti)
            ->first();
        if (isset($pengembalianKasbank)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'pengembalian kas/bank',
                'kodeerror' => 'TDT'
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

    public function default()
    {

        $bankId = request()->bank_id;


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
            ->where('id', '=', $bankId)
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

    public function processStore(array $data): PengeluaranHeader
    {
        $bankid = $data['bank_id'];
        $querysubgrppengeluaran = Bank::from(DB::raw("bank with (readuncommitted)"))
            ->select('parameter.grp', 'parameter.subgrp', 'bank.formatpengeluaran', 'bank.coa', 'bank.tipe')
            ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpengeluaran', 'parameter.id')
            ->whereRaw("bank.id = $bankid")
            ->first();
        /*STORE HEADER*/
        $group = $querysubgrppengeluaran->grp;
        $subGroup = $querysubgrppengeluaran->subgrp;
        $format = DB::table('parameter')->where('grp', $group)->where('subgrp', $subGroup)->first();

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $pengeluaranHeader = new PengeluaranHeader();

        $pengeluaranHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $pengeluaranHeader->pelanggan_id = $data['pelanggan_id'] ?? 0;
        $pengeluaranHeader->postingdari = $data['postingdari'] ?? 'ENTRY PENGELUARAN KAS/BANK';
        $pengeluaranHeader->statusapproval = $statusApproval->id ?? $data['statusapproval'];
        $pengeluaranHeader->dibayarke = $data['dibayarke'] ?? '';
        $pengeluaranHeader->alatbayar_id = $data['alatbayar_id'] ?? 0;
        $pengeluaranHeader->bank_id = $data['bank_id'] ?? 0;
        $pengeluaranHeader->userapproval = $data['userapproval'] ?? '';
        $pengeluaranHeader->tglapproval = $data['tglapproval'] ?? '';
        $pengeluaranHeader->transferkeac = $data['transferkeac'] ?? '';
        $pengeluaranHeader->transferkean = $data['transferkean'] ?? '';
        $pengeluaranHeader->transferkebank = $data['transferkebank'] ?? '';
        $pengeluaranHeader->statusformat = $data['statusformat'] ?? $querysubgrppengeluaran->formatpengeluaran;
        $pengeluaranHeader->statuscetak = $statusCetak->id;
        $pengeluaranHeader->userbukacetak = '';
        $pengeluaranHeader->tglbukacetak = '';
        $pengeluaranHeader->modifiedby = auth('api')->user()->name;
        $pengeluaranHeader->nobukti = (new RunningNumberService)->get($group, $subGroup, $pengeluaranHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$pengeluaranHeader->save()) {
            throw new \Exception("Error storing Hutang header.");
        }

        $pengeluaranHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($pengeluaranHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY PENGELUARAN HEADER'),
            'idtrans' => $pengeluaranHeader->id,
            'nobuktitrans' => $pengeluaranHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $pengeluaranHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        $pengeluaranDetails = [];
        $coadebet_detail = [];
        $coakredit_detail = [];
        $nominal_detail = [];
        $keterangan_detail = [];
        for ($i = 0; $i < count($data['nominal_detail']); $i++) {
            $pengeluaranDetail = (new PengeluaranDetail())->processStore($pengeluaranHeader, [
                'pengeluaran_id' => $pengeluaranHeader->id,
                'nobukti' => $pengeluaranHeader->nobukti,
                'nowarkat' =>  $data['nowarkat'][$i],
                'tgljatuhtempo' =>  date('Y-m-d', strtotime($data['tgljatuhtempo'][$i])),
                'nominal' => $data['nominal_detail'][$i],
                'coadebet' =>  $data['coadebet'][$i],
                'coakredit' => (array_key_exists("coakredit", $data)) ? $data['coakredit'][$i] : $querysubgrppengeluaran->coa,
                'keterangan' => $data['keterangan_detail'][$i],
                'bulanbeban' =>  date('Y-m-d', strtotime($data['bulanbeban'][$i] ?? '1900/1/1')),
                'modifiedby' => auth('api')->user()->name,
            ]);
            $pengeluaranDetails[] = $pengeluaranDetail->toArray();
            $coadebet_detail[] =  $data['coadebet'][$i];
            $coakredit_detail[] = (array_key_exists("coakredit", $data)) ? $data['coakredit'][$i] : $querysubgrppengeluaran->coa;
            $nominal_detail[] = $data['nominal_detail'][$i];
            $keterangan_detail[] = $data['keterangan_detail'][$i];
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($pengeluaranDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY PENGELUARAN DETAIL'),
            'idtrans' =>  $pengeluaranHeaderLogTrail->id,
            'nobuktitrans' => $pengeluaranHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $pengeluaranDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);

        /*STORE JURNAL*/
        $jurnalRequest = [
            'tanpaprosesnobukti' => 1,
            'nobukti' => $pengeluaranHeader->nobukti,
            'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
            'postingdari' => "ENTRY PENGELUARAN",
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

        $jurnalUmumHeader = (new JurnalUmumHeader())->processStore($jurnalRequest);
        return $pengeluaranHeader;
    }

    public function processUpdate(PengeluaranHeader $pengeluaranHeader, array $data): PengeluaranHeader
    {
        $bankid = $data['bank_id'];
        $querysubgrppengeluaran = Bank::from(DB::raw("bank with (readuncommitted)"))
            ->select('parameter.grp', 'parameter.subgrp', 'bank.formatpengeluaran', 'bank.coa', 'bank.tipe')
            ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpengeluaran', 'parameter.id')
            ->whereRaw("bank.id = $bankid")
            ->first();
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $pengeluaranHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $pengeluaranHeader->pelanggan_id = $data['pelanggan_id'] ?? 0;
        $pengeluaranHeader->postingdari = $data['postingdari'] ?? 'ENTRY PENGELUARAN KAS/BANK';
        $pengeluaranHeader->statusapproval = $statusApproval->id ?? $data['statusapproval'];
        $pengeluaranHeader->dibayarke = $data['dibayarke'] ?? '';
        $pengeluaranHeader->alatbayar_id = $data['alatbayar_id'] ?? 0;
        $pengeluaranHeader->bank_id = $data['bank_id'] ?? 0;
        $pengeluaranHeader->userapproval = $data['userapproval'] ?? '';
        $pengeluaranHeader->tglapproval = $data['tglapproval'] ?? '';
        $pengeluaranHeader->transferkeac = $data['transferkeac'] ?? '';
        $pengeluaranHeader->transferkean = $data['transferkean'] ?? '';
        $pengeluaranHeader->transferkebank = $data['transferkebank'] ?? '';
        $pengeluaranHeader->statusformat = $data['statusformat'] ?? $querysubgrppengeluaran->formatpengeluaran;
        $pengeluaranHeader->statuscetak = $statusCetak->id;
        $pengeluaranHeader->userbukacetak = '';
        $pengeluaranHeader->tglbukacetak = '';
        $pengeluaranHeader->modifiedby = auth('api')->user()->name;

        if (!$pengeluaranHeader->save()) {
            throw new \Exception("Error Update Pengeluaran header.");
        }

        $pengeluaranHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($pengeluaranHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('edit PENGELUARAN HEADER'),
            'idtrans' => $pengeluaranHeader->id,
            'nobuktitrans' => $pengeluaranHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $pengeluaranHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        /*DELETE EXISTING JURNAL*/
        $JurnalUmumDetail = JurnalUmumDetail::where('nobukti', $pengeluaranHeader->nobukti)->lockForUpdate()->delete();
        $JurnalUmumHeader = JurnalUmumHeader::where('nobukti', $pengeluaranHeader->nobukti)->lockForUpdate()->delete();
        /*DELETE EXISTING Pengeluaran*/
        $pengeluaranDetail = PengeluaranDetail::where('pengeluaran_id', $pengeluaranHeader->id)->lockForUpdate()->delete();

        $pengeluaranDetails = [];
        $coadebet_detail = [];
        $coakredit_detail = [];
        $nominal_detail = [];
        $keterangan_detail = [];
        for ($i = 0; $i < count($data['nominal_detail']); $i++) {
            $pengeluaranDetail = (new PengeluaranDetail())->processStore($pengeluaranHeader, [
                'pengeluaran_id' => $pengeluaranHeader->id,
                'nobukti' => $pengeluaranHeader->nobukti,
                'nowarkat' =>  $data['nowarkat'][$i],
                'tgljatuhtempo' =>  date('Y-m-d', strtotime($data['tgljatuhtempo'][$i])),
                'nominal' => $data['nominal_detail'][$i],
                'coadebet' =>  $data['coadebet'][$i],
                'coakredit' => (array_key_exists("coakredit", $data)) ? $data['coakredit'][$i] : $querysubgrppengeluaran->coa,
                'keterangan' => $data['keterangan_detail'][$i],
                'bulanbeban' =>  date('Y-m-d', strtotime($data['bulanbeban'][$i] ?? '1900/1/1')),
                'modifiedby' => auth('api')->user()->name,
            ]);
            $pengeluaranDetails[] = $pengeluaranDetail->toArray();
            $coadebet_detail[] =  $data['coadebet'][$i];
            $coakredit_detail[] = (array_key_exists("coakredit", $data)) ? $data['coakredit'][$i] : $querysubgrppengeluaran->coa;
            $nominal_detail[] = $data['nominal_detail'][$i];
            $keterangan_detail[] = $data['keterangan_detail'][$i];
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($pengeluaranDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? strtoupper('ENTRY PENGELUARAN DETAIL'),
            'idtrans' =>  $pengeluaranHeaderLogTrail->id,
            'nobuktitrans' => $pengeluaranHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $pengeluaranDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);

        /*STORE JURNAL*/
        $jurnalRequest = [
            'tanpaprosesnobukti' => 1,
            'nobukti' => $pengeluaranHeader->nobukti,
            'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
            'postingdari' => "ENTRY PENGELUARAN",
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

        $jurnalUmumHeader = (new JurnalUmumHeader())->processStore($jurnalRequest);
        return $pengeluaranHeader;
    }

    public function processDestroy($id, $postingDari = ''): PengeluaranHeader
    {
        $pengeluaranHeader = PengeluaranHeader::findOrFail($id);
        $dataHeader =  $pengeluaranHeader->toArray();
        $pengeluaranDetail = PengeluaranDetail::where('pengeluaran_id', '=', $pengeluaranHeader->id)->get();
        $dataDetail = $pengeluaranDetail->toArray();

        /*DELETE EXISTING JURNAL*/
        $JurnalUmumDetail = JurnalUmumDetail::where('nobukti', $pengeluaranHeader->nobukti)->lockForUpdate()->delete();
        $JurnalUmumHeader = JurnalUmumHeader::where('nobukti', $pengeluaranHeader->nobukti)->lockForUpdate()->delete();
        /*DELETE EXISTING HUTANG*/
        $pengeluaranDetail = PengeluaranDetail::where('pengeluaran_id', $pengeluaranHeader->id)->lockForUpdate()->delete();

        $pengeluaranHeader = $pengeluaranHeader->lockAndDestroy($id);
        $hutangLogTrail = (new LogTrail())->processStore([
            'namatabel' => $this->table,
            'postingdari' => strtoupper('DELETE pengeluaran Header'),
            'idtrans' => $pengeluaranHeader->id,
            'nobuktitrans' => $pengeluaranHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $dataHeader,
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'PENGELUARANDETAIL',
            'postingdari' => strtoupper('DELETE pengeluaran detail'),
            'idtrans' => $hutangLogTrail['id'],
            'nobuktitrans' => $pengeluaranHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $dataDetail,
            'modifiedby' => auth('api')->user()->name
        ]);

        return $pengeluaranHeader;
    }
}
