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
            ->addOption('frontend', 'f', InputOption::VALUE_OPTIONAL, 'frontend 名稱', 'frontend')
            ->addOption('backend', 'b', InputOption::VALUE_OPTIONAL, 'backend 名稱', 'backend')
            ->addOption('server', 's', InputOption::VALUE_OPTIONAL, '發佈 server [0.service.dgfactor.com,1.service.dgfacgtor.com]')
            ->addOption('web-server', null, InputOption::VALUE_OPTIONAL, '前台 web 發佈 server [0.www.dgfactor.com,1.www.dgfactor.com]')
            ->addOption('admin-server', null, InputOption::VALUE_OPTIONAL, '後台 admin 發佈 server [0.admin.dgfactor.com,1.admin.dgfactor.com]')
            ->addOption('service-server', null, InputOption::VALUE_OPTIONAL, 'api server 發佈 server [0.service.dgfactor.com,1.service.dgfactor.com]')
            ->addOption('host', null, InputOption::VALUE_OPTIONAL, '網站網址 [dgfactor.com]')
            ->addOption('web-host', null, InputOption::VALUE_OPTIONAL, '前台 web 網址 www.{host}]')
            ->addOption('admin-host', null, InputOption::VALUE_OPTIONAL, '後台 admin 網址 [admin.{host}]')
            ->addOption('service-host', null, InputOption::VALUE_OPTIONAL, 'api service 網址 [service.{host}]')
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
        $config['build']['source']['frontend'] = $input->getOption('frontend');
        $config['build']['source']['backend'] = $input->getOption('backend');
        $config['build']['target']['web']['host'] = $this->createPrefixParam('www', $input->getOption('host'), $input->getOption('web-host'));
        $config['build']['target']['admin']['host'] = $this->createPrefixParam('admin', $input->getOption('host'), $input->getOption('admin-host'));
        $config['build']['target']['service']['host'] = $this->createPrefixParam('service', $input->getOption('host'), $input->getOption('service-host'));

        $config['build']['target']['web']['server'] = $this->createServerParam($input->getOption('server'), $input->getOption('web-server'));
        $config['build']['target']['admin']['server'] = $this->createServerParam($input->getOption('server'), $input->getOption('admin-server'));
        $config['build']['target']['service']['server'] = $this->createServerParam($input->getOption('server'), $input->getOption('service-server'));
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

    protected function createPrefixParam($prefix, $baseHost, $host)
    {
        if (!is_null($host)) {
            return $host;
        }

        if (!is_null($baseHost)) {
            return "$prefix.$baseHost";
        }

        return null;
    }

    protected function createServerParam($baseHost, $host)
    {
        if (!is_null($host)) {
            return explode(",", $host);
        }

        if (!is_null($baseHost)) {
            return explode(',', $baseHost);
        }

        return null;
    }
}
