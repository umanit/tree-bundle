## Purpose

The bundle allows you to easily manage content type routing, with the slug, breadcrumb and SEO

## General configuration

As the routing for all content is managed by the bundle, you have to register the unique route **at the end** of your
`app/config/routing.yml` (in order to not override your custom routes) :

```
umanit_tree:
    resource: "@UmanitTreeBundle/Resources/config/routing.yml"
    prefix:   /
```

Register the bundle to your `app/AppKernel.php`

```php           
    new Umanit\Bundle\TreeBundle\UmanitTreeBundle(),
```

Update your database schema to add our model
```
bin/console doctrine:schema:update --force
```

Now, you have to create the root node. The default object is RootEntity, but you can override it in configuration (param
`umanit_tree.root_class`).

To create the root node, execute the following command

```
bin/console umanit:tree:initialize
```

## Create a new node type

You can now easily manage a new node type :

- Create an entity
- Implements `Umanit\Bundle\TreeBundle\Model\TreeNodeInterface` and `Umanit\Bundle\TreeBundle\Model\SeoInterface`
- You can now use the `Umanit\Bundle\TreeBundle\Model\TreeNodeTrait` and `Umanit\Bundle\TreeBundle\Model\SeoTrait` to have
a default implementation for most of the methods to implement

The 4 methods of TreeNodeInterface are :

- `public function getTreeNodeName();` : Returns the node name, used to build a slug of your route
- `public function getParents();` : Returns the parent objects. For example, if you want a path /category/my-product for
"My Product", must returns an array with an object "Category" that implements TreeNodeInterface too
- `public function createRootNodeByDefault();` : If the node has or hasn't parent, should a node be created ? (e.g /my-product)
- `public function getLocale()` : Locale of the object (a locale recognize by `$request->getLocale()` of Symfony)

You can manage some SEO options such as title, description and keywords with the `Umanit\Bundle\TreeBundle\Model\SeoInterface`
and use the `Umanit\Bundle\TreeBundle\Model\SeoTrait` to automatically add an attribute "seoMetadata" to your entity

## Twig helpers

- `get_seo_title(default = "", override = false)`
- `get_seo_description(default = "", override = false)`
- `get_seo_keywords(default = "", override = false)`

Returns the title, description and keywords of the current document if the route is managed by an entity that implements
SeoInterface. Otherwise, the default value (from config) will be used, or the value from "default" parameter if it is set.
If you set override to true, the value of the "default" parameter will be always used.

- `get_breadcrumb(elements = array())`

Returns the breadcrumb (array of name/link). It will parse all parents of the current entity if the route is managed by
an entity. You can add additional links with the "elements" parameter. An array of name/link.

- `get_path(object)`

Returns the route for the given entity (if the entity implements TreeNodeInterface)
