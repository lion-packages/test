<?php

declare(strict_types=1);

namespace Tests\Provider;

class ClassProvider
{
    private int $bits = 100;

    public function setBits(int $bits): void
    {
        $this->bits = $bits;
    }

    private function getBits(): int
    {
        return $this->bits;
    }

    /**
     * @phpstan-ignore-next-line
     */
    private function subtractBits(int $bits): void
    {
        $this->bits -= $bits;
    }

    /**
     * @phpstan-ignore-next-line
     */
    private function resultBits(int $bits): int
    {
        $this->bits -= $bits;

        return $this->getBits();
    }
}
