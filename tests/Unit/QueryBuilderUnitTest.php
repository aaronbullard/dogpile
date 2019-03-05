<?php

namespace Dogpile\Tests\Unit;

use Dogpile\Tests\TestCase;
use Dogpile\QueryBuilder;

class QueryBuilderUnitTest extends TestCase
{
    /** @test */
    public function it_finds_the_parent_include()
    {
        $this->assertEquals('$', QueryBuilder::parent('comments'));
        $this->assertEquals('comments', QueryBuilder::parent('comments.author'));
        $this->assertEquals('comments.author', QueryBuilder::parent('comments.author.posts'));
    }
}