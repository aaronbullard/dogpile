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

    public function mergeRelationships(RelationshipCollection $collection): RelationshipCollection
    {
        $relTypes = $collection->listRelationships();
try{
        foreach($relTypes as $type){
            $this->addRelationships($type, ...$collection->identifiersFor($type));
        }
}
catch(\Throwable $e){
    dd($relTypes);
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