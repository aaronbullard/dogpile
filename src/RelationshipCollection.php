<?php

namespace JsonApiRepository;

class RelationshipCollection
{
    protected $relatives = [];

    public function add(string $relationshipType, ResourceIdentifier ...$resourceIdentifiers): RelationshipCollection
    {
        if(!isset($this->relatives[$relationshipType])){
            $this->relatives[$relationshipType] = [];
        }

        foreach($resourceIdentifiers as $ri){
            $this->relatives[$relationshipType][$ri->id()] = $ri;
        }
        
        return $this;
    }

    public function listRelationships(): array
    {
        return array_keys($this->relatives);
    }

    public function merge(RelationshipCollection $collection): RelationshipCollection
    {
        $relTypes = $collection->listRelationships();

        foreach($relTypes as $type){
            $this->add($type, ...$collection->resourceIdentifiersFor($type));
        }

        return $this;
    }

    public function resourceIdentifiersFor(string $relationshipType): array
    {
        if(false == in_array($relationshipType, $this->listRelationships())){
            return [];
        }

        return array_values($this->relatives[$relationshipType]);
    }

    public function getIdsFor(string $relationshipType): array
    {
        return array_map(function($ri) {
            return $ri->id();
        }, $this->resourceIdentifiersFor($relationshipType));
    }
}