<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidasiReminderOliPersneling implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($param, $keterangan)
    {
        $this->kondisi = $param;
        $this->pesan = $keterangan;
    }

    public $kondisi;
    public $pesan;
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($this->kondisi == true) {
            return false;
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
        return ':attribute' . ' ' .  $this->pesan;
    }
}
