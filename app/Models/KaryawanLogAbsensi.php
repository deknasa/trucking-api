<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;


class KaryawanLogAbsensi extends MyModel
{
    use HasFactory;

    use HasFactory;
    protected $table = 'karyawanlogabsensi';

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
        $this->setRequestParameters();

        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'KaryawanLogAbsensiController';

        $aktif = request()->aktif ?? '';
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

            $tempkaryawanabsen = '##tempkaryawanabsen' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempkaryawanabsen, function ($table) {
                $table->integer('idabsen')->nullable();
                $table->string('karyawan', 1000)->nullable();
            });

            $querykaryawanabsen = DB::table('logabsensi')->from(
                db::raw("logabsensi a with (readuncommitted)")
            )
                ->select(
                    'a.id as idabsen',
                    DB::raw("max(a.personname) as karyawan")
                )
                ->groupBy('a.id');



            DB::table($tempkaryawanabsen)->insertUsing([
                'idabsen',
                'karyawan',
            ], $querykaryawanabsen);



            Schema::create($temtabel, function (Blueprint $table) {
                $table->id();
                $table->integer('idabsen')->nullable();
                $table->string('karyawan', 1000)->nullable();
                $table->date('tglresign')->nullable();
                $table->longText('statusaktif')->nullable();
            });

            $memoaktif = DB::table('parameter')->from(
                db::raw("parameter a with (readuncommitted")
            )
                ->select(
                    'memo'
                )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('subgrp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $querykaryawan = DB::table($tempkaryawanabsen)->from(
                db::raw($tempkaryawanabsen . " a with (readuncommitted)")
            )
                ->select(
                    'a.idabsen',
                    'a.karyawan',
                    db::raw("isnull(b.tglresign,null) as tglresign"),
                    db::raw("isnull(c.memo,'" . $memoaktif->memo . "') as statusaktif")
                )
                ->leftjoin(DB::raw("karyawanlogabsensi b with (readuncomitted"), 'a.idabsen', 'b.idabsen')
                ->leftjoin(DB::raw("parameter c with (readuncomitted"), 'b.statusaktif', 'c.id')
                ->orderBY('a.idabsen', 'asc');

            DB::table($temtabel)->insertUsing([
                'idabsen',
                'karyawan',
                'tglresign',
                'statusaktif',
            ], $querykaryawan);
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

        $query=DB::table($temtabel)->from(
            db::raw($temtabel. " a")
        )
        ->select(
            'idabsen',
            'karyawan',
            'tglresign',
            'statusaktif'
        );


        // dd(request()->forReport);

        $report = request()->forReport ?? false;


        $this->filter($query);

        if ($aktif == 'AKTIF') {
            $statusaktif = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )
                ->where('grp', '=', 'STATUS AKTIF')
                ->where('text', '=', 'AKTIF')
                ->first();

            $query->where('karyawan.statusaktif', '=', $statusaktif->id);
        }

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }
}
