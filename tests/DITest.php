<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use VPA\DI\Container;
use VPA\DI\Injectable;
use VPA\DI\NotFoundException;

#[Injectable]
class A
{
}

#[Injectable]
class B
{
    public function __construct(protected A $a, private int $num)
    {
    }
}

#[Injectable]
class D
{
    public function __construct(protected A $a)
    {
    }
}

class F extends A
{
}

#[Injectable]
interface H
{
}

class I implements H
{
}

class C
{
    public function __construct(protected A $a)
    {
    }
}


class DITest extends TestCase
{
    /**
     * @var Container
     */
    private Container $di;

    public function setUp(): void
    {
        parent::setUp();
        $this->di = new Container();
        $this->di->registerContainers([
            '\E' => A::class,
            'Tests\H' => I::class
        ]);
    }

    public function testInitAilasedInterface()
    {
        $a = $this->di->get('Tests\H');
        $this->assertTrue($a instanceof I);
    }

    public function testInitClassWithoutDependencies()
    {
        $a = $this->di->get(A::class);
        $this->assertTrue($a instanceof A);
    }

    public function testInitClassWithDependencies()
    {
        $d = $this->di->get(D::class);
        $this->assertTrue($d instanceof D);
    }

    public function testInitClassWithParams()
    {
        $b = $this->di->get(B::class, ['num' => 10]);
        $this->assertTrue($b instanceof B);
    }

    public function testInitClassWithoutAttributeInjection()
    {
        try {
            $this->di->get(C::class);
            $this->assertTrue(false);
        } catch (NotFoundException $e) {
            $this->assertTrue(true);
        }
    }

    public function testInitAilasedClass()
    {
        $a = $this->di->get('\E');
        $this->assertTrue($a instanceof A);
    }

    public function testInitDISingleton()
    {
        $di = new Container();
        $a = $di->get(A::class);
        $this->assertTrue($a instanceof A);
    }

    public function testInitDISingletonAliasedClass()
    {
        $di = new Container();
        $a = $di->get('\E');
        $this->assertTrue($a instanceof A);
    }

    public function testGetClassIfInjectableParentClass()
    {
        $a = $this->di->get(F::class);
        $this->assertTrue($a instanceof F);
    }

    public function testHasInjectableClass()
    {
        $this->assertTrue($this->di->has(A::class));
    }

    public function testHasInjectableParentClass()
    {
        $this->assertTrue($this->di->has(F::class));
    }

    public function testHasNotInjectableClass()
    {
        $this->assertFalse($this->di->has(C::class));
    }

    public function testGetClassIfInjectableInterface()
    {
        $a = $this->di->get(I::class);
        $this->assertTrue($a instanceof I);
    }

    public function testGetClassIfInjectableInterfaceWithDisabledBubblePropagation()
    {
        try {
            $this->di->setBubblePropagation(false);
            $a = $this->di->get(I::class);
            $this->assertFalse($a instanceof I);
        } catch (NotFoundException $e) {
            $this->assertTrue(true);
        }
    }

    public function testHasInjectableParentClassWithDisabledBubblePropagation()
    {
        $this->di->setBubblePropagation(false);
        $this->assertFalse($this->di->has(C::class));
    }

    public function testGetClassIfInjectableParentClassWithDisabledBubblePropagation()
    {
        try {
            $this->di->setBubblePropagation(false);
            $this->di->get(F::class);
        } catch (NotFoundException $e) {
            $this->assertTrue(true);
        }
    }


}