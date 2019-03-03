<?php

namespace JsonApiRepository;

class IncludesCollection extends RelationshipCollection
{
    const ROOT = "$";

    public function isRoot(string $relationship): bool
    {
        return $relationship === static::ROOT;
    }

    public static function parent(string $relationship): string 
    {
        $arr = explode('.', $relationship);

        if(count($arr) == 1){
            return static::ROOT;
        }

        unset($arr[count($arr) - 1]);

        return implode('.', $arr);
    }

    public static function last(string $relationship): string 
    {
        $arr = explode('.', $relationship);

        return end($arr);
    }
}