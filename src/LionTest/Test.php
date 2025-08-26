<?php

declare(strict_types=1);

namespace Lion\Test;

use Closure;
use DateTime;
use DateTimeImmutable;
use Exception as GlobalException;
use InvalidArgumentException;
use JsonException;
use Lion\Exceptions\Exception;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Throwable;

/**
 * TestCase extended abstract test class, allows you to write unit tests in PHP
 * using the PHPUnit framework.
 */
abstract class Test extends TestCase
{
    /**
     * Holds a ReflectionClass instance for inspecting the target class at runtime.
     *
     * This object provides access to metadata of the reflected class, including:
     * - Class name and namespace
     * - Methods and their signatures
     * - Properties and their visibility
     * - Parent classes, interfaces, and traits
     * - Doc comments and annotations
     *
     * @var ReflectionClass<object> $reflectionClass
     */
    private ReflectionClass $reflectionClass;

    /**
     * Object that will be reflected
     *
     * @var mixed $instance
     */
    private mixed $instance;

    /**
     * Exception class
     *
     * @var string $exception
     */
    private string $exception;

    /**
     * Exception message
     *
     * @var string $exceptionMessage
     */
    private string $exceptionMessage;

    /**
     * Exception response status
     *
     * @var string $exceptionStatus
     */
    private string $exceptionStatus;

    /**
     * Exception code
     *
     * @var int|string $exceptionCode
     */
    private int|string $exceptionCode;

    /**
     * Initializes reflection for the provided object instance.
     *
     * This method sets up the ReflectionClass instance, granting
     * access to the target class metadata (methods, properties, traits, etc.).
     *
     * @param object $instance The object whose class will be reflected.
     *
     * @return void
     *
     * @throws ReflectionException If reflection cannot be initialized.
     *
     * @codeCoverageIgnore
     * @phpstan-ignore-next-line
     */
    final protected function initReflection(object $instance): void
    {
        $this->instance = $instance;

        $this->reflectionClass = new ReflectionClass($this->instance);
    }

    /**
     * Invokes a private or protected method of the reflected class.
     *
     * Useful for testing or when controlled access to hidden methods is required.
     *
     * @param string $method Name of the private or protected method to invoke.
     * @param array<int|string, mixed> $args Optional arguments to pass to the
     * method.
     *
     * @return mixed The result of the invoked method.
     *
     * @throws ReflectionException If the method does not exist.
     *
     * @infection-ignore-all
     */
    final protected function getPrivateMethod(string $method, array $args = []): mixed
    {
        if (!$this->reflectionClass->hasMethod($method)) {
            throw new ReflectionException(
                "Method {$method} does not exist in class {$this->reflectionClass->getName()}.",
                500
            );
        }

        return $this->reflectionClass
            ->getMethod($method)
            ->invokeArgs($this->instance, $args);
    }

    /**
     * Retrieves the value of a private or protected property from the reflected
     * class instance.
     *
     * This method allows controlled access to non-public properties, primarily
     * for testing or advanced use cases where encapsulation must be bypassed.
     *
     * @param string $property Name of the private or protected property.
     *
     * @return mixed The current value of the property.
     *
     * @throws ReflectionException If the property does not exist in the reflected
     * class.
     *
     * @infection-ignore-all
     */
    final protected function getPrivateProperty(string $property): mixed
    {
        if (!$this->reflectionClass->hasProperty($property)) {
            throw new ReflectionException(
                "Property '{$property}' does not exist in class {$this->reflectionClass->getName()}.",
                500
            );
        }

        return $this->reflectionClass
            ->getProperty($property)
            ->getValue($this->instance);
    }

    /**
     * Sets the value of a private or protected property on the reflected class
     * instance.
     *
     * This method allows controlled modification of non-public properties, primarily
     * for testing or advanced use cases where encapsulation must be bypassed.
     *
     * @param string $property Name of the private or protected property to modify.
     * @param mixed $value The value to assign to the specified property.
     *
     * @return void
     *
     * @throws ReflectionException If the property does not exist in the reflected class.
     *
     * @infection-ignore-all
     */
    final protected function setPrivateProperty(string $property, mixed $value): void
    {
        if (!$this->reflectionClass->hasProperty($property)) {
            throw new ReflectionException(
                "Property '{$property}' does not exist in class {$this->reflectionClass->getName()}.",
                500
            );
        }

        $this->reflectionClass
            ->getProperty($property)
            ->setValue($this->instance, $value);
    }

    /**
     * Deletes a directory and all its contents recursively.
     *
     * This method will traverse through all files and subdirectories of the given
     * path and remove them before finally removing the root directory itself.
     *
     * Use with caution: This operation is destructive and irreversible.
     *
     * @param string $dir Absolute or relative path to the directory to be deleted.
     *
     * @return void
     *
     * @throws RuntimeException If a file or directory cannot be deleted.
     *
     * @infection-ignore-all
     */
    final protected function rmdirRecursively(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $objects = scandir($dir);

        if ($objects === false) {
            throw new RuntimeException("Unable to read directory: {$dir}.", 500);
        }

        foreach ($objects as $object) {
            if ($object === '.' || $object === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $object;

            if (is_dir($path)) {
                $this->rmdirRecursively($path);
            } elseif (is_file($path) || is_link($path)) {
                if (!unlink($path)) {
                    throw new RuntimeException("Failed to delete file: {$path}.", 500);
                }
            }
        }

        if (!rmdir($dir)) {
            throw new RuntimeException("Failed to remove directory: {$dir}.", 500);
        }
    }

    /**
     * Creates a directory (and any necessary parent directories) from a given path.
     *
     * If the directory already exists, the method does nothing. If the directory
     * cannot be created, it throws a RuntimeException.
     *
     * @param string $directory Absolute or relative path of the directory to
     * create.
     *
     * @return void
     *
     * @throws RuntimeException If the directory cannot be created.
     *
     * @codeCoverageIgnore
     */
    final protected function createDirectory(string $directory): void
    {
        if (is_dir($directory)) {
            return;
        }

        if (!mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new RuntimeException("Failed to create directory: {$directory}.", 500);
        }
    }

    /**
     * Generates a blank PNG image with specified dimensions and saves it to a given
     * directory with a specified file name.
     *
     * The generated image will have a white background by default.
     *
     * @param int $width  Width of the image in pixels (must be greater than 0).
     * @param int $height Height of the image in pixels (must be greater than 0).
     * @param string $path Directory where the image should be saved (must be
     * writable).
     * @param string $fileName File name of the image (e.g., "image.png").
     *
     * @return void
     *
     * @throws InvalidArgumentException If width or height are invalid.
     * @throws RuntimeException If the image creation or saving fails.
     *
     * @codeCoverageIgnore
     */
    final protected function createImage(
        int $width = 100,
        int $height = 100,
        string $path = './storage/',
        string $fileName = 'image.png'
    ): void {
        if ($width <= 0 || $height <= 0) {
            throw new InvalidArgumentException('Width and height must be greater than 0.', 500);
        }

        if (!is_dir($path) && !mkdir($path, 0777, true) && !is_dir($path)) {
            throw new RuntimeException("Failed to create directory: {$path}.", 500);
        }

        if (!is_writable($path)) {
            throw new RuntimeException("The directory is not writable: {$path}.", 500);
        }

        $image = imagecreatetruecolor($width, $height);

        if ($image === false) {
            throw new RuntimeException('Failed to create image resource.', 500);
        }

        $white = imagecolorallocate($image, 255, 255, 255);

        if ($white === false) {
            imagedestroy($image);

            throw new RuntimeException('Failed to allocate color.', 500);
        }

        imagefill($image, 0, 0, $white);

        $filePath = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;

        if (!imagepng($image, $filePath)) {
            imagedestroy($image);

            throw new RuntimeException("Failed to save image at: {$filePath}.", 500);
        }

        imagedestroy($image);
    }

    /**
     * Asserts that a JSON string is structurally and value-wise identical
     * to the given expected array.
     *
     * The expected array is encoded to JSON (with pretty print and strict error
     * handling), and then compared against the provided JSON string to ensure
     * both represent exactly the same structure and values.
     *
     * @param string $json JSON string to compare against the expected data.
     * @param array<string, mixed> $expected Expected data structure to validate against.
     *
     * @return void
     *
     * @throws JsonException If encoding the expected array to JSON fails.
     *
     * @infection-ignore-all
     */
    final protected function assertJsonContent(string $json, array $expected): void
    {
        $expectedJson = json_encode(
            value: $expected,
            flags: JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );

        $this->assertJsonStringEqualsJsonString($expectedJson, $json);
    }

    /**
     * Asserts that the value of a given private or protected property
     * strictly matches the expected value.
     *
     * Retrieves the property value from the reflected class instance
     * and compares it to the provided expected value.
     *
     * @param string $property Name of the property to check.
     * @param mixed  $expected Expected value of the property.
     *
     * @return void
     *
     * @throws ReflectionException If the property does not exist or cannot be accessed.
     *
     * @infection-ignore-all
     */
    final protected function assertPropertyValue(string $property, mixed $expected): void
    {
        $this->assertSame($expected, $this->getPrivateProperty($property));
    }

    /**
     * Asserts that the given object is an instance of all specified classes.
     *
     * This is useful for testing objects that implement multiple interfaces or
     * extend certain base classes. The method will iterate over the list of
     * class/interface names and assert that the object matches each one.
     *
     * @param object $instance The object whose type you want to verify.
     * @param array<int, class-string> $instances Array of fully-qualified
     * class/interface names against which the object will be tested.
     *
     * @return void
     *
     * @throws AssertionFailedError If the object is not an instance of one or more
     * classes.
     *
     * @infection-ignore-all
     */
    final protected function assertInstances(object $instance, array $instances): void
    {
        foreach ($instances as $class) {
            $this->assertInstanceOf($class, $instance);
        }
    }

    /**
     * Asserts that the output generated by executing a callback within an output
     * buffer matches the expected output string.
     *
     * This method uses output buffering (`ob_start`) to capture any direct output
     * (e.g., echo, print) from the callback, compares it against the expected
     * output, and returns the captured value.
     *
     * @param string  $expected Expected output content.
     * @param Closure $callback Callback to execute, whose output will be captured.
     *
     * @return string The actual captured output.
     *
     * @throws RuntimeException If output buffering fails unexpectedly.
     *
     * @infection-ignore-all
     */
    final protected function assertWithOb(string $expected, Closure $callback): string
    {
        if (!ob_start()) {
            throw new RuntimeException('Failed to start output buffering.', 500);
        }

        $callback();

        $actual = ob_get_clean();

        if (!$actual) {
            throw new RuntimeException('Failed to retrieve output buffer.', 500);
        }

        $this->assertSame($expected, $actual);

        return $actual;
    }

    /**
     * Extracts and returns the substring that appears after the last occurrence of
     * a given separator within a message string.
     *
     * @param string $message The original message to process.
     * @param string $separator The delimiter used to split the message.
     *
     * @return string The trimmed substring after the last separator.
     *
     * @throws InvalidArgumentException If the separator is an empty string.
     *
     * @infection-ignore-all
     */
    final protected function getResponse(string $message, string $separator): string
    {
        if ('' === $separator) {
            throw new InvalidArgumentException('Separator cannot be an empty string.', 500);
        }

        $parts = explode($separator, $message);

        return trim((string) end($parts));
    }

    /**
     * Executes a callback and captures a GlobalException if thrown.
     *
     * This helper is useful when testing or handling API calls that may throw a
     * GlobalException, allowing you to inspect the exception object directly
     * instead of letting it bubble up.
     *
     * @param Closure $callback Callback expected to potentially throw a
     * GlobalException.
     *
     * @return GlobalException|null Returns the exception object if caught, or null
     * if none was thrown.
     *
     * @infection-ignore-all
     */
    final protected function getExceptionFromApi(Closure $callback): ?GlobalException
    {
        try {
            $callback();

            return null;
        } catch (GlobalException $exception) {
            return $exception;
        }
    }

    /**
     * Executes a process to validate that a predefined exception is thrown.
     *
     * This helper method is designed to assert that a specific exception type,
     * message, status, and code are raised during testing. It supports two modes:
     *
     * 1. **Automatic mode (no callback provided):**
     *    It will instantiate and throw the configured exception based on the
     *    current test context, asserting that its properties match the expected
     *    values.
     *
     * 2. **Callback mode (callback provided):**
     *    Executes the given callback, and if an exception is thrown, validates
     *    that the exception matches the configured expectations.
     *
     * @param Closure|null $callback Optional callback to execute, expected to throw
     * the configured exception.
     *
     * @return void
     *
     * @throws InvalidArgumentException If the configured exception class does not
     * extend Throwable.
     * @throws Exception If validation or instantiation of the exception fails.
     */
    final protected function expectLionException(?Closure $callback = null): void
    {
        if (null === $callback) {
            if (!is_subclass_of($this->exception, Throwable::class)) {
                throw new InvalidArgumentException('The exception must be a subclass of Throwable.', 500);
            }

            /** @var Exception $lionException */
            $lionException = new $this->exception(
                $this->exceptionMessage,
                $this->exceptionStatus,
                $this->exceptionCode
            );

            $this->expectException($this->exception);
            $this->expectExceptionMessage($this->exceptionMessage);
            $this->expectExceptionCode($this->exceptionCode);
            $this->assertSame($this->exceptionStatus, $lionException->getStatus());

            throw $lionException;
        }

        try {
            $callback();
        } catch (Exception $e) {
            $this->assertSame($this->exception, $e::class);
            $this->assertSame($this->exceptionStatus, $e->getStatus());
            $this->assertSame($this->exceptionMessage, $e->getMessage());
            $this->assertSame($this->exceptionCode, $e->getCode());
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
     * @param string $exceptionMessage Exception message
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
     * @param string $exceptionStatus Exception response status
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
     * @param int|string $exceptionCode Exception code
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
     * Assert that a given string is a valid date in the specified format.
     *
     * This assertion checks that the string can be parsed into a valid date
     * and that its formatted output matches exactly the expected format.
     *
     * @param string $value The string value to validate as a date.
     * @param string $format The expected date format (default: 'Y-m-d').
     *
     * @return void
     *
     * @infection-ignore-all
     */
    protected function assertIsDate(string $value, string $format = 'Y-m-d'): void
    {
        $date = DateTimeImmutable::createFromFormat($format, $value);

        $errors = DateTimeImmutable::getLastErrors();

        $isValidDate = false !== $date
            && empty($errors['warning_count'])
            && empty($errors['error_count'])
            && $date->format($format) === $value;

        $this->assertTrue($isValidDate);
    }

    /**
     * Removes a specific header from the $_SERVER superglobal and asserts
     * that it no longer exists in the array.
     *
     * This method is useful in test scenarios where it is necessary to
     * ensure that a given HTTP header has been cleared and is not present
     * in the request context.
     *
     * @param string $header The name of the header key to remove from $_SERVER.
     *
     * @return void
     *
     * @throws InvalidArgumentException If the provided header name is empty.
     *
     * @infection-ignore-all
     */
    final protected function assertHeaderNotHasKey(string $header): void
    {
        if ('' === $header) {
            throw new InvalidArgumentException('Header name cannot be empty.', 500);
        }

        unset($_SERVER[$header]);

        $this->assertArrayNotHasKey($header, $_SERVER);
    }

    /**
     * Removes a specific key from the PHP superglobals ($_POST, $_GET, $_FILES, $_SERVER, $_COOKIE)
     * and asserts that the key no longer exists in any of them.
     *
     * This method is useful in test scenarios where it is necessary to ensure
     * that no residual request data (query parameters, form fields, file uploads,
     * cookies, or server headers) is present for the given key.
     *
     * @param string $key The key to remove and validate its absence across superglobals.
     *
     * @return void
     *
     * @throws InvalidArgumentException If the provided key is an empty string.
     *
     * @infection-ignore-all
     */
    final protected function assertHttpBodyNotHasKey(string $key): void
    {
        if ($key === '') {
            throw new InvalidArgumentException('Superglobal key cannot be empty.', 500);
        }

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

        if (isset($_COOKIE[$key])) {
            unset($_COOKIE[$key]);

            $this->assertArrayNotHasKey($key, $_COOKIE);
        }
    }
}
