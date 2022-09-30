<?php
declare(strict_types=1);


namespace VPA\DI;

use PHPUnit\Exception;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionNamedType;

class Container implements ContainerInterface
{
    private static array $classes = [];
    private static bool $bubblePropagation = true;
    private static array $manualConfig;

    function __construct()
    {
    }

    public function setBubblePropagation(bool $bubblePropagation): void
    {
        self::$bubblePropagation = $bubblePropagation;
        $this->reloadContainers();
    }

    /**
     * @param array $manualConfig
     * @throws NotFoundException
     */
    public function registerContainers(array $manualConfig = []): void
    {
        self::$manualConfig = $manualConfig;
        $injectedClasses = [];
        $classes = get_declared_classes();
        $loadedClasses = array_combine($classes, $classes);
        $classesNeedCheck = array_merge($loadedClasses, $manualConfig);
        foreach ($classesNeedCheck as $alias => $class) {
            assert(is_string($class));
            if (class_exists($class)) {
                if ($this->isInjectable($class)) {
                    $injectedClasses[$alias] = $class;
                }
            }
        }
        self::$classes = $injectedClasses;
    }

    private function reloadContainers(): void
    {
        $this->registerContainers(self::$manualConfig);
    }

    private function entityIsInjectable(string $entity): bool
    {
        try {
            assert(class_exists($entity) || interface_exists($entity));
            $reflectionClass = new ReflectionClass($entity);
        } catch (\ReflectionException $e) {
            throw new NotFoundException("VPA\DI\Container::registerClasses: Class $entity not found");
        }
        return !empty($reflectionClass->getAttributes(Injectable::class));
    }

    private function parentClassIsInjectable(string $class): bool
    {
        $parents = class_parents($class);
        return $this->checkInjectableTree($parents);
    }

    private function interfaceIsInjectable(string $class): bool
    {
        $interfaces = class_implements($class);
        return $this->checkInjectableTree($interfaces);
    }

    private function checkInjectableTree(array $tree): bool
    {
        foreach ($tree as $branch) {
            if ($this->entityIsInjectable($branch)) {
                return true;
            }
        }
        return false;
    }

    private function isInjectable(string $class): bool
    {
        if ($this->entityIsInjectable($class)) {
            return true;
        }
        if (self::$bubblePropagation) {
            if ($this->parentClassIsInjectable($class)) {
                return true;
            }
            if ($this->interfaceIsInjectable($class)) {
                return true;
            }
        }
        return false;
    }

    private function prepareObject(string $aliasName, string $className, array $params = []): object
    {
        if ($this->has($className) || $this->isInjectable($className)) {
            return $this->getObject($className, $params);
        }
        throw new NotFoundException("VPA\DI\Container::get('$aliasName->$className'): Class with attribute Injectable not found. Check what class exists and attribute Injectable is set");
    }

    private function getObject(string $className, array $params): object
    {
        assert(class_exists($className));
        $reflectionClass = new ReflectionClass($className);
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
            assert($argumentType instanceof ReflectionNamedType);
            $argumentTypeName = $argumentType->getName();
            if (class_exists($argumentTypeName) || interface_exists($argumentTypeName)) {
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
        $class = self::$classes[$id] ?? $id;
        return (isset(self::$classes[$id]) || $this->isInjectable($class));
    }
}