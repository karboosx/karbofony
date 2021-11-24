<?php

namespace Lib\Kernel;

class Container
{
    private array $concrete = [];
    private array $params = [];
    private array $aliases = [];

    public function get(string $id, array $params = [])
    {
        if (array_key_exists($id, $this->aliases)) {
            $id = $this->aliases[$id];
        }

        if (array_key_exists($id, $this->concrete)) {
            return $this->concrete[$id];
        }

        return $this->make($id, $params);
    }

    public function setConcrete(string $id, $object)
    {
        $this->concrete[$id] = $object;
    }

    public function setAlias(string $id, $object)
    {
        $this->aliases[$id] = $object;
    }

    public function setConcreteClass($object)
    {
        $this->setConcrete(get_class($object), $object);
    }

    public function make(string $class, array $overrideParams = [])
    {
        $reflectionClass = new \ReflectionClass($class);
        $constructor = $reflectionClass->getConstructor();

        if ($constructor === null) {
            return $this->build($class, []);
        }

        $params = [];

        foreach ($constructor->getParameters() as $reflectionParameter) {

            if (array_key_exists($reflectionParameter->getName(), $overrideParams)) {
                $params[] = $overrideParams[$reflectionParameter->getName()];
                continue;
            }

            $methodName = $this->getMethodName($class, $reflectionParameter->getName());

            if (array_key_exists($methodName, $this->params)) {
                $params[] = $this->params[$methodName];
                continue;
            }

            $paramClass = $reflectionParameter->getClass();

            if ($paramClass === null) {
                throw new ContainerException("Parameter \"$reflectionParameter->name\" can't be initialized for $class!");
            }

            $params[] = $this->get($paramClass->getName());
        }

        return $this->build($class, $params);
    }

    public function setParam(string $className, string $parameterName, $value)
    {
        $this->params[$this->getMethodName($className, $parameterName)] = $value;
    }

    private function getMethodName(string $class, string $name): string
    {
        return $class . '::' . $name;
    }

    private function build(string $class, array $params)
    {
        $object =  new $class(...$params);

        if ($object instanceof ContainerAwareInterface) {
            $object->setContainer($this);
        }

        return $object;
    }
}
