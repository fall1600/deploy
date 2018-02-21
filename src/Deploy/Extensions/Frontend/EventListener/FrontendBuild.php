<?php

namespace Deploy\Extensions\Frontend\EventListener;

use Deploy\Events\BuildSourceEvent;
use Deploy\Events\PreBuildSourceEvent;
use Deploy\Extensions\Base\Service\ShellExecutor\Local;

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
}
