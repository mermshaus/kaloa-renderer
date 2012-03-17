<?php
/**
 * This is a PHP 5.3 port of the PHP Markdown class written by Michel Fortin.
 * PHP Markdown is based on the work of John Gruber. See README and LICENSE
 * files in the root directory of this package for full license info.
 */

namespace Kaloa\Renderer\Markdown\Filter;

use Kaloa\Renderer\Markdown\Filter\AbstractFilter;

use Kaloa\Renderer\Markdown\Hasher;
use Kaloa\Renderer\Markdown\Parser;

/**
 *
 */
class DoHeadersFilter extends AbstractFilter
{
    /**
     *
     * @var Hasher
     */
    protected $hasher;

    /**
     *
     * @var Parser
     */
    protected $parser;

    /**
     *
     * @param Hasher $hasher
     * @param Parser $parser
     */
    public function __construct(Hasher $hasher, Parser $parser)
    {
        $this->hasher = $hasher;
        $this->parser = $parser;
    }

    /**
     *
     * @param  string $text
     * @return string
     */
    public function run($text)
    {
        // Setext-style headers:
        //      Header 1
        //      ========
        //
        //      Header 2
        //      --------
        $text = preg_replace_callback('{ ^(.+?)[ ]*\n(=+|-+)[ ]*\n+ }mx',
            array($this, '_doHeaders_callback_setext'), $text);

        // atx-style headers:
        //    # Header 1
        //    ## Header 2
        //    ## Header 2 with closing hashes ##
        //    ...
        //    ###### Header 6
        $text = preg_replace_callback('{
                ^(\#{1,6})   # $1 = string of #\'s
                [ ]*
                (.+?)        # $2 = Header text
                [ ]*
                \#*          # optional closing #\'s (not counted)
                \n+
            }xm',
            array($this, '_doHeaders_callback_atx'), $text);

        return $text;
    }

    /**
     *
     * @param  array  $matches
     * @return string
     */
    protected function _doHeaders_callback_setext($matches)
    {
        // Terrible hack to check we haven't found an empty list item.
        if ($matches[2] === '-' && preg_match('{^-(?: |$)}', $matches[1]))
            return $matches[0];

        $level = (substr($matches[2], 0, 1) === '=') ? 1 : 2;
        $block = "<h$level>".$this->parser->runSpanGamut($matches[1])."</h$level>";
        return "\n" . $this->hasher->hashBlock($block) . "\n\n";
    }

    /**
     *
     * @param  array  $matches
     * @return string
     */
    protected function _doHeaders_callback_atx($matches)
    {
        $level = strlen($matches[1]);
        $block = "<h$level>".$this->parser->runSpanGamut($matches[2])."</h$level>";
        return "\n" . $this->hasher->hashBlock($block) . "\n\n";
    }
}
