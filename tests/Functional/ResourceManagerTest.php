<?php

namespace Dogpile\Tests\Unit;

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
            ->add('author', ResourceIdentifier::create('people', '11'))
            ->add('comments', ResourceIdentifier::create('comments', '101'))
            ->add('comments', ResourceIdentifier::create('comments', '102'))
            ->add('comments', ResourceIdentifier::create('posts', '2')) // different type
            ->add('link', ResourceIdentifier::create('href', '42')); // undefined type

        // Give comments authors
        $this->comments->find('101')->relationships()
            ->add('author', ResourceIdentifier::create('people', '12'));

        $this->comments->find('102')->relationships()
            ->add('author', ResourceIdentifier::create('people', '13'));

        // Give person 12 two posts they authored
        $this->people->find('12')->relationships()
            ->add('posts', ResourceIdentifier::create('posts', '2'))
            ->add('posts', ResourceIdentifier::create('posts', '3'));
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
        $collection = $this->manager->newQuery()
                ->setRelationships($this->posts->find('1')->relationships())
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
            'title' => "it returns nested includes by themselves",
            'includes' => ['comments.author.posts'],
            'count' => 2,
            'collection' => [
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
            'title' => "it handles an undefined NESTED relationship",
            'includes' => ['comments', 'comments.zebras.posts'],
            'count' => 3,
            'collection' => [
                ['comments', '101'],
                ['comments', '102'],
                ['posts', '2']
            ]
        ];
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
            ->add('comments', ResourceIdentifier::create('comments', '1111'));

        // Assert
        $this->expectException(NotFoundException::class);
        $collection = $this->manager->newQuery()
                            ->setRelationships($this->posts->find('1')->relationships())
                            ->include('comments')
                            ->query();
    }
}