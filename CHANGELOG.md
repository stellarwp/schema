# Change Log

All notable changes to this project will be documented in this file. This project adhere to the [Semantic Versioning](http://semver.org/) standard.

## [unreleased] Unreleased

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
