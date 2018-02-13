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
//        $this->localExecutor->execute(array('php', 'app/console', 'a:d', '-e', 'prod'), $config->getBackendSourcePath(), $output);
    }

    public function patchAPIBaseConfig(BuildSourceEvent $event)
    {

    }

    public function buildBackendAdmin(BuildSourceEvent $event)
    {

    }
}
