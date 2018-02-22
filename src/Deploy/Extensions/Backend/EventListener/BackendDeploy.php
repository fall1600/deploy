<?php

namespace Deploy\Extensions\Backend\EventListener;

use Deploy\Extensions\Base\Service\ShellExecutor\Local;
use Deploy\Extensions\Base\Service\ShellExecutor\Remote;
use Deploy\Extensions\Base\Service\ShellExecutor\Rsync;

class BackendDeploy
{
    /** @var Local */
    protected $localExecutor;

    /** @var Remote */
    protected $remoteExecutor;

    /** @var Rsync */
    protected $rsyncExecutor;

    public function injectShellExecuteLocal(Local $localExecutor)
    {
        $this->localExecutor = $localExecutor;
    }

    public function injectShellExecuteRemote(Remote $remoteExecutor)
    {
        $this->remoteExecutor = $remoteExecutor;
    }

    public function injectShellExecuteRsync(Rsync $rsyncExecutor)
    {
        $this->rsyncExecutor = $rsyncExecutor;
    }


}
