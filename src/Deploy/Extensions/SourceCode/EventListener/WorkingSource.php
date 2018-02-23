<?php

namespace Deploy\Extensions\SourceCode\EventListener;

use Deploy\Events\CleanupSourceEvent;
use Deploy\Events\FetchSourceEvent;
use Deploy\Extensions\Base\Service\ShellExecutor\Local;
use Symfony\Component\Filesystem\Filesystem;

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

    public function onCleanupSource(CleanupSourceEvent $event)
    {
        $output = $event->getOutput();
        $config = $event->getConfigWrapper();
        $output->write("clean up source...");
        $filesystem = new Filesystem();
        $filesystem->remove($config->getSourcePath());
        $output->writeln('clean up source done');
    }
}
