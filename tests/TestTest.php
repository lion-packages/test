<?php

declare(strict_types=1);

namespace Tests;

use LionTest\Test;

class TestTest extends Test
{
    const BITS = 16;
    const PROPIERTY = 'bits';
    const URL_PATH = './storage/example/';

    private mixed $customClass;

    protected function setUp(): void
    {
        $this->customClass = new class {
            private int $bits;

            public function setBits(int $bits): void
            {
                $this->bits = $bits;
            }
        };
    }

    public function testGetPrivateProperty(): void
    {
        $this->initReflection($this->customClass);
        $this->customClass->setBits(self::BITS);

        $this->assertSame(self::BITS, $this->getPrivateProperty(self::PROPIERTY));
    }

    public function testRmdirRecursively(): void
    {
        $this->createDirectory(self::URL_PATH);
        $this->rmdirRecursively(self::URL_PATH);

        $this->assertFalse(is_dir(self::URL_PATH));
    }

    public function testCreateDirectory(): void
    {
        $this->createDirectory(self::URL_PATH);

        $this->assertTrue(is_dir(self::URL_PATH));
    }
}
