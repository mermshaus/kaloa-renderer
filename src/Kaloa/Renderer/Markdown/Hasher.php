<?php
/**
 * This is a PHP 5.3 port of the PHP Markdown class written by Michel Fortin.
 * PHP Markdown is based on the work of John Gruber. See README and LICENSE
 * files in the root directory of this package for full license info.
 */

namespace Kaloa\Renderer\Markdown;

use Exception;

/**
 *
 * @author Marc Ermshaus <marc@ermshaus.org>
 */
class Hasher
{
    /**
     * Stores token to expanded content map
     * @var array
     */
    protected $hashes = array();

    /**
     * Called whenever a tag must be hashed when a function insert an atomic
     * element in the text stream. Passing $text to through this function gives
     * a unique text-token which will be reverted back when calling unhash.
     *
     * The $boundary argument specify what character should be used to surround
     * the token. By convension, "B" is used for block elements that needs not
     * to be wrapped into paragraph tags at the end, ":" is used for elements
     * that are word separators and "X" is used in the general case.
     *
     * @param  string $text
     * @param  string $boundary
     * @return string String that will replace the tag.
     */
    public function hashPart($text, $boundary = 'X')
    {
        // Swap back any tag hash found in $text so we do not have to `unhash`
        // multiple times at the end.
        $text = $this->unhash($text);

        // Then hash the block.
        $key = $boundary . "\x1A" . count($this->hashes) . $boundary;
        $this->hashes[$key] = $text;

        return $key;
    }

    /**
     * Shortcut function for hashPart with block-level boundaries.
     *
     * @param  string $text
     * @return string
     */
    public function hashBlock($text)
    {
        return $this->hashPart($text, 'B');
    }

    /**
     * Swap back in all the tags hashed by _HashHTMLBlocks.
     *
     * @param  string $text
     * @return string
     */
    public function unhash($text)
    {
        $hashes = $this->hashes;

        return preg_replace_callback('/(.)\x1A[0-9]+\1/',
            function ($matches) use ($hashes) {
                return $hashes[$matches[0]];
            }, $text);
    }

    /**
     *
     * @param  string $key
     * @return string
     * @throws Exception
     */
    public function getHashByKey($key)
    {
        if (!isset($this->hashes[$key])) {
            throw new Exception('Hash ' . $key . ' not found');
        }

        return $this->hashes[$key];
    }

    /**
     * Removes all stored hashes.
     */
    public function clear()
    {
        $this->hashes = array();
    }
}
