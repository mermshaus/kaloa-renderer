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

/**
 * Finally form paragraph and restore hashed blocks.
 *
 * @author Marc Ermshaus <marc@ermshaus.org>
 */
class FormParagraphsFilter extends AbstractFilter
{
    /**
     *
     * @var Hasher
     */
    protected $hasher;

    /**
     *
     * @var Parser
     */
    protected $parser;

    /**
     *
     * @param Hasher $hasher
     * @param Parser $parser
     */
    public function __construct($hasher, $parser)
    {
        $this->hasher = $hasher;
        $this->parser = $parser;
    }

    /**
     *
     * @param  string $text string to process with html <p> tags
     * @return string
     */
    public function run($text)
    {
        // Strip leading and trailing lines:
        $text = preg_replace('/\A\n+|\n+\z/', '', $text);

        $grafs = preg_split('/\n{2,}/', $text, -1, PREG_SPLIT_NO_EMPTY);

        // Wrap <p> tags and unhashify HTML blocks
        foreach ($grafs as $key => $value) {
            if (!preg_match('/^B\x1A[0-9]+B$/', $value)) {
                // Is a paragraph.
                $value = $this->parser->runSpanGamut($value);
                $value = preg_replace('/^([ ]*)/', "<p>", $value);
                $value .= "</p>";
                $grafs[$key] = $this->hasher->unhash($value);
            } else {
                // Is a block.
                // Modify elements of @grafs in-place...
                $grafs[$key] = $this->hasher->getHashByKey($value);
            }
        }

        return implode("\n\n", $grafs);
    }
}
