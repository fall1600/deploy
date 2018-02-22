<?php

namespace Deploy\Extensions\Base\Service\ShellExecutor;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class Remote
{
    /** @var Local */
    protected $localExecutor;

    public function injectShellExecuteLocal(Local $localExecutor)
    {
        $this->localExecutor = $localExecutor;
    }

    public function execute($sshKeyPath, $targetHost, $command, $workingPath, OutputInterface $output, $stderr = STDERR, $stopOnError = true)
    {
        $sshCommand = array(
            'ssh',
            '-o', 'StrictHostKeyChecking=no',
            '-o', 'UserKnownHostsFile=/dev/null',
            $targetHost
        );

        if (!is_array($command)) {
            $command = array($command);
        }

        if (!is_null($sshKeyPath)) {
            array_push($sshCommand, '-i');
            array_push($sshCommand, $sshKeyPath);
        }

        if (!is_null($workingPath)) {
            array_push($sshCommand, "cd $workingPath;");
        }

        $this->localExecutor->execute(array_merge($sshCommand, $command), null, $output, $stderr, $stopOnError);
    }

    public function fileExists($sshKeyPath, $sourceHost, $sourcePath)
    {
        try {
            $executeCommand = array(
                'ssh',
                '-o', 'StrictHostKeyChecking=no',
                '-o', 'UserKnownHostsFile=/dev/null',
                $sourceHost,
                'stat', $sourcePath
            );

            if (!is_null($sshKeyPath)) {
                array_push($executeCommand, '-i');
                array_push($executeCommand, $sshKeyPath);
            }

            $this->localExecutor->execute($executeCommand, null, new BufferedOutput());
        } catch (RuntimeException $exception) {
            return false;
        }

        return true;
    }

    public function fetch($sshKeyPath, $sourceHost, $sourcePath, $targetPath, OutputInterface $output)
    {
        $executeCommand = array(
            'scp',
            '-o', 'StrictHostKeyChecking=no',
            '-o', 'UserKnownHostsFile=/dev/null',
            "$sourceHost:$sourcePath", $targetPath
        );

        if (!is_null($sshKeyPath)) {
            array_push($executeCommand, '-i');
            array_push($executeCommand, $sshKeyPath);
        }

        $this->localExecutor->execute($executeCommand, null, $output);
    }
}
