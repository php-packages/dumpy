<?php namespace PhpPackages\Dumpy;

use InvalidArgumentException,
    UnexpectedValueException;

/**
 * Better var_dump for PHP.
 */
class Dumpy
{

    /**
     * Dumpy configuration.
     *
     * @var array
     */
    protected $config = [
        // If a string has length of over str_max_length,
        // the first str_max_length characters (no multibyte support as for now) will be taken,
        // the rest (no matter how many) will be replaced with three dots (...).
        "str_max_length" => 50,
    ];

    /**
     * @throws \UnexpectedValueException
     * @param string $option
     * @return mixed
     */
    public function getConfigOption($option)
    {
        // An exception will be thrown instead of returning NULL or something similar.
        if ( ! array_key_exists($option, $this->config)) {
            throw new UnexpectedValueException("Unexpected option name: {$option}");
        }

        return $this->config[$option];
    }

    /**
     * @throws \InvalidArgumentException
     * @param string $option
     * @param mixed $value
     * @return void
     */
    public function configure($option, $value)
    {
        // Dumpy won't let you add NEW configuration options, only update the existing ones.
        if ( ! array_key_exists($option, $this->config)) {
            // Note: something really weird is going to happen if $option is, say, an array.
            throw new InvalidArgumentException("Invalid option name: {$option}");
        }

        // Note that:
        // 1) the passed value can be of any type and contain anything
        // 2) the option value will be overridden permanently, no way back
        $this->config[$option] = $value;
    }
}
