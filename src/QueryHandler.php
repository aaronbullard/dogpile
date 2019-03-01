<?php

namespace JsonApiRepository;

class QueryHandler
{
    protected $manager;

    protected $includes;

    protected $resouces;

    public function __construct(ResourceManager $manager, IncludesCollection $includes, ResourceCollection $resources)
    {
        $this->manager      = $manager;
        $this->includes     = $includes;
        $this->resources    = $resources;
    }

    public static function create(ResourceManager $manager, IncludesCollection $includes, ResourceCollection $resources): QueryHandler
    {
        return new static($manager, $includes, $resources);
    }

    public function resolve(string $path)
    {
        // If recursion brought us to the root, stop
        if($this->includes->isRoot($path)){
            return;
        }

        // do we have the identifiers 
        if(!$this->includes->has($path)){
            // (inception...) Let's get resources for our parent
            $this->resolve($this->includes->parent($path));
        }

        // get identifiers, do we have resources?
        $identifiers = $this->includes->identifiersFor($path)
                            ->filter(function($identifier){
                                return false === $this->resources->has($identifier->type(), $identifier->id());
                            });

        // Identifiers may be mixed types, need to separate, query, then join
        $types = $identifiers->map(function($identifier){ return $identifier->type(); })->unique();

        foreach($types as $type){
            $ids = $identifiers->map(function($identifier){ return $identifier->id(); })->values()->toArray();

            $resources = $this->manager->repositoryFor($type)->findHavingIds($ids);

            // Add new identifiers to IncludesCollection for other queries
            foreach($resources->relationships()->listRelationships() as $relationshipType){
                $newPath = sprintf("%s.%s", $path, $relationshipType);

                $relatedIdentifiers = $resources->relationships()->resourceIdentifiersFor($relationshipType);

                $this->includes->add($newPath, ...$relatedIdentifiers);
            }

            // Add resources to ResourcesCollection
            $this->resources->merge($resources);
        }
    }   
}