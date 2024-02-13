<?php

declare(strict_types=1);

namespace Tests\Provider;

use Tests\Provider\TestProviderInterface;

trait TestProviderTrait
{
    public static function assertInstancesProvider(): array
    {
        $instance1 = new class implements TestProviderInterface {
            public function exampleMethod(): void
            {
            }
        };

        return [
            [
                'instance' => $instance1,
                'instances' => [$instance1::class]
            ],
            [
                'instance' => $instance1,
                'instances' => [TestProviderInterface::class]
            ],
            [
                'instance' => $instance1,
                'instances' => [$instance1::class, TestProviderInterface::class]
            ]
        ];
    }
}
