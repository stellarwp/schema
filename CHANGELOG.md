# Change Log

All notable changes to this project will be documented in this file. This project adhere to the [Semantic Versioning](http://semver.org/) standard.

## [3.2.0] Unreleased

* Feature - Add support for nested sub-WHERE clauses in `paginate()` and `get_total_items()` methods. This allows building complex queries like `WHERE (col1 = 'a' OR col2 = 'b') AND col3 = 'c'`.
* Feature - Add new `stellarwp_schema_custom_table_paginate_query` filter to allow modification of the paginate query before execution.
* Tweak - Rename hooks from `tec_common_*` prefix to `stellarwp_schema_*` prefix for consistency with the StellarWP namespace.

### Breaking Changes

The following hooks have been renamed:
- `tec_common_custom_table_query_pre_results` → `stellarwp_schema_custom_table_query_pre_results`
- `tec_common_custom_table_query_post_results` → `stellarwp_schema_custom_table_query_post_results`
- `tec_common_custom_table_query_results` → `stellarwp_schema_custom_table_query_results`
- `tec_common_custom_table_query_where` → `stellarwp_schema_custom_table_query_where`

[3.2.0]: https://github.com/stellarwp/schema/releases/tag/3.2.0

## [3.1.4] 2025-10-16

* Fix - Handle array values correctly in the `get_*` query methods.

## [3.1.3] 2025-10-08

* Fix - `get_current_schema` method will npw cache the schema of each implementation correctly.

[3.1.3]: https://github.com/stellarwp/schema/releases/tag/3.1.3

## [3.1.2] 2025-10-02

* Fix - `update_many` method will now properly check if the transaction was successful.

[3.1.2]: https://github.com/stellarwp/schema/releases/tag/3.1.2

## [3.1.1] 2025-10-01

* Tweak - Fix the `get_all_by` method to accept an order by clause.

[3.1.1]: https://github.com/stellarwp/schema/releases/tag/3.1.1

## [3.1.0] 2025-09-30

* Feature - Introduce new column types: `Blob_Column`, `Binary_Column`, and `Boolean_Column`.
* Feature - Introduce new PHP type: Blob. Blob will be stored as a base64 encoded string.
* Tweak - Update string based columns to have the ability to become primary keys. Those columns include: char, varchar, binary and varbinary.

[3.1.0]: https://github.com/stellarwp/schema/releases/tag/3.1.0

## [3.0.0] 2025-09-24

* Feature - Introduces stricter column and indexes definitions. This is NOT a backwards compatible change. Read the migration guide in docs/migrating-from-v2-to-v3.md.

[3.0.0]: https://github.com/stellarwp/schema/releases/tag/3.0.0

## [2.0.1] 2025-07-18

* Fix - Avoid dead db in multisite installations with too early checks.

## [2.0.0] 2025-05-20

Feature - Bump di52 to 4.0.1 and all other deps.

## [1.1.9] 2025-02-26

* Tweak - Add @throws tags from the [stellarwp/db](https://github.com/stellarwp/db) library and better generics.

## [1.1.8] 2025-01-10

* Feature - Introduce truncate method which does what the empty_table method was doing. Update empty_table to actually empty the table instead of truncating it.
* Tweak - Decide if we can create/update during this requests based on blog's status, preventing multiple "check" queries.

## [1.1.7] 2024-06-05

* Fix - `Collection::get_by_group()` now properly works with a single string group name.
* Fix - `Group_FilterIterator::count()` now properly returns the filtered count and not the base iterator count.
* Fix - `Needs_Update_FilterIterator::count()` now properly returns the filtered count and not the base iterator count.
* Fix - Use proper PSR namespacing for tests.
* Tests - code clean up and file name standardization.

## [1.1.3] 2023-04-04

* Feature - Added `Table::has_foreign_key()` method.

## [1.1.2] 2022-11-2

* Tweak - Set the composer's `config.platform.php` to `7.0`.

## [1.1.1] 2022-10-08

* Fix - Resolves some issues with docblocks that didn't pass PHPStan during actual usage of this library elsewhere.

## [1.1.0] 2022-08-30

### Changed

* Feature - Added [stellarwp/db](https://github.com/stellarwp/db) as a dependency.
* Tweak - Swapped out direct `$wpdb` calls with the `DB` class.
* Tweak - Reorganized abstract classes and interfaces into `Contracts/` directories.
* Tweak - Removed container and require the setting of a container via the `Config` class.
* Tests - Added some tests for index checking on tables.

## [1.0.0] 2022-08-17

### Added

* Feature - Initial version
* Docs - Documentation
* Tests - Automated tests

[1.0.0]: https://github.com/stellarwp/schema/releases/tag/1.0.0
[1.1.0]: https://github.com/stellarwp/schema/releases/tag/1.1.0
[1.1.1]: https://github.com/stellarwp/schema/releases/tag/1.1.1
[1.1.2]: https://github.com/stellarwp/schema/releases/tag/1.1.2
