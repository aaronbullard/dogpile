<?php

namespace Dogpile;

use Dogpile\Collections\Collection;
use Dogpile\Collections\ResourceCollection;
use Dogpile\Collections\RelationshipCollection;

class QueryBuilder
{
    const ROOT = "$";

    protected $manager;

    protected $relationships;

    protected $resources;

    protected $paths;

    protected $completedPaths = [];

    public function __construct(ResourceManager $manager)
    {
        $this->manager          = $manager;
        $this->relationships    = new RelationshipCollection();
        $this->resources        = new ResourceCollection();
    }

    public function relationships(): RelationshipCollection
    {
        return $this->relationships;
    }

    public function resources(): ResourceCollection
    {
        return $this->resources;
    }

    public function includes(): Collection
    {
        return $this->paths;
    }

    public function setRelationships(RelationshipCollection $relationships): QueryBuilder
    {
        $relationships->listRelationships()->each(function($relationshipType) use ($relationships){
            $this->relationships->add($relationshipType, ...$relationships->identifiersFor($relationshipType));
        });

        return $this;
    }

    public function include(string ...$paths): QueryBuilder
    {
        // remove duplicates
        $paths = array_unique($paths);

        // Sorting allows parents to go before children e.g. author, author.comments
        sort($paths);

        $this->paths = Collection::wrap($paths);

        return $this;
    }

    public function query(): ResourceCollection
    {
        $this->includes()->each(function($path){
            $this->resolve($path);
        });

        // At this point, all resources have been gathered
        // Let's return only what was requested PLUS the required
        // intermediate resources to provide full linkage as per
        // https://jsonapi.org/format/#fetching-includes

        // get identifiers for all necessary paths
        return Collection::wrap($this->completedPaths)
            ->map(function($path){
                return $this->relationships->identifiersFor($path);
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
        if(false === $this->relationships->has($path)){
            // (inception...) Let's get resources from our parent e.g. if no ids for comments.author, go query comments
            $this->resolve(static::parent($path));
        }

        // We now have our identifiers, go get the resources
        $identifiers = $this->relationships->identifiersFor($path);

        $resources = $this->queryResources($identifiers);

        // update ResourceCollection
        $this->resources->add(...array_values($resources->all()));

        // need ALL Resources (not just ones queried) for this path
        // need to update the relationships collection with the child related identifiers
        $allResourcesInPath = $identifiers->map(function($ident){
                return $this->resources->find($ident->type(), $ident->id());
            })
            ->pipe(function($resources){ return new ResourceCollection(...$resources); });

        // update RelationshipCollection with new child relationships for other queries
        $this->indexRelationships($path, $allResourcesInPath->relationships())->each(function($identifiers, $nestedRelationshipType){
            $this->relationships->add($nestedRelationshipType, ...$identifiers);
        });

        // Update completed paths for faster operation
        $this->completedPaths[] = $path;
    }

    /**
     * Query and return all resources which we don't already have in $this->resources
     *
     * @param string $path
     * @return ResourceCollection
     */
    protected function queryResources(Collection $identifiers): ResourceCollection
    {
            // filter out identifiers for resources we already have
        return $identifiers->filter(function($identifier){
                return false === $this->resources()->exists($identifier->type(), $identifier->id());
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

    /**
     * Prepend the current relationship path to the child relationships
     * 
     * e.g. comments => post.comments
     *
     * @param string $path
     * @param ResourceCollection $resources
     * @return Collection
     */
    protected function indexRelationships(string $path, RelationshipCollection $relationships): Collection
    {
        // update IncludesCollection with new child relationships for other queries
        return $relationships->listRelationships()
            ->mapWithKeys(function($relationshipType) use ($path, $relationships){
                $nestedPath = sprintf("%s.%s", $path, $relationshipType);
                return [$nestedPath => $relationships->identifiersFor($relationshipType)]; 
            });
    }

    public function isRoot(string $relationship): bool
    {
        return $relationship === static::ROOT;
    }

    /**
     * Get the parent relationship type
     * 
     * e.g. author.comments => author
     *
     * @param string $relationship
     * @return string
     */
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