<?php

namespace JsonApiRepository;

class ResourceIdentifier implements Resource
{
    protected $type;

    protected $id;

    public function __construct(string $type, string $id)
    {
        $this->type = $type;
        $this->id = $id;
    }

    public static function create(string $type, string $id): ResourceIdentifier
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

    public function equals(ResourceIdentifier $resourceIdentifier): bool
    {
        return $this->type() === $resourceIdentifier->type() && $this->id() === $resourceIdentifier->id();
    }
}