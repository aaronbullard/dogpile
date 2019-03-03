<?php

namespace Dogpile;

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

    public function resolve(string $path): void
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

        $resources = $this->includes->identifiersFor($path)
            // filter out identifiers for resources we already have
            ->filter(function($identifier){
                return false === $this->resources->has($identifier->type(), $identifier->id());
            })
            // group identifiers by their type for group querying
            ->groupBy(function($identifier){ return $identifier->type(); })
            // map Identifiers to Resources
            ->map(function($identifiers, $type){
                return $this->manager->repositoryFor($type)->findHavingIds(
                    $identifiers->map(function($identifier){ return $identifier->id(); })->toArray()
                );
            })
            // get rid of hash grouping by type
            ->flatten()
            ->reduce(function($carry, $resources){
                return $carry->merge($resources);
            }, new ResourceCollection());

        // roll new resources into the ResourceCollection singleton
        $this->resources->merge($resources);

        // update IncludesCollection with new child relationships for other queries
        foreach($resources->relationships()->listRelationships() as $relationshipType){
            $relatedIdentifiers = $resources->relationships()->identifiersFor($relationshipType);

            $newPath = sprintf("%s.%s", $path, $relationshipType);

            $this->includes->add($newPath, ...$relatedIdentifiers);
        }
    }   
}