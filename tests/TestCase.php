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
use JsonApiRepository\Tests\Stubs\UsersRepository;

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
            Model::create('people', '11'),
            Model::create('people', '12'),
            Model::create('people', '13')
        );

        $this->comments = new CommentsRepository(
            Model::create('comments', '101'),
            Model::create('comments', '102'),
            Model::create('comments', '103')
        );

        $this->users = new UsersRepository(
            Model::create('users', '21'),
            Model::create('users', '22'),
            Model::create('users', '23')
        );

        $this->manager = new ResourceManager(
            $this->posts,
            $this->people,
            $this->comments,
            $this->users
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