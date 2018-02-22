<?php

namespace Deploy\Extensions\Base\Service\ShellExecutor;

use Symfony\Component\Console\Output\OutputInterface;

class Rsync
{
    /** @var Local */
    protected $localExecutor;

    public function injectShellExecuteLocal(Local $localExecutor)
    {
        $this->localExecutor = $localExecutor;
    }

    public function execute($sshKeyPath, $targetHost, $sourcePath, $targetPath, array $excludes, OutputInterface $output)
    {
        $sshOption = "";
        if (!is_null($sshKeyPath)) {
            $sshOption = "-i $sshKeyPath";
        }

        $executeCommand = array(
            'rsync',
            '--rsync-path', "mkdir -p $targetPath && rsync",
            '-av', '--delete',
            '-e', sprintf('ssh %s -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null', $sshOption),
            "$sourcePath/",
            "$targetHost:$targetPath",
        );

        $executeCommand = $this->makeExcludeOptions($executeCommand, $excludes);
        $this->localExecutor->execute($executeCommand, null, $output);
    }

    protected function makeExcludeOptions(array $executeCommand, array $excludes)
    {
        foreach ($excludes as $exclude) {
            array_push($executeCommand, "--exclude");
            array_push($executeCommand, $exclude);
        }

        return $executeCommand;
    }
}
