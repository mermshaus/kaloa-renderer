<?php
/**
 * This is a PHP 5.3 port of the PHP Markdown class written by Michel Fortin.
 * PHP Markdown is based on the work of John Gruber. See README and LICENSE
 * files in the root directory of this package for full license info.
 */

namespace Kaloa\Renderer\Markdown\Filter;

use Kaloa\Renderer\Markdown\Filter\AbstractFilter;
use Kaloa\Renderer\Markdown\Hasher;

/**
 *
 */
class DoHorizontalRulesFilter extends AbstractFilter
{
    /**
     *
     * @var Hasher
     */
    protected $hasher;

    /**
     *
     * @var bool
     */
    protected $empty_element_suffix;

    /**
     *
     * @param Hasher $hasher
     */
    public function __construct(Hasher $hasher, $emptyElementSuffix)
    {
        $this->hasher               = $hasher;
        $this->empty_element_suffix = $emptyElementSuffix;
    }

    /**
     *
     * @param  string $text
     * @return string
     */
    public function run($text)
    {
        // Do Horizontal Rules:
        return preg_replace(
            '{
                ^[ ]{0,3}      # Leading space
                ([-*_])        # $1: First marker
                (?>            # Repeated marker group
                    [ ]{0,2}   # Zero, one, or two spaces.
                    \1         # Marker character
                ){2,}          # Group repeated at least twice
                [ ]*           # Tailing spaces
                $              # End of line.
            }mx',
            "\n".$this->hasher->hashBlock("<hr$this->empty_element_suffix")."\n",
            $text);
    }
}
