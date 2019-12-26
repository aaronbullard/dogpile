<?php

namespace Dogpile\Tests\Unit;

use Dogpile\Tests\TestCase;
use Dogpile\ResourceIdentifier;
use Dogpile\Collections\ResourceCollection;

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
        $models = $this->createResources(5, 'people');

        $this->collection->addResources($models[0], $models[1]);
        $this->collection->addResources($models[2], $models[3], $models[4]);
        $this->collection->addResources($models[4]);
        $this->collection->addResources(...$this->createResources(5, 'comments'));

        $this->assertCount(10, $this->collection);
        $this->assertEquals(10, $this->collection->count());
        $this->assertCount(10, $this->collection->toArray());
    }

    /** @test */
    public function it_returns_all_relationships()
    {
        $models = $this->createResources(5, 'people');

        $this->collection->addResources(...$models)
            ->each(function($person){
                $person->relationships()->addRelationships('child', ResourceIdentifier::create('people', $person->id()));
            });

        $this->assertCount(5, $this->collection->relationships()->identifiersFor('child'));
        $this->assertEquals('child', $this->collection->relationships()->listRelationships()->first());
    }
}