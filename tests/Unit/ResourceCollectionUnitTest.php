<?php

namespace Dogpile\Tests\Unit;

use Dogpile\Tests\TestCase;
use Dogpile\ResourceCollection;

class ResourceCollectionUnitTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->collection = new ResourceCollection();
    }

    /** @test */
    public function it_adds_distinct_resources()
    {
        $resIdents = $this->createResources(5, 'people');

        $this->collection->add($resIdents[0], $resIdents[1]);
        $this->collection->add($resIdents[2], $resIdents[3], $resIdents[4]);
        $this->collection->add($resIdents[4]);
        $this->collection->add(...$this->createResources(5, 'comments'));

        $this->assertCount(10, $this->collection);
        $this->assertEquals(10, $this->collection->count());
        $this->assertCount(10, $this->collection->toArray());
    }
}