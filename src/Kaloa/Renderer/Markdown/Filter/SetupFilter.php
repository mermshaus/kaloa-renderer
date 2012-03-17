<?php
/**
 * This is a PHP 5.3 port of the PHP Markdown class written by Michel Fortin.
 * PHP Markdown is based on the work of John Gruber. See README and LICENSE
 * files in the root directory of this package for full license info.
 */

namespace Kaloa\Renderer\Markdown\Filter;

use Kaloa\Renderer\Markdown\Filter\AbstractFilter;

/**
 *
 * @author Marc Ermshaus <marc@ermshaus.org>
 */
class SetupFilter extends AbstractFilter
{
    protected $tab_width;

    /**
     *
     * @param int $tabWidth
     */
    public function __construct($tabWidth)
    {
        $this->tab_width = $tabWidth;
    }

    /**
     *
     * @param  string $text
     * @return string
     */
    public function run($text)
    {
        // Remove UTF-8 BOM and marker character in input, if present.
        $text = preg_replace('{^\xEF\xBB\xBF|\x1A}', '', $text);

        // Standardize line endings:
        //   DOS to Unix and Mac to Unix
        $text = preg_replace('{\r\n?}', "\n", $text);

        // Make sure $text ends with a couple of newlines:
        $text .= "\n\n";

        // Convert all tabs to spaces.
        $text = $this->detab($text);

        return $text;
    }

    /**
     * Replace tabs with the appropriate amount of space.
     *
     * @param  string $text
     * @return string
     */
    protected function detab($text)
    {
        // For each line we separate the line in blocks delemited by
        // tab characters. Then we reconstruct every line by adding the
        // appropriate number of space between each blocks.

        $text = preg_replace_callback('/^.*\t.*$/m',
            array($this, '_detab_callback'), $text);

        return $text;
    }

    /**
     *
     * @param  array  $matches
     * @return string
     */
    protected function _detab_callback($matches)
    {
        $line = $matches[0];

        // Split in blocks.
        $blocks = explode("\t", $line);
        // Add each blocks to the line.
        $line = $blocks[0];
        // Do not add first block twice.
        unset($blocks[0]);
        foreach ($blocks as $block) {
            // Calculate amount of space, insert spaces, insert block.
            $amount = $this->tab_width -
                mb_strlen($line, 'UTF-8') % $this->tab_width;
            $line .= str_repeat(" ", $amount) . $block;
        }
        return $line;
    }
}
