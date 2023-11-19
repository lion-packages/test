<?php 

declare(strict_types=1);

namespace LionTest;

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
     * Gets the value of a private property of a class
     * */
    public function getPrivateProperty(string $property): mixed
    {
        $customProperty = $this->reflectionClass->getProperty($property);
        $customProperty->setAccessible(true);

        return $customProperty->getValue($this->instance);
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
    ): void
    {
        $image = imagecreatetruecolor($x, $y);
        imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));
        imagepng($image, "{$path}{$fileName}");
    }
}
