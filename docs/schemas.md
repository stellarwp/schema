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

## Table schemas

Table schema classes hold all of the building blocks for getting a custom table to be defined and managed by this library. Table

## Field schemas
