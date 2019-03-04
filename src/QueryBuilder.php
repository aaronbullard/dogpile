<?php

namespace Dogpile;

use Dogpile\Collections\Collection;
use Dogpile\Collections\ResourceCollection;
use Dogpile\Collections\RelationshipCollection;

class QueryBuilder
{
    protected $manager;

    protected $includes;

    protected $resources;

    protected $paths;

    public function __construct(ResourceManager $manager)
    {
        $this->manager      = $manager;
        $this->includes     = new RelationshipCollection();
        $this->resources    = new ResourceCollection();
    }

    public function includesCollection(): RelationshipCollection
    {
        return $this->includes;
    }

    public function resourceCollection(): ResourceCollection
    {
        return $this->resources;
    }

    public function setRelationships(RelationshipCollection $relationships): QueryBuilder
    {
        $this->relationships = $relationships;

        foreach($relationships->listRelationships() as $path){
            $this->includes->add($path, ...$relationships->identifiersFor($path));
        }

        return $this;
    }

    public function include(string ...$paths): QueryBuilder
    {
        // remove duplicates
        $paths = array_unique($paths);

        // Sorting allows parents to go before children e.g. author, author.comments
        sort($paths);

        $this->paths = $paths;

        return $this;
    }

    public function query(): ResourceCollection
    {
        foreach($this->paths as $path){
            $this->resolve($path);
        }

        // At this point, all resources have been gathered
        // Let's return only what was requested

        // get identifiers for just the includes that were requested
        $identifiers = new Collection();
        foreach($this->paths as $path){
            $identifiers = $this->includes->identifiersFor($path)->merge($identifiers);
        }

        // return back a collection of resources that were requested
        $resources = new ResourceCollection();
        foreach($identifiers as $identifier){
            $resources->add(
                $this->resources->find($identifier->type(), $identifier->id())
            );
        }

        return $resources;
    }

    protected function resolve(string $path): void
    {
        // If recursion brought us to the root, stop
        if($this->includes->isRoot($path)){
            return;
        }

        // do we have the identifiers 
        if(false === $this->includes->has($path)){
            // (inception...) Let's get resources from our parent e.g. if no ids for comments.author, go query comments
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
            // get rid of hash grouping by type, return list of resources
            ->flatten()
            ->pipe(function($collection){
                return new ResourceCollection(...$collection);
            })
            // roll new resources into the ResourceCollection singleton
            ->each(function($resource){
                $this->resources->add($resource);
            });

        // update IncludesCollection with new child relationships for other queries
        foreach($resources->relationships()->listRelationships() as $relationshipType){
            $relatedIdentifiers = $resources->relationships()->identifiersFor($relationshipType);

            $newPath = sprintf("%s.%s", $path, $relationshipType);

            $this->includes->add($newPath, ...$relatedIdentifiers);
        }
    }
}