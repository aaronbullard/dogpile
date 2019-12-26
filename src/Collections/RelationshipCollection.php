<?php

namespace Dogpile\Collections;

use Dogpile\ResourceIdentifier;

class RelationshipCollection extends Collection
{
    public function addRelationships(string $relationship, ResourceIdentifier ...$identities): RelationshipCollection
    {
        if(false === $this->has($relationship)){
            $this->items[$relationship] = [];
        }

        foreach($identities as $i){
            $key = $i->type() . '-' . $i->id();
            $this->items[$relationship][$key] = $i;
        }
        
        return $this;
    }

    public function listRelationships(): Collection
    {
        return $this->keys()->sort()->values();
    }

    public function merge($collection): RelationshipCollection
    {
        if (!$collection instanceof RelationshipCollection) {
            throw new \InvalidArgumentException("Parameter 1 of ".get_class($this)."::merge must be of type ".get_class($this));
        }

        $relTypes = $collection->listRelationships();

        foreach($relTypes as $type){
            $this->addRelationships($type, ...$collection->identifiersFor($type));
        }

        return $this;
    }

    public function identifiersFor(string $relationship): Collection
    {
        return $this->has($relationship) 
            ? Collection::wrap($this->items[$relationship])->values()
            : Collection::wrap([]);
    }
}