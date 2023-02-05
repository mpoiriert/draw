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