# `CHANGELOG.md`

This changelog will keep track of any significant changes to the package. Versions will be named and versioned according to the following schema:

- Major versions (The "X" in `vX.Y.Z`) will be released when the minimum required version of PHP or Laravel changes. These versions may contain breaking 
  changes, so it is recommended to be careful when updating. The corresponding section of this document will contain any relevant information regarding any 
  breaking changes.
- Minor versions (The "Y" in `vX.Y.Z`) will be released when the underlying code has changed in a significant way but which does not contain any breaking 
  changes. This includes optimizations, refactors, additional features, etc. It should always be safe to update to the latest minor version.
- Patch versions (The "Z" in `vX.Y.Z`) will be released when the underlying code has gone through a minor change that does not affect intended functionality or 
  introduce any new features. This includes bugfixes, fixing spelling mistakes, updating documentation, etc.

## `v1.1.0` Minor feature addition, 2022-10-08

- Added `forget()` method to `CacheHelper` class.
- Standardized line length in `README.md`.
- Cleaned up some DocBlocks.

## `v1.0.2` Bugfix, 2022-09-17

- Made `clearClassCache()` and `clearModelFinderCache()` public, not protected.

## `v1.0.1` Bugfix, 2022-09-10

- `ModelFinderShared::searchInModel()` should only return `Model`, not `Collection|Model`.

## `v1.0.0` Initial Release, 2022-09-09

Nothing special to report here, as this is the initial public release of this package. Future changes will be documented in this file.
