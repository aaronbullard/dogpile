<?php

namespace JsonApiRepository;

use Countable;

class ResourceCollection implements Countable
{
    protected $collection = [];

    public function __construct(Resource ...$resources)
    {
        $this->add(...$resources);
    }

    public function add(Resource ...$resources): ResourceCollection
    {
        foreach($resources as $r){
            if(!isset($this->collection[$r->type()])){
                $this->collection[$r->type()] = [];
            }

            $this->collection[$r->type()][$r->id()] = $r;
        }
        
        return $this;
    }

    public function listTypes(): array
    {
        return array_keys($this->collection);
    }

    public function count(): int
    {
        return array_reduce($this->collection, function($carry, $i){
            return count($i) + $carry;
        }, 0);
    }

    public function exists(string $type, string $id): bool
    {
        if(!isset($this->collection[$type])){
            return false;
        }

        if(!isset($this->collection[$type][$id])){
            return false;
        }

        return true;
    }

    public function find(string $type, string $id)
    {
        if($this->exists($type, $id) === false){
            throw NotFoundException::resource($type, $id);
        }

        return $this->collection[$type][$id];
    }

    public function toArray(): array
    {
        return array_reduce($this->collection, function($carry, $i){
            return array_merge($carry, $i);
        }, []);
    }
}
