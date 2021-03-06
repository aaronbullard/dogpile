<?php

namespace Dogpile\Tests\Functional;

use Dogpile\Tests\TestCase;
use Dogpile\ResourceManager;
use Dogpile\Tests\Stubs\Model;
use Dogpile\ResourceIdentifier;
use Dogpile\Collections\ResourceCollection;
use Dogpile\Exceptions\ResourceQueryNotFoundException;
use Dogpile\Exceptions\NotFoundException;

class ResourceManagerTest extends TestCase
{
    // For reference
    protected $possibleIncludes = [
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
            ->addRelationships('author', ResourceIdentifier::create('people', '11'))
            ->addRelationships('comments', ResourceIdentifier::create('comments', '101'))
            ->addRelationships('comments', ResourceIdentifier::create('comments', '102'))
            ->addRelationships('comments', ResourceIdentifier::create('posts', '2')) // different type
            ->addRelationships('link', ResourceIdentifier::create('href', '42')); // undefined type

        // Give author of post the inverse relationship
        $this->people->find('11')->relationships()
            ->addRelationships('posts', ResourceIdentifier::create('posts', '1'));

        // Give comments authors
        $this->comments->find('101')->relationships()
            ->addRelationships('author', ResourceIdentifier::create('people', '12'));

        $this->comments->find('102')->relationships()
            ->addRelationships('author', ResourceIdentifier::create('people', '13'));

        // Give person 12 two posts they authored
        $this->people->find('12')->relationships()
            ->addRelationships('posts', ResourceIdentifier::create('posts', '2'))
            ->addRelationships('posts', ResourceIdentifier::create('posts', '3'));
    }

    /** @test */
    public function run_test_suite()
    {
        foreach($this->dataProvider() as $test){
            $this->execute_test($test);
        }
    }

    protected function execute_test(array $test)
    {
        // Execute
        $query = $this->manager->newQuery();
           
        $collection = $query->setRelationships($this->posts->find('1')->relationships())
                ->include(...$test['includes'])
                ->query();

        // Assert
        $this->assertInstanceOf(ResourceCollection::class, $collection);

        $this->assertCount($test['count'], $collection, $test['title']);

        foreach($test['collection'] as $item){
            $this->assertTrue($collection->exists(...$item), $test['title']);
        }
    }

    protected function dataProvider()
    {
        yield [
            'title' => "it returns included relationships",
            'includes' => ['author', 'comments'],
            'count' => 4,
            'collection' => [
                ['people', '11'],
                ['comments', '101'],
                ['comments', '102']
            ]
        ];

        yield [
            'title' => "it returns included relationships once",
            'includes' => ['author', 'comments', 'comments', 'comments'],
            'count' => 4,
            'collection' => [
                ['people', '11'],
                ['comments', '101'],
                ['comments', '102']
            ]
        ];

        yield [
            'title' => 'it handles no includes',
            'includes' => [],
            'count' => 0,
            'collection' => []
        ];

        yield [
            'title' => "it returns nested includes",
            'includes' => ['author', 'comments', 'comments.author', 'comments.author.posts'],
            'count' => 7,
            'collection' => [
                ['people', '11'],
                ['comments', '101'],
                ['comments', '102'],
                ['people', '12'],
                ['people', '13'],
                ['posts', '2'],
                ['posts', '3']
            ]
        ];

        yield [
            'title' => "it returns nested includes AND their parents",
            'includes' => ['comments.author.posts'],
            'count' => 6,
            'collection' => [
                ['comments', '101'],
                ['comments', '102'],
                ['people', '12'],
                ['people', '13'],
                ['posts', '2'],
                ['posts', '3']
            ]
        ];

        yield [
            'title' => "it handles mixed types in a relationship",
            'includes' => ['comments'],
            'count' => 3,
            'collection' => [
                ['comments', '101'],
                ['comments', '102'],
                ['posts', '2']
            ]
        ];

        yield [
            'title' => 'it handles an undefined relationship',
            'includes' => ['posts'],
            'count' => 0,
            'collection' => []
        ];

        yield [
            'title' => "it ignores an undefined NESTED relationship",
            'includes' => ['comments', 'comments.zebras.author'],
            'count' => 3,
            'collection' => [
                ['comments', '101'],
                ['comments', '102'],
                ['posts', '2']
            ]
        ];

        yield [
            'title' => "it handles a recursive relationship",
            'includes' => ['author.posts.author.posts.author.posts.author'],
            'count' => 2,
            'collection' => [
                ['people', '11'],
                ['posts', '1']
            ]
        ];
    }

    /** @test */
    public function it_lists_registered_resources()
    {
        $resourceTypes = $this->manager->listResourceTypes();

        foreach(['posts', 'comments', 'people'] as $resourceType){
            $this->assertTrue(in_array($resourceType, $resourceTypes));
        }
    }

    /** @test */
    public function it_checks_for_a_registered_resource_type()
    {
        $this->assertTrue($this->manager->hasResourceType('posts'));
        $this->assertFalse($this->manager->hasResourceType('monkeys'));
    }

    /** @test */
    public function it_throws_exception_for_an_undefined_type_when_included()
    {
        // Assert
        $this->expectException(ResourceQueryNotFoundException::class);
        $collection = $this->manager->newQuery()
                            ->setRelationships($this->posts->find('1')->relationships())
                            ->include('author', 'comments', 'link')
                            ->query();
    }

    /** @test */
    public function it_throws_exception_when_a_resource_is_not_found()
    {
        /**
         * This is for documented intended behavior.  It's up to the developer
         * to throw an exception from the ResourceQuery object if a resource object
         * is not found.
         */

        // Setup
        // Add a resource identifier that doesn't exist
        $this->posts->find('1')->relationships()
            ->addRelationships('comments', ResourceIdentifier::create('comments', '1111'));

        // Assert
        $this->expectException(NotFoundException::class);
        $collection = $this->manager->newQuery()
                            ->setRelationships($this->posts->find('1')->relationships())
                            ->include('comments')
                            ->query();
    }

    /** @test */
    public function it_maps_child_identifiers_of_known_and_unqueried_resources()
    {
        // Setup
        $query = $this->manager->newQuery();

        // preload resource, comment 101 has an author => people-12
        $query->resources()->addResources(
            $this->comments->find('101')
        );

        // Execute
        $collection = $query->setRelationships($this->posts->find('1')->relationships())
                            ->include('comments')
                            ->query();

        // Assert
        // Comment 101 has author people-12.  Comment 101 was known and wasn't queried.  This 
        // test insures even known resources have their identifiers mapped to the correct path
        $this->assertCount(
            1,
            $query->relationships()->identifiersFor('comments.author')->filter(function($ident){
                return $ident->type() === 'people' && $ident->id() === '12';
            })
        );
    }
}