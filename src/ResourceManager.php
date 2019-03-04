<?php

namespace Dogpile;

use Dogpile\Contracts\ResourceQuery;
use Dogpile\Exceptions\ResourceQueryNotFoundException;

class ResourceManager
{
    protected $repos = [];

    protected $relationships;

    protected $includes;

    protected $resourceObjects = [];

    public function __construct(ResourceQuery ...$repos)
    {
        foreach($repos as $repo){
            $this->register($repo);
        }
    }

    public function register(ResourceQuery $repo): ResourceManager
    {
        $this->repos[$repo->resourceType()] = $repo;

        return $this;
    }

    public function repositoryFor(string $resourceType): ResourceQuery
    {
        if (!isset($this->repos[$resourceType])) {
            throw ResourceQueryNotFoundException::missing($resourceType);
        }

        return $this->repos[$resourceType];
    }

    public function newQuery(): QueryBuilder
    {
        return new QueryBuilder($this);
    }

}