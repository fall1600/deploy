<?php

namespace Deploy\Extensions\Backend\EventListener;

use Deploy\Events\DeploySourceEvent;
use Deploy\Events\PostDeploySourceEvent;
use Deploy\Extensions\Base\Service\ShellExecutor\Local;
use Deploy\Extensions\Base\Service\ShellExecutor\Remote;
use Deploy\Extensions\Base\Service\ShellExecutor\Rsync;
use Symfony\Component\Console\Output\OutputInterface;

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

    public function deployAdmin(DeploySourceEvent $event)
    {
        $output = $event->getOutput();
        $configWrapper = $event->getConfigWrapper();
        $sourcePath = $configWrapper->getAdminSourcePath();
        $distPath = $configWrapper->getAdminPath();
        $servers = $configWrapper->getAdminServers();
        $remoteUser = $configWrapper->getRemoteUser();
        $remoteKey = $configWrapper->getRemoteKey();
        foreach ($servers as $server) {
            $this->rsyncExecutor->execute($remoteKey, "$remoteUser@$server", "$sourcePath/dist", "$distPath/dist", array(), $output);
        }
    }

    public function deployService(DeploySourceEvent $event)
    {
        $output = $event->getOutput();
        $configWrapper = $event->getConfigWrapper();
        $sourcePath = $configWrapper->getBackendSourcePath();
        $distPath = $configWrapper->getServicePath().'/backend';
        $servers = $configWrapper->getServiceServers();
        $remoteUser = $configWrapper->getRemoteUser();
        $remoteKey = $configWrapper->getRemoteKey();
        foreach ($servers as $server) {
            $this->backupConfig(
                $remoteKey,
                "$remoteUser@$server",
                $sourcePath,
                $distPath,
                $output
            );
            $this->rsyncExecutor->execute(
                $remoteKey,
                "$remoteUser@$server",
                $sourcePath,
                $distPath,
                array('vue', 'vue.cm4', 'app/logs', 'web/upload'),
                $output
            );
        }
    }

    public function rebuildServiceCache(PostDeploySourceEvent $event)
    {
        $output = $event->getOutput();
        $configWrapper = $event->getConfigWrapper();
        $servicePath = $configWrapper->getServicePath().'/backend';
        $servers = $configWrapper->getServiceServers();
        $remoteUser = $configWrapper->getRemoteUser();
        $remoteKey = $configWrapper->getRemoteKey();
        foreach ($servers as $server) {
            $this->remoteExecutor->execute(
                $remoteKey, "$remoteUser@$server",
                array('chmod', '1777', "$servicePath/web/upload"),
                $servicePath, $output
            );
            $this->remoteExecutor->execute(
                $remoteKey, "$remoteUser@$server",
                array('composer', 'install', '--no-interaction'),
                $servicePath, $output
            );
            $this->remoteExecutor->execute(
                $remoteKey, "$remoteUser@$server",
                array('setfacl', '-R', '-m', 'u:www-data:rwX', '-m', "u:$remoteUser:rwX", 'app/cache', 'app/logs'),
                $servicePath, $output, STDERR, false
            );
            $this->remoteExecutor->execute(
                $remoteKey, "$remoteUser@$server",
                array('setfacl', '-dR', '-m', 'u:www-data:rwX', '-m', "u:$remoteUser:rwX", 'app/cache', 'app/logs'),
                $servicePath, $output, STDERR, false
            );
            $this->remoteExecutor->execute(
                $remoteKey, "$remoteUser@$server",
                array('app/console', 'c:c', '-e', 'prod'),
                $servicePath, $output
            );
        }
    }

    protected function backupConfig($remoteKey, $remoteHost, $localPath, $remotePath, OutputInterface $output)
    {
        $remoteConfigPath = $remotePath.'/app/config/parameters.yml';
        $localConfigPath = $localPath.'/app/config/parameters.yml';

        if (!$this->remoteExecutor->fileExists($remoteKey, $remoteKey, $remoteHost, $remoteConfigPath)) {
            return;
        }

        $this->remoteExecutor->fetch($remoteKey, $remoteHost, $remoteConfigPath, $localConfigPath, $output);
    }
}
