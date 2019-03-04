<?php

namespace Dogpile\Exceptions;

class NotFoundException extends BaseException
{
    public static function resource(string $type, string $id): NotFoundException
    {
        $message = sprintf("Could not find Resource of type '%s' and with id '%s'", $type, $id);

        return new self($message);
    }

    public static function resourceRepository(string $resourceType): NotFoundException
    {
        $message = sprintf("Could not find a Resource Repository for Resource Objects of type %s", $resourceType);

        return new self($message);
    }
}