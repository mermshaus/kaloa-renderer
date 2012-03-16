<?php

namespace Kaloa\Renderer\Markdown\Filter;

use Kaloa\Renderer\Markdown\Filter\AbstractFilter;
use Kaloa\Renderer\Markdown\Hasher;
use Kaloa\Renderer\Markdown\Parser;

class DoCodeBlocksFilter extends AbstractFilter
{
    /**
     *
     * @var Hasher
     */
    protected $hasher;

    /**
     *
     * @var int
     */
    protected $tab_width;

    /**
     *
     * @var Parser
     */
    protected $parser;

    /**
     *
     * @param Hasher $hasher
     * @param int    $tab_width
     * @param Parser $parser
     */
    public function __construct(Hasher $hasher, $tab_width, Parser $parser)
    {
        $this->hasher    = $hasher;
        $this->tab_width = $tab_width;
        $this->parser    = $parser;
    }

    /**
     * Process Markdown `<pre><code>` blocks.
     *
     * @param  string $text
     * @return string
     */
    public function run($text)
    {
        $text = preg_replace_callback('{
                (?:\n\n|\A\n?)
                (                # $1 = the code block -- one or more lines, starting with a space/tab
                  (?>
                    [ ]{'.$this->tab_width.'}  # Lines must start with a tab or a tab-width of spaces
                    .*\n+
                  )+
                )
                ((?=^[ ]{0,'.$this->tab_width.'}\S)|\Z)    # Lookahead for non-space at line-start, or end of doc
            }xm',
            array($this, '_doCodeBlocks_callback'), $text);

        return $text;
    }

    /**
     *
     * @param  array  $matches
     * @return string
     */
    protected function _doCodeBlocks_callback($matches)
    {
        $codeblock = $matches[1];

        $codeblock = $this->parser->outdent($codeblock);
        $codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);

        // trim leading newlines and trailing newlines
        $codeblock = preg_replace('/\A\n+|\n+\z/', '', $codeblock);

        $codeblock = "<pre><code>$codeblock\n</code></pre>";
        return "\n\n".$this->hasher->hashBlock($codeblock)."\n\n";
    }
}
