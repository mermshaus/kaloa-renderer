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

class DoBlockQuotesFilter extends AbstractFilter
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
        $text = preg_replace_callback('/
              (               # Wrap whole match in $1
                (?>
                  ^[ ]*>[ ]?  # ">" at the start of a line
                    .+\n      # rest of the first line
                  (.+\n)*     # subsequent consecutive lines
                  \n*         # blanks
                )+
              )
            /xm',
            array($this, '_doBlockQuotes_callback'), $text);

        return $text;
    }

    /**
     *
     * @param  array  $matches
     * @return string
     */
    protected function _doBlockQuotes_callback($matches)
    {
        $bq = $matches[1];
        // trim one level of quoting - trim whitespace-only lines
        $bq = preg_replace('/^[ ]*>[ ]?|^[ ]+$/m', '', $bq);
        $bq = $this->parser->runBlockGamut($bq);        // recurse

        $bq = preg_replace('/^/m', "  ", $bq);
        // These leading spaces cause problem with <pre> content,
        // so we need to fix that:
        $bq = preg_replace_callback('{(\s*<pre>.+?</pre>)}sx',
            array($this, '_doBlockQuotes_callback2'), $bq);

        return "\n". $this->hasher->hashBlock("<blockquote>\n$bq\n</blockquote>")."\n\n";
    }

    /**
     *
     * @param  array  $matches
     * @return string
     */
    protected function _doBlockQuotes_callback2($matches)
    {
        $pre = $matches[1];
        $pre = preg_replace('/^  /m', '', $pre);
        return $pre;
    }
}
