# Change Log

All notable changes to this project will be documented in this file. See [standard-version](https://github.com/conventional-changelog/standard-version) for commit guidelines.

<a name="3.3.1"></a>

## [3.3.1](https://github.com/imgix/imgix-php/compare/3.3.0...3.3.1) (2021-03-24)
* docs: update fixed-widths section  ([#77](https://github.com/imgix/imgix-php/pull/77))
* fix: dpr srcset when only h param  ([#76](https://github.com/imgix/imgix-php/pull/76))
* docs: update travis badge to travis-ci.com  ([#74](https://github.com/imgix/imgix-php/pull/74))
* docs: reorder install instruction to promote usage of composer ([#72](https://github.com/imgix/imgix-php/pull/72))

<a name="3.3.0"></a>

## [3.3.0](https://github.com/imgix/imgix-php/compare/3.2.0...3.3.0) (2020-06-05)
* fix: normalize behavior of target widths ([#56](https://github.com/imgix/imgix-php/pull/56))
* fix: remove ensure even requirement ([#57](https://github.com/imgix/imgix-php/pull/57))
* feat: create custom srcsets ([#58](https://github.com/imgix/imgix-php/pull/58))
* feat: validate custom srcsets ([#61](https://github.com/imgix/imgix-php/pull/61))

<a name="3.2.0"></a>

## [3.2.0](https://github.com/imgix/imgix-php/compare/3.1.0...3.2.0) (2020-03-31)

* feat: use https by default ([#53](https://github.com/imgix/imgix-php/pull/53))

<a name="3.1.0"></a>

## [3.1.0](https://github.com/imgix/imgix-php/compare/3.0.0...3.1.0) (2019-08-29)

* feat: add srcset generation ([#47](https://github.com/imgix/imgix-php/pull/47))

<a name="3.0.0"></a>

## [3.0.0](https://github.com/imgix/imgix-php/compare/2.3.0...3.0.0) (2019-06-11)

* fix: remove deprecated domain sharding functionality ([#45](https://github.com/imgix/imgix-php/pull/45))

<a name="2.3.0"></a>

## [2.3.0](https://github.com/imgix/imgix-php/compare/2.2.0...2.3.0) (2019-05-06)

* deprecate domain sharding ([#42](https://github.com/imgix/imgix-php/pull/42)) ([#43](https://github.com/imgix/imgix-php/pull/43))

<a name="2.2.0"></a>

## [2.2.0](https://github.com/imgix/imgix-php/compare/2.1.1...2.2.0) (2019-04-08)

### Features

* add support for multiple URL parameter values by flattening nested arrays passed into $params ([#40](https://github.com/imgix/imgix-php/pull/40))

### Bug Fixes

* replace deprecated phpunit annotations with exception methods ([#39](https://github.com/imgix/imgix-php/pull/39))
* add domain validation at UrlBuilder initialization ([#41](https://github.com/imgix/imgix-php/pull/41)), fixes [#10](https://github.com/imgix/imgix-php/issues/10)
