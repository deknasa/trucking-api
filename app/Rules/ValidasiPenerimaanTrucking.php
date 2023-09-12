<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ValidasiPenerimaanTrucking implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
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
        $tempbukti = '##tempbukti' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempbukti, function ($table) {
            $table->string('nobukti',50)->nullable();
        });

        $id_detail = request()->nominal ?? 0;
        if ($id_detail != 0) {
            for ($i = 0; $i < count(request()->nominal); $i++) {
                $nobukti =  request()->pengeluarantruckingheader_nobukti[$i] ?? '';

                DB::table($tempbukti)->insert(
                    [
                        'nobukti' => $nobukti,
                    ]
                );
            }
        }

        $tempcoa = '##tempcoa' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempcoa, function ($table) {
            $table->string('coa',50)->nullable();
        });

        $querycoa=db::table($tempbukti)->from(db::raw($tempbukti." as a "))
        ->select ('b.coa')
        ->join(db::raw("pengeluarantruckingheader as b with (readuncommitted)"),'a.nobukti','b.nobukti')
        ->groupby('b.coa');

        DB::table($tempcoa)->insertUsing([
            'coa',
        ], $querycoa);

        $jumlah=db::table($tempcoa)->from(db::raw($tempcoa . " as a"))
        ->select(
            db::raw("count(a.coa) as jumlah")
        )->first()->jumlah ??0 ;
        $allowed = true;
        if ($jumlah>1) {
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
        return 'Untuk Pinjaman Posting dan NOn Posting jangan digabung';
    }
}
