<?php

namespace JsonApiRepository;

class QueryBuilder
{
    protected $manager;

    protected $includes;

    protected $resources;

    protected $paths;

    public function __construct(ResourceManager $manager)
    {
        $this->manager      = $manager;
        $this->includes     = new IncludesCollection();
        $this->resources    = new ResourceCollection();
    }

    public function includesCollection(): IncludesCollection
    {
        return $this->includess;
    }

    public function resourceCollection(): ResourceCollection
    {
        return $this->resources;
    }

    public function setRelationships(RelationshipCollection $relationships): QueryBuilder
    {
        $this->relationships = $relationships;

        foreach($relationships->listRelationships() as $path){
            $this->includes->add($path, ...$relationships->resourceIdentifiersFor($path));
        }

        return $this;
    }

    public function includes(string ...$paths): QueryBuilder
    {
        // Sorting allows parents to go before children e.g. author, author.comments
        sort($paths);

        $this->paths = $paths;

        return $this;
    }

    public function query(): ResourceCollection
    {
        foreach($this->paths as $path){
            QueryHandler::create($this->manager, $this->includes, $this->resources)->resolve($path);
        }

        // get identifiers for just the includes that were requested
        $identifiers = new Collection();
        foreach($this->paths as $path){
            $identifiers = $this->includes->identifiersFor($path)->merge($identifiers);
        }

        // return back a collectoin of resources that were requested
        $resources = new ResourceCollection();
        foreach($identifiers as $identifier){
            $resources->add(
                $this->resources->find($identifier->type(), $identifier->id())
            );
        }

        return $resources;
    }
}