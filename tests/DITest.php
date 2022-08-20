<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use VPA\DI\Container;
use VPA\DI\Injectable;
use VPA\DI\NotFoundException;

#[Injectable]
class A {
}

#[Injectable]
class B {

    public function __construct(protected A $a, private int $num) {
    }
}

#[Injectable]
class D {

    public function __construct(protected A $a) {
    }
}

class F extends D {
}

class C {

    public function __construct(protected A $a) {
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
            '\E'=>A::class
        ]);
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
        $b = $this->di->get(B::class, ['num'=>10]);
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
        $di = new Container();
        $a = $di->get(F::class);
        $this->assertTrue($a instanceof F);
    }

    public function testGetClassIfInjectableParentClassWithDisabledBubblePropagation()
    {
        $di = new Container();
        //$di->setBubblePropagation(false);
        $a = $di->get(F::class);
        $this->assertTrue($a instanceof F);
    }

}