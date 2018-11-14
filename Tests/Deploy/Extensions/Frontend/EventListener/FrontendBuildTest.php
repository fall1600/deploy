<?php
namespace Deploy\Tests\Deploy\Extensions\Frontend\Fixtures\EventListener;


use Deploy\Event\PostBuildSourceEvent;
use Deploy\Extensions\Base\ConfigWraper;
use Deploy\Extensions\Frontend\EventListener\FrontendBuild;
use Deploy\Tests\BaseTestCase;
use Symfony\Component\Console\Output\BufferedOutput;

class FrontendBuildTest extends BaseTestCase
{
    public function test_analyzeCss()
    {
        //arrange
        $expected = array(
            '</static/css/app.3923487a14510308e28601b5819befae.css>; rel=preload; as=stylesheet',
        );
        $htmlContent = file_get_contents(__DIR__.'/../Fixtures/dist/index.html');
        $frontendBuild = new FrontendBuild();

        //act
        $result = $this->callObjectMethod($frontendBuild, 'analyzeCss', $htmlContent);

        //assert
        $this->assertEquals($expected, $result);
    }

    public function test_analyzeJs()
    {
        //arrange
        $expected = array(
            '</static/js/manifest.12bc9af4b4da3fb38222.js>; rel=preload; as=script',
            '</static/js/vendor.5539db379b3ad23e2cab.js>; rel=preload; as=script',
            '</static/js/app.c445eb93fe9d402e6f3d.js>; rel=preload; as=script',
        );

        $htmlContent = file_get_contents(__DIR__.'/../Fixtures/dist/index.html');
        $frontendBuild = new FrontendBuild();

        //act
        $result = $this->callObjectMethod($frontendBuild, 'analyzeJs', $htmlContent);

        //assert
        $this->assertEquals($expected, $result);
    }

    public function test_addServerPush()
    {
        //arrange
        $links = array(
            'a',
            'b',
            'c',
            'd',
        );
        $htmlContent = file_get_contents(__DIR__.'/../Fixtures/dist/index.html');
        $output = new BufferedOutput();
        $configWraper = $this->getMockBuilder(ConfigWraper::class)
            ->disableOriginalConstructor()
            ->setMethods(array('getFrontendSourcePath'))
            ->getMock();
        $configWraper
            ->expects($this->atLeastOnce())
            ->method('getFrontendSourcePath')
            ->willReturn(__DIR__.'/../Fixtures');
        $event = new PostBuildSourceEvent($configWraper, $output);
        $frontBuild = $this->getMockBuilder(FrontendBuild::class)
            ->setMethods(array('analyzeCss', 'analyzeJs', 'patchServerPushConfig'))
            ->getMock();
        $frontBuild
            ->expects($this->once())
            ->method('analyzeCss')
            ->with($htmlContent)
            ->willReturn(array('a', 'b'));
        $frontBuild
            ->expects($this->once())
            ->method('analyzeJs')
            ->with($htmlContent)
            ->willReturn(array('c', 'd'));
        $frontBuild
            ->expects($this->once())
            ->method('patchServerPushConfig')
            ->with(__DIR__.'/../Fixtures/dist/.htaccess', $links);

        //act
        $frontBuild->addServerPush($event);

        //assert
    }
}
