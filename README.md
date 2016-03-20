# kaloa/renderer

The package as a whole is published under the MIT License. See LICENSE for full
license info.

This is an early release.

Marc Ermshaus <marc@ermshaus.org>


## Usage

~~~ php
$renderer = Kaloa\Renderer\Factory::createRenderer('markdown');
echo $renderer->render('**Hallo [Welt](http://example.org)!**');
// <p><strong>Hallo <a href="http://example.org">Welt</a>!</strong></p>
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
