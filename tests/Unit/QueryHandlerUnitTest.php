<?php

namespace JsonApiRepository\Tests\Unit;

use JsonApiRepository\Tests\TestCase;
use JsonApiRepository\ResourceManager;
use JsonApiRepository\ResourceCollection;

class QueryHandlerUnitTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->manager = new ResourceManager();
        $this->resCollection = new ResourceCollection();
        $this->handler = new QueryHandler($this->manager, $this->resCollection);

        $includes = [
            'author', 
            'comments', 
            'comments.author',
            'comments.author.posts'
        ];

        $paths = [
            ['author'],
            ['comments'],
            ['comments', 'author'],
            ['comments', 'author', 'posts']
        ];
    }
}