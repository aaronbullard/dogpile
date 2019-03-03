<?php

namespace Dogpile;

interface Resource
{
    /**
     * Resource type
     *
     * @return string
     */
    public function type(): string;

    /**
     * Resource id
     *
     * @return string
     */
    public function id(): string;

    /**
     * Returns an array of ResourceIdentifiers
     *
     * @return RelationshipCollection
     */
    public function relationships(): RelationshipCollection;
}