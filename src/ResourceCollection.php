<?php

namespace Dogpile;

class ResourceCollection extends Collection
{
    public function __construct(Resource ...$resources)
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
        return isset($this->items[static::getKey($type, $id)]);
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
        $relationships = new RelationshipCollection();

        foreach($this->toArray() as $resource){
            $relationships->mergeRelationships($resource->relationships());
        }

        return $relationships;
    }
}
