<?php namespace PhpPackages\Dumpy;

class DumpyTest extends \Essence\Extensions\PhpunitExtension
{

    /**
     * @return void
     */
    public function setUp()
    {
        $this->dumpy = new Dumpy;
    }

    /**
     * @test
     */
    public function it_is_configurable()
    {
        // Let's set the configuration - change str_max_length option.
        $this->dumpy->configure("str_max_length", 100);

        // If you type something awful, Dumpy will be sad.
        $dumpy =& $this->dumpy;

        expect(function() use($dumpy) {
            $dumpy->configure("foo", null);
        })->toThrow("InvalidArgumentException");

        // Let's ensure that str_max_length value was changed.
        expect($this->dumpy->getConfigOption("str_max_length"))->toEqual(100);

        // Whoops!
        expect(function() use($dumpy) {
            $dumpy->getConfigOption("foo");
        })->toThrow("UnexpectedValueException");
    }

    /**
     * @test
     */
    public function it_prints_a_boolean_value()
    {
        // Lowercase output.
        $this->dumpy->configure("bool_lowercase", true);

        expect($this->dumpy->dump(false))->toBeEqual("false");
        expect($this->dumpy->dump(true))->toBeEqual("true");

        // Uppercase output.
        $this->dumpy->configure("bool_lowercase", false);

        expect($this->dumpy->dump(false))->toBeEqual("FALSE");
        expect($this->dumpy->dump(true))->toBeEqual("TRUE");
    }

    /**
     * @test
     */
    public function it_prints_NULL_value()
    {
        // Lowercase.
        $this->dumpy->configure("null_lowercase", true);

        expect($this->dumpy->dump(null))->toBeEqual("null");

        // Uppercase.
        $this->dumpy->configure("null_lowercase", false);

        expect($this->dumpy->dump(null))->toBeEqual("NULL");
    }

    /**
     * @test
     */
    public function it_prints_integer_and_double_values()
    {
        // Integers.
        expect($this->dumpy->dump(123))->toBeEqual("123"); // Positive decimal.
        expect($this->dumpy->dump(-42))->toBeEqual("-42"); // Negative decimal.
        expect($this->dumpy->dump(0123))->toBeEqual("83"); // Octal.
        expect($this->dumpy->dump(0x1A))->toBeEqual("26"); // Hexadecimal.
        expect($this->dumpy->dump(0b1))->toBeEqual("1"); // Binary.

        // Floats (doubles).
        expect($this->dumpy->dump(1.234))->toBeEqual("1.234");
        expect($this->dumpy->dump(1.2e4))->toBeEqual("12000");
        expect($this->dumpy->dump(7E-10))->toBeEqual("7.0E-10");

        // With said precision.
        $this->dumpy->configure("round_double", 0);
        expect($this->dumpy->dump(1.23456789))->toBeEqual("1");
    }
}
