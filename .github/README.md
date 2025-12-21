<img width="1920" height="1080" alt="presentation-lion-packages" src="https://github.com/user-attachments/assets/9d777705-ff36-4054-8b92-03944ddcde35" />

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
