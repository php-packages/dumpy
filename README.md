# Dumpy 1.0.2

Better var_dump for PHP.

## Features

- pretty configurable (one-configuration-per-instance)
- fully tested (PHPUnit as a test runner + Essence as BDD assertion framework)
- documented (comments in source code + this readme file)
- decent source code, unlike many solutions I've seen previously

## Installation

```
composer require php-packages/dumpy
```

## Usage

It's pretty darn simple.

### Configuration

```php
$dumpy = new PhpPackages\Dumpy\Dumpy;
$dumpy->configure("optionName", "optionValue");
$dumpy->getConfigOption("optionName"); # => "optionValue"
```

| Option name | Description | Possible values |
--------------|-------------|------------------
| str_max_length      | the maximum string length | any *positive integer* |
| bool_lowercase      | either `FALSE` or `false` | either `false` or `true` |
| null_lowercase      | either `NULL` or `null` | either `false` or `true` |
| round_double        | whether to `round()` doubles to given decimal point | `false` or any *positive integer* |
| replace_newline     | whether to replace `PHP_EOL` with `\\n` | either `false` or `true` |
| array_max_elements  | a treshold - all odd elements with be replaced with a single `...` | any *positive integer* |
| array_indenting     | an indentation sequence | any *string* (e.g., `  `) |
| object_limited_info | whether to display parent classes, interfaces, traits | either `false` or `true` |

### Dumping

```php
$dumpy = new PhpPackages\Dumpy\Dumpy;
// Configure Dumpy...
$stringRepresentation = $dumpy->dump($anyValue);
```

## License

The MIT license (MIT).
