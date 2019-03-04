<?php

namespace Dogpile\Exceptions;

class ResourceRepositoryNotFoundException extends BaseException
{
    public static function missing(string $resourceType): ResourceRepositoryNotFoundException
    {
        $message = sprintf("Could not find a Resource Repository for Resource Objects of type %s", $resourceType);

        return new self($message);
    }
}