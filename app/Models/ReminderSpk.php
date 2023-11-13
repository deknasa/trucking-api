<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ReminderSpk extends MyModel
{
    use HasFactory;

    public function get()
    {

        $this->setRequestParameters();
        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'ReminderSpkController';

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
                $table->id();
                $table->longText('gudang')->nullable();
                $table->string('stok', 1000)->nullable();
                $table->double('qty', 15, 2)->nullable();
                $table->double('total', 15, 2)->nullable();
            });





            $phari = 0;
            $tgl2 = date('Y-m-d');
            $ptgl = date('Y-m-d', strtotime($tgl2 . ' -' . $phari . ' day'));
            $datepart = DB::select("select datepart(dw,'" . $ptgl . "') as dpart");

            $dpart = json_decode(json_encode($datepart), true)[0]['dpart'];
            if ($dpart == 2) {
                $ptgl = date('Y-m-d', strtotime($ptgl . ' -2 day'));
            } else {
                $ptgl = date('Y-m-d', strtotime($ptgl . ' -1 day'));
            }

            $pnominal = 200000;
            $pengeluaranstok_id = 1;
            $kelompoksparepart = 2;

            $temppengeluaranstokheader = '##temppengeluaranstokheader' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temppengeluaranstokheader, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->string('nobukti', 50)->unique();
                $table->date('tglbukti', 50)->nullable();
                $table->longText('keterangan')->nullable();
                $table->unsignedBigInteger('pengeluaranstok_id')->nullable();
                $table->unsignedBigInteger('trado_id')->nullable();
                $table->unsignedBigInteger('gudang_id')->nullable();
                $table->unsignedBigInteger('gandengan_id')->nullable();
                $table->unsignedBigInteger('supir_id')->nullable();
                $table->unsignedBigInteger('supplier_id')->nullable();
                $table->string('pengeluaranstok_nobukti', 50)->nullable();
                $table->string('penerimaanstok_nobukti', 50)->nullable();
                $table->string('pengeluarantrucking_nobukti', 50)->nullable();
                $table->string('servicein_nobukti', 50)->nullable();
                $table->unsignedBigInteger('kerusakan_id')->nullable();
                $table->integer('statuspotongretur')->Length(11)->nullable();
                $table->unsignedBigInteger('bank_id')->nullable();
                $table->string('penerimaan_nobukti', 50)->nullable();
                $table->string('coa', 50)->nullable();
                $table->string('postingdari', 50)->nullable();
                $table->date('tglkasmasuk')->nullable();
                $table->string('hutangbayar_nobukti', 50)->nullable();
                $table->unsignedBigInteger('statusformat')->nullable();
                $table->integer('statuscetak')->Length(11)->nullable();
                $table->string('userbukacetak', 50)->nullable();
                $table->date('tglbukacetak')->nullable();
                $table->integer('jumlahcetak')->Length(11)->nullable();
                $table->integer('statusapprovaledit')->Length(11)->nullable();
                $table->string('userapprovaledit', 50)->nullable();
                $table->date('tglapprovaledit')->nullable();
                $table->dateTime('tglbatasedit')->nullable();
                $table->longText('info')->nullable();
                $table->string('modifiedby', 50)->nullable();
            });


            $temppengeluaranstokdetail = '##temppengeluaranstokdetail' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temppengeluaranstokdetail, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->unsignedBigInteger('pengeluaranstokheader_id')->nullable();
                $table->string('nobukti', 50)->nullable();
                $table->unsignedBigInteger('stok_id');
                $table->double('qty', 15, 2)->nullable();
                $table->double('harga', 15, 2)->nullable();
                $table->double('selisihhargafifo', 15, 2)->nullable();
                $table->double('persentasediscount', 15, 2)->nullable();
                $table->double('nominaldiscount', 15, 2)->nullable();
                $table->double('total', 15, 2)->nullable();
                $table->longText('keterangan')->nullable();
                $table->unsignedBigInteger('vulkanisirke')->nullable();
                $table->integer('statusservicerutin')->length(11)->nullable();
                $table->integer('statusoli')->length(11)->nullable();
                $table->integer('statusban')->length(11)->nullable();
                $table->string('pengeluaranstok_nobukti', 50)->nullable();
                $table->integer('jumlahhariaki')->length(11)->nullable();
                $table->longText('info')->nullable();
                $table->string('modifiedby', 50)->nullable();
            });


            $querypengeluaranstokheader = db::table("pengeluaranstokheader")->from(db::raw("pengeluaranstokheader a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.nobukti',
                    'a.tglbukti',
                    'a.keterangan',
                    'a.pengeluaranstok_id',
                    'a.trado_id',
                    'a.gudang_id',
                    'a.gandengan_id',
                    'a.supir_id',
                    'a.supplier_id',
                    'a.pengeluaranstok_nobukti',
                    'a.penerimaanstok_nobukti',
                    'a.pengeluarantrucking_nobukti',
                    'a.servicein_nobukti',
                    'a.kerusakan_id',
                    'a.statuspotongretur',
                    'a.bank_id',
                    'a.penerimaan_nobukti',
                    'a.coa',
                    'a.postingdari',
                    'a.tglkasmasuk',
                    'a.hutangbayar_nobukti',
                    'a.statusformat',
                    'a.statuscetak',
                    'a.userbukacetak',
                    'a.tglbukacetak',
                    'a.jumlahcetak',
                    'a.statusapprovaledit',
                    'a.userapprovaledit',
                    'a.tglapprovaledit',
                    'a.tglbatasedit',
                    'a.info',
                    'a.modifiedby',
                )
                ->where('a.pengeluaranstok_id', $pengeluaranstok_id)
                ->where('a.tglbukti', $ptgl);

            DB::table($temppengeluaranstokheader)->insertUsing([
                'id',
                'nobukti',
                'tglbukti',
                'keterangan',
                'pengeluaranstok_id',
                'trado_id',
                'gudang_id',
                'gandengan_id',
                'supir_id',
                'supplier_id',
                'pengeluaranstok_nobukti',
                'penerimaanstok_nobukti',
                'pengeluarantrucking_nobukti',
                'servicein_nobukti',
                'kerusakan_id',
                'statuspotongretur',
                'bank_id',
                'penerimaan_nobukti',
                'coa',
                'postingdari',
                'tglkasmasuk',
                'hutangbayar_nobukti',
                'statusformat',
                'statuscetak',
                'userbukacetak',
                'tglbukacetak',
                'jumlahcetak',
                'statusapprovaledit',
                'userapprovaledit',
                'tglapprovaledit',
                'tglbatasedit',
                'info',
                'modifiedby',
            ], $querypengeluaranstokheader);


            $querypengeluaranstokdetail = db::table("pengeluaranstokdetail")->from(db::raw("pengeluaranstokdetail a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.pengeluaranstokheader_id',
                    'a.nobukti',
                    'a.stok_id',
                    'a.qty',
                    'a.harga',
                    'a.selisihhargafifo',
                    'a.persentasediscount',
                    'a.nominaldiscount',
                    'a.total',
                    'a.keterangan',
                    'a.vulkanisirke',
                    'a.statusservicerutin',
                    'a.statusoli',
                    'a.statusban',
                    'a.pengeluaranstok_nobukti',
                    'a.jumlahhariaki',
                    'a.info',
                    'a.modifiedby',
                )
                ->join(db::raw($temppengeluaranstokheader . " b"), 'a.nobukti', 'b.nobukti')
                ->join(db::raw("stok c with (readuncommitted)"), 'a.stok_id', 'c.id')
                ->where('c.kelompok_id', $kelompoksparepart)
                ->whereRaw("a.total>=" . $pnominal)
                ->whereRaw("(isnull(b.trado_id,0)<>0 or isnull(b.gandengan_id,0)<>0)");


            DB::table($temppengeluaranstokdetail)->insertUsing([
                'id',
                'pengeluaranstokheader_id',
                'nobukti',
                'stok_id',
                'qty',
                'harga',
                'selisihhargafifo',
                'persentasediscount',
                'nominaldiscount',
                'total',
                'keterangan',
                'vulkanisirke',
                'statusservicerutin',
                'statusoli',
                'statusban',
                'pengeluaranstok_nobukti',
                'jumlahhariaki',
                'info',
                'modifiedby',
            ], $querypengeluaranstokdetail);


            $tempdataheader = '##tempdataheader' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempdataheader, function ($table) {
                $table->integer('id')->nullable();
                $table->string('trado', 1000)->nullable();
                $table->string('gandengan', 1000)->nullable();
                $table->string('stok', 1000)->nullable();
                $table->double('qty', 15, 2)->nullable();
                $table->double('total', 15, 2)->nullable();
            });

            $querydataheader = db::table($temppengeluaranstokdetail)->from(db::raw($temppengeluaranstokdetail . " a "))
                ->select(
                    DB::raw("row_number() Over(Order By D.kodetrado) as id"),
                    db::raw("isnull(D.kodetrado,'') as trado"),
                    db::raw("isnull(e.kodegandengan,'') as gandengan"),
                    db::raw("isnull(c.namastok,'') as stok"),
                    db::raw("sum(a.qty) as qty"),
                    db::raw("sum(a.total) as total")
                )
                ->join(db::raw($temppengeluaranstokheader . " b"), 'a.nobukti', 'b.nobukti')
                ->join(db::raw("stok c with (readuncommitted)"), 'a.stok_id', 'c.id')
                ->leftjoin(db::raw("trado d with (readuncommitted)"), 'b.trado_id', 'd.id')
                ->leftjoin(db::raw("gandengan e with (readuncommitted)"), 'b.gandengan_id', 'e.id')
                ->groupby('d.kodetrado')
                ->groupby('e.kodegandengan')
                ->groupby('c.namastok');

            DB::table($tempdataheader)->insertUsing([
                'id',
                'trado',
                'gandengan',
                'stok',
                'qty',
                'total',
            ], $querydataheader);



            $query = db::table($tempdataheader)->from(db::raw($tempdataheader . " a"))
                ->select(
                    db::raw("(case when isnull(a.trado,'')='' then a.gandengan else a.trado end) as gudang"),
                    'a.stok',
                    'a.qty',
                    'a.total'
                )
                ->orderBY('a.id');


            DB::table($temtabel)->insertUsing([
                'gudang',
                'stok',
                'qty',
                'total',
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

        $query = DB::table(DB::raw($temtabel))->from(
            DB::raw(DB::raw($temtabel) . " a with (readuncommitted)")
        )
            ->select(
                'a.gudang',
                'a.stok',
                'a.qty',
                'a.total',
            );

        $this->totalRows = $query->count();

        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);

        // dd($query->toSql());
        $this->filter($query);
        // dd($query->get());
        $this->paginate($query);

        $data = $query->get();


        // } else {
        //     $data = [];
        // }

        return $data;
    }


    public function filter($query, $relationFields = [])
    {

        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {

                        // $query = $query->where('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        $query = $query->whereRaw('a' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {

                            // $query->orWhere('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->OrwhereRaw('a' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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
}
