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

    public static function getResponseProvider(): array
    {
        return [
            [
                'text' => 'testing in classes',
                'split' => 'in',
                'return' => 'classes'
            ],
            [
                'text' => 'example test',
                'split' => ' ',
                'return' => 'test'
            ]
        ];
    }

    public static function assertIsDateProvider(): array
    {
        return [
            [
                'date' => '2024-11-02',
                'format' => 'Y-m-d',
            ],
            [
                'date' => '2024-11-02 19:14:30',
                'format' => 'Y-m-d H:i:s',
            ],
        ];
    }

    public static function assertHeaderNotHasKeyProvider(): array
    {
        return [
            [
                'header' => 'HTTP_AUTHORIZATION',
                'headerValue' => 'bearer example.bearer.test',
            ],
            [
                'header' => 'HTTP_CONTENT_TYPE',
                'headerValue' => 'application/json',
            ],
            [
                'header' => 'HTTP_ACCEPT_LANGUAGE',
                'headerValue' => 'en-US,en;q=0.9',
            ],
            [
                'header' => 'HTTP_USER_AGENT',
                'headerValue' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.82 Safari/537.36',
            ],
            [
                'header' => 'HTTP_X_REQUESTED_WITH',
                'headerValue' => 'XMLHttpRequest',
            ],
        ];
    }

    public static function assertHttpBodyNotHasKeyProvider(): array
    {
        return [
            [
                'key' => 'HTTP_AUTHORIZATION',
                'global' => '_SERVER',
                'value' => 'bearer example.bearer.test',
            ],
            [
                'key' => 'page',
                'global' => '_GET',
                'value' => '1',
            ],
            [
                'key' => 'username',
                'global' => '_POST',
                'value' => 'test_user',
            ],
            [
                'key' => 'file_upload',
                'global' => '_FILES',
                'value' => [
                    'name' => 'example.txt',
                    'type' => 'text/plain'
                ],
            ],
            [
                'key' => 'X-Requested-With',
                'global' => '_SERVER',
                'value' => 'XMLHttpRequest',
            ],
        ];
    }
}
