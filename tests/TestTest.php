<?php

declare(strict_types=1);

namespace Tests;

use Exception as GlobalException;
use InvalidArgumentException;
use JsonSerializable;
use Lion\Exceptions\Exception;
use Lion\Exceptions\Traits\ExceptionTrait;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test as Testing;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Tests\Provider\ClassProvider;
use Tests\Provider\TestProviderTrait;

class TestTest extends Test
{
    use TestProviderTrait;

    private const int BITS = 16;
    private const int X = 200;
    private const int Y = 150;
    private const string PROPERTY = 'bits';
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

    private ClassProvider $customClass;

    protected function setUp(): void
    {
        $this->createDirectory(self::URL_PATH);

        $this->customClass = new ClassProvider();
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively(self::STORAGE);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function initReflectionTest(): void
    {
        $classObject = new class {
            /** @phpstan-ignore-next-line */
            private int $number;
        };

        $this->initReflection($classObject);
        $this->setPrivateProperty('number', 1);
        $this->assertSame(1, $this->getPrivateProperty('number'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function initReflectionValueIsNotObjectTest(): void
    {
        $this->expectException(ReflectionException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('The provided instance is not an object');

        $this->initReflection(1);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function getPrivateMethodTest(): void
    {
        $this->initReflection($this->customClass);

        $this->setPrivateProperty(self::PROPERTY, 100);

        $bits = $this->getPrivateMethod('getBits');

        $this->assertIsInt($bits);
        $this->assertSame(100, $bits);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function getPrivateMethodWithArguments(): void
    {
        $this->initReflection($this->customClass);

        $this->setPrivateProperty(self::PROPERTY, 100);

        $this->getPrivateMethod('subtractBits', [self::BITS]);

        $this->assertIsInt($this->getPrivateProperty(self::PROPERTY));
        $this->assertSame(84, $this->getPrivateProperty(self::PROPERTY));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function getPrivateMethodWithArgumentsAndReturn(): void
    {
        $this->initReflection($this->customClass);

        $this->setPrivateProperty(self::PROPERTY, 100);

        $result = $this->getPrivateMethod('resultBits', [self::BITS]);

        $this->assertIsInt($result);
        $this->assertSame(84, $result);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function getPrivatePropertyTest(): void
    {
        $this->initReflection($this->customClass);

        $this->customClass->setBits(self::BITS);

        $this->assertSame(self::BITS, $this->getPrivateProperty(self::PROPERTY));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setPrivatePropertyTest(): void
    {
        $this->initReflection($this->customClass);

        $this->setPrivateProperty(self::PROPERTY, self::BITS);

        $this->assertSame(self::BITS, $this->getPrivateProperty(self::PROPERTY));
    }

    #[Testing]
    public function rmdirRecursivelyTest(): void
    {
        $this->createDirectory(self::URL_PATH);

        $this->rmdirRecursively(self::URL_PATH);

        $this->assertFalse(is_dir(self::URL_PATH));
    }

    #[Testing]
    public function createDirectoryTest(): void
    {
        $this->createDirectory(self::URL_PATH);

        $this->assertTrue(is_dir(self::URL_PATH));
    }

    #[Testing]
    public function createImageDefaultValues(): void
    {
        $this->createImage();

        $this->assertFileExists(self::STORAGE . self::FILE_NAME);
    }

    #[Testing]
    public function createImageCustomValues(): void
    {
        $this->createImage(self::X, self::Y, self::URL_PATH, self::FILE_NAME_CUSTOM);

        $this->assertFileExists(self::URL_PATH . self::FILE_NAME_CUSTOM);
    }

    #[Testing]
    #[TestWith(['x' => 0, 'y' => 0])]
    #[TestWith(['x' => 0, 'y' => -1])]
    #[TestWith(['x' => 1, 'y' => 0])]
    #[TestWith(['x' => 1, 'y' => -1])]
    public function createImageWithException(int $x, int $y): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('Width and height must be greater than 0');

        $this->createImage($x, $y);
    }

    #[Testing]
    public function assertJsonContentTest(): void
    {
        /** @var non-empty-string $json */
        $json = json_encode(self::JSON);

        $this->assertJsonContent($json, [
            'name' => 'lion',
        ]);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function assertPropertyValueTest(): void
    {
        $this->initReflection($this->customClass);

        $this->setPrivateProperty('bits', self::BITS);

        $this->assertPropertyValue('bits', self::BITS);
    }

    /**
     * Match instances of an object
     *
     * @param mixed $instance [Instance object]
     * @param array<int, class-string> $instances [List of instance objects]
     *
     * @return void
     */
    #[Testing]
    #[DataProvider('assertInstancesProvider')]
    public function assertInstancesTest(mixed $instance, array $instances): void
    {
        $this->assertInstances($instance, $instances);
    }

    #[Testing]
    public function assertWithObTest(): void
    {
        $this->assertWithOb(self::MESSAGE, function (): void {
            echo (self::MESSAGE);
        });
    }

    #[Testing]
    #[DataProvider('getResponseProvider')]
    public function getResponseTest(string $text, string $split, string $return): void
    {
        $this->assertSame($return, $this->getResponse($text, $split));
    }

    #[Testing]
    public function getResponseSplitIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('Separator cannot be an empty string');

        $this->getResponse('response:', '');
    }

    /**
     * @throws GlobalException
     */
    #[Testing]
    public function getExceptionFromApiTest(): void
    {
        $exception = $this->getExceptionFromApi(function (): void {
            throw new GlobalException(self::EXCEPTION_MESSAGE, self::ERR_EXCEPTION_CODE);
        });

        $this->assertIsObject($exception);
        $this->assertInstanceOf(GlobalException::class, $exception);
        $this->assertSame(self::EXCEPTION_MESSAGE, $exception->getMessage());
        $this->assertSame(self::ERR_EXCEPTION_CODE, $exception->getCode());
    }

    #[Testing]
    public function getExceptionFromApiIsNullTest(): void
    {
        $exception = $this->getExceptionFromApi(function (): void {
        });

        $this->assertNull($exception);
    }

    /**
     * @throws Exception
     */
    #[Testing]
    public function expectLionExceptionIsString(): void
    {
        $customException = new class (
            self::ERR_EXCEPTION_MESSAGE,
            self::ERR_EXCEPTION_STATUS,
            self::ERR_EXCEPTION_CODE
        ) extends Exception implements JsonSerializable {
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
    #[Testing]
    public function expectLionExceptionIsCallback(): void
    {
        $customException = new class (
            self::ERR_EXCEPTION_MESSAGE,
            self::ERR_EXCEPTION_STATUS,
            self::ERR_EXCEPTION_CODE
        ) extends Exception implements JsonSerializable {
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

    #[Testing]
    public function exceptionTest(): void
    {
        $customException = new class (
            self::ERR_EXCEPTION_MESSAGE,
            self::ERR_EXCEPTION_STATUS,
            self::ERR_EXCEPTION_CODE
        ) extends Exception implements JsonSerializable {
            use ExceptionTrait;
        };

        $this->assertInstances($this->exception($customException::class), [
            Test::class,
            TestCase::class
        ]);
    }

    #[Testing]
    public function exceptionMessageTest(): void
    {
        $this->assertInstances($this->exceptionMessage(self::EXCEPTION_MESSAGE), [
            Test::class,
            TestCase::class
        ]);
    }

    #[Testing]
    public function exceptionStatusTest(): void
    {
        $this->assertInstances($this->exceptionStatus(self::ERR_EXCEPTION_STATUS), [
            Test::class,
            TestCase::class
        ]);
    }

    #[Testing]
    public function exceptionCodeTest(): void
    {
        $this->assertInstances($this->exceptionCode(self::ERR_EXCEPTION_CODE), [
            Test::class,
            TestCase::class
        ]);
    }

    #[Testing]
    #[DataProvider('assertIsDateProvider')]
    public function assertIsDateTest(string $date, string $format): void
    {
        $this->assertIsDate($date, $format);
    }

    #[Testing]
    #[DataProvider('assertHeaderNotHasKeyProvider')]
    public function assertHeaderNotHasKeyTest(string $header, string $headerValue): void
    {
        $_SERVER[$header] = $headerValue;

        $this->assertArrayHasKey($header, $_SERVER);

        $this->assertHeaderNotHasKey($header);
    }

    /**
     * Match and strip values from values sent over HTTP
     *
     * @param string $key [Body index]
     * @param string $global [Global variable]
     * @param array<string, string>|string $value [Value of the body]
     *
     * @return void
     */
    #[Testing]
    #[DataProvider('assertHttpBodyNotHasKeyProvider')]
    public function assertHttpBodyNotHasKeyTest(string $key, string $global, array|string $value): void
    {
        global $$global;

        /** @phpstan-ignore-next-line */
        $$global[$key] = $value;

        $this->assertHttpBodyNotHasKey($key);
        /** @phpstan-ignore-next-line */
        $this->assertArrayNotHasKey($key, $$global);
    }
}
