<?php

namespace Deploy\Extensions\SourceCode\EventListener;

use Deploy\Events\FetchSourceEvent;
use Deploy\Extensions\Base\Service\ShellExecutor\Local;

class WorkingSource
{
    /** @var Local */
    protected $localExecutor;

    public function injectShellExecuteLocal(Local $localExecutor)
    {
        $this->localExecutor = $localExecutor;
    }

    public function onFetchSource(FetchSourceEvent $event)
    {
        $config = $event->getConfigWrapper();
        $this->localExecutor->execute(array("git", "clone", $config->getSourceRepo(), $config->getSourcePath()), null, $event->getOutput());
        $this->localExecutor->execute(array('git', 'checkout', $config->getSourceRevision()), $config->getSourcePath(), $event->getOutput());
    }
}
