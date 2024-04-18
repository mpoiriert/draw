# DrawSonataImportBundle

This bundle add a basic import from csv system of entities in Sonata.

> It's currently only support update of existing entities.

Base on a configuration you can set which entity can be imported.

When uploading a csv file the system will detect the list of attribute base on a header column.
It will then try to detect the identifier and the mutator to update the entities.

## Configuration

Here is an example of the configuration:

```YAML
draw_sonata_import:
  classes:
    App\Entity\User:
      alias: 'User' #The alias will be used instead of the full class name in the dropdown and database
    App\Entity\Product:
      alias: 'Product'
```

This tell the system that it support import for **App\Entity\User** and **App\Entity\Product**.

## Sonata admin

A new menu **Import** will be available on the lef menu to create a new import. A dropdown to specify which entity
you are importing is available base on the configuration. There is also an action **Import** on the list view of the
entities that will link directly to this page with the entity selected in the dropdown.

You must select a csv file from which the system will detect the header and will try to fill the column information.
You can then adjust it manually and select to **Process** the file.

## Column Information Extraction

The system try to extract column information base on a **Draw\Bundle\SonataImportBundle\Column\ColumnBuilder\ColumnBuilderInterface**.
There is two extractor provider with the system, one that will set if a column is the identifier base on it's name (id only),
another one that will check if there is a **setter** base on the header to assign the mutator.

## Import

When importing data a event **Draw\Bundle\SonataImportBundle\Event\AttributeImportEvent** is dispatch for every entity/column.
Listening to this event let you do custom import logic of the data. If you did process the event you need to stop
is propagation. If the event was not stop, the import logic will fall back on the column mutator that was set.

The setter just do a simple set of the raw value in the column (that is always a string) so if it's a reference to another
object you must implement a listener to do have a custom logic.
