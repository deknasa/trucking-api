<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\ExistBank;
use App\Rules\ExistSupir;
use App\Rules\ValidasiHutangList;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class StorePendapatanSupirHeaderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        $tempkomisi = '##tempkomisi' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempkomisi, function ($table) {
            $table->integer('supir_id')->nullable();
            $table->double('komisi')->nullable();
            $table->double('gajikenek')->nullable();
        });

        // dd(request()->supir_id);
        for ($i = 0; $i < count($this->id_detail); $i++) {
            $supir_id =  $this->supirtrip[$i] ?? 0;
            $komisi =  $this->nominal_detail[$i] ?? 0;
            $gajikenek =  $this->gajikenek[$i] ?? 0;

            DB::table($tempkomisi)->insert(
                [
                    'supir_id' => $supir_id ,
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
        for ($i = 0; $i < count($this->supir_depo); $i++) {
            $supir_id =  $this->supir_depo[$i] ?? 0;
            $deposito =  $this->nominal_depo[$i] ?? 0;

            DB::table($tempkomisi)->insert(
                [
                    'supir_id' => $supir_id ,
                    'deposito' => $deposito,
                ]
            );
        }        

        $temppelunasanpinjaman = '##temppelunasanpinjaman' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppelunasanpinjaman, function ($table) {
            $table->integer('supir_id')->nullable();
            $table->double('pelunasanpinjaman')->nullable();
        });

        // dd(request()->supir_id);
        for ($i = 0; $i < count($this->pinj_id); $i++) {
            $supir_id =  $this->pinj_supir[$i] ?? 0;
            $pelunasanpinjaman =  $this->pinj_nominal[$i] ?? 0;

            DB::table($tempkomisi)->insert(
                [
                    'supir_id' => $supir_id ,
                    'pelunasanpinjaman' => $pelunasanpinjaman,
                ]
            );
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

        $parameter = new Parameter();
        $getBatas = $parameter->getBatasAwalTahun();
        $tglbatasawal = $getBatas->text;
        $tglbatasakhir = (date('Y') + 1) . '-01-01';

        $jumlahdetail = $this->jumlahdetail ?? 0;
        $bank_id = $this->bank_id;
        $ruleBank_id = [];
        if ($bank_id != null) {
            $ruleBank_id = [
                'bank_id' => ['required', 'numeric', 'min:1', new ExistBank()]
            ];
        } else if ($bank_id == null && $this->bank != '') {
            $ruleBank_id = [
                'bank_id' => ['required', 'numeric', 'min:1', new ExistBank()]
            ];
        }

        $supir_id = $this->supir_id;
        $rulesSupir_id = [];
        if ($supir_id != null) {
            $rulesSupir_id = [
                'supir_id' => ['required', 'numeric', 'min:1', new ExistSupir()]
            ];
        } else if ($supir_id == null && $this->supir != '') {
            $rulesSupir_id = [
                'supir_id' => ['required', 'numeric', 'min:1', new ExistSupir()]
            ];
        }

        $rules = [
            'tglbukti' => [
                'required',
                'before_or_equal:' . date('d-m-Y'),
                new DateTutupBuku()
            ],
            'bank' => 'required',
            'supir' => [
                new ValidasiHutangList($jumlahdetail)
            ],
            'tgldari' => [
                'required', 'date_format:d-m-Y',
                'before:' . $tglbatasakhir,
                new DateTutupBuku()
            ],
            'tglsampai' => [
                'required', 'date_format:d-m-Y',
                'before:' . $tglbatasakhir,
                'after_or_equal:' . $this->tgldari
            ],
        ];


        $rules = array_merge(
            $rules,
            $ruleBank_id
        );

        return $rules;
    }

    public function attributes()
    {
        return [
            'tglbukti' => 'tanggal bukti',
            'tgldari' => 'tanggal dari',
            'tglsampai' => 'tanggal sampai',
        ];
    }

    public function messages()
    {
        $tglbatasakhir = (date('Y') + 1) . '-01-01';
        return [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tgldari.before' => app(ErrorController::class)->geterror('NTLB')->keterangan . ' ' . $tglbatasakhir,
            'tglsampai.before' => app(ErrorController::class)->geterror('NTLB')->keterangan . ' ' . $tglbatasakhir,
        ];
    }
}
