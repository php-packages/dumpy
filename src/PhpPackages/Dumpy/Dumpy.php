<?php namespace PhpPackages\Dumpy;

use InvalidArgumentException,
    UnexpectedValueException;

use ReflectionClass,
    ReflectionProperty;

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

        // When bool_lowercase is set to TRUE, dump() will return "false" or "true".
        // Otherwise, "FALSE" or "TRUE".
        "bool_lowercase" => false,

        // Same as bool_lowercase, but for NULL values ("null" or "NULL").
        "null_lowercase" => false,

        // Whether float values should be rounded and to what precision (decimal points).
        "round_double" => false,

        // Whether to replace PHP_EOL occurrences with "\\n".
        "replace_newline" => true,

        // If an array has more than array_max_nesting elements,
        // show the first array_max_nesting elements, skip everything else.
        "array_max_elements" => 20,

        // Defaults to "    " (4 spaces).
        "array_indenting" => "    ",

        // Whether NOT to print parent classes, interfaces and traits.
        "object_limited_info" => true,
    ];

    /**
     * Returns the value of given configuration option.
     *
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
     * Sets a configuration option to given value.
     *
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

    /**
     * This method helps you get string representations of various PHP data types.
     *
     * @param mixed $value
     * @return string
     */
    public function dump($value)
    {
        switch (gettype($value)) {
            // Handle boolean values.
            case "boolean": {
                $output = $value ? "true" : "false";

                // You can represent booleans (and NULLs) in either lower- or uppercase format.
                return $this->config["bool_lowercase"] ? $output : strtoupper($output);
            }

            // Handle NULL values.
            case "NULL": {
                return $this->config["null_lowercase"] ? "null" : "NULL";
            }

            // Handle integers.
            case "integer": {
                return (string) $value;
            }

            // Handle floats (doubles).
            case "double": {
                // Dumpy allows you to round floats to round_double decimal points.
                if ($this->config["round_double"] !== false) {
                    $value = round($value, $this->config["round_double"], PHP_ROUND_HALF_UP);
                }

                return (string) $value;
            }

            // Handle strings.
            case "string": {
                // @suggestion Multibyte support?
                // Look above for the detailed explanation.
                if (strlen($value) > $this->config["str_max_length"]) {
                    $value = substr($value, 0, $this->config["str_max_length"]) . "...";
                }

                // Dumpy allows you to replace newline characters with "\\n".
                if ($this->config["replace_newline"]) {
                    $value = str_replace(PHP_EOL, "\\n", $value);
                }

                return "\"{$value}\"";
            }

            // Handle arrays.
            case "array": {
                return $this->printArray($value) . PHP_EOL;
            }

            // Handle objects.
            case "object": {
                return $this->printObject($value);
            }
            // @codeCoverageIgnoreStart
        }
    } // @codeCoverageIgnoreEnd

    /**
     * Returns a string representation of given array.
     *
     * @param array $value
     * @param integer $level
     * @return string
     */
    protected function printArray(array $value, $level = 1)
    {
        // Open bracket.
        $result  = "[" . PHP_EOL;
        // This approach is much faster than the one that uses array_values function.
        // To make it even faster, we cache the value.
        $isAssoc = (array_keys($value) !== range(0, count($value) - 1));
        // We'll need this variable to determine whether we need to omit remaining elements.
        $counter = 0;

        foreach ($value as $key => $element) {
            $counter++;

            // Decide whether to omit the rest of the values in given array
            // depending on array_max_elements value.
            if ($counter > $this->config["array_max_elements"]) {
                $result .= str_repeat($this->config["array_indenting"], $level);
                $result .= "..." . PHP_EOL;

                break;
            }

            if (is_array($element)) {
                $element = $this->printArray($element, $level + 1);
            } else {
                $element = $this->dump($element);
            }

            // Maintain the proper indenting.
            $result .= str_repeat($this->config["array_indenting"], $level);

            if ( ! $isAssoc) {
                $result .= $element;
            } else {
                $result .= sprintf("%s => %s", $this->dump($key), $element);
            }

            $result .= "," . PHP_EOL;
        }

        // Maintain the proper indenting.
        $result .= str_repeat($this->config["array_indenting"], $level - 1);
        // Close bracket.
        $result .= "]";

        return $result;
    }

    /**
     * Returns a string representation of given object.
     *
     * @param object $value
     * @return string
     */
    protected function printObject($value)
    {
        $reflector = new ReflectionClass($value);

        // Display the general information about given object.
        $result = $this->getGeneralObjectInfo($value);

        if ( ! $this->config["object_limited_info"]) {
            // Display all parent classes.
            $result .= sprintf(
                "Classes: %s" . PHP_EOL,
                implode(", ", $this->getAllParentClassNames($reflector))
            );

            // Display all interfaces (interface inheritance is supported).
            $result .= sprintf(
                "Interfaces: %s" . PHP_EOL,
                implode(", ", $reflector->getInterfaceNames())
            );

            // Display all class traits.
            $result .= sprintf(
                "Traits: %s" . PHP_EOL,
                implode(", ", $this->getAllTraitNames($reflector))
            );
        }

        // Display property values.
        $result .= "Properties:" . PHP_EOL;
        $result .= $this->getPropertyValues($reflector, $value);

        return $result;
    }

    /**
     * At the moment (PHP 5.6), ReflectionClass::getTraits doesn't care about inheritance.
     * I took the recursive approach to this problem since data volume is pretty small.
     *
     * @param \ReflectionClass $trait
     * @return array
     */
    protected function getAllTraitNames(ReflectionClass $trait)
    {
        $names = [];

        foreach ($trait->getTraits() as $trait) {
            /**
             * @var \ReflectionClass $trait
             */
            $names[] = $trait->getName();

            if ($trait->getTraits()) {
                $names = array_merge($names, $this->getAllTraitNames($trait));
            }
        }

        return $names;
    }

    /**
     * Returns a well-formed string that contains:
     *  - object's fully qualified class name
     *  - object's unique ID
     *
     * @param object $object
     * @return string
     */
    protected function getGeneralObjectInfo($object)
    {
        return sprintf("%s #%s", get_class($object), spl_object_hash($object));
    }

    /**
     * Returns ALL (inheritance is taken into account) parent classes, including abstract ones.
     *
     * @param \ReflectionClass $reflector
     * @return array
     */
    protected function getAllParentClassNames(ReflectionClass $reflector)
    {
        $classes = [];

        if ($parent = $reflector->getParentClass()) {
            /**
             * @var \ReflectionClass $parent
             */
            if ( ! $parent->isAbstract()) {
                $classes[] = $parent->getName();
            } else {
                $classes[] = sprintf("%s (abstract)", $parent->getName());
            }

            $classes = array_merge($classes, $this->getAllParentClassNames($parent));
        }

        return $classes;
    }

    /**
     * Returns all object's property names and their respective values.
     * Format: "name: value\n".
     *
     * @param \ReflectionClass $reflector
     * @param object $object
     * @return string
     */
    protected function getPropertyValues(ReflectionClass $reflector, $object)
    {
        $result = "";

        foreach ($reflector->getProperties() as $property) {
            // Make the property readable (accessible) and then read its value.
            $property->setAccessible(true);

            $dumpedValue = $this->dump($propertyValue = $property->getValue($object));

            // Indent the output if it's an array.
            if (is_array($propertyValue)) {
                $lines = explode(PHP_EOL, $dumpedValue);

                for ($index = 1; $index < count($lines); $index++) {
                    // Skip empty lines.
                    if ( ! trim($lines[$index])) {
                        unset ($lines[$index]);

                        continue;
                    }

                    $lines[$index] = "    " . $lines[$index];
                }

                $dumpedValue = implode(PHP_EOL, $lines);
            }

            $result .= sprintf("    %s: %s" . PHP_EOL, $property->getName(), $dumpedValue);
        }

        return $result;
    }
}
