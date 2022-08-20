<?php
declare(strict_types=1);


namespace VPA\DI;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    private static array $classes = [];
    private static bool $bubblePropagation = true;

    function __construct()
    {
    }

    public function setBubblePropagation(bool $bubblePropagation): void
    {
        self::$bubblePropagation = $bubblePropagation;
    }

    public function registerContainers(array $manualConfig = []): void
    {
        $injectedClasses = [];
        $classes = get_declared_classes();
        $loadedClasses = array_combine($classes, $classes);
        $classesNeedCheck = array_merge($loadedClasses, $manualConfig);
        foreach ($classesNeedCheck as $alias => $class) {
            assert(is_string($class));
            if (class_exists($class)) {
                if ($this->classIsInjectable($class)) {
                    $injectedClasses[$alias] = $class;
                }
                if (self::$bubblePropagation) {
                    $parents = class_parents($class);
                    foreach ($parents as $parent) {
                        if ($this->classIsInjectable($parent)) {
                            $injectedClasses[$alias] = $class;
                            break;
                        }
                    }
                }
            } else {
                throw new NotFoundException("VPA\DI\Container::registerClasses: Class $class not found");
            }
        }
        self::$classes = $injectedClasses;
    }

    private function classIsInjectable(string $class): bool
    {
        assert(class_exists($class));
        $reflectionClass = new \ReflectionClass($class);
        $attributes = $reflectionClass->getAttributes();
        foreach ($attributes as $attribute) {
            $typeOfEntity = $attribute->getName();
            if ($typeOfEntity === 'VPA\DI\Injectable') {
                return true;
            }
        }
        return false;
    }

    private function prepareObject(string $aliasName, string $className, array $params = []): object
    {
        if ($this->has($className) || $this->classIsInjectable($className)) {
            return $this->getObject($className, $params);
        }
        throw new NotFoundException("VPA\DI\Container::get('$aliasName->$className'): Class with attribute Injectable not found. Check what class exists and attribute Injectable is set");
    }

    private function getObject(string $className, array $params): object
    {
        assert(class_exists($className));
        $reflectionClass = new \ReflectionClass($className);
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
            $argumentName = $argument->getName();
            assert($argumentType instanceof \ReflectionNamedType);
            $argumentTypeName = $argumentType->getName();
            if (class_exists($argumentTypeName)) {
                $args[$argumentName] = $this->get($argumentTypeName);
            } else {
                $args[$argumentName] = $params[$argumentName] ?? null;
            }
        }

        return new $className(...$args);
    }


    public function get(string $id, array $params = []): object
    {
        $class = self::$classes[$id] ?? $id;
        assert(is_string($class));
        return $this->prepareObject($id, $class, $params);
    }

    public function has(string $id): bool
    {
        return isset(self::$classes[$id]);
    }
}