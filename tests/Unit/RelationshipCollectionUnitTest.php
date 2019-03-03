<?php

namespace Dogpile\Tests\Unit;

use Dogpile\Tests\TestCase;
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
}