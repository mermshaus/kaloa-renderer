# kaloa/renderer

[![Latest Version](https://img.shields.io/github/release/mermshaus/kaloa-renderer.svg?style=flat-square)](https://github.com/mermshaus/kaloa-renderer/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/mermshaus/kaloa-renderer/blob/master/LICENSE)
[![Build Status](https://img.shields.io/travis/mermshaus/kaloa-renderer/master.svg?style=flat-square)](https://travis-ci.org/mermshaus/kaloa-renderer)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/mermshaus/kaloa-renderer.svg?style=flat-square)](https://scrutinizer-ci.com/g/mermshaus/kaloa-renderer/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/mermshaus/kaloa-renderer.svg?style=flat-square)](https://scrutinizer-ci.com/g/mermshaus/kaloa-renderer)
[![Total Downloads](https://img.shields.io/packagist/dt/mermshaus/kaloa-renderer.svg?style=flat-square)](https://packagist.org/packages/kaloa/renderer)


## Install

Via Composer:

~~~ bash
$ composer require kaloa/renderer
~~~


## Requirements

The following PHP versions are supported:

- PHP 5.4
- PHP 5.5
- PHP 5.6
- PHP 7
- HHVM


## Documentation

### Usage

~~~ php
use Kaloa\Renderer\Factory;

$cm = Factory::createRenderer('commonmark');
echo $cm->render('**Hello *[World](http://example.org)*!**');
// <p><strong>Hello <em><a href="http://example.org">World</a></em>!</strong></p>

$md = Factory::createRenderer('markdown');
echo $md->render('**Hello *[World](http://example.org)*!**');
// <p><strong>Hello <em><a href="http://example.org">World</a></em>!</strong></p>

$bb = Factory::createRenderer('inigo');
echo $bb->render('[i]Hello [s]Moon[/s] [b]Earth[/b]![/i]');
// <p><em>Hello <s>Moon</s> <strong>Earth</strong>!</em></p>

// ...
~~~

### Renderers

#### commonmark (third-party)

The [league/commonmark](https://github.com/thephpleague/commonmark) parser for CommonMark.

#### inigo

This is basically a BBCode renderer. The parser tries to automatically add `<p>` elements where appropriate. Therefore, all tags are classified as inline or outline.

Supported tags:

- `i`|`em`, `b`|`strong`
- `u`, `s`|`strike`
- `icode`
- `h1`-`h6`
- `dl`, `dt`, `dd`
- `ul`, `ol`, `li`
- `table`, `tr`, `th`, `td`
- `quote` (`=@author`)
- `off`|`noparse`
- `var`
- `indent`, `center`
- `url`|`link` (`=@href`, `@title`)
- `img` (`=@src`)
- `abbr` (`=@title`)
- `html`
- `code` (`=@lang`)
- `fn`, `fnt`
- `youtube`

---

- `amazon`

#### markdown (third-party)

The [PHP Markdown parser](https://github.com/michelf/php-markdown) (without extras).

#### xml

todo

#### xmllegacy

todo


## Testing

~~~ bash
$ ./vendor/bin/phpunit
~~~

Further quality assurance:

~~~ bash
$ ./vendor/bin/phpcs --standard=PSR2 ./src
$ ./vendor/bin/phpmd ./src text codesize,design,naming
~~~


## Credits

- [Marc Ermshaus](https://github.com/mermshaus)


## License

The package is published under the MIT License. See [LICENSE](https://github.com/mermshaus/kaloa-renderer/blob/master/LICENSE) for full license info.
