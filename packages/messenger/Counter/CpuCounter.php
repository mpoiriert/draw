<?php

namespace Draw\Component\Messenger\Counter;

use Fidry\CpuCoreCounter\CpuCoreCounter;
use Fidry\CpuCoreCounter\NumberOfCpuCoreNotFound;

class CpuCounter
{
    public function count(): int
    {
        try {
            return (new CpuCoreCounter())
                ->getCount()
            ;
        } catch (NumberOfCpuCoreNotFound) {
            return 1;
        }
    }
}
