<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ValidasiPendapatanSupir implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */

    public $supir;
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */

    public function passes($attribute, $value)
    {

        $tempkomisi = '##tempkomisi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempkomisi, function ($table) {
            $table->integer('supir_id')->nullable();
            $table->double('komisi')->nullable();
            $table->double('gajikenek')->nullable();
        });

        $requestData = json_decode(request()->detail, true);
        for ($i = 0; $i < count($requestData['id_detail']); $i++) {
            $supir_id =  $requestData['supirtrip'][$i] ?? 0;
            $komisi =  $requestData['nominal_detail'][$i] ?? 0;
            $gajikenek =  $requestData['gajikenek'][$i] ?? 0;

            DB::table($tempkomisi)->insert(
                [
                    'supir_id' => $supir_id,
                    'komisi' => $komisi,
                    'gajikenek' => $gajikenek,
                ]
            );
        }


        $temprekapkomisi = '##temprekapkomisi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekapkomisi, function ($table) {
            $table->integer('supir_id')->nullable();
            $table->double('komisi')->nullable();
            $table->double('gajikenek')->nullable();
        });

        $queryrekapkomisi = DB::table($tempkomisi)->from(
            DB::raw($tempkomisi . " as a")
        )
            ->select(
                'a.supir_id',
                db::raw("sum(a.komisi) as komisi"),
                db::raw("sum(a.gajikenek) as gajikenek")
            )
            ->groupby('a.supir_id');

        DB::table($temprekapkomisi)->insertUsing([
            'supir_id',
            'komisi',
            'gajikenek',
        ], $queryrekapkomisi);



        $tempdeposito = '##tempdeposito' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdeposito, function ($table) {
            $table->integer('supir_id')->nullable();
            $table->double('deposito')->nullable();
        });

        // dd(request()->supir_id);
        $supir_depo = request()->supir_depo ?? 0;

        if ($supir_depo != 0) {
            for ($i = 0; $i < count($supir_depo); $i++) {
                $supir_id =  request()->supir_depo[$i] ?? 0;
                $deposito =  request()->nominal_depo[$i] ?? 0;

                DB::table($tempdeposito)->insert(
                    [
                        'supir_id' => $supir_id,
                        'deposito' => $deposito,
                    ]
                );
            }
        }
        $temppelunasanpinjaman = '##temppelunasanpinjaman' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppelunasanpinjaman, function ($table) {
            $table->integer('supir_id')->nullable();
            $table->double('pelunasanpinjaman')->nullable();
        });

        $pinj_id = request()->pinj_id ?? 0;

        // dd(request()->supir_id);
        if ($pinj_id != 0) {
            for ($i = 0; $i < count($pinj_id); $i++) {
                $supir_id =  request()->pinj_supir[$i] ?? 0;
                $pelunasanpinjaman =  request()->pinj_nominal[$i] ?? 0;

                DB::table($temppelunasanpinjaman)->insert(
                    [
                        'supir_id' => $supir_id,
                        'pelunasanpinjaman' => $pelunasanpinjaman,
                    ]
                );
            }
        }


        $temprekappelunasanpinjaman = '##temprekappelunasanpinjaman' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temprekappelunasanpinjaman, function ($table) {
            $table->integer('supir_id')->nullable();
            $table->double('pelunasanpinjaman')->nullable();
        });

        $queryrekappelunasanpinjaman = DB::table($temppelunasanpinjaman)->from(
            DB::raw($temppelunasanpinjaman . " as a")
        )
            ->select(
                'a.supir_id',
                db::raw("sum(a.pelunasanpinjaman) as pelunasanpinjaman"),
            )
            ->groupby('a.supir_id');

        DB::table($temprekappelunasanpinjaman)->insertUsing([
            'supir_id',
            'pelunasanpinjaman',
        ], $queryrekappelunasanpinjaman);


        $query = DB::table("supir")->from(
            DB::raw("supir as a")
        )
            ->select(
                'a.id as supir_id',
                'a.namasupir as namasupir',
                db::raw("((isnull(a1.komisi,0)+isnull(a1.gajikenek,0))-isnull(b.deposito,0)-isnull(c.pelunasanpinjaman,0)) as nominal"),
            )
            ->leftjoin(db::raw($temprekapkomisi . " as a1"), 'a.id', 'a1.supir_id')
            ->leftjoin(db::raw($tempdeposito . " as b"), 'a.id', 'b.supir_id')
            ->leftjoin(db::raw($temprekappelunasanpinjaman . " as c"), 'a.id', 'c.supir_id')
            ->whereRaw("((isnull(a1.komisi,0)+isnull(a1.gajikenek,0))-isnull(b.deposito,0)-isnull(c.pelunasanpinjaman,0))<0")
            ->get();

        $allowed = true;
        if (count($query) > 0) {
            $query1 = json_decode($query, true);
            $this->supir = '';
            $hit = 0;
            foreach ($query1 as $item) {
                if ($hit == 0) {
                    $this->supir = $this->supir . $item['namasupir'];
                } else {
                    $this->supir = $this->supir . ',' . $item['namasupir'];
                }

                $hit = $hit + 1;
            }
            $allowed = false;
        }



        return $allowed;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Nominal Akhir Ada yang minus, proses tidak bisa dilanjutkan, Supir :' . $this->supir;
    }
}
