<?php


namespace VPA\DI;

use Psr\Container\ContainerInterface;
use ReflectionType;

class Container implements ContainerInterface
{
    private static array $classes = [];

    function __construct()
    {
    }

    public function registerContainers(array $manualConfig = [])
    {
        $injectedClasses = [];
        $classes = get_declared_classes();
        $loadedClasses = array_combine($classes, $classes);
        $classesNeedCheck = array_merge($loadedClasses, $manualConfig);
        foreach ($classesNeedCheck as $alias => $class) {
            $reflectionClass = new \ReflectionClass($class);
            $attributes = $reflectionClass->getAttributes();
            foreach ($attributes as $attribute) {
                $typeOfEntity = $attribute->getName();
                if ($typeOfEntity === 'VPA\DI\Injectable') {
                    $injectedClasses[$alias]=$class;
                }
            }
        }

        self::$classes = $injectedClasses;

        foreach (self::$classes as $aliasName => $className) {
            if (!class_exists($className)) {
                throw new NotFoundException("VPA\DI\Container::registerClasses: Class $className not found");
            }
        }
    }

    private function prepareObject(string $aliasName, string $className, array $params = [])
    {
        assert(class_exists($className));
        $reflectionClass = new \ReflectionClass($className);

        $attributes = $reflectionClass->getAttributes();
        foreach ($attributes as $attribute) {
            $typeOfEntity = $attribute->getName();
            if ($typeOfEntity === 'VPA\DI\Injectable') {
                return $this->getObject($className, $reflectionClass, $params);
            }
        }
        throw new NotFoundException("VPA\DI\Container::get('$aliasName->$className'): Class with attribute Injectable not found. Check what class exists and attribute Injectable is set");
    }

    private function getObject(string $className, \ReflectionClass $reflectionClass, array $params): object
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
            $argumentName = (string)$argument->getName();
            $argumentTypeName = (string)$argumentType->getName();
            assert($argumentType instanceof ReflectionType);
            if (class_exists($argumentTypeName)) {
                $args[$argumentName] = (new Container)->get($argumentTypeName);
            } else {
                $args[$argumentName] = $params[$argumentName];
            }
        }

        return new $className(...$args);
    }


    public function get(string $alias, array $params = []): mixed
    {
        $class = self::$classes[$alias] ?? $alias;
        return $this->prepareObject($alias, $class, $params);
    }

    public function has(string $id): bool
    {
        return isset(self::$classes[$id]);
    }
}