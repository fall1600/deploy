<?php

namespace Deploy\Extensions\Frontend\EventListener;

use Deploy\Events\DeploySourceEvent;
use Deploy\Extensions\Base\Service\ShellExecutor\Rsync;

class FrontendDeploy
{
    /** @var Rsync */
    protected $rsyncExecutor;

    public function injectShellExecuteRsync(Rsync $rsyncExecutor)
    {
        $this->rsyncExecutor = $rsyncExecutor;
    }

    public function deployFrontend(DeploySourceEvent $event)
    {
        $output = $event->getOutput();
        $configWrapper = $event->getConfigWrapper();
        $sourcePath = $configWrapper->getFrontendSourcePath();
        $distPath = $configWrapper->getWebPath();
        $servers = $configWrapper->getWebServers();
        $remoteUser = $configWrapper->getRemoteUser();
        $remoteKey = $configWrapper->getRemoteKey();
        foreach ($servers as $server) {
            $this->rsyncExecutor->execute(
                $remoteKey,
                "$remoteUser@$server",
                "$sourcePath/dist",
                "$distPath/dist",
                array(),
                $output
            );
        }
    }
}
