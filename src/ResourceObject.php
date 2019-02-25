<?php

namespace JsonApiRepository;

interface ResourceObject extends Resource
{
    /**
     * Returns an array of ResourceIdentifiers
     *
     * @return RelationshipCollection
     */
    public function relationships(): RelationshipCollection;
}