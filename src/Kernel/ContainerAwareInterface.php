<?php

namespace Lib\Kernel;

interface ContainerAwareInterface
{
    public function setContainer(Container $container): void;
}
