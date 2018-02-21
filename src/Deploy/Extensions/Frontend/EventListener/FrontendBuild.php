<?php

namespace Deploy\Extensions\Frontend\EventListener;

use Deploy\Events\BuildSourceEvent;
use Deploy\Events\PostBuildSourceEvent;
use Deploy\Events\PreBuildSourceEvent;
use Deploy\Extensions\Base\Service\ShellExecutor\Local;
use Symfony\Component\DomCrawler\Crawler;

class FrontendBuild
{
    /** @var Local */
    protected $localExecutor;

    public function injectShellExecuteLocal(Local $localExecutor)
    {
        $this->localExecutor = $localExecutor;
    }

    public function patchAPIBaseConfig(PreBuildSourceEvent $event)
    {
        $output = $event->getOutput();
        $configWrapper = $event->getConfigWrapper();

        $output->write('frontend patch apibase...');

        $apiBaseConfigPath = $this->findAPIBaseConfigPath($configWrapper->getFrontendSourcePath());
        if (!$apiBaseConfigPath) {
            $output->write('fail');
            return;
        }

        $config = $this->readJsonFile($apiBaseConfigPath);
        $config['apibase'] = $configWrapper->getServiceAPIBase();
        $this->writeJsonFile($apiBaseConfigPath, $config);
        $output->writeln('done');
    }

    public function buildFrontend(BuildSourceEvent $event)
    {
        $config = $event->getConfigWrapper();
        $this->localExecutor->execute(
            array("yarn", "install"),
            $config->getFrontendSourcePath(),
            $event->getOutput()
        );
        $this->localExecutor->execute(
            array("yarn", "build"),
            $config->getFrontendSourcePath(),
            $event->getOutput()
        );
    }

    public function addServerPush(PostBuildSourceEvent $event)
    {
        $config = $event->getConfigWrapper();
        $output = $event->getOutput();
        $htmlContent = file_get_contents($config->getFrontendSourcePath()."/dist/index.html");
        $output->writeln("patch server push .htaccess");
        $cssLinks = $this->analyzeCss($htmlContent);
        $jsLinks = $this->analyzeJs($htmlContent);
        $this->patchServerPushConfig(
            $config->getFrontendSourcePath().'/dist/.htaccess',
            array_merge($cssLinks, $jsLinks)
        );
    }

    protected function findAPIBaseConfigPath($frontendPath)
    {
        if (file_exists("$frontendPath/src/static/apibase.json")) {
            return "$frontendPath/src/static/apibase.json";
        }

        if (file_exists("$frontendPath/static/apibase.json")) {
            return "$frontendPath/static/apibase.json";
        }

        return false;
    }

    protected function readJsonFile($filePath)
    {
        return json_decode(file_get_contents($filePath), true);
    }

    protected function writeJsonFile($filePath, $content)
    {
        file_put_contents(
            $filePath,
            json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    protected function analyzeCss($htmlContent)
    {
        $css = array();
        $crawler = new Crawler($htmlContent);
        $crawler
            ->filter("link[rel='stylesheet']")
            ->reduce(function(Crawler $node, $i) use (&$css) {
                $href = $node->attr('href');
                array_push($css, "<$href>; rel=preload; as=stylesheet");
            });
        return $css;
    }

    protected function analyzeJs($htmlContent)
    {
        $js = array();
        $crawler = new Crawler($htmlContent);
        $crawler
            ->filter('script')
            ->reduce(function(Crawler $node, $i) use (&$js) {
                $src = $node->attr('src');
                if ($src != "") {
                    array_push($js, "<$src>; rel=preload; as=script");
                }
            });
        return $js;
    }

    protected function patchServerPushConfig($htaccessPath, $links)
    {
        if (!file_exists($htaccessPath)) {
            return;
        }

        $file = fopen($htaccessPath, 'a');
        $content = sprintf(
            "\nHeader set Link\"%s\" env=index_assets_push\n",
            implode(', ', $links)
        );
        fwrite($file, $content);
        fclose($file);
    }
}
