<?php

namespace Kaloa\Renderer\Markdown;

use Exception;

class Encoder
{
    protected $no_entities = null;

    public function setNoEntities($noEntities)
    {
        $this->no_entities = $noEntities;
    }

    /**
     * Encode text for a double-quoted HTML attribute. This function
     * is *not* suitable for attributes enclosed in single quotes.
     *
     * @param  string $text
     * @return string
     */
    public function encodeAttribute($text)
    {
        $text = $this->encodeAmpsAndAngles($text);
        $text = str_replace('"', '&quot;', $text);
        return $text;
    }

    /**
     * Smart processing for ampersands and angle brackets that need to
     * be encoded. Valid character entities are left alone unless the
     * no-entities mode is set.
     *
     * @param  string $text
     * @return string
     */
    public function encodeAmpsAndAngles($text)
    {
        if ($this->no_entities === null) {
            throw new Exception('You need to call setNoEntities.');
        }

        if ($this->no_entities) {
            $text = str_replace('&', '&amp;', $text);
        } else {
            // Ampersand-encoding based entirely on Nat Irons's Amputator
            // MT plugin: <http://bumppo.net/projects/amputator/>
            $text = preg_replace('/&(?!#?[xX]?(?:[0-9a-fA-F]+|\w+);)/',
                                '&amp;', $text);;
        }
        // Encode remaining <'s
        $text = str_replace('<', '&lt;', $text);

        return $text;
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
     * @param  string $addr
     * @return string
     */
    public function encodeEmailAddress($addr)
    {
        $addr = "mailto:" . $addr;
        $chars = preg_split('/(?<!^)(?!$)/', $addr);
        // Deterministic seed.
        $seed = (int)abs(crc32($addr) / strlen($addr));

        foreach ($chars as $key => $char) {
            $ord = ord($char);
            // Ignore non-ascii chars.
            if ($ord < 128) {
                // Pseudo-random function.
                $r = ($seed * (1 + $key)) % 100;
                // roughly 10% raw, 45% hex, 45% dec
                // '@' *must* be encoded. I insist.
                if ($r > 90 && $char != '@') /* do nothing */;
                else if ($r < 45) $chars[$key] = '&#x'.dechex($ord).';';
                else              $chars[$key] = '&#'.$ord.';';
            }
        }

        $addr = implode('', $chars);
        $text = implode('', array_slice($chars, 7)); // text without `mailto:`
        $addr = "<a href=\"$addr\">$text</a>";

        return $addr;
    }
}
