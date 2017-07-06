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

Add Gedmo configuration in your services.yml

```yaml
services:
    # Doctrine Extension listeners to handle behaviors
    gedmo.listener.tree:
        class: Gedmo\Tree\TreeListener
        tags:
            - { name: doctrine.event_subscriber, connection: default }
        calls:
            - [ setAnnotationReader, [ "@annotation_reader" ] ]

    gedmo.listener.translatable:
        class: Gedmo\Translatable\TranslatableListener
        tags:
            - { name: doctrine.event_subscriber, connection: default }
        calls:
            - [ setAnnotationReader, [ "@annotation_reader" ] ]
            - [ setDefaultLocale, [ %locale% ] ]
            - [ setTranslationFallback, [ false ] ]

    gedmo.listener.timestampable:
        class: Gedmo\Timestampable\TimestampableListener
        tags:
            - { name: doctrine.event_subscriber, connection: default }
        calls:
            - [ setAnnotationReader, [ "@annotation_reader" ] ]

    gedmo.listener.sluggable:
        class: Gedmo\Sluggable\SluggableListener
        tags:
            - { name: doctrine.event_subscriber, connection: default }
        calls:
            - [ setAnnotationReader, [ "@annotation_reader" ] ]

    gedmo.listener.sortable:
        class: Gedmo\Sortable\SortableListener
        tags:
            - { name: doctrine.event_subscriber, connection: default }
        calls:
            - [ setAnnotationReader, [ "@annotation_reader" ] ]

    gedmo.listener.loggable:
        class: Gedmo\Loggable\LoggableListener
        tags:
            - { name: doctrine.event_subscriber, connection: default }
        calls:
            - [ setAnnotationReader, [ "@annotation_reader" ] ]
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

## Create a Parent Node selector

### Usage example :

```php
$builder
    ->add(
        'parents',
        \Umanit\Bundle\TreeBundle\Form\Type\TreeNodeType::class,
        array(
            'required'     => false,
            'by_reference' => false,
        )
    )
;
```

## Create SEO Metadata Form

### Usage example :

```php
$builder
    ->add(
        'seoMetadata',
        \Umanit\Bundle\TreeBundle\Form\Type\SeoMetadataType::class,
        array(
            'required'     => false,
        )
    )
;
```


## Create a link selector

In order to create links to one or more nodes (or external links), it's possible !

In your entity that will have the link, adds a relation with the entity `Umanit\Bundle\TreeBundle\Entity\Link`.

In your forms, you can materialize the relation with the `Umanit\Bundle\TreeBundle\Form\Type\LinkType`. By default, you'll have 2 fields, "internal link" (a textfield) and "external link" (a select). By default, the select will be empty. You have to populate it by giving the models allowed. You can keep only one field with the options : `allow_internal: false` or `allow_external: false`. Note : only one field can be filled at the same time.

You can define labels with `label_internal` and `label_external`

### Usage example :

```php
$builder
    ->add('link', 'umanit_link_type_translatable', array(
        'label' => 'Link',
        // List of content types available
        'models' => array(
            'Page'    => 'Umanit\AppBundle\Entity\Page',
            'Article' => 'Umanit\AppBundle\Entity\Article'
        ),
        // Filters for some content types (if needed)
        query_filters = array(
            'Umanit\AppBundle\Entity\Page' => ['locale' => 'en']
        ),
        'allow_external' => false
    ))
;
```

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

- `get_path(object, parentObject = null, root = false, absolute = false, parameters = [)`

Returns the route for the given entity (if the entity implements TreeNodeInterface)

- `get_path_from_node(node, absolute = false, parameters = [])`

Returns the route for the given node (instance of `Umanit\Bundle\TreeBundle\Entity\Node`)

- `get_path_from_link(link)`

Returns the path for the given link instance (instance of `Umanit\Bundle\TreeBundle\Entity\Link`).

- `is_external_link(link)`

Returns true if the given link targets an external URL (instance of `Umanit\Bundle\TreeBundle\Entity\Link`).

## Configuration reference

```yaml
umanit_tree:
    locale:               '%locale%'                                    # Default locale to use
    root_class:           \Umanit\Bundle\TreeBundle\Entity\RootEntity   # Class for the root node. If you have a homepage object, put it there

    # Defines a controller to call by class. Foreach entity ("class"), set a controller and method to call
    controllers_by_class:
        -
            class:                ~ # Required. Ex. : AppBundle\Entity\Page
            controller:           ~ # Required. Ex. : AppBundle:Page:show


    # Seo default values and translation domain
    seo:
        redirect_301:         true   # Redirect old URLs to new one
        default_title:        'Umanit Tree'
        default_description:  'Umanit tree bundle'
        default_keywords:     'umanit, web, bundle, symfony2'
        translation_domain:   'messages'

    # Root node and translation domain for breadcrumb elements
    breadcrumb:
        root_name:            'Home'
        translation_domain:   'messages'
```
