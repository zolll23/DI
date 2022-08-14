<?php
declare(strict_types=1);

require_once(__DIR__ . '/../vendor/autoload.php');

use VPA\DI\Container;
use VPA\DI\Injectable;

#[Injectable]
class A
{

    public function __construct()
    {
        echo "\n-----\nA\n";
    }

    public function echo()
    {
        print("\nThis is Sparta!\n");
    }

}

#[Injectable]
class B
{

    public function __construct(protected A $a, private int $num)
    {
        echo "\n-----\nB\n";
    }

    public function echo()
    {
        $this->a->echo();
        print("\nThis is Sparta! {$this->num}\n");
    }
}

class C
{

    public function __construct(protected A $a)
    {
        echo "\n-----\nA\n";
    }

    public function echo()
    {
        $this->a->echo();
    }
}

try {
    $di = new Container();
    $di->registerContainers([
        'aaaa' => 'B'
    ]);
    $b = $di->get('aaaa', ['num' => 12]);
    //$b = $di->get(::class);
    $b->echo();
} catch (Exception $e) {
    print($e->getMessage() . "\n");
}