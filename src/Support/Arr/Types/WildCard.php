<?php

namespace Felora\Support\Arr\Types;

use Felora\Contracts\Support\Arr\Types\Type;

class WildCard implements Type
{
    const CHARACTER = '*';

    public function __construct(private array $data)
    {
        //
    }

    public function get()
    {
        return $this->data;
    }
}