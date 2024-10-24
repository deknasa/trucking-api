<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InvoiceLunasKePusat extends MyModel
{
    use HasFactory;

    protected $table = 'invoicelunaskepusat';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    public function get()
    {

        $periode = request()->periode ?? '01-1900';
        $this->setRequestParameters();
        $tempinvoice = '##tempinvoice' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempinvoice, function ($table) {
            $table->id();
            $table->integer('invoiceheader_id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->unsignedBigInteger('agen_id')->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->date('tglbayar')->nullable();
            $table->double('bayar', 15, 2)->nullable();
            $table->double('potongan', 15, 2)->nullable();
            $table->double('sisa', 15, 2)->nullable();
        });

        $querytemp = db::table("invoicelunaskepusat")->from(db::raw("invoicelunaskepusat a with (readuncommitted)"))
            ->select(
                'a.invoiceheader_id',
                'a.nobukti',
                'a.tglbukti',
                'a.agen_id',
                'a.nominal',
                'a.tglbayar',
                'a.bayar',
                db::raw("isnull(a.potongan,0) as potongan"),
                'a.sisa',
            )
            ->whereRaw("format(a.tglbukti,'MM-yyyy')='" . $periode . "'");

        DB::table($tempinvoice)->insertUsing([
            'invoiceheader_id',
            'nobukti',
            'tglbukti',
            'agen_id',
            'nominal',
            'tglbayar',
            'bayar',
            'potongan',
            'sisa',
        ], $querytemp);

        $querytemp = db::table("invoiceheader")->from(db::raw("invoiceheader a with (readuncommitted)"))
            ->select(
                'a.id as invoiceheader',
                'a.nobukti',
                'a.tglbukti',
                'a.agen_id',
                'a.nominal',
                db::raw("null as tglbayar"),
                db::raw("0 as bayar"),
                db::raw("0 as potongan"),
                db::raw("a.nominal as sisa"),
            )
            ->leftjoin(db::raw($tempinvoice . " b "), 'a.nobukti', 'b.nobukti')
            ->whereRaw("isnull(b.nobukti,'')=''")
            ->whereRaw("format(a.tglbukti,'MM-yyyy')='" . $periode . "'");

        // dd($querytemp->tosql());

        DB::table($tempinvoice)->insertUsing([
            'invoiceheader_id',
            'nobukti',
            'tglbukti',
            'agen_id',
            'nominal',
            'tglbayar',
            'bayar',
            'potongan',
            'sisa',
        ], $querytemp);

        $querytemp = db::table("invoiceextraheader")->from(db::raw("invoiceextraheader a with (readuncommitted)"))
            ->select(
                'a.id as invoiceheader',
                'a.nobukti',
                'a.tglbukti',
                'a.agen_id',
                'a.nominal',
                db::raw("null as tglbayar"),
                db::raw("0 as bayar"),
                db::raw("0 as potongan"),
                db::raw("a.nominal as sisa"),
            )
            ->leftjoin(db::raw($tempinvoice . " b "), 'a.nobukti', 'b.nobukti')
            ->whereRaw("isnull(b.nobukti,'')=''")
            ->whereRaw("format(a.tglbukti,'MM-yyyy')='" . $periode . "'");

        // dd($querytemp->tosql());

        DB::table($tempinvoice)->insertUsing([
            'invoiceheader_id',
            'nobukti',
            'tglbukti',
            'agen_id',
            'nominal',
            'tglbayar',
            'bayar',
            'potongan',
            'sisa',
        ], $querytemp);

        $query = db::table($tempinvoice)->from(db::raw($tempinvoice . " a"))
            ->select(
                DB::raw("row_number() Over(Order By a.nobukti) as id"),
                'a.invoiceheader_id',
                'a.nobukti',
                'a.tglbukti',
                db::raw("isnull(b.kodeagen,'') as agen_id"),
                'a.nominal',
                'a.tglbayar',
                'a.bayar',
                'a.potongan',
                'a.sisa',
            )
            ->leftjoin(db::raw("agen  b with (readuncommitted)"), 'a.agen_id', 'b.id')
            ->orderBy('a.nobukti', 'asc');

        // dd($query->get());
        $this->filter($query);
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $this->sort($query);
        $this->paginate($query);
        $data = $query->get();
        // dd($this->totalPages);
        return $data;
    }
    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'agen_id') {
                                $query = $query->where('b.kodeagen', '=', $filters['data']);
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
                                if ($filters['field'] == 'agen_id') {
                                    $query = $query->orWhere('b.kodeagen', '=', $filters['data']);
                                } else {
                                    $query = $query->orWhereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function sort($query)
    {

        return $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function getinvoicelunas($id)
    {
        $cek = substr(request()->nobukti, 0, 3);
        if ($cek == 'INV') {

            $query = db::table("invoiceheader")->from(db::raw("invoiceheader a with (readuncommitted)"))
                ->select(
                    'c.id',
                    'a.id as invoiceheader_id',
                    'a.nobukti',
                    'a.tglbukti',
                    db::raw("isnull(b.kodeagen,'') as agen"),
                    db::raw("isnull(b.id,0) as agen_id"),
                    'a.nominal',
                    db::raw("isnull(c.tglbayar,format(getdate(),'yyyy/MM/dd')) as tglbayar"),
                    db::raw("isnull(c.bayar,0) as bayar"),
                    db::raw("isnull(c.potongan,0) as potongan"),
                    db::raw("(isnull(a.nominal,0)-isnull(c.nominal,0)) as sisa"),
                )
                ->leftjoin(db::raw("agen b with (readuncommitted)"), 'a.agen_id', 'b.id')
                ->leftjoin(db::raw("invoicelunaskepusat c with (readuncommitted)"), 'a.nobukti', 'c.nobukti')
                ->where('a.id', $id);
        } else {

            $query = db::table("invoiceextraheader")->from(db::raw("invoiceextraheader a with (readuncommitted)"))
                ->select(
                    'c.id',
                    'a.id as invoiceheader_id',
                    'a.nobukti',
                    'a.tglbukti',
                    db::raw("isnull(b.kodeagen,'') as agen"),
                    db::raw("isnull(b.id,0) as agen_id"),
                    'a.nominal',
                    db::raw("isnull(c.tglbayar,format(getdate(),'yyyy/MM/dd')) as tglbayar"),
                    db::raw("isnull(c.bayar,0) as bayar"),
                    db::raw("isnull(c.potongan,0) as potongan"),
                    db::raw("(isnull(a.nominal,0)-isnull(c.nominal,0)) as sisa"),
                )
                ->leftjoin(db::raw("agen b with (readuncommitted)"), 'a.agen_id', 'b.id')
                ->leftjoin(db::raw("invoicelunaskepusat c with (readuncommitted)"), 'a.nobukti', 'c.nobukti')
                ->where('a.id', $id);
        }

        return $query->first();
    }

    public function processStore(array $data)
    {
        $InvoiceLunaskePusat = new InvoiceLunasKePusat();
        $InvoiceLunaskePusat->invoiceheader_id = $data['invoiceheader_id'];
        $InvoiceLunaskePusat->nobukti = $data['nobukti'] ?? '';
        $InvoiceLunaskePusat->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $InvoiceLunaskePusat->agen_id = $data['agen_id'];
        $InvoiceLunaskePusat->nominal = $data['nominal'];
        $InvoiceLunaskePusat->tglbayar = date('Y-m-d', strtotime($data['tglbayar']));
        $InvoiceLunaskePusat->bayar = $data['bayar'];
        $InvoiceLunaskePusat->sisa = $data['sisa'];
        $InvoiceLunaskePusat->potongan = $data['potongan'];
        $InvoiceLunaskePusat->modifiedby = auth('api')->user()->name;
        $InvoiceLunaskePusat->info = html_entity_decode(request()->info);
        // $request->sortname = $request->sortname ?? 'id';
        // $request->sortorder = $request->sortorder ?? 'asc';

        if (!$InvoiceLunaskePusat->save()) {
            throw new \Exception("Error Simpan Invoice Lunas ke Pusat");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($InvoiceLunaskePusat->getTable()),
            'postingdari' => 'ENTRY INVOICE LUNAS KE PUSAT',
            'idtrans' => $InvoiceLunaskePusat->id,
            'nobuktitrans' => $InvoiceLunaskePusat->id,
            'aksi' => 'ENTRY',
            'datajson' => $InvoiceLunaskePusat->toArray(),
            'modifiedby' => $InvoiceLunaskePusat->modifiedby
        ]);

        return $InvoiceLunaskePusat;
    }
    public function processUpdate(InvoiceLunaskePusat $InvoiceLunaskePusat, array $data)
    {

        $InvoiceLunaskePusat->invoiceheader_id = $data['invoiceheader_id'];
        $InvoiceLunaskePusat->nobukti = $data['nobukti'] ?? '';
        $InvoiceLunaskePusat->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $InvoiceLunaskePusat->agen_id = $data['agen_id'];
        $InvoiceLunaskePusat->nominal = $data['nominal'];
        $InvoiceLunaskePusat->tglbayar = date('Y-m-d', strtotime($data['tglbayar']));
        $InvoiceLunaskePusat->bayar = $data['bayar'];
        $InvoiceLunaskePusat->sisa = $data['sisa'];
        $InvoiceLunaskePusat->potongan = $data['potongan'];
        $InvoiceLunaskePusat->modifiedby = auth('api')->user()->name;
        $InvoiceLunaskePusat->info = html_entity_decode(request()->info);


        if (!$InvoiceLunaskePusat->save()) {
            throw new \Exception("Error update Invoice Lunas ke Pusat.");
        }
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($InvoiceLunaskePusat->getTable()),
            'postingdari' => 'EDIT INVOICE LUNAS KE PUSAT',
            'idtrans' => $InvoiceLunaskePusat->id,
            'nobuktitrans' => $InvoiceLunaskePusat->id,
            'aksi' => 'EDIT',
            'datajson' => $InvoiceLunaskePusat->toArray(),
            'modifiedby' => $InvoiceLunaskePusat->modifiedby
        ]);

        return $InvoiceLunaskePusat;
    }


    public function processDestroy($id)
    {
        // $AbsensiSupirHeader = AbsensiSupirHeader::where('id', $AbsensiSupirDetail->absensi_id)->first();
        $InvoiceLunaskePusat = new InvoiceLunasKePusat();
        $InvoiceLunaskePusat = $InvoiceLunaskePusat->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($InvoiceLunaskePusat->getTable()),
            'postingdari' => 'DELETE INVOICE LUNAS KE PUSAT',
            'idtrans' => $InvoiceLunaskePusat->id,
            'nobuktitrans' => $InvoiceLunaskePusat->id,
            'aksi' => 'DELETE',
            'datajson' => $InvoiceLunaskePusat->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        return $InvoiceLunaskePusat;
    }

    public function report()
    {

        $periode = request()->periode ?? '01-1900';
        $forReport = request()->forReport ?? false;
        $invId = request()->invId ?? '';
        $tempinvoice = '##tempinvoice' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempinvoice, function ($table) {
            $table->id();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('piutang_nobukti', 50)->nullable();
            $table->string('agen', 100)->nullable();
            $table->string('invbulan', 50)->nullable();
            $table->date('tglpiutang')->nullable();
            $table->string('cabang', 50)->nullable();
            $table->string('bagian', 50)->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });
        $cabang = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))
            ->select('cabang.kodecabang')
            ->join(DB::raw("cabang with (readuncommitted)"), 'cabang.id', 'parameter.text')
            ->where('parameter.grp', 'ID CABANG')->first();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();
        $querytemp = db::table("invoiceheader")->from(db::raw("invoiceheader a with (readuncommitted)"))
            ->select(
                'a.nobukti',
                'a.tglbukti',
                'a.piutang_nobukti',
                'agen.namaagen as agen',
                DB::raw("'$periode' as invbulan"),
                'piutangheader.tglbukti as tglpiutang',
                DB::raw("'$cabang->kodecabang' as cabang"),
                'jenisorder.keterangan as bagian',
                'a.nominal',
            )
            ->leftJoin(DB::raw("piutangheader with (readuncommitted)"), 'piutangheader.nobukti', 'a.piutang_nobukti')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'agen.id', 'a.agen_id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'jenisorder.id', 'a.jenisorder_id')
            ->whereRaw("format(a.tglbukti,'MM-yyyy')='" . $periode . "'")
            ->whereRaw("a.id in ($invId)");

        // dd($querytemp->tosql());

        DB::table($tempinvoice)->insertUsing([
            'nobukti',
            'tglbukti',
            'piutang_nobukti',
            'agen',
            'invbulan',
            'tglpiutang',
            'cabang',
            'bagian',
            'nominal'
        ], $querytemp);

        $query = db::table($tempinvoice)->from(db::raw($tempinvoice . " a"))
            ->select(
                'a.nobukti',
                'a.tglbukti',
                'a.piutang_nobukti',
                'a.agen',
                'a.invbulan',
                'a.tglpiutang',
                'a.cabang',
                'a.bagian',
                'a.nominal',
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'LAMPIRAN (INVOICE TRUCKING)' as judulLaporan"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->orderBy('a.nobukti', 'asc');


        $data = $query->get();
        return $data;
    }

    public function getExport(array $data)
    {
        $nobukti = json_encode($data['nobukti']);
        $query = db::table('a')->from(DB::raw("OPENJSON ('$nobukti')"))
            ->select(db::raw("string_agg('''' + [value] + '''', ',') as nobukti"))
            ->first();


        $tempinvoice = '##tempinvoice' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempinvoice, function ($table) {
            $table->id();
            $table->integer('invoiceheader_id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->unsignedBigInteger('agen_id')->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->date('tglbayar')->nullable();
            $table->double('bayar', 15, 2)->nullable();
            $table->double('potongan', 15, 2)->nullable();
            $table->double('sisa', 15, 2)->nullable();
        });

        $querytemp = db::table("invoicelunaskepusat")->from(db::raw("invoicelunaskepusat a with (readuncommitted)"))
            ->select(
                'a.invoiceheader_id',
                'a.nobukti',
                'a.tglbukti',
                'a.agen_id',
                'a.nominal',
                'a.tglbayar',
                'a.bayar',
                db::raw("isnull(a.potongan,0) as potongan"),
                'a.sisa',
            )
            ->whereRaw("a.nobukti in ($query->nobukti)");

        DB::table($tempinvoice)->insertUsing([
            'invoiceheader_id',
            'nobukti',
            'tglbukti',
            'agen_id',
            'nominal',
            'tglbayar',
            'bayar',
            'potongan',
            'sisa',
        ], $querytemp);

        $querytemp = db::table("invoiceheader")->from(db::raw("invoiceheader a with (readuncommitted)"))
            ->select(
                'a.id as invoiceheader',
                'a.nobukti',
                'a.tglbukti',
                'a.agen_id',
                'a.nominal',
                db::raw("null as tglbayar"),
                db::raw("0 as bayar"),
                db::raw("0 as potongan"),
                db::raw("a.nominal as sisa"),
            )
            ->leftjoin(db::raw($tempinvoice . " b "), 'a.nobukti', 'b.nobukti')
            ->whereRaw("isnull(b.nobukti,'')=''")
            ->whereRaw("a.nobukti in ($query->nobukti)");


        DB::table($tempinvoice)->insertUsing([
            'invoiceheader_id',
            'nobukti',
            'tglbukti',
            'agen_id',
            'nominal',
            'tglbayar',
            'bayar',
            'potongan',
            'sisa',
        ], $querytemp);

        $querytemp = db::table("invoiceextraheader")->from(db::raw("invoiceextraheader a with (readuncommitted)"))
            ->select(
                'a.id as invoiceheader',
                'a.nobukti',
                'a.tglbukti',
                'a.agen_id',
                'a.nominal',
                db::raw("null as tglbayar"),
                db::raw("0 as bayar"),
                db::raw("0 as potongan"),
                db::raw("a.nominal as sisa"),
            )
            ->leftjoin(db::raw($tempinvoice . " b "), 'a.nobukti', 'b.nobukti')
            ->whereRaw("isnull(b.nobukti,'')=''")
            ->whereRaw("a.nobukti in ($query->nobukti)");

        // dd($querytemp->tosql());

        DB::table($tempinvoice)->insertUsing([
            'invoiceheader_id',
            'nobukti',
            'tglbukti',
            'agen_id',
            'nominal',
            'tglbayar',
            'bayar',
            'potongan',
            'sisa',
        ], $querytemp);

        $query = db::table($tempinvoice)->from(db::raw($tempinvoice . " a"))
            ->select(
                DB::raw("row_number() Over(Order By a.nobukti) as id"),
                'a.invoiceheader_id',
                'a.nobukti',
                'a.tglbukti',
                db::raw("isnull(b.kodeagen,'') as agen_id"),
                'a.nominal',
                'a.tglbayar',
                'a.bayar',
                'a.potongan',
                'a.sisa',
            )
            ->leftjoin(db::raw("agen  b with (readuncommitted)"), 'a.agen_id', 'b.id')
            ->orderBy('a.tglbukti', 'asc')
            ->orderBy('a.nobukti', 'asc');


        return $query->get();
    }
}
