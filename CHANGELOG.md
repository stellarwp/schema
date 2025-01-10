# Change Log

All notable changes to this project will be documented in this file. This project adhere to the [Semantic Versioning](http://semver.org/) standard.

## [unreleased] Unreleased

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
