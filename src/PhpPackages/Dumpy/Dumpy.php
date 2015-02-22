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

        // Whether float values should be rounded and to what precision.
        "round_double" => false,

        // Whether to replace PHP_EOL occurrences with "\\n".
        "replace_newline" => true,

        // If an array has more than array_max_nesting elements,
        // show the first array_max_nesting elements, skip everything else.
        "array_max_elements" => 20,

        // Defaults to "    " (4 spaces).
        "array_indenting" => "    ",
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
                if ($this->config["round_double"] !== false) {
                    $value = round($value, $this->config["round_double"], PHP_ROUND_HALF_UP);
                }

                return (string) $value;
            }

            // Handle strings.
            case "string": {
                // Multibyte support?
                if (strlen($value) > $this->config["str_max_length"]) {
                    $value = substr($value, 0, $this->config["str_max_length"]) . "...";
                }

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
        }
    }

    /**
     * @param array $value
     * @param integer $level
     * @return string
     */
    protected function printArray(array $value, $level = 1)
    {
        $result  = "[" . PHP_EOL;
        $isAssoc = (array_keys($value) !== range(0, count($value) - 1));
        $counter = 0;

        foreach ($value as $key => $element) {
            $counter++;

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

            $result .= str_repeat($this->config["array_indenting"], $level);

            if ( ! $isAssoc) {
                $result .= $element;
            } else {
                $result .= sprintf("%s => %s", $this->dump($key), $element);
            }

            $result .= "," . PHP_EOL;
        }

        $result .= str_repeat($this->config["array_indenting"], $level - 1);
        $result .= "]";

        return $result;
    }

    /**
     * @param object $value
     * @return string
     */
    protected function printObject($value)
    {
        $result    = $this->getGeneralObjectInfo($value);
        $reflector = new ReflectionClass($value);

        // Get all parent classes.
        $classes = [];
        $parent  = $reflector->getParentClass();

        do {
            $class = $parent->getName();

            if ($parent->isAbstract()) {
                $class .= " (abstract)";
            }

            $classes[] = $class;
        }
        while ($parent = $parent->getParentClass());

        $result .= sprintf("Classes: %s" . PHP_EOL, implode(", ", $classes));

        // Handle interfaces.
        $interfaces = array_keys($reflector->getInterfaces());

        $result .= sprintf("Interfaces: %s" . PHP_EOL, implode(", ", $interfaces));

        // Handle traits.
        $traits = $this->getAllTraitNames($reflector->getTraits());

        $result .= sprintf("Traits: %s" . PHP_EOL, implode(", ", $traits));

        // Show property values.
        $result .= "Properties:" . PHP_EOL;

        foreach ($reflector->getProperties() as $property) {
            // Make the property readable (accessible) and then read its value.
            $property->setAccessible(true);
            $dumpedValue = $this->dump($propertyValue = $property->getValue($value));

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

            // Print.
            $result .= sprintf(
                "    %s: %s" . PHP_EOL,
                $property->getName(),
                $dumpedValue
            );
        }

        // Return the result.
        return $result;
    }

    /**
     * At the moment (PHP 5.6), ReflectionClass::getTraits doesn't care about inheritance.
     * I took the recursive approach to this problem since data volume is pretty small.
     *
     * @param array $traits
     * @return array
     */
    protected function getAllTraitNames(array $traits)
    {
        $names = [];

        foreach ($traits as $trait) {
            /**
             * @var \ReflectionClass $trait
             */
            $names[] = $trait->getName();

            if ($trait->getTraits()) {
                $names = array_merge($names, $this->getAllTraitNames($trait->getTraits()));
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
}
