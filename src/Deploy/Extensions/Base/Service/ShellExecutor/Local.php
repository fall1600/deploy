<?php

namespace Deploy\Extensions\Base\Service\ShellExecutor;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class Local
{
    public function execute($command, $workingPath, OutputInterface $output, $stderr = STDERR, $stopOnError = true)
    {
        $process = new Process($command, $workingPath);
        $process->setTimeout(0);
        $process->start();
        $process->wait(function($type, $buffer) use($output, $stderr) {
            if ($type === Process::ERR) {
                if ($stderr !== null) {
                    fwrite($stderr, $buffer);
                }
                return;
            }
            $output->write($buffer);
        });

        if ($statusCode = $process->getExitCode() != 0 && $stopOnError) {
            throw new RuntimeException($process->getExitCodeText(), $statusCode);
        }
    }
}
