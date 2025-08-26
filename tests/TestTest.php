<?php

declare(strict_types=1);

namespace Tests;

use Exception as GlobalException;
use InvalidArgumentException;
use JsonException;
use Lion\Exceptions\Exception;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test as Testing;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use RuntimeException;
use Tests\Provider\ClassProvider;
use Tests\Provider\ExceptionProviderClass;
use Tests\Provider\TestProviderTrait;

class TestTest extends Test
{
    use TestProviderTrait;

    private const int BITS = 16;
    private const int X = 200;
    private const int Y = 150;
    private const string PROPERTY = 'bits';
    private const string FILE_NAME = 'image.png';
    private const string FILE_NAME_CUSTOM = 'custom.png';
    private const array JSON = [
        'name' => 'lion',
    ];
    private const string MESSAGE = 'Testing';
    private const string EXCP_MESSAGE = 'ERR';
    private const string EXCP_STATUS = 'session-error';
    private const int EXCP_CODE = 500;

    private ClassProvider $customClass;
    private string $tempDir;

    protected function setUp(): void
    {
        $this->customClass = new ClassProvider();

        $this->tempDir = sys_get_temp_dir() . '/lion_test_' . uniqid() . '/storage/';

        mkdir($this->tempDir, 0777, true);
    }

    protected function tearDown(): void
    {
        if (isset($this->tempDir) && is_dir($this->tempDir)) {
            $this->rmdirRecursively($this->tempDir);
        }
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function initReflectionTest(): void
    {
        $classObject = new class () {
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
    public function getPrivateMethodItDoesNotExistTest(): void
    {
        $this->expectException(ReflectionException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('Method errMethod does not exist in class Tests\Provider\ClassProvider.');

        $this->initReflection($this->customClass);

        $this->getPrivateMethod('errMethod');
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
    public function getPrivatePropertyItDoesNotExistTest(): void
    {
        $this->expectException(ReflectionException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage("Property 'errProperty' does not exist in class Tests\Provider\ClassProvider.");

        $this->initReflection($this->customClass);

        $this->getPrivateProperty('errProperty');
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
    public function setPrivatePropertyItDoesNotExistTest(): void
    {
        $this->expectException(ReflectionException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage("Property 'errProperty' does not exist in class Tests\Provider\ClassProvider.");

        $this->initReflection($this->customClass);

        $this->setPrivateProperty('errProperty', null);
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
    #[TestWith(['dir' => 'directory-1/'])]
    #[TestWith(['dir' => 'directory-2/'])]
    #[TestWith(['dir' => 'directory-3/'])]
    #[TestWith(['dir' => 'directory-4/'])]
    public function rmdirRecursivelyInvalidDirTest(string $dir): void
    {
        $dir = $this->tempDir . $dir;

        $this->assertFalse(is_dir($dir));

        $this->rmdirRecursively($dir);

        $this->assertFalse(is_dir($dir));
    }

    #[Testing]
    #[TestWith(['dir' => 'directory-1/'], 'case-0')]
    #[TestWith(['dir' => 'directory-2/'], 'case-1')]
    #[TestWith(['dir' => 'directory-3/'], 'case-2')]
    #[TestWith(['dir' => 'directory-4/'], 'case-3')]
    public function rmdirRecusivelyInvalidObjectsTest(string $dir): void
    {
        $dir = $this->tempDir . $dir;

        mkdir($dir);

        chmod($dir, 0000);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage("Unable to read directory: {$dir}.");

        try {
            @$this->rmdirRecursively($dir);
        } finally {
            chmod($dir, 0777);

            rmdir($dir);
        }
    }

    #[Testing]
    #[RunInSeparateProcess]
    #[TestWith(['dir' => 'directory-1/'], 'case-0')]
    #[TestWith(['dir' => 'directory-2/'], 'case-1')]
    #[TestWith(['dir' => 'directory-3/'], 'case-2')]
    #[TestWith(['dir' => 'directory-4/'], 'case-3')]
    public function rmdirRecursivelyUnlinkFailureTest(string $dir): void
    {
        $dir = $this->tempDir . $dir;

        mkdir($dir);

        $file = $dir . '/file.txt';

        file_put_contents($file, 'test');

        chmod($dir, 0555);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage("Failed to delete file: {$file}.");

        try {
            @$this->rmdirRecursively($dir);
        } finally {
            chmod($dir, 0755);

            if (is_file($file)) {
                unlink($file);
            }

            if (is_dir($dir)) {
                rmdir($dir);
            }
        }
    }

    #[Testing]
    public function rmdirRecursivelyWithSubdirectoriesTest(): void
    {
        $dir = $this->tempDir . '/parent';

        $subDir = $dir . '/child';

        mkdir($subDir, 0777, true);

        $file = $subDir . '/file.txt';

        file_put_contents($file, 'test');

        $this->rmdirRecursively($dir);

        $this->assertFalse(is_dir($dir));
        $this->assertFalse(is_dir($subDir));
        $this->assertFalse(is_file($file));
    }

    #[Testing]
    public function rmdirRecursivelyTest(): void
    {
        $this->rmdirRecursively($this->tempDir);

        $this->assertFalse(is_dir($this->tempDir));
    }

    #[Testing]
    public function createDirectoryTest(): void
    {
        $this->createDirectory($this->tempDir);

        $this->assertTrue(is_dir($this->tempDir));
    }

    #[Testing]
    #[RunInSeparateProcess]
    public function testCreateDirectoryFailure(): void
    {
        eval('
            namespace Lion\Test {
                function mkdir(string $directory, int $permissions = 0777, bool $recursive = false): bool
                {
                    return false;
                }
            }
        ');

        $dir = "/root/fake_dir_fail_" . uniqid();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Failed to create directory: {$dir}.");
        $this->expectExceptionCode(500);

        @$this->createDirectory($dir);
    }

    #[Testing]
    public function createImageDefaultValues(): void
    {
        $this->createImage(
            path: $this->tempDir
        );

        $this->assertFileExists($this->tempDir . self::FILE_NAME);
    }

    #[Testing]
    public function createImageCustomValues(): void
    {
        $this->createImage(
            width: self::X,
            height: self::Y,
            path: $this->tempDir,
            fileName: self::FILE_NAME_CUSTOM
        );

        $this->assertFileExists($this->tempDir . self::FILE_NAME_CUSTOM);
    }

    #[Testing]
    #[RunInSeparateProcess]
    public function createImageFailsToCreateDirectoryTest(): void
    {
        $path = '/root/fake_image_dir_' . uniqid();

        $fileName = 'test.png';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Failed to create directory: {$path}.");
        $this->expectExceptionCode(500);

        @$this->createImage(100, 100, $path, $fileName);

        restore_error_handler();
    }

    #[Testing]
    #[RunInSeparateProcess]
    public function createImageDirectoryNotWritableTest(): void
    {
        $path = sys_get_temp_dir() . '/fake_not_writable_' . uniqid();

        mkdir($path);

        chmod($path, 0555);

        $fileName = 'test.png';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("The directory is not writable: {$path}.");
        $this->expectExceptionCode(500);

        try {
            $this->createImage(100, 100, $path, $fileName);
        } finally {
            chmod($path, 0777);

            rmdir($path);
        }
    }

    #[Testing]
    #[RunInSeparateProcess]
    public function createImageImageResourceFailureTest(): void
    {
        eval('
            namespace Lion\Test {
                function imagecreatetruecolor(int $width, int $height): \GdImage|false
                {
                    return false;
                }
            }
        ');

        $path = sys_get_temp_dir() . '/fake_image_fail_' . uniqid();

        mkdir($path);

        $fileName = 'test.png';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to create image resource.');
        $this->expectExceptionCode(500);

        try {
            $this->createImage(100, 100, $path, $fileName);
        } finally {
            rmdir($path);
        }
    }

    #[Testing]
    #[RunInSeparateProcess]
    public function createImageColorAllocateFailureTest(): void
    {
        eval('
            namespace Lion\Test {
                function imagecolorallocate(\GdImage $image, int $red, int $green, int $blue): int|false
                {
                    return false;
                }
            }
        ');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to allocate color.');
        $this->expectExceptionCode(500);

        $this->createImage(10, 10, $this->tempDir, 'fail.png');
    }

    #[Testing]
    #[RunInSeparateProcess]
    public function createImageSaveFailureTest(): void
    {
        eval('
            namespace Lion\Test {
                function imagepng($image, $file) {
                    return false;
                }
            }
        ');

        $fileName = 'fail_save.png';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Failed to save image at: {$this->tempDir}{$fileName}.");
        $this->expectExceptionCode(500);

        $this->createImage(10, 10, $this->tempDir, $fileName);
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
        $this->expectExceptionMessage('Width and height must be greater than 0.');

        $this->createImage(width: $x, height: $y);
    }

    /**
     * @throws JsonException
     */
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
     * @param object $instance [Instance object]
     * @param array<int, class-string> $instances [List of instance objects]
     *
     * @return void
     */
    #[Testing]
    #[DataProvider('assertInstancesProvider')]
    public function assertInstancesTest(object $instance, array $instances): void
    {
        $this->assertInstances($instance, $instances);
    }

    #[Testing]
    #[RunInSeparateProcess]
    public function assertWithObStartFailureTest(): void
    {
        eval('
            namespace Lion\Test {
                function ob_start(): bool {
                    return false;
                }
            }
        ');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('Failed to start output buffering.');

        $this->assertWithOb('expected', function (): void {
            echo 'expected';
        });
    }

    #[Testing]
    #[RunInSeparateProcess]
    public function assertWithObGetCleanFailureTest(): void
    {
        eval('
            namespace Lion\Test {
                function ob_start(): bool {
                    return true;
                }
                function ob_get_clean(): bool {
                    return false;
                }
            }
        ');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('Failed to retrieve output buffer.');

        $this->assertWithOb('.', function (): void {
            echo '.';
        });
    }

    #[Testing]
    public function assertWithObTest(): void
    {
        $this->assertWithOb(self::MESSAGE, function (): void {
            echo(self::MESSAGE);
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
            throw new GlobalException(self::EXCP_MESSAGE, self::EXCP_CODE);
        });

        $this->assertIsObject($exception);
        $this->assertInstanceOf(GlobalException::class, $exception);
        $this->assertSame(self::EXCP_MESSAGE, $exception->getMessage());
        $this->assertSame(self::EXCP_CODE, $exception->getCode());
    }

    #[Testing]
    public function getExceptionFromApiIsNullTest(): void
    {
        $exception = $this->getExceptionFromApi(function (): void {
        });

        $this->assertNull($exception);
    }

    /**
     * @throws Exception If validation or instantiation of the exception fails.
     * @throws InvalidArgumentException If the configured exception class does not
     *  extend Throwable.
     */
    #[Testing]
    #[RunInSeparateProcess]
    public function expectLionExceptionWithInvalidExceptionClassTest(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The exception must be a subclass of Throwable.');
        $this->expectExceptionCode(500);

        $this
            ->exception('ClassItDoesNotExist')
            ->expectLionException();
    }


    /**
     * @throws Exception
     */
    #[Testing]
    public function expectLionExceptionIsString(): void
    {
        $customException = new ExceptionProviderClass(self::EXCP_MESSAGE, self::EXCP_STATUS, self::EXCP_CODE);

        $this
            ->exception($customException::class)
            ->exceptionMessage(self::EXCP_MESSAGE)
            ->exceptionStatus(self::EXCP_STATUS)
            ->exceptionCode(self::EXCP_CODE)
            ->expectLionException();
    }

    /**
     * @throws Exception
     */
    #[Testing]
    public function expectLionExceptionIsCallback(): void
    {
        $customException = new ExceptionProviderClass(self::EXCP_MESSAGE, self::EXCP_STATUS, self::EXCP_CODE);

        $this
            ->exception($customException::class)
            ->exceptionMessage(self::EXCP_MESSAGE)
            ->exceptionStatus(self::EXCP_STATUS)
            ->exceptionCode(self::EXCP_CODE)
            ->expectLionException(function () use ($customException): void {
                throw new $customException(
                    self::EXCP_MESSAGE,
                    self::EXCP_STATUS,
                    self::EXCP_CODE
                );
            });
    }

    #[Testing]
    public function exceptionTest(): void
    {
        $customException = new ExceptionProviderClass(self::EXCP_MESSAGE, self::EXCP_STATUS, self::EXCP_CODE);

        $this->assertInstances($this->exception($customException::class), [
            Test::class,
            TestCase::class
        ]);
    }

    #[Testing]
    public function exceptionMessageTest(): void
    {
        $this->assertInstances($this->exceptionMessage(self::EXCP_MESSAGE), [
            Test::class,
            TestCase::class
        ]);
    }

    #[Testing]
    public function exceptionStatusTest(): void
    {
        $this->assertInstances($this->exceptionStatus(self::EXCP_STATUS), [
            Test::class,
            TestCase::class
        ]);
    }

    #[Testing]
    public function exceptionCodeTest(): void
    {
        $this->assertInstances($this->exceptionCode(self::EXCP_CODE), [
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
    public function assertHeaderNotHasKeyEmptyKeyTest(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('Header name cannot be empty.');
        $this->assertHeaderNotHasKey('');
    }

    #[Testing]
    #[DataProvider('assertHeaderNotHasKeyProvider')]
    public function assertHeaderNotHasKeyTest(string $header, string $headerValue): void
    {
        $_SERVER[$header] = $headerValue;

        $this->assertArrayHasKey($header, $_SERVER);

        $this->assertHeaderNotHasKey($header);
    }

    #[Testing]
    public function assertHttpBodyNotHasKeyEmptyKeyTest(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('Superglobal key cannot be empty.');
        $this->assertHttpBodyNotHasKey('');
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
