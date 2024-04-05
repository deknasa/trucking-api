<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\RunningNumberService;
use Illuminate\Database\Schema\Blueprint;

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

        $user_id = auth('api')->user()->id ?? 0;
        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'PengeluaranHeaderController';

        if ($proses == 'reload') {
            $temtabel = 'temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;

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
                $table->integer('id')->nullable();
                $table->string('nobukti', 50)->nullable();
                $table->date('tglbukti')->nullable();
                $table->string('pelanggan_id', 1000)->nullable();
                $table->string('postingdari', 1000)->nullable();
                $table->string('dibayarke', 1000)->nullable();
                $table->string('alatbayar_id', 50)->nullable();
                $table->string('bank', 50)->nullable();
                $table->longtext('penerima')->nullable();
                $table->longtext('statusapproval')->nullable();
                $table->string('statusapprovaltext', 200)->nullable();
                $table->date('tglapproval')->nullable();
                $table->string('userapproval', 200)->nullable();
                $table->string('transferkeac', 200)->nullable();
                $table->string('transferkean', 200)->nullable();
                $table->string('transferkebank', 200)->nullable();
                $table->date('tglbukacetak')->nullable();
                $table->longtext('statuscetak')->nullable();
                $table->string('statuscetaktext', 200)->nullable();
                $table->string('userbukacetak', 200)->nullable();
                $table->date('tglkirimberkas')->nullable();
                $table->longtext('statuskirimberkas')->nullable();
                $table->string('statuskirimberkastext', 200)->nullable();
                $table->string('userkirimberkas', 200)->nullable();
                $table->string('penerimaan_nobukti', 200)->nullable();
                $table->integer('jumlahcetak')->nullable();
                $table->string('modifiedby', 200)->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
            });
            $tempPenerima = '##tempPenerima' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempPenerima, function ($table) {
                $table->string('nobukti')->nullable();
                $table->string('penerima')->nullable();
            });
            $getPenerima = DB::table("pengeluaranpenerima")->from(DB::raw("pengeluaranpenerima"))
                ->select(DB::raw("pengeluaranpenerima.nobukti, STRING_AGG(penerima.namapenerima, ', ') AS penerima"))
                ->leftJoin(DB::raw("penerima with (readuncommitted)"), 'pengeluaranpenerima.penerima_id', 'penerima.id')
                ->groupBy("pengeluaranpenerima.nobukti");

            DB::table($tempPenerima)->insertUsing(['nobukti', 'penerima'], $getPenerima);

            $query = DB::table($this->table)->from(DB::raw("pengeluaranheader with (readuncommitted)"))
                ->select(
                    'pengeluaranheader.id',
                    'pengeluaranheader.nobukti',
                    'pengeluaranheader.tglbukti',
                    'pelanggan.namapelanggan as pelanggan_id',
                    'pengeluaranheader.postingdari',
                    'pengeluaranheader.dibayarke',
                    'alatbayar.namaalatbayar as alatbayar_id',
                    'bank.namabank as bank',
                    'penerima.penerima',
                    'statusapproval.memo as statusapproval',
                    'statusapproval.text as statusapprovaltext',
                    DB::raw('(case when (year(pengeluaranheader.tglapproval) <= 2000) then null else pengeluaranheader.tglapproval end ) as tglapproval'),
                    'pengeluaranheader.userapproval',
                    'pengeluaranheader.transferkeac',
                    'pengeluaranheader.transferkean',
                    'pengeluaranheader.transferkebank',
                    DB::raw('(case when (year(pengeluaranheader.tglbukacetak) <= 2000) then null else pengeluaranheader.tglbukacetak end ) as tglbukacetak'),
                    'statuscetak.memo as statuscetak',
                    'statuscetak.text as statuscetaktext',
                    'pengeluaranheader.userbukacetak',
                    DB::raw('(case when (year(pengeluaranheader.tglkirimberkas) <= 2000) then null else pengeluaranheader.tglkirimberkas end ) as tglkirimberkas'),
                    'statuskirimberkas.memo as statuskirimberkas',
                    'statuskirimberkas.text as statuskirimberkastext',
                    'pengeluaranheader.userkirimberkas',
                    'pengeluaranheader.penerimaan_nobukti',
                    'pengeluaranheader.jumlahcetak',
                    'pengeluaranheader.modifiedby',
                    'pengeluaranheader.created_at',
                    'pengeluaranheader.updated_at'

                )

                ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'pengeluaranheader.pelanggan_id', 'pelanggan.id')
                ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pengeluaranheader.alatbayar_id', 'alatbayar.id')
                ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluaranheader.bank_id', 'bank.id')
                ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'pengeluaranheader.statusapproval', 'statusapproval.id')
                ->leftJoin(DB::raw("$tempPenerima as penerima with (readuncommitted)"), 'pengeluaranheader.nobukti', 'penerima.nobukti')
                ->leftJoin(DB::raw("parameter as statuskirimberkas with (readuncommitted)"), 'pengeluaranheader.statuskirimberkas', 'statuskirimberkas.id')
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
            DB::table($temtabel)->insertUsing([
                'id',
                'nobukti',
                'tglbukti',
                'pelanggan_id',
                'postingdari',
                'dibayarke',
                'alatbayar_id',
                'bank',
                'penerima',
                'statusapproval',
                'statusapprovaltext',
                'tglapproval',
                'userapproval',
                'transferkeac',
                'transferkean',
                'transferkebank',
                'tglbukacetak',
                'statuscetak',
                'statuscetaktext',
                'userbukacetak',
                'tglkirimberkas',
                'statuskirimberkas',
                'statuskirimberkastext',
                'userkirimberkas',
                'penerimaan_nobukti',
                'jumlahcetak',
                'modifiedby',
                'created_at',
                'updated_at',
            ], $query);
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

            // dd($querydata);
            $temtabel = $querydata->namatabel;
        }

        $query = DB::table($temtabel)->from(DB::raw($temtabel . " a "))
            ->select(
                'a.id',
                'a.nobukti',
                'a.tglbukti',
                'a.pelanggan_id',
                'a.postingdari',
                'a.dibayarke',
                'a.alatbayar_id',
                'a.bank',
                'a.penerima',
                'a.statusapproval',
                'a.statusapprovaltext',
                'a.tglapproval',
                'a.userapproval',
                'a.userbukacetak',
                'a.transferkeac',
                'a.transferkean',
                'a.transferkebank',
                'a.tglbukacetak',
                'a.statuscetak',
                'a.statuscetaktext',
                'a.userbukacetak',
                'tglkirimberkas',
                'statuskirimberkas',
                'statuskirimberkastext',
                'userkirimberkas',
                'a.penerimaan_nobukti',
                'a.jumlahcetak',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
            );
            // dd($query->get());
        // dd(request()->limit);
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
                'pengeluaranheader.penerimaan_nobukti as nobukti_penerimaan',
                'pengeluaranheader.statuscetak',
                'pengeluaranheader.userbukacetak',
                'pengeluaranheader.jumlahcetak',
                'pengeluaranheader.tglbukacetak',
                'pengeluaranheader.statuskirimberkas',
                'pengeluaranheader.userkirimberkas',
                'pengeluaranheader.tglkirimberkas',
            )
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'pengeluaranheader.pelanggan_id', 'pelanggan.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pengeluaranheader.alatbayar_id', 'alatbayar.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluaranheader.bank_id', 'bank.id')
            ->where('pengeluaranheader.id', $id);

        $data = $query->first();

        return $data;
    }

    public function selectColumns()
    {
        $temp = '##tempselect' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('pelanggan_id', 1000)->nullable();
            $table->string('postingdari', 1000)->nullable();
            $table->string('dibayarke', 1000)->nullable();
            $table->string('alatbayar_id', 50)->nullable();
            $table->string('bank', 50)->nullable();
            $table->integer('bank_id')->nullable();
            $table->longtext('penerima')->nullable();
            $table->longtext('statusapproval')->nullable();
            $table->string('statusapprovaltext', 200)->nullable();
            $table->date('tglapproval')->nullable();
            $table->string('userapproval', 200)->nullable();
            $table->string('transferkeac', 200)->nullable();
            $table->string('transferkean', 200)->nullable();
            $table->string('transferkebank', 200)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->longtext('statuscetak')->nullable();
            $table->string('statuscetaktext', 200)->nullable();
            $table->string('userbukacetak', 200)->nullable();
            $table->date('tglkirimberkas')->nullable();
            $table->longtext('statuskirimberkas')->nullable();
            $table->string('statuskirimberkastext', 200)->nullable();
            $table->string('userkirimberkas', 200)->nullable();
            $table->string('penerimaan_nobukti', 200)->nullable();
            $table->integer('jumlahcetak')->nullable();
            $table->string('modifiedby', 200)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
        $tempPenerima = '##tempPenerima' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempPenerima, function ($table) {
            $table->string('nobukti')->nullable();
            $table->string('penerima')->nullable();
        });
        $getPenerima = DB::table("pengeluaranpenerima")->from(DB::raw("pengeluaranpenerima"))
            ->select(DB::raw("pengeluaranpenerima.nobukti, STRING_AGG(penerima.namapenerima, ', ') AS penerima"))
            ->leftJoin(DB::raw("penerima with (readuncommitted)"), 'pengeluaranpenerima.penerima_id', 'penerima.id')
            ->groupBy("pengeluaranpenerima.nobukti");

        DB::table($tempPenerima)->insertUsing(['nobukti', 'penerima'], $getPenerima);


        $query = DB::table($this->table)->from(DB::raw("pengeluaranheader with (readuncommitted)"))
            ->select(
                'pengeluaranheader.id',
                'pengeluaranheader.nobukti',
                'pengeluaranheader.tglbukti',
                'pelanggan.namapelanggan as pelanggan_id',
                'pengeluaranheader.postingdari',
                'pengeluaranheader.dibayarke',
                'alatbayar.namaalatbayar as alatbayar_id',
                'bank.namabank as bank',
                'pengeluaranheader.bank_id',
                'penerima.penerima',
                'statusapproval.memo as statusapproval',
                'statusapproval.text as statusapprovaltext',
                DB::raw('(case when (year(pengeluaranheader.tglapproval) <= 2000) then null else pengeluaranheader.tglapproval end ) as tglapproval'),
                'pengeluaranheader.userapproval',
                'pengeluaranheader.transferkeac',
                'pengeluaranheader.transferkean',
                'pengeluaranheader.transferkebank',
                DB::raw('(case when (year(pengeluaranheader.tglbukacetak) <= 2000) then null else pengeluaranheader.tglbukacetak end ) as tglbukacetak'),
                'statuscetak.memo as statuscetak',
                'statuscetak.text as statuscetaktext',
                'pengeluaranheader.userbukacetak',
                DB::raw('(case when (year(pengeluaranheader.tglkirimberkas) <= 2000) then null else pengeluaranheader.tglkirimberkas end ) as tglkirimberkas'),
                'statuskirimberkas.memo as statuskirimberkas',
                'statuskirimberkas.text as statuskirimberkastext',
                'pengeluaranheader.userkirimberkas',
                'pengeluaranheader.penerimaan_nobukti',
                'pengeluaranheader.jumlahcetak',
                'pengeluaranheader.modifiedby',
                'pengeluaranheader.created_at',
                'pengeluaranheader.updated_at'

            )

            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'pengeluaranheader.pelanggan_id', 'pelanggan.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pengeluaranheader.alatbayar_id', 'alatbayar.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluaranheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'pengeluaranheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("$tempPenerima as penerima with (readuncommitted)"), 'pengeluaranheader.nobukti', 'penerima.nobukti')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'pengeluaranheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("parameter as statuskirimberkas with (readuncommitted)"), 'pengeluaranheader.statuskirimberkas', 'statuskirimberkas.id');
        DB::table($temp)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'pelanggan_id',
            'postingdari',
            'dibayarke',
            'alatbayar_id',
            'bank',
            'bank_id',
            'penerima',
            'statusapproval',
            'statusapprovaltext',
            'tglapproval',
            'userapproval',
            'transferkeac',
            'transferkean',
            'transferkebank',
            'tglbukacetak',
            'statuscetak',
            'statuscetaktext',
            'userbukacetak',
            'tglkirimberkas',
            'statuskirimberkas',
            'statuskirimberkastext',
            'userkirimberkas',
            'penerimaan_nobukti',
            'jumlahcetak',
            'modifiedby',
            'created_at',
            'updated_at',
        ], $query);

        $query = DB::table($temp)->from(DB::raw($temp . " a "))
            ->select(
                'a.id',
                'a.nobukti',
                'a.tglbukti',
                'a.pelanggan_id',
                'a.postingdari',
                'a.dibayarke',
                'a.alatbayar_id',
                'a.bank',
                'a.bank_id',
                'a.penerima',
                'a.statusapproval',
                'a.statusapprovaltext',
                'a.tglapproval',
                'a.userapproval',
                'a.transferkeac',
                'a.transferkean',
                'a.transferkebank',
                'a.tglbukacetak',
                'a.statuscetak',
                'a.statuscetaktext',
                'a.userbukacetak',
                'a.tglkirimberkas',
                'a.statuskirimberkas',
                'a.statuskirimberkastext',
                'a.userkirimberkas',
                'a.penerimaan_nobukti',
                'a.jumlahcetak',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
            );

        return $query;
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->integer('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('pelanggan_id', 1000)->nullable();
            $table->string('postingdari', 1000)->nullable();
            $table->string('dibayarke', 1000)->nullable();
            $table->string('alatbayar_id', 50)->nullable();
            $table->string('bank', 50)->nullable();
            $table->integer('bank_id')->nullable();
            $table->longtext('penerima')->nullable();
            $table->longtext('statusapproval')->nullable();
            $table->string('statusapprovaltext', 200)->nullable();
            $table->date('tglapproval')->nullable();
            $table->string('userapproval', 200)->nullable();
            $table->string('transferkeac', 200)->nullable();
            $table->string('transferkean', 200)->nullable();
            $table->string('transferkebank', 200)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->longtext('statuscetak')->nullable();
            $table->string('statuscetaktext', 200)->nullable();
            $table->string('userbukacetak', 200)->nullable();
            $table->date('tglkirimberkas')->nullable();
            $table->longtext('statuskirimberkas')->nullable();
            $table->string('statuskirimberkastext', 200)->nullable();
            $table->string('userkirimberkas', 200)->nullable();
            $table->string('penerimaan_nobukti', 200)->nullable();
            $table->integer('jumlahcetak')->nullable();
            $table->string('modifiedby', 200)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        if ((date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tgldariheader))) || (date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tglsampaiheader)))) {
            request()->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            request()->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
        }
        $this->setRequestParameters();
        $query = $this->selectColumns();
        $this->sort($query);
        $models = $this->filter($query);
        $models =  $query->whereBetween('a.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))])->where('a.bank_id', request()->bankheader);
        DB::table($temp)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'pelanggan_id',
            'postingdari',
            'dibayarke',
            'alatbayar_id',
            'bank',
            'bank_id',
            'penerima',
            'statusapproval',
            'statusapprovaltext',
            'tglapproval',
            'userapproval',
            'transferkeac',
            'transferkean',
            'transferkebank',
            'tglbukacetak',
            'statuscetak',
            'statuscetaktext',
            'userbukacetak',
            'tglkirimberkas',
            'statuskirimberkas',
            'statuskirimberkastext',
            'userkirimberkas',
            'penerimaan_nobukti',
            'jumlahcetak',
            'modifiedby',
            'created_at',
            'updated_at',
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
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statusapproval') {
                                $query = $query->where('a.statusapprovaltext', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statuscetak') {
                                $query = $query->where('a.statuscetaktext', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statuskirimberkas') {
                                $query = $query->where('a.statuskirimberkastext', '=', "$filters[data]");
                                // } else if ($filters['field'] == 'pelanggan_id') {
                                //     $query = $query->where('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                                // } else if ($filters['field'] == 'alatbayar_id') {
                                //     $query = $query->where('alatbayar.namaalatbayar', 'LIKE', "%$filters[data]%");
                                // } else if ($filters['field'] == 'bank_id') {
                                //     $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                                // } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak') {
                                //     $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                // } else if ($filters['field'] == 'tglbukti_pengeluaran') {
                                //     $query = $query->whereRaw("format(" . $this->table . ".tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                // } else if ($filters['field'] == 'nobukti_pengeluaran') {
                                //     $query = $query->where('pengeluaranheader.nobukti', 'LIKE', "%$filters[data]%");
                                // } else if ($filters['field'] == 'keterangan_detail') {
                                //     $query = $query->where('pengeluarandetail.keterangan', 'LIKE', "%$filters[data]%");
                                // } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                //     $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                // } else if ($filters['field'] == 'nominal_detail') {
                                //     $query = $query->whereRaw("format(c.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'statusapproval') {
                                    $query->orWhere('a.statusapprovaltext', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statuscetak') {
                                    $query->orWhere('a.statuscetaktext', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statuskirimberkas') {
                                    $query->orWhere('a.statuskirimberkastext', '=', "$filters[data]");
                                    // } else if ($filters['field'] == 'pelanggan_id') {
                                    //     $query->orWhere('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                                    // } else if ($filters['field'] == 'alatbayar_id') {
                                    //     $query->orWhere('alatbayar.namaalatbayar', 'LIKE', "%$filters[data]%");
                                    // } else if ($filters['field'] == 'bank_id') {
                                    //     $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                                    // } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak') {
                                    //     $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                    // } else if ($filters['field'] == 'tglbukti_pengeluaran') {
                                    //     $query = $query->orWhereRaw("format(" . $this->table . ".tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                    // } else if ($filters['field'] == 'nobukti_pengeluaran') {
                                    //     $query = $query->orWhere('pengeluaranheader.nobukti', 'LIKE', "%$filters[data]%");
                                    // } else if ($filters['field'] == 'keterangan_detail') {
                                    //     $query = $query->orWhere('pengeluarandetail.keterangan', 'LIKE', "%$filters[data]%");
                                    // } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    //     $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                    // } else if ($filters['field'] == 'nominal_detail') {
                                    //     $query = $query->orWhereRaw("format(c.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                                } else {
                                    // $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $query = DB::table("pengeluaranheader")->from(DB::raw("pengeluaranheader"))
            ->select(
                'pengeluaranheader.nobukti as nobukti_pengeluaran',
                'pengeluaranheader.tglbukti as tglbukti_pengeluaran',
                DB::raw("SUM(pengeluarandetail.nominal) as nominal_detail")

            )
            ->leftJoin(DB::raw("pengeluarandetail with (readuncommitted)"), 'pengeluaranheader.nobukti', 'pengeluarandetail.nobukti')
            ->where('pengeluaranheader.bank_id', $bank)
            ->where('pengeluaranheader.tglbukti', $tglbukti)
            ->whereRaw("pengeluaranheader.nobukti not in (select pengeluaran_nobukti from rekappengeluarandetail)")
            ->groupBy('pengeluaranheader.nobukti')
            ->groupBy('pengeluaranheader.tglbukti');

        Schema::create($temp, function ($table) {
            $table->string('nobukti_pengeluaran')->nullable();
            $table->date('tglbukti_pengeluaran')->nullable();
            $table->double('nominal_detail', 15, 2)->nullable();
        });

        DB::table($temp)->insertUsing(['nobukti_pengeluaran', 'tglbukti_pengeluaran', 'nominal_detail'], $query);

        $dataTemp =  DB::table("$temp")->from(DB::raw("$temp"))
            ->select(
                $temp . '.nobukti_pengeluaran',
                $temp . '.tglbukti_pengeluaran',
                $temp . '.nominal_detail',

            );

        $this->filterRekap($dataTemp, $temp);
        $this->totalNominal = $dataTemp->sum($temp . '.nominal_detail');
        $this->totalRows = $dataTemp->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $dataTemp->orderBy($temp . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        $dataTemp->skip($this->params['offset'])->take($this->params['limit']);

        $data = $dataTemp->get();

        return $data;
    }

    public function filterRekap($dataTemp, $temp, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'tglbukti_pengeluaran') {
                                $dataTemp = $dataTemp->whereRaw("format(" . $temp . ".tglbukti_pengeluaran, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'nominal_detail') {
                                $dataTemp = $dataTemp->whereRaw("format($temp.nominal_detail, '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                // $dataTemp = $dataTemp->where($temp . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $dataTemp = $dataTemp->whereRaw($temp . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    $dataTemp = $dataTemp->where(function ($dataTemp, $temp) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'tglbukti_pengeluaran') {
                                    $dataTemp = $dataTemp->orWhereRaw("format(" . $temp . ".tglbukti_pengeluaran, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'nominal_detail') {
                                    $dataTemp = $dataTemp->orWhereRaw("format($temp.nominal_detail, '#,#0.00') LIKE '%$filters[data]%'");
                                } else {
                                    // $dataTemp->orWhere($temp . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $dataTemp = $dataTemp->OrwhereRaw($temp . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                                }
                            }
                        }
                    });

                    break;
                default:

                    break;
            }
        }

        return $dataTemp;
    }

    public function cekvalidasiaksi($nobukti)
    {
        $error = new Error();
        $keteranganerror = $error->cekKeteranganError('SAPP') ?? '';
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        // $keterangantambahanerror2 = $error->cekKeteranganError('BD');

        $jurnal = DB::table('pengeluaranheader')
            ->from(
                DB::raw("pengeluaranheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($jurnal)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $jurnal->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror,
                'kodeerror' => 'SAPP',
                'editcoa' => false
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('TDT');
        $pelunasanhutangheader = DB::table('pelunasanhutangheader')
            ->from(
                DB::raw("pelunasanhutangheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.pengeluaran_nobukti'
            )
            ->where('a.pengeluaran_nobukti', '=', $nobukti)
            ->first();
        if (isset($pelunasanhutangheader)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti pelunasan hutang <b>' . $pelunasanhutangheader->nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Pelunasan Hutang '. $pelunasanhutangheader->nobukti,
                'kodeerror' => 'TDT',
                'editcoa' => false
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('TDT');
        $kasGantung = DB::table('kasgantungheader')
            ->from(
                DB::raw("kasgantungheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.pengeluaran_nobukti'
            )
            ->where('a.pengeluaran_nobukti', '=', $nobukti)
            ->first();
        if (isset($kasGantung)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti kas gantung <b>' . $kasGantung->nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'kas gantung '. $kasGantung->nobukti,
                'kodeerror' => 'TDT',
                'editcoa' => false
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('TDT');
        $notaKredit = DB::table('notakreditheader')
            ->from(
                DB::raw("notakreditheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.pengeluaran_nobukti'
            )
            ->where('a.pengeluaran_nobukti', '=', $nobukti)
            ->first();
        if (isset($notaKredit)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti kas gantung <b>' . $notaKredit->nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'kas gantung '. $kasGantung->nobukti,
                'kodeerror' => 'TDT',
                'editcoa' => false
            ];
            goto selesai;
        }
        $keteranganerror = $error->cekKeteranganError('TDT');

        $absensiApproval = DB::table('absensisupirapprovalheader')
            ->from(
                DB::raw("absensisupirapprovalheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.pengeluaran_nobukti'
            )
            ->where('a.pengeluaran_nobukti', '=', $nobukti)
            ->first();
        if (isset($absensiApproval)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti absensi supir posting <b>' . $absensiApproval->nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Absensi Supir posting '. $absensiApproval->nobukti,
                'kodeerror' => 'TDT',
                'editcoa' => false
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('TDT');
        $prosesUangjalan = DB::table('prosesuangjalansupirdetail')
            ->from(
                DB::raw("prosesuangjalansupirdetail as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.pengeluarantrucking_nobukti'
            )
            ->where('a.pengeluarantrucking_nobukti', '=', $nobukti)
            ->first();
        if (isset($prosesUangjalan)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti proses uang jalan supir <b>' . $prosesUangjalan->nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'proses uang jalan supir '. $prosesUangjalan->nobukti,
                'kodeerror' => 'TDT',
                'editcoa' => false
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('TDT');
        $pelunasanHutangHeader = DB::table('pelunasanhutangheader')
            ->from(
                DB::raw("pelunasanhutangheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.pengeluaran_nobukti'
            )
            ->where('a.pengeluaran_nobukti', '=', $nobukti)
            ->first();
        if (isset($pelunasanHutangHeader)) {
            $data = [
                'kondisi' => true,
                // 'keterangan' => 'PELUNASAN HUTANG '. $pelunasanHutangHeader->nobukti,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti pelunasan hutang <b>' . $pelunasanHutangHeader->nobukti . '</b> <br> ' . $keterangantambahanerror,
                'kodeerror' => 'TDT',
                'editcoa' => false
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('TDT');
        $pengeluaranTrucking = DB::table('pengeluarantruckingheader')
            ->from(
                DB::raw("pengeluarantruckingheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.pengeluaran_nobukti'
            )
            ->where('a.pengeluaran_nobukti', '=', $nobukti)
            ->first();
        if (isset($pengeluaranTrucking)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti pengeluaran trucking <b>' . $pengeluaranTrucking->nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'pengeluaran trucking '. $pengeluaranTrucking->nobukti,
                'kodeerror' => 'TDT',
                'editcoa' => false
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('TDT');
        $pengembalianKasbank = DB::table('pengembaliankasbankheader')
            ->from(
                DB::raw("pengembaliankasbankheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.pengeluaran_nobukti'
            )
            ->where('a.pengeluaran_nobukti', '=', $nobukti)
            ->first();
        if (isset($pengembalianKasbank)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti pengembalian kas/bank <b>' . $pengembalianKasbank->nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'pengembalian kas/bank '. $pengembalianKasbank->nobukti,
                'kodeerror' => 'TDT',
                'editcoa' => false
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('TDT');
        $prosesGajiSupir = DB::table('prosesgajisupirheader')
            ->from(
                DB::raw("prosesgajisupirheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.pengeluaran_nobukti'
            )
            ->where('a.pengeluaran_nobukti', '=', $nobukti)
            ->first();
        if (isset($prosesGajiSupir)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti Proses Gaji Supir <b>' . $prosesGajiSupir->nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Proses Gaji Supir '. $prosesGajiSupir->nobukti,
                'kodeerror' => 'TDT',
                'editcoa' => false
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('TDT');
        $pendapatanSupir = DB::table('pendapatansupirheader')
            ->from(
                DB::raw("pendapatansupirheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.pengeluaran_nobukti'
            )
            ->where('a.pengeluaran_nobukti', '=', $nobukti)
            ->first();
        if (isset($pendapatanSupir)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti Komisi Supir <b>' . $pendapatanSupir->nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Pendapatan Supir '. $pendapatanSupir->nobukti,
                'kodeerror' => 'TDT',
                'editcoa' => false
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('TDT');

        $pelunasanPiutang = DB::table('pelunasanpiutangheader')
            ->from(
                DB::raw("pelunasanpiutangheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.pengeluaran_nobukti'
            )
            ->where('a.pengeluaran_nobukti', '=', $nobukti)
            ->first();
        if (isset($pelunasanPiutang)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti Pelunasan Piutang <b>' . $pelunasanPiutang->nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Pelunasan Piutang ' . $pelunasanPiutang->nobukti,
                'kodeerror' => 'TDT',
                'editcoa' => false
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('TDT');
        $pemutihanSupir = DB::table('pemutihansupirheader')
            ->from(
                DB::raw("pemutihansupirheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.pengeluaran_nobukti'
            )
            ->where('a.pengeluaran_nobukti', '=', $nobukti)
            ->first();
        if (isset($pemutihanSupir)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti pemutihan supir <b>' . $pemutihanSupir->nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'kas gantung '. $pemutihanSupir->nobukti,
                'kodeerror' => 'TDT',
                'editcoa' => false
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('SCG');
        $pencairangiro = DB::table('pencairangiropengeluaranheader')
            ->from(
                DB::raw("pencairangiropengeluaranheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.pengeluaran_nobukti'
            )
            ->where('a.pengeluaran_nobukti', '=', $nobukti)
            ->first();
        if (isset($pencairangiro)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti pencairan giro <b>' . $pencairangiro->nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'kas gantung '. $kasGantung->nobukti,
                'kodeerror' => 'SCG',
                'editcoa' => false
            ];
            goto selesai;
        }
        $keteranganerror = $error->cekKeteranganError('TDT');

        $keteranganerror = $error->cekKeteranganError('SATL');
        $rekap = DB::table('rekappengeluarandetail')
            ->from(
                DB::raw("rekappengeluarandetail as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.pengeluaran_nobukti'
            )
            ->where('a.pengeluaran_nobukti', '=', $nobukti)
            ->first();
        if (isset($rekap)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti Rekap Pengeluaran <b>' . $rekap->nobukti . '</b> <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Rekap Pengeluaran '. $rekap->nobukti,
                'kodeerror' => 'SATL',
                'editcoa' => true
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
                'tipe'

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
            ->where('tipe', $bank->tipe)
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
        $manual = $data['manual'] ?? false;

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
        $statusKirimBerkas = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSKIRIMBERKAS')->where('text', 'BELUM KIRIM BERKAS')->first();
        $alatabayargiro = DB::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.text',
                'a.memo'
            )
            ->where('a.grp', 'ALAT BAYAR GIRO')
            ->where('a.subgrp', 'ALAT BAYAR GIRO')
            ->first();


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
        $pengeluaranHeader->penerimaan_nobukti = $data['penerimaan_nobukti'] ?? '';
        $pengeluaranHeader->statusformat = $data['statusformat'] ?? $querysubgrppengeluaran->formatpengeluaran;
        $pengeluaranHeader->statuscetak = $statusCetak->id;
        $pengeluaranHeader->userbukacetak = '';
        $pengeluaranHeader->tglbukacetak = '';
        $pengeluaranHeader->statuskirimberkas = $statusKirimBerkas->id;
        $pengeluaranHeader->userkirimberkas = '';
        $pengeluaranHeader->tglkirimberkas = '';
        $pengeluaranHeader->modifiedby = auth('api')->user()->name;
        $pengeluaranHeader->info = html_entity_decode(request()->info);
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

        $alatabayarid = $data['alatbayar_id'] ?? 0;
        $pengeluaranDetails = [];
        $coadebet_detail = [];
        $coakredit_detail = [];
        $nominal_detail = [];
        $keterangan_detail = [];
        for ($i = 0; $i < count($data['nominal_detail']); $i++) {
            if ($alatabayarid == $alatabayargiro->text) {
                $memo = json_decode($alatabayargiro->memo, true);
                $coakredit_detail[] = $memo['JURNAL'];
                $coaKredit = $memo['JURNAL'];
            } else {
                $coakredit_detail[] = $querysubgrppengeluaran->coa;
                $coaKredit = $querysubgrppengeluaran->coa;
            }

            $pengeluaranDetail = (new PengeluaranDetail())->processStore($pengeluaranHeader, [
                'pengeluaran_id' => $pengeluaranHeader->id,
                'nobukti' => $pengeluaranHeader->nobukti,
                'nowarkat' =>  $data['nowarkat'][$i],
                'tgljatuhtempo' =>  date('Y-m-d', strtotime($data['tgljatuhtempo'][$i])),
                'nominal' => $data['nominal_detail'][$i],
                'coadebet' =>  $data['coadebet'][$i],
                // 'coakredit' => ($data['coakredit']) ? $data['coakredit'][$i] : $querysubgrppengeluaran->coa,
                'coakredit' => $coaKredit,
                'keterangan' => $data['keterangan_detail'][$i],
                'noinvoice' => $data['noinvoice'][$i] ?? '',
                'bank' => $data['bank_detail'][$i] ?? '',
                'modifiedby' => auth('api')->user()->name,
            ]);

            $pengeluaranDetails[] = $pengeluaranDetail->toArray();
            $coadebet_detail[] =  $data['coadebet'][$i];
            // $coakredit_detail[] = ($data['coakredit']) ? $data['coakredit'][$i] : $querysubgrppengeluaran->coa;

            $nominal_detail[] = $data['nominal_detail'][$i];
            $keterangan_detail[] = $data['keterangan_detail'][$i];
        }


        if ($manual) {
            if ($data['penerima_id'] != '') {

                for ($i = 0; $i < count($data['penerima_id']); $i++) {

                    $pengeluaranPenerima = (new PengeluaranPenerima())->processStore([
                        'pengeluaran_id' => $pengeluaranHeader->id,
                        'nobukti' => $pengeluaranHeader->nobukti,
                        'penerima_id' =>  $data['penerima_id'][$i],
                    ]);
                }
            }
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
            'postingdari' => $data['postingdari'] ?? "ENTRY PENGELUARAN",
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
        $nobuktiOld = $pengeluaranHeader->nobukti;
        $bankid = $data['bank_id'];
        $manual = $data['manual'] ?? false;

        $from = $data['from'] ?? '';
        $querysubgrppengeluaran = Bank::from(DB::raw("bank with (readuncommitted)"))
            ->select('parameter.grp', 'parameter.subgrp', 'bank.formatpengeluaran', 'bank.coa', 'bank.tipe')
            ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpengeluaran', 'parameter.id')
            ->whereRaw("bank.id = $bankid")
            ->first();

        $group = $querysubgrppengeluaran->grp;
        $subGroup = $querysubgrppengeluaran->subgrp;

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();
        $statusKirimBerkas = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSKIRIMBERKAS')->where('text', 'BELUM KIRIM BERKAS')->first();
        $getTgl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'EDIT TANGGAL BUKTI')->where('subgrp', 'PENGELUARAN KAS/BANK')->first();

        if (trim($getTgl->text) == 'YA') {
            $querycek = DB::table('pengeluaranheader')->from(
                DB::raw("pengeluaranheader a with (readuncommitted)")
            )
                ->select(
                    'a.nobukti'
                )
                ->where('a.id', $pengeluaranHeader->id)
                ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
                ->first();


            if (isset($querycek)) {
                $nobukti = $querycek->nobukti;
            } else {
                $nobukti = (new RunningNumberService)->get($group, $subGroup, $pengeluaranHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
            }

            $pengeluaranHeader->nobukti = $nobukti;
            $pengeluaranHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        } else {
            if ($from == 'pelunasanhutang') {
                if ($data['bank_id'] != $pengeluaranHeader->bank_id) {

                    $nobukti = (new RunningNumberService)->get($group, $subGroup, $pengeluaranHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
                    $pengeluaranHeader->nobukti = $nobukti;
                    $pengeluaranHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
                }
            }
        }



        $pengeluaranHeader->pelanggan_id = $data['pelanggan_id'] ?? 0;
        $pengeluaranHeader->postingdari = $data['postingdari'] ?? 'ENTRY PENGELUARAN KAS/BANK';
        // $pengeluaranHeader->statusapproval = $statusApproval->id ?? $data['statusapproval'];
        $pengeluaranHeader->dibayarke = $data['dibayarke'] ?? '';
        $pengeluaranHeader->alatbayar_id = $data['alatbayar_id'] ?? 0;
        $pengeluaranHeader->bank_id = $data['bank_id'] ?? 0;
        // $pengeluaranHeader->userapproval = $data['userapproval'] ?? '';
        // $pengeluaranHeader->tglapproval = $data['tglapproval'] ?? '';
        $pengeluaranHeader->transferkeac = $data['transferkeac'] ?? '';
        $pengeluaranHeader->transferkean = $data['transferkean'] ?? '';
        $pengeluaranHeader->transferkebank = $data['transferkebank'] ?? '';
        $pengeluaranHeader->penerimaan_nobukti = $data['penerimaan_nobukti'] ?? '';
        $pengeluaranHeader->statusformat = $data['statusformat'] ?? $querysubgrppengeluaran->formatpengeluaran;
        $pengeluaranHeader->statuscetak = $statusCetak->id;
        $pengeluaranHeader->userbukacetak = '';
        $pengeluaranHeader->tglbukacetak = '';
        $pengeluaranHeader->editing_by = '';
        $pengeluaranHeader->editing_at = null;
        $pengeluaranHeader->modifiedby = auth('api')->user()->name;
        $pengeluaranHeader->info = html_entity_decode(request()->info);

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
        // $JurnalUmumDetail = JurnalUmumDetail::where('nobukti', $pengeluaranHeader->nobukti)->lockForUpdate()->delete();
        // $JurnalUmumHeader = JurnalUmumHeader::where('nobukti', $pengeluaranHeader->nobukti)->lockForUpdate()->delete();
        /*DELETE EXISTING Pengeluaran*/
        $pengeluaranDetail = PengeluaranDetail::where('pengeluaran_id', $pengeluaranHeader->id)->lockForUpdate()->delete();
        $alatabayargiro = DB::table('parameter')->from(db::raw("parameter a with (readuncommitted)"))
            ->select(
                'a.text',
                'a.memo'
            )
            ->where('a.grp', 'ALAT BAYAR GIRO')
            ->where('a.subgrp', 'ALAT BAYAR GIRO')
            ->first();

        $alatabayarid = $data['alatbayar_id'] ?? 0;
        $pengeluaranDetails = [];
        $coadebet_detail = [];
        $coakredit_detail = [];
        $nominal_detail = [];
        $keterangan_detail = [];

        for ($i = 0; $i < count($data['nominal_detail']); $i++) {

            $coakredit = $data['coakredit'][$i] ?? '';

            // if ($coakredit == '') {
            //     $coaKredit = $querysubgrppengeluaran->coa;
            // } else {
            //     $coaKredit = $coakredit;
            // } 
            if ($alatabayarid == $alatabayargiro->text) {
                $memo = json_decode($alatabayargiro->memo, true);
                $coakredit_detail[] = $memo['JURNAL'];
                $coaKredit = $memo['JURNAL'];
            } else {
                $coakredit_detail[] = $querysubgrppengeluaran->coa;
                $coaKredit = $querysubgrppengeluaran->coa;
            }

            $pengeluaranDetail = (new PengeluaranDetail())->processStore($pengeluaranHeader, [
                'pengeluaran_id' => $pengeluaranHeader->id,
                'nobukti' => $pengeluaranHeader->nobukti,
                'nowarkat' =>  $data['nowarkat'][$i],
                'tgljatuhtempo' =>  date('Y-m-d', strtotime($data['tgljatuhtempo'][$i])),
                'nominal' => $data['nominal_detail'][$i],
                'coadebet' =>  $data['coadebet'][$i],
                'coakredit' =>  $coaKredit, //( $coakredit) ?  $coakredit : $querysubgrppengeluaran->coa,
                'keterangan' => $data['keterangan_detail'][$i],
                'noinvoice' => $data['noinvoice'][$i] ?? '',
                'bank' => $data['bank_detail'][$i] ?? '',
                'modifiedby' => auth('api')->user()->name,
            ]);
            $pengeluaranDetails[] = $pengeluaranDetail->toArray();
            $coadebet_detail[] =  $data['coadebet'][$i];
            // $coakredit_detail[] = $coaKredit; //($data['coakredit']) ? $data['coakredit'][$i] : $querysubgrppengeluaran->coa;
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

        if ($manual) {
            PengeluaranPenerima::where('pengeluaran_id', $pengeluaranHeader->id)->lockForUpdate()->delete();

            if ($data['penerima_id'] != '') {

                for ($i = 0; $i < count($data['penerima_id']); $i++) {

                    $pengeluaranPenerima = (new PengeluaranPenerima())->processStore([
                        'pengeluaran_id' => $pengeluaranHeader->id,
                        'nobukti' => $pengeluaranHeader->nobukti,
                        'penerima_id' =>  $data['penerima_id'][$i],
                    ]);
                }
            }
        }

        /*STORE JURNAL*/
        $jurnalRequest = [
            'tanpaprosesnobukti' => 1,
            'nobukti' => $pengeluaranHeader->nobukti,
            'tglbukti' => $pengeluaranHeader->tglbukti,
            'postingdari' =>  $data['postingdari'] ?? "ENTRY PENGELUARAN",
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

        // $jurnalUmumHeader = (new JurnalUmumHeader())->processStore($jurnalRequest);
        $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $nobuktiOld)->first();
        if (isset($getJurnal)) {
            $newJurnal = new JurnalUmumHeader();
            $newJurnal = $newJurnal->find($getJurnal->id);
            if ($nobuktiOld != $pengeluaranHeader->nobukti) {
                (new JurnalUmumHeader())->processDestroy($getJurnal->id, 'UPDATE PELUNASAN HUTANG');
                (new JurnalUmumHeader())->processStore($jurnalRequest);
            } else {
                (new JurnalUmumHeader())->processUpdate($newJurnal, $jurnalRequest);
            }
        } else {
            $jurnalRequest = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $pengeluaranHeader->nobukti,
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'postingdari' => $data['postingdari'] ?? "ENTRY PENGELUARAN",
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
        }
        return $pengeluaranHeader;
    }

    public function processDestroy($id, $postingDari = ''): PengeluaranHeader
    {
        $pengeluaranDetail = PengeluaranDetail::where('pengeluaran_id', '=', $id)->get();
        $dataDetail = $pengeluaranDetail->toArray();

        $pengeluaranHeader = new PengeluaranHeader();
        $pengeluaranHeader = $pengeluaranHeader->lockAndDestroy($id);

        $hutangLogTrail = (new LogTrail())->processStore([
            'namatabel' => $this->table,
            'postingdari' => ($postingDari == "") ? $postingDari : strtoupper('DELETE pengeluaran Header'),
            'idtrans' => $pengeluaranHeader->id,
            'nobuktitrans' => $pengeluaranHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $pengeluaranHeader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'PENGELUARANDETAIL',
            'postingdari' => ($postingDari == "") ? $postingDari : strtoupper('DELETE pengeluaran detail'),
            'idtrans' => $hutangLogTrail['id'],
            'nobuktitrans' => $pengeluaranHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $dataDetail,
            'modifiedby' => auth('api')->user()->name
        ]);

        $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $pengeluaranHeader->nobukti)->first();
        $jurnalumumHeader = (new JurnalUmumHeader())->processDestroy($getJurnal->id, ($postingDari == "") ? $postingDari : strtoupper('DELETE pengeluaran Header'));

        return $pengeluaranHeader;
    }

    public function getExport($id)
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

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
                'bank.tipe as tipe_bank',
                'pengeluaranheader.transferkeac',
                'pengeluaranheader.transferkean',
                'pengeluaranheader.transferkebank',
                'statuscetak.memo as statuscetak',
                'statuscetak.id as  statuscetak_id',
                'pengeluaranheader.jumlahcetak',
                DB::raw("'Laporan Pengeluaran' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'pengeluaranheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'pengeluaranheader.pelanggan_id', 'pelanggan.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pengeluaranheader.alatbayar_id', 'alatbayar.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluaranheader.bank_id', 'bank.id');

        $data = $query->first();
        return $data;
    }
}
