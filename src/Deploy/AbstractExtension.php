<?php

namespace Deploy;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

abstract class AbstractExtension extends Extension
{
    /** @var ContainerBuilder */
    protected $container;

    public function setContainer(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    public function boot()
    {
        $this->load(array(), $this->container);
    }
}
