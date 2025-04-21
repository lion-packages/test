<?php

declare(strict_types=1);

namespace Lion\Test;

use Closure;
use DateTime;
use Exception as GlobalException;
use InvalidArgumentException;
use Lion\Exceptions\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Throwable;

/**
 * TestCase extended abstract test class, allows you to write unit tests in PHP
 * using the PHPUnit framework
 *
 * @package Lion\Test
 */
abstract class Test extends TestCase
{
    /**
     * [Object of ReflectionClass class]
     *
     * @var ReflectionClass<object> $reflectionClass
     */
    private ReflectionClass $reflectionClass;

    /**
     * [Object that will be reflected]
     *
     * @var mixed $instance
     */
    private mixed $instance;

    /**
     * [Exception class]
     *
     * @var string $exception
     */
    private string $exception;

    /**
     * [Exception message]
     *
     * @var string $exceptionMessage
     */
    private string $exceptionMessage;

    /**
     * [Exception response status]
     *
     * @var string $exceptionStatus
     */
    private string $exceptionStatus;

    /**
     * [Exception code]
     *
     * @var int|string $exceptionCode
     */
    private int|string $exceptionCode;

    /**
     * Initializes the object to perform a reflection on a class
     *
     * @param mixed $instance [Object of any type that is subjected to
     * reflection]
     *
     * @return void
     *
     * @throws ReflectionException
     *
     * @codeCoverageIgnore
     */
    final protected function initReflection(mixed $instance): void
    {
        if (!is_object($instance)) {
            throw new ReflectionException('The provided instance is not an object', 500);
        }

        $this->instance = $instance;

        $className = get_class($this->instance);

        if (!class_exists($className)) {
            throw new ReflectionException('The class does not exist', 500);
        }

        $this->reflectionClass = new ReflectionClass($this->instance);
    }

    /**
     * Gets the private or protected methods of a reflected class
     *
     * @param string $method [Name of the private or protected method that you
     * want to get and execute]
     * @param array<int|string, mixed> $args [Optional parameter that allows you
     * to specify the arguments that will be passed to the method when it is
     * invoked]
     *
     * @return mixed
     *
     * @throws ReflectionException
     *
     * @infection-ignore-all
     */
    final protected function getPrivateMethod(string $method, array $args = []): mixed
    {
        /** @var object $instance */
        $instance = $this->instance;

        return $this->reflectionClass
            ->getMethod($method)
            ->invokeArgs($instance, $args);
    }

    /**
     * Gets the value of a private or protected property of a reflected class
     *
     * @param string $property [Name of the private or protected property
     * whose value you want to obtain]
     *
     * @return mixed
     *
     * @throws ReflectionException
     *
     * @infection-ignore-all
     */
    final protected function getPrivateProperty(string $property): mixed
    {
        /** @var object $instance */
        $instance = $this->instance;

        return $this->reflectionClass
            ->getProperty($property)
            ->getValue($instance);
    }

    /**
     * Sets the value of a private or protected property of a reflected class
     *
     * @param string $property [Name of the private or protected property whose
     * value you want to set]
     * @param mixed $value [Value to assign to the specified property]
     *
     * @return void
     *
     * @throws ReflectionException
     *
     * @infection-ignore-all
     */
    final protected function setPrivateProperty(string $property, mixed $value): void
    {
        /** @var object $instance */
        $instance = $this->instance;

        $this->reflectionClass
            ->getProperty($property)
            ->setValue($instance, $value);
    }

    /**
     * Delete a directory and all its contents recursively
     *
     * @param string $dir [Directory to be deleted recursively]
     *
     * @return void
     *
     * @infection-ignore-all
     */
    final protected function rmdirRecursively(string $dir): void
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);

            if (is_array($objects)) {
                foreach ($objects as $object) {
                    if ($object != '.' && $object != '..') {
                        $path = $dir . '/' . $object;

                        if (is_dir($path)) {
                            $this->rmdirRecursively($path);
                        } else {
                            unlink($path);
                        }
                    }
                }

                rmdir($dir);
            }
        }
    }

    /**
     * Create folders from a defined path
     *
     * @param string $directory [Indicates the path of the directory you want
     * to create]
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    final protected function createDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0777, true)) {
                throw new RuntimeException("Could not create directory: {$directory}", 500);
            }
        }
    }

    /**
     * Allows generating a blank image with specified dimensions and saving it
     * to a specific path with a given file name
     *
     * @param int $x [Represents the width of the image to be created]
     * @param int $y [Represents the height of the image to be created]
     * @param string $path [Directory path where the image is saved]
     * @param string $fileName [Name of the image file to be created]
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    final protected function createImage(
        int $x = 100,
        int $y = 100,
        string $path = './storage/',
        string $fileName = 'image.png'
    ): void {
        if ($x <= 0 || $y <= 0) {
            throw new InvalidArgumentException('Width and height must be greater than 0', 500);
        }

        $image = imagecreatetruecolor($x, $y);

        $color = imagecolorallocate($image, 255, 255, 255);

        if (!$color) {
            throw new RuntimeException('Failed to allocate color', 500);
        }

        imagefill($image, 0, 0, $color);

        imagepng($image, "{$path}{$fileName}");
    }

    /**
     * Assertion to test if a JSON object is identical to the defined array
     *
     * @param string $json [JSON string to parse and compare with the provided
     * data structure]
     * @param array<string, mixed> $options [Expected data structure expected to be present
     * in the JSON]
     *
     * @return void
     *
     * @infection-ignore-all
     */
    final protected function assertJsonContent(string $json, array $options): void
    {
        $jsonEncode = json_encode($options, JSON_PRETTY_PRINT);

        if (!is_string($jsonEncode)) {
            throw new RuntimeException('Failed to convert JSON to string', 500);
        }

        $this->assertJsonStringEqualsJsonString($json, $jsonEncode);
    }

    /**
     * Makes an assertion about the value of a specific property of a class
     *
     * @param string $property [Name of the property on which the assertion
     * will be made]
     * @param mixed $value [Expected value of the property]
     *
     * @return void
     *
     * @throws ReflectionException
     *
     * @infection-ignore-all
     */
    final protected function assertPropertyValue(string $property, mixed $value): void
    {
        $this->assertSame($value, $this->getPrivateProperty($property));
    }

    /**
     * Method to perform an assertion of an object to test if it is an
     * instance of that class
     *
     * @param mixed $instance [Object whose type you want to verify]
     * @param array<int, class-string> $instances [Array containing the names of
     * the classes with which you want to compare the object]
     *
     * @return void
     *
     * @infection-ignore-all
     */
    final protected function assertInstances(mixed $instance, array $instances): void
    {
        foreach ($instances as $class) {
            $this->assertInstanceOf($class, $instance);
        }
    }

    /**
     * Perform assertions implementing the use of outputs in the buffer with
     * ob_start
     *
     * @param string $output [Expected Output Message]
     * @param Closure $callback [Anonymous function to be executed within the
     * context of output buffering]
     *
     * @return string
     *
     * @infection-ignore-all
     */
    final protected function assertWithOb(string $output, Closure $callback): string
    {
        ob_start();

        $callback();

        $outputGetClean = ob_get_clean();

        $this->assertSame($output, $outputGetClean);

        return $outputGetClean;
    }

    /**
     * Gets a response string from the separation of a defined word
     *
     * @param string $message [Defined message]
     * @param string $messageSplit [Separation text]
     *
     * @return string
     *
     * @infection-ignore-all
     */
    final protected function getResponse(string $message, string $messageSplit): string
    {
        if ('' === $messageSplit) {
            throw new InvalidArgumentException('Separator cannot be an empty string', 500);
        }

        $split = explode($messageSplit, $message);

        return trim(end($split));
    }

    /**
     * Gets the exception object when consuming an API
     *
     * @param Closure $callback [Function that executes the exception]
     *
     * @return GlobalException|null
     *
     * @infection-ignore-all
     */
    final protected function getExceptionFromApi(Closure $callback): ?GlobalException
    {
        try {
            $callback();

            return null;
        } catch (GlobalException $e){
            return $e;
        }
    }

    /**
     * Run a process to validate if an exception is thrown
     *
     * @param Closure|null $callback [Function that is executed]
     *
     * @return void
     *
     * @throws Exception [If the process fails]
     *
     * @codeCoverageIgnore
     */
    final protected function expectLionException(?Closure $callback = null): void
    {
        if (null === $callback) {
            /** @var Exception $lionException */
            $lionException = new $this->exception(
                $this->exceptionMessage,
                $this->exceptionStatus,
                $this->exceptionCode
            );

            if (!is_subclass_of($this->exception, Throwable::class)) {
                throw new InvalidArgumentException('The exception must be a subclass of Throwable', 500);
            }

            $this->expectException($this->exception);
            $this->expectExceptionMessage($this->exceptionMessage);
            $this->assertSame($this->exceptionStatus, $lionException->getStatus());
            $this->expectExceptionCode($this->exceptionCode);

            throw $lionException;
        } else {
            try {
                $callback();
            } catch (Exception $e) {
                $this->assertSame($this->exception, $e::class);
                $this->assertSame($this->exceptionStatus, $e->getStatus());
                $this->assertSame($this->exceptionMessage, $e->getMessage());
                $this->assertSame($this->exceptionCode, $e->getCode());
            }
        }
    }


    /**
     * Initialize an exception
     *
     * @param string $exception [Exception class]
     *
     * @return Test
     *
     * @infection-ignore-all
     */
    final protected function exception(string $exception): Test
    {
        $this->exception = $exception;

        return $this;
    }

    /**
     * Initialize the exception message
     *
     * @param string $exceptionMessage [Exception message]
     *
     * @return Test
     *
     * @infection-ignore-all
     */
    final protected function exceptionMessage(string $exceptionMessage): Test
    {
        $this->exceptionMessage = $exceptionMessage;

        return $this;
    }

    /**
     * Initialize the response state of the exception
     *
     * @param string $exceptionStatus [Exception response status]
     *
     * @return Test
     *
     * @infection-ignore-all
     */
    final protected function exceptionStatus(string $exceptionStatus): Test
    {
        $this->exceptionStatus = $exceptionStatus;

        return $this;
    }

    /**
     * Initialize the exception code
     *
     * @param int|string $exceptionCode [Exception code]
     *
     * @return Test
     *
     * @infection-ignore-all
     */
    final protected function exceptionCode(int|string $exceptionCode): Test
    {
        $this->exceptionCode = $exceptionCode;

        return $this;
    }

    /**
     * Assert that a value is a date in the specified format.
     *
     * @param string $value [The value to check]
     * @param string $format [The date format to validate against (default is
     * 'Y-m-d')]
     *
     * @return void
     *
     * @infection-ignore-all
     */
    protected function assertIsDate(string $value, string $format = 'Y-m-d'): void
    {
        $date = DateTime::createFromFormat($format, $value);

        $isValidDate = $date && $date->format($format) === $value;

        $this->assertTrue($isValidDate, "Failed asserting that '{$value}' is a valid date in format '{$format}'.");
    }

    /**
     * Remove the $_SERVER header and assert if it does not exist
     *
     * @param string $header [Header name]
     *
     * @return void
     *
     * @infection-ignore-all
     */
    final protected function assertHeaderNotHasKey(string $header): void
    {
        unset($_SERVER[$header]);

        $this->assertArrayNotHasKey($header, $_SERVER);
    }

    /**
     * Removes the values of $_POST, $_GET, $_FILES, $_SERVER and asserts that
     * they do not exist
     *
     * @param string $key
     *
     * @return void
     *
     * @infection-ignore-all
     */
    final protected function assertHttpBodyNotHasKey(string $key): void
    {
        if (isset($_SERVER[$key])) {
            $this->assertHeaderNotHasKey($key);
        }

        if (isset($_GET[$key])) {
            unset($_GET[$key]);

            $this->assertArrayNotHasKey($key, $_GET);
        }

        if (isset($_POST[$key])) {
            unset($_POST[$key]);

            $this->assertArrayNotHasKey($key, $_POST);
        }

        if (isset($_FILES[$key])) {
            unset($_FILES[$key]);

            $this->assertArrayNotHasKey($key, $_FILES);
        }
    }
}
