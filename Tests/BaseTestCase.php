<?php
namespace Deploy\Tests;

use Deploy\Kernel;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    /** @var Kernel */
    protected $kernel;

    /** @var ContainerInterface */
    protected $container;

    protected function setUp()
    {
        $this->bootKernel();
    }

    protected function tearDown()
    {
        unset($this->kernel);
        unset($this->container);
    }

    protected function bootKernel()
    {
        $this->kernel = new Kernel();
        $this->container = $this->kernel->getContainer();
    }

    protected function callObjectMethod($object, $methodName)
    {
        $args = func_get_args();
        array_shift($args); //$object
        array_shift($args); //$methodName
        $reflect = new \ReflectionClass($object);
        $method = $reflect->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $args);
    }

    protected function setObjectAttribute($object, $attributeName, $value, $class = null)
    {
        $reflect = new \ReflectionClass($class===null?$object:$class);
        $property = $reflect->getProperty($attributeName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
