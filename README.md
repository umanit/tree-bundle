## General configuration

As the routing for all content is managed by the bundle, you have to register the unique route **at the end** of your
`app/config/routing.yml` (in order to not override your custom routes) :

```
umanit_tree:
    resource: "@UmanitTreeBundle/Resources/config/routing.yml"
    prefix:   /
```

Register the bundle and doctrine extension to your `app/AppKernel.php`

```php           
    new Umanit\Bundle\TreeBundle\UmanitTreeBundle(),
    new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
```

Set doctrine extension config, at least like this one :
```yaml
stof_doctrine_extensions:
    default_locale: %locale%
    translation_fallback: true
    orm:
        default:
            tree: true
            sluggable: true
```

Update your database schema to add our model
```
bin/console doctrine:schema:update --force
```

## Create a new node type

You can now easily manage a new node type
