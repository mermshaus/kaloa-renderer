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
 * Turn block-level HTML blocks into hash entries.
 *
 * @author Marc Ermshaus <marc@ermshaus.org>
 */
class HashHtmlBlocksFilter extends AbstractFilter
{
    protected $hasher;
    protected $tab_width;
    protected $no_markup;

    public function __construct(Hasher $hasher, $tabWidth, $noMarkup)
    {
        $this->hasher = $hasher;
        $this->tab_width = $tabWidth;
        $this->no_markup = $noMarkup;
    }

    /**
     *
     * @param  string $text
     * @return string
     */
    public function run($text)
    {
        if ($this->no_markup) return $text;

        $less_than_tab = $this->tab_width - 1;

        /**
         * Hashify HTML blocks:
         * We only want to do this for block-level HTML tags, such as headers,
         * lists, and tables. That's because we still want to wrap <p>s around
         * "paragraphs" that are wrapped in non-block-level tags, such as
         * anchors, phrase emphasis, and spans. The list of tags we're looking
         * for is hard-coded:
         *
         * -  List "a" is made of tags which can be both inline or block-level.
         *    These will be treated block-level when the start tag is alone on
         *    its line, otherwise they're not matched here and will be taken as
         *    inline later.
         * -  List "b" is made of tags which are always block-level;
         */
        $block_tags_a_re = 'ins|del';
        $block_tags_b_re = 'p|div|h[1-6]|blockquote|pre|table|dl|ol|ul|address|'.
                           'script|noscript|form|fieldset|iframe|math';

        // Regular expression for the content of a block tag.
        $nested_tags_level = 4;
        $attr = '
            (?>                # optional tag attributes
              \s            # starts with whitespace
              (?>
                [^>"/]+        # text outside quotes
              |
                /+(?!>)        # slash not followed by ">"
              |
                "[^"]*"        # text inside double quotes (tolerate ">")
              |
                \'[^\']*\'    # text inside single quotes (tolerate ">")
              )*
            )?
            ';
        $content =
            str_repeat('
                (?>
                  [^<]+            # content without tag
                |
                  <\2            # nested opening tag
                    '.$attr.'    # attributes
                    (?>
                      />
                    |
                      >', $nested_tags_level).  // end of opening tag
                      '.*?'.                    // last level nested tag content
            str_repeat('
                      </\2\s*>    # closing nested tag
                    )
                  |
                    <(?!/\2\s*>    # other tags with a different name
                  )
                )*',
                $nested_tags_level);
        $content2 = str_replace('\2', '\3', $content);

        // First, look for nested blocks, e.g.:
        //     <div>
        //         <div>
        //         tags for inner block must be indented.
        //         </div>
        //    </div>
        //
        // The outermost tags must start at the left margin for this to match, and
        // the inner nested divs must be indented.
        // We need to do this before the next, more liberal match, because the next
        // match will start at the first `<div>` and stop at the first `</div>`.
        $text = preg_replace_callback('{(?>
            (?>
                (?<=\n\n)        # Starting after a blank line
                |                # or
                \A\n?            # the beginning of the doc
            )
            (                    # save in $1

              # Match from `\n<tag>` to `</tag>\n`, handling nested tags
              # in between.

                        [ ]{0,'.$less_than_tab.'}
                        <('.$block_tags_b_re.')# start tag = $2
                        '.$attr.'>     # attributes followed by > and \n
                        '.$content.'   # content, support nesting
                        </\2>          # the matching end tag
                        [ ]*           # trailing spaces/tabs
                        (?=\n+|\Z)     # followed by a newline or end of document

            | # Special version for tags of group a.

                        [ ]{0,'.$less_than_tab.'}
                        <('.$block_tags_a_re.') # start tag = $3
                        '.$attr.'>[ ]*\n        # attributes followed by >
                        '.$content2.'           # content, support nesting
                        </\3>                   # the matching end tag
                        [ ]*                    # trailing spaces/tabs
                        (?=\n+|\Z)              # followed by a newline or end of document

            | # Special case just for <hr />. It was easier to make a special
              # case than to make the other regex more complicated.

                        [ ]{0,'.$less_than_tab.'}
                        <(hr)                # start tag = $2
                        '.$attr.'            # attributes
                        /?>                  # the matching end tag
                        [ ]*
                        (?=\n{2,}|\Z)        # followed by a blank line or end of document

            | # Special case for standalone HTML comments:

                    [ ]{0,'.$less_than_tab.'}
                    (?s:
                        <!-- .*? -->
                    )
                    [ ]*
                    (?=\n{2,}|\Z)        # followed by a blank line or end of document

            | # PHP and ASP-style processor instructions (<? and <%)

                    [ ]{0,'.$less_than_tab.'}
                    (?s:
                        <([?%])            # $2
                        .*?
                        \2>
                    )
                    [ ]*
                    (?=\n{2,}|\Z)        # followed by a blank line or end of document

            )
            )}Sxmi',
            array($this, '_hashHTMLBlocks_callback'),
            $text);

        return $text;
    }

    /**
     *
     * @param  array  $matches
     * @return string
     */
    protected function _hashHTMLBlocks_callback($matches)
    {
        $text = $matches[1];
        $key  = $this->hasher->hashBlock($text);
        return "\n\n$key\n\n";
    }
}
