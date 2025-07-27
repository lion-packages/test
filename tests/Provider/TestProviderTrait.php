<?php

declare(strict_types=1);

namespace Tests\Provider;

trait TestProviderTrait
{
    public static function assertInstancesProvider(): array
    {
        $instance1 = new class() implements TestProviderInterface {
            public function exampleMethod(): void
            {
            }
        };

        return [
            [
                'instance'  => $instance1,
                'instances' => [$instance1::class],
            ],
            [
                'instance'  => $instance1,
                'instances' => [TestProviderInterface::class],
            ],
            [
                'instance'  => $instance1,
                'instances' => [$instance1::class, TestProviderInterface::class],
            ],
        ];
    }

    public static function getResponseProvider(): array
    {
        return [
            [
                'text'   => 'testing in classes',
                'split'  => 'in',
                'return' => 'classes',
            ],
            [
                'text'   => 'example test',
                'split'  => ' ',
                'return' => 'test',
            ],
        ];
    }
}
