# Kaloa Component Library for PHP -- Renderer

The package as a whole is published under the MIT License. See LICENSE for full
license info.

This is an early release.

Marc Ermshaus <marc@ermshaus.org>


## Unit tests (run from project root directory):

~~~ bash
$ phpunit .
~~~

Other tools:

~~~ bash
$ phpmd ./src text codesize,design,naming
~~~

Parts of this package contain and/or expand on work of the following people:

- Michel Fortin <http://michelf.com/>

  The Markdown renderer of the package is a partial rewrite of PHP Markdown
  <http://michelf.com/projects/php-markdown/> (see LICENSE for full license
  info).

  Most of the examples in the Markdown test suite are taken from MDTest
  <http://git.michelf.com/mdtest/> (relicensed by permission).

- John Gruber <http://daringfireball.net/>

  PHP Markdown is based on the original Markdown distribution
  <http://daringfireball.net/projects/markdown/>.
