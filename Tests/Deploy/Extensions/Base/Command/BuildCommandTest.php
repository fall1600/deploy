<?php
namespace Deploy\Tests\Deploy\Extensions\Base\Command;

use Deploy\Extensions\Base\Command\BuildCommand;
use Deploy\Tests\BaseTestCase;

class BuildCommandTest extends BaseTestCase
{
    public function test_createPrefixParam_host有指定數值則直接使用host設定()
    {
        //arrange
        $prefix = 'prefix';
        $baseHost = '';
        $host = 'host';
        $command = $this->getMockBuilder(BuildCommand::class)
            ->setMethods()
            ->getMock();

        //act
        $result = $this->callObjectMethod($command, 'createPrefixParam', $prefix, $baseHost, $host);

        //assert
        $this->assertEquals($host, $result);
    }

    public function test_createPrefixParam_baseHost有指定數值_host為null_使用prefix加baseHost()
    {
        //arrange
        $prefix = 'prefix';
        $baseHost = 'base';
        $host = null;
        $command = $this->getMockBuilder(BuildCommand::class)
            ->setMethods()
            ->getMock();

        //act
        $result = $this->callObjectMethod($command, 'createPrefixParam', $prefix, $baseHost, $host);

        //assert
        $this->assertEquals($prefix.'.'.$baseHost, $result);
    }

    public function test_createPrefixParam_baseHost跟host都為null_回傳null()
    {
        //arrange
        $prefix = 'prefix';
        $baseHost = null;
        $host = null;
        $command = $this->getMockBuilder(BuildCommand::class)
            ->setMethods()
            ->getMock();

        //act
        $result = $this->callObjectMethod($command, 'createPrefixParam', $prefix, $baseHost, $host);

        //assert
        $this->assertNull($result);
    }

    public function test_createServerParam_host有指定數值則直接使用host設定()
    {
        //arrange
        $baseHost = '';
        $host = 'host1,host2';
        $command = $this->getMockBuilder(BuildCommand::class)
            ->setMethods()
            ->getMock();

        //act
        $result = $this->callObjectMethod($command, 'createServerParam', $baseHost, $host);

        //assert
        $this->assertEquals(array('host1', 'host2'), $result);
    }

    public function test_createServerParam_baseHost有指定數值_host為null_使用baseHost()
    {
        //arrange
        $baseHost = 'base1,base2';
        $host = null;
        $command = $this->getMockBuilder(BuildCommand::class)
            ->setMethods()
            ->getMock();

        //act
        $result = $this->callObjectMethod($command, 'createServerParam', $baseHost, $host);

        //assert
        $this->assertEquals(array('base1', 'base2'), $result);
    }

    public function test_createServerParam_baseHost跟host都為null_回傳null()
    {
        //arrange
        $baseHost = null;
        $host = null;
        $command = $this->getMockBuilder(BuildCommand::class)
            ->setMethods()
            ->getMock();

        //act
        $result = $this->callObjectMethod($command, 'createServerParam', $baseHost, $host);

        //assert
        $this->assertNull($result);
    }

    /**
     * @dataProvider dataProvider_test_createBooleanChoiceParam
     */
    public function test_createBooleanChoiceParam($aspect, $baseHost, $host)
    {
        //arrange
        $command = $this->getMockBuilder(BuildCommand::class)
            ->setMethods()
            ->getMock();

        //act
        $result = $this->callObjectMethod($command, 'createBooleanChoiceParam', $baseHost, $host);

        //assert
        $this->assertEquals($aspect, $result);
    }

    public function dataProvider_test_createBooleanChoiceParam()
    {
        return array(
            array(
                'aspect' => true,
                'baseHost' => true,
                'host' => true,
            ),
            array(
                'aspect' => false,
                'baseHost' => false,
                'host' => false,
            ),
            array(
                'aspect' => true,
                'baseHost' => true,
                'host' => false,
            ),
            array(
                'aspect' => true,
                'baseHost' => false,
                'host' => true,
            ),
        );
    }
}
