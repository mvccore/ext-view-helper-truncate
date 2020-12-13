# MvcCore - Extension - View - Helper - Truncate

[![Latest Stable Version](https://img.shields.io/badge/Stable-v4.3.1-brightgreen.svg?style=plastic)](https://github.com/mvccore/ext-view-helper-truncate/releases)
[![License](https://img.shields.io/badge/Licence-BSD-brightgreen.svg?style=plastic)](https://mvccore.github.io/docs/mvccore/4.0.0/LICENCE.md)
![PHP Version](https://img.shields.io/badge/PHP->=5.4-brightgreen.svg?style=plastic)

Truncate plain text or text with html tags by given max. characters number and add three dots at the end.

## Installation
```shell
composer require mvccore/ext-view-helper-truncate
```

## Example
```php
<b><?php echo $this->Truncate('Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 27); ?></b>
```
```html
<b>Lorem ipsum dolor sit amet...</b>
```