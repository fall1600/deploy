<?php

namespace Deploy\Extensions\Base\Command;

use Deploy\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

abstract class BaseCommand extends ContainerAwareCommand
{
    protected $defaultConfig;

    /**
     * @return $this
     */
    protected function applyOptions()
    {
        return $this
            ->addOption('path', 'p', InputOption::VALUE_OPTIONAL, '專案 git source 暫存路徑', posix_getcwd())
            ->addOption('repo', null, InputOption::VALUE_OPTIONAL, '專案 git source 來源')
            ->addOption('revision', 'r', InputOption::VALUE_OPTIONAL, '專案 revision [master, develop, relase, tag]')
            ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, '設定檔')
            ;

    }

    protected function processConfig(InputInterface $input): array
    {
        $optionConfig = $this->readOptionConfig($input);
        $configFileConfig = $this->readConfigFileConfig($input->getOption('config'));
        $defaultConfig = $this->readDefaultConfig();
        $buildConfig = $this->container->get("build_config");
        $result = array_replace_recursive($defaultConfig, $this->filterNullNode($optionConfig), $this->filterNullNode($configFileConfig));
        $processor = new Processor();
        $config = $processor->processConfiguration($buildConfig, $result);
        return $config;
    }

    protected function readOptionConfig(InputInterface $input)
    {
        $config = $this->readDefaultConfig();
        $config['build']['source']['path'] = $input->getOption('path');
        $config['build']['source']['repo'] = $input->getOption('repo');
        $config['build']['source']['revision'] = $input->getOption('revision');
        return $config;
    }

    protected function readConfigFileConfig(string $configFile = null)
    {
        if ($configFile === null || !file_exists($configFile)) {
            return array();
        }
        return Yaml::parse(file_get_contents($configFile));
    }
    
    protected function readDefaultConfig()
    {
        if ($this->defaultConfig === null) {
            $this->defaultConfig = Yaml::parse(file_get_contents(__DIR__.'/../Resources/config/build.yml'));
        }
        return $this->defaultConfig;
    }

    protected function filterNullNode($array)
    {
        if (!is_array($array)) {
            return $array;
        }

        foreach ($array as $key => $value) {
            if ($value === null) {
                unset($array[$key]);
                continue;
            }
            $array[$key] = $this->filterNullNode($value);
        }
        return $array;
    }
}
