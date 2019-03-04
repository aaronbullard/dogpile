<?php

namespace Dogpile\Tests\Stubs;

use Dogpile\Contracts\Resource;
use Dogpile\Collections\RelationshipCollection;

class Model implements Resource
{
    protected $type;

    protected $id;

    protected $relatives;

    public function __construct(string $type, string $id)
    {
        $this->type = $type;
        $this->id = $id;
        $this->relatives = new RelationshipCollection();
    }

    public static function create(string $type, string $id): Model
    {
        return new static($type, $id);
    }

    public function type(): string
    {
        return $this->type;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function relationships(): RelationshipCollection
    {
        return $this->relatives;
    } 
}