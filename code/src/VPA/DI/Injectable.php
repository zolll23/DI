<?php


namespace VPA\DI;

#[\Attribute]
class Injectable
{
    function __construct(protected string $className)
    {
    }
}