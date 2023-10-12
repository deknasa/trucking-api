<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ValidasiNominalSaldo implements Rule
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


        $pelunasannotadebet = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select('a.id')
            ->where('grp', 'PELUNASAN')
            ->where('subgrp', 'PELUNASAN')
            ->where('text', 'NOTA DEBET')
            ->first()->id ?? 0;

        if (request()->statuspelunasan == $pelunasannotadebet) {
            $tempnotadebetfifo = '##tempnotadebetfifo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempnotadebetfifo, function ($table) {
                $table->string('notadebet_nobukti', 100)->nullable();
                $table->double('nominal', 15, 2)->nullable();
            });



            $querynotadebetfifo = db::table('notadebetfifo')->from(db::raw("notadebetfifo a with (readuncommitted)"))
                ->select(
                    'a.notadebet_nobukti as nobukti',
                    db::raw("sum(a.nominal) as nominal"),
                )
                ->join(db::raw("notadebetheader b with (readuncommitted)"), 'a.notadebet_nobukti', 'b.nobukti')
                ->where('b.agen_id', '=',   request()->agen_id)
                ->groupBY('a.notadebet_nobukti');



            DB::table($tempnotadebetfifo)->insertUsing([
                'notadebet_nobukti',
                'nominal',
            ], $querynotadebetfifo);


            $nominalsisa = db::table('notadebetrincian')->from(db::raw("notadebetrincian a with (readuncommitted)"))
                ->select(
                    db::raw("sum(isnull(a.nominal,0)-isnull(b.nominal,0)) as nominal"),
                )
                ->leftjoin(db::raw($tempnotadebetfifo . " b "), 'a.nobukti', 'b.notadebet_nobukti')
                ->join(db::raw("notadebetheader c with (readuncommitted)"), 'a.nobukti', 'c.nobukti')
                ->where('c.agen_id', '=',   request()->agen_id)
                ->first()->nominal ?? 0;

            
            $nominalbuktiedit = db::table('pelunasanpiutangdetail')->from(db::raw("pelunasanpiutangdetail a with (readuncommitted)"))
                ->select(
                    db::raw("sum(a.nominal) as nominal"),
                )
                ->where('a.nobukti', '=',   request()->nobukti)
                ->first();

            if (isset($nominalbuktiedit))  {
                $nominaledit=$nominalbuktiedit->nominal ?? 0;
            } else {
                $nominaledit=0;
            }

            $nominalbayar = 0;
            for ($i = 0; $i < count(request()->piutang_id); $i++) {
                $bayar = request()->bayar[$i] ?? 0;
                $nominalbayar = $nominalbayar + $bayar;
            }

            $sisa=$nominalsisa+$nominaledit;
            // dd($sisa);



            if ($nominalbayar > $sisa) {
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Nominal Panjar Tidak Mencukupi ';
    }
}
