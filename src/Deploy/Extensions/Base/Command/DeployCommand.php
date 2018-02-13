<?php

namespace Deploy\Extensions\Base\Command;

use Deploy\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeployCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName("deploy")
            ->setDescription("發佈專案")
            ->setHelp(<<<EOT
<info>{$_SERVER['argv'][0]} build</info>
EOT
            )
            ->applyOptions()
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->processConfig($input);
        return $this->container->get("deploy")->deploy($config, $output);
    }
}
