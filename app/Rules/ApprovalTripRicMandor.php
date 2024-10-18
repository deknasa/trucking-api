<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApprovalTripRicMandor implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $trip;
    public $ric;
    public $error;
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

        $requestData = json_decode(request()->detail, true);
        if (count($requestData['nobukti']) > 0) {

            $requestData = request()->detail;
            $query = db::table('a')->from(db::raw("OPENJSON ('$requestData')"))
                ->select(db::raw("[value]"))
                ->whereRaw("[key]='nobukti'")
                ->first();

            $query2 = db::table('a')->from(db::raw("OPENJSON ('$query->value')"))
                ->select(db::raw("[key],[value]"))
                ->whereRaw("[value] like '%TRP%'");

            $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temp, function ($table) {
                $table->bigInteger('key')->nullable();
                $table->string('value')->nullable();
            });
            DB::table($temp)->insertUsing(['key', 'value'], $query2);

            $cekric = DB::table("$temp")->from(DB::raw("$temp as a with (readuncommitted)"))
                ->select(DB::raw("STRING_AGG(a.value, ', ') as datatrip"))
                ->leftJoin(DB::raw("gajisupirdetail as b with (readuncommitted)"), 'b.suratpengantar_nobukti', 'a.value')
                ->whereRaw("isnull(b.nobukti,'')=''")
                ->first();
            if ($cekric->datatrip != '') {
                $this->trip = $cekric->datatrip;
            }

            $query2 = db::table('a')->from(db::raw("OPENJSON ('$query->value')"))
                ->select(db::raw("[key],[value]"))
                ->whereRaw("[value] like '%RTT%'");

            $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temp, function ($table) {
                $table->bigInteger('key')->nullable();
                $table->string('value')->nullable();
            });
            DB::table($temp)->insertUsing(['key', 'value'], $query2);

            $cekric = DB::table("$temp")->from(DB::raw("$temp as a with (readuncommitted)"))
                ->select(DB::raw("STRING_AGG(a.value, ', ') as datatrip"))
                ->leftJoin(DB::raw("gajisupirdetail as b with (readuncommitted)"), 'b.ritasi_nobukti', 'a.value')
                ->whereRaw("isnull(b.nobukti,'')=''")
                ->first();
            if ($cekric->datatrip != '') {
                if ($this->trip != '') {
                    $this->trip .= ', ';
                }
                $this->trip .= $cekric->datatrip;
            }


            if ($this->trip == '') {
                // $statuscetak = (new Parameter())->cekId('STATUSCETAK', 'STATUSCETAK', 'CETAK');

                $query2 = db::table('a')->from(db::raw("OPENJSON ('$query->value')"))
                    ->select(db::raw("[key],[value]"))
                    ->whereRaw("[value] like '%TRP%'");

                $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($temp, function ($table) {
                    $table->bigInteger('key')->nullable();
                    $table->string('value')->nullable();
                });
                DB::table($temp)->insertUsing(['key', 'value'], $query2);
                $cekric = DB::table("$temp")->from(DB::raw("$temp as a with (readuncommitted)"))
                    ->select(DB::raw("c.nobukti"))
                    ->leftJoin(DB::raw("gajisupirdetail as b with (readuncommitted)"), 'b.suratpengantar_nobukti', 'a.value')
                    ->join(DB::raw("prosesgajisupirdetail as c with (readuncommitted)"), 'b.nobukti', 'c.gajisupir_nobukti')
                    ->groupBy('c.nobukti');

                $tempric = '##tempric' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempric, function ($table) {
                    $table->string('nobukti')->nullable();
                });
                DB::table($tempric)->insertUsing(['nobukti'], $cekric);
                $cekric1 = DB::table("$tempric")->from(DB::raw("$tempric as a with (readuncommitted)"))
                    ->select(DB::raw("STRING_AGG(a.nobukti, ', ') as datatrip"))
                    ->first();
                if ($cekric1->datatrip != '') {
                    $this->ric = $cekric1->datatrip;
                }

                $query2 = db::table('a')->from(db::raw("OPENJSON ('$query->value')"))
                    ->select(db::raw("[key],[value]"))
                    ->whereRaw("[value] like '%RTT%'");

                $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($temp, function ($table) {
                    $table->bigInteger('key')->nullable();
                    $table->string('value')->nullable();
                });
                DB::table($temp)->insertUsing(['key', 'value'], $query2);
                $cekric = DB::table("$temp")->from(DB::raw("$temp as a with (readuncommitted)"))
                    ->select(DB::raw("c.nobukti"))
                    ->leftJoin(DB::raw("gajisupirdetail as b with (readuncommitted)"), 'b.ritasi_nobukti', 'a.value')
                    ->join(db::raw("prosesgajisupirdetail as c with (readuncommitted)"), 'b.nobukti', 'c.gajisupir_nobukti')
                    ->groupBy('c.nobukti');

                $tempric = '##tempric' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempric, function ($table) {
                    $table->string('nobukti')->nullable();
                });
                DB::table($tempric)->insertUsing(['nobukti'], $cekric);
                $cekric2 = DB::table("$tempric")->from(DB::raw("$tempric as a with (readuncommitted)"))
                    ->select(DB::raw("STRING_AGG(a.nobukti, ', ') as datatrip"))
                    ->first();
                if ($cekric2->datatrip != '') {

                    $arrayA = array_filter(array_map('trim', explode(',', $cekric1->datatrip)));
                    $arrayB = array_filter(array_map('trim', explode(',', $cekric2->datatrip)));

                    // Menggabungkan kedua array dan menghilangkan nilai duplikat
                    $mergedArray = array_unique(array_merge($arrayA, $arrayB));

                    // Mengubah array kembali menjadi string
                    $c = implode(', ', $mergedArray);
                    if ($this->ric != '') {
                        $this->ric .= ', ';
                    }
                    $this->ric = $c;
                }
                if ($this->ric != '') {
                    $this->error = app(ErrorController::class)->geterror('SPOST')->keterangan . ' (<b>' . $this->ric . '</b>)';
                    return false;
                } else {
                     return true;
                }
            } else {
                $this->error = app(ErrorController::class)->geterror('NRIC')->keterangan . ' (<b>' . $this->trip . '</b>)';
                return false;
            }
        } else {
            $this->error = 'DATA ' . app(ErrorController::class)->geterror('WP')->keterangan;
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->error;
    }
}
