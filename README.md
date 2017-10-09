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
            - { name: doctrine.event_subscriber, connection: default, priority: 10 }
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
            - { name: doctrine.event_subscriber, connection: default, priority: 100 }
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

## Events

You can subscribe to some events in order to alter some behaviors

(in order)
- `umanit.node.before_update` : Called before any node save for an entity
- `umanit.node.parent_register` : Allows to add/remove parents to an entity
- `umanit.node.updated` : Called once an entity saved its nodes and parents

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

## Using the menu admin

> **/!\ For performance concerns, we chose to support PostgreSQL only.**

Follow those two steps to get started:

### 1. Create your Menu entity

```php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Umanit\Bundle\TreeBundle\Entity\AbstractMenu as BaseMenu;

/**
 * Menu
 *
 * @ORM\Table(name="menu")
 * @ORM\Entity(repositoryClass="Umanit\Bundle\TreeBundle\Repository\MenuRepository") // Using TreeBundle's repository is mandatory
 * @ORM\HasLifecycleCallbacks() // This is mandatory too
 */
class Menu extends BaseMenu
{
}
```

### 2. Configure TreeBundle to use your Menu entity

```yaml
# app/config/config.yml
umanit_tree:
    # ...
    menu_entity_class:    AppBundle\Entity\Menu

```

### Usage

#### Front

TreeBundle doesn't come with a template for the menu. A global twig variable is injected to your site, use it to build your menu template(s).

**Example:**

```twig
<nav class="nav-primary">
    <ul class="nav-primary__list nav-primary__lvl-1">
        {% for menu in menus %}
            {% if menu.position == 'primary' %}
                <li class="nav-primary__item">
                    <a href="{{ menu.link is empty ? '#' : get_path_from_link(menu.link) }}" class="nav-primary__link">
                        {{- menu.title|raw -}}
                        {#- <br class="hidden-xs hidden-sm"> -#}
                    </a>
                    {% if menu.children is not empty %}
                        <ul class="nav-primary__list nav-primary__lvl-2">
                            {% for subMenu in menu.children %}
                                {%  if subMenu.link is empty %}
                                    <li class="nav-primary__label">{{ subMenu.title|raw }}</li>
                                    {% else %}
                                    <li class="nav-primary__item">
                                        <a href="{{ get_path_from_link(subMenu.link) }}" class="nav-primary__link"
                                            <span class="nav-primary__text">{{ subMenu.title|raw }}</span>
                                        </a>
                                    </li>
                                {% endif %}
                            {% endfor %}
                        </ul>
                    {% endif %}
                </li>
            {%  endif %}
        {% endfor %}
    </ul>
</nav>
```

#### Menu admin

A CRUD is provided in order to administrate your menus. It's available on the route `tree_admin_menu_dashboard`, /admin/menu.

Start by running `php bin/console assets:install` to get the assets in your web directory.

##### Customizing the admin layout

The layout can be customized to your needs by setting the `admin_layout` configuration value. 

Example if you want to use Sonata Admin's layout:
```yaml
# config.yml
umanit_tree:
    ...
    admin_layout: '@SonataAdmin/standard_layout.html.twig' # Default is '@UmanitTree/admin/default_layout.html.twig'
```

The menu admin has 4 javascript dependencies, you're ought to include them as well. Have a look in the default_layout.html.twig. 

```HTML
    <!-- @UmanitTree/admin/default_layout.html.twig -->
    <script src="{{ asset('bundles/umanittree/vendor/js/jquery.min.js') }}"></script>
    <script src="{{ asset('bundles/umanittree/vendor/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('bundles/umanittree/vendor/js/jquery.fancytree-all-deps.min.js') }}"></script>
    <script src="{{ asset('bundles/umanittree/vendor/js/jquery.fancytree.dnd.js') }}"></script>
```

TreeBundle ships with those assets, you may use them or your own.

Again, if you want to use it with SonataAdmin, configure it as following:

```yaml

sonata_admin:
    # ...
    assets:
        stylesheets:
            # Defaults:
            - bundles/sonatacore/vendor/bootstrap/dist/css/bootstrap.min.css
            - bundles/sonatacore/vendor/components-font-awesome/css/font-awesome.min.css
            - bundles/sonatacore/vendor/ionicons/css/ionicons.min.css
            - bundles/sonataadmin/vendor/admin-lte/dist/css/AdminLTE.min.css
            - bundles/sonataadmin/vendor/admin-lte/dist/css/skins/skin-black.min.css
            - bundles/sonataadmin/vendor/iCheck/skins/square/blue.css
            - bundles/sonatacore/vendor/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css
            - bundles/sonataadmin/vendor/jqueryui/themes/base/jquery-ui.css
            - bundles/sonatacore/vendor/select2/select2.css
            - bundles/sonatacore/vendor/select2-bootstrap-css/select2-bootstrap.min.css
            - bundles/sonataadmin/vendor/x-editable/dist/bootstrap3-editable/css/bootstrap-editable.css
            - bundles/sonataadmin/css/styles.css
            - bundles/sonataadmin/css/layout.css
            - bundles/sonataadmin/css/tree.css
            - bundles/sonataadmin/css/colors.css
            # TreeBundle's assets
            - bundles/umanittree/css/admin.css
            - bundles/umanittree/css/vendor/ui.fancytree.min.css
        javascripts:
            # Defaults:
            - bundles/sonatacore/vendor/jquery/dist/jquery.min.js
            - bundles/sonataadmin/vendor/jquery.scrollTo/jquery.scrollTo.min.js
            - bundles/sonatacore/vendor/moment/min/moment.min.js
            - bundles/sonataadmin/vendor/jqueryui/ui/minified/jquery-ui.min.js
            - bundles/sonataadmin/vendor/jqueryui/ui/minified/i18n/jquery-ui-i18n.min.js
            - bundles/sonatacore/vendor/bootstrap/dist/js/bootstrap.min.js
            - bundles/sonatacore/vendor/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js
            - bundles/sonataadmin/vendor/jquery-form/jquery.form.js
            - bundles/sonataadmin/jquery/jquery.confirmExit.js
            - bundles/sonataadmin/vendor/x-editable/dist/bootstrap3-editable/js/bootstrap-editable.min.js
            - bundles/sonatacore/vendor/select2/select2.min.js
            - bundles/sonataadmin/vendor/admin-lte/dist/js/app.min.js
            - bundles/sonataadmin/vendor/iCheck/icheck.min.js
            - bundles/sonataadmin/vendor/slimScroll/jquery.slimscroll.min.js
            - bundles/sonataadmin/vendor/waypoints/lib/jquery.waypoints.min.js
            - bundles/sonataadmin/vendor/waypoints/lib/shortcuts/sticky.min.js
            - bundles/sonataadmin/Admin.js
            - bundles/sonataadmin/treeview.js
            # TreeBundle's assets
            - bundles/umanittree/js/admin.js
            - bundles/umanittree/js/admin.multi-media.js
            - bundles/umanittree/js/vendor/jquery.fancytree-all-deps.min.js
            - bundles/umanittree/js/vendor/jquery.fancytree.dnd.js

```
##### Customizing the admin form

Let's assume you added an image attribute on your Menu entity and want to use VichUploader to administrate it.

First, Create a form type:
```php
namespace AppBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Umanit\Bundle\TreeBundle\Form\Type\MenuType as BaseMenuType;

class MenuType extends BaseMenuType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('imageFile', VichImageType::class, [
                'label'        => 'Image',
                'required'     => false,
                'allow_delete' => true,
                'attr'         => [
                    'imagine_pattern' => 'admin',
                ],
            ])
            ->add('altImage')
        ;
    }
}

```

Then add you form type to TreeBundle's configuration:
```yaml
umanit_tree:
    # ...
    menu_form_class:      AppBundle\Form\MenuType
```

## Configuration reference

```yaml
umanit_tree:
    locale:               '%locale%'                                    # Optional. Default locale to use
    root_class:           '\Umanit\Bundle\TreeBundle\Entity\RootEntity' # Optional. Class for the root node. If you have a homepage object, put it there
    admin_layout:         '@UmanitTree/admin/default_layout.html.twig'  # Optional. Default layout for the menu admin section
    menu_form_class:      'Umanit\Bundle\TreeBundle\Form\Type\MenuType' # Optional. Default form for Menu
    menu_entity_class:    'AppBundle\Entity\Menu'                       # Optional. Your menu entity. Required if you want to use the menu admin
    menus:                ['primary']                                   # Optional. Configure you menus.
    
    # Defines configuration per node types. You can set a specific controller per class and set if the node type must appear in the menu admin.
    node_types:
        # Prototype
        -
            class:                ~ # Required. Ex. : AppBundle\Entity\Page
            controller:           ~ # Optional. Default FrameworkBundle:Template:template. Ex. : AppBundle:Page:show
            template:             ~ # Required if controller is not set.
            menu:                 ~ # Optional. Default is false


    # Seo default values and translation domain
    seo:
        redirect_301:         true   # Redirect old URLs to new ones
        default_title:        'Umanit Tree'
        default_description:  'Umanit tree bundle'
        default_keywords:     'umanit, web, bundle, symfony2'
        translation_domain:   'messages'

    # Root node and translation domain for breadcrumb elements
    breadcrumb:
        root_name:            'Home'
        translation_domain:   'messages'

```
