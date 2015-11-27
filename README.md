# File Permission Calculator
Library to convert file permissions from various formats to another

## Installation
```bash
composer require gisostallenberg/file-permission-calculator
```

## Usage examples
```php
use GisoStallenberg\FilePermissionCalculator\FilePermissionCalculator;

echo new FilePermissionCalculator(0100700); // -rwx------
echo new FilePermissionCalculator(0700); // urwx------
echo FilePermissionCalculator::fromStringRepresentation('-rw-r--r--')->getMode(); // 33188
echo FilePermissionCalculator::fromStringRepresentation('-rw-r--r--')->getOctalString(); // 100644
echo FilePermissionCalculator::fromStringRepresentation('-rw-r--r--'); // -rw-r--r--
echo FilePermissionCalculator::fromOctalString('0100700'); // -rwx------
```
