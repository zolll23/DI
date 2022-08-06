<?php


namespace VPA\DI;

use Psr\Container\ContainerInterface;
use ReflectionType;

class Container implements ContainerInterface
{
    static private array $containers = [];

    public function registerContainers(): void
    {
        $classes = get_declared_classes();
        $this->registerClasses(array_combine($classes, $classes));
    }

    public function registerManually(array $classes)
    {
        $this->registerClasses($classes);
    }

    private function registerClasses(array $classes)
    {
        foreach ($classes as $className) {
            $reflectionClass = new \ReflectionClass($className);
            $attributes = $reflectionClass->getAttributes();
            foreach ($attributes as $attribute) {
                $typeOfEntity = $attribute->getName();
                switch ($typeOfEntity) {
                    case 'VPA\DI\Injectable':
                        self::$containers[$className] = $this->prepareObject($className);
                        break;
                }
            }
        }
    }

    private function prepareObject(string $className): object
    {
        assert(class_exists($className));
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
            $argumentType = $argument->getType();
            assert($argumentType instanceof ReflectionType);
            $args[$argument->getName()] = (new Container)->get((string)$argumentType->getName());
        }

        return new $className(...$args);
    }


    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            throw new NotFoundException("Class $id with attribute Injectable not found. Check what class exists and attribute Injectable is set");
        }
        return self::$containers[$id];
    }

    public function has(string $id): bool
    {
        return isset(self::$containers[$id]);
    }
}