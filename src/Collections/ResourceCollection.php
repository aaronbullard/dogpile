<?php

namespace Dogpile\Collections;

use Dogpile\Contracts\Resource;

class ResourceCollection extends Collection
{
    public function __construct(array $resources = [])
    {
        $items = $this->getArrayableItems(array_values($resources));
        
        $this->addResources(...$items);
    }

    public function addResources(Resource ...$resources): ResourceCollection
    {
        foreach($resources as $r){
            $this->items[static::getKey($r->type(), $r->id())] = $r;
        }

        return $this;
    }

    public function merge($collection): ResourceCollection
    {
        if (!$collection instanceof ResourceCollection) {
            throw new \InvalidArgumentException("Parameter 1 of ".get_class($this)."::merge must be of type ".get_class($this));
        }

        $this->addResources(...array_values($collection->all()));

        return $this;
    }

    protected static function getKey($type, $id): string
    {
        return $type . '-' . $id;
    }

    public function exists(string $type, string $id): bool
    {
        return $this->has(static::getKey($type, $id));
    }

    public function find(string $type, string $id)
    {
        if($this->exists($type, $id) === false){
            throw NotFoundException::resource($type, $id);
        }

        return $this->items[static::getKey($type, $id)];
    }

    public function relationships(): RelationshipCollection
    {
        return $this->reduce(function($carry, $resource){
            return $carry->merge($resource->relationships());
        }, new RelationshipCollection());
    }
}
