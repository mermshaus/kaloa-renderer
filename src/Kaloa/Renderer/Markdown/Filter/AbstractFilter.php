<?php
/**
 * This is a PHP 5.3 port of the PHP Markdown class written by Michel Fortin.
 * PHP Markdown is based on the work of John Gruber. See README and LICENSE
 * files in the root directory of this package for full license info.
 */

namespace Kaloa\Renderer\Markdown\Filter;

/**
 *
 * @author Marc Ermshaus <marc@ermshaus.org>
 */
abstract class AbstractFilter
{
    abstract public function run($text);
}
