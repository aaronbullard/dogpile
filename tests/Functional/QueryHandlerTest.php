<?php

namespace JsonApiRepository\Tests\Functional;

use JsonApiRepository\Tests\TestCase;
use JsonApiRepository\QueryHandler;
use JsonApiRepository\ResourceIdentifier as Ident;
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
        $this->resources->add($this->comments->find('101'));

        // Give a relationship to a comment to test if it gets logged with the includes
        $this->comments->find('102')->relationships()->add('author', Ident::create('people', '11'));

        $this->includes->add('comments', 
            Ident::create('comments', '101'),
            Ident::create('comments', '102'),
            Ident::create('comments', '103')
        );

        $this->handler->resolve('comments');

        $this->assertTrue($this->resources->has('comments', '101'));
        $this->assertTrue($this->resources->has('comments', '102'));
        $this->assertTrue($this->resources->has('comments', '103'));
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