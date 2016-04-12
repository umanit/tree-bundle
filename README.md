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
