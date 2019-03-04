<?php
namespace Dogpile\Tests;

use Mockery;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Dogpile\ResourceManager;
use Dogpile\ResourceIdentifier;
use Dogpile\Tests\Stubs\Model;
use Dogpile\Tests\Stubs\PostsRepository;
use Dogpile\Tests\Stubs\PeopleRepository;
use Dogpile\Tests\Stubs\CommentsRepository;

class TestCase extends PHPUnitTestCase
{
    protected $posts;

    protected $people;

    protected $comments;

    protected $manager;

    protected function setUp()
    {
        parent::setUp();
        $this->posts = new PostsRepository(
            Model::create('posts', '1'),
            Model::create('posts', '2'),
            Model::create('posts', '3')
        );

        $this->people = new PeopleRepository(
            Model::create('people', '11'),
            Model::create('people', '12'),
            Model::create('people', '13')
        );

        $this->comments = new CommentsRepository(
            Model::create('comments', '101'),
            Model::create('comments', '102'),
            Model::create('comments', '103')
        );

        $this->manager = new ResourceManager(
            $this->posts,
            $this->people,
            $this->comments
        );
    }

    protected function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    protected function createResourceIdentifiers($num, $type): array
    {
        $results = [];

        for($i=0; $i < $num; $i++){
            $results[] = ResourceIdentifier::create($type, $i);
        }

        return $results;
    }

    protected function createResources($num, $type): array
    {
        $results = [];

        for($i=0; $i < $num; $i++){
            $results[] = Model::create($type, $i);
        }

        return $results;
    }
}