<?php

namespace Dogpile\Tests\Unit;

use Dogpile\Tests\TestCase;
use Dogpile\ResourceManager;
use Dogpile\ResourceCollection;
use Dogpile\ResourceIdentifier;
use Dogpile\Tests\Stubs\Model;
use Dogpile\ResourceRepositoryNotFoundException;

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

        $this->comments->find('101')->relationships()
            ->add('author', ResourceIdentifier::create('people', '12'));

        $this->comments->find('102')->relationships()
            ->add('author', ResourceIdentifier::create('people', '13'));

        $this->people->find('12')->relationships()
            ->add('posts', ResourceIdentifier::create('posts', '2'));

        $this->people->find('12')->relationships()
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
            'title' => "it handles an mixed types in a relationship",
            'includes' => ['comments'],
            'count' => 3,
            'collection' => [
                ['comments', '101'],
                ['comments', '102'],
                ['posts', '2']
            ]
        ];

        yield [
            'title' => "it handles an undefined relationship",
            'includes' => ['comments', 'comments.zebras.posts'],
            'count' => 3,
            'collection' => [
                ['comments', '101'],
                ['comments', '102']
            ]
        ];

        yield [
            'title' => "it handles an undefined type",
            'includes' => ['author', 'comments'],
            'count' => 4,
            'collection' => [
                ['people', '11'],
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
        $this->expectException(ResourceRepositoryNotFoundException::class);
        $collection = $this->manager->newQuery()
                            ->setRelationships($this->posts->find('1')->relationships())
                            ->include('author', 'comments', 'link')
                            ->query();
    }
}