# Schema management

This library is all about managing custom schemas (a.k.a. SQL table definitions). You can do that by declaring both [table schemas](#table-schemas) and [field schemas](#field-schemas) as classes within your project's codebase.

## Where to put your schema class files

Each project is different and this library tries to avoid being overly opinionated. So, put your table and field schema classes wherever it makes sense in your project. If you use namespaces, awesome. That works nicely. If you don't that works too. You'll just need to include your classes before you can register the schemas.

### Example directory stucture

For a plugin that uses composer and namespacing, this is a potential structure for placing your schema classes - putting them in `Fields/` and `Tables/` directories.

```
composer.lock
composer.json
boom-shakalaka.php
src/
	BoomShakalaka/
		Fields/
			A_Field_Schema.php
			Another_Field_Schema.php
		Tables/
			A_Table_Schema.php
			Another_Table_Schema.php
		...other stuff
vendor/
```

## `dbDelta()` under the hood

Under the hood of this library, schema management is done via the [`dbDelta()`](https://developer.wordpress.org/reference/functions/dbdelta/) function within WordPress. That function is battle tested and reliable, so we are standing on the shoulders of champions here. Any schema - whether a table schema of a field schema - has its SQL run through that function to ensure the table definitions are up to date.

It is important to note that `dbDelta()` does not _remove_ fields or indices in a database table. This approach is meant to prevent the accidentaly removal of data. We take a similar stance with this library. There _are_ ways to remove fields and drop tables, but those actions do not happen automatically and are up to you to decide when it is appropriate to do so.

## Schema documentation

* [Table schemas](schemas-table.md)
* [Field schemas](schemas-field.md)
