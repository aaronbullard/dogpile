<?php

namespace Dogpile;

class RelationshipCollection extends Collection
{
    const ROOT = "$";

    protected $items = [];

    public function add(string $relationship, ResourceIdentifier ...$identities): RelationshipCollection
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
        return $this->keys();
    }

    public function mergeRelationships(RelationshipCollection $collection): RelationshipCollection
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
            ? Collection::wrap(array_values($this->items[$relationship]))
            : Collection::wrap([]);
    }

    public function isRoot(string $relationship): bool
    {
        return $relationship === static::ROOT;
    }

    public static function parent(string $relationship): string 
    {
        $arr = explode('.', $relationship);

        if(count($arr) == 1){
            return static::ROOT;
        }

        unset($arr[count($arr) - 1]);

        return implode('.', $arr);
    }
}