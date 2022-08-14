# DI

Simple Dependency Injection pattern implementation for PHP 8.x

To specify the classes for which this pattern can be applied, attributes are used, support for which was added to PHP 8.

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
