<?php

namespace TCG\Voyager\Events;

class MenuDisplay
{
    public function __construct(public readonly mixed $menu)
    {
    }
}
