<?php
/**
 * This is a PHP 5.3 port of the PHP Markdown class written by Michel Fortin.
 * PHP Markdown is based on the work of John Gruber. See README and LICENSE
 * files in the root directory of this package for full license info.
 */

namespace Kaloa\Renderer\Markdown\Filter;

use ArrayObject;

/**
 *
 * @author Marc Ermshaus <marc@ermshaus.org>
 */
class StripLinkDefinitionsFilter extends AbstractFilter
{
    protected $urls;
    protected $titles;
    protected $tab_width;

    /**
     *
     * @param ArrayObject $urls
     * @param ArrayObject $titles
     * @param int         $tab_width
     */
    public function __construct(ArrayObject $urls, ArrayObject $titles, $tab_width)
    {
        $this->urls      = $urls;
        $this->titles    = $titles;
        $this->tab_width = $tab_width;
    }

    /**
     * Strips link definitions from text, stores the URLs and titles in
     * hash references.
     *
     * @param  string $text
     * @return string
     */
    public function run($text)
    {
        $less_than_tab = $this->tab_width - 1;

        // Link defs are in the form: ^[id]: url "optional title"
        $text = preg_replace_callback('{
                            ^[ ]{0,'.$less_than_tab.'}\[(.+)\][ ]?:    # id = $1
                              [ ]*
                              \n?         # maybe *one* newline
                              [ ]*
                            (?:
                              <(.+?)>     # url = $2
                            |
                              (\S+?)      # url = $3
                            )
                              [ ]*
                              \n?         # maybe one newline
                              [ ]*
                            (?:
                                (?<=\s)   # lookbehind for whitespace
                                ["(]
                                (.*?)     # title = $4
                                [")]
                                [ ]*
                            )?            # title is optional
                            (?:\n+|\Z)
            }xm',
            array($this, '_stripLinkDefinitions_callback'),
            $text);
        return $text;
    }

    /**
     *
     * @param  array  $matches
     * @return string
     */
    protected function _stripLinkDefinitions_callback($matches)
    {
        $link_id = strtolower($matches[1]);
        $url = ($matches[2] === '') ? $matches[3] : $matches[2];

        $this->urls[$link_id]   = $url;
        $this->titles[$link_id] = (isset($matches[4])) ? $matches[4] : '';

        // String that will replace the block
        return '';
    }
}
