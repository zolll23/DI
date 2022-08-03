<?php


namespace VPA\DI;

#[\Attribute]
abstract class Injectable
{
    function __construct(protected string $className)
    {
    }
}