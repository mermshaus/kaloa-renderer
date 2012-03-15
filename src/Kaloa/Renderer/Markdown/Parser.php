<?php

namespace Kaloa\Renderer\Markdown;

use ArrayObject;

use Kaloa\Renderer\Markdown\Hasher;
use Kaloa\Renderer\Markdown\RegexManager;

use Kaloa\Renderer\Markdown\Filter\SetupFilter;
use Kaloa\Renderer\Markdown\Filter\HashHtmlBlocksFilter;
use Kaloa\Renderer\Markdown\Filter\ParseSpanFilter;
use Kaloa\Renderer\Markdown\Filter\StripLinkDefinitionsFilter;

/**
 * Markdown Parser
 *
 * This is a PHP 5.3 port of the PHP Markdown class written by Michel Fortin.
 * PHP Markdown is based on the work of John Gruber.
 *
 * Here's the full license text for PHP Markdown:
 *
 *     PHP Markdown & Extra
 *     Copyright (c) 2004-2012 Michel Fortin
 *     <http://michelf.com/>
 *     All rights reserved.
 *
 *     Based on Markdown
 *     Copyright (c) 2003-2006 John Gruber
 *     <http://daringfireball.net/>
 *     All rights reserved.
 *
 *     Redistribution and use in source and binary forms, with or without
 *     modification, are permitted provided that the following conditions are
 *     met:
 *
 *     * Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 *     * Neither the name "Markdown" nor the names of its contributors may
 *       be used to endorse or promote products derived from this software
 *       without specific prior written permission.
 *
 *     This software is provided by the copyright holders and contributors "as
 *     is" and any express or implied warranties, including, but not limited
 *     to, the implied warranties of merchantability and fitness for a
 *     particular purpose are disclaimed. In no event shall the copyright owner
 *     or contributors be liable for any direct, indirect, incidental, special,
 *     exemplary, or consequential damages (including, but not limited to,
 *     procurement of substitute goods or services; loss of use, data, or
 *     profits; or business interruption) however caused and on any theory of
 *     liability, whether in contract, strict liability, or tort (including
 *     negligence or otherwise) arising in any way out of the use of this
 *     software, even if advised of the possibility of such damage.
 *
 * @author Marc Ermshaus <marc@ermshaus.org>
 */
class Parser
{
    const VERSION       = '1.0.1o';

    /**
     * Change to ">" for HTML output.
     * @var string
     */
    public $empty_element_suffix = ' />';

    /**
     * Define the width of a tab for code blocks.
     * @var int
     */
    public $tab_width = 4;

    /**
     * Change to `true` to disallow markup or entities.
     */
    public $no_markup = false;
    public $no_entities = false;

    /**
     * Predefined urls and titles for reference links and images.
     */
    public $predef_urls = array();
    public $predef_titles = array();

    protected $list_level = 0;


    /**
     * Internal hashes used during transformation.
     * @var type
     */
    protected $urls = array();
    protected $titles = array();


    /**
     * Hasher
     * @var Hasher
     */
    protected $hasher;

    /**
     * RegexManager
     * @var RegexManager
     */
    protected $rem;

    /**
     * Status flag to avoid invalid nesting.
     * @var bool
     */
    protected $in_anchor = false;

    /**
     * These are all the transformations that occur *within* block-level
     * tags like paragraphs, headers, and list items.
     * @var type
     */
    protected $span_gamut = array(
        # Process character escapes, code spans, and inline HTML
        # in one shot.
        #"parseSpan"           => -30,

        # Process anchor and image tags. Images must come first,
        # because ![foo][f] looks like an anchor.
        "doImages"            =>  10,
        "doAnchors"           =>  20,

        # Make links out of things like `<http://example.com/>`
        # Must come after doAnchors, because you can use < and >
        # delimiters in inline links like [this](<url>).
        "doAutoLinks"         =>  30,
        "encodeAmpsAndAngles" =>  40,

        "doItalicsAndBold"    =>  50,
        "doHardBreaks"        =>  60,
        );

    /**
     * These are all the transformations that form block-level tags like
     * paragraphs, headers, and list items.
     *
     * @var type
     */
    protected $block_gamut = array(
        "doHeaders"         => 10,
        "doHorizontalRules" => 20,
        "doLists"           => 40,
        "doCodeBlocks"      => 50,
        "doBlockQuotes"     => 60,
        );

    /**
     * Constructor function. Initialize appropriate member variables.
     */
    public function __construct(Hasher $hasher, RegexManager $rem)
    {
        $this->hasher = $hasher;
        $this->rem    = $rem;

        # Sort document, block, and span gamut in ascendent priority order.
        asort($this->block_gamut);
        asort($this->span_gamut);
    }

    /**
     * Called before the transformation process starts to setup parser states.
     */
    protected function setup()
    {
        # Clear global hashes.
        $this->urls = new ArrayObject($this->predef_urls);
        $this->titles = new ArrayObject($this->predef_titles);
        $this->hasher->clear();

        $this->in_anchor = false;
    }

    /**
     * Called after the transformation process to clear any variable which may
     * be taking up memory unnecessarly.
     */
    protected function teardown()
    {
        $this->urls = array();
        $this->titles = array();
        $this->hasher->clear();
    }

    /**
     * Main function. Performs some preprocessing on the input text and pass it
     * through the document gamut.
     *
     * @param type $text
     * @return type
     */
    public function transform($text)
    {
        $this->setup();

        $sf   = new SetupFilter($this->tab_width);
        $text = $sf->run($text);

        $hf   = new HashHtmlBlocksFilter($this->hasher, $this->tab_width, $this->no_markup);
        $text = $hf->run($text);

        # Strip any lines consisting only of spaces and tabs.
        # This makes subsequent regexen easier to write, because we can
        # match consecutive blank lines with /\n+/ instead of something
        # contorted like /[ ]*\n+/ .
        $text = preg_replace('/^[ ]+$/m', '', $text);



        $f = new StripLinkDefinitionsFilter($this->urls, $this->titles, $this->tab_width);
        $text = $f->run($text);

        $text = $this->runBasicBlockGamut($text);

        $this->teardown();

        return $text . "\n";
    }

    /**
     * Run block gamut tranformations.
     */
    protected function runBlockGamut($text)
    {
        # We need to escape raw HTML in Markdown source before doing anything
        # else. This need to be done for each block, and not only at the
        # begining in the Markdown function since hashed blocks can be part of
        # list items and could have been indented. Indented blocks would have
        # been seen as a code block in a previous pass of hashHTMLBlocks.
        #$text = $this->hashHTMLBlocks($text);

        $hf   = new HashHtmlBlocksFilter($this->hasher, $this->tab_width, $this->no_markup);
        $text = $hf->run($text);

        return $this->runBasicBlockGamut($text);
    }

    /**
     * Run block gamut tranformations, without hashing HTML blocks. This is
     * useful when HTML blocks are known to be already hashed, like in the first
     * whole-document pass.
     *
     * @param type $text
     * @return type
     */
    protected function runBasicBlockGamut($text)
    {
        foreach (array_keys($this->block_gamut) as $method) {
            $text = $this->$method($text);
        }

        # Finally form paragraph and restore hashed blocks.
        $text = $this->formParagraphs($text);

        return $text;
    }

    /**
     *
     * @param type $text
     * @return type
     */
    protected function doHorizontalRules($text)
    {
        # Do Horizontal Rules:
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

    /**
     * Run span gamut tranformations.
     *
     * @param type $text
     * @return type
     */
    protected function runSpanGamut($text)
    {
        $sf = new ParseSpanFilter($this->rem, $this->hasher, $this->no_markup);

        $text = $sf->run($text);

        foreach (array_keys($this->span_gamut) as $method) {
            $text = $this->$method($text);
        }

        return $text;
    }

    /**
     *
     * @param type $text
     * @return type
     */
    protected function doHardBreaks($text)
    {
        # Do hard breaks:
        return preg_replace_callback('/ {2,}\n/',
            array($this, '_doHardBreaks_callback'), $text);
    }

    /**
     *
     * @param type $matches
     * @return type
     */
    protected function _doHardBreaks_callback(/*$matches*/)
    {
        return $this->hasher->hashPart("<br$this->empty_element_suffix\n");
    }

    /**
     * Turn Markdown link shortcuts into XHTML <a> tags.
     *
     * @param type $text
     * @return type
     */
    protected function doAnchors($text)
    {
        if ($this->in_anchor) return $text;
        $this->in_anchor = true;

        #
        # First, handle reference-style links: [link text] [id]
        #
        $text = preg_replace_callback('{
            (                    # wrap whole match in $1
              \[
                ('.$this->rem->getPattern('nested_brackets').')    # link text = $2
              \]

              [ ]?               # one optional space
              (?:\n[ ]*)?        # one optional newline followed by spaces

              \[
                (.*?)            # id = $3
              \]
            )
            }xs',
            array(&$this, '_doAnchors_reference_callback'), $text);

        #
        # Next, inline-style links: [link text](url "optional title")
        #
        $text = preg_replace_callback('{
            (                # wrap whole match in $1
              \[
                ('.$this->rem->getPattern('nested_brackets').')    # link text = $2
              \]
              \(            # literal paren
                [ \n]*
                (?:
                    <(.+?)>    # href = $3
                |
                    ('.$this->rem->getPattern('nested_url_parenthesis').')    # href = $4
                )
                [ \n]*
                (            # $5
                  ([\'"])    # quote char = $6
                  (.*?)      # Title = $7
                  \6         # matching quote
                  [ \n]*     # ignore any spaces/tabs between closing quote and )
                )?           # title is optional
              \)
            )
            }xs',
            array(&$this, '_doAnchors_inline_callback'), $text);

        #
        # Last, handle reference-style shortcuts: [link text]
        # These must come last in case you've also got [link text][1]
        # or [link text](/foo)
        #
        $text = preg_replace_callback('{
            (                    # wrap whole match in $1
              \[
                ([^\[\]]+)       # link text = $2; can\'t contain [ or ]
              \]
            )
            }xs',
            array(&$this, '_doAnchors_reference_callback'), $text);

        $this->in_anchor = false;
        return $text;
    }

    /**
     *
     * @param type $matches
     * @return type
     */
    protected function _doAnchors_reference_callback($matches)
    {
        $whole_match =  $matches[1];
        $link_text   =  $matches[2];
        $link_id     =& $matches[3];

        if ($link_id == "") {
            # for shortcut links like [this][] or [this].
            $link_id = $link_text;
        }

        # lower-case and turn embedded newlines into spaces
        $link_id = strtolower($link_id);
        $link_id = preg_replace('{[ ]?\n}', ' ', $link_id);

        if (isset($this->urls[$link_id])) {
            $url = $this->urls[$link_id];
            $url = $this->encodeAttribute($url);

            $result = "<a href=\"$url\"";
            if ( isset( $this->titles[$link_id] ) ) {
                $title = $this->titles[$link_id];
                $title = $this->encodeAttribute($title);
                $result .=  " title=\"$title\"";
            }

            $link_text = $this->runSpanGamut($link_text);
            $result .= ">$link_text</a>";
            $result = $this->hasher->hashPart($result);
        }
        else {
            $result = $whole_match;
        }
        return $result;
    }

    /**
     *
     * @param type $matches
     * @return type
     */
    protected function _doAnchors_inline_callback($matches)
    {
        #$whole_match    =  $matches[1];
        $link_text        =  $this->runSpanGamut($matches[2]);
        $url            =  $matches[3] == '' ? $matches[4] : $matches[3];
        $title            =& $matches[7];

        $url = $this->encodeAttribute($url);

        $result = "<a href=\"$url\"";
        if (isset($title)) {
            $title = $this->encodeAttribute($title);
            $result .=  " title=\"$title\"";
        }

        $link_text = $this->runSpanGamut($link_text);
        $result .= ">$link_text</a>";

        return $this->hasher->hashPart($result);
    }

    /**
     * Turn Markdown image shortcuts into <img> tags.
     *
     * @param type $text
     * @return type
     */
    protected function doImages($text)
    {
        #
        # First, handle reference-style labeled images: ![alt text][id]
        #
        $text = preg_replace_callback('{
            (                # wrap whole match in $1
              !\[
                ('.$this->rem->getPattern('nested_brackets').')        # alt text = $2
              \]

              [ ]?                # one optional space
              (?:\n[ ]*)?        # one optional newline followed by spaces

              \[
                (.*?)        # id = $3
              \]

            )
            }xs',
            array(&$this, '_doImages_reference_callback'), $text);

        #
        # Next, handle inline images:  ![alt text](url "optional title")
        # Don't forget: encode * and _
        #
        $text = preg_replace_callback('{
            (                # wrap whole match in $1
              !\[
                ('.$this->rem->getPattern('nested_brackets').')        # alt text = $2
              \]
              \s?            # One optional whitespace character
              \(            # literal paren
                [ \n]*
                (?:
                    <(\S*)>    # src url = $3
                |
                    ('.$this->rem->getPattern('nested_url_parenthesis').')    # src url = $4
                )
                [ \n]*
                (            # $5
                  ([\'"])    # quote char = $6
                  (.*?)        # title = $7
                  \6        # matching quote
                  [ \n]*
                )?            # title is optional
              \)
            )
            }xs',
            array(&$this, '_doImages_inline_callback'), $text);

        return $text;
    }

    /**
     *
     * @param type $matches
     * @return type
     */
    protected function _doImages_reference_callback($matches)
    {
        $whole_match = $matches[1];
        $alt_text    = $matches[2];
        $link_id     = strtolower($matches[3]);

        if ($link_id == "") {
            $link_id = strtolower($alt_text); # for shortcut links like ![this][].
        }

        $alt_text = $this->encodeAttribute($alt_text);
        if (isset($this->urls[$link_id])) {
            $url = $this->encodeAttribute($this->urls[$link_id]);
            $result = "<img src=\"$url\" alt=\"$alt_text\"";
            if (isset($this->titles[$link_id])) {
                $title = $this->titles[$link_id];
                $title = $this->encodeAttribute($title);
                $result .=  " title=\"$title\"";
            }
            $result .= $this->empty_element_suffix;
            $result = $this->hasher->hashPart($result);
        }
        else {
            # If there's no such link ID, leave intact:
            $result = $whole_match;
        }

        return $result;
    }

    /**
     *
     * @param type $matches
     * @return type
     */
    protected function _doImages_inline_callback($matches)
    {
        #$whole_match    = $matches[1];
        $alt_text        = $matches[2];
        $url            = $matches[3] == '' ? $matches[4] : $matches[3];
        $title            =& $matches[7];

        $alt_text = $this->encodeAttribute($alt_text);
        $url = $this->encodeAttribute($url);
        $result = "<img src=\"$url\" alt=\"$alt_text\"";
        if (isset($title)) {
            $title = $this->encodeAttribute($title);
            $result .=  " title=\"$title\""; # $title already quoted
        }
        $result .= $this->empty_element_suffix;

        return $this->hasher->hashPart($result);
    }

    /**
     *
     * @param type $text
     * @return type
     */
    protected function doHeaders($text)
    {
        # Setext-style headers:
        #      Header 1
        #      ========
        #
        #      Header 2
        #      --------
        #
        $text = preg_replace_callback('{ ^(.+?)[ ]*\n(=+|-+)[ ]*\n+ }mx',
            array(&$this, '_doHeaders_callback_setext'), $text);

        # atx-style headers:
        #    # Header 1
        #    ## Header 2
        #    ## Header 2 with closing hashes ##
        #    ...
        #    ###### Header 6
        #
        $text = preg_replace_callback('{
                ^(\#{1,6})    # $1 = string of #\'s
                [ ]*
                (.+?)        # $2 = Header text
                [ ]*
                \#*            # optional closing #\'s (not counted)
                \n+
            }xm',
            array(&$this, '_doHeaders_callback_atx'), $text);

        return $text;
    }

    /**
     *
     * @param type $matches
     * @return type
     */
    protected function _doHeaders_callback_setext($matches)
    {
        # Terrible hack to check we haven't found an empty list item.
        if ($matches[2] == '-' && preg_match('{^-(?: |$)}', $matches[1]))
            return $matches[0];

        $level = $matches[2]{0} == '=' ? 1 : 2;
        $block = "<h$level>".$this->runSpanGamut($matches[1])."</h$level>";
        return "\n" . $this->hasher->hashBlock($block) . "\n\n";
    }

    /**
     *
     * @param type $matches
     * @return type
     */
    protected function _doHeaders_callback_atx($matches)
    {
        $level = strlen($matches[1]);
        $block = "<h$level>".$this->runSpanGamut($matches[2])."</h$level>";
        return "\n" . $this->hasher->hashBlock($block) . "\n\n";
    }

    /**
     * Form HTML ordered (numbered) and unordered (bulleted) lists.
     *
     * @param type $text
     * @return type
     */
    protected function doLists($text)
    {
        $less_than_tab = $this->tab_width - 1;

        # Re-usable patterns to match list item bullets and number markers:
        $marker_ul_re  = '[*+-]';
        $marker_ol_re  = '\d+[\.]';
        #$marker_any_re = "(?:$marker_ul_re|$marker_ol_re)";

        $markers_relist = array(
            $marker_ul_re => $marker_ol_re,
            $marker_ol_re => $marker_ul_re,
            );

        foreach ($markers_relist as $marker_re => $other_marker_re) {
            # Re-usable pattern to match any entirel ul or ol list:
            $whole_list_re = '
                (                                # $1 = whole list
                  (                                # $2
                    ([ ]{0,'.$less_than_tab.'})    # $3 = number of spaces
                    ('.$marker_re.')            # $4 = first list item marker
                    [ ]+
                  )
                  (?s:.+?)
                  (                                # $5
                      \z
                    |
                      \n{2,}
                      (?=\S)
                      (?!                        # Negative lookahead for another list item marker
                        [ ]*
                        '.$marker_re.'[ ]+
                      )
                    |
                      (?=                        # Lookahead for another kind of list
                        \n
                        \3                        # Must have the same indentation
                        '.$other_marker_re.'[ ]+
                      )
                  )
                )
            '; // mx

            # We use a different prefix before nested lists than top-level lists.
            # See extended comment in _ProcessListItems().

            if ($this->list_level) {
                $text = preg_replace_callback('{
                        ^
                        '.$whole_list_re.'
                    }mx',
                    array(&$this, '_doLists_callback'), $text);
            }
            else {
                $text = preg_replace_callback('{
                        (?:(?<=\n)\n|\A\n?) # Must eat the newline
                        '.$whole_list_re.'
                    }mx',
                    array(&$this, '_doLists_callback'), $text);
            }
        }

        return $text;
    }

    /**
     *
     * @param type $matches
     * @return type
     */
    protected function _doLists_callback($matches)
    {
        # Re-usable patterns to match list item bullets and number markers:
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
     *
     * @param type $list_str
     * @param type $marker_any_re
     * @return type
     */
    protected function processListItems($list_str, $marker_any_re)
    {
        # The $this->list_level global keeps track of when we're inside a list.
        # Each time we enter a list, we increment it; when we leave a list,
        # we decrement. If it's zero, we're not in a list anymore.
        #
        # We do this because when we're not inside a list, we want to treat
        # something like this:
        #
        #        I recommend upgrading to version
        #        8. Oops, now this line is treated
        #        as a sub-list.
        #
        # As a single paragraph, despite the fact that the second line starts
        # with a digit-period-space sequence.
        #
        # Whereas when we're inside a list (or sub-list), that line will be
        # treated as the start of a sub-list. What a kludge, huh? This is
        # an aspect of Markdown's syntax that's hard to parse perfectly
        # without resorting to mind-reading. Perhaps the solution is to
        # change the syntax rules such that sub-lists must start with a
        # starting cardinal number; e.g. "1." or "a.".

        $this->list_level++;

        # trim trailing blank lines:
        $list_str = preg_replace("/\n{2,}\\z/", "\n", $list_str);

        $list_str = preg_replace_callback('{
            (\n)?                            # leading line = $1
            (^[ ]*)                            # leading whitespace = $2
            ('.$marker_any_re.'                # list marker and space = $3
                (?:[ ]+|(?=\n))    # space only required if item is not empty
            )
            ((?s:.*?))                        # list item text   = $4
            (?:(\n+(?=\n))|\n)                # tailing blank line = $5
            (?= \n* (\z | \2 ('.$marker_any_re.') (?:[ ]+|(?=\n))))
            }xm',
            array(&$this, '_processListItems_callback'), $list_str);

        $this->list_level--;
        return $list_str;
    }

    /**
     *
     * @param type $matches
     * @return type
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
            # Replace marker with the appropriate whitespace indentation
            $item = $leading_space . str_repeat(' ', strlen($marker_space)) . $item;
            $item = $this->runBlockGamut($this->outdent($item)."\n");
        }
        else {
            # Recursion for sub-lists:
            $item = $this->doLists($this->outdent($item));
            $item = preg_replace('/\n+$/', '', $item);
            $item = $this->runSpanGamut($item);
        }

        return "<li>" . $item . "</li>\n";
    }

    /**
     * Process Markdown `<pre><code>` blocks.
     *
     * @param type $text
     * @return type
     */
    protected function doCodeBlocks($text)
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
            array(&$this, '_doCodeBlocks_callback'), $text);

        return $text;
    }

    /**
     *
     * @param type $matches
     * @return type
     */
    protected function _doCodeBlocks_callback($matches)
    {
        $codeblock = $matches[1];

        $codeblock = $this->outdent($codeblock);
        $codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);

        # trim leading newlines and trailing newlines
        $codeblock = preg_replace('/\A\n+|\n+\z/', '', $codeblock);

        $codeblock = "<pre><code>$codeblock\n</code></pre>";
        return "\n\n".$this->hasher->hashBlock($codeblock)."\n\n";
    }

    /**
     *
     * @param type $text
     * @return type
     */
    protected function doItalicsAndBold($text)
    {
        $token_stack = array('');
        $text_stack = array('');
        $em = '';
        $strong = '';
        $tree_char_em = false;

        $xyztodo = $this->rem->getPattern('em_strong_prepared');

        while (1) {
            #
            # Get prepared regular expression for seraching emphasis tokens
            # in current context.
            #
            $token_re = $xyztodo["$em$strong"];

            #
            # Each loop iteration search for the next emphasis token.
            # Each token is then passed to handleSpanToken.
            #
            $parts = preg_split($token_re, $text, 2, PREG_SPLIT_DELIM_CAPTURE);
            $text_stack[0] .= $parts[0];
            $token =& $parts[1];
            $text =& $parts[2];

            if (empty($token)) {
                # Reached end of text span: empty stack without emitting.
                # any more emphasis.
                while ($token_stack[0]) {
                    $text_stack[1] .= array_shift($token_stack);
                    $text_stack[0] .= array_shift($text_stack);
                }
                break;
            }

            $token_len = strlen($token);
            if ($tree_char_em) {
                # Reached closing marker while inside a three-char emphasis.
                if ($token_len == 3) {
                    # Three-char closing marker, close em and strong.
                    array_shift($token_stack);
                    $span = array_shift($text_stack);
                    $span = $this->runSpanGamut($span);
                    $span = "<strong><em>$span</em></strong>";
                    $text_stack[0] .= $this->hasher->hashPart($span);
                    $em = '';
                    $strong = '';
                } else {
                    # Other closing marker: close one em or strong and
                    # change current token state to match the other
                    $token_stack[0] = str_repeat($token{0}, 3-$token_len);
                    $tag = $token_len == 2 ? "strong" : "em";
                    $span = $text_stack[0];
                    $span = $this->runSpanGamut($span);
                    $span = "<$tag>$span</$tag>";
                    $text_stack[0] = $this->hasher->hashPart($span);
                    $$tag = ''; # $$tag stands for $em or $strong
                }
                $tree_char_em = false;
            } else if ($token_len == 3) {
                if ($em) {
                    # Reached closing marker for both em and strong.
                    # Closing strong marker:
                    for ($i = 0; $i < 2; ++$i) {
                        $shifted_token = array_shift($token_stack);
                        $tag = strlen($shifted_token) == 2 ? "strong" : "em";
                        $span = array_shift($text_stack);
                        $span = $this->runSpanGamut($span);
                        $span = "<$tag>$span</$tag>";
                        $text_stack[0] .= $this->hasher->hashPart($span);
                        $$tag = ''; # $$tag stands for $em or $strong
                    }
                } else {
                    # Reached opening three-char emphasis marker. Push on token
                    # stack; will be handled by the special condition above.
                    $em = $token{0};
                    $strong = "$em$em";
                    array_unshift($token_stack, $token);
                    array_unshift($text_stack, '');
                    $tree_char_em = true;
                }
            } else if ($token_len == 2) {
                if ($strong) {
                    # Unwind any dangling emphasis marker:
                    if (strlen($token_stack[0]) == 1) {
                        $text_stack[1] .= array_shift($token_stack);
                        $text_stack[0] .= array_shift($text_stack);
                    }
                    # Closing strong marker:
                    array_shift($token_stack);
                    $span = array_shift($text_stack);
                    $span = $this->runSpanGamut($span);
                    $span = "<strong>$span</strong>";
                    $text_stack[0] .= $this->hasher->hashPart($span);
                    $strong = '';
                } else {
                    array_unshift($token_stack, $token);
                    array_unshift($text_stack, '');
                    $strong = $token;
                }
            } else {
                # Here $token_len == 1
                if ($em) {
                    if (strlen($token_stack[0]) == 1) {
                        # Closing emphasis marker:
                        array_shift($token_stack);
                        $span = array_shift($text_stack);
                        $span = $this->runSpanGamut($span);
                        $span = "<em>$span</em>";
                        $text_stack[0] .= $this->hasher->hashPart($span);
                        $em = '';
                    } else {
                        $text_stack[0] .= $token;
                    }
                } else {
                    array_unshift($token_stack, $token);
                    array_unshift($text_stack, '');
                    $em = $token;
                }
            }
        }
        return $text_stack[0];
    }

    /**
     *
     * @param type $text
     * @return type
     */
    protected function doBlockQuotes($text)
    {
        $text = preg_replace_callback('/
              (                                # Wrap whole match in $1
                (?>
                  ^[ ]*>[ ]?            # ">" at the start of a line
                    .+\n                    # rest of the first line
                  (.+\n)*                    # subsequent consecutive lines
                  \n*                        # blanks
                )+
              )
            /xm',
            array(&$this, '_doBlockQuotes_callback'), $text);

        return $text;
    }

    /**
     *
     * @param type $matches
     * @return type
     */
    protected function _doBlockQuotes_callback($matches)
    {
        $bq = $matches[1];
        # trim one level of quoting - trim whitespace-only lines
        $bq = preg_replace('/^[ ]*>[ ]?|^[ ]+$/m', '', $bq);
        $bq = $this->runBlockGamut($bq);        # recurse

        $bq = preg_replace('/^/m', "  ", $bq);
        # These leading spaces cause problem with <pre> content,
        # so we need to fix that:
        $bq = preg_replace_callback('{(\s*<pre>.+?</pre>)}sx',
            array(&$this, '_doBlockQuotes_callback2'), $bq);

        return "\n". $this->hasher->hashBlock("<blockquote>\n$bq\n</blockquote>")."\n\n";
    }

    /**
     *
     * @param type $matches
     * @return type
     */
    protected function _doBlockQuotes_callback2($matches)
    {
        $pre = $matches[1];
        $pre = preg_replace('/^  /m', '', $pre);
        return $pre;
    }

    /**
     *
     * @param string $text string to process with html <p> tags
     * @return type
     */
    protected function formParagraphs($text)
    {
        # Strip leading and trailing lines:
        $text = preg_replace('/\A\n+|\n+\z/', '', $text);

        $grafs = preg_split('/\n{2,}/', $text, -1, PREG_SPLIT_NO_EMPTY);

        #
        # Wrap <p> tags and unhashify HTML blocks
        #
        foreach ($grafs as $key => $value) {
            if (!preg_match('/^B\x1A[0-9]+B$/', $value)) {
                # Is a paragraph.
                $value = $this->runSpanGamut($value);
                $value = preg_replace('/^([ ]*)/', "<p>", $value);
                $value .= "</p>";
                $grafs[$key] = $this->hasher->unhash($value);
            }
            else {
                # Is a block.
                # Modify elements of @grafs in-place...
                $graf = $value;
                $block = $this->hasher->getHashByKey($graf);
                $graf = $block;
//                if (preg_match('{
//                    \A
//                    (                            # $1 = <div> tag
//                      <div  \s+
//                      [^>]*
//                      \b
//                      markdown\s*=\s*  ([\'"])    #    $2 = attr quote char
//                      1
//                      \2
//                      [^>]*
//                      >
//                    )
//                    (                            # $3 = contents
//                    .*
//                    )
//                    (</div>)                    # $4 = closing tag
//                    \z
//                    }xs', $block, $matches))
//                {
//                    list(, $div_open, , $div_content, $div_close) = $matches;
//
//                    # We can't call Markdown(), because that resets the hash;
//                    # that initialization code should be pulled into its own sub, though.
//                    $div_content = $this->hashHTMLBlocks($div_content);
//
//                    # Run document gamut methods on the content.
//                    foreach ($this->document_gamut as $method => $priority) {
//                        $div_content = $this->$method($div_content);
//                    }
//
//                    $div_open = preg_replace(
//                        '{\smarkdown\s*=\s*([\'"]).+?\1}', '', $div_open);
//
//                    $graf = $div_open . "\n" . $div_content . "\n" . $div_close;
//                }
                $grafs[$key] = $graf;
            }
        }

        return implode("\n\n", $grafs);
    }


    /**
     * Encode text for a double-quoted HTML attribute. This function
     * is *not* suitable for attributes enclosed in single quotes.
     *
     * @param type $text
     * @return type
     */
    protected function encodeAttribute($text)
    {
        $text = $this->encodeAmpsAndAngles($text);
        $text = str_replace('"', '&quot;', $text);
        return $text;
    }

    /**
     * Smart processing for ampersands and angle brackets that need to
     * be encoded. Valid character entities are left alone unless the
     * no-entities mode is set.
     */
    protected function encodeAmpsAndAngles($text)
    {
        if ($this->no_entities) {
            $text = str_replace('&', '&amp;', $text);
        } else {
            # Ampersand-encoding based entirely on Nat Irons's Amputator
            # MT plugin: <http://bumppo.net/projects/amputator/>
            $text = preg_replace('/&(?!#?[xX]?(?:[0-9a-fA-F]+|\w+);)/',
                                '&amp;', $text);;
        }
        # Encode remaining <'s
        $text = str_replace('<', '&lt;', $text);

        return $text;
    }

    /**
     *
     * @param type $text
     * @return type
     */
    protected function doAutoLinks($text)
    {
        $text = preg_replace_callback('{<((https?|ftp|dict):[^\'">\s]+)>}i',
            array(&$this, '_doAutoLinks_url_callback'), $text);

        # Email addresses: <address@domain.foo>
        $text = preg_replace_callback('{
            <
            (?:mailto:)?
            (
                (?:
                    [-!#$%&\'*+/=?^_`.{|}~\w\x80-\xFF]+
                |
                    ".*?"
                )
                \@
                (?:
                    [-a-z0-9\x80-\xFF]+(\.[-a-z0-9\x80-\xFF]+)*\.[a-z]+
                |
                    \[[\d.a-fA-F:]+\]    # IPv4 & IPv6
                )
            )
            >
            }xi',
            array(&$this, '_doAutoLinks_email_callback'), $text);

        return $text;
    }

    /**
     *
     * @param type $matches
     * @return type
     */
    protected function _doAutoLinks_url_callback($matches)
    {
        $url = $this->encodeAttribute($matches[1]);
        $link = "<a href=\"$url\">$url</a>";
        return $this->hasher->hashPart($link);
    }

    /**
     *
     * @param type $matches
     * @return type
     */
    protected function _doAutoLinks_email_callback($matches)
    {
        $address = $matches[1];
        $link = $this->encodeEmailAddress($address);
        return $this->hasher->hashPart($link);
    }

    /**
     * Input: an email address, e.g. "foo@example.com"
     *
     * Output: the email address as a mailto link, with each character of the
     * address encoded as either a decimal or hex entity, in the hopes of
     * foiling most address harvesting spam bots. E.g.:
     *
     *    <p><a href="&#109;&#x61;&#105;&#x6c;&#116;&#x6f;&#58;&#x66;o&#111;
     *        &#x40;&#101;&#x78;&#97;&#x6d;&#112;&#x6c;&#101;&#46;&#x63;&#111;
     *        &#x6d;">&#x66;o&#111;&#x40;&#101;&#x78;&#97;&#x6d;&#112;&#x6c;
     *        &#101;&#46;&#x63;&#111;&#x6d;</a></p>
     *
     * Based by a filter by Matthew Wickline, posted to BBEdit-Talk. With some
     * optimizations by Milian Wolff.
     *
     * @param type $addr
     * @return type
     */
    protected function encodeEmailAddress($addr)
    {
        $addr = "mailto:" . $addr;
        $chars = preg_split('/(?<!^)(?!$)/', $addr);
        $seed = (int)abs(crc32($addr) / strlen($addr)); # Deterministic seed.

        foreach ($chars as $key => $char) {
            $ord = ord($char);
            # Ignore non-ascii chars.
            if ($ord < 128) {
                $r = ($seed * (1 + $key)) % 100; # Pseudo-random function.
                # roughly 10% raw, 45% hex, 45% dec
                # '@' *must* be encoded. I insist.
                if ($r > 90 && $char != '@') /* do nothing */;
                else if ($r < 45) $chars[$key] = '&#x'.dechex($ord).';';
                else              $chars[$key] = '&#'.$ord.';';
            }
        }

        $addr = implode('', $chars);
        $text = implode('', array_slice($chars, 7)); # text without `mailto:`
        $addr = "<a href=\"$addr\">$text</a>";

        return $addr;
    }

    /**
     * Remove one level of line-leading tabs or spaces
     *
     * @param string $text
     * @return string
     */
    protected function outdent($text)
    {
        return preg_replace('/^(\t|[ ]{1,'.$this->tab_width.'})/m', '', $text);
    }
}
