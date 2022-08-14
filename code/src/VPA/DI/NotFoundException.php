<?php
declare(strict_types=1);


namespace VPA\DI;


use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends \Exception implements NotFoundExceptionInterface
{

}