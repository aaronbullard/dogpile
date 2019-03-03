<?php

namespace Dogpile;

class RelationshipCollection
{
    protected $items = [];

    public function add(string $relationship, ResourceIdentifier ...$identities): RelationshipCollection
    {
        if(!isset($this->items[$relationship])){
            $this->items[$relationship] = [];
        }

        foreach($identities as $i){
            $key = $i->type() . '-' . $i->id();
            $this->items[$relationship][$key] = $i;
        }
        
        return $this;
    }

    public function has(string $relationship): bool
    {
        return isset($this->items[$relationship]);
    }

    public function listRelationships(): array
    {
        return array_keys($this->items);
    }

    public function merge(RelationshipCollection $collection): RelationshipCollection
    {
        $relTypes = $collection->listRelationships();

        foreach($relTypes as $type){
            $this->add($type, ...$collection->identifiersFor($type));
        }

        return $this;
    }

    public function identifiersFor(string $relationship): Collection
    {
        return $this->has($relationship) 
            ? new Collection(array_values($this->items[$relationship]))
            : new Collection();
    }
}