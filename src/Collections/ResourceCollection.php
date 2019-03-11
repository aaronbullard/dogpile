<?php

namespace Dogpile\Collections;

use Dogpile\Contracts\Resource;

class ResourceCollection extends Collection
{
    public function __construct(array $resources = [])
    {
        $this->add(...$resources);
    }

    public function add(Resource ...$resources): ResourceCollection
    {
        foreach($resources as $r){
            $this->items[static::getKey($r->type(), $r->id())] = $r;
        }

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
            return $carry->mergeRelationships($resource->relationships());
        }, new RelationshipCollection());
    }
}
