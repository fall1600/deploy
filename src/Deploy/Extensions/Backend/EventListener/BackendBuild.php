<?php

namespace Deploy\Extensions\Backend\EventListener;

use Deploy\Events\BuildSourceEvent;
use Deploy\Extensions\Base\Service\ShellExecutor\Local;

class BackendBuild
{
    /** @var Local */
    protected $localExecutor;

    public function injectShellExecuteLocal(Local $localExecutor)
    {
        $this->localExecutor = $localExecutor;
    }

    public function buildBackendService(BuildSourceEvent $event)
    {
        $config = $event->getConfigWrapper();
        $output = $event->getOutput();
        $this->localExecutor->execute(array('composer', 'install'), $config->getBackendSourcePath(), $output);
        $this->localExecutor->execute(array('php', 'app/console', 'c:c', '-e', 'prod'), $config->getBackendSourcePath(), $output);
        $this->localExecutor->execute(array('php', 'app/console', 'p:b', '-e', 'prod'), $config->getBackendSourcePath(), $output);
        $this->localExecutor->execute(array('php', 'app/console', 'a:d', '-e', 'prod'), $config->getBackendSourcePath(), $output);
    }

    public function patchAPIBaseConfig(BuildSourceEvent $event)
    {
        $output = $event->getOutput();
        $output->write("admin patch apibase...");
        $configWrapper = $event->getConfigWrapper();
        $apiBaseConfigPath = $this->findAPIBaseConfigPath($configWrapper->getBackendSourcePath());
        if (!$apiBaseConfigPath) {
            $output->writeln("fail");
            return;
        }
        $config = $this->readJsonFile($apiBaseConfigPath);
        $config['apibase'] = $configWrapper->getAdminAPIBase();
        $this->writeJsonFile($apiBaseConfigPath, $config);
        $output->write("done");
    }

    public function buildBackendAdmin(BuildSourceEvent $event)
    {
        $config = $event->getConfigWrapper();
        $output = $event->getOutput();
        $adminPath = $config->getBackendSourcePath().'/vue';
        $this->localExecutor->execute(array('php', 'app/console', 'd:a:i', '-e', 'prod'), $config->getBackendSourcePath(), $output);
        $this->localExecutor->execute(array('yarn', 'install'), $adminPath, $output);
        $this->localExecutor->execute(array('yarn', 'build'), $adminPath, $output);
    }

    protected function findAPIBaseConfigPath($backendPath)
    {
        if (file_exists("$backendPath/vue.cm4/static/apibase.json")) {
            return "$backendPath/vue.cm4/static/apibase.json";
        }

        return false;
    }

    protected function writeJsonFile($filePath, $content)
    {
        file_put_contents(
            $filePath,
            json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    protected function readJsonFile($filePath)
    {
        return json_decode(file_get_contents($filePath), true);
    }
}
