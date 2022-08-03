<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use VPA\DI\Container;
use VPA\DI\Injectable;

#[Injectable]
class A {

    function __construct() {
        echo "\n-----\nA\n";
    }
    function echo () {
        print("\nThis is Sparta!\n");
    }

}

#[Injectable]
class B {

    function __construct(protected A $a) {
        echo "\n-----\nA\n";
    }
    function echo () {
        $this->a->echo();
    }
}

class C {

    function __construct(protected A $a) {
        echo "\n-----\nA\n";
    }
    function echo () {
        $this->a->echo();
    }
}

try {
    $di = new Container();
    $di->registerContainers();
    $b = $di->get(B::class);
    $b->echo();
} catch (Exception $e) {
    print($e->getMessage()."\n");
}