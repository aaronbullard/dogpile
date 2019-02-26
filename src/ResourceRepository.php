<?php

namespace JsonApiRepository;

interface ResourceRepository
{
    /**
     * Returns the type of Resource Objects
     *
     * @return void
     */
    public function resourceType(): string;

    /**
     * Queries the resource based on the ids provided
     * 
     * Must return an array of objects implementing the ResourceObject interface
     *
     * @param array $ids
     * @return array
     */
    public function findHavingIds(array $ids): ResourceCollection;
}