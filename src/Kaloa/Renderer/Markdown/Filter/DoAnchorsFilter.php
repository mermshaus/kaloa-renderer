<?php
/**
 * This is a PHP 5.3 port of the PHP Markdown class written by Michel Fortin.
 * PHP Markdown is based on the work of John Gruber. See README and LICENSE
 * files in the root directory of this package for full license info.
 */

namespace Kaloa\Renderer\Markdown\Filter;

use Kaloa\Renderer\Markdown\Filter\AbstractFilter;
use Kaloa\Renderer\Markdown\Encoder;
use Kaloa\Renderer\Markdown\Hasher;
use Kaloa\Renderer\Markdown\RegexManager;
use Kaloa\Renderer\Markdown\Parser;
use ArrayObject;

/**
 *
 */
class DoAnchorsFilter extends AbstractFilter
{
    /**
     *
     * @var Encoder
     */
    protected $encoder;

    /**
     *
     * @var RegexManager
     */
    protected $rem;

    /**
     *
     * @var Hasher
     */
    protected $hasher;

    /**
     *
     * @var ArrayObject
     */
    protected $urls;

    /**
     *
     * @var ArrayObject
     */
    protected $titles;

    /**
     *
     * @var Parser
     */
    protected $parser;

    /**
     *
     * @param Encoder      $encoder
     * @param RegexManager $rem
     * @param Hasher       $hasher
     * @param ArrayObject  $urls
     * @param ArrayObject  $titles
     * @param Parser       $parser
     */
    public function __construct(Encoder $encoder, RegexManager $rem,
            Hasher $hasher, ArrayObject $urls, ArrayObject $titles,
            Parser $parser)
    {
        $this->encoder = $encoder;
        $this->rem     = $rem;
        $this->hasher  = $hasher;
        $this->urls    = $urls;
        $this->titles  = $titles;
        $this->parser  = $parser;
    }

    /**
     * Turn Markdown link shortcuts into XHTML <a> tags.
     *
     * @param  string $text
     * @return string
     */
    public function run($text)
    {
        if ($this->parser->in_anchor) return $text;
        $this->parser->in_anchor = true;

        // First, handle reference-style links: [link text] [id]
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
            array($this, '_doAnchors_reference_callback'), $text);

        // Next, inline-style links: [link text](url "optional title")
        $text = preg_replace_callback('{
            (                # wrap whole match in $1
              \[
                ('.$this->rem->getPattern('nested_brackets').')    # link text = $2
              \]
              \(             # literal paren
                [ \n]*
                (?:
                    <(.+?)>  # href = $3
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
            array($this, '_doAnchors_inline_callback'), $text);

        // Last, handle reference-style shortcuts: [link text]
        // These must come last in case you've also got [link text][1]
        // or [link text](/foo)
        $text = preg_replace_callback('{
            (                    # wrap whole match in $1
              \[
                ([^\[\]]+)       # link text = $2; can\'t contain [ or ]
              \]
            )
            }xs',
            array($this, '_doAnchors_reference_callback'), $text);

        $this->parser->in_anchor = false;
        return $text;
    }

    /**
     *
     * @param  array  $matches
     * @return string
     */
    protected function _doAnchors_reference_callback($matches)
    {
        $whole_match =  $matches[1];
        $link_text   =  $matches[2];
        $link_id     =& $matches[3];

        if ($link_id == "") {
            // for shortcut links like [this][] or [this].
            $link_id = $link_text;
        }

        // lower-case and turn embedded newlines into spaces
        $link_id = strtolower($link_id);
        $link_id = preg_replace('{[ ]?\n}', ' ', $link_id);

        if (isset($this->urls[$link_id])) {
            $url = $this->urls[$link_id];
            $url = $this->encoder->encodeAttribute($url);

            $result = "<a href=\"$url\"";
            if (isset($this->titles[$link_id])) {
                $title = $this->titles[$link_id];
                $title = $this->encoder->encodeAttribute($title);
                $result .=  " title=\"$title\"";
            }

            $link_text = $this->parser->runSpanGamut($link_text);
            $result .= ">$link_text</a>";
            $result = $this->hasher->hashPart($result);
        } else {
            $result = $whole_match;
        }
        return $result;
    }

    /**
     *
     * @param  array  $matches
     * @return string
     */
    protected function _doAnchors_inline_callback($matches)
    {
        $link_text = $this->parser->runSpanGamut($matches[2]);
        $url       = ($matches[3] === '') ? $matches[4] : $matches[3];
        $title     =& $matches[7];

        $url = $this->encoder->encodeAttribute($url);

        $result = "<a href=\"$url\"";
        if (isset($title)) {
            $title = $this->encoder->encodeAttribute($title);
            $result .=  " title=\"$title\"";
        }

        $link_text = $this->parser->runSpanGamut($link_text);
        $result .= ">$link_text</a>";

        return $this->hasher->hashPart($result);
    }
}
