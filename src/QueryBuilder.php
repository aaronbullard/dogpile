<?php

namespace Dogpile;

use Dogpile\Collections\Collection;
use Dogpile\Collections\ResourceCollection;
use Dogpile\Collections\RelationshipCollection;

class QueryBuilder
{
    const ROOT = "$";

    protected $manager;

    protected $includes;

    protected $resources;

    protected $paths;

    protected $completedPaths = [];

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
        return Collection::wrap($this->paths)
            ->map(function($path){
                return $this->includes->identifiersFor($path);
            })
            ->flatten()
            // Map resource identifier to resource object
            ->map(function($identifier){
                return $this->resources->find($identifier->type(), $identifier->id());
            })
            // return back a collection of resources that were requested
            ->pipe(function($resources){
                return new ResourceCollection(...$resources);
            });
    }

    protected function resolve(string $path): void
    {
        // If recursion brought us to the root, stop
        if(static::isRoot($path)){
            return;
        }

        // Has this path already been queried
        if(in_array($path, $this->completedPaths)){
            return;
        }

        // do we have the identifiers 
        if(false === $this->includes->has($path)){
            // (inception...) Let's get resources from our parent e.g. if no ids for comments.author, go query comments
            $this->resolve(static::parent($path));
        }

        // We now have our identifiers, go get the resources
        $resources = $this->queryResources($path);

        // update ResourceCollection
        $this->resources->add(...array_values($resources->all()));

        // update IncludesCollection with new child relationships for other queries
        $this->indexRelationships($path, $resources)->each(function($identifiers, $relationshipType){
            $this->includes->add($relationshipType, ...$identifiers);
        });

        // Update completed paths for faster operaion
        $this->completedPaths[] = $path;
    }

    protected function queryResources(string $path): ResourceCollection
    {
        return $this->includes->identifiersFor($path)
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
            });
    }

    protected function indexRelationships(string $path, ResourceCollection $resources): Collection
    {
        // update IncludesCollection with new child relationships for other queries
        return $resources->relationships()->listRelationships()
            ->mapWithKeys(function($relationshipType) use ($path, $resources){
                $newPath = sprintf("%s.%s", $path, $relationshipType);
                return [$newPath => $resources->relationships()->identifiersFor($relationshipType)]; 
            });
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