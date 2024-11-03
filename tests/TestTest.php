<?php

declare(strict_types=1);

namespace Tests;

use DateTime;
use Exception as GlobalException;
use JsonSerializable;
use Lion\Exceptions\Exception;
use Lion\Exceptions\Traits\ExceptionTrait;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Tests\Provider\TestProviderTrait;

class TestTest extends Test
{
    use TestProviderTrait;

    private const int BITS = 16;
    private const int X = 200;
    private const int Y = 150;
    private const string PROPIERTY = 'bits';
    private const string STORAGE = './storage/';
    private const string URL_PATH = self::STORAGE . 'example/';
    private const string FILE_NAME = 'image.png';
    private const string FILE_NAME_CUSTOM = 'custom.png';
    private const array JSON = ['name' => 'lion'];
    private const string MESSAGE = 'Testing';
    private const string EXCEPTION_MESSAGE = 'Exception in the tests';
    private const string ERR_EXCEPTION_MESSAGE = 'ERR';
    private const string ERR_EXCEPTION_STATUS = 'session-error';
    private const int ERR_EXCEPTION_CODE = 500;

    private mixed $customClass;

    protected function setUp(): void
    {
        $this->createDirectory(self::URL_PATH);

        $this->customClass = new class
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

    /**
     * @throws ReflectionException
     */
    public function testGetPrivateMethod(): void
    {
        $this->initReflection($this->customClass);

        $this->setPrivateProperty(self::PROPIERTY, 100);

        $bits = $this->getPrivateMethod('getBits');

        $this->assertIsInt($bits);
        $this->assertSame(100, $bits);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetPrivateMethodWithArguments(): void
    {
        $this->initReflection($this->customClass);

        $this->setPrivateProperty(self::PROPIERTY, 100);

        $this->getPrivateMethod('subtractBits', [self::BITS]);

        $this->assertIsInt($this->getPrivateProperty(self::PROPIERTY));
        $this->assertSame(84, $this->getPrivateProperty(self::PROPIERTY));
    }

    /**
     * @throws ReflectionException
     */
    public function testGetPrivateMethodWithArgumentsAndReturn(): void
    {
        $this->initReflection($this->customClass);

        $this->setPrivateProperty(self::PROPIERTY, 100);

        $result = $this->getPrivateMethod('resultBits', [self::BITS]);

        $this->assertIsInt($result);
        $this->assertSame(84, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetPrivateProperty(): void
    {
        $this->initReflection($this->customClass);

        $this->customClass->setBits(self::BITS);

        $this->assertSame(self::BITS, $this->getPrivateProperty(self::PROPIERTY));
    }

    /**
     * @throws ReflectionException
     */
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

    /**
     * @throws ReflectionException
     */
    public function testAssertPropertyValue(): void
    {
        $this->initReflection($this->customClass);

        $this->setPrivateProperty('bits', self::BITS);

        $this->assertPropertyValue('bits', self::BITS);
    }

    #[DataProvider('assertInstancesProvider')]
    public function testAssertInstances(object $instance, array|string $instances): void
    {
        $this->assertInstances($instance, $instances);
    }

    public function testAssertWithOb(): void
    {
        $this->assertWithOb(self::MESSAGE, function (): void {
            echo (self::MESSAGE);
        });
    }

    #[DataProvider('getResponseProvider')]
    public function testGetResponse(string $text, string $split, string $return): void
    {
        $this->assertSame($return, $this->getResponse($text, $split));
    }

    /**
     * @throws GlobalException
     */
    public function testGetExceptionFromApi(): void
    {
        $exception = $this->getExceptionFromApi(function (): void {
            throw new GlobalException(self::EXCEPTION_MESSAGE, self::ERR_EXCEPTION_CODE);
        });

        $this->assertIsObject($exception);
        $this->assertInstanceOf(GlobalException::class, $exception);
        $this->assertSame(self::EXCEPTION_MESSAGE, $exception->getMessage());
        $this->assertSame(self::ERR_EXCEPTION_CODE, $exception->getCode());
    }

    /**
     * @throws Exception
     */
    public function testExpectLionExceptionIsString(): void
    {
        $customException = new class extends Exception implements JsonSerializable
        {
            use ExceptionTrait;
        };

        $this
            ->exception($customException::class)
            ->exceptionMessage(self::ERR_EXCEPTION_MESSAGE)
            ->exceptionStatus(self::ERR_EXCEPTION_STATUS)
            ->exceptionCode(self::ERR_EXCEPTION_CODE)
            ->expectLionException();
    }

    /**
     * @throws Exception
     */
    public function testExpectLionExceptionIsCallback(): void
    {
        $customException = new class extends Exception implements JsonSerializable
        {
            use ExceptionTrait;
        };

        $this
            ->exception($customException::class)
            ->exceptionMessage(self::ERR_EXCEPTION_MESSAGE)
            ->exceptionStatus(self::ERR_EXCEPTION_STATUS)
            ->exceptionCode(self::ERR_EXCEPTION_CODE)
            ->expectLionException(function () use ($customException): void {
                throw new $customException(
                    self::ERR_EXCEPTION_MESSAGE,
                    self::ERR_EXCEPTION_STATUS,
                    self::ERR_EXCEPTION_CODE
                );
            });
    }

    public function testException(): void
    {
        $customException = new class extends Exception implements JsonSerializable
        {
            use ExceptionTrait;
        };

        $this->assertInstances($this->exception($customException::class), [
            Test::class,
            TestCase::class
        ]);
    }

    public function testExceptionMessage(): void
    {
        $this->assertInstances($this->exceptionMessage(self::EXCEPTION_MESSAGE), [
            Test::class,
            TestCase::class
        ]);
    }

    public function testExceptionStatus(): void
    {
        $this->assertInstances($this->exceptionStatus(self::ERR_EXCEPTION_STATUS), [
            Test::class,
            TestCase::class
        ]);
    }

    public function testExceptionCode(): void
    {
        $this->assertInstances($this->exceptionCode(self::ERR_EXCEPTION_CODE), [
            Test::class,
            TestCase::class
        ]);
    }

    #[DataProvider('assertIsDateProvider')]
    public function testAssertIsDate(string $date, string $format): void
    {
        $this->assertIsDate($date, $format);
    }

    #[DataProvider('assertHeaderNotHasKeyProvider')]
    public function testAssertHeaderNotHasKey(string $header, string $headerValue): void
    {
        $_SERVER[$header] = $headerValue;

        $this->assertArrayHasKey($header, $_SERVER);

        $this->assertHeaderNotHasKey($header);
    }
}
