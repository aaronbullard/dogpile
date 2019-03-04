<?php

namespace Dogpile;

use Dogpile\Contracts\ResourceRepository;
use Dogpile\Exceptions\ResourceRepositoryNotFoundException;

class ResourceManager
{
    protected $repos = [];

    protected $relationships;

    protected $includes;

    protected $resourceObjects = [];

    public function __construct(ResourceRepository ...$repos)
    {
        foreach($repos as $repo){
            $this->register($repo);
        }
    }

    public function register(ResourceRepository $repo): ResourceManager
    {
        $this->repos[$repo->resourceType()] = $repo;

        return $this;
    }

    public function repositoryFor(string $resourceType): ResourceRepository
    {
        if (!isset($this->repos[$resourceType])) {
            throw ResourceRepositoryNotFoundException::missing($resourceType);
        }

        return $this->repos[$resourceType];
    }

    public function newQuery(): QueryBuilder
    {
        return new QueryBuilder($this);
    }

}