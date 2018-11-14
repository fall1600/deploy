<?php
namespace Deploy\Tests\Deploy;

use Deploy\Kernel;
use Deploy\Tests\BaseTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class KernelTest extends BaseTestCase
{
    public function test___construct()
    {
        //arrange
        $name = 'test';
        $version = '1.0.0';
        $kernel = $this->getMockBuilder(Kernel::class)
            ->disableOriginalConstructor()
            ->setMethods(array('init'))
            ->getMock();

        $kernel->expects($this->once())
            ->method('init')
            ;

        //act
        $kernel->__construct($name, $version);

        //assert
        $this->assertEquals($name, $this->getObjectAttribute($kernel, 'name'));
        $this->assertEquals($version, $this->getObjectAttribute($kernel, 'version'));

    }

    public function test_getContainer()
    {
        //arrange
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMockForAbstractClass();

        $kernel = $this->getMockBuilder(Kernel::class)
            ->setMethods()
            ->disableOriginalConstructor()
            ->getMock();

        $this->setObjectAttribute($kernel, 'container', $container);

        //act
        $result = $kernel->getContainer();

        //assert
        $this->assertSame($container, $result);
    }

    public function test_getExtansions()
    {
        //arrange
        $extensions = array('a', 'b', 'c');

        $kernel = $this->getMockBuilder(Kernel::class)
            ->setMethods()
            ->disableOriginalConstructor()
            ->getMock();

        $this->setObjectAttribute($kernel, 'extensions', $extensions);

        //act
        $result = $kernel->getExtensions();

        //assert
        $this->assertEquals($extensions, $result);
    }
}