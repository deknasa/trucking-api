<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class validasiJobBedaBulan implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $nojob;
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
        $requestData = request()->detail;
        $tglbukti = request()->tglbukti;
        $tgldari = request()->tgldari;
        if (date('m', strtotime($tglbukti)) != date('m', strtotime($tgldari))) {
            $query = DB::table('a')->from(db::raw("OPENJSON ('$requestData')"))
                ->select(db::raw("[value]"))
                ->whereRaw("[key]='nojobemkl'")
                ->first();

            $query2 = db::table('a')->from(db::raw("OPENJSON ('$query->value')"))
                ->select(db::raw("[key],[value]"));
            $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temp, function ($table) {
                $table->bigInteger('key')->nullable();
                $table->string('value')->nullable();
            });
            DB::table($temp)->insertUsing(['key', 'value'], $query2);
            $query = DB::table($temp)->from(db::raw("$temp as a with (readuncommitted)"))
            ->select(db::raw("string_agg(a.value, ', ') as nobukti"))
            ->leftJoin(db::raw("jurnalumumheader as b with (readuncommitted)"), 'a.value', 'b.nobukti')
            ->whereRaw("isnull(b.nobukti,'')=''")
            ->first();

            if ($query != '') {
                if($query->nobukti != ''){
                    $this->nojob = $query->nobukti;
                    return false;
                }
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
        return app(ErrorController::class)->geterror('BPI')->keterangan . ' nilai awal (' . $this->nojob . ')';
    }
}
