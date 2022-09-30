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

    public function getNum(): int
    {
        return $this->num;
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

#[Injectable]
class K
{
    // Constructor with empty params
    public function __construct()
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
            'Tests\H' => I::class,
        ]);
    }

    public function testNotExistedClass()
    {
        $this->expectException(NotFoundException::class);
        $this->di->get('notExist');
    }

    public function testInitAilasedInterface()
    {
        $a = $this->di->get('Tests\H');
        $this->assertTrue($a instanceof I);
    }

    public function testInitClassWithEmptyConstructor()
    {
        $k = $this->di->get(K::class);
        $this->assertTrue($k instanceof K);
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
        $num = $b->getNum();
        $this->assertTrue($num === 10);
    }

    public function testInitClassWithoutAttributeInjection()
    {
        $this->expectException(NotFoundException::class);
        $this->di->get(C::class);
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
        $this->expectException(NotFoundException::class);
        $this->di->setBubblePropagation(false);
        $a = $this->di->get(I::class);
        $this->assertFalse($a instanceof I);
    }

    public function testHasInjectableParentClassWithDisabledBubblePropagation()
    {
        $this->di->setBubblePropagation(false);
        $this->assertFalse($this->di->has(C::class));
    }

    public function testGetClassIfInjectableParentClassWithDisabledBubblePropagation()
    {
        $this->expectException(NotFoundException::class);
        $this->di->setBubblePropagation(false);
        $this->di->get(F::class);
    }


}