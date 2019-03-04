<?php

namespace Dogpile\Tests\Unit;

use Dogpile\Tests\TestCase;
use Dogpile\Collection;
use Dogpile\ResourceIdentifier as Ident;
use Dogpile\RelationshipCollection;

class RelationshipCollectionUnitTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->collection = new RelationshipCollection();
    }

    /** @test */
    public function it_adds_distinct_relationships()
    {
        $resIdents = $this->createResourceIdentifiers(5, 'people');

        $this->collection->add('authors', $resIdents[0], $resIdents[1]);
        $this->collection->add('authors', $resIdents[2], $resIdents[3], $resIdents[4]);
        $this->collection->add('authors', $resIdents[4]);

        $this->assertInstanceOf(Collection::class, $this->collection->identifiersFor('authors'));
        $this->assertCount(5, $this->collection->identifiersFor('authors'));
    }

    /** @test */
    public function it_adds_distinct_relationships_with_different_types()
    {
        $people = $this->createResourceIdentifiers(5, 'people');
        $users = $this->createResourceIdentifiers(1, 'users');

        $this->collection->add('authors', ...$people);
        $this->collection->add('authors', $users[0]);

        $this->assertCount(6, $this->collection->identifiersFor('authors'));
    }

    /** @test */
    public function it_finds_the_parent_include()
    {
        $this->assertEquals('$', RelationshipCollection::parent('comments'));
        $this->assertEquals('comments', RelationshipCollection::parent('comments.author'));
        $this->assertEquals('comments.author', RelationshipCollection::parent('comments.author.posts'));
    }

    /** @test */
    public function it_merges_nested_arrays()
    {
        // Setup
        $primary = new RelationshipCollection();
        $primary->add('comments', Ident::create('comments', '1'), Ident::create('comments', '2'));

        $secondary = new RelationshipCollection();
        $secondary->add('comments', Ident::create('comments', '2'), Ident::create('comments', '3'));

        // Execute
        $primary->mergeRelationships($secondary);
dd($primary);
        // Assert
        $this->assertTrue(
            $primary->has('comments', '1') &&
            $primary->has('comments', '2') &&
            $primary->has('comments', '3')
        );
    }
}