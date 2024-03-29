<?php

declare(strict_types=1);

namespace Tests;

use Exception;
use Lion\Test\Test;
use Tests\Provider\TestProviderTrait;

class TestTest extends Test
{
    use TestProviderTrait;

    const BITS = 16;
    const X = 200;
    const Y = 150;
    const PROPIERTY = 'bits';
    const STORAGE = './storage/';
    const URL_PATH = self::STORAGE . 'example/';
    const FILE_NAME = 'image.png';
    const FILE_NAME_CUSTOM = 'custom.png';
    const JSON = ['name' => 'lion'];
    const MESSAGE = 'Testing';
    const EXCEPTION_MESSAGE = 'Exception in the tests';

    private mixed $customClass;

    protected function setUp(): void
    {
        $this->createDirectory(self::URL_PATH);

        $this->customClass = new class {
            private int $bits = 100;

            public function setBits(int $bits): void
            {
                $this->bits = $bits;
            }

            private function getBits(): int
            {
                return $this->bits;
            }

            private function subtractBits(int $bits): void
            {
                $this->bits -= $bits;
            }

            private function resultBits(int $bits): int
            {
                $this->bits -= $bits;

                return $this->getBits();
            }
        };
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively(self::STORAGE);
    }

    public function testGetPrivateMethod(): void
    {
        $this->initReflection($this->customClass);
        $this->setPrivateProperty(self::PROPIERTY, 100);
        $bits = $this->getPrivateMethod('getBits');

        $this->assertIsInt($bits);
        $this->assertSame(100, $bits);
    }

    public function testGetPrivateMethodWithArguments(): void
    {
        $this->initReflection($this->customClass);
        $this->setPrivateProperty(self::PROPIERTY, 100);
        $this->getPrivateMethod('subtractBits', [self::BITS]);

        $this->assertIsInt($this->getPrivateProperty(self::PROPIERTY));
        $this->assertSame(84, $this->getPrivateProperty(self::PROPIERTY));
    }

    public function testGetPrivateMethodWithArgumentsAndReturn(): void
    {
        $this->initReflection($this->customClass);
        $this->setPrivateProperty(self::PROPIERTY, 100);
        $result = $this->getPrivateMethod('resultBits', [self::BITS]);

        $this->assertIsInt($result);
        $this->assertSame(84, $result);
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

    public function testAssertJsonContent(): void
    {
        $this->assertJsonContent(json_encode(self::JSON), ['name' => 'lion']);
    }

    public function testAssertPropertyValue(): void
    {
        $this->initReflection($this->customClass);
        $this->setPrivateProperty('bits', self::BITS);

        $this->assertPropertyValue('bits', self::BITS);
    }

    /**
     * @dataProvider assertInstancesProvider
     * */
    public function testAssertInstances(object $instance, array|string $instances): void
    {
        $this->assertInstances($instance, $instances);
    }

    public function testAssertWithOb(): void
    {
        $this->assertWithOb(self::MESSAGE, function() {
            echo(self::MESSAGE);
        });
    }

    /**
     * @dataProvider getResponseProvider
     */
    public function testGetResponse(string $text, string $split, string $return): void
    {
        $this->assertSame($return, $this->getResponse($text, $split));
    }

    public function testGetExceptionFromApi(): void
    {
        $exception = $this->getExceptionFromApi(function() {
            throw new Exception(self::EXCEPTION_MESSAGE);
        });

        $this->assertSame(self::EXCEPTION_MESSAGE, $exception->getMessage());
    }
}
