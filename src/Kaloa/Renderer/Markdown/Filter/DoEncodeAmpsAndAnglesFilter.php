<?php
/**
 * This is a PHP 5.3 port of the PHP Markdown class written by Michel Fortin.
 * PHP Markdown is based on the work of John Gruber. See README and LICENSE
 * files in the root directory of this package for full license info.
 */

namespace Kaloa\Renderer\Markdown\Filter;

use Kaloa\Renderer\Markdown\Filter\AbstractFilter;
use Kaloa\Renderer\Markdown\Encoder;

/**
 *
 */
class DoEncodeAmpsAndAnglesFilter extends AbstractFilter
{
    /**
     *
     * @var Encoder
     */
    protected $encoder;

    /**
     *
     * @param Encoder $encoder
     */
    public function __construct(Encoder $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     *
     * @param  string $text
     * @return string
     */
    public function run($text)
    {
        return $this->encoder->encodeAmpsAndAngles($text);
    }
}
