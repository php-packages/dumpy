<?php

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

class ComplexClass extends SomeClass implements OneInterface, AnotherInterface
{

    use OneTrait, AnotherTrait;
}
