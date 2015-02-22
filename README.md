# Dumpy

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
| str_max_length      | ... | ... |
| bool_lowercase      | ... | ... |
| null_lowercase      | ... | ... |
| round_double        | ... | ... |
| replace_newline     | ... | ... |
| array_max_elements  | ... | ... |
| array_indenting     | ... | ... |
| object_limited_info | ... | ... |

### Dumping

```php
$dumpy = new PhpPackages\Dumpy\Dumpy;
// Configure Dumpy...
$stringRepresentation = $dumpy->dump($anyValue);
```

## License

The MIT license (MIT).
