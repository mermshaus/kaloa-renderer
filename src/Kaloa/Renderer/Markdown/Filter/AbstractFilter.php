<?php

namespace Kaloa\Renderer\Markdown\Filter;

/**
 *
 *
 * This is a PHP 5.3 port of the PHP Markdown class written by Michel Fortin.
 * PHP Markdown is based on the work of John Gruber.
 *
 * See Kaloa\Renderer\Markdown\Parser for full license info.
 *
 * @author Marc Ermshaus <marc@ermshaus.org>
 */
abstract class AbstractFilter
{
    abstract public function run($text);
}
