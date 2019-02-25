<?php

namespace JsonApiRepository;

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
}