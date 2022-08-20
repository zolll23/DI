# DI

[![Latest Stable Version](http://poser.pugx.org/vpa/di/v)](https://packagist.org/packages/vpa/di) 
[![Total Downloads](http://poser.pugx.org/vpa/di/downloads)](https://packagist.org/packages/vpa/di) 
[![Monthly Downloads](http://poser.pugx.org/vpa/di/d/monthly)](https://packagist.org/packages/vpa/di)
[![Latest Unstable Version](http://poser.pugx.org/vpa/di/v/unstable)](https://packagist.org/packages/vpa/di) 
[![License](http://poser.pugx.org/vpa/di/license)](https://packagist.org/packages/vpa/di) 
[![PHP Version Require](http://poser.pugx.org/vpa/di/require/php)](https://packagist.org/packages/vpa/di)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/zolll23/DI/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/zolll23/DI/?branch=main)
[![Code Coverage](https://scrutinizer-ci.com/g/zolll23/DI/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/zolll23/DI/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/zolll23/DI/badges/build.png?b=main)](https://scrutinizer-ci.com/g/zolll23/DI/build-status/main)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/zolll23/DI/badges/code-intelligence.svg?b=main)](https://scrutinizer-ci.com/code-intelligence)

Simple Dependency Injection pattern implementation PSR-11 (Psr\Container\ContainerInterface) for PHP 8.x 

To specify the classes for which this pattern can be applied, attributes are used, support for which was added to PHP 8.

**Install**

```
composer require vpa/di
```

**Example**:

```
require_once(__DIR__ . '/../vendor/autoload.php');

use VPA\DI\Container;
use VPA\DI\Injectable;

#[Injectable]
class A {

    function __construct() {}
    function echo () {
        print("\nThis is Sparta!\n");
    }
}

#[Injectable]
class B {

    function __construct(protected A $a) {}
    function echo () {
        $this->a->echo();
    }
}

class C {

    function __construct(protected A $a) {}
    function echo () {
        $this->a->echo();
    }
}

try {
    $di = new Container();
    $di->registerContainers();
    $b = $di->get(B::class); // returns instance of class B
    $b->echo();
    $c = $di->get(C::class); // returns exception (class C not tagged as Injectable)
    $c->echo();
} catch (Exception $e) {
    print($e->getMessage()."\n");
}
```

You can add aliased classes manually, but the declaration of these classes must still have the #[Injecatble] tag.
```
$di = new Container();
$di->registerContainers(['E'=>A::class]);
$e = $di->get('E');
echo $e instanceof A; // returns true
```

If your class has a constructor with parameters (and the types of those parameters are not an object) you can pass those parameters as the second parameter of the get method as an array:
```
#[Injectable]
class A {
    function __construct() {}
}
#[Injectable]
class B {

    function __construct(protected A $a, private int $x, private int $y) {}
}

$di = new Container();
$di->registerContainers();
$b = $di->get(B::class,['x'=>10,'y'=>20]);
```

**Bubble Propagation for attribute Injectable**

In version 0.2.0 added method ```setBubblePropagation(bool $bubblePropogation)```
(default is true) which specifies whether parent classes should be checked for the presence of an attribute _Injectable_. Еhis allows you to not set the attribute _Injectable_ to all children that should be DI.

Example:
```
// In versions less 0.2.0 code below returns Exception, in version 0.2.0 and great - will return the desired class 
#[Injectable]
class A {}
class B extends A {} // this class is not marked with the attribute Injectable
$di = new Container();
$di->registerContainers();
$b = $di->get(B::class);
```
