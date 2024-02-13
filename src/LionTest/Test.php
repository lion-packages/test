<?php 

declare(strict_types=1);

namespace Lion\Test;

use Closure;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

abstract class Test extends TestCase
{
	private mixed $instance;
    private ReflectionClass $reflectionClass;

    /**
     * Initializes the object to perform a reflection on a class
     * */
    public function initReflection(mixed $instance): void
    {
        $this->instance = $instance;
        $this->reflectionClass = new ReflectionClass($this->instance);
    }

    /**
     * Gets the private or protected methods of a class
     * */
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
     * Gets the value of a private or protected property of a class
     * */
    public function getPrivateProperty(string $property): mixed
    {
        $customProperty = $this->reflectionClass->getProperty($property);
        $customProperty->setAccessible(true);

        return $customProperty->getValue($this->instance);
    }

    /**
     * Changes the value of a private or protected property of a class
     * */
    public function setPrivateProperty(string $property, mixed $value): void
    {
        $customProperty = $this->reflectionClass->getProperty($property);
        $customProperty->setAccessible(true);
        $customProperty->setValue($this->instance, $value);
    }

    /**
     * Remove all files in a defined path
     * */
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
     * */
    public function createDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0777, true)) {
                throw new RuntimeException("Could not create directory: {$directory}");
            }
        }
    }

    /**
     * Generates a blank image with the defined properties
     * */
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
     * */
    public function assertJsonContent(string $json, array $options): void
    {
        $this->assertSame($options, json_decode($json, true));
    }

    /**
     * Method to make an assertion to a defined value
     * */
    public function assertPropertyValue(string $property, mixed $value): void
    {
        $this->assertSame($value, $this->getPrivateProperty($property));
    }

    /**
     * Method to perform an assertion of an object to test if it is an
     * instance of that class
     * */
    public function assertInstances(object $instance, array $instances): void
    {
        foreach ($instances as $class) {
            $this->assertInstanceOf($class, $instance);
        }
    }

    /**
     * Perform assertions implementing the use of outputs in the buffer
     * with ob_start
     * */
    public function assertWithOb(Closure $callback): void
    {
        ob_start();
        $callback();
        ob_end_clean();
    }
}
