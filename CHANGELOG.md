# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Fixed

- Wrong service declaration for both controllers has been fixed

## [1.0.5] - 2023-04-13

### Fixed

- Fixes wrong typing on `SeoTrait->getSeoMetadata()` parameter and `SeoTrait->setSeoMetadata()` return value
  (allowing null value to comply with property type declaration)

## [1.0.4] - 2023-04-03

### Fixed

- Adds a return to a setter in `SeoMetadata` to comply with return type declaration

## [1.0.3] - 2023-02-02

### Fixed

- Fixes erroneous changes to constructor in `NodeUpdatedEvent` and `NodeBeforeUpdateEvent`

## [1.0.2] - 2023-01-26

### Fixed

- Fixes typing misdeclarations in `SeoMetadata`

## [1.0.1] - 2023-01-18

### Added

- Adds declaration of custom entity manager
- Adds Doctrine as a package requirement

### Changed

- Manually injects custom entity manager in `NodeHelper` declaration

## [1.0.0] - 2023-01-18

### Added

- Adds initial CHANGELOG
- Adds support for PHP >= 8.0
- Adds support for Symfony >= 5.4
- Adds typing
- Adds attributes (both annotations and attributes are available for Doctrine ORM mapping configuration)

### Changed

- Changes bundle namespace from `Umanit\Bundle\TreeBundle` to `Umanit\TreeBundle`
- Changes classes, interfaces, traits etc. method signatures
- Updates route declarations

### Removed

- Drops support for PHP < 8.0
- Drops support for Symfony < 5.4

## [0.3.2] - 2018-10-05

Last release of v0.

[Unreleased]: https://github.com/umanit/tree-bundle/compare/1.0.5...HEAD

[1.0.5]: https://github.com/umanit/tree-bundle/compare/1.0.4...1.0.5

[1.0.4]: https://github.com/umanit/tree-bundle/compare/1.0.3...1.0.4

[1.0.3]: https://github.com/umanit/tree-bundle/compare/1.0.2...1.0.3

[1.0.2]: https://github.com/umanit/tree-bundle/compare/1.0.1...1.0.2

[1.0.1]: https://github.com/umanit/tree-bundle/compare/1.0.0...1.0.1

[1.0.0]: https://github.com/umanit/tree-bundle/compare/0.3.2...1.0.0

[0.3.2]: https://github.com/umanit/tree-bundle/releases/tag/0.3.2
