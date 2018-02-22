<?php

namespace Deploy\Extensions\Backend\EventListener;

use Deploy\Extensions\Base\Service\ShellExecutor\Local;

class BackendDeploy
{
    /** @var Local */
    protected $localExecutor;

    /** @var Remote */
    protected $remoteExecutor;

    public function injectShellExecuteLocal(Local $localExecutor)
    {
        $this->localExecutor = $localExecutor;
    }




}
