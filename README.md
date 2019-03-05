# Dogpile

[![Maintainability](https://api.codeclimate.com/v1/badges/fd29ace6ed68f526906a/maintainability)](https://codeclimate.com/github/aaronbullard/dogpile/maintainability)

![](./img/dogpile.jpg)

JSON:API helper library to cleanly import included relationships

## Installation

### Library

```bash
git clone git@github.com:aaronbullard/dogpile.git
```

### Composer

[Install PHP Composer](https://getcomposer.org/doc/00-intro.md)

```bash
composer require aaronbullard/dogpile
```

### Testing

```bash
composer test
```

## Usage

First, visit https://jsonapi.org for documentation on JSON:API standard.  This library attempts to simplify and modularize included related resource objects in api calls.

    Ex. GET /posts/1?include=author,comments,comments.author

This library will quickly query and include the related 'author' and 'comments' assosciated with the resource.  To do so, requires a few interface implementations.

```php

// Example contoller method for GET /posts/1?include=author,comments,comments.author
public function show(Request $httpRequest): JsonResponse
{
    $postId = $httpRequest->get('postId');
    $includes = explode(',', $httpRequest->get('include')); //['author', 'comments', 'comments.author'];

    // In this example, $post implements the Dogpile\Contracts\Resource interface;
    $post = $this->myPostRepo->find($postId);

    // Example of using Dogpile\ResourceManager::class
    $includedResources = $this->resourceManager->newQuery()
        ->setRelationships($post->relationships())
        ->include(...$includes)
        ->query();

    return new JsonResponse([
        'data' => $post->toJsonapi(), // or however you want to transform your model
        'included' => array_map(function($resource){
            return $resource->toJsonapi();
        }, $includedResources);
    ], 200);
}
```
## How it works
The Dogpile\ResourceManager class contains several ResourceQuery objects for each resource type.  When an array of included relationships are provided, the Dogpile\QueryBuilder class will use the ResourceIdentifiers from the RelationshipCollection and query the resources via the RepositoryQuery::findHavingIds() method.  Each resource will only be queried once.

## Setup

### First

Dogpile requires a Dogpile\Contracts\ResourceQuery interface implementation for each resource.  As in the above example, you would want an implementation for 'people' (to query authors); and one for 'comments'.

```php
<?php

namespace Dogpile\Contracts;

use Dogpile\Collections\ResourceCollection;

interface ResourceQuery
{
    /**
     * Returns the type of Resource Objects
     *
     * @return void
     */
    public function resourceType(): string;

    /**
     * Queries the resource based on the ids provided
     * 
     * Must return an array of objects implementing the ResourceObject interface
     *
     * @param array $ids
     * @return array
     * @throws NotFoundException
     */
    public function findHavingIds(array $ids): ResourceCollection;
}

```

### Second

Register your ResourceQuery handlers with your Dogpile\ResourceManager

```php
<?php
// some bootstrap file

use Dogpile\ResourceManager;

$conn = $app['database_connection'];

$app[ResouceManager::class] = new ResourceManager(
    new AuthorsResourceQuery($conn),
    new CommentsResourceQuery($conn)
);

```

### Third

Dogpile\Contracts\ResourceQuery::findHavingIds() must return a Dogpile\Collections\ResourceCollection class containing objects that implement the Dogpile\Contracts\Resource interface.

```php
<?php

namespace Dogpile\Contracts;

use Dogpile\Collections\RelationshipCollection;

interface Resource
{
    /**
     * Resource type
     *
     * @return string
     */
    public function type(): string;

    /**
     * Resource id
     *
     * @return string
     */
    public function id(): string;

    /**
     * Returns an array of ResourceIdentifiers
     *
     * @return RelationshipCollection
     */
    public function relationships(): RelationshipCollection;
}
```

### Fourth

As stated in step above, each Resource object must implement the Resource::realtionships() interface which returns a RelationshipCollection class. The Dogpile\Collections\RelationshipCollection class can be implemented as such below.

```php

<?php

// Example data
$jsonapiData = [
    'type' => 'posts',
    'id' => '42',
    'attributes' => [
        'title' => 'Bridge of Death',
        'body' => 'What is the airspeed velocity of an unladed swallow?'
    ],
    'relationships' => [
        'author' => [
            'data' => ['type' => 'people', 'id' => '24']
        ],
        'comments' => [
            'data' => [
                ['type' => 'comments', 'id' => '1'],
                ['type' => 'comments', 'id' => '2'],
                ['type' => 'comments', 'id' => '3']
            ]
        ]
    ]
]

// Example model
use Dogpile\ResourceIdentifier;
use Dogpile\Contracts\Resource;
use Dogpile\Collections\RelationshipCollection;

class SomeObjectModel implements Resource
{
    protected $jsonapiData = [];

    public function __construct(array $jsonapiData)
    {
        $this->jsonapiData = $jsonapiData;
    }

    public function type(): string
    {
        return $this->jsonapiData['type'];
    }

    public function id(): string
    {
        return $this->jsonapiData['id'];
    }

    public function relationships(): RelationshipCollection
    {
        $collection = new RelationshipCollection();

        // add author
        $authorIdent = $this->jsonapiData['relationships']['author']['data'];
        $collection->add('author', ResourceIdentifier::create($authorIdent['type'], $authorIdent['id']));

        // add comments
        $commentIdentifiers = array_map(function($comment){
            return ResourceIdentifier::create($comment['type'], $comment['id']);
        }, $this->jsonapiData['relationships']['comments']['data']);


        $collection->add('comments', ...$commentIdentifiers);


        return $collection;
    }
}

```

For more examples, see the tests: `tests\Functional\ResourceManagerTest.php`