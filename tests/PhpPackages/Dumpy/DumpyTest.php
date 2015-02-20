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
}
