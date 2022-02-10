<?php

namespace App\Rules;

use App\Models\UserAcl;
use Illuminate\Contracts\Validation\Rule;

class NotExistsRule implements Rule
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
        $query = UserAcl::select('user_id')
            ->where('user_id', $value)
            ->first();

        if (!isset($query->user_id)) {
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
        return 'User id sudah pernah diinput';
    }
}
