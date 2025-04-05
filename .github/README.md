# 🦁 Lion-Test

<p align="center">
  <a href="https://dev.lion-packages.com/docs/library/content">
    <img 
        src="https://github.com/lion-packages/framework/assets/56183278/60871c9f-1c93-4481-8c1e-d70282b33254"
        width="450" 
        alt="Lion-Packages Logo"
    >
  </a>
</p>

<p align="center">
  <a href="https://packagist.org/packages/lion/test">
    <img src="https://poser.pugx.org/lion/test/v" alt="Latest Stable Version">
  </a>
  <a href="https://packagist.org/packages/lion/test">
    <img src="https://poser.pugx.org/lion/test/downloads" alt="Total Downloads">
  </a>
  <a href="https://github.com/lion-packages/test/blob/main/LICENSE">
    <img src="https://poser.pugx.org/lion/test/license" alt="License">
  </a>
  <a href="https://www.php.net/">
    <img src="https://poser.pugx.org/lion/test/require/php" alt="PHP Version Require">
  </a>
</p>

🚀 **Lion-Test** library to implement testing with helpers that allow easy testing with PHPUnit.

---

## 📖 Features

✔️ Easy-to-use assertions beyond the default PHPUnit set.  
✔️ Custom helper functions for common test patterns.  
✔️ Integration with PHPUnit for seamless test execution.  

---

## 📦 Installation

Install the test using **Composer**:

```bash
composer require --dev phpunit/phpunit lion/test
```

## Usage Example

```php
<?php

declare(strict_types=1);

namespace Tests;

use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;

class ExampleTest extends Test
{
    protected function setUp(): void
    {
        $this->initReflection(new ExampleController());
    }

    #[Testing]
    public function example(): void
    {
        $this->assertPropertyValue('id', 1);
    }
}
```

## 📝 License

The <strong>test</strong> is open-sourced software licensed under the [MIT License](https://github.com/lion-packages/test/blob/main/LICENSE).
