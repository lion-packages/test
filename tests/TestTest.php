<?php

declare(strict_types=1);

namespace Tests;

use LionTest\Test;

class TestTest extends Test
{
    const BITS = 16;
    const X = 200;
    const Y = 150;
    const PROPIERTY = 'bits';
    const STORAGE = './storage/';
    const URL_PATH = self::STORAGE . 'example/';
    const FILE_NAME = 'image.png';
    const FILE_NAME_CUSTOM = 'custom.png';

    private mixed $customClass;

    protected function setUp(): void
    {
        $this->createDirectory(self::URL_PATH);

        $this->customClass = new class {
            private int $bits;

            public function setBits(int $bits): void
            {
                $this->bits = $bits;
            }
        };
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively(self::STORAGE);
    }

    public function testGetPrivateProperty(): void
    {
        $this->initReflection($this->customClass);
        $this->customClass->setBits(self::BITS);

        $this->assertSame(self::BITS, $this->getPrivateProperty(self::PROPIERTY));
    }

    public function testSetPrivateProperty(): void
    {
        $this->initReflection($this->customClass);
        $this->setPrivateProperty(self::PROPIERTY, self::BITS);

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

    public function testCreateImageDefaultValues(): void
    {
        $this->createImage();

        $this->assertFileExists(self::STORAGE . self::FILE_NAME);
    }

    public function testCreateImageCustomValues(): void
    {
        $this->createImage(self::X, self::Y, self::URL_PATH, self::FILE_NAME_CUSTOM);

        $this->assertFileExists(self::URL_PATH . self::FILE_NAME_CUSTOM);
    }
}
