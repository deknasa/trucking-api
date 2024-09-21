<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrderanEmkl extends MyModel
{
    use HasFactory;

    public function get()
    {
        $this->setRequestParameters();
        $bulan = date('m-Y');
        $bulanjob = request()->bulanjob ?? $bulan;
        $container = request()->container_id ?? 0;
        $jenisorder = request()->jenisorder_id ?? 0;


        $getParameter = (new Parameter())->cekText('ORDERAN EMKL REPLICATION', 'ORDERAN EMKL REPLICATION');
        $koneksi = ($getParameter == 'YA') ? 'sqlsrv' : 'sqlsrvemkl';

        if ($container == '1') {
            $containerid = '6,4,10';
        } elseif ($container == '2') {
            $containerid = '7,10';
        } elseif ($container == '3') {
            $containerid = '6,4,10';
        } else {
            $containerid = '6,4,10';
        }

        if ($jenisorder == '1') {
            $jenisorder = 'MUATAN';
        } elseif ($jenisorder == '2') {
            $jenisorder = 'BONGKARAN';
        } elseif ($jenisorder == '3') {
            $jenisorder = 'IMPORT';
        } elseif ($jenisorder == '4') {
            $jenisorder = 'EXPORT';
        } else {
            $jenisorder = $jenisorder;
        }

        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'OrderanEmklController';
        if ($proses == 'reload') {
            $temtabel = 'temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;

            $querydata = DB::connection($koneksi)->table('listtemporarytabel')->from(
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
                Schema::connection($koneksi)->dropIfExists($querydata->namatabel);
                DB::connection($koneksi)->table('listtemporarytabel')->where('id', $querydata->id)->delete();
            }

            DB::connection($koneksi)->table('listtemporarytabel')->insert(
                [
                    'class' => $class,
                    'namatabel' => $temtabel,
                    'modifiedby' => $user,
                    'created_at' => date('Y/m/d H:i:s'),
                    'updated_at' => date('Y/m/d H:i:s'),
                ]
            );
            Schema::connection($koneksi)->create($temtabel, function (Blueprint $table) {
                $table->string('nojob', 50)->nullable();
                $table->date('tgl')->nullable();
                $table->string('nocont', 1000)->nullable();
                $table->string('noseal', 1000)->nullable();
                $table->string('nospempty', 1000)->nullable();
                $table->string('nospfull', 1000)->nullable();
                $table->string('jenisorderan', 1000)->nullable();
                $table->longtext('pelanggan')->nullable();
                $table->integer('fidcontainer')->nullable();
                $table->longtext('fasalmuatan')->nullable();
            });


            $temphasil = '##temphasil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::connection($koneksi)->create($temphasil, function (Blueprint $table) {
                $table->string('nojob', 255)->nullable();
                $table->date('tgl')->nullable();
                $table->string('nocont', 1000)->nullable();
                $table->string('noseal', 1000)->nullable();
                $table->string('nospempty', 1000)->nullable();
                $table->string('nospfull', 1000)->nullable();
                $table->string('jenisorderan', 100)->nullable();
                $table->string('pelanggan', 100)->nullable();
                $table->integer('fidcontainer')->nullable();
                $table->string('fasalmuatan', 100)->nullable();
            });
            if ($jenisorder == 'MUATAN') {
                $query = DB::connection($koneksi)->table('tpreorderanmuatan')->from(DB::raw("tpreorderanmuatan as orderan with (readuncommitted)"))
                    ->select(
                        'orderan.fntrans as nojob',
                        'orderan.ftgl as tgl',
                        DB::raw("(case when isnull(c.FNtrans,'')='' then isnull(orderan.fnocont,'') else  isnull(c.fnocont,'') end) as nocont "),
                        DB::raw("(case when isnull(c.FNtrans,'')='' then isnull(orderan.fnoseal,'') else  isnull(c.fnoseal,'') end) as noseal "),
                        'c.FNoSP as nospempty',
                        'c.FNoSpFull as nospfull',
                        DB::raw("'MUATAN' AS jenisorderan"),
                        'b.fnshipper as pelanggan',
                        'orderan.fidcontainer as fidcontainer',
                        DB::raw("'' as fasalmuatan")
                    )
                    ->join(DB::raw("mshipper b with (readuncommitted)"), 'orderan.fidshipper', 'b.fidtrans')
                    ->leftjoin(DB::raw("torderanMuatan c with (readuncommitted)"), 'orderan.FNtransorder', 'c.FNtrans');

                    DB::connection($koneksi)->table($temphasil)->insertUsing([
                    'nojob',
                    'tgl',
                    'nocont',
                    'noseal',
                    'nospempty',
                    'nospfull',
                    'jenisorderan',
                    'pelanggan',
                    'fidcontainer',
                    'fasalmuatan',


                ], $query);
            } else if ($jenisorder == 'BONGKARAN') {
                $query = db::connection($koneksi)->table('torderanbongkaran')->from(DB::raw("torderanbongkaran as orderan with (readuncommitted)"))
                    ->select(
                        'orderan.fntrans as nojob',
                        'orderan.ftglproses as tgl',
                        'orderan.fnocont as nocont',
                        'orderan.fnoseal as noseal',
                        'orderan.FNoSpEmpty as nospempty',
                        'orderan.FNoSpFull as nospfull',
                        DB::raw("'BONGKARAN' AS jenisorderan"),
                        'orderan.fshipper as pelanggan',
                        'orderan.fidukurancontainer as fidcontainer',
                        DB::raw("'' as fasalmuatan")

                    );

                DB::connection($koneksi)->table($temphasil)->insertUsing([
                    'nojob',
                    'tgl',
                    'nocont',
                    'noseal',
                    'nospempty',
                    'nospfull',
                    'jenisorderan',
                    'pelanggan',
                    'fidcontainer',
                    'fasalmuatan',


                ], $query);
                // dd('test'); 

                $query = db::connection($koneksi)->table('tpreorderanmuatan')->from(DB::raw("tpreorderanmuatan as orderan with (readuncommitted)"))
                    ->select(
                        'orderan.fntrans as nojob',
                        'orderan.ftgl as tgl',
                        'orderan.fnocont as nocont',
                        'orderan.fnoseal as noseal',
                        DB::raw("'MUATAN' AS jenisorderan"),
                        'b.fnshipper as pelanggan',
                        'orderan.fidcontainer as fidcontainer',
                        DB::raw("isnull(orderan.fasalmuatan,'') as fasalmuatan")

                    )
                    ->join(DB::raw("mshipper b with (readuncommitted)"), 'orderan.fidshipper', 'b.fidtrans');
                // ->whereRaw("isnull(orderan.fasalmuatan,'')<>'MEDAN'");

                // dd($query->toSql());


                DB::connection($koneksi)->table($temphasil)->insertUsing([
                    'nojob',
                    'tgl',
                    'nocont',
                    'noseal',
                    'jenisorderan',
                    'pelanggan',
                    'fidcontainer',
                    'fasalmuatan',


                ], $query);
            } else if ($jenisorder == 'IMPORT') {
                $query = db::connection($koneksi)->table('torderanimportdetail')->from(DB::raw("torderanimportdetail as orderan with (readuncommitted)"))
                    ->select(
                        'orderan.fntrans as nojob',
                        'a.ftgl as tgl',
                        'orderan.fnocont as nocont',
                        'orderan.fnoseal as noseal',
                        'orderan.FSPEmpty as nospempty',
                        'orderan.FSPFull as nospfull',
                        DB::raw("'IMPORT' AS jenisorderan"),
                        'b.fnshipper as pelanggan',
                        'orderan.fidcontainer as fidcontainer',
                        DB::raw("'' as fasalmuatan")

                    )
                    ->join(DB::raw("torderanimport a with (readuncommitted)"), 'orderan.fntrans', 'a.fntrans')
                    ->join(DB::raw("mshipper b with (readuncommitted)"), 'a.fidshipper', 'b.fidtrans');

                DB::connection($koneksi)->table($temphasil)->insertUsing([
                    'nojob',
                    'tgl',
                    'nocont',
                    'noseal',
                    'nospempty',
                    'nospfull',
                    'jenisorderan',
                    'pelanggan',
                    'fidcontainer',
                    'fasalmuatan',


                ], $query);
            } else if ($jenisorder == 'EXPORT') {
                $query = db::connection($koneksi)->table('torderanexport')->from(DB::raw("torderanexport as orderan with (readuncommitted)"))
                    ->select(
                        'orderan.fntrans as nojob',
                        'orderan.ftgl as tgl',
                        'orderan.fnocont as nocont',
                        'orderan.fnoseal as noseal',
                        'orderan.fnosp as nospempty',
                        'orderan.fnospfull as nospfull',
                        DB::raw("'EXPORT' AS jenisorderan"),
                        'b.fnshipper as pelanggan',
                        'orderan.fidcontainer as fidcontainer',
                        DB::raw("'' as fasalmuatan")

                    )
                    ->join(DB::raw("mshipper b with (readuncommitted)"), 'orderan.fidshipper', 'b.fidtrans');

                DB::connection($koneksi)->table($temphasil)->insertUsing([
                    'nojob',
                    'tgl',
                    'nocont',
                    'noseal',
                    'nospempty',
                    'nospfull',
                    'jenisorderan',
                    'pelanggan',
                    'fidcontainer',
                    'fasalmuatan',

                ], $query);
            } else {
                $query = db::connection($koneksi)->table('tpreorderanmuatan')->from(DB::raw("tpreorderanmuatan as orderan with (readuncommitted)"))
                    ->select(
                        'orderan.fntrans as nojob',
                        'orderan.ftgl as tgl',
                        'orderan.fnocont as nocont',
                        'orderan.fnoseal as noseal',
                        DB::raw("'MUATAN' AS jenisorderan"),
                        'b.fnshipper as pelanggan',
                        'orderan.fidcontainer as fidcontainer',
                        DB::raw("'' as fasalmuatan")

                    )
                    ->join(DB::raw("mshipper b with (readuncommitted)"), 'orderan.fidshipper', 'b.fidtrans');

                DB::connection($koneksi)->table($temphasil)->insertUsing([
                    'nojob',
                    'tgl',
                    'nocont',
                    'noseal',
                    'jenisorderan',
                    'pelanggan',
                    'fidcontainer',
                    'fasalmuatan',

                ], $query);
            }

            $query = db::connection($koneksi)->table($temphasil)
                ->select(
                    'nojob',
                    'tgl',
                    'nocont',
                    'noseal',
                    'nospempty',
                    'nospfull',
                    'jenisorderan',
                    'pelanggan',
                    'fidcontainer',
                    'fasalmuatan',
                );
            DB::connection($koneksi)->table($temtabel)->insertUsing([
                'nojob',
                'tgl',
                'nocont',
                'noseal',
                'nospempty',
                'nospfull',
                'jenisorderan',
                'pelanggan',
                'fidcontainer',
                'fasalmuatan',
            ], $query);
        } else {
            $querydata = DB::connection($koneksi)->table('listtemporarytabel')->from(
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

        $query = DB::connection($koneksi)->table($temtabel)->from(
            db::raw($temtabel . " orderan")
        )
            ->select(
                'orderan.nojob',
                'orderan.tgl',
                'orderan.nocont',
                'orderan.noseal',
                'orderan.nospempty',
                'orderan.nospfull',
                'orderan.jenisorderan',
                'orderan.pelanggan',
                'orderan.fasalmuatan',
            );

        $this->sort($query);

        $this->filter($query, $jenisorder);
        if ($jenisorder == 'MUATAN') {
            $query->WhereRaw("(orderan.FidContainer in (" . $containerid . ")");
            $query->WhereRaw("year(orderan.tgl)=cast(right('" . $bulanjob . "',4) as integer) and month(orderan.tgl)=cast(left('" . $bulanjob . "',2) as integer))");
        } else if ($jenisorder == 'BONGKARAN') {
            $query->WhereRaw("(orderan.FidContainer in (" . $containerid . ")");
            $query->WhereRaw("year(orderan.tgl)=cast(right('" . $bulanjob . "',4) as integer) and month(orderan.tgl)=cast(left('" . $bulanjob . "',2) as integer))");
            $query->WhereRaw("orderan.Fasalmuatan not in ('MEDAN')");
        } else if ($jenisorder == 'IMPORT') {
            $query->WhereRaw("(orderan.FidContainer in (" . $containerid . ")");
            $query->WhereRaw("year(orderan.tgl)=cast(right('" . $bulanjob . "',4) as integer) and month(orderan.tgl)=cast(left('" . $bulanjob . "',2) as integer))");;
        } else if ($jenisorder == 'EXPORT') {
            $query->WhereRaw("(orderan.FidContainer in (" . $containerid . ")");
            $query->WhereRaw("year(orderan.tgl)=cast(right('" . $bulanjob . "',4) as integer) and month(orderan.tgl)=cast(left('" . $bulanjob . "',2) as integer))");;
        } else {
            $query->WhereRaw("(orderan.FidContainer in (" . $containerid . ")");
            $query->WhereRaw("year(orderan.tgl)=cast(right('" . $bulanjob . "',4) as integer) and month(orderan.tgl)=cast(left('" . $bulanjob . "',2) as integer))");;
        }
        $this->totalRows = $query->count();

        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->paginate($query);
        $data = $query->get();

        return $data;
    }

    public function getjob($job)
    {
        $query = db::connection('sqlsrvemkl')->table('tpreorderanmuatan')
            ->from(DB::raw("tpreorderanmuatan as orderan with (readuncommitted)"))
            ->select('orderan.ftgl as tgl')
            ->where('orderan.fntrans', '=', $job)
            ->first();

        if (isset($query)) {
            $data = $query->tgl;
            goto selesai;
        }

        $query = db::connection('sqlsrvemkl')->table('torderanbongkaran')
            ->from(DB::raw("torderanbongkaran as orderan with (readuncommitted)"))
            ->select('orderan.ftglproses as tgl')
            ->where('orderan.fntrans', '=', $job)
            ->first();

        if (isset($query)) {
            $data = $query->tgl;
            goto selesai;
        }

        $query = db::connection('sqlsrvemkl')->table('torderanexport')
            ->from(DB::raw("torderanexport as orderan with (readuncommitted)"))
            ->select('orderan.ftgl as tgl')
            ->where('orderan.fntrans', '=', $job)
            ->first();

        if (isset($query)) {
            $data = $query->tgl;
            goto selesai;
        }

        $query = db::connection('sqlsrvemkl')->table('torderanimport')
            ->from(DB::raw("torderanimport as orderan with (readuncommitted)"))
            // ->select(DB::raw("cast(format(orderan.ftgl,'yyyy-MM-dd') as date) as tgl"))
            ->select('orderan.ftgl as tgl')
            ->where('orderan.fntrans', '=', $job)
            ->first();

        if (isset($query)) {
            $data = $query->tgl;
            goto selesai;
        }


        $tgl = now();
        $data = date_format($tgl, "Y-m-d");
        selesai:
        return $data;
    }

    public function setRequestParameters()
    {
        $this->params = [
            'offset' => request()->offset ?? ((request()->page - 1) * request()->limit),
            'limit' => request()->limit ?? 10,
            'filters' => json_decode(request()->filters, true) ?? [],
            'sortIndex' => request()->sortIndex ?? 'nojob',
            'sortOrder' => request()->sortOrder ?? 'asc',
        ];
    }

    public function sort($query)
    {
        return $query->orderBy($this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $jenisorder, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($jenisorder == 'MUATAN') {
                            //     if ($filters['field'] == 'nojob') {
                            //         $query = $query->where('orderan.fntrans', 'LIKE', "%$filters[data]%");
                            //     } else if ($filters['field'] == 'tgl') {
                            //         $query = $query->where(db::Raw("format(orderan.ftgl,'dd-MM-yyyy')"), 'LIKE', "%$filters[data]%");
                            //     } else if ($filters['field'] == 'nocont') {
                            //         $query = $query->where('orderan.fnocont', 'LIKE', "%$filters[data]%");
                            //     } else if ($filters['field'] == 'noseal') {
                            //         $query = $query->where('orderan.fnoseal', 'LIKE', "%$filters[data]%");
                            //     } else if ($filters['field'] == 'jenisorderan') {
                            //         $query = $query->whereRaw("'MUATAN' LIKE '%$filters[data]%'");
                            //     } else if ($filters['field'] == 'pelanggan') {
                            //         $query = $query->where('b.fnshipper', 'LIKE', "%$filters[data]%");
                            //     } else {
                            $query = $query->where($filters['field'], 'LIKE', "%$filters[data]%");
                            // }
                        } else if ($jenisorder == 'BONGKARAN') {
                            // if ($filters['field'] == 'nojob') {
                            //     $query = $query->where('orderan.fntrans', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'tgl') {
                            //     $query = $query->where(db::Raw("format(orderan.ftglproses,'dd-MM-yyyy')"), 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'nocont') {
                            //     $query = $query->where('orderan.fnocont', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'noseal') {
                            //     $query = $query->where('orderan.fnoseal', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'jenisorderan') {
                            //     $query = $query->whereRaw("'BONGKARAN' LIKE '%$filters[data]%'");
                            // } else if ($filters['field'] == 'pelanggan') {
                            //     $query = $query->where('orderan.fshipper', 'LIKE', "%$filters[data]%");
                            // } else {
                            $query = $query->where($filters['field'], 'LIKE', "%$filters[data]%");
                            // }
                        } else if ($jenisorder == 'IMPORT') {
                            // if ($filters['field'] == 'nojob') {
                            //     $query = $query->where('orderan.fntrans', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'tgl') {
                            //     $query = $query->where(db::Raw("format(a.ftgl,'dd-MM-yyyy')"), 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'nocont') {
                            //     $query = $query->where('orderan.fnocont', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'noseal') {
                            //     $query = $query->where('orderan.fnoseal', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'jenisorderan') {
                            //     $query = $query->whereRaw("'IMPORT' LIKE '%$filters[data]%'");
                            // } else if ($filters['field'] == 'pelanggan') {
                            //     $query = $query->where('b.fnshipper', 'LIKE', "%$filters[data]%");
                            // } else {
                            $query = $query->where($filters['field'], 'LIKE', "%$filters[data]%");
                            // }
                        } else if ($jenisorder == 'EXPORT') {
                            // if ($filters['field'] == 'nojob') {
                            //     $query = $query->where('orderan.fntrans', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'tgl') {
                            //     $query = $query->where(db::Raw("format(orderan.ftgl,'dd-MM-yyyy')"), 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'nocont') {
                            //     $query = $query->where('orderan.fnocont', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'noseal') {
                            //     $query = $query->where('orderan.fnoseal', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'jenisorderan') {
                            //     $query = $query->whereRaw("'EXPORT' LIKE '%$filters[data]%'");
                            // } else if ($filters['field'] == 'pelanggan') {
                            //     $query = $query->where('b.fnshipper', 'LIKE', "%$filters[data]%");
                            // } else {
                            $query = $query->where($filters['field'], 'LIKE', "%$filters[data]%");
                            // }
                        } else {
                            // if ($filters['field'] == 'nojob') {
                            //     $query = $query->where('orderan.fntrans', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'tgl') {
                            //     $query = $query->where(db::Raw("format(orderan.ftgl,'dd-MM-yyyy')"), 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'nocont') {
                            //     $query = $query->where('orderan.fnocont', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'noseal') {
                            //     $query = $query->where('orderan.fnoseal', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'jenisorderan') {
                            //     $query = $query->whereRaw("'MUATAN' LIKE '%$filters[data]%'");
                            // } else if ($filters['field'] == 'pelanggan') {
                            //     $query = $query->where('b.fnshipper', 'LIKE', "%$filters[data]%");
                            // } else {
                            $query = $query->where($filters['field'], 'LIKE', "%$filters[data]%");
                            // }
                        }
                    }
                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($jenisorder == 'MUATAN') {
                            // if ($filters['field'] == 'nojob') {
                            //     $query = $query->OrwhereRaw("(orderan.fntrans like '%$filters[data]%'");
                            // } else if ($filters['field'] == 'tgl') {
                            //     $query = $query->Orwhere(db::Raw("format(orderan.ftgl,'dd-MM-yyyy')"), 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'nocont') {
                            //     $query = $query->Orwhere('orderan.fnocont', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'noseal') {
                            //     $query = $query->Orwhere('orderan.fnoseal', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'jenisorderan') {
                            //     $query = $query->oRwhereRaw("'MUATAN' LIKE '%$filters[data]%'");
                            // } else if ($filters['field'] == 'pelanggan') {
                            //     $query = $query->OrwhereRaw("b.fnshipper LIKE  '%$filters[data]%' )");
                            // } else {
                            $query = $query->Orwhere($filters['field'], 'LIKE', "%$filters[data]%");
                            // }
                        } else if ($jenisorder == 'BONGKARAN') {
                            // if ($filters['field'] == 'nojob') {
                            //     $query = $query->OrwhereRaw("(orderan.fntrans like '%$filters[data]%'");
                            // } else if ($filters['field'] == 'tgl') {
                            //     $query = $query->Orwhere(db::Raw("format(orderan.ftglproses,'dd-MM-yyyy')"), 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'nocont') {
                            //     $query = $query->Orwhere('orderan.fnocont', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'noseal') {
                            //     $query = $query->Orwhere('orderan.fnoseal', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'jenisorderan') {
                            //     $query = $query->oRwhereRaw("'BONGKARAN' LIKE '%$filters[data]%'");
                            // } else if ($filters['field'] == 'pelanggan') {
                            //     $query = $query->OrwhereRaw("orderan.fshipper LIKE  '%$filters[data]%' )");
                            // } else {
                            $query = $query->Orwhere($filters['field'], 'LIKE', "%$filters[data]%");
                            // }
                        } else if ($jenisorder == 'IMPORT') {
                            // if ($filters['field'] == 'nojob') {
                            //     $query = $query->OrwhereRaw("(orderan.fntrans like '%$filters[data]%'");
                            // } else if ($filters['field'] == 'tgl') {
                            //     $query = $query->Orwhere(db::Raw("format(a.ftgl,'dd-MM-yyyy')"), 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'nocont') {
                            //     $query = $query->Orwhere('orderan.fnocont', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'noseal') {
                            //     $query = $query->Orwhere('orderan.fnoseal', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'jenisorderan') {
                            //     $query = $query->oRwhereRaw("'IMPORT' LIKE '%$filters[data]%'");
                            // } else if ($filters['field'] == 'pelanggan') {
                            //     $query = $query->OrwhereRaw("b.fnshipper LIKE  '%$filters[data]%' )");
                            // } else {
                            $query = $query->Orwhere($filters['field'], 'LIKE', "%$filters[data]%");
                            // }
                        } else if ($jenisorder == 'EXPORT') {
                            // if ($filters['field'] == 'nojob') {
                            //     $query = $query->OrwhereRaw("(orderan.fntrans like '%$filters[data]%'");
                            // } else if ($filters['field'] == 'tgl') {
                            //     $query = $query->Orwhere(db::Raw("format(orderan.ftgl,'dd-MM-yyyy')"), 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'nocont') {
                            //     $query = $query->Orwhere('orderan.fnocont', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'noseal') {
                            //     $query = $query->Orwhere('orderan.fnoseal', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'jenisorderan') {
                            //     $query = $query->oRwhereRaw("'EXPORT' LIKE '%$filters[data]%'");
                            // } else if ($filters['field'] == 'pelanggan') {
                            //     $query = $query->OrwhereRaw("b.fnshipper LIKE  '%$filters[data]%' )");
                            // } else {
                            $query = $query->Orwhere($filters['field'], 'LIKE', "%$filters[data]%");
                            // }
                        } else {
                            // if ($filters['field'] == 'nojob') {
                            //     $query = $query->OrwhereRaw("(orderan.fntrans like '%$filters[data]%'");
                            // } else if ($filters['field'] == 'tgl') {
                            //     $query = $query->Orwhere(db::Raw("format(orderan.ftgl,'dd-MM-yyyy')"), 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'nocont') {
                            //     $query = $query->Orwhere('orderan.fnocont', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'noseal') {
                            //     $query = $query->Orwhere('orderan.fnoseal', 'LIKE', "%$filters[data]%");
                            // } else if ($filters['field'] == 'jenisorderan') {
                            //     $query = $query->oRwhereRaw("'MUATAN' LIKE '%$filters[data]%'");
                            // } else if ($filters['field'] == 'pelanggan') {
                            //     $query = $query->OrwhereRaw("b.fnshipper LIKE  '%$filters[data]%' )");
                            // } else {
                            $query = $query->Orwhere($filters['field'], 'LIKE', "%$filters[data]%");
                            // }   
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
}
