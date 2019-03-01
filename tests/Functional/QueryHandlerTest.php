<?php

namespace JsonApiRepository\Tests\Functional;

use Mockery;
use JsonApiRepository\Tests\TestCase;
use JsonApiRepository\Tests\Stubs\Model;
use JsonApiRepository\Collection;
use JsonApiRepository\QueryHandler;
use JsonApiRepository\ResourceIdentifier as Ident;
use JsonApiRepository\ResourceManager;
use JsonApiRepository\ResourceCollection;
use JsonApiRepository\IncludesCollection;

class QueryHandlerTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->includes = new IncludesCollection();
        $this->resources = new ResourceCollection();
        $this->handler = new QueryHandler($this->manager, $this->includes, $this->resources);
    }

    /** @test */
    public function it_queries_relationships()
    {
        $this->resources->add($this->comments->find('1'));

        // Give a relationship to a comment to test if it gets logged with the includes
        $this->comments->find('2')->relationships()->add('author', Ident::create('people', '1'));

        $this->includes->add('comments', 
            Ident::create('comments', '1'),
            Ident::create('comments', '2'),
            Ident::create('comments', '3')
        );

        $this->handler->resolve('comments');

        $this->assertTrue($this->resources->has('comments', '1'));
        $this->assertTrue($this->resources->has('comments', '2'));
        $this->assertTrue($this->resources->has('comments', '3'));
        $this->assertTrue($this->includes->has('comments'));
        $this->assertTrue($this->includes->has('comments.author'));
    }

    public function it_resolves_recursively_for_nested_includes()
    {

    }

    public function includes_has_identifier_for_known_resources()
    {

    }

}