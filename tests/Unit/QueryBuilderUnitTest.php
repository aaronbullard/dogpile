<?php

namespace Dogpile\Tests\Unit;

use Mockery;
use Dogpile\Tests\TestCase;
use Dogpile\QueryBuilder;
use Dogpile\ResourceManager;

class QueryBuilderUnitTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->manager = Mockery::mock(ResourceManager::class);
        $this->queryBuilder = new QueryBuilder($this->manager);
    }

    /** @test */
    public function it_finds_the_parent_include()
    {
        $this->assertEquals('$', QueryBuilder::parent('comments'));
        $this->assertEquals('comments', QueryBuilder::parent('comments.author'));
        $this->assertEquals('comments.author', QueryBuilder::parent('comments.author.posts'));
    }

    /** @test */
    public function includes_method_is_fluent()
    {
        $this->queryBuilder->include('a', 'b')->include('c');

        $this->assertEquals(['a', 'b', 'c'], $this->queryBuilder->includes()->toArray());
    }

    /** @test */
    public function includes_method_removes_duplicates()
    {
        $this->queryBuilder->include('a', 'b', 'b');

        $this->assertEquals(['a', 'b'], $this->queryBuilder->includes()->toArray());
    }

    /** @test */
    public function includes_are_sorted_alphabetically()
    {
        $this->queryBuilder->include('c', 'b', 'a');

        foreach(['a', 'b', 'c'] as $i => $value){
            $this->assertEquals(
                $value,
                $this->queryBuilder->includes()[$i]
            );
        }
    }
}