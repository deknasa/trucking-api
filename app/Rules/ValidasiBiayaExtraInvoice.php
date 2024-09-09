<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ValidasiBiayaExtraInvoice implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $jobtrucking;
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
        if ($cabang == 'MAKASSAR') {
            $requestData = request()->detail;
            $query = db::table('a')->from(db::raw("OPENJSON ('$requestData')"))
                ->select(db::raw("[value]"))
                ->whereRaw("[key]='sp_id'")
                ->first();

            $query2 = db::table('a')->from(db::raw("OPENJSON ('$query->value')"))
                ->select(db::raw("[key],[value]"));

            $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temp, function ($table) {
                $table->bigInteger('key')->nullable();
                $table->bigInteger('value')->nullable();
            });
            DB::table($temp)->insertUsing(['key', 'value'], $query2);

            $query = db::table("suratpengantar")->from(db::raw("suratpengantar as a with (readuncommitted)"))
                ->select('a.nobukti', 'c.nobukti as biayaextrasupir_nobukti')
                ->join(db::raw("$temp as b with (readuncommitted)"), 'a.id', 'b.value')
                ->join(db::raw("biayaextrasupirheader as c with (readuncommitted)"), 'a.nobukti', 'c.suratpengantar_nobukti');

            $tempsp = '##tempsp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempsp, function ($table) {
                $table->string('nobukti')->nullable();
                $table->string('biayaextrasupir_nobukti')->nullable();
            });
            DB::table($tempsp)->insertUsing(['nobukti', 'biayaextrasupir_nobukti'], $query);

            $query = db::table("gajisupirdetail")->from(db::raw("gajisupirdetail as a with (readuncommitted)"))
                ->select(db::raw("a.suratpengantar_nobukti,a.biayaextrasupir_nobukti"))
                ->join(db::raw("$tempsp as b with (readuncommitted)"), 'a.suratpengantar_nobukti', 'b.nobukti')
                ->whereRaw("isnull(a.biayaextrasupir_nobukti,'')!=''");

            $tempbes = '##tempbes' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempbes, function ($table) {
                $table->string('suratpengantar_nobukti')->nullable();
                $table->string('biayaextrasupir_nobukti')->nullable();
            });
            DB::table($tempbes)->insertUsing(['suratpengantar_nobukti', 'biayaextrasupir_nobukti'], $query);

            $query = db::table($tempsp)->from(db::raw("$tempsp as a with (readuncommitted)"))
                ->select(db::raw("string_agg(c.jobtrucking, ', ') as jobtrucking"))
                ->leftJoin(db::raw("$tempbes as b with (readuncommitted)"), 'a.biayaextrasupir_nobukti', 'b.biayaextrasupir_nobukti')
                ->join(db::raw("suratpengantar as c with (readuncommitted)"), 'a.nobukti', 'c.nobukti')
                ->whereRaw("isnull(b.suratpengantar_nobukti,'')=''")
                ->groupBy('c.jobtrucking')
                ->first();
            if ($query != '') {
                $this->jobtrucking = $query->jobtrucking;
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
        return app(ErrorController::class)->geterror('BESTR')->keterangan . ' (' . $this->jobtrucking . ')';
    }
}
