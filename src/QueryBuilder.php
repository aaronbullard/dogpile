<?php

namespace JsonApiRepository;

class QueryBuilder
{
    protected $manager;

    protected $relationships;

    protected $includes;

    protected $collection;

    public function __construct(ResourceManager $manager)
    {
        $this->manager      = $manager;
        $this->collection   = new ResourceCollection();
    }

    public function setRelationships(RelationshipCollection $relationships): QueryBuilder
    {
        $this->relationships = $relationships;

        return $this;
    }

    public function includes(string ...$includes): QueryBuilder
    {
        $this->includes = static::parseIncludes(...$includes);

        return $this;
    }

    public function query(): ResourceCollection
    {
        foreach($this->includes as $include => $next){
            $handler = new QueryHandler($this->manager, $this->collection);
            
            $handler->next($next);

            $identifiers = $this->relationships->resourceIdentifiersFor($include);

            $handler->find(...$identifiers);
        }

        return $this->collection;
    }

    public static function parseIncludes(string ...$includes): array
    {
        sort($includes);

        $array = [];

        foreach($includes as $include){
            $array[$include] = null;
        }

        $newArray = array();

        foreach($array as $key => $value) {
            $dots = explode(".", $key);

            if(count($dots) > 1) {
                $last = &$newArray[ $dots[0] ];
                foreach($dots as $k => $dot) {
                    if($k == 0) continue;
                    $last = &$last[$dot];
                }
                $last = $value;
            } else {
                $newArray[$key] = $value;
            }
        }

        return $newArray;
    }

}