<?php

namespace Deploy\Extensions\Base;

use Deploy\Events\AbstractEvent;
use Deploy\Events\BuildSourceEvent;
use Deploy\Events\FetchSourceEvent;
use Deploy\Events\PreBuildSourceEvent;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Deploy
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    public function injectEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function deploy($config, OutputInterface $output)
    {
        $configWrapper = new ConfigWrapper($config);
        try {
            $this->fetchSource($configWrapper, $output);
            $this->preBuildSource($configWrapper, $output);
            $this->buildSource($configWrapper, $output);
        } catch (RuntimeException $exception) {
            $output->writeln($exception->getMessage());
            return $exception->getCode();
        }
        return 0;
    }

    protected function fetchSource(ConfigWrapper $configWrapper, OutputInterface $output)
    {
        $event = new FetchSourceEvent($configWrapper, $output);
        $this->dispatch($event);
        return $event;
    }

    protected function preBuildSource(ConfigWrapper $configWrapper, OutputInterface $output)
    {
        $event = new PreBuildSourceEvent($configWrapper, $output);
        $this->dispatch($event);
        return $event;
    }

    protected function buildSource(ConfigWrapper $configWrapper, OutputInterface $output)
    {
        $event = new BuildSourceEvent($configWrapper, $output);
        $this->dispatch($event);
        return $event;
    }

    protected function dispatch(AbstractEvent $event)
    {
        $this->eventDispatcher->dispatch($event::EVENT_NAME, $event);
        return $event;
    }
}
