<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReminderStok extends MyModel
{
    use HasFactory;
    public function get()
    {
        $this->setRequestParameters();
        $statusaktif = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', '=', 'STATUS AKTIF')
            ->where('text', '=', 'AKTIF')
            ->first();

        $sudahExp =  Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', '=', 'STATUS STOK MIN')
            ->where('text', '=', 'STOK DIBAWAH QTY MINIMUM')
            ->first();
        $hampirExp =  Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )
            ->where('grp', '=', 'STATUS STOK MIN')
            ->where('text', '=', 'STOK HAMPIR DIBAWAH QTY MINIMUM')
            ->first();


        $class = 'ReminderStokController';
        $user = auth('api')->user()->name;
        $proses = request()->proses ?? 'reload';

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
                $table->string('namastok', 1000)->nullable();
                $table->string('keterangan', 1000)->nullable();
                $table->double('qtymin')->nullable();
                $table->double('qty')->nullable();
                $table->integer('status')->nullable();
            });
            $getQuery = DB::table('stok')->from(DB::raw("stok with (readuncommitted)"))
                ->select(
                    'stok.id',
                    'stok.namastok',
                    'stok.keterangan',
                    'stok.qtymin',
                    'stokpersediaan.qty',
                    DB::raw("$sudahExp->id as status")
                )
                ->join(DB::raw("stokpersediaan with (readuncommitted)"), 'stokpersediaan.stok_id', 'stok.id')
                ->where('stok.qtymin', '!=', '0')
                ->where('stokpersediaan.gudang_id', '1')
                ->whereRaw("stokpersediaan.qty <= stok.qtymin")
                ->where('stok.statusaktif', $statusaktif->id);

            DB::table($temtabel)->insertUsing(['id', 'namastok', 'keterangan', 'qtymin', 'qty', 'status'], $getQuery);
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

        $forExport = request()->forExport ?? false;

        if ($forExport) {
            $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('text')
                ->where('grp', 'JUDULAN LAPORAN')
                ->where('subgrp', 'JUDULAN LAPORAN')
                ->first();
            $query = DB::table(DB::raw($temtabel))->from(
                DB::raw(DB::raw($temtabel) . " stok with (readuncommitted)")
            )
                ->select(
                    'stok.id',
                    'stok.namastok',
                    'stok.keterangan',
                    'stok.qtymin',
                    'stok.qty',
                    'parameter.memo as status',
                    DB::raw("'Laporan Reminder Stok' as judulLaporan"),
                    DB::raw("'" . $getJudul->text . "' as judul"),
                )
                ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'stok.status', 'parameter.id');
        } else {
            $query = DB::table(DB::raw($temtabel))->from(
                DB::raw(DB::raw($temtabel) . " stok with (readuncommitted)")
            )
                ->select(
                    'stok.id',
                    'stok.namastok',
                    'stok.keterangan',
                    'stok.qtymin',
                    'stok.qty',
                    'parameter.memo as status'
                )
                ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'stok.status', 'parameter.id');


            $this->filter($query);

            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->sort($query);
            $this->paginate($query);
        }

        $data = $query->get();

        return $data;
    }


    public function sort($query)
    {

        return $query->orderBy('stok.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'status') {
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw("stok.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'status') {
                                $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                            } else {
                                // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw("stok.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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
}
