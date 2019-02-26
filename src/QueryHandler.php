<?php

namespace JsonApiRepository;

class QueryHandler
{
    protected $manager;

    protected $collection;

    protected $ids = [];

    public function __construct(ResourceManager $manager, ResourceCollection $collection)
    {
        $this->manager = $manager;
        $this->collection = $collection;
    }

    public function next($next): QueryHandler
    {
        $this->next = $next;

        return $this;
    }

    public function find(ResourceIdentifier ...$identifiers): ResourceCollection
    {
        // filter previously queried objects
        $filtered = array_filter($identifiers, function($item) {
            return $this->collection->exists($item->type(), $item->id()) === false;
        });

        // incase for some reason not all identifiers are of the same type, we'll separate them first
        foreach($filtered as $identifier){
            // Check to initialize array if not already
            if(!isset($this->ids[$identifier->type()])){
                $this->ids[$identifier->type()] = [];
            }

            $this->ids[$identifier->type()][] = $identifier->id();
        }

        // query
        $relationships = new RelationshipCollection();

        foreach($this->ids as $type => $ids){
            $collection = $this->manager->repositoryFor($type)->findHavingIds($this->ids[$type]);
            
            $relationships->merge($collection->relationships());
            
            $this->collection->merge($collection);

            unset($collection);
        }

        if(is_array($this->next)){
            foreach($this->next as $include => $next){
                $handler = new static($this->manager, $this->collection);

                $handler->next($next);

                $identifiers = $relationships->resourceIdentifiersFor($include);

                $handler->find(...$identifiers);

                unset($handler);
            }
        }

        unset($relationships);

        return $this->collection;
    }
}