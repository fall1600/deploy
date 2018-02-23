<?php

namespace Deploy\Extensions\Base\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class DumpCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('dump')
            ->setDescription('建立設定檔')
            ->setHelp(
                <<<EOT
<info>{$_SERVER['argv'][0]} dump</info>
EOT
            )
            ->addArgument('output', InputArgument::REQUIRED, '產生設定檔路徑')
            ->addOption('force', null, InputOption::VALUE_OPTIONAL, '強制寫入')
            ->applyOptions()
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->processConfig($input);
        unset($config['path']);
        $force = $input->getOption('force');
        $outputFile = $input->getArgument('output');
        if (file_exists($outputFile) && !$force) {
            $output->writeln("$outputFile\t 已存在請加上 --force 覆蓋");
        }
        file_put_contents($outputFile, Yaml::dump(array('build' => $config), 10, 4));
        $output->writeln("設定檔輸出至 $outputFile");
    }
}
