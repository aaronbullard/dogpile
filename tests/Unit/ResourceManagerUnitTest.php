<?php

namespace JsonApiRepository\Tests\Unit;

use JsonApiRepository\Tests\TestCase;
use JsonApiRepository\ResourceManager;
use JsonApiRepository\ResourceCollection;
use JsonApiRepository\ResourceIdentifier;
use JsonApiRepository\Tests\Stubs\Model;

class ResourceManagerUnitTest extends TestCase
{
    protected $includes = [
        'author', 
        'comments', 
        'comments.author',
        'comments.author.posts',
        'comments.author.comments'
    ];

    protected function setUp()
    {
        parent::setUp();

        $this->posts->find('1')->relationships()
            ->add('author', ResourceIdentifier::create('people', '1'))
            ->add('comments', ResourceIdentifier::create('comments', '1'))
            ->add('comments', ResourceIdentifier::create('comments', '2'));

        $this->comments->find('1')->relationships()
            ->add('author', ResourceIdentifier::create('people', '2'));

        $this->comments->find('2')->relationships()
            ->add('author', ResourceIdentifier::create('people', '3'));
    }

    /** @test */
    public function it_returns_included_relationships()
    {
        // Execute
        $collection = $this->manager->newQuery()
                            ->setRelationships($this->posts->find('1')->relationships())
                            ->includes('author', 'comments')
                            ->query();

        // Assert
        $this->assertInstanceOf(ResourceCollection::class, $collection);
        $this->assertCount(3, $collection);
        $this->assertTrue(
            $collection->exists('people', '1') &&
            $collection->exists('comments', '1') &&
            $collection->exists('comments', '2')
        );
    }

    /** @test */
    public function it_returns_nested_includes()
    {
        // Execute
        $collection = $this->manager->newQuery()
                            ->setRelationships($this->posts->find('1')->relationships())
                            ->includes('author', 'comments', 'comments.author')
                            ->query();

        // Assert
        $this->assertInstanceOf(ResourceCollection::class, $collection);
        $this->assertCount(5, $collection);
        $this->assertTrue(
            $collection->exists('people', '1') &&
            $collection->exists('comments', '1') &&
            $collection->exists('comments', '2') &&
            $collection->exists('people', '2') &&
            $collection->exists('people', '3')
        );
    }
}