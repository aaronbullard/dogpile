<?php

namespace Dogpile\Collections;

use Tightenco\Collect\Support\Collection as TightenCollection;

class Collection extends TightenCollection
{
    public function toArray(): array
    {
        return $this->all();
    }
}