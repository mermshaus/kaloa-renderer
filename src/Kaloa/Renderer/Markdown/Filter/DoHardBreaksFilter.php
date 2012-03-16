<?php

namespace Kaloa\Renderer\Markdown\Filter;

use Kaloa\Renderer\Markdown\Filter\AbstractFilter;
use Kaloa\Renderer\Markdown\Hasher;

/**
 *
 */
class DoHardBreaksFilter extends AbstractFilter
{
    /**
     *
     * @var Hasher
     */
    protected $hasher;

    /**
     *
     * @var bool
     */
    protected $empty_element_suffix;

    /**
     *
     * @param Hasher $hasher
     * @param bool   $emptyElementSuffix
     */
    public function __construct($hasher, $emptyElementSuffix)
    {
        $this->hasher = $hasher;
        $this->empty_element_suffix = $emptyElementSuffix;
    }

    /**
     *
     * @param  string $text
     * @return string
     */
    public function run($text)
    {
        // Do hard breaks:
        return preg_replace_callback('/ {2,}\n/',
            array($this, '_doHardBreaks_callback'), $text);
    }

    /**
     *
     * @param  array $matches
     * @return string
     */
    protected function _doHardBreaks_callback(/*$matches*/)
    {
        return $this->hasher->hashPart("<br$this->empty_element_suffix\n");
    }
}
