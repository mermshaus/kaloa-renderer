# kaloa/renderer

[![Build status](https://img.shields.io/travis/mermshaus/kaloa-renderer/master.svg?style=flat-square)](https://travis-ci.org/mermshaus/kaloa-renderer)

The package as a whole is published under the MIT License. See LICENSE for full
license info.

This is an early release.

Marc Ermshaus <marc@ermshaus.org>


## Usage

~~~ php
use Kaloa\Renderer\Factory;

$cm = Factory::createRenderer('commonmark');
echo $cm->render('**Hello *[World](http://example.org)*!**');
// <p><strong>Hello <em><a href="http://example.org">World</a></em>!</strong></p>

$md = Factory::createRenderer('markdown');
echo $md->render('**Hello *[World](http://example.org)*!**');
// <p><strong>Hello <em><a href="http://example.org">World</a></em>!</strong></p>
~~~


## Renderers

### `inigo`

This is basically a BBCode renderer. The parser tries to automatically add `<p>` elements where appropriate. Therefore, a concept of outline and inline tags is supported.

Available tags:

* `i`, `em`, `b`, `strong`
* `u`, `s`|`strike`
* `icode`
* `h1`-`h6`
* `dl`, `dt`, `dd`
* `ul`, `ol`, `li`
* `table`, `tr`, `th`, `td`
* `quote` (`=@author`)
* `off`|`noparse`
* `var`
* `indent`, `center`
* `url`|`link` (`=@href`, `@title`)
* `img` (`=@src`)
* `abbr` (`=@title`)
* `html`
* `code` (`=@lang`)
* `fn`, `fnt`
* `youtube`

---

* `amazon`


## Testing

~~~ bash
$ ./vendor/bin/phpunit
~~~


## Tools

~~~ bash
$ ./vendor/bin/phpcs --standard=PSR2 ./src
$ ./vendor/bin/phpmd ./src text codesize,design,naming
~~~
