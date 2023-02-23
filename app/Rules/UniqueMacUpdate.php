<?php

namespace App\Rules;

use App\Models\Device;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueMacUpdate implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $mac = strtolower(preg_replace('/(\W)/', '', $value));
        $id = request()->segment(2);

        if (Device::whereMac($mac)->whereNot('id', $id)->count() !== 0) {
            $fail(__('devices.uniqueness_required'));
        }
    }
}
