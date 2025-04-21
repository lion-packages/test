<?php

declare(strict_types=1);

namespace Tests\Provider;

use JsonSerializable;
use Lion\Exceptions\Exception;
use Lion\Exceptions\Traits\ExceptionTrait;

class ExceptionProviderClass extends Exception implements JsonSerializable
{
    use ExceptionTrait;
}
