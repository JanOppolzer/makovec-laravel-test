<?php

namespace App\Rules;

use App\Models\Device;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueMacStore implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $mac = strtolower(preg_replace('/(\W)/', '', $value));

        if (Device::whereMac($mac)->count() !== 0) {
            $fail(__('devices.uniqueness_required'));
        }
    }
}
