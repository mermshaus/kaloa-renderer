# kaloa/renderer

## Install

Via Composer:

~~~ bash
$ composer require kaloa/renderer
~~~


## Requirements

- PHP >= 8.5


## Demo

~~~ bash
$ php -S localhost:9090 -t demos/renderer
~~~


## Documentation

### Usage

~~~ php
use Kaloa\Renderer\Factory;

$cm = Factory::createRenderer('commonmark');
echo $cm->render('**Hello *[World](http://example.org)*!**');
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
$ ./vendor/bin/phpmd ./src text codesize,design,naming
~~~


## Credits

- [Marc Ermshaus](https://github.com/mermshaus)


## License

The package is published under the MIT License. See [LICENSE](https://github.com/mermshaus/kaloa-renderer/blob/master/LICENSE) for full license info.
