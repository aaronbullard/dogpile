<?php

namespace JsonApiRepository\Tests\Stubs;

use JsonApiRepository\ResourceRepository;

abstract class BaseRepository implements ResourceRepository
{
    protected $models = [];

    public function __construct(Model ...$models)
    {
        foreach($models as $model){
            $this->models[$model->id()] = $model;
        }
    }

    /**
     * Returns the type of Resource Objects
     *
     * @return void
     */
    public function resourceType(): string
    {
        return static::TYPE;
    }

    public function find(string $id): Model
    {
        return $this->models[$id];
    }

    /**
     * Queries the resource based on the ids provided
     * 
     * Must return an array of objects implementing the ResourceObject interface
     *
     * @param array $ids
     * @return array
     */
    public function findHavingIds(array $ids): array
    {
        return array_map(function($id){
            return $this->find($id);
        }, $ids);
    }
}