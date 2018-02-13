<?php

namespace Deploy;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;

class Kernel
{
    /** @var ContainerBuilder $container */
    protected $container;

    /** @var AbstractExtension[] $extensions */
    protected $extensions = array();

    public function __construct()
    {
        $this->init();
    }

    public function handle(InputInterface $input = null, OutputInterface $output = null)
    {
        return $this->container->get('application')->run($input, $output);
    }

    protected function init()
    {
        $this->initContainer();
        $this->initExtensions();
        $container = $this->getContainer();

        foreach ($this->getExtensions() as $extension) {
            $extension->setContainer($container);
            $extension->boot();
        }
        $this->initEventListeners();
        $this->initCommands();
    }

    protected function initContainer()
    {
        $this->container = new ContainerBuilder();
    }

    /**
     * @return AbstractExtension[]
     */
    protected function initExtensions()
    {
        $finder = new Finder();
        /** @var \SplFileInfo $dirs */
        $dirs = $finder->directories()->in(__DIR__."/Extensions")->depth(0);
        foreach ($dirs as $dir) {
            $extensionClass = "Deploy\\Extensions\\{$dir->getBaseName()}\\DependencyInjection\\{$dir->getBaseName()}Extension";
            $this->registerExtension($extensionClass);
        }
    }

    protected function registerExtension($extensionClass)
    {
        if (!class_exists($extensionClass)) {
            return;
        }

        $extension = new $extensionClass();

        if (!($extension instanceof AbstractExtension)) {
            return;
        }

        array_push($this->extensions, $extension);
    }

    protected function initEventListeners()
    {
        $eventDispatcher = $this->container->get('event_dispatcher');
        foreach ($this->container->findTaggedServiceIds("event_listener") as $serviceId => $listeners) {
            $service = $this->container->get($serviceId);
            foreach ($listeners as $listener) {
                if (($listener['event']??null) && is_callable(array($service, $listener['method']??null))) {
                    $eventDispatcher->addListener($listener['event'], array($service, $listener['method']), (int)( $listener['priority']??0));
                }
            }
        }
    }

    protected function initCommands()
    {
        /** @var Application $application */
        $application = $this->container->get("application");
        foreach ($this->container->findTaggedServiceIds('command') as $serviceId => $config) {
            $service = $this->container->get($serviceId);
            if ($service instanceof ContainerAwareCommand) {
                $service->setContainer($this->getContainer());
                $application->add($service);
            }
        }
    }
    
    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return AbstractExtension[]
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }
}
