<?php

declare(strict_types=1);

namespace Lion\Test;

use Closure;
use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

/**
 * TestCase extended abstract test class, allows you to write unit tests in PHP
 * using the PHPUnit framework.
 *
 * @property object $instance [Object that will be reflected]
 * @property ReflectionClass $reflectionClass [Object of ReflectionClass class]
 *
 * @package Lion\Test
 */
abstract class Test extends TestCase
{
    /**
     * [Object that will be reflected]
     *
     * @var object $instance
     */
    private object $instance;

    /**
     * [Object of ReflectionClass class]
     *
     * @var ReflectionClass $reflectionClass
     */
    private ReflectionClass $reflectionClass;

    /**
     * Initializes the object to perform a reflection on a class
     *
     * @param object $instance [Object of any type that is subjected to
     * reflection]
     *
     * @return void
     */
    public function initReflection(object $instance): void
    {
        $this->instance = $instance;

        $this->reflectionClass = new ReflectionClass($this->instance);
    }

    /**
     * Gets the private or protected methods of a reflected class
     *
     * @param string $method [Name of the private or protected method that you
     * want to get and execute]
     * @param array $args [Optional parameter that allows you to specify the
     * arguments that will be passed to the method when it is invoked]
     *
     * @return mixed
     */
    public function getPrivateMethod(string $method, ?array $args = null): mixed
    {
        $reflectionMethod = $this->reflectionClass->getMethod($method);

        $reflectionMethod->setAccessible(true);

        if (is_array($args)) {
            return $reflectionMethod->invokeArgs($this->instance, $args);
        }

        return $reflectionMethod->invoke($this->instance);
    }

    /**
     * Gets the value of a private or protected property of a reflected class
     *
     * @param string $property [Name of the private or protected property
     * whose value you want to obtain]
     *
     * @return mixed
     */
    public function getPrivateProperty(string $property): mixed
    {
        $customProperty = $this->reflectionClass->getProperty($property);

        $customProperty->setAccessible(true);

        return $customProperty->getValue($this->instance);
    }

    /**
     * Sets the value of a private or protected property of a reflected class
     *
     * @param string $property [Name of the private or protected property whose
     * value you want to set]
     * @param mixed $value [Value to assign to the specified property]
     *
     * @return void
     */
    public function setPrivateProperty(string $property, mixed $value): void
    {
        $customProperty = $this->reflectionClass->getProperty($property);

        $customProperty->setAccessible(true);

        $customProperty->setValue($this->instance, $value);
    }

    /**
     * Delete a directory and all its contents recursively
     *
     * @param string $dir [Directory to be deleted recursively]
     *
     * @return void
     */
    public function rmdirRecursively(string $dir): void
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);

            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
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

    /**
     * Create folders from a defined path
     *
     * @param string $directory [Indicates the path of the directory you want
     * to create]
     *
     * @return void
     */
    public function createDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0777, true)) {
                throw new RuntimeException("could not create directory: {$directory}");
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
     */
    public function createImage(
        int $x = 100,
        int $y = 100,
        string $path = './storage/',
        string $fileName = 'image.png'
    ): void {
        $image = imagecreatetruecolor($x, $y);

        imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));

        imagepng($image, "{$path}{$fileName}");
    }

    /**
     * Assertion to test if a JSON object is identical to the defined array
     *
     * @param string $json [JSON string to parse and compare with the provided
     * data structure]
     * @param array $options [Expected data structure expected to be present
     * in the JSON]
     *
     * @return void
     */
    public function assertJsonContent(string $json, array $options): void
    {
        $this->assertSame($options, json_decode($json, true));
    }

    /**
     * Makes an assertion about the value of a specific property of a class
     *
     * @param string $property [Name of the property on which the assertion
     * will be made]
     * @param mixed $value [Expected value of the property]
     *
     * @return void
     */
    public function assertPropertyValue(string $property, mixed $value): void
    {
        $this->assertSame($value, $this->getPrivateProperty($property));
    }

    /**
     * Method to perform an assertion of an object to test if it is an
     * instance of that class
     *
     * @param object $instance [Object whose type you want to verify]
     * @param array $instances [Array containing the names of the classes
     * with which you want to compare the object]
     *
     * @return void
     */
    public function assertInstances(object $instance, array $instances): void
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
     * @return string|false
     */
    public function assertWithOb(string $output, Closure $callback): string|false
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
     */
    public function getResponse(string $message, string $messageSplit): string
    {
        $split = explode($messageSplit, $message);

        return trim(end($split));
    }

    /**
     * Gets the exception object when consuming an API
     *
     * @param Closure $callback [Function that executes the exception]
     *
     * @return Exception
     */
    public function getExceptionFromApi(Closure $callback): Exception
    {
        try {
            $callback();
        } catch (Exception $e) {
            return $e;
        }
    }
}
