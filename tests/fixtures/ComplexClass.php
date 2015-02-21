<?php

namespace SomeNamespace
{

    // Classes.

    class SomeClass extends SomeAbstractClass
    {
    }

    abstract class SomeAbstractClass
    {
    }

    // Interfaces.

    interface OneInterface
    {
    }

    interface AnotherInterface extends OneInterface
    {
    }

    // Traits.

    trait OneTrait
    {
    }

    trait AnotherTrait
    {

        use OneTrait;
    }

    // ComplexClass.

    class ComplexClass extends SomeClass implements AnotherInterface
    {

        protected $foo = "bar";

        public $baz = [1, 2, 3];

        use AnotherTrait;
    }
}
