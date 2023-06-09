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

## New Template types

Some templates type are added to the default Sonata templates.

By default, all the js and css files are added to the `sonata_admin.assets.extra_javascripts` 
and `sonata_admin.assets.extra_stylesheets section.

If you want to install them manually you can set the `draw_sonata_extra.install_assets` to false.

### Show

#### json

Allow to display a json data based on the <https://github.com/abodelot/jquery.json-viewer>.

If you want to import the assets via webpack you must import this:

```javascript
import 'jquery.json-viewer/json-viewer/jquery.json-viewer.js';
import 'jquery.json-viewer/json-viewer/jquery.json-viewer.css';
import '../public/bundles/drawsonataextra/js/json_viewer.js';
```

> **Note**: Don't forget to install the [jquery.json-viewer](https://www.npmjs.com/package/jquery.json-viewer) package.
