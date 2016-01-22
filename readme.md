Spiral, modular RAD Framework (beta)
=======================
[![Latest Stable Version](https://poser.pugx.org/spiral/framework/v/stable)](https://packagist.org/packages/spiral/framework) [![Total Downloads](https://poser.pugx.org/spiral/framework/downloads)](https://packagist.org/packages/spiral/framework) [![License](https://poser.pugx.org/spiral/framework/license)](https://packagist.org/packages/spiral/framework) [![Build Status](https://travis-ci.org/spiral/spiral.svg?branch=master)](https://travis-ci.org/spiral/spiral) [![Gitter](https://badges.gitter.im/Join Chat.svg)](https://gitter.im/spiral/hotline)

The Spiral framework provides a modular Rapid Application Development (RAD) platform to develop web applications using an HMVC architecture, separation of database and service layers, code re-usability, modern practices, PSR-7, simple syntax and customizable scaffolding mechanisms.

[**Skeleton App**](https://github.com/spiral-php/application) | [Guide](https://github.com/spiral-php/guide) | [Gitter](https://gitter.im/spiral/hotline) | [**Forum**](https://groups.google.com/forum/#!forum/spiral-framework) | [Twitter](https://twitter.com/spiralphp) | [**Foundation Components**](https://github.com/spiral/components) | [Modules](https://github.com/spiral-modules) | [**Contributing Guide**](https://github.com/spiral/guide/blob/master/contributing.md)

Spiral framework has not been publicly released/announced yet due guide is still in progress. :/ Currenlty development focused
on functionality transtion from previous generation engine.

Temporary in transition
=======================
* PHPStorm IDE help module (ORM and ODM)
* Guide update (current version is seriously outdated)

Examples:
========

```php
class HomeController extends Controller
{
    use TranslatorTrait;

    /**
     * DI can automatically deside what database/cache/storage
     * instance to provide for every action parameter based on it's 
     * name or type.
     *
     * In most cases you don't even need to configure DI to make your
     * application work due autowiring nature of default container.
     *
     * @param Database   $database
     * @param Database   $logDatabase
     * @param HttpConfig $config
     * @return string
     */
    public function indexAction(Database $database, Database $logDatabase, HttpConfig $config)
    {
        dump($config->basePath());
    
        $logDatabase->table('log')->insert(['message' => 'Yo!']);
    
        return $this->views->render('welcome', [
            'users' => $database->table('users')->select()->where('name', 'John')->all()
        ]);
    }
    
    /**
     * @param string      $id
     * @param PostSource $source
     * @return array
     */
    public function updateAction($id, PostSource $source)
    {
        if (empty($post = $source->findByPK($id))) {
            throw new ForbiddenException("Undefined post");
        }

        //See Security Module
        $this->authorize('posts.edit', compact('post'));

        //In-Model filtration and validation
        $entity->setFields($this->input->data);
        if (!$source->save($entity, $errors)) {
            return [
                'status' => 400,
                'errors' => $errors
            ];
        }

        return [
            'status'  => 200,
            'message' => $this->say('Post information has been updated')
        ];
    }
}
```

PSR-7 is under the hood:

```php
$route->middleware(function ($request, $response, $next) {
    return $next($request, $response)->withHeader('My-Header', 'Yay!');
});
```

JSON responses, method injections, container visibility scopes:

```php
public function indexAction(ServerRequestInterface $request)
{
    return [
        'status' => 200,
        'uri'    => (string)$request->getUri()
    ];
}
```

Database introspection and schema declaration (diff based):

![Databases](https://raw.githubusercontent.com/spiral/guide/master/resources/db-schema.gif)

ORM with adaptive scaffolding for MySQL, PostgresSQL, SQLite, SQLServer:

```php
class Post extends Record //or RecordEntity to use as DataMapper
{
    use TimestampsTrait;

    //Database partitions, isolation and aliasing
    protected $database = 'blog';

    protected $schema = [
        'id'     => 'bigPrimary',
        'title'  => 'string(64)',
        'status' => 'enum(published,draft)',
        'body'   => 'text',
        
        //Simple relation definition (optional)
        'author'   => [self::BELONGS_TO => Author::class],
        'comments' => [self::HAS_MANY => Comment::class],
        
        //Not very simple relation definitions (optional)
        'collaborators' => [
            self::MANY_TO_MANY  => User::class,
            self::PIVOT_TABLE   => 'post_collaborators_map',
            self::PIVOT_COLUMNS => [
                'time_assigned' => 'datetime',
                'type'          => 'string, nullable',
            ],
            User::INVERSE       => 'collaborated_posts'
        ],
    ];
}
```

```php
//Post::find() == $this->orm->selector(Post::class) == PostSource->find()
$posts = Post::find()
    ->distinct()
    ->with('comments') //Automatic joins
    ->with('author')->where('author.name', 'LIKE', $authorName) //Fluent
    ->load('comments.author') //Cascade eager-loading (joins or external query)
    ->paginate(10) //Quick pagination using active request
    ->all();

foreach($posts as $post) {
    echo $post->author->getName();
}
```

Embedded functionality for static indexation of your code:

```php
public function indexAction(ClassLocatorInterface $locator, InvocationLocatorInterface $invocations)
{
    //Not AST yet, but planned... :(
    dump($locator->getClasses(ControllerInterface::class));
}
```

Extendable and programmable template engine compatible with any command syntax ([plain PHP by default](https://github.com/spiral/spiral/issues/125)):

```html
<spiral:grid source="<?= $uploads ?>" as="upload">
    <grid:cell title="ID:" value="<?= $upload->getId() ?>"/>
    <grid:cell title="Time Created:" value="<?= $upload->getTimeCreated() ?>"/>
    <grid:cell title="Label:" value="<?= e($upload->getLabel()) ?>"/>

    <grid:cell.bytes title="Filesize:" value="<?= $upload->getFilesize() ?>"/>

    <grid:cell>
        <a href="<?= uri('uploads::edit', $upload) ?>">Edit</a>
    </grid:cell>
</spiral:grid>
```
> You can write your own virtual tags (similar to web components), layouts and wrappers with almost any functionality or connect external libraries like [Vault](https://github.com/spiral-modules/vault):

![Grid](https://raw.githubusercontent.com/spiral/guide/master/resources/grid.png)


Includes
=============
Plug and Play extensions, ajax form tookit, componental nature, small footprint, IDE friendly, frontend toolkit, cache and logic cache, 
static analysis, metaprograming, cloud storages, auto-indexable translator, Interop Container, Zend Diactoros, Symfony Console, 
Symfony Translation (interfaces), Symfony Events, Monolog, Twig, debugging/profiling tools and much more.

Modules
=======
[Scaffolder](https://github.com/spiral-modules/scaffolder) - provides set of console commands and extendable class declarations for application scaffolding.

[Security Layer](https://github.com/spiral-modules/security) - flat RBAC security layer with Role-Permission-Rule association mechanism. 

[Vault](https://github.com/spiral-modules/vault) - friendly and extendable administration panel based on Materialize CSS and Security component.

Inspired by
===========
Laravel 5+, CodeIgniter, Yii 2, Symfony 2, Ruby on Rails (conceptually), many other engines.
