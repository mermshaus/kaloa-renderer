<?php

namespace Kaloa\Renderer\Markdown\Filter;

use Kaloa\Renderer\Markdown\Filter\AbstractFilter;
use Kaloa\Renderer\Markdown\Encoder;
use Kaloa\Renderer\Markdown\Hasher;

/**
 * Make links out of things like `<http://example.com/>`. Must come after
 * DoAnchors, because you can use < and > delimiters in inline links like
 * [this](<url>).
 */
class DoAutoLinksFilter extends AbstractFilter
{
    /**
     *
     * @var Encoder
     */
    protected $encoder;

    /**
     *
     * @var Hasher
     */
    protected $hasher;

    /**
     *
     * @param Encoder $encoder
     * @param Hasher  $hasher
     */
    public function __construct(Encoder $encoder, Hasher $hasher)
    {
        $this->encoder = $encoder;
        $this->hasher  = $hasher;
    }

    /**
     *
     * @param  string $text
     * @return string
     */
    public function run($text)
    {
        $text = preg_replace_callback('{<((https?|ftp|dict):[^\'">\s]+)>}i',
            array($this, '_doAutoLinks_url_callback'), $text);

        // Email addresses: <address@domain.foo>
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
            array($this, '_doAutoLinks_email_callback'), $text);

        return $text;
    }

    /**
     *
     * @param  array  $matches
     * @return string
     */
    protected function _doAutoLinks_url_callback($matches)
    {
        $url = $this->encoder->encodeAttribute($matches[1]);
        $link = "<a href=\"$url\">$url</a>";
        return $this->hasher->hashPart($link);
    }

    /**
     *
     * @param  array $matches
     * @return string
     */
    protected function _doAutoLinks_email_callback($matches)
    {
        $address = $matches[1];
        $link = $this->encoder->encodeEmailAddress($address);
        return $this->hasher->hashPart($link);
    }
}
