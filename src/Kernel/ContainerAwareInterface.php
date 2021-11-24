<?php

namespace Lib\Kernel;

interface ContainerAwareInterface
{
    public function getContainer(): Container;

    public function setContainer(Container $container): void;
}
