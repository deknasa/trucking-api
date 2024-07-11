<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class StatusOliTrado extends MyModel
{
    use HasFactory;


    public function get($tgldari, $tglsampai, $trado_id, $statusoli)
    {

        $trado_id = $trado_id ?? 0;
        $statusoli = $statusoli ?? 0;
        $this->setRequestParameters();

        $datafilter = request()->filter ?? 0;
        $forExport = request()->forExport ?? false;
        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'StatusOliController';

        $temtabel = 'temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;

        if ($proses == 'reload') {

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
                $table->integer('trado_id')->nullable();
                $table->longText('nopol')->nullable();
                $table->date('tanggal')->nullable();
                $table->string('status', 100)->nullable();
                $table->string('kodestok', 1000)->nullable();
                $table->double('qty', 15, 2)->nullable();
                $table->string('satuan', 1000)->nullable();
            });

            DB::table($temtabel)->insertUsing([
                'trado_id',
                'nopol',
                'tanggal',
                'status',
                'kodestok',
                'qty',
                'satuan',

            ], $this->getdata($tgldari, $tglsampai, $trado_id, $statusoli));

            //  dd('test');
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
                'a.trado_id',
                'a.nopol',
                'a.tanggal',
                'a.status',
                'a.kodestok',
                'a.qty',
                'a.satuan',

            );

        //    dd($query->get());

        if (!$forExport) {

            $this->filter($query);
            // dd('test');
            $this->totalRows = $query->count();

            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
            $query->orderBy('a.id', 'asc');
            $this->paginate($query);
        } else {
            $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp', 'JUDULAN LAPORAN')
                ->where('subgrp', 'JUDULAN LAPORAN')
                ->first();
            $query->addSelect(DB::raw("'" . $getJudul->text . "' as judul"), DB::raw("'LAPORAN STATUS OLI' as judulLaporan"));
        }

        $data = $query->get();
        return $data;
    }


    public function filter($query, $relationFields = [])
    {

        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'tanggal') {
                            $query = $query->whereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else {
                            $query = $query->whereRaw('a' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'tanggal') {
                                $query = $query->orWhereRaw("format(a." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->OrwhereRaw('a' . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function getdata($tgldari, $tglsampai, $trado_id, $statusoli)
    {



        $trado_id = $trado_id ?? 0;
        $statusoli = $statusoli ?? 0;
        $serviceoli = Parameter::where('grp', 'STATUS SERVICE RUTIN')->where('subgrp', 'STATUS SERVICE RUTIN')->where('text', 'PERGANTIAN OLI MESIN')->first()->id ?? 0;
        // dd($serviceoli);
        if ($trado_id == 0) {

            $query = db::table("pengeluaranstokheader")->from(db::raw("pengeluaranstokheader a with (readuncommitted)"))
                ->select(
                    'd.id as trado_id',
                    'd.kodetrado as nopol',
                    'a.tglbukti as tanggal',
                    'e.text as status',
                    'c.namastok as kodestok',
                    'b.qty',
                    db::raw("isnull(f.satuan,'') as satuan")
                )
                ->join(db::raw("pengeluaranstokdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
                ->join(db::raw("stok c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->join(db::raw("trado d with (readuncommitted)"), 'a.trado_id', 'd.id')
                ->leftjoin(db::raw("parameter e with (readuncommitted)"), 'b.statusoli', 'e.id')
                ->leftjoin(db::raw("satuan f with (readuncommitted)"), 'c.satuan_id', 'f.id')
                ->whereraw("(a.tglbukti>='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                ->whereraw("(" . $statusoli . "=0 or isnull(b.statusoli,0)=" . $statusoli . ")")
                ->where('c.statusservicerutin', $serviceoli)
                ->orderby('a.tglBukti', 'desc')
                ->orderby('b.stok_id', 'asc');
        } else {
            $query = db::table("pengeluaranstokheader")->from(db::raw("pengeluaranstokheader a with (readuncommitted)"))
                ->select(
                    'd.id as trado_id',
                    'd.kodetrado as nopol',
                    'a.tglbukti as tanggal',
                    'e.text as status',
                    'c.namastok as kodestok',
                    'b.qty',
                    db::raw("isnull(f.satuan,'') as satuan")
                )
                ->join(db::raw("pengeluaranstokdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
                ->join(db::raw("stok c with (readuncommitted)"), 'b.stok_id', 'c.id')
                ->join(db::raw("trado d with (readuncommitted)"), 'a.trado_id', 'd.id')
                ->leftjoin(db::raw("parameter e with (readuncommitted)"), 'b.statusoli', 'e.id')
                ->leftjoin(db::raw("satuan f with (readuncommitted)"), 'c.satuan_id', 'f.id')
                ->whereraw("(a.tglbukti>='" . $tgldari . "' and a.tglbukti<='" . $tglsampai . "')")
                ->whereraw("(" . $statusoli . "=0 or isnull(b.statusoli,0)=" . $statusoli . ")")
                ->where('c.statusservicerutin', $serviceoli)
                ->where('d.id', $trado_id)
                ->orderby('a.tglBukti', 'desc')
                ->orderby('b.stok_id', 'asc');
        }
        // dd($query->get());

        return $query;
    }
}
