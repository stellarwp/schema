# Change Log

All notable changes to this project will be documented in this file. This project adhere to the [Semantic Versioning](http://semver.org/) standard.

## [unreleased] Unreleased

## [1.1.0] 2022-08-30

### Changed

- Added [stellarwp/db](https://github.com/stellarwp/db) as a dependency.
- Swapped out direct `$wpdb` calls with the `DB` class.
- Added some tests for index checking on tables.
- Reorganized abstract classes and interfaces into `Contracts/` directories.
- Switched away from `StellarWP\Schema\Container` in favor of using `lucatume\DI52\App` and `lucatume\DI52\Container`.

## [1.0.0] 2022-08-17

### Added

- Initial version
- Documentation
- Automated tests

[1.0.0]: https://github.com/stellarwp/schema/releases/tag/1.0.0
