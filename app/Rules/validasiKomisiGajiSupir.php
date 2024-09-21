<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class validasiKomisiGajiSupir implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $notrip;
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

        $cabang = (new Parameter())->cekText('CABANG', 'CABANG');
        if ($cabang == 'MEDAN') {
            $requestData = json_encode(request()->rincian_nobukti);
            $query = db::table('a')->from(DB::raw("OPENJSON ('$requestData')"))
                ->select(db::raw("[value]"))
                ->groupBy('value');

            $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temp, function ($table) {
                $table->string('value')->nullable();
            });
            DB::table($temp)->insertUsing(['value'], $query);

            $final = db::table("suratpengantar")->from(db::raw("suratpengantar as a with (readuncommitted)"))
                ->select(db::raw("STRING_AGG(a.nobukti, ', ') as nobukti"))
                ->join(db::raw("$temp as b with (readuncommitted)"), 'a.nobukti', 'b.value')
                ->join(db::raw("upahsupirrincian as c with (readuncommitted)"), 'a.upah_id', 'c.upahsupir_id')
                ->whereRaw("c.container_id = a.container_id and c.statuscontainer_id=a.statuscontainer_id and a.komisisupir!=c.nominalkomisi")
                ->first();
            if ($final->nobukti != '') {
                $this->notrip = $final->nobukti;
                return false;
            }
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return app(ErrorController::class)->geterror('NTC')->keterangan . ', komisi dengan master (' . $this->notrip . ')';
    }
}
