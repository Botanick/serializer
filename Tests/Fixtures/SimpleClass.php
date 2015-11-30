<?php

namespace Botanick\Serializer\Tests\Fixtures;

class SimpleClass
{
    public $a = 1;

    public $b = 'b';

    public $null = null;

    public function getC()
    {
        return 'c';
    }

    public $propD = 'd';
}