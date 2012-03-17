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

class DoListsFilter extends AbstractFilter
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
     */
    public function __construct(Hasher $hasher, $tab_width, Parser $parser)
    {
        $this->hasher    = $hasher;
        $this->tab_width = $tab_width;
        $this->parser    = $parser;
    }

    /**
     * Form HTML ordered (numbered) and unordered (bulleted) lists.
     *
     * @param  string $text
     * @return string
     */
    public function run($text)
    {
        $less_than_tab = $this->tab_width - 1;

        // Re-usable patterns to match list item bullets and number markers:
        $marker_ul_re = '[*+-]';
        $marker_ol_re = '\d+[\.]';

        $markers_relist = array(
            $marker_ul_re => $marker_ol_re,
            $marker_ol_re => $marker_ul_re
            );

        foreach ($markers_relist as $marker_re => $other_marker_re) {
            // Re-usable pattern to match any entirel ul or ol list:
            $whole_list_re = '
                (                               # $1 = whole list
                  (                             # $2
                    ([ ]{0,'.$less_than_tab.'}) # $3 = number of spaces
                    ('.$marker_re.')            # $4 = first list item marker
                    [ ]+
                  )
                  (?s:.+?)
                  (                             # $5
                      \z
                    |
                      \n{2,}
                      (?=\S)
                      (?!                       # Negative lookahead for another list item marker
                        [ ]*
                        '.$marker_re.'[ ]+
                      )
                    |
                      (?=                        # Lookahead for another kind of list
                        \n
                        \3                       # Must have the same indentation
                        '.$other_marker_re.'[ ]+
                      )
                  )
                )
            '; // mx

            // We use a different prefix before nested lists than top-level lists.
            // See extended comment in _ProcessListItems().

            if ($this->parser->list_level) {
                $text = preg_replace_callback('{
                        ^
                        '.$whole_list_re.'
                    }mx',
                    array($this, '_doLists_callback'), $text);
            } else {
                $text = preg_replace_callback('{
                        (?:(?<=\n)\n|\A\n?) # Must eat the newline
                        '.$whole_list_re.'
                    }mx',
                    array($this, '_doLists_callback'), $text);
            }
        }

        return $text;
    }

    /**
     *
     * @param  array  $matches
     * @return string
     */
    protected function _doLists_callback($matches)
    {
        // Re-usable patterns to match list item bullets and number markers:
        $marker_ul_re  = '[*+-]';
        $marker_ol_re  = '\d+[\.]';
        $marker_any_re = "(?:$marker_ul_re|$marker_ol_re)";

        $list = $matches[1];
        $list_type = preg_match("/$marker_ul_re/", $matches[4]) ? "ul" : "ol";

        $marker_any_re = ( $list_type == "ul" ? $marker_ul_re : $marker_ol_re );

        $list .= "\n";
        $result = $this->processListItems($list, $marker_any_re);

        $result = $this->hasher->hashBlock("<$list_type>\n" . $result . "</$list_type>");
        return "\n". $result ."\n\n";
    }

    /**
     * Process the contents of a single ordered or unordered list, splitting it
     * into individual list items.
     *
     * @param  string $list_str
     * @param  string $marker_any_re
     * @return string
     */
    protected function processListItems($list_str, $marker_any_re)
    {
        // The $this->list_level global keeps track of when we're inside a list.
        // Each time we enter a list, we increment it; when we leave a list,
        // we decrement. If it's zero, we're not in a list anymore.
        //
        // We do this because when we're not inside a list, we want to treat
        // something like this:
        //
        //        I recommend upgrading to version
        //        8. Oops, now this line is treated
        //        as a sub-list.
        //
        // As a single paragraph, despite the fact that the second line starts
        // with a digit-period-space sequence.
        //
        // Whereas when we're inside a list (or sub-list), that line will be
        // treated as the start of a sub-list. What a kludge, huh? This is
        // an aspect of Markdown's syntax that's hard to parse perfectly
        // without resorting to mind-reading. Perhaps the solution is to
        // change the syntax rules such that sub-lists must start with a
        // starting cardinal number; e.g. "1." or "a.".

        $this->parser->list_level++;

        // trim trailing blank lines:
        $list_str = preg_replace("/\n{2,}\\z/", "\n", $list_str);

        $list_str = preg_replace_callback('{
            (\n)?                  # leading line = $1
            (^[ ]*)                # leading whitespace = $2
            ('.$marker_any_re.'    # list marker and space = $3
                (?:[ ]+|(?=\n))    # space only required if item is not empty
            )
            ((?s:.*?))             # list item text   = $4
            (?:(\n+(?=\n))|\n)     # tailing blank line = $5
            (?= \n* (\z | \2 ('.$marker_any_re.') (?:[ ]+|(?=\n))))
            }xm',
            array($this, '_processListItems_callback'), $list_str);

        $this->parser->list_level--;
        return $list_str;
    }

    /**
     *
     * @param  array  $matches
     * @return string
     */
    protected function _processListItems_callback($matches)
    {
        $item = $matches[4];
        $leading_line =& $matches[1];
        $leading_space =& $matches[2];
        $marker_space = $matches[3];
        $tailing_blank_line =& $matches[5];

        if ($leading_line || $tailing_blank_line ||
            preg_match('/\n{2,}/', $item))
        {
            // Replace marker with the appropriate whitespace indentation
            $item = $leading_space . str_repeat(' ', strlen($marker_space)) . $item;
            $item = $this->parser->runBlockGamut($this->parser->outdent($item)."\n");
        } else {
            // Recursion for sub-lists:
            $item = $this->run($this->parser->outdent($item));
            $item = preg_replace('/\n+$/', '', $item);
            $item = $this->parser->runSpanGamut($item);
        }

        return "<li>" . $item . "</li>\n";
    }
}
