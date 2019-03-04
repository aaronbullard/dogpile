<?php

namespace Dogpile;

use Countable;

class ResourceCollection implements Countable
{
    protected $collection = [];

    public function __construct(Resource ...$resources)
    {
        $this->add(...$resources);
    }

    public function add(Resource ...$resources): ResourceCollection
    {
        foreach($resources as $r){
            $this->collection[static::getKey($r->type(), $r->id())] = $r;
        }

        return $this;
    }

    protected static function getKey($type, $id): string
    {
        return $type . '-' . $id;
    }

    public function merge(ResourceCollection $collection): ResourceCollection
    {
        $resources = $collection->toArray();

        $this->add(...$resources);

        return $this;
    }

    public function count(): int
    {
        return count($this->collection);
    }

    public function has(string $type, string $id): bool
    {
        return isset($this->collection[static::getKey($type, $id)]);
    }

    public function relationships(): RelationshipCollection
    {
        $relationships = new RelationshipCollection();

        foreach($this->toArray() as $resource){
            $relationships->mergeRelationships($resource->relationships());
        }

        return $relationships;
    }

    public function find(string $type, string $id)
    {
        if($this->has($type, $id) === false){
            throw NotFoundException::resource($type, $id);
        }

        return $this->collection[static::getKey($type, $id)];
    }

    public function toArray(): array
    {
        return array_values($this->collection);
    }
}
