<?php

namespace Lib\Kernel;

trait ContainerAwareTrait
{
    protected Container $container;

    public function setContainer(Container $container): void
    {
        $this->container = $container;
    }
}