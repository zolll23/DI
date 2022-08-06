<?php


namespace VPA\DI;

use Psr\Container\ContainerInterface;
use ReflectionType;

class Container implements ContainerInterface
{
    static private array $classes = [];
    static private array $containers = [];

    function __construct(array $manualConfig = []) {
        $classes = get_declared_classes();
        self::$classes = array_combine($classes, $classes);
        self::$classes = array_merge(self::$classes, $manualConfig);
    }

    public function getContainers(): array
    {
        return self::$containers;
    }

    public function registerContainers(): void
    {
        $this->registerClasses(self::$classes);
    }

    private function registerClasses(array $classes)
    {
        foreach ($classes as $aliasName => $className) {
            if (!class_exists($className)) {
                throw new NotFoundException("VPA\DI\Container::registerClasses: Class $className not found");
            }
            $this->prepareObject($aliasName, $className);
        }
    }

    private function prepareObject(string $aliasName, string $className)
    {
        assert(class_exists($className));
        $reflectionClass = new \ReflectionClass($className);

        $attributes = $reflectionClass->getAttributes();
        foreach ($attributes as $attribute) {
            $typeOfEntity = $attribute->getName();
            switch ($typeOfEntity) {
                case 'VPA\DI\Injectable':
                    self::$containers[$aliasName] = $this->getObject($className, $reflectionClass);
                    break;
            }
        }
    }

    private function getObject(string $className, \ReflectionClass $reflectionClass): object
    {
        $constructReflector = $reflectionClass->getConstructor();
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


    public function get(string $alias): mixed
    {
        $class = self::$classes[$alias] ?? $alias;
        $this->prepareObject($alias, $class);
        if (!$this->has($alias)) {
            throw new NotFoundException("VPA\DI\Container::get('$alias->$class'): Class with attribute Injectable not found. Check what class exists and attribute Injectable is set");
        }
        return self::$containers[$alias];
    }

    public function has(string $id): bool
    {
        return isset(self::$containers[$id]);
    }
}