<?php

namespace Dogpile\Exceptions;

class ResourceQueryNotFoundException extends BaseException
{
    public static function missing(string $resourceType): ResourceQueryNotFoundException
    {
        $message = sprintf("Could not find a Resource Repository for Resource Objects of type %s", $resourceType);

        return new self($message);
    }
}