<?php

namespace JsonApiRepository\Tests\Unit;

use JsonApiRepository\Tests\TestCase;
use JsonApiRepository\ResourceIdentifier;
use JsonApiRepository\IncludesCollection;

class IncludesCollectionUnitTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->collection = new IncludesCollection();
    }

    /** @test */
    public function it_adds_distinct_relationships()
    {
        $this->collection->add('authors', ResourceIdentifier::create('people', '1'), ResourceIdentifier::create('people', '2'));
        $this->collection->add('authors', ResourceIdentifier::create('people', '1'));

        $this->assertTrue($this->collection->has('authors'));
        $this->assertCount(2, $this->collection->identifiersFor('authors'));
        $this->assertTrue($this->collection->has('authors'));
        $this->assertFalse($this->collection->has('comments'));
    }

    /** @test */
    public function it_finds_the_parent_include()
    {
        $this->assertEquals('$', IncludesCollection::parent('comments'));
        $this->assertEquals('comments', IncludesCollection::parent('comments.author'));
        $this->assertEquals('comments.author', IncludesCollection::parent('comments.author.posts'));
    }

    /** @test */
    public function it_finds_the_last_include()
    {
        $this->assertEquals('comments', IncludesCollection::last('comments'));
        $this->assertEquals('author', IncludesCollection::last('comments.author'));
        $this->assertEquals('posts', IncludesCollection::last('comments.author.posts'));
    }
}