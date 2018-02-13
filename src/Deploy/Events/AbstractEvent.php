<?php

namespace Deploy\Events;

use Deploy\Extensions\Base\ConfigWrapper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractEvent extends Event
{
    /** @var ConfigWrapper */
    protected $configWrapper;
    /** @var OutputInterface */
    protected $output;

    public function __construct(ConfigWrapper $configWrapper, OutputInterface $output)
    {
        $this->configWrapper = $configWrapper;
        $this->output = $output;
    }

    /**
     * @return ConfigWrapper
     */
    public function getConfigWrapper(): ConfigWrapper
    {
        return $this->configWrapper;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput(): OutputInterface
    {
        return $this->output;
    }
}
