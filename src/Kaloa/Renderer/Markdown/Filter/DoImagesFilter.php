<?php

namespace Kaloa\Renderer\Markdown\Filter;

use Kaloa\Renderer\Markdown\Filter\AbstractFilter;
use Kaloa\Renderer\Markdown\Encoder;
use Kaloa\Renderer\Markdown\Hasher;
use Kaloa\Renderer\Markdown\RegexManager;
use ArrayObject;

/**
 *
 */
class DoImagesFilter extends AbstractFilter
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
     * @var bool
     */
    protected $empty_element_suffix;

    /**
     *
     * @param Encoder      $encoder
     * @param RegexManager $rem
     * @param Hasher       $hasher
     * @param ArrayObject  $urls
     * @param ArrayObject  $titles
     * @param bool         $empty_element_suffix
     */
    public function __construct(Encoder $encoder, RegexManager $rem,
            Hasher $hasher, ArrayObject $urls, ArrayObject $titles,
            $empty_element_suffix)
    {
        $this->encoder = $encoder;
        $this->rem = $rem;
        $this->hasher = $hasher;
        $this->urls = $urls;
        $this->titles = $titles;
        $this->empty_element_suffix = $empty_element_suffix;
    }

    /**
     * Turn Markdown image shortcuts into <img> tags.
     *
     * @param string $text
     * @return string
     */
    public function run($text)
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

        $alt_text = $this->encoder->encodeAttribute($alt_text);
        if (isset($this->urls[$link_id])) {
            $url = $this->encoder->encodeAttribute($this->urls[$link_id]);
            $result = "<img src=\"$url\" alt=\"$alt_text\"";
            if (isset($this->titles[$link_id])) {
                $title = $this->titles[$link_id];
                $title = $this->encoder->encodeAttribute($title);
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
     * @param  array  $matches
     * @return string
     */
    protected function _doImages_inline_callback($matches)
    {
        $alt_text = $matches[2];
        $url      = ($matches[3] === '') ? $matches[4] : $matches[3];
        $title    = &$matches[7];

        $alt_text = $this->encoder->encodeAttribute($alt_text);
        $url      = $this->encoder->encodeAttribute($url);
        $result = "<img src=\"$url\" alt=\"$alt_text\"";
        if (isset($title)) {
            $title = $this->encoder->encodeAttribute($title);
            // $title already quoted
            $result .= ' title="' . $title . '"';
        }
        $result .= $this->empty_element_suffix;

        return $this->hasher->hashPart($result);
    }
}
