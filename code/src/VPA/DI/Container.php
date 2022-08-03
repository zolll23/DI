<?php


namespace VPA\DI;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    static private array $containers = [];
    static private array $acceptors = [];

    public function registerContainers()
    {
        foreach (get_declared_classes() as $className) {
            $reflectionClass = new \ReflectionClass($className);
            $attributes = $reflectionClass->getAttributes();
            foreach ($attributes as $attribute) {
                $typeOfEntity = $attribute->getName();
                switch ($typeOfEntity) {
                    case 'VPA\DI\Injectable':
                        self::$containers[$className] = $this->prepareObject($className);
                        break;
                    case 'VPA\EventSourcing\Inject':
                        self::$acceptors[$className] = $className;
                        break;
                }
            }
        }
        var_dump(self::$containers);
    }

    private function prepareObject(string $className): object
    {
        $classReflector = new \ReflectionClass($className);

        $constructReflector = $classReflector->getConstructor();
        if (empty($constructReflector)) {
            return new $className;
        }

        $constructArguments = $constructReflector->getParameters();
        if (empty($constructArguments)) {
            return new $className;
        }

        $args = [];
        foreach ($constructArguments as $argument) {
            $argumentType = $argument->getType()->getName();
            $args[$argument->getName()] = (new Container)->get($argumentType);
        }

        return new $className(...$args);
    }


    public function get($id)
    {
        var_dump($id);
        if (!$this->has($id)) {
            throw new NotFoundException("Class $id with attribute Injectable not found. Check what class exists and attribute Injectable is set");
        }
        return self::$containers[$id];
    }

    public function has($id)
    {
        return isset(self::$containers[$id]);
    }
}