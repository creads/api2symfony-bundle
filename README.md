# Api2Symfony Bundle

A Symfony2 bundle to automatically generate controllers from standard API specifications (RAML, Blueprint, Swagger...)

BUT... We only support the following specification formats now:

* RAML

But we'd like to also support:

* Blueprint
* Swagger

This bundle uses the [api2symfony](https://github.com/creads/api2symfony) library.

## Installation

Using composer:

`composer require creads/api2symfony-bundle 1.0.*@dev`

Register the bundle by updating `AppKernel.php`:

    <?php

	// in AppKernel::registerBundles()
	if (in_array($this->getEnvironment(), ['dev', 'test'])) {
    	    // ...
    	    $bundles[] = new Creads\Api2SymfonyBundle\Api2SymfonyBundle();
    	    // ...
	};

## Use case

The bundle provides a command:

`app/console.php api2symfony:generate:raml path/to/api.raml Some\\Namespace`

### Example

`php app/console.php api2symfony:generate:raml path/to/api.raml Acme\\DemoBundle`

`New controller dumped at app/cache/dev/api2symfony/Acme/DemoBundle/1_0_1_alpha/PostsController.php`

```yaml

	#%RAML 0.8
	title: Api Example
	version: 1.0.1-alpha

	/posts:
	  description: Collection of available post resource
	  get:
    	description: Get a list of post
	  post:
    	description: Create a new post
      /{id}:
        displayName: Post
        get:
          description: Get a single post
          responses:
            200:
              body:
                application/json:
                  example: |
                    {
                      "title": "An amazing news"
                    }
        delete:
          description: Delete a specific post
        /comments:
          description: Collection of available post's comments
          displayName: Comments
          get:
            description: Get list of comment for given post
          post:
            description: Comment a post
```

Generated code:

```php

	<?php

	namespace Acme\DemoBundle\1_0_1_alpha;

	use Symfony\Bundle\FrameworkBundle\Controller\Controller;

	use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
	use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

	/**
	 * Collection of available post resource
	 *
	 * This is an auto generated class provided by Creads\Api2Symfony PHP library.
	 */
	class PostsController extends Controller
	{
    	/**
	     * Get a list of post
    	 *
	     * @Route("/posts", name="get_posts")
    	 * @Method({"GET"})
	     */
    	public function getPostsAction()
	    {

    	}

	    /**
    	 * Create a new post
	     *
    	 * @Route("/posts", name="post_posts")
	     * @Method({"POST"})
    	 */
	    public function postPostsAction()
    	{

	    }

    	/**
	     * Get a single post
    	 *
	     * @Route("/posts/{id}", name="get_posts_post")
    	 * @Method({"GET"})
	     */
    	public function getPostsPostAction($id)
	    {
    	    return new Response('{"title": "An amazing news"}', 200, array('Content-Type' => 'application/json'));
	    }

	    /**
    	 * Delete a specific post
	     *
    	 * @Route("/posts/{id}", name="delete_posts_post")
	     * @Method({"DELETE"})
    	 */
	    public function deletePostsPostAction($id)
    	{

	    }

	    /**
    	 * Get list of comment for given post
	     *
    	 * @Route("/posts/{id}/comments", name="get_posts_post_comments")
	     * @Method({"GET"})
    	 */
    	public function getPostsPostCommentsAction($id)
	    {

    	}

	    /**
    	 * Comment a post
	     *
    	 * @Route("/posts/{id}/comments", name="post_posts_post_comments")
	     * @Method({"POST"})
    	 */
	    public function postPostsPostCommentsAction($id)
    	{

	    }
	}
```
