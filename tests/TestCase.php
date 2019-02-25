<?php
namespace JsonApiRepository\Tests;

use Mockery;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use JsonApiRepository\ResourceManager;
use JsonApiRepository\ResourceIdentifier;
use JsonApiRepository\Tests\Stubs\Model;
use JsonApiRepository\Tests\Stubs\PostsRepository;
use JsonApiRepository\Tests\Stubs\PeopleRepository;
use JsonApiRepository\Tests\Stubs\CommentsRepository;

class TestCase extends PHPUnitTestCase
{
    protected $posts;

    protected $people;

    protected $comments;

    protected function setUp()
    {
        parent::setUp();
        $this->posts = new PostsRepository(
            Model::create('posts', '1'),
            Model::create('posts', '2'),
            Model::create('posts', '3')
        );

        $this->people = new PeopleRepository(
            Model::create('people', '1'),
            Model::create('people', '2'),
            Model::create('people', '3')
        );

        $this->comments = new CommentsRepository(
            Model::create('comments', '1'),
            Model::create('comments', '2'),
            Model::create('comments', '3')
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
}