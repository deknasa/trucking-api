<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidasiKlaimSPKPGRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */

    protected $stok_id;
    protected $pengeluaranstok_nobukti;
    protected $penerimaanstok_nobukti;

    public function __construct(
        $stok_id,
        $pengeluaranstok_nobukti,
        $penerimaanstok_nobukti
    )
    {
        $this->stok_id = $stok_id;
        $this->pengeluaranstok_nobukti = $pengeluaranstok_nobukti;
        $this->penerimaanstok_nobukti = $penerimaanstok_nobukti;
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
        $combinations = [];
        $stokIds = $this->stok_id;
        $pengeluaranStokNobuktis = $this->pengeluaranstok_nobukti;
        $penerimaanStokNobuktis = $this->penerimaanstok_nobukti;

        // dd(
        //     $stokIds,
        //     $pengeluaranStokNobuktis,
        //     $penerimaanStokNobuktis
        // );
        foreach ($stokIds as $index => $stokId) {
            $pengeluaranStokNobukti = $pengeluaranStokNobuktis[$index]??'';
            $penerimaanStokNobukti = $penerimaanStokNobuktis[$index]??'';
            $combination = $stokId . '|' . $pengeluaranStokNobukti . '|' . $penerimaanStokNobukti;
            if (in_array($combination, $combinations)) {
                return false;
            } else {
                $combinations[] = $combination;
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
        return 'Stok dan nobukti ini sudah pernah di input ';
    }
}
