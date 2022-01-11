DrawSonataExtraBundle
====================

This bundle adds some functionalities to the [Sonata Project](https://sonata-project.org/) different bundle.

## Detect admin argument

The current way to define admin argument is that way:

```YAML
App\Sonata\Admin\UserAdmin:
    arguments: [ ~, 'App\Entity\User', ~ ]
```

You can now omit the constructor argument by setting default value in your constructor class:

```PHP
namespace App\Sonata\Admin;

user App\Entity\User;
use Sonata\AdminBundle\Admin\AbstractAdmin;

class UserAdmin extends AbstractAdmin
{
    public function __construct($code, $class = User::class, $baseControllerName = null)
    {
        parent::__construct($code, $class, $baseControllerName);
    }
}
```

A compiler will extract the default value from the argument. They need to have the exact same name to be extracted. If
you have defined the arguments from any other mean prior to the compiler pass they will not be replaced.

## TagSonataAdmin

Base on the [terminal42/service-annotation-bundle](https://github.com/terminal42/service-annotation-bundle) a custom
**Draw\Bundle\SonataExtraBundle\Annotation\TagSonataAdmin** annotation is available. This bundle need to be installed
for the annotation to work. 

The tag define the option available at this time in the sonata admin tag. It's not strict so if you define any extra option
it will not throw any exception and will pass them through. This allows to support new option without breaking anything
but will also not validate any option.

You can now go from:

```YAML
App\Sonata\Admin\UserAdmin:
    tags:
        - { name: 'sonata.admin', manager_type: 'orm', group: 'User', pager_type: 'simple', icon: 'fa fa-user' }
```

To:
```YAML
App\Sonata\Admin\UserAdmin: ~
```

```PHP
namespace App\Sonata\Admin;

user App\Entity\User;
use Draw\Bundle\SonataExtraBundle\Annotation\TagSonataAdmin;
use Sonata\AdminBundle\Admin\AbstractAdmin;

/**
 * @TagSonataAdmin(group="User", pager_type="simple", icon="fa fa-user")
 */
class UserAdmin extends AbstractAdmin
{
    public function __construct($code, $class = User::class, $baseControllerName = null)
    {
        parent::__construct($code, $class, $baseControllerName);
    }
}
```

## Display date in admin according to user time zone

Date from doctrine will be displayed in user timezone.

Any save will be done on base on server configuration (UTC recommended).

Enable this feature in config:
```YAML
draw_sonata_extra:
  user_timezone:
    enabled: true
```
Add the javascript
```YAML
sonata_admin:
  assets:
    extra_javascripts:
      - bundles/drawsonataextra/main.js
```

## Fix menu depth when only 1 sub menu

When a menu just have one submenu it can be fix to remove the submenu.

This:
```
User
 --> List
Entity
 --> List
Section
 --> Entity1 List
 --> Entity2 List
```

Would become:
```
User
Entity
Section
 --> Entity1 List
 --> Entity2 List
```

Enable this feature in config:
```YAML
draw_sonata_extra:
  fix_menu_depth: true
```