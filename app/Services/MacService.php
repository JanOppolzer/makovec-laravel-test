<?php

namespace App\Services;

class MacService
{
    public function clean(string $mac = null): string|null
    {
        if (is_null($mac)) {
            return null;
        }

        $stripped = preg_replace('/(\W)/', '', $mac);
        $coloned = preg_replace('/(..)(..)(..)(..)(..)(..)/', '\1:\2:\3:\4:\5:\6', $stripped);
        $uppercased = strtoupper($coloned);

        return $uppercased;
    }
}
