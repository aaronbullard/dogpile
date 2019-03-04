<?php

namespace Dogpile\Tests\Stubs;

use Dogpile\Contracts\ResourceQuery;
use Dogpile\Collections\ResourceCollection;
use Dogpile\Exceptions\NotFoundException;

abstract class BaseRepository implements ResourceQuery
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
        if(!isset($this->models[$id])){
            throw NotFoundException::resource($this->resourceType(), $id);
        }

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
    public function findHavingIds(array $ids): ResourceCollection
    {
        $models = array_map(function($id){
            return $this->find($id);
        }, $ids);

        return new ResourceCollection(...$models);
    }
}