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
  skip_value: '_SKIP_' # Optional, defaults to "_SKIP_". See "Preserving existing values" below.
  classes:
    App\Entity\User:
      alias: 'User' #The alias will be used instead of the full class name in the dropdown and database
    App\Entity\Product:
      alias: 'Product'
```

This tell the system that it support import for **App\Entity\User** and **App\Entity\Product**.

## Preserving existing values on partial imports

When updating existing entities, an empty cell or any other value is normally written
to the entity. This is a problem for partial imports — for example, updating only one
locale of a translatable field while leaving the other locales untouched.

To opt out of writing a given cell, use the **skip value** (default `_SKIP_`):

```csv
id,translation#en.title,translation#fr.title,translation#pl.title
42,Ocean kingdom,Royaume océanique,_SKIP_
```

For row `id=42`, the English and French titles are updated and the Polish title is
left exactly as it is in the database. The check happens in
`Importer::assignValue()` *before* any type coercion, so the marker also works on
date columns, boolean columns, etc.

You can change the marker per project via the `skip_value` configuration key. The
marker only makes sense on update — on insert (`insertWhenNotFound: true`) a skipped
column simply leaves the field at its default value.

An empty cell is **not** a skip — it still clears the field. Use the marker explicitly
when you want to keep the stored value.

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
