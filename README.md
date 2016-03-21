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


## Testing

~~~ bash
$ ./vendor/bin/phpunit
~~~


## Tools

~~~ bash
$ ./vendor/bin/phpcs --standard=PSR2 ./src
$ ./vendor/bin/phpmd ./src text codesize,design,naming
~~~
