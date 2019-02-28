<?php

namespace JsonApiRepository;

use Tightenco\Collect\Support\Collection;

class IncludesCollection
{
    protected $items = [];

    public function add(string $path, ResourceIdentifier ...$identities): IncludesCollection
    {
        // initialize includes path
        if(!$this->has($path)){
            $this->items[$path] = [];
        }

        foreach($identities as $i){
            $key = $i->type() . $i->id();
            $this->items[$path][$key] = $i;
        }
        
        return $this;
    }

    public function has(string $path): bool
    {
        return isset($this->items[$path]);
    }

    public function identifiersFor(string $path): array
    {
        return $this->has($path) ? $this->items[$path] : [];
    }

    public function toArray(): array
    {
        return array_map(function($identifiers) {
            return array_values($identifiers);
        }, $this->items);
    }

    public static function parent(string $path): string 
    {
        $arr = explode('.', $path);

        if(count($arr) == 1){
            return '$';
        }

        unset($arr[count($arr) - 1]);

        return implode('.', $arr);
    }

    public static function last(string $path): string 
    {
        $arr = explode('.', $path);

        return end($arr);
    }
}