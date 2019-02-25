<?php

namespace JsonApiRepository\Tests\Unit;

use JsonApiRepository\Tests\TestCase;
use JsonApiRepository\ResourceManager;
use JsonApiRepository\QueryBuilder;

class QueryBuilderUnitTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->manager = new ResourceManager();
        $this->builder = new QueryBuilder($this->manager);

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

    protected function includesDataProvider()
    {
        yield [
            'given' => ['author', 'comments', 'comments.author', 'comments.author.posts', 'comments.author.comments'],
            'expect' => [
                'author' => null,
                'comments' => [
                    'author' => [
                        'posts' => null,
                        'comments' => null
                    ]
                ]
            ]
        ];

        yield [
            'given' => ['comments.author.posts', 'comments.author.comments', 'comments.author', 'comments', 'author'],
            'expect' => [
                'author' => null,
                'comments' => [
                    'author' => [
                        'posts' => null,
                        'comments' => null
                    ]
                ]
            ]
        ];
    }

    /** @test */
    public function it_parses_includes()
    {
        foreach($this->includesDataProvider() as $test){
            $includes = QueryBuilder::parseIncludes(...$test['given']);
            $this->assertEquals($test['expect'], $includes);
        }
    }
}