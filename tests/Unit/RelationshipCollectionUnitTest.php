<?php

namespace JsonApiRepository\Tests\Unit;

use JsonApiRepository\Tests\TestCase;
use JsonApiRepository\ResourceIdentifier;
use JsonApiRepository\RelationshipCollection;

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

        $this->assertCount(5, $this->collection->resourceIdentifiersFor('authors'));
        $this->assertEquals([0,1,2,3,4], $this->collection->getIdsFor('authors'));
    }
}