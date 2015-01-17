<?php namespace Magician\Tests;

use ReflectionClass;

// NOTE: this could be implemented as a trait for composition and greater flexibility in tests

abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
    protected $mocked;

    protected $reflection;

    public function mockAbstract($name)
    {
        $this->mocked = $this->getMockForAbstractClass($name);

        $this->reflection = new ReflectionClass($this->mocked);
    }

    public function openProperty($property)
    {
        $property = $this->reflection->getProperty($property);
        $property->setAccessible(true);
    }

    public function setProperty($property, $value)
    {
        $property = $this->reflection->getProperty($property);
        $property->setAccessible(true);

        return $property->setValue($this->mocked, $value);
    }

    public function getProperty($property)
    {
        $property = $this->reflection->getProperty($property);
        $property->setAccessible(true);

        return $property->getValue($this->mocked);
    }
}
